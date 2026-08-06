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
    $q = get_posts([
        'post_type'      => 'undangan',
        'post_status'    => ['publish', 'draft', 'pending'],
        'posts_per_page' => 1,
        'fields'         => 'ids',
        'meta_key'       => 'order_id',
        'meta_value'     => (string) $order_id,
    ]);
    return $q ? (int) $q[0] : 0;
}

/** Isi undangan yang relevan untuk cetak, dalam urutan tetap agar hash stabil. */
function undangan_data_snapshot(int $undangan_id): array {
    $kunci = [
        'nama_pria', 'nama_wanita', 'ortu_pria', 'ortu_wanita',
        'anak_ke_pria', 'anak_ke_wanita', 'nuansa',
        'tanggal_akad', 'waktu_akad', 'lokasi_akad_nama', 'lokasi_akad_alamat',
        'tanggal_resepsi', 'waktu_resepsi', 'lokasi_nama', 'lokasi_alamat',
        'turut_mengundang', 'rundown', 'dresscode', 'alamat_kado',
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
        ['order' => $order->get_id(), 'key' => substr(hash_hmac('sha256', (string) $order->get_id(), FORM_TOKEN_SECRET), 0, 16)],
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

/** Link persetujuan proof — token HMAC yang sama dengan /isi-data/ & /upsell/. */
function undangan_proof_link(WC_Order $order): string {
    if (!defined('FORM_TOKEN_SECRET')) return '';
    $id = $order->get_id();
    return add_query_arg([
        'order' => $id,
        'key'   => substr(hash_hmac('sha256', (string) $id, FORM_TOKEN_SECRET), 0, 16),
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
