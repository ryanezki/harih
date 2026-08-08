<?php
/**
 * Template Name: Halaman Teks hariH
 *
 * Halaman teks panjang (kontak + 3 halaman legal) dengan konsep desain yang
 * sama seperti homepage. Sebelum 2026-08-06 halaman-halaman ini dirender tema
 * induk Astra — satu-satunya bagian situs yang tampilannya berbeda dari brand,
 * padahal justru ke sinilah pembeli pergi saat mencari kepastian sebelum
 * membayar (syarat, refund, cara menghubungi). Tampilan yang berbeda di titik
 * itu terbaca sebagai halaman tempelan.
 *
 * Isi halaman tetap berasal dari `docs/konten-legal/*.md` lewat
 * `scripts/publish-legal.py` — repo adalah sumber kebenarannya, template ini
 * hanya membungkus.
 */

if (!defined('ABSPATH')) exit;

the_post();

$harih_ada_harga    = (bool) get_page_by_path('harga');
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<?php wp_head(); ?>
</head>
<body class="katalog-body teks-body">

<?php get_template_part('template-parts/toko/nav', null, ['beranda' => false]); ?>

<main class="teks">
    <h1><?php the_title(); ?></h1>
    <article class="teks-isi">
        <?php the_content(); ?>
    </article>
</main>

<section class="cta-penutup">
    <h2>Masih ada yang ingin <em class="aksen-emas-muda">ditanyakan</em>?</h2>
    <p>Balasan CS maksimal 1×24 jam pada hari kerja — pemesanan sendiri berjalan otomatis 24 jam.</p>
    <a class="btn btn-terang" href="https://wa.me/6282251975575" target="_blank" rel="noopener">Chat WhatsApp</a>
</section>

<?php get_template_part('template-parts/toko/kaki', null, null); ?>

<?php wp_footer(); ?>
</body>
</html>
