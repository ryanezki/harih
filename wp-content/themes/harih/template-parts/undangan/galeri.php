<?php
/**
 * Section — Galeri. Dua tata letak, dipilih per undangan lewat meta `galeri_tata`:
 *
 *   slider (bawaan) — digeser kanan-kiri di dalam bingkai arch (scroll-snap
 *                     native, mulus di iOS tanpa library), titik penunjuk
 *                     tersinkron, ketuk untuk memperbesar.
 *   kolase          — grid mozaik, semua foto terlihat sekaligus.
 *
 * Kolase ditambahkan 2026-08-07 (G1.4) sebagai PILIHAN, bukan pengganti: pemilik
 * 8–10 foto ingin semuanya terlihat, pemilik 3 foto bagus lebih pas dengan
 * slider. Undangan tanpa meta ini tetap slider — desain sedang dibekukan, jadi
 * tidak ada tampilan lama yang boleh berubah tanpa diminta.
 *
 * Lightbox dipakai bersama keduanya (`undangan.js` menyeleksi
 * `.galeri-slide img, .galeri-grid img`).
 */
if (!defined('ABSPATH')) exit;
$u      = $args;
$kolase = ($u['galeri_tata'] ?? 'slider') === 'kolase';
?>
<section class="section galeri" id="galeri">
    <p class="section-title" data-reveal>Galeri</p>

    <?php if ($kolase) : ?>
        <div class="galeri-grid" data-reveal data-delay="140">
            <?php foreach ($u['galeri'] as $i => $url) : ?>
            <figure class="galeri-sel"><img src="<?php echo esc_url($url); ?>" alt="<?php echo esc_attr('Foto galeri ' . ($i + 1)); ?>" loading="lazy" decoding="async"></figure>
            <?php endforeach; ?>
        </div>
        <p class="galeri-hint" data-reveal data-delay="200">Ketuk foto untuk memperbesar</p>
    <?php else : ?>
        <div class="galeri-bingkai" data-reveal data-delay="140">
            <div class="galeri-slider" id="galeri-slider">
                <?php foreach ($u['galeri'] as $i => $url) : ?>
                <div class="galeri-slide"><img src="<?php echo esc_url($url); ?>" alt="<?php echo esc_attr('Foto galeri ' . ($i + 1)); ?>" loading="lazy" decoding="async"></div>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="galeri-dots" id="galeri-dots" data-reveal data-delay="200" role="tablist" aria-label="Navigasi galeri"></div>
        <p class="galeri-hint" data-reveal data-delay="240">Geser untuk foto berikutnya &nbsp;·&nbsp; ketuk untuk memperbesar</p>
    <?php endif; ?>
</section>
