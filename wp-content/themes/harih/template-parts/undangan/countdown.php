<?php
/**
 * Section 2 — Countdown (standar acuan §2.4: angka serif besar dipisah
 * hairline, bukan kotak-kotak; tiap pergantian angka dianimasikan di
 * undangan.js) + tombol simpan ke Google Calendar (tanpa backend).
 */
if (!defined('ABSPATH')) exit;
$u = $args;
if ($u['target'] === '') return;

/* U20 — DUA TOMBOL, BUKAN SATU.
 *
 * Sebelumnya hanya ada satu <a> ke calendar.google.com. Pencarian `.ics`,
 * `text/calendar`, dan `BEGIN:VCALENDAR` di seluruh wp-content mengembalikan
 * NOL hasil — padahal komentar undangan.js:234 sudah menyebut baris ini sebagai
 * "tombol Google + .ics", jadi niatnya memang ada dan separuhnya tidak pernah
 * dibuat. Tanpa berkas .ics tidak ada jalur ke Kalender bawaan iOS/Android.
 *
 * .ics dibangun sebagai `data:` URI di PHP: nol endpoint baru, nol permintaan
 * jaringan, dan ikut ter-cache bersama halamannya.
 */
$judul  = trim(($u['nama_pria'] ?: 'Pernikahan') . ' & ' . $u['nama_wanita'], ' &');

/** Satu baris ICS — CRLF wajib, dan koma/titik-koma harus di-escape. */
$harih_ics_esc = static function (string $t): string {
    return str_replace(["\\", "\n", ',', ';'], ['\\\\', '\\n', '\\,', '\\;'], $t);
};

/**
 * Bangun sepasang tautan (Google + .ics) untuk satu acara.
 * $iso = "YYYY-MM-DDTHH:MM…" dalam WIB.
 */
$harih_kalender = static function (string $iso, string $nama_acara, string $lokasi) use ($u, $judul, $harih_ics_esc): array {
    if (!preg_match('/^(\d{4}-\d{2}-\d{2})T(\d{2}):(\d{2})/', $iso, $m)) return [];
    $mulai   = strtotime("{$m[1]} {$m[2]}:{$m[3]}:00") - 7 * 3600;   // WIB → UTC
    $selesai = $mulai + 3 * 3600;
    $teks    = $nama_acara . ' ' . $judul;

    $google = 'https://calendar.google.com/calendar/render?' . http_build_query([
        'action'   => 'TEMPLATE',
        'text'     => $teks,
        'dates'    => gmdate('Ymd\THis\Z', $mulai) . '/' . gmdate('Ymd\THis\Z', $selesai),
        'location' => $lokasi,
        'details'  => 'Undangan: ' . $u['permalink'],
    ]);

    $ics = implode("\r\n", [
        'BEGIN:VCALENDAR', 'VERSION:2.0', 'PRODID:-//hariH//undangan//ID',
        'BEGIN:VEVENT',
        'UID:' . md5($u['permalink'] . $iso . $nama_acara) . '@harih.id',
        'DTSTAMP:' . gmdate('Ymd\THis\Z'),
        'DTSTART:' . gmdate('Ymd\THis\Z', $mulai),
        'DTEND:'   . gmdate('Ymd\THis\Z', $selesai),
        'SUMMARY:'  . $harih_ics_esc($teks),
        'LOCATION:' . $harih_ics_esc($lokasi),
        'DESCRIPTION:' . $harih_ics_esc('Undangan: ' . $u['permalink']),
        'END:VEVENT', 'END:VCALENDAR', '',
    ]);

    return [
        'nama'   => $nama_acara,
        'google' => $google,
        'ics'    => 'data:text/calendar;charset=utf-8;base64,' . base64_encode($ics),
        'berkas' => sanitize_title($teks) . '.ics',
    ];
};

/* Kedua acara masing-masing membawa jam & venue-nya sendiri. Tombol lama hanya
 * memakai target countdown — yaitu resepsi — sehingga tamu yang diundang AKAD
 * tidak pernah punya jalan menyimpannya. */
$harih_acara_kal = [];
$lokasi_resepsi  = trim($u['lokasi_nama'] . ', ' . $u['lokasi_alamat'], ', ');
if (trim((string) ($u['tanggal_akad'] ?? '')) !== '') {
    $jam_akad = preg_match('/^(\d{1,2})[:.](\d{2})/', (string) ($u['waktu_akad'] ?? ''), $ma)
        ? sprintf('%02d:%s', (int) $ma[1], $ma[2]) : '00:00';
    $lokasi_akad = trim(
        (($u['lokasi_akad_nama'] ?? '') !== '' ? $u['lokasi_akad_nama'] : $u['lokasi_nama'])
        . ', ' . (($u['lokasi_akad_alamat'] ?? '') !== '' ? $u['lokasi_akad_alamat'] : $u['lokasi_alamat']),
        ', '
    );
    $k = $harih_kalender($u['tanggal_akad'] . 'T' . $jam_akad, 'Akad Nikah', $lokasi_akad);
    if ($k) $harih_acara_kal[] = $k;
}
$k = $harih_kalender($u['target'], $harih_acara_kal ? 'Resepsi' : 'Pernikahan', $lokasi_resepsi);
if ($k) $harih_acara_kal[] = $k;
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
    <?php if ($harih_acara_kal) : ?>
    <div class="cd-cal-baris" data-reveal data-delay="260">
        <?php foreach ($harih_acara_kal as $harih_k) : ?>
        <div class="cd-cal-acara">
            <?php if (count($harih_acara_kal) > 1) : ?>
                <span class="cd-cal-nama"><?php echo esc_html($harih_k['nama']); ?></span>
            <?php endif; ?>
            <a class="btn btn-ghost" href="<?php echo esc_url($harih_k['google']); ?>" target="_blank" rel="noopener">Google Calendar</a>
            <a class="btn btn-ghost" href="<?php echo esc_attr($harih_k['ics']); ?>" download="<?php echo esc_attr($harih_k['berkas']); ?>">Kalender HP</a>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</section>
