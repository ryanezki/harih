<?php
/**
 * Undangan Core — snapshot beku (F4.1) & persetujuan proof (F4.5).
 *
 * KENAPA INI ADA: S&K §12.1 sudah menyatakan "setelah proof Anda setujui,
 * kebenaran seluruh teks menjadi tanggung jawab Anda" — klausul itu tayang
 * sejak halaman harga terbit, padahal sistemnya belum bisa membuktikan apa
 * pun: tidak ada catatan siapa menyetujui apa dan kapan. Selama itu kosong,
 * setiap sengketa typo otomatis jadi biaya kita.
 *
 * Dua bagian yang saling mengunci:
 *   1. SNAPSHOT — data undangan dibekukan jadi JSON + hash pada saat proof
 *      dibuat. Produksi membaca snapshot, bukan data live; pelanggan yang
 *      mengubah undangan digitalnya setelah itu tidak diam-diam mengubah apa
 *      yang sedang dicetak.
 *   2. PERSETUJUAN — halaman bertoken mencatat waktu, hash berkas proof, dan
 *      hash snapshot yang berlaku saat disetujui. Yang disetujui bisa
 *      dibuktikan, bukan diingat-ingat.
 */

if (!defined('ABSPATH')) exit;

/** Undangan milik sebuah order (dibuat WF-02 dengan meta `order_id`). */
function undangan_cari_undangan_order(int $order_id): int {
    $cari = static function (int $id): int {
        $q = get_posts([
            'post_type'      => 'undangan',
            'post_status'    => ['publish', 'draft', 'pending'],
            'posts_per_page' => 1,
            'fields'         => 'ids',
            'meta_key'       => 'order_id',
            'meta_value'     => (string) $id,
        ]);
        return $q ? (int) $q[0] : 0;
    };

    $uid = $cari($order_id);
    if ($uid) return $uid;

    /* D1 — IKUTI RANTAI UPGRADE.
     *
     * Pembelian upgrade (`UPG-*`) lahir sebagai order BARU lewat checkout biasa,
     * sementara undangannya melekat pada order ASAL lewat meta `order_id`.
     * Tanpa penelusuran ini, seluruh konsumennya gagal diam-diam pada order
     * upgrade: snapshot tidak bisa dibekukan, `/tamu/` bilang "undangan belum
     * dibuat", `/rekap/` kosong, dan Antrean Cetak menampilkan "Menunggu data
     * undangan" selamanya — pada pesanan yang justru sudah dibayar.
     *
     * Batas 5 langkah: upgrade bertingkat tidak dilarang, tapi rantai yang
     * melingkar (meta salah isi) tidak boleh berubah jadi loop tak berujung.
     */
    if (!function_exists('wc_get_order')) return 0;
    $kunjungi = [$order_id => true];
    $kini = $order_id;
    for ($langkah = 0; $langkah < 5; $langkah++) {
        $o = wc_get_order($kini);
        if (!$o) return 0;
        $asal = (int) $o->get_meta('_upgrade_dari');
        if (!$asal || isset($kunjungi[$asal])) return 0;
        $kunjungi[$asal] = true;
        $uid = $cari($asal);
        if ($uid) return $uid;
        $kini = $asal;
    }
    return 0;
}

/* D1 — `upgrade_dari` dibawa dari halaman upsell lewat query string saat
 * add-to-cart, disimpan di sesi WooCommerce, lalu dituliskan ke meta order.
 * Lewat sesi karena checkout memakai BLOK: parameter URL tidak ikut sampai ke
 * permintaan Store API yang akhirnya membuat ordernya. */
add_action('woocommerce_add_to_cart', function () {
    $asal = absint($_GET['upgrade_dari'] ?? 0);
    if ($asal && function_exists('WC') && WC()->session) {
        WC()->session->set('harih_upgrade_dari', $asal);
    }
}, 10, 0);

/** Jalur BLOK (yang benar-benar dipakai checkout). */
add_action('woocommerce_store_api_checkout_update_order_from_request', function ($order) {
    if (!function_exists('WC') || !WC()->session) return;
    $asal = absint(WC()->session->get('harih_upgrade_dari'));
    if ($asal && $asal !== $order->get_id()) {
        $order->update_meta_data('_upgrade_dari', $asal);
        $order->add_order_note(sprintf('Upgrade dari pesanan #%d — undangan & daftar tamu mengikuti pesanan itu.', $asal));
    }
}, 20, 1);

