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
    // Konteks keranjang tidak selalu ada (mis. saat admin memuat locale).
    if (!function_exists('WC') || !WC()->cart || WC()->cart->is_empty()) return $locale;
    if (undangan_cart_ada_fisik()) return $locale;

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
