<?php
/**
 * Section 2 — Countdown (standar acuan §2.4: angka serif besar dipisah
 * hairline, bukan kotak-kotak; tiap pergantian angka dianimasikan di
 * undangan.js) + tombol simpan ke Google Calendar (tanpa backend).
 */
if (!defined('ABSPATH')) exit;
$u = $args;
if ($u['target'] === '') return;

/* Link Google Calendar: WIB (UTC+7) → UTC. Durasi default 3 jam. */
$cal = '';
if (preg_match('/^(\d{4}-\d{2}-\d{2})T(\d{2}):(\d{2})/', $u['target'], $m)) {
    $mulai = strtotime("{$m[1]} {$m[2]}:{$m[3]}:00") - 7 * 3600;
    $judul = trim(($u['nama_pria'] ?: 'Pernikahan') . ' & ' . $u['nama_wanita'], ' &');
    $lokasi = trim($u['lokasi_nama'] . ', ' . $u['lokasi_alamat'], ', ');
    $cal = 'https://calendar.google.com/calendar/render?' . http_build_query([
        'action'   => 'TEMPLATE',
        'text'     => 'Pernikahan ' . $judul,
        'dates'    => gmdate('Ymd\THis\Z', $mulai) . '/' . gmdate('Ymd\THis\Z', $mulai + 3 * 3600),
        'location' => $lokasi,
        'details'  => 'Undangan: ' . $u['permalink'],
    ]);
}
?>
<section class="section countdown" id="countdown">
    <h2 class="label-atas" data-reveal>Menghitung Hari</h2>
    <div class="countdown-grid" id="countdown-grid" data-target="<?php echo esc_attr($u['target']); ?>" data-reveal data-delay="140">
        <div class="cd-col"><span class="cd-num" data-cd="hari">0</span><span class="cd-label">Hari</span></div>
        <div class="cd-col"><span class="cd-num" data-cd="jam">0</span><span class="cd-label">Jam</span></div>
        <div class="cd-col"><span class="cd-num" data-cd="menit">0</span><span class="cd-label">Menit</span></div>
        <div class="cd-col"><span class="cd-num" data-cd="detik">0</span><span class="cd-label">Detik</span></div>
    </div>
    <p class="countdown-done" id="countdown-done" hidden>Acara telah berlangsung — terima kasih atas doa &amp; restu Anda.</p>
    <?php if ($cal !== '') : ?>
    <div class="cd-cal-baris" data-reveal data-delay="260">
        <a class="btn btn-ghost" href="<?php echo esc_url($cal); ?>" target="_blank" rel="noopener">Catat Tanggalnya</a>
    </div>
    <?php endif; ?>
</section>
