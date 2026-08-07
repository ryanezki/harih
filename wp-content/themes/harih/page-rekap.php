<?php
/**
 * Template Name: Rekap RSVP hariH
 *
 * Rekap konfirmasi kehadiran untuk mempelai (TASKS FU.5), bertoken seperti
 * `/isi-data/`, `/upsell/`, `/proof/`, `/tamu/`.
 *
 * KENAPA INI BUKAN "FITUR BARU": RSVP sudah dijual di SEMUA paket sejak awal,
 * tapi pembelinya tidak pernah punya cara memakainya — dinding ucapan di
 * undangan hanya menampilkan pesan, tanpa hitungan, tanpa pembagian sesi,
 * dan tidak bisa dibawa ke katering. Halaman ini menutup lubang pada fitur
 * yang sudah dibayar, bukan menambah yang baru.
 *
 * Catatan privasi: sejak nomor WhatsApp tamu tidak lagi dikumpulkan di form
 * RSVP (keputusan owner — undangan disebar lewat WA, kontaknya sudah dipegang),
 * halaman ini pun tidak menampilkan kolom kontak. Yang ada hanya nama, jumlah,
 * sesi, dan pesan.
 */

if (!defined('ABSPATH')) exit;

// Halaman bertoken tidak boleh di-cache — sama seperti /isi-data/. URL-nya
// memuat token order, dan isinya khusus satu pemesan.
nocache_headers();
do_action('litespeed_control_set_nocache'); // no-op bila LSCWP tidak aktif

$order_id = absint($_GET['order'] ?? 0);
$key      = sanitize_text_field((string) ($_GET['key'] ?? ''));

// B9 — token DICAKUP per halaman: token /rekap/ tidak membuka halaman lain.
// Rumusnya terpusat di undangan_token_halaman() (mu-plugins/undangan-core).
$sah = undangan_token_sah($order_id, 'rekap', $key);
if (!$sah) {
    wp_die('Link rekap tidak valid. Hubungi CS bila kamu merasa ini keliru.', 'Link tidak valid', ['response' => 403]);
}

$undangan_id = function_exists('undangan_cari_undangan_order') ? undangan_cari_undangan_order($order_id) : 0;

$ucapan = $undangan_id ? get_posts([
    'post_type'   => 'ucapan',
    'numberposts' => 500,
    'meta_key'    => 'undangan_id',
    'meta_value'  => (string) $undangan_id,
    'orderby'     => 'date',
    'order'       => 'DESC',
]) : [];

$SESI  = ['akad' => 'Akad', 'resepsi' => 'Resepsi', 'keduanya' => 'Akad & Resepsi'];
$STAT  = ['hadir' => 'Hadir', 'tidak' => 'Berhalangan', 'ragu' => 'Belum pasti'];

$rekap = ['hadir' => 0, 'tidak' => 0, 'ragu' => 0];
$per_sesi = ['akad' => 0, 'resepsi' => 0, 'keduanya' => 0];
$total_tamu = 0;
$baris = [];

foreach ($ucapan as $u) {
    $hadir  = (string) get_post_meta($u->ID, 'hadir', true) ?: 'ragu';
    $jumlah = max(1, (int) get_post_meta($u->ID, 'jumlah', true));
    $sesi   = (string) get_post_meta($u->ID, 'sesi', true) ?: 'keduanya';

    $rekap[$hadir] = ($rekap[$hadir] ?? 0) + 1;
    if ($hadir === 'hadir') {
        $total_tamu += $jumlah;
        if (isset($per_sesi[$sesi])) $per_sesi[$sesi] += $jumlah;
    }
    $baris[] = [
        'nama'   => wp_specialchars_decode($u->post_title, ENT_QUOTES),
        'hadir'  => $hadir,
        'jumlah' => $jumlah,
        'sesi'   => $sesi,
        'pesan'  => wp_specialchars_decode($u->post_content, ENT_QUOTES),
        'waktu'  => get_the_date('d/m/Y H:i', $u),
    ];
}

// Yang benar-benar dipakai saat menghitung porsi katering & kursi.
$hadir_akad    = $per_sesi['akad'] + $per_sesi['keduanya'];
$hadir_resepsi = $per_sesi['resepsi'] + $per_sesi['keduanya'];
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="robots" content="noindex, nofollow">
<meta name="referrer" content="no-referrer">
<?php wp_head(); ?>
</head>
<body class="katalog-body rekap-body">

<header class="hero hero-ringkas">
    <p class="brand">hariH</p>
    <h1>Rekap konfirmasi kehadiran</h1>
    <p class="hero-sub">Angka di bawah dihitung dari jumlah tamu yang dibawa tiap responden — bukan sekadar jumlah orang yang mengisi form. Ini yang dipakai saat menghitung porsi katering &amp; kursi.</p>