/** Jalur klasik, bila checkout suatu saat dikembalikan ke shortcode. */
add_action('woocommerce_checkout_create_order', function ($order) {
    if (!function_exists('WC') || !WC()->session) return;
    $asal = absint(WC()->session->get('harih_upgrade_dari'));
    if ($asal) $order->update_meta_data('_upgrade_dari', $asal);
}, 20, 1);

/** Bersihkan sesi setelah order jadi — supaya pembelian berikutnya tidak ikut tertandai. */
add_action('woocommerce_checkout_order_created', function () {
    if (function_exists('WC') && WC()->session) WC()->session->__unset('harih_upgrade_dari');
});
add_action('woocommerce_store_api_checkout_order_processed', function () {
    if (function_exists('WC') && WC()->session) WC()->session->__unset('harih_upgrade_dari');
});

/** Isi undangan yang relevan untuk cetak, dalam urutan tetap agar hash stabil. */
function undangan_data_snapshot(int $undangan_id): array {
    $kunci = [
        'nama_pria', 'nama_wanita', 'ortu_pria', 'ortu_wanita',
        'anak_ke_pria', 'anak_ke_wanita', 'nuansa',
        'tanggal_akad', 'waktu_akad', 'lokasi_akad_nama', 'lokasi_akad_alamat',
        'tanggal_resepsi', 'waktu_resepsi', 'lokasi_nama', 'lokasi_alamat',
        'turut_mengundang', 'rundown', 'dresscode', 'alamat_kado',
        // C6 — daftar nama tamu IKUT dibekukan. Amplop bernama adalah pembeda
        // yang dijual, 50–150 keping per order, dan tanpa ini `_proof_hash`
        // tidak pernah mengunci satu pun nama: tidak ada jejak versi mana yang
        // pemesan setujui. Dikerjakan sekarang justru karena NOL snapshot ada
        // di produksi — menambah kunci tidak membatalkan hash siapa pun. Nanti
        // mahal. `sort()` di bawah menjaga urutan tetap, jadi hash tetap stabil.
        'daftar_tamu',
    ];
    sort($kunci); // urutan tetap → hash tidak berubah hanya karena urutan
    $data = ['undangan_id' => $undangan_id, 'permalink' => get_permalink($undangan_id)];
    foreach ($kunci as $k) $data[$k] = trim((string) get_post_meta($undangan_id, $k, true));
    return $data;
}

/**
 * Bekukan data undangan ke order. Aman dipanggil ulang: snapshot yang sudah
 * DISETUJUI tidak pernah ditimpa — itu inti gunanya.
 */
function undangan_bekukan_snapshot(WC_Order $order, bool $paksa = false): array {
    if (!$paksa && $order->get_meta('_proof_disetujui')) {
        return ['ok' => false, 'pesan' => 'Snapshot sudah disetujui pelanggan — tidak boleh ditimpa.'];
    }
    $uid = undangan_cari_undangan_order($order->get_id());
    if (!$uid) {
        return ['ok' => false, 'pesan' => 'Undangan untuk order ini belum ada — pelanggan belum mengisi formulir data.'];
    }
    $data = undangan_data_snapshot($uid);
    $json = wp_json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $order->update_meta_data('_snapshot', $json);
    $order->update_meta_data('_snapshot_hash', substr(hash('sha256', $json), 0, 16));
    $order->update_meta_data('_snapshot_tgl', gmdate('c'));
    $order->save();
    return ['ok' => true, 'hash' => $order->get_meta('_snapshot_hash')];
}

/** Daftar URL berkas proof yang dilampirkan CS. */
function undangan_proof_berkas(WC_Order $order): array {
    $raw = (string) $order->get_meta('_proof_url');
    $url = array_filter(array_map('trim', preg_split('/[\r\n,]+/', $raw)));
    return array_values(array_filter($url, static fn($u) => (bool) filter_var($u, FILTER_VALIDATE_URL)));
}

