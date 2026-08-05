<?php
/**
 * Section 4 — Rangkaian acara. Tiap kartu (Akad / Resepsi) membawa venue-nya
 * SENDIRI + tombol Google Maps sendiri — di Indonesia akad & resepsi lazim
 * berbeda tempat. Venue akad kosong = kartu akad tampil tanpa blok lokasi.
 * Waktu diasumsikan WIB (T3.7).
 */
if (!defined('ABSPATH')) exit;
$u = $args;

$kartu = [];
if ($u['tanggal_akad'] !== '') {
    $kartu[] = [
        'jenis'   => 'Akad Nikah',
        'tanggal' => $u['tanggal_akad'],
        'waktu'   => $u['waktu_akad'],
        'lokasi'  => $u['lokasi_akad_nama'],
        'alamat'  => $u['lokasi_akad_alamat'],
        'gmaps'   => $u['gmaps_akad_url'],
    ];
}
if ($u['tanggal_resepsi'] !== '') {
    $kartu[] = [
        'jenis'   => 'Resepsi',
        'tanggal' => $u['tanggal_resepsi'],
        'waktu'   => $u['waktu_resepsi'],
        'lokasi'  => $u['lokasi_nama'],
        'alamat'  => $u['lokasi_alamat'],
        'gmaps'   => $u['gmaps_url'],
    ];
}
if (!$kartu) return;
?>
<section class="section acara" id="acara">
    <p class="section-title" data-reveal>Rangkaian Acara</p>

    <?php foreach ($kartu as $i => $k) : ?>
    <div class="acara-card" data-reveal data-delay="<?php echo esc_attr(120 + $i * 100); ?>">
        <span class="acara-diamond" aria-hidden="true"></span>
        <h3 class="acara-jenis"><?php echo esc_html($k['jenis']); ?></h3>
        <p class="acara-tanggal"><?php echo esc_html(harih_format_tanggal($k['tanggal'])); ?></p>
        <?php if ($k['waktu'] !== '') : ?>
            <p class="acara-waktu"><?php echo esc_html($k['waktu']); ?> WIB</p>
        <?php endif; ?>

        <?php if ($k['lokasi'] !== '' || $k['alamat'] !== '') : ?>
            <i class="acara-garis" data-grow="56" aria-hidden="true"></i>
            <?php if ($k['lokasi'] !== '') : ?>
                <p class="lokasi-nama"><?php echo esc_html($k['lokasi']); ?></p>
            <?php endif; ?>
            <?php if ($k['alamat'] !== '') : ?>
                <p class="lokasi-alamat"><?php echo nl2br(esc_html($k['alamat'])); ?></p>
            <?php endif; ?>
            <?php if ($k['gmaps'] !== '') : ?>
                <a class="btn btn-ghost acara-maps" href="<?php echo esc_url($k['gmaps']); ?>" target="_blank" rel="noopener">Buka Google Maps</a>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</section>
