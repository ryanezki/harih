<?php
/**
 * Undangan Core — produk CETAK (fisik) di WooCommerce. TASKS F3.1, F3.4–F3.8.
 *
 * Latar: seluruh toko dibangun untuk satu jenis barang — undangan digital.
 * Tiga aturan di `woocommerce.php` (sold individually global, cart dikosongkan
 * tiap add-to-cart, semua field alamat di-unset) memang disengaja untuk
 * menaikkan konversi mobile, tapi bersama-sama membuat barang fisik MUSTAHIL
 * dipesan: kuantitas terkunci 1, cetak+digital tidak bisa satu keranjang, dan
 * tidak ada alamat kirim di mana pun.
 *
 * Berkas ini membuat ketiga aturan itu KONDISIONAL, bukan menghapusnya:
 * perilaku lama tetap persis sama selama keranjang hanya berisi digital.
 *
 * Konvensi SKU adalah sumber kebenaran jenis produk — dipakai di sini DAN di
 * WF-01 (yang hanya menerima payload REST, tanpa akses kategori WP):
 *   HARIH-*  → paket undangan digital
 *   CETAK-*  → paket hybrid (digital + barang cetak)
 *   SATUAN-* → item cetak satuan (à la carte), tanpa komponen digital
 *   UPG-*    → SKU upgrade pasca-bayar (dibuat di F3.9 setelah harga F1.3)
 */

if (!defined('ABSPATH')) exit;

/** Jenis produk dari SKU. 'digital' | 'hybrid' | 'satuan' | '' */
function undangan_jenis_produk($product): string {
    if (is_numeric($product)) $product = wc_get_product($product);
    if (!$product instanceof WC_Product) return '';
    $sku = strtoupper((string) $product->get_sku());
    if (str_starts_with($sku, 'HARIH-'))  return 'digital';
    if (str_starts_with($sku, 'CETAK-') || str_starts_with($sku, 'UPG-')) return 'hybrid';
    if (str_starts_with($sku, 'SATUAN-')) return 'satuan';
    // Tanpa SKU dikenal: produk virtual dianggap digital (aman — perilaku lama).
    return $product->is_virtual() ? 'digital' : 'satuan';
}

/** Keranjang memuat barang yang harus dikirim? */
function undangan_cart_ada_fisik(): bool {
    if (!function_exists('WC') || !WC()->cart) return false;
    foreach (WC()->cart->get_cart() as $item) {
        $p = $item['data'] ?? null;
        if ($p instanceof WC_Product && !$p->is_virtual()) return true;
    }
    return false;
}

/* =========================================================================
 * F3.4 — `sold_individually` hanya untuk paket, bukan item satuan.
 * Filter global di woocommerce.php mengunci kuantitas SEMUA produk di 1,
 * sehingga "150 kartu QR" à la carte tidak mungkin dipesan.
 * ========================================================================= */
add_filter('woocommerce_is_sold_individually', function ($sold, $product) {
    // Paket (digital & hybrid) tetap 1 per order: 1 order = 1 undangan/1 token.
    // Item satuan bebas kuantitas — memang dijual per pcs dengan minimum.
    return undangan_jenis_produk($product) === 'satuan' ? false : $sold;
}, 20, 2);

/* =========================================================================
 * F3.5 — pengosongan cart jadi selektif.
 * Aturan lama mengosongkan SELURUH cart tiap penambahan, jadi pembeli tidak
 * bisa menambah item satuan ke paketnya. Aturan baru: hanya PAKET yang saling
 * menggantikan (tetap 1 paket per order); item satuan menumpuk seperti biasa.
 * ========================================================================= */
add_filter('woocommerce_add_to_cart_validation', function ($passed, $product_id) {
    if (!$passed || !function_exists('WC') || !WC()->cart || WC()->cart->is_empty()) return $passed;

    if (undangan_jenis_produk($product_id) === 'satuan') return $passed; // menumpuk

    // Yang ditambahkan adalah paket → buang paket lain, sisakan item satuan.
    foreach (WC()->cart->get_cart() as $key => $item) {
        if (undangan_jenis_produk($item['data'] ?? null) !== 'satuan') {
            WC()->cart->remove_cart_item($key);
        }
    }
    return $passed;
}, 20, 2);