/* =========================================================================
 * Halaman order: lampirkan proof, bekukan snapshot, lihat status persetujuan
 * ========================================================================= */

add_action('woocommerce_admin_order_data_after_shipping_address', function ($order) {
    $punya_cetak = false;
    foreach ($order->get_items() as $item) {
        if (in_array(undangan_jenis_produk($item->get_product()), ['hybrid', 'satuan'], true)) { $punya_cetak = true; break; }
    }
    if (!$punya_cetak) return;

    $disetujui = $order->get_meta('_proof_disetujui');
    $hash      = $order->get_meta('_snapshot_hash');
    $link      = undangan_proof_link($order);

    echo '<div style="margin-top:14px;padding:12px;border:1px solid #dcdcde;border-radius:4px;background:#fff">';
    echo '<p style="margin:0 0 8px"><strong>Proof cetak</strong></p>';

    echo '<p class="form-field form-field-wide"><label for="harih_proof_url">URL berkas proof <em>(satu per baris)</em></label>';
    echo '<textarea id="harih_proof_url" name="harih_proof_url" rows="2" style="width:100%">' . esc_textarea((string) $order->get_meta('_proof_url')) . '</textarea></p>';

    echo '<p><label><input type="checkbox" name="harih_bekukan_snapshot" value="1"> Bekukan snapshot data undangan sekarang</label></p>';

    if ($hash) {
        printf('<p style="margin:6px 0">Snapshot: <code>%s</code> · dibekukan %s</p>', esc_html($hash), esc_html((string) $order->get_meta('_snapshot_tgl')));
    } elseif ((string) $order->get_meta('_proof_url') !== '') {
        /* U7 — kombinasi paling berbahaya, dan yang paling mudah terjadi:
           URL proof sudah ditempel tapi kotak "Bekukan snapshot" terlewat.
           Pelanggan lalu melihat halaman tanpa tabel data, dan `_proof_hash`
           mengunci berkas saja — nol data. Halaman pelanggan sekarang menahan
           tombolnya, tapi CS harus tahu SEBABNYA di sini, bukan menebak kenapa
           persetujuan tidak kunjung masuk. */
        echo '<p style="margin:6px 0;padding:8px 10px;border-left:3px solid #d63638;background:#fcf0f1;color:#8a1f21">'
           . '<strong>URL proof sudah diisi tapi snapshot BELUM dibekukan.</strong> Tombol persetujuan sengaja tidak ditampilkan ke pelanggan sampai snapshot ada — centang kotak di atas lalu simpan.</p>';
    } else {
        echo '<p style="margin:6px 0;color:#996800">Snapshot belum dibekukan.</p>';
    }

    if ($disetujui) {
        printf(
            '<p style="margin:6px 0;color:#1e7e34"><strong>DISETUJUI pelanggan</strong> %s · hash berkas <code>%s</code></p>',
            esc_html($disetujui), esc_html((string) $order->get_meta('_proof_hash'))
        );
    } elseif ($link) {
        printf('<p style="margin:6px 0">Link persetujuan untuk pelanggan:<br><input type="text" readonly value="%s" style="width:100%%" onclick="this.select()"></p>', esc_attr($link));
    }
    $tamu = add_query_arg(
        ['order' => $order->get_id(), 'key' => undangan_token_halaman($order->get_id(), 'tamu')],
        home_url('/tamu/')
    );
    printf('<p style="margin:10px 0 0">Link daftar tamu (amplop bernama + link personal):<br><input type="text" readonly value="%s" style="width:100%%" onclick="this.select()"></p>', esc_attr($tamu));
    $uid = undangan_cari_undangan_order($order->get_id());
    if ($uid) {
        $n = count(array_filter(array_map('trim', explode("\n", (string) get_post_meta($uid, 'daftar_tamu', true)))));
        printf('<p style="margin:6px 0">Nama tamu terkumpul: <strong>%d</strong></p>', $n);
    }
    echo '</div>';
}, 20);

