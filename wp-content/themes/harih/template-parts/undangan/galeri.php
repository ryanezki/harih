<?php
/**
 * Section — Galeri (acuan + permintaan owner): bisa DIGESER kanan-kiri di
 * dalam bingkai arch (scroll-snap native — mulus di iOS tanpa library),
 * titik penunjuk tersinkron, dan ketuk untuk memperbesar (lightbox).
 */
if (!defined('ABSPATH')) exit;
$u = $args;
?>
<section class="section galeri" id="galeri">
    <p class="section-title" data-reveal>Galeri</p>
    <div class="galeri-bingkai" data-reveal data-delay="140">
        <div class="galeri-slider" id="galeri-slider">
            <?php foreach ($u['galeri'] as $i => $url) : ?>
            <div class="galeri-slide"><img src="<?php echo esc_url($url); ?>" alt="<?php echo esc_attr('Foto galeri ' . ($i + 1)); ?>" loading="lazy" decoding="async"></div>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="galeri-dots" id="galeri-dots" data-reveal data-delay="200" role="tablist" aria-label="Navigasi galeri"></div>
    <p class="galeri-hint" data-reveal data-delay="240">Geser untuk foto berikutnya &nbsp;·&nbsp; ketuk untuk memperbesar</p>
</section>
