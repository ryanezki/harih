<?php
/**
 * Undangan Core — penegakan masa aktif paket (TASKS P2.1, eks-T3.13).
 *
 * Masa aktif H+7 / H+30 / 1 tahun tertulis di katalog, deskripsi produk WC, dan
 * S&K §4 ("halaman dinonaktifkan dan media dapat dihapus") — sebelum ini dijual
 * tanpa mekanisme apa pun. Dua akibatnya: disk & inodes hosting tumbuh selamanya,
 * dan "aktif 1 tahun" bukan pembeda Premium karena paket Hemat pun hidup abadi.
 *
 * KENAPA DI WP, BUKAN DI n8n (WF-05):
 * - Sumber kebenaran `paket` + `tanggal_resepsi` ada di meta post ini sendiri —
 *   tidak perlu REST, autentikasi, atau nomor baris Google Sheet yang bisa drift.
 * - Kueri `post_status=publish` membuat proses ini idempoten dengan sendirinya:
 *   undangan yang sudah draft tidak akan terambil lagi.
 * - Menyusul sendiri bila satu hari terlewat (n8n mati, cron gagal) — bukan
 *   aksi sekali-jalan yang hilang kalau momennya lewat.
 *
 * WF-05 tetap memegang PERINGATAN WA H-3 sebelum nonaktif (butuh nomor WA dari
 * sheet). Konstanta hari di bawah diduplikasi di node `Susun Pesan Harian`
 * WF-05 — bila keduanya drift, yang meleset hanya waktu peringatan, sedangkan
 * penegakannya tetap benar karena file inilah otoritasnya.
 */

if (!defined('ABSPATH')) exit;

const UNDANGAN_CRON_MASA_AKTIF = 'undangan_cek_masa_aktif';

/** Masa aktif per paket, dihitung sejak TANGGAL ACARA (§10 & S&K §4). */
function undangan_masa_aktif_hari(): array {
    return ['hemat' => 7, 'favorit' => 30, 'premium' => 365];
}

/**
 * Tanggal kedaluwarsa (Y-m-d) sebuah undangan, atau '' bila tidak dapat dihitung
 * (tanggal resepsi kosong/tidak valid — undangan seperti itu tidak pernah
 * dinonaktifkan otomatis; lebih baik menyisakan halaman hidup daripada
 * mematikan undangan customer karena datanya tidak terbaca).
 */
function undangan_tanggal_kedaluwarsa(int $post_id): string {
    $tgl = (string) get_post_meta($post_id, 'tanggal_resepsi', true);
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tgl)) return '';

    $paket = (string) get_post_meta($post_id, 'paket', true);
    $hari  = undangan_masa_aktif_hari()[$paket] ?? undangan_masa_aktif_hari()['hemat'];

    $ts = strtotime($tgl . ' 23:59:59 +07:00');
    if (!$ts) return '';

    return gmdate('Y-m-d', $ts + ($hari * DAY_IN_SECONDS));
}

/**
 * Undangan demo & undangan tanpa order tidak boleh ikut dinonaktifkan — demo
 * adalah etalase penjualan yang harus hidup terus (P0.2), dan mematikannya
 * berarti katalog menautkan halaman mati.
 */
function undangan_dikecualikan_masa_aktif(int $post_id): bool {
    $order_id = trim((string) get_post_meta($post_id, 'order_id', true));
    return $order_id === '' || strtolower($order_id) === 'demo';
}

/**
 * Nonaktifkan (set `draft`) semua undangan yang lewat masa aktif.
 * Mengembalikan daftar ID yang dinonaktifkan. `$dry_run` untuk uji tanpa efek.
 */
function undangan_jalankan_masa_aktif(bool $dry_run = false): array {
    $hari_ini = wp_date('Y-m-d');

    $posts = get_posts([
        'post_type'        => 'undangan',
        'post_status'      => 'publish',
        'numberposts'      => 200,   // batas aman per jalan; sisanya menyusul besok
        'fields'           => 'ids',
        'suppress_filters' => true,
    ]);

    $dinonaktifkan = [];
    foreach ($posts as $id) {
        $id = (int) $id;
        if (undangan_dikecualikan_masa_aktif($id)) continue;

        $kedaluwarsa = undangan_tanggal_kedaluwarsa($id);
        if ($kedaluwarsa === '' || $hari_ini < $kedaluwarsa) continue;

        if (!$dry_run) {
            wp_update_post(['ID' => $id, 'post_status' => 'draft']);
            update_post_meta($id, 'nonaktif_sejak', $hari_ini);
        }
        $dinonaktifkan[] = $id;
    }

    if ($dinonaktifkan && !$dry_run) {
        // Jejak untuk runbook: kenapa sebuah undangan tiba-tiba tidak bisa dibuka.
        error_log(sprintf(
            '[hariH] masa aktif: %d undangan dinonaktifkan (%s)',
            count($dinonaktifkan),
            implode(', ', $dinonaktifkan)
        ));
    }

    return $dinonaktifkan;
}