add_action('woocommerce_process_shop_order_meta', function ($order_id) {
    if (!current_user_can('edit_shop_orders')) return;
    $order = wc_get_order($order_id);
    if (!$order) return;

    if (isset($_POST['harih_proof_url'])) {
        $order->update_meta_data('_proof_url', sanitize_textarea_field(wp_unslash($_POST['harih_proof_url'])));
        $order->save();
    }
    if (!empty($_POST['harih_bekukan_snapshot'])) {
        $hasil = undangan_bekukan_snapshot($order);
        $order->add_order_note($hasil['ok']
            ? sprintf('Snapshot data undangan dibekukan (hash %s).', $hasil['hash'])
            : 'Snapshot GAGAL dibekukan: ' . $hasil['pesan']);
    }
}, 30);

/** Link persetujuan proof — token bercakupan `proof` (B9). */
function undangan_proof_link(WC_Order $order): string {
    if (!defined('FORM_TOKEN_SECRET')) return '';
    $id = $order->get_id();
    return add_query_arg([
        'order' => $id,
        'key'   => undangan_token_halaman($id, 'proof'),
    ], home_url('/proof/'));
}

/* =========================================================================
 * Pencatatan persetujuan (dipanggil dari template halaman proof)
 * ========================================================================= */

function undangan_catat_persetujuan(WC_Order $order): array {
    if ($order->get_meta('_proof_disetujui')) {
        return ['ok' => true, 'pesan' => 'Proof ini sudah disetujui sebelumnya.'];
    }
    $berkas = undangan_proof_berkas($order);
    if (!$berkas) {
        return ['ok' => false, 'pesan' => 'Belum ada berkas proof untuk disetujui.'];
    }
    // Hash mengunci DUA hal sekaligus: berkas proof yang dilihat pelanggan, dan
    // snapshot data yang berlaku saat itu. Salah satunya berubah → hash berbeda.
    $bahan = implode('|', $berkas) . '|' . (string) $order->get_meta('_snapshot_hash');
    $order->update_meta_data('_proof_disetujui', gmdate('c'));
    $order->update_meta_data('_proof_hash', substr(hash('sha256', $bahan), 0, 16));
    $order->update_meta_data('_proof_ip', sanitize_text_field((string) ($_SERVER['REMOTE_ADDR'] ?? '')));
    $order->save();
    $order->add_order_note(sprintf(
        'Proof DISETUJUI pelanggan pada %s (hash %s, snapshot %s). Sejak titik ini kesalahan teks yang sudah tampak di proof menjadi tanggung jawab pemesan — S&K §12.1.',
        $order->get_meta('_proof_disetujui'), $order->get_meta('_proof_hash'), (string) $order->get_meta('_snapshot_hash')
    ), false);
    return ['ok' => true, 'pesan' => 'Terima kasih — persetujuan Anda tercatat.'];
}

/* =========================================================================
 * F4.7 — ANTREAN PRODUKSI.
 *
 * Diurutkan berdasarkan **tenggat**, bukan tanggal order: pesanan yang masuk
 * belakangan bisa saja acaranya lebih dulu. Halaman ini yang dipakai memilih
 * pekerjaan hari ini, jadi yang ditampilkan hanya hal yang mengubah keputusan:
 * berapa hari tersisa, langkah mana yang menghambat, dan berapa nama tamu yang
 * sudah masuk (tanpa itu amplop tidak bisa dicetak).
 * ========================================================================= */

add_action('admin_menu', function () {
    add_submenu_page(
        'woocommerce',
        'Antrean Produksi Cetak',
        'Antrean Cetak',
        'edit_shop_orders',
        'harih-antrean',
        'undangan_render_antrean'
    );
});

