<?php
/** Section 3 — Profil mempelai + orang tua (§7.3). */
if (!defined('ABSPATH')) exit;
$u = $args;

$inisial = static fn(string $nama): string => $nama !== '' ? mb_strtoupper(mb_substr($nama, 0, 1)) : '♥';
?>
<section class="section mempelai" id="mempelai" data-reveal>
    <p class="section-intro">Dengan memohon rahmat Tuhan Yang Maha Esa, dengan penuh sukacita kami mengundang Bapak/Ibu/Saudara/i pada pernikahan kami:</p>

    <div class="mempelai-card">
        <div class="monogram" aria-hidden="true"><?php echo esc_html($inisial($u['nama_pria'])); ?></div>
        <h3 class="mempelai-nama"><?php echo esc_html($u['nama_pria']); ?></h3>
        <?php if ($u['ortu_pria'] !== '') : ?>
            <p class="mempelai-ortu">Putra dari <?php echo esc_html($u['ortu_pria']); ?></p>
        <?php endif; ?>
    </div>

    <div class="mempelai-amp" aria-hidden="true">&amp;</div>

    <div class="mempelai-card">
        <div class="monogram" aria-hidden="true"><?php echo esc_html($inisial($u['nama_wanita'])); ?></div>
        <h3 class="mempelai-nama"><?php echo esc_html($u['nama_wanita']); ?></h3>
        <?php if ($u['ortu_wanita'] !== '') : ?>
            <p class="mempelai-ortu">Putri dari <?php echo esc_html($u['ortu_wanita']); ?></p>
        <?php endif; ?>
    </div>
</section>
