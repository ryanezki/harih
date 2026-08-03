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

add_action(UNDANGAN_CRON_MASA_AKTIF, function () {
    undangan_jalankan_masa_aktif();
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