/* =========================================================================
 * F3.6 — alamat kirim muncul HANYA bila keranjang memuat barang fisik.
 * Checkout ramping (nama + email + WA) tetap jadi jalur default produk
 * digital: itu yang menjaga konversi mobile.
 * ========================================================================= */
add_filter('woocommerce_checkout_fields', function ($fields) {
    if (!undangan_cart_ada_fisik()) return $fields; // biarkan checkout ramping

    $alamat = [
        'billing_address_1' => ['label' => 'Alamat lengkap', 'placeholder' => 'Nama jalan, nomor rumah, RT/RW', 'required' => true,  'priority' => 60],
        'billing_address_2' => ['label' => 'Detail tambahan (patokan)', 'placeholder' => 'Blok / kecamatan / patokan', 'required' => false, 'priority' => 65],
        'billing_city'      => ['label' => 'Kota / Kabupaten', 'required' => true, 'priority' => 70],
        'billing_state'     => ['label' => 'Provinsi', 'required' => true, 'priority' => 75],
        'billing_postcode'  => ['label' => 'Kode pos', 'required' => true, 'priority' => 80, 'validate' => ['postcode']],
        'billing_country'   => ['type' => 'country', 'label' => 'Negara', 'required' => true, 'priority' => 85, 'default' => 'ID', 'class' => ['hidden-field']],
    ];
    foreach ($alamat as $key => $cfg) {
        $fields['billing'][$key] = array_merge($fields['billing'][$key] ?? [], $cfg);
    }

    // Catatan pesanan dikembalikan: pembeli cetak sering menitipkan pesan
    // (patokan rumah, jam terima paket) yang tidak ada tempatnya di field lain.
    $fields['order']['order_comments'] = [
        'label'       => 'Catatan pengiriman (opsional)',
        'placeholder' => 'Patokan rumah, jam terima paket, dsb.',
        'required'    => false,
    ];

    return $fields;
}, 20);

// Alamat kirim = alamat tagih (satu alamat per pesanan, keputusan F1).
add_filter('woocommerce_cart_needs_shipping_address', function ($needs) {
    return undangan_cart_ada_fisik() ? $needs : false;
});
add_filter('woocommerce_ship_to_different_address_checked', '__return_false');

/* =========================================================================
 * F3.6b — TEMUAN F3 (2026-08-06): halaman checkout memakai blok
 * `woocommerce/checkout`, BUKAN shortcode klasik. Akibatnya filter
 * `woocommerce_checkout_fields` di atas (dan di woocommerce.php) TIDAK
 * berlaku pada checkout yang benar-benar dilihat pembeli — "checkout ramping"
 * untuk produk digital tidak pernah aktif, dan pembeli undangan Rp 99rb tetap
 * dimintai alamat lengkap dari HP.
 *
 * Blok checkout menghormati aturan locale negara, termasuk `hidden`. Jadi
 * untuk keranjang TANPA barang fisik, field alamat ditandai tersembunyi &
 * tidak wajib lewat jalur itu. Begitu ada barang cetak, aturan ini tidak
 * dipasang sehingga alamat kembali muncul apa adanya.
 * ========================================================================= */
add_filter('woocommerce_get_country_locale', function ($locale) {
    // ⚠️ REKURSI — penyebab `/harga/` menggantung 2026-08-06:
    // filter ini membaca keranjang, sedangkan MEMUAT keranjang sendiri
    // meminta data locale → filter ini dipanggil lagi → tak berujung.
    // Dua penjaga: bendera re-entrancy, dan syarat bahwa keranjang memang
    // sudah selesai dimuat dari sesi (tanpa itu jangan disentuh sama sekali).
    static $sedang_jalan = false;
    if ($sedang_jalan) return $locale;
    if (is_admin() || !function_exists('WC')) return $locale;
    if (!did_action('woocommerce_cart_loaded_from_session')) return $locale;
    if (!WC()->cart || WC()->cart->is_empty()) return $locale;

    $sedang_jalan = true;
    $ada_fisik = undangan_cart_ada_fisik();
    $sedang_jalan = false;
    if ($ada_fisik) return $locale;

    $sembunyi = ['address_1', 'address_2', 'city', 'state', 'postcode'];
    foreach (array_keys($locale) as $negara) {
        foreach ($sembunyi as $f) {
            $locale[$negara][$f] = array_merge($locale[$negara][$f] ?? [], [
                'required' => false,
                'hidden'   => true,
            ]);
        }
    }
    return $locale;
}, 20);

