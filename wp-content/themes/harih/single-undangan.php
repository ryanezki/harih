<?php
/**
 * Template CPT `undangan` — halaman berdiri sendiri (blueprint §7).
 *
 * Tidak memakai get_header()/get_footer() Astra (undangan = halaman imersif
 * penuh), tapi wp_head()/wp_footer() TETAP dipanggil agar LiteSpeed, robots
 * noindex, OG tags, dan enqueue aset berfungsi. Semua meta dibaca server-side
 * (get_post_meta) — REST baca publik diblokir di mu-plugin.
 *
 * Tema visual = skin CSS: undangan/shared/ (kerangka) + undangan/{tema}/ (kulit).
 */

if (!defined('ABSPATH')) exit;

the_post();

$id = get_the_ID();
$m  = static fn(string $k): string => trim((string) get_post_meta($id, $k, true));

$paket   = harih_paket_aktif($id);
$plus    = $paket !== 'hemat';   // fitur Favorit ke atas (§10)
$premium = $paket === 'premium';

$galeri = json_decode($m('galeri') ?: '[]', true);
$galeri = is_array($galeri) ? array_values(array_filter(array_map('esc_url', $galeri))) : [];

$undangan = [
    'id'              => $id,
    'judul'           => get_the_title(),
    'permalink'       => get_permalink($id),
    'paket'           => $paket,
    'plus'            => $plus,
    'premium'         => $premium,
    'nama_pria'       => $m('nama_pria'),
    'nama_wanita'     => $m('nama_wanita'),
    'ortu_pria'       => $m('ortu_pria'),
    'ortu_wanita'     => $m('ortu_wanita'),
    'tanggal_akad'    => $m('tanggal_akad'),
    'waktu_akad'      => $m('waktu_akad'),
    'tanggal_resepsi' => $m('tanggal_resepsi'),
    'waktu_resepsi'   => $m('waktu_resepsi'),
    'lokasi_nama'     => $m('lokasi_nama'),
    'lokasi_alamat'   => $m('lokasi_alamat'),
    'gmaps_url'       => $m('gmaps_url'),
    'love_story'      => $m('love_story'),
    'galeri'          => $galeri,
    'cover_image'     => $galeri[0] ?? '',
    'musik_url'       => $m('musik_url'),
    'video_url'       => $m('video_url'),
    'rekening'        => $m('rekening'),
    'qris_media_url'  => $m('qris_media_url'),
    'wa_cp'           => $m('wa_cp'),
];
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<?php wp_head(); ?>
</head>
<body class="undangan-body is-locked">
<main class="undangan" id="top">
<?php
get_template_part('template-parts/undangan/cover', null, $undangan);
get_template_part('template-parts/undangan/countdown', null, $undangan);
get_template_part('template-parts/undangan/mempelai', null, $undangan);
get_template_part('template-parts/undangan/acara', null, $undangan);

if ($plus && $undangan['love_story'] !== '') {
    get_template_part('template-parts/undangan/love-story', null, $undangan);
}
if ($plus && $galeri) {
    get_template_part('template-parts/undangan/galeri', null, $undangan);
}
if ($premium && $undangan['video_url'] !== '') {
    get_template_part('template-parts/undangan/video', null, $undangan);
}
if ($plus && ($undangan['rekening'] !== '' || $undangan['qris_media_url'] !== '')) {
    get_template_part('template-parts/undangan/amplop', null, $undangan);
}

get_template_part('template-parts/undangan/rsvp', null, $undangan);
get_template_part('template-parts/undangan/penutup', null, $undangan);
?>
</main>
<?php if ($undangan['musik_url'] !== '') : ?>
<audio id="undangan-audio" src="<?php echo esc_url($undangan['musik_url']); ?>" loop preload="none"></audio>
<button type="button" id="music-toggle" class="music-toggle" aria-label="Putar / jeda musik" hidden><span aria-hidden="true">♪</span></button>
<?php endif; ?>
<?php wp_footer(); ?>
</body>
</html>
