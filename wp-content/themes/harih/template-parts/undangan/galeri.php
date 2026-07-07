<?php
/** Section 6 — Galeri foto (§7.6, paket ≠ hemat). URL sudah di-esc_url di single-undangan.php. */
if (!defined('ABSPATH')) exit;
$u = $args;
?>
<section class="section galeri" id="galeri" data-reveal>
    <h2 class="section-title">Galeri</h2>
    <div class="galeri-grid">
        <?php foreach ($u['galeri'] as $i => $url) : ?>
            <img src="<?php echo esc_url($url); ?>" alt="<?php echo esc_attr('Galeri foto ' . ($i + 1)); ?>" loading="lazy" decoding="async">
        <?php endforeach; ?>
    </div>
</section>
