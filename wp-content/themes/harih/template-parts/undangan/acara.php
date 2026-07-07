<?php
/** Section 4 — Detail akad & resepsi + Google Maps (§7.4). Waktu diasumsikan WIB (T3.7). */
if (!defined('ABSPATH')) exit;
$u = $args;
?>
<section class="section acara" id="acara" data-reveal>
    <h2 class="section-title">Rangkaian Acara</h2>

    <?php if ($u['tanggal_akad'] !== '') : ?>
    <div class="acara-card">
        <h3 class="acara-jenis">Akad Nikah</h3>
        <p class="acara-tanggal"><?php echo esc_html(harih_format_tanggal($u['tanggal_akad'])); ?></p>
        <?php if ($u['waktu_akad'] !== '') : ?>
            <p class="acara-waktu"><?php echo esc_html($u['waktu_akad']); ?> WIB</p>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php if ($u['tanggal_resepsi'] !== '') : ?>
    <div class="acara-card">
        <h3 class="acara-jenis">Resepsi</h3>
        <p class="acara-tanggal"><?php echo esc_html(harih_format_tanggal($u['tanggal_resepsi'])); ?></p>
        <?php if ($u['waktu_resepsi'] !== '') : ?>
            <p class="acara-waktu"><?php echo esc_html($u['waktu_resepsi']); ?> WIB</p>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php if ($u['lokasi_nama'] !== '' || $u['lokasi_alamat'] !== '') : ?>
    <div class="acara-lokasi">
        <?php if ($u['lokasi_nama'] !== '') : ?>
            <p class="lokasi-nama"><?php echo esc_html($u['lokasi_nama']); ?></p>
        <?php endif; ?>
        <?php if ($u['lokasi_alamat'] !== '') : ?>
            <p class="lokasi-alamat"><?php echo nl2br(esc_html($u['lokasi_alamat'])); ?></p>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php if ($u['gmaps_url'] !== '') : ?>
        <a class="btn" href="<?php echo esc_url($u['gmaps_url']); ?>" target="_blank" rel="noopener">Buka Google Maps</a>
    <?php endif; ?>
</section>
