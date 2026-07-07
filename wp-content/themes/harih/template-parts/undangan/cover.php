<?php
/** Section 1 — Cover fullscreen (§7.1). Nama tamu diisi client-side dari ?to= (A2). */
if (!defined('ABSPATH')) exit;
$u = $args;
?>
<section class="section cover<?php echo $u['cover_image'] !== '' ? ' has-photo' : ''; ?>" id="cover"<?php
    if ($u['cover_image'] !== '') printf(' style="--cover-image:url(%s)"', esc_url($u['cover_image']));
?>>
    <div class="cover-inner">
        <p class="cover-eyebrow">Undangan Pernikahan</p>
        <h1 class="cover-names">
            <?php echo esc_html($u['nama_pria'] !== '' ? $u['nama_pria'] : 'Mempelai Pria'); ?>
            <span class="amp" aria-hidden="true">&amp;</span>
            <?php echo esc_html($u['nama_wanita'] !== '' ? $u['nama_wanita'] : 'Mempelai Wanita'); ?>
        </h1>
        <?php if ($u['tanggal_resepsi'] !== '') : ?>
            <p class="cover-date"><?php echo esc_html(harih_format_tanggal($u['tanggal_resepsi'])); ?></p>
        <?php endif; ?>
        <div class="ornament" aria-hidden="true"></div>
        <p class="cover-to">Kepada Yth.<br>
            <span class="guest-name">Bapak/Ibu/Saudara/i</span>
        </p>
        <button type="button" class="btn btn-open" id="buka-undangan">Buka Undangan</button>
    </div>
</section>