</header>

<main>
    <?php if (!$undangan_id) : ?>
        <p class="satuan-kosong">Undangan kamu belum dibuat, jadi belum ada RSVP yang masuk.</p>
    <?php elseif (!$baris) : ?>
        <p class="satuan-kosong">Belum ada tamu yang mengisi konfirmasi kehadiran. Bagikan undanganmu, lalu buka halaman ini lagi.</p>
    <?php else : ?>

    <section class="rekap-angka">
        <div class="rekap-kartu rekap-utama">
            <p class="rekap-nilai"><?php echo esc_html($total_tamu); ?></p>
            <p class="rekap-label">total tamu yang menyatakan hadir</p>
        </div>
        <div class="rekap-kartu"><p class="rekap-nilai"><?php echo esc_html($hadir_akad); ?></p><p class="rekap-label">hadir di akad</p></div>
        <div class="rekap-kartu"><p class="rekap-nilai"><?php echo esc_html($hadir_resepsi); ?></p><p class="rekap-label">hadir di resepsi</p></div>
        <div class="rekap-kartu"><p class="rekap-nilai"><?php echo esc_html(count($baris)); ?></p><p class="rekap-label">responden</p></div>
    </section>

    <section class="rekap-status">
        <?php foreach ($STAT as $k => $label) : ?>
            <span class="rekap-pil"><?php echo esc_html($label); ?>: <strong><?php echo esc_html((int) ($rekap[$k] ?? 0)); ?></strong></span>
        <?php endforeach; ?>
    </section>

    <section class="tamu-link">
        <div class="tamu-aksi">
            <button type="button" class="btn btn-garis" id="unduh-rekap">Unduh CSV</button>
            <a class="btn btn-garis" href="<?php echo esc_url(add_query_arg(['order' => $order_id, 'key' => undangan_token_halaman($order_id, 'tamu')], home_url('/tamu/'))); ?>">Daftar tamu &amp; link personal</a>
        </div>

        <table class="rekap-tabel" id="rekap-tabel">
            <thead><tr><th>Nama</th><th>Status</th><th>Tamu</th><th>Sesi</th><th>Waktu</th></tr></thead>
            <tbody>
            <?php foreach ($baris as $b) : ?>
                <tr data-pesan="<?php echo esc_attr($b['pesan']); ?>">
                    <td><?php echo esc_html($b['nama']); ?></td>
                    <td><?php echo esc_html($STAT[$b['hadir']] ?? $b['hadir']); ?></td>
                    <td class="rekap-angka-sel"><?php echo esc_html($b['hadir'] === 'hadir' ? $b['jumlah'] : '—'); ?></td>
                    <td><?php echo esc_html($b['hadir'] === 'hadir' ? ($SESI[$b['sesi']] ?? '—') : '—'); ?></td>
                    <td><?php echo esc_html($b['waktu']); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>

    <?php endif; ?>
</main>

<footer class="kaki">
    <p class="brand">hariH</p>
    <p class="kaki-tag">Pesanan #<?php echo esc_html($order_id); ?></p>
    <nav class="kaki-nav"><a href="<?php echo esc_url(home_url('/kontak/')); ?>">Kontak</a></nav>
</footer>

<script>
(function () {
    'use strict';
    var btn = document.getElementById('unduh-rekap');
    var tabel = document.getElementById('rekap-tabel');
    if (!btn || !tabel) return;
    btn.addEventListener('click', function () {
        // Pesan tamu ikut di CSV meski tidak ditampilkan di tabel — di layar ia
        // membuat baris jadi panjang, tapi mempelai sering ingin membacanya utuh.
        var baris = [['nama', 'status', 'jumlah_tamu', 'sesi', 'waktu', 'pesan']];
        tabel.querySelectorAll('tbody tr').forEach(function (tr) {
            var sel = [].map.call(tr.children, function (td) { return td.textContent.trim(); });
            sel.push(tr.getAttribute('data-pesan') || '');
            baris.push(sel);
        });
        var isi = baris.map(function (r) {
            return r.map(function (v) { return '"' + String(v).replace(/"/g, '""') + '"'; }).join(',');
        }).join('\n');
        var url = URL.createObjectURL(new Blob(["﻿" + isi], { type: 'text/csv;charset=utf-8' }));
        var a = document.createElement('a');
        a.href = url; a.download = 'rekap-rsvp.csv';
        document.body.appendChild(a); a.click(); a.remove();
        setTimeout(function () { URL.revokeObjectURL(url); }, 1000);
    });
})();
</script>
<?php wp_footer(); ?>
</body>
</html>