// Cron harian 03:00 WIB — sepi trafik, dan sudah lewat tengah malam sehingga
// "hari ini" pasti bergeser. Bergantung cron nyata hPanel (T1.6), bukan WP-cron
// bawaan yang butuh pengunjung.
add_action('init', function () {
    if (!wp_next_scheduled(UNDANGAN_CRON_MASA_AKTIF)) {
        $jam3 = strtotime('tomorrow 03:00:00 +07:00');
        wp_schedule_event($jam3 ?: time() + HOUR_IN_SECONDS, 'daily', UNDANGAN_CRON_MASA_AKTIF);
    }
});

/* =========================================================================
 * RETENSI 90 HARI (R2, eks-C8) — janji yang sudah tayang sejak 22 Juli
 *
 * `kebijakan-privasi.md` §5 menjanjikan data undangan & foto "dihapus/diarsipkan
 * paling lambat 90 hari setelah masa aktif berakhir", dan §7 menjanjikan hak
 * penghapusan ditanggapi ≤7 hari kerja. Sampai R2 tidak ada satu baris kode pun
 * yang menegakkannya: pass pertama di atas hanya mengubah post jadi `draft`,
 * sehingga HTML-nya mati tapi **foto pranikah dan gambar QRIS mempelai tetap
 * bisa diakses siapa pun lewat URL-nya**, selamanya. Yang mati cuma halamannya.
 *
 * Kenapa allowlist, bukan daftar-yang-dihapus: CPT ini punya 45 meta dan akan
 * bertambah (M2 menambah `mitra_id`). Daftar hapus akan tertinggal diam-diam
 * setiap kali meta baru lahir — dan yang tertinggal adalah data pribadi yang
 * seharusnya sudah hilang. Dengan allowlist, meta baru terhapus secara bawaan
 * dan yang perlu bertahan harus disebut sengaja. Disiplin yang sama dengan
 * `undangan_get_temas()` dan whitelist host `dompet`.
 * ========================================================================= */

const UNDANGAN_RETENSI_HARI = 90;

/**
 * Meta yang BOLEH bertahan setelah data pribadi dihapus.
 * Semuanya non-pribadi dan dibutuhkan pembukuan atau halaman 410.
 */
function undangan_meta_dipertahankan(): array {
    return [
        'order_id', 'paket', 'template_id', 'tanggal_resepsi', 'nonaktif_sejak', 'data_dihapus',
        // R4 — `mitra_id` bukan data pribadi mempelai melainkan relasi bisnis,
        // dan setelah pembersihan postnya jadi satu-satunya catatan mitra mana
        // yang menjual pesanan ini. Ditambahkan sengaja: allowlist bekerja
        // dengan cara membuang apa pun yang tidak disebut di sini.
        'mitra_id',
    ];
}

/**
 * Undangan yang tidak boleh dihapus datanya.
 *
 * Selain demo & undangan tanpa order (pengecualian yang sama dengan pass
 * pertama), pesanan bertanda `_harih_uji=1` juga dilindungi: `TEST-173` adalah
 * SUMBER berkas sampel cetak yang dipakai untuk menjawab pertanyaan `maxDim`
 * dan untuk memotret foto produk. Menghapus medianya berarti kehilangan satu-
 * satunya cetakan nyata yang pernah dibuat.
 */
function undangan_dikecualikan_hapus(int $post_id): bool {
    if (undangan_dikecualikan_masa_aktif($post_id)) return true;

    $order_id = (int) get_post_meta($post_id, 'order_id', true);
    if (!$order_id || !function_exists('wc_get_order')) return false;

    $order = wc_get_order($order_id);
    return $order instanceof WC_Order && function_exists('undangan_pesanan_uji')
        && undangan_pesanan_uji($order);
}

/**
 * URL media → ID attachment.
 *
 * `attachment_url_to_postid()` saja tidak cukup: ia mencocokkan lewat `guid`,
 * yang meleset bila URL situs pernah berubah (http→https, domain sementara).
 * Cadangannya mencocokkan path relatif ke `_wp_attached_file`, satu-satunya
 * nilai yang tidak ikut berubah saat URL situs bergeser.
 */
