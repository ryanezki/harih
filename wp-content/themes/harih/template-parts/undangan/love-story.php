<?php
/**
 * Section — Kisah Kami (acuan: foto full-bleed dengan gradasi atas-bawah yang
 * melebur ke latar + parallax halus, lalu kutipan italic). Foto memakai
 * galeri[1] bila ada (galeri[0] sudah dipakai hero) — tanpa foto, kutipan
 * tetap tampil sendiri.
 */
if (!defined('ABSPATH')) exit;
$u = $args;
$foto = $u['galeri'][1] ?? ($u['galeri'][0] ?? '');
?>
<section class="section love-story" id="love-story">
    <p class="section-title" data-reveal>Kisah Kami</p>
    <?php if ($foto !== '') : ?>
    <div class="kisah-foto" data-reveal data-delay="120">
        <img src="<?php echo esc_url($foto); ?>" alt="" loading="lazy" decoding="async" data-parallax="44">
    </div>
    <?php endif; ?>
    <div class="prose" data-reveal data-delay="200"><?php echo nl2br(esc_html($u['love_story'])); ?></div>
</section>
