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
        'catatan' => $u['catatan_lokasi_akad'],
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
        'catatan' => $u['catatan_lokasi'],
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
        <?php if ($u['nuansa_teks']['hijriah']) : ?><p class="tgl-hijriah" data-hijriah="<?php echo esc_attr($k['tanggal']); ?>"></p><?php endif; ?>
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
            <?php
            /* Peta TERSEMAT dengan pola facade — sama seperti live streaming:
               iframe Google baru dimuat saat DIKLIK. Kalau dimuat saat load,
               tiap undangan menyeret ±1 MB skrip peta dan satu permintaan ke
               pihak ketiga sebelum tamu meminta apa pun. Kueri peta dirakit
               dari nama+alamat venue (embed tanpa API key). */
            $peta_q = trim($k['lokasi'] . ($k['alamat'] !== '' ? ', ' . $k['alamat'] : ''));
            ?>
            <?php if ($peta_q !== '') : ?>
                <div class="peta-facade" data-peta="<?php echo esc_attr($peta_q); ?>" role="button" tabindex="0"
                     aria-label="Tampilkan peta <?php echo esc_attr($k['lokasi'] !== '' ? $k['lokasi'] : $k['jenis']); ?>">
                    <span class="peta-pin" aria-hidden="true">
                        <svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="currentColor" stroke-width="1.3">
                            <path d="M12 21s7-6.2 7-11a7 7 0 1 0-14 0c0 4.8 7 11 7 11z"/><circle cx="12" cy="10" r="2.6"/>
                        </svg>
                    </span>
                    <span class="peta-teks">Tampilkan Peta</span>
                </div>
            <?php endif; ?>
            <?php if ($k['gmaps'] !== '') : ?>
                <a class="btn btn-ghost acara-maps" href="<?php echo esc_url($k['gmaps']); ?>" target="_blank" rel="noopener">Petunjuk Arah</a>
            <?php endif; ?>
            <?php if (trim($k['catatan']) !== '') : ?>
                <p class="lokasi-catatan"><?php echo nl2br(esc_html($k['catatan'])); ?></p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>

    <?php
    /* Dress code + swatch warna (evaluasi: "hampir wajib sekarang"). Warna =
       hex dipisah koma, divalidasi ketat di sini. */
    $warna = [];
    foreach (array_filter(array_map('trim', explode(',', $u['dresscode_warna']))) as $w) {
        if (preg_match('/^#?([0-9a-f]{3}|[0-9a-f]{6})$/i', $w, $m)) $warna[] = '#' . $m[1];
        if (count($warna) >= 4) break;
    }
    ?>
    <?php if ($u['dresscode'] !== '' || $warna) : ?>
    <div class="dresscode" data-reveal>
        <p class="label-atas dresscode-label">Dress Code</p>
        <?php if ($u['dresscode'] !== '') : ?><p class="dresscode-teks"><?php echo esc_html($u['dresscode']); ?></p><?php endif; ?>
        <?php if ($warna) : ?>
        <div class="dresscode-swatch" aria-hidden="true">
            <?php foreach ($warna as $w) : ?><i style="background: <?php echo esc_attr($w); ?>"></i><?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php
    /* Susunan acara: "18.00 Pembukaan" per baris → waktu emas · judul. */
    $acara_rundown = [];
    foreach (array_filter(array_map('trim', explode("\n", $u['rundown']))) as $r) {
        if (preg_match('/^(\d{1,2}[.:]\d{2})\s*[-–·]?\s*(.+)$/u', $r, $m)) {
            $acara_rundown[] = [$m[1], $m[2]];
        } else {
            $acara_rundown[] = ['', $r];
        }
    }
    ?>
    <?php if ($acara_rundown) : ?>
    <div class="rundown" data-reveal>
        <p class="label-atas rundown-label">Susunan Acara</p>
        <ul class="rundown-daftar">
            <?php foreach ($acara_rundown as $r) : ?>
            <li><?php if ($r[0] !== '') : ?><span class="rd-waktu"><?php echo esc_html($r[0]); ?></span><?php endif; ?><span class="rd-nama"><?php echo esc_html($r[1]); ?></span></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>
</section>
