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
    <p class="label-atas" data-reveal>Menghitung Hari</p>
    <div class="countdown-grid" id="countdown-grid" data-target="<?php echo esc_attr($u['target']); ?>" data-reveal data-delay="140">
        <div class="cd-col"><span class="cd-num" data-cd="hari">0</span><span class="cd-label">Hari</span></div>
        <div class="cd-col"><span class="cd-num" data-cd="jam">0</span><span class="cd-label">Jam</span></div>
        <div class="cd-col"><span class="cd-num" data-cd="menit">0</span><span class="cd-label">Menit</span></div>
        <div class="cd-col"><span class="cd-num" data-cd="detik">0</span><span class="cd-label">Detik</span></div>
    </div>
    <p class="countdown-done" id="countdown-done" hidden>Acara telah berlangsung — terima kasih atas doa &amp; restu Anda.</p>
    <?php
    /* .ics untuk Apple Calendar / Outlook — data URI, tanpa backend. */
    $ics = '';
    if (preg_match('/^(\d{4}-\d{2}-\d{2})T(\d{2}):(\d{2})/', $u['target'], $mi)) {
        $mulai  = strtotime("{$mi[1]} {$mi[2]}:{$mi[3]}:00") - 7 * 3600;
        $judul  = trim(($u['nama_pria'] ?: 'Pernikahan') . ' & ' . $u['nama_wanita'], ' &');
        $lokasi = trim($u['lokasi_nama'] . ', ' . $u['lokasi_alamat'], ', ');
        $baris_ics = implode("\r\n", [
            'BEGIN:VCALENDAR', 'VERSION:2.0', 'PRODID:-//hariH//ID',
            'BEGIN:VEVENT',
            'UID:' . $u['id'] . '@harih.id',
            'DTSTART:' . gmdate('Ymd\THis\Z', $mulai),
            'DTEND:' . gmdate('Ymd\THis\Z', $mulai + 3 * 3600),
            'SUMMARY:Pernikahan ' . str_replace([',', ';'], ' ', $judul),
            'LOCATION:' . str_replace([',', ';'], '\\,', $lokasi),
            'DESCRIPTION:Undangan: ' . $u['permalink'],
            'END:VEVENT', 'END:VCALENDAR',
        ]);
        $ics = 'data:text/calendar;charset=utf-8,' . rawurlencode($baris_ics);
    }
    ?>
    <?php if ($cal !== '') : ?>
    <div class="cd-cal-baris" data-reveal data-delay="260">
        <a class="btn btn-ghost" href="<?php echo esc_url($cal); ?>" target="_blank" rel="noopener">Google Calendar</a>
        <?php if ($ics !== '') : ?><a class="btn btn-ghost" href="<?php echo $ics; // data URI, sudah rawurlencode ?>" download="pernikahan.ics">Apple / .ics</a><?php endif; ?>
    </div>
    <?php endif; ?>
</section>