function undangan_attachment_dari_url(string $url): int {
    $id = (int) attachment_url_to_postid($url);
    if ($id) return $id;

    $up = wp_get_upload_dir();
    if (!empty($up['error'])) return 0;

    $base = trailingslashit($up['baseurl']);
    if (strpos($url, $base) !== 0) return 0;

    global $wpdb;
    return (int) $wpdb->get_var($wpdb->prepare(
        "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_wp_attached_file' AND meta_value = %s LIMIT 1",
        ltrim(substr($url, strlen($base)), '/')
    ));
}

/**
 * Hapus satu berkas media beserta seluruh ukuran turunannya.
 *
 * Bila attachment-nya tidak terlacak sama sekali (baris DB-nya hilang, berkasnya
 * tidak), berkasnya tetap dihapus langsung — karena yang dijanjikan ke pelanggan
 * adalah fotonya tidak lagi bisa diakses, bukan barisnya rapi. Penghapusan
 * langsung dibatasi ke dalam folder uploads: `realpath()` dibandingkan terhadap
 * basedir supaya URL yang dipalsukan tidak bisa menunjuk keluar.
 */
function undangan_hapus_media_url(string $url): bool {
    $url = trim($url);
    if ($url === '') return false;

    if ($att = undangan_attachment_dari_url($url)) {
        return (bool) wp_delete_attachment($att, true);
    }

    $up = wp_get_upload_dir();
    if (!empty($up['error'])) return false;

    $base = trailingslashit($up['baseurl']);
    if (strpos($url, $base) !== 0) return false;

    $abs = realpath(trailingslashit($up['basedir']) . ltrim(substr($url, strlen($base)), '/'));
    $dir = realpath($up['basedir']);
    if (!$abs || !$dir || strpos($abs, trailingslashit($dir)) !== 0) return false;

    return @unlink($abs);
}

/**
 * Hapus seluruh data pribadi satu undangan — foto, QRIS, ucapan tamu, daftar
 * nama tamu, kartu OG, dan seluruh meta di luar allowlist.
 *
 * Dipakai DUA jalur: cron retensi 90 hari di bawah, dan permintaan penghapusan
 * pelanggan (§7 Kebijakan Privasi, ≤7 hari kerja) — lihat runbook §7b. Postnya
 * sendiri sengaja TIDAK dihapus: slug-nya masih dibutuhkan halaman 410 supaya
 * tamu yang membuka link lama dapat penjelasan, bukan 404 telanjang.
 *
 * Mengembalikan ringkasan apa yang dihapus. `$dry_run` menghitung tanpa efek.
 */
function undangan_hapus_data_undangan(int $post_id, bool $dry_run = false): array {
    $hasil = ['post_id' => $post_id, 'media' => 0, 'ucapan' => 0, 'meta' => [], 'kartu_og' => 0, 'snapshot_order' => false];

    // --- media: galeri (maks 10) + QRIS ---
    $galeri = json_decode((string) get_post_meta($post_id, 'galeri', true), true);
    $urls   = is_array($galeri) ? $galeri : [];
    $urls[] = (string) get_post_meta($post_id, 'qris_media_url', true);
    foreach (array_filter(array_map('strval', $urls)) as $url) {
        if ($dry_run) { $hasil['media']++; continue; }
        if (undangan_hapus_media_url($url)) $hasil['media']++;
    }

    // --- ucapan tamu (memuat `wa_rsvp`, nomor WA tamu) ---
    $ucapan = get_posts([
        'post_type'        => 'ucapan',
        'post_status'      => 'any',
        'numberposts'      => -1,
        'fields'           => 'ids',
        'meta_key'         => 'undangan_id',
        'meta_value'       => $post_id,
        'suppress_filters' => true,
    ]);
    foreach ($ucapan as $uid) {
        if ($dry_run) { $hasil['ucapan']++; continue; }
        if (wp_delete_post((int) $uid, true)) $hasil['ucapan']++;
    }

    // --- meta: semua kecuali allowlist ---
    $simpan = undangan_meta_dipertahankan();
    foreach (array_keys((array) get_post_meta($post_id)) as $key) {
        if (in_array($key, $simpan, true) || strpos($key, '_') === 0) continue;
        $hasil['meta'][] = $key;
        if (!$dry_run) delete_post_meta($post_id, $key);
    }

    // --- kartu OG: memuat nama mempelai + potongan foto mereka ---
    $up = wp_get_upload_dir();
    if (empty($up['error'])) {
        $dir = trailingslashit($up['basedir']) . 'harih-og';
        foreach ((array) glob("{$dir}/" . $post_id . "-*.jpg") as $berkas) {
            $hasil['kartu_og']++;
            if (!$dry_run) @unlink($berkas);
        }
    }

    // --- snapshot proof di ORDER: hash bertahan, isinya tidak ---
    // `_snapshot` membekukan 20 kunci data TERMASUK `daftar_tamu` — jadi tanpa
    // langkah ini daftar 600 nama tamu tetap tersimpan di order meski sudah
    // hilang dari undangannya. Yang dipertahankan `_snapshot_hash`,
    // `_proof_hash`, dan `_proof_disetujui`: cukup untuk membuktikan pelanggan
    // pernah menyetujui proof (S&K §12.1) tanpa menyimpan isinya lagi.
    $order_id = (int) get_post_meta($post_id, 'order_id', true);
    if ($order_id && function_exists('wc_get_order') && ($order = wc_get_order($order_id))) {
        if ($order->get_meta('_snapshot') !== '' || $order->get_meta('_proof_ip') !== '') {
            $hasil['snapshot_order'] = true;
            if (!$dry_run) {
                $order->delete_meta_data('_snapshot');
                $order->delete_meta_data('_proof_ip');
                $order->save();
            }
        }
    }

    if (!$dry_run) update_post_meta($post_id, 'data_dihapus', wp_date('Y-m-d'));

    return $hasil;
}