/* =========================================================================
 * F3.1 — kupon reseller `RES-` tidak boleh menyedot komisi dari barang cetak.
 * Pembatasan kategori di pengaturan kupon bisa lupa dipasang saat kupon baru
 * dibuat; guard ini berlaku otomatis untuk SEMUA kupon berawalan RES-.
 * Marjin cetak dibayar dalam rupiah tetap (150/300/500rb), bukan persen.
 * ========================================================================= */
add_filter('woocommerce_coupon_is_valid_for_product', function ($valid, $product, $coupon) {
    if (!$coupon instanceof WC_Coupon) return $valid;
    if (!str_starts_with(strtoupper($coupon->get_code()), 'RES-')) return $valid;
    return undangan_jenis_produk($product) === 'digital' ? $valid : false;
}, 10, 3);

// Kupon RES- pada keranjang yang SELURUHNYA cetak: tolak dengan pesan jelas,
// bukan diam-diam berlaku 0 (pembeli berhak tahu kenapa kodenya tidak jalan).
add_filter('woocommerce_coupon_is_valid', function ($valid, $coupon) {
    if (!$valid || !$coupon instanceof WC_Coupon) return $valid;
    if (!str_starts_with(strtoupper($coupon->get_code()), 'RES-')) return $valid;
    if (!function_exists('WC') || !WC()->cart) return $valid;
    foreach (WC()->cart->get_cart() as $item) {
        if (undangan_jenis_produk($item['data'] ?? null) === 'digital') return $valid;
    }
    throw new Exception('Kode reseller hanya berlaku untuk paket undangan digital.');
}, 10, 2);

/* =========================================================================
 * F3.11 — aturan minimum item satuan (à la carte).
 *
 * Dua lapis, keduanya wajib:
 *   1. minimum PER PRODUK (meta `_min_qty`) — order 20 pcs memakan waktu
 *      setup yang hampir sama dengan 200 pcs, jadi minimum itu yang menjaga
 *      pekerjaan tetap layak dikerjakan;
 *   2. minimum Rp 1.000.000 PER TRANSAKSI (keputusan terkunci) — berlaku bila
 *      keranjang HANYA berisi item satuan. Begitu ada paket di keranjang,
 *      nilai transaksinya sudah jauh di atas itu.
 *
 * Ditegakkan untuk checkout klasik DAN Store API (checkout blok memakai Store
 * API — pelajaran F3.6: memasang hook klasik saja berarti aturan tidak pernah
 * benar-benar berlaku).
 * ========================================================================= */
const UNDANGAN_MIN_TRANSAKSI_SATUAN = 1000000;

function undangan_min_qty($product): int {
    if (is_numeric($product)) $product = wc_get_product($product);
    if (!$product instanceof WC_Product) return 1;
    return max(1, (int) $product->get_meta('_min_qty'));
}

/** Daftar pelanggaran minimum pada keranjang saat ini. */
function undangan_cek_minimum(): array {
    $galat = [];
    if (!function_exists('WC') || !WC()->cart || WC()->cart->is_empty()) return $galat;

    $subtotal_satuan = 0;
    $ada_paket = false;

    foreach (WC()->cart->get_cart() as $item) {
        $p = $item['data'] ?? null;
        if (!$p instanceof WC_Product) continue;
        if (undangan_jenis_produk($p) !== 'satuan') { $ada_paket = true; continue; }

        $min = undangan_min_qty($p);
        if ((int) $item['quantity'] < $min) {
            $galat[] = sprintf('%s: minimum %d pcs per pesanan (sekarang %d).', $p->get_name(), $min, (int) $item['quantity']);
        }
        $subtotal_satuan += (float) $item['line_subtotal'];
    }

    if (!$ada_paket && $subtotal_satuan > 0 && $subtotal_satuan < UNDANGAN_MIN_TRANSAKSI_SATUAN) {
        $galat[] = sprintf(
            'Pembelian item satuan minimum %s per transaksi — kurang %s lagi. Tambahkan item, atau pilih paket yang sudah termasuk undangan digital.',
            strip_tags(wc_price(UNDANGAN_MIN_TRANSAKSI_SATUAN)),
            strip_tags(wc_price(UNDANGAN_MIN_TRANSAKSI_SATUAN - $subtotal_satuan))
        );
    }
    return $galat;
}