function undangan_render_antrean(): void {
    $orders = wc_get_orders([
        'limit'   => 60,
        'status'  => ['processing', 'on-hold', 'completed'],
        'return'  => 'objects',
        'orderby' => 'date',
        'order'   => 'DESC',
    ]);

    $baris = [];
    foreach ($orders as $o) {
        $cetak = false;
        foreach ($o->get_items() as $item) {
            if (in_array(undangan_jenis_produk($item->get_product()), ['hybrid', 'satuan'], true)) { $cetak = true; break; }
        }
        if (!$cetak) continue;

        $uid   = undangan_cari_undangan_order($o->get_id());
        $acara = (string) $o->get_meta('_tanggal_acara');
        if (!$acara && $uid) $acara = (string) get_post_meta($uid, 'tanggal_resepsi', true);
        $sisa  = $acara ? (int) floor((strtotime($acara) - time()) / 86400) : null;
        $tamu  = $uid ? count(array_filter(array_map('trim', explode("\n", (string) get_post_meta($uid, 'daftar_tamu', true))))) : 0;

        // Langkah penghambat — satu kalimat, karena itu yang menentukan
        // pekerjaan berikutnya. Urutannya = urutan alur produksi.
        if (!$uid)                              $tahap = ['Menunggu data undangan', 'gagal'];
        elseif ($o->get_meta('_proof_disetujui')) $tahap = $o->get_meta('_resi') ? ['Terkirim', 'ok'] : ['SIAP CETAK', 'siap'];
        elseif (undangan_proof_berkas($o))        $tahap = ['Menunggu persetujuan pelanggan', 'tunggu'];
        elseif ($tamu === 0)                      $tahap = ['Menunggu daftar tamu', 'tunggu'];
        else                                      $tahap = ['Siapkan proof', 'aksi'];

        // Pesanan uji TETAP tampil di antrean — justru supaya kelihatan sedang
        // ada di jalur produksi — tapi ditandai terang supaya tidak ada yang
        // mencetaknya untuk pelanggan. Ia tidak menghitung kuota (cetak.php).
        if (function_exists('undangan_pesanan_uji') && undangan_pesanan_uji($o)) {
            $tahap = ['UJI INTERNAL · ' . $tahap[0], 'uji'];
        }
        $baris[] = compact('o', 'acara', 'sisa', 'tamu', 'tahap') + ['uid' => $uid];
    }

    // Tenggat terdekat di atas; yang tanpa tanggal ditaruh paling bawah.
    usort($baris, static fn($a, $b) => ($a['sisa'] ?? PHP_INT_MAX) <=> ($b['sisa'] ?? PHP_INT_MAX));

    $warna = ['gagal' => '#b32d2e', 'tunggu' => '#996800', 'aksi' => '#2271b1', 'siap' => '#1e7e34', 'ok' => '#646970', 'uji' => '#8c5e00'];

    echo '<div class="wrap"><h1>Antrean Produksi Cetak</h1>';
    printf(
        '<p>Kuota bulan ini: <strong>%d dari %d</strong> terpakai. Diurutkan berdasarkan <strong>tenggat acara</strong>, bukan tanggal pesanan.</p>',
        (int) undangan_kuota_terpakai(), (int) UNDANGAN_KUOTA_BULAN
    );

    if (!$baris) {
        echo '<p>Belum ada pesanan cetak yang berjalan.</p></div>';
        return;
    }

    echo '<table class="wp-list-table widefat fixed striped"><thead><tr>'
       . '<th style="width:90px">Pesanan</th><th>Pelanggan</th><th style="width:150px">Acara</th>'
       . '<th style="width:90px">Sisa</th><th style="width:80px">Tamu</th><th style="width:230px">Langkah berikutnya</th>'
       . '<th style="width:150px">Resi</th></tr></thead><tbody>';

    foreach ($baris as $b) {
        $o = $b['o'];
        printf(
            '<tr><td><a href="%s"><strong>#%d</strong></a></td><td>%s</td><td>%s</td>'
            . '<td%s>%s</td><td>%s</td><td><span style="color:%s;font-weight:600">%s</span></td><td>%s</td></tr>',
            esc_url($o->get_edit_order_url()),
            $o->get_id(),
            esc_html(trim($o->get_billing_first_name() . ' ' . $o->get_billing_last_name()) ?: '—'),
            esc_html($b['acara'] ?: '—'),
            $b['sisa'] !== null && $b['sisa'] < 14 ? ' style="color:#b32d2e;font-weight:600"' : '',
            $b['sisa'] === null ? '—' : esc_html($b['sisa'] . ' hari'),
            esc_html((string) $b['tamu']),
            esc_attr($warna[$b['tahap'][1]] ?? '#000'),
            esc_html($b['tahap'][0]),
            esc_html(trim($o->get_meta('_kurir') . ' ' . $o->get_meta('_resi')) ?: '—')
        );
    }
    echo '</tbody></table></div>';
}