/**
 * Pass kedua: undangan yang sudah nonaktif lebih dari UNDANGAN_RETENSI_HARI
 * kehilangan seluruh data pribadinya.
 *
 * Bersandar pada `nonaktif_sejak` yang sudah ditulis pass pertama sejak awal —
 * jadi tidak perlu menebak kapan sebuah undangan berhenti aktif. Undangan yang
 * di-draft manual tanpa meta itu sengaja dilewati: tanpa tanggal, tidak ada
 * dasar menghitung 90 hari, dan menghapus data pelanggan atas dasar tebakan
 * jauh lebih buruk daripada menundanya.
 */
function undangan_jalankan_hapus_data(bool $dry_run = false): array {
    $batas = wp_date('Y-m-d', time() - (UNDANGAN_RETENSI_HARI * DAY_IN_SECONDS));

    $posts = get_posts([
        'post_type'        => 'undangan',
        'post_status'      => 'draft',
        'numberposts'      => 50,   // lebih kecil dari pass pertama: tiap item menghapus berkas
        'fields'           => 'ids',
        'suppress_filters' => true,
        'meta_query'       => [
            ['key' => 'nonaktif_sejak', 'value' => $batas, 'compare' => '<=', 'type' => 'CHAR'],
            ['key' => 'data_dihapus', 'compare' => 'NOT EXISTS'],
        ],
    ]);

    $hasil = [];
    foreach ($posts as $id) {
        $id = (int) $id;
        if (undangan_dikecualikan_hapus($id)) continue;

        // Sabuk pengaman kedua: hitung ulang di PHP, jangan percaya meta_query
        // saja untuk keputusan yang tidak bisa dibatalkan.
        $sejak = (string) get_post_meta($id, 'nonaktif_sejak', true);
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $sejak) || $sejak > $batas) continue;

        $hasil[] = undangan_hapus_data_undangan($id, $dry_run);
    }

    if ($hasil && !$dry_run) {
        error_log(sprintf(
            '[hariH] retensi %d hari: data %d undangan dihapus (%s)',
            UNDANGAN_RETENSI_HARI,
            count($hasil),
            implode(', ', array_column($hasil, 'post_id'))
        ));
    }

    return $hasil;
}

add_action(UNDANGAN_CRON_MASA_AKTIF, function () {
    undangan_jalankan_masa_aktif();
    undangan_jalankan_hapus_data();
});

/**
 * Halaman undangan yang sudah nonaktif: beri penjelasan, bukan 404 telanjang —
 * tamu yang membuka link lama dari WhatsApp harus paham apa yang terjadi.
 */
add_action('template_redirect', function () {
    if (!is_404()) return;

    $slug = trim((string) get_query_var('name'));
    if ($slug === '') return;

    $post = get_posts([
        'post_type'        => 'undangan',
        'post_status'      => 'draft',
        'name'             => $slug,
        'numberposts'      => 1,
        'suppress_filters' => true,
    ]);
    if (!$post) return;

    status_header(410); // Gone — pernah ada, sengaja tidak lagi
    nocache_headers();
    wp_die(
        '<p>Masa aktif undangan ini telah berakhir.</p>' .
        '<p>Bila Anda pemilik undangan dan ingin mengaktifkannya kembali, hubungi CS hariH melalui ' .
        '<a href="' . esc_url(home_url('/kontak/')) . '">halaman kontak</a>.</p>',
        'Masa aktif berakhir — hariH',
        ['response' => 410]
    );
});