// Checkout klasik & halaman keranjang.
add_action('woocommerce_check_cart_items', function () {
    foreach (undangan_cek_minimum() as $pesan) wc_add_notice($pesan, 'error');
});

// Checkout blok (Store API) — jalur yang benar-benar dipakai pembeli.
add_action('woocommerce_store_api_cart_errors', function ($errors) {
    foreach (undangan_cek_minimum() as $i => $pesan) {
        $errors->add('undangan_minimum_' . $i, $pesan);
    }
}, 10, 1);

// Kuantitas awal di tombol beli mengikuti minimum produk — pembeli tidak perlu
// menebak, dan tidak terlanjur menaruh 1 pcs lalu ditolak di checkout.
add_filter('woocommerce_quantity_input_args', function ($args, $product) {
    if (undangan_jenis_produk($product) !== 'satuan') return $args;
    $min = undangan_min_qty($product);
    $args['min_value'] = $min;
    $args['step'] = 1;
    if (($args['input_value'] ?? 1) < $min) $args['input_value'] = $min;
    return $args;
}, 10, 2);

// Menambah item satuan dari link `?add-to-cart=` tanpa qty → langsung minimum.
add_filter('woocommerce_add_to_cart_quantity', function ($qty, $product_id) {
    if (undangan_jenis_produk($product_id) !== 'satuan') return $qty;
    return max((int) $qty, undangan_min_qty($product_id));
}, 10, 2);

/* =========================================================================
 * C1 (keputusan owner 2026-08-06) — order bernilai besar hanya lewat VA.
 *
 * Paket cetak berkisar Rp 1,19–5,9 juta. E-wallet & QRIS punya batas nominal
 * per transaksi yang lebih rendah dan lebih sering gagal di angka sebesar itu;
 * gagal bayar di langkah terakhir = order hilang setelah semua gesekan
 * dilewati. Untuk keranjang di atas ambang, hanya virtual account & gerai
 * yang ditawarkan.
 * ========================================================================= */
const UNDANGAN_AMBANG_VA = 2000000;

add_filter('woocommerce_available_payment_gateways', function ($gateways) {
    if (is_admin() || !function_exists('WC') || !WC()->cart) return $gateways;
    // Hanya di halaman keranjang/checkout. `get_total('edit')` memicu
    // calculate_totals() → kalkulasi ongkir; kalau filter ini ikut berjalan di
    // halaman biasa (plugin lain bisa menghitung gateway kapan saja), setiap
    // pemuatan halaman menyeret pekerjaan yang tidak ada gunanya di situ.
    if (!function_exists('is_checkout') || !(is_checkout() || is_cart() || (defined('REST_REQUEST') && REST_REQUEST))) return $gateways;
    $total = (float) WC()->cart->get_total('edit');
    if ($total <= UNDANGAN_AMBANG_VA) return $gateways;

    // Yang dipertahankan: semua VA + gerai retail (nominal besar aman).
    foreach (array_keys($gateways) as $id) {
        $aman = str_contains($id, '_va_') || str_contains($id, 'briva') || str_contains($id, 'indomaret') || str_contains($id, 'alfamart');
        if (!$aman) unset($gateways[$id]);
    }
    return $gateways;
}, 20);

/* =========================================================================
 * F4.6 — GERBANG PRODUKSI: tenggat H-21 & kuota bulanan.
 *
 * Dua janji di halaman harga sudah jadi klausul S&K §12: barang tiba H-14
 * atau uang kembali 100%, dan kuota produksi per bulan terbatas. Keduanya
 * hanya bisa ditepati kalau order yang MUSTAHIL dikerjakan ditolak SEBELUM
 * uang berpindah — menolak setelah dibayar berarti refund, dan refund atas
 * order Rp 2,9 juta bukan kerugian kecil.
 *
 * Tanggal acara ditanyakan DI CHECKOUT khusus pesanan cetak. Untuk pesanan
 * digital tanggal tetap diisi belakangan di /isi-data/ — di sana tidak ada
 * tenggat produksi yang perlu dijaga.
 * ========================================================================= */
const UNDANGAN_TENGGAT_HARI = 21;
const UNDANGAN_KUOTA_BULAN  = 8;

