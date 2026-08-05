<?php
/**
 * Section 3 — Profil mempelai (standar acuan §3: lingkaran inisial ala avatar
 * terlihat seperti aplikasi chat — diganti panel ARCH bergradasi dengan
 * inisial serif italic besar, menggemakan motif arch galeri & hero).
 */
if (!defined('ABSPATH')) exit;
$u = $args;

$inisial = static fn(string $nama): string => $nama !== '' ? mb_strtoupper(mb_substr($nama, 0, 1)) : '♥';
?>
<section class="section mempelai" id="mempelai">
    <p class="section-title" data-reveal>Kedua Mempelai</p>

    <div class="mempelai-card" data-reveal data-delay="120">
        <div class="arch-inisial" aria-hidden="true"><?php echo esc_html($inisial($u['nama_pria'])); ?></div>
        <h3 class="mempelai-nama"><?php echo esc_html($u['nama_pria']); ?></h3>
        <?php if ($u['ortu_pria'] !== '') : ?>
            <p class="mempelai-ortu">Putra dari <?php echo esc_html($u['ortu_pria']); ?></p>
        <?php endif; ?>
    </div>

    <div class="mempelai-amp" aria-hidden="true" data-reveal>&amp;</div>

    <div class="mempelai-card" data-reveal data-delay="120">
        <div class="arch-inisial" aria-hidden="true"><?php echo esc_html($inisial($u['nama_wanita'])); ?></div>
        <h3 class="mempelai-nama"><?php echo esc_html($u['nama_wanita']); ?></h3>
        <?php if ($u['ortu_wanita'] !== '') : ?>
            <p class="mempelai-ortu">Putri dari <?php echo esc_html($u['ortu_wanita']); ?></p>
        <?php endif; ?>
    </div>
</section>