add_action('woocommerce_init', function () {
    if (!function_exists('woocommerce_register_additional_checkout_field')) return;
    woocommerce_register_additional_checkout_field([
        'id'         => 'harih/tanggal-acara',
        'label'      => 'Tanggal acara (untuk pesanan cetak)',
        'location'   => 'order',
        'type'       => 'text',
        'required'   => false, // kewajibannya bersyarat — divalidasi di bawah
        'attributes' => [
            'placeholder' => 'YYYY-MM-DD, mis. 2026-11-21',
            'inputmode'   => 'numeric',
            'autocomplete'=> 'off',
        ],
    ]);
});

/** Sisa slot produksi bulan berjalan (di-cache 10 menit — dipanggil tiap
 *  pemuatan halaman harga, dan hitungannya menyapu order). */
function undangan_sisa_slot(): int {
    $kunci = 'harih_sisa_slot_' . gmdate('Y-m');
    $sisa  = get_transient($kunci);
    if ($sisa === false) {
        $sisa = max(0, UNDANGAN_KUOTA_BULAN - undangan_kuota_terpakai());
        set_transient($kunci, $sisa, 10 * MINUTE_IN_SECONDS);
    }
    return (int) $sisa;
}

/** Jumlah pesanan cetak yang sudah masuk pada bulan berjalan. */
function undangan_kuota_terpakai(): int {
    $orders = wc_get_orders([
        'limit'        => 50,
        'status'       => ['processing', 'on-hold', 'completed'],
        'date_created' => '>=' . gmdate('Y-m-01'),
        'return'       => 'objects',
    ]);
    $n = 0;
    foreach ($orders as $o) {
        foreach ($o->get_items() as $item) {
            if (in_array(undangan_jenis_produk($item->get_product()), ['hybrid', 'satuan'], true)) { $n++; break; }
        }
    }
    return $n;
}

/**
 * Galat yang menghalangi checkout pesanan cetak. $tanggal boleh kosong saat
 * dipanggil dari keranjang (field-nya baru ada di checkout).
 */
function undangan_cek_gerbang_cetak(string $tanggal = '', bool $wajib_tanggal = false): array {
    if (!undangan_cart_ada_fisik()) return [];
    $galat = [];

    if (undangan_kuota_terpakai() >= UNDANGAN_KUOTA_BULAN) {
        $galat[] = sprintf(
            'Kuota produksi cetak bulan ini (%d pesanan) sudah penuh. Hubungi kami lewat WhatsApp untuk jadwal bulan berikutnya — kami tidak menerima pesanan yang tidak bisa kami penuhi tepat waktu.',
            UNDANGAN_KUOTA_BULAN
        );
    }

    $tanggal = trim($tanggal);
    if ($tanggal === '') {
        if ($wajib_tanggal) $galat[] = 'Isi tanggal acara (format YYYY-MM-DD) — kami memakainya untuk memastikan pesanan bisa tiba tepat waktu.';
        return $galat;
    }

    $t = DateTime::createFromFormat('Y-m-d', $tanggal);
    if (!$t || $t->format('Y-m-d') !== $tanggal) {
        $galat[] = 'Format tanggal acara belum benar — tulis seperti 2026-11-21.';
        return $galat;
    }

    $selisih = (int) floor((strtotime($tanggal) - strtotime(gmdate('Y-m-d'))) / 86400);
    if ($selisih < UNDANGAN_TENGGAT_HARI) {
        $galat[] = sprintf(
            'Acara Anda %d hari lagi, sementara pesanan cetak diterima paling lambat H-%d. Batas ini yang membuat kami bisa menjamin barang tiba H-14 — pesanan yang lebih mepet tidak kami terima. Hubungi WhatsApp kami untuk mencari jalan keluar tercepat.',
            max(0, $selisih), UNDANGAN_TENGGAT_HARI
        );
    }
    return $galat;
}

// Peringatan dini di keranjang: kuota penuh diberitahukan sebelum pembeli
// mengisi alamat, bukan setelahnya.
add_action('woocommerce_store_api_cart_errors', function ($errors) {
    if (!undangan_cart_ada_fisik()) return;
    if (undangan_kuota_terpakai() < UNDANGAN_KUOTA_BULAN) return;
    $errors->add('undangan_kuota', sprintf(
        'Kuota produksi cetak bulan ini (%d pesanan) sudah penuh — hubungi WhatsApp kami untuk jadwal bulan berikutnya.',
        UNDANGAN_KUOTA_BULAN
    ));
}, 20, 1);

// Gerbang sesungguhnya: checkout blok (Store API).
add_action('woocommerce_store_api_checkout_update_order_from_request', function ($order, $request) {
    $fields  = (array) ($request['additional_fields'] ?? []);
    $tanggal = (string) ($fields['harih/tanggal-acara'] ?? '');
    $galat   = undangan_cek_gerbang_cetak($tanggal, true);
    if (!$galat) {
        if ($tanggal !== '') $order->update_meta_data('_tanggal_acara', $tanggal);
        return;
    }
    if (class_exists('\Automattic\WooCommerce\StoreApi\Exceptions\RouteException')) {
        throw new \Automattic\WooCommerce\StoreApi\Exceptions\RouteException(
            'undangan_gerbang_cetak', implode(' ', $galat), 400
        );
    }
}, 10, 2);

// Jalur klasik (bila checkout dikembalikan ke shortcode suatu saat).
add_action('woocommerce_after_checkout_validation', function ($data, $errors) {
    foreach (undangan_cek_gerbang_cetak((string) ($_POST['harih-tanggal-acara'] ?? ''), true) as $pesan) {
        $errors->add('undangan_gerbang_cetak', $pesan);
    }
}, 20, 2);

// Tanggal acara tampil di halaman order, bersebelahan dengan resi.
add_action('woocommerce_admin_order_data_after_shipping_address', function ($order) {
    if ($t = $order->get_meta('_tanggal_acara')) {
        $sisa = (int) floor((strtotime($t) - time()) / 86400);
        printf('<p><strong>Tanggal acara:</strong> %s <em>(%d hari lagi)</em></p>', esc_html($t), $sisa);
    }
}, 5);

/* =========================================================================
 * F3.8 — nomor resi pengiriman: field order + kolom daftar pesanan.
 * Disimpan sebagai meta `_resi` (HPOS-aman lewat API CRUD order).
 * ========================================================================= */
add_action('woocommerce_admin_order_data_after_shipping_address', function ($order) {
    $resi = $order->get_meta('_resi');
    $kurir = $order->get_meta('_kurir');
    echo '<p class="form-field form-field-wide"><label for="harih_kurir">Kurir</label>';
    echo '<input type="text" id="harih_kurir" name="harih_kurir" value="' . esc_attr($kurir) . '" placeholder="mis. JNE / SiCepat"></p>';
    echo '<p class="form-field form-field-wide"><label for="harih_resi">Nomor resi</label>';
    echo '<input type="text" id="harih_resi" name="harih_resi" value="' . esc_attr($resi) . '" placeholder="nomor resi pengiriman"></p>';
});

add_action('woocommerce_process_shop_order_meta', function ($order_id) {
    if (!current_user_can('edit_shop_orders')) return;
    $order = wc_get_order($order_id);
    if (!$order) return;
    if (isset($_POST['harih_resi']))  $order->update_meta_data('_resi', sanitize_text_field(wp_unslash($_POST['harih_resi'])));
    if (isset($_POST['harih_kurir'])) $order->update_meta_data('_kurir', sanitize_text_field(wp_unslash($_POST['harih_kurir'])));
    $order->save();
}, 20);

// Kolom resi di daftar pesanan (HPOS memakai hook berbeda dari tabel lama —
// keduanya didaftarkan supaya tidak bergantung mode penyimpanan).
add_filter('manage_woocommerce_page_wc-orders_columns', 'undangan_kolom_resi', 20);
add_filter('manage_edit-shop_order_columns', 'undangan_kolom_resi', 20);
function undangan_kolom_resi($kolom) {
    $baru = [];
    foreach ($kolom as $k => $v) {
        $baru[$k] = $v;
        if ($k === 'order_status') $baru['harih_resi'] = 'Resi';
    }
    return isset($baru['harih_resi']) ? $baru : $kolom + ['harih_resi' => 'Resi'];
}

add_action('manage_woocommerce_page_wc-orders_custom_column', 'undangan_isi_kolom_resi', 10, 2);
add_action('manage_shop_order_posts_custom_column', 'undangan_isi_kolom_resi', 10, 2);
function undangan_isi_kolom_resi($kolom, $order) {
    if ($kolom !== 'harih_resi') return;
    $order = $order instanceof WC_Order ? $order : wc_get_order($order);
    if (!$order) return;
    $resi = $order->get_meta('_resi');
    echo $resi ? esc_html(trim($order->get_meta('_kurir') . ' ' . $resi)) : '—';
}
