<?php
/**
 * Undangan Core — kartu og:image per undangan (TASKS FU.1).
 *
 * MASALAH YANG DISELESAIKAN: sebelumnya `og:image` undangan bergaleri memakai
 * FOTO PERTAMA APA ADANYA. Foto pasangan hampir selalu potret (contoh nyata di
 * produksi: 1073×1600, rasio 0,67) sementara WhatsApp/Facebook memotongnya ke
 * ±1,91:1 — yang tampil hanya pita tengah foto, tanpa nama, tanpa tanggal.
 * Padahal gambar inilah yang muncul SETIAP KALI pemesan menyebarkan undangannya.
 *
 * Solusi: komposit 1200×630 dibangun di server (GD + FreeType tersedia di
 * Hostinger — sudah diverifikasi): foto di-cover-crop + gradasi khas tema +
 * nama mempelai & tanggal dengan font tema yang sebenarnya.
 *
 * INVALIDASI TANPA HOOK: nama berkas memuat hash dari data yang tampil di
 * kartu. Meta berubah → hash berubah → berkas baru dibuat. Tidak ada cache
 * basi yang perlu di-purge, dan tidak ada hook `updated_post_meta` yang bisa
 * terlewat (WF-02 menulis meta lewat REST, jalur yang berbeda dari wp-admin).
 *
 * Kartu dibangun MALAS (saat tag OG dirender) lalu disimpan permanen. Crawler
 * membaca HTML lebih dulu, jadi berkasnya sudah ada saat URL-nya dijemput.
 */

if (!defined('ABSPATH')) exit;

/** Token per tema — disalin dari undangan/{tema}/style.css, jaga tetap sinkron. */
function undangan_og_tema_token(string $tema): array {
    $peta = [
        'tema-01' => [
            'display' => 'CormorantGaramond.ttf', 'label' => 'CormorantGaramond.ttf',
            'ink' => [247, 244, 234], 'gold' => [208, 172, 92], 'scrim' => [28, 42, 35],
            'spasi_label' => 7, 'skala_nama' => 1.0,
        ],
        'tema-02' => [
            'display' => 'Karla.ttf', 'label' => 'Karla.ttf',
            'ink' => [250, 241, 230], 'gold' => [215, 154, 104], 'scrim' => [59, 30, 18],
            'spasi_label' => 6, 'skala_nama' => 0.86,
        ],
        'tema-03' => [
            'display' => 'Prata.ttf', 'label' => 'Manrope.ttf',
            'ink' => [232, 228, 216], 'gold' => [201, 164, 92], 'scrim' => [19, 27, 46],
            'spasi_label' => 8, 'skala_nama' => 0.82,
        ],
    ];
    return $peta[$tema] ?? $peta['tema-01'];
}

/** URL → path lokal (foto galeri selalu di server ini: uploads/ atau tema). */
function undangan_og_path_lokal(string $url): string {
    $home = home_url();
    if (strpos($url, $home) !== 0) return '';
    $rel = ltrim(substr($url, strlen($home)), '/');
    $abs = ABSPATH . $rel;
    return is_readable($abs) ? $abs : '';
}

/** Gambar sumber → canvas 1200×630 dengan cover-crop (potret dipotong dari atas). */
function undangan_og_cover(string $file, int $W, int $H) {
    $info = @getimagesize($file);
    if (!$info) return null;
    $src = match ($info[2]) {
        IMAGETYPE_JPEG => @imagecreatefromjpeg($file),
        IMAGETYPE_PNG  => @imagecreatefrompng($file),
        IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($file) : null,
        default        => null,
    };
    if (!$src) return null;

    $sw = imagesx($src); $sh = imagesy($src);
    $skala = max($W / $sw, $H / $sh);
    $cw = (int) round($W / $skala);
    $ch = (int) round($H / $skala);
    $cx = (int) round(($sw - $cw) / 2);
    // Potret dipotong dari SEPERTIGA atas, bukan tengah: pada foto pernikahan
    // subjek hampir selalu di paruh atas, dan pita tengah biasanya berisi badan.
    $cy = $sh > $sw ? (int) round(($sh - $ch) * 0.28) : (int) round(($sh - $ch) / 2);

    $dst = imagecreatetruecolor($W, $H);
    imagecopyresampled($dst, $src, 0, 0, $cx, $cy, $W, $H, $cw, $ch);
    imagedestroy($src);
    return $dst;
}

/**
 * Teks dengan bayangan lembut. Gradasi saja tidak cukup: foto pernikahan
 * sering punya area putih terang (gaun, buket) tepat di belakang nama, dan
 * krem-di-atas-putih hilang total di thumbnail WhatsApp. Bayangan membuat
 * keterbacaan tidak bergantung pada foto pemesan.
 */
function undangan_og_teks($img, float $size, int $x, int $y, $warna, string $font, string $teks): void {
    $bayang = imagecolorallocatealpha($img, 0, 0, 0, 88);
    imagettftext($img, $size, 0, $x + 1, $y + 2, $bayang, $font, $teks);
    imagettftext($img, $size, 0, $x, $y, $warna, $font, $teks);
    imagecolordeallocate($img, $bayang);
}

/** Teks ber-letter-spacing (GD tidak punya tracking bawaan). */
function undangan_og_teks_spasi($img, float $size, int $x, int $y, $warna, string $font, string $teks, float $spasi): int {
    $huruf = preg_split('//u', $teks, -1, PREG_SPLIT_NO_EMPTY);
    foreach ($huruf as $h) {
        undangan_og_teks($img, $size, $x, $y, $warna, $font, $h);
        $b = imagettfbbox($size, 0, $font, $h);
        $x += ($b[2] - $b[0]) + $spasi;
    }
    return $x;
}

/** Lebar teks (px) pada ukuran font tertentu. */
function undangan_og_lebar(float $size, string $font, string $teks): int {
    $b = imagettfbbox($size, 0, $font, $teks);
    return $b[2] - $b[0];
}

/** Ukuran font terbesar yang masih muat di lebar maksimum. */
function undangan_og_fit(string $font, string $teks, int $maks_lebar, float $awal, float $min = 34): float {
    $s = $awal;
    while ($s > $min && undangan_og_lebar($s, $font, $teks) > $maks_lebar) $s -= 2;
    return $s;
}

/**
 * URL kartu og:image undangan. '' bila gagal — pemanggil jatuh ke kartu tema.
 */
function undangan_og_kartu(int $id): string {
    if (!function_exists('imagettftext') || !function_exists('imagecreatetruecolor')) return '';

    $m = static fn(string $k): string => trim((string) get_post_meta($id, $k, true));

    $pria   = $m('nama_pria');
    $wanita = $m('nama_wanita');
    if ($pria === '' || $wanita === '') return '';

    $tema  = function_exists('harih_tema_aktif') ? harih_tema_aktif($id) : 'tema-01';
    $tgl   = $m('tanggal_resepsi') ?: $m('tanggal_akad');
    $galeri = json_decode($m('galeri') ?: '[]', true);
    $foto   = (is_array($galeri) && !empty($galeri[0]) && is_string($galeri[0])) ? $galeri[0] : '';

    // Hash = seluruh isi yang tampil di kartu → berubah isinya, berubah berkasnya.
    $sidik = substr(md5(implode('|', [$pria, $wanita, $tgl, $foto, $tema, '3'])), 0, 10);

    $up  = wp_upload_dir();
    $dir = trailingslashit($up['basedir']) . 'harih-og';
    $file = "{$dir}/{$id}-{$sidik}.jpg";
    $url  = trailingslashit($up['baseurl']) . "harih-og/{$id}-{$sidik}.jpg";

    if (file_exists($file)) return $url;
    if (!wp_mkdir_p($dir)) return '';

    $W = 1200; $H = 630;
    $t = undangan_og_tema_token($tema);
    $fdir = get_stylesheet_directory() . '/aset/font/';
    $f_display = $fdir . $t['display'];
    $f_label   = $fdir . $t['label'];
    if (!is_readable($f_display) || !is_readable($f_label)) return '';

    // --- kanvas: foto cover-crop, atau blok warna tema bila tak ada foto ---
    $img = $foto !== '' ? undangan_og_cover(undangan_og_path_lokal($foto), $W, $H) : null;
    $berfoto = (bool) $img;
    if (!$img) {
        $img = imagecreatetruecolor($W, $H);
        imagefilledrectangle($img, 0, 0, $W, $H, imagecolorallocate($img, ...$t['scrim']));
    }
    // Tanpa foto, blok teks naik ke tengah — kalau tidak, separuh atas kartu
    // kosong melompong. Ini jalur paket Hemat, tier dengan volume tertinggi.
    $dy = $berfoto ? 0 : -78;

    // --- gradasi: transparan di atas → pekat di bawah, agar teks selalu terbaca ---
    [$sr, $sg, $sb] = $t['scrim'];
    for ($y = 0; $y < $H; $y++) {
        $p = $y / $H;
        // 0,34 di puncak (foto masih terbaca sebagai foto) → 0,93 di dasar.
        $op = 0.34 + 0.55 * pow($p, 1.25);
        // Pelat tambahan tepat di balik blok nama (y 240–560) — di situlah foto
        // paling sering terang dan teks paling mudah hilang.
        if ($y > 240) $op += 0.16 * min(1.0, ($y - 240) / 220);
        $alpha = (int) round((1 - min(0.93, $op)) * 127);
        $c = imagecolorallocatealpha($img, $sr, $sg, $sb, max(0, min(127, $alpha)));
        imagefilledrectangle($img, 0, $y, $W, $y, $c);
        imagecolordeallocate($img, $c);
    }

    $ink  = imagecolorallocate($img, ...$t['ink']);
    $gold = imagecolorallocate($img, ...$t['gold']);

    if (!$berfoto) {
        $bingkai = imagecolorallocatealpha($img, ...array_merge($t['gold'], [88]));
        imagerectangle($img, 34, 34, $W - 35, $H - 35, $bingkai);
    }

    // --- eyebrow ---
    $eyebrow = 'UNDANGAN PERNIKAHAN';
    $lebar_eb = undangan_og_lebar(19, $f_label, $eyebrow) + $t['spasi_label'] * (mb_strlen($eyebrow) - 1);
    undangan_og_teks_spasi($img, 19, (int) (($W - $lebar_eb) / 2), 300 + $dy, $gold, $f_label, $eyebrow, $t['spasi_label']);

    // --- nama mempelai: dua baris, ukuran menyesuaikan yang terpanjang ---
    $maks = $W - 150;
    $size = min(
        undangan_og_fit($f_display, $pria, $maks, 78 * $t['skala_nama']),
        undangan_og_fit($f_display, $wanita, $maks, 78 * $t['skala_nama'])
    );
    undangan_og_teks($img, $size, (int) (($W - undangan_og_lebar($size, $f_display, $pria)) / 2), 386 + $dy, $ink, $f_display, $pria);
    $amp = '&';
    undangan_og_teks($img, $size * 0.52, (int) (($W - undangan_og_lebar($size * 0.52, $f_display, $amp)) / 2), 434 + $dy, $gold, $f_display, $amp);
    undangan_og_teks($img, $size, (int) (($W - undangan_og_lebar($size, $f_display, $wanita)) / 2), 498 + $dy, $ink, $f_display, $wanita);

    // --- garis rambut + tanggal ---
    imagefilledrectangle($img, (int) ($W / 2 - 42), 528 + $dy, (int) ($W / 2 + 42), 528 + $dy, $gold);
    if ($tgl !== '') {
        $teks_tgl = function_exists('harih_format_tanggal') ? harih_format_tanggal($tgl) : $tgl;
        $teks_tgl = mb_strtoupper($teks_tgl);
        $lebar_tgl = undangan_og_lebar(17, $f_label, $teks_tgl) + 5 * (mb_strlen($teks_tgl) - 1);
        undangan_og_teks_spasi($img, 17, (int) (($W - $lebar_tgl) / 2), 570 + $dy, $ink, $f_label, $teks_tgl, 5);
    }

    // --- wordmark kecil ---
    undangan_og_teks($img, 16, $W - 92, 54, $gold, $f_label, 'hariH');

    $ok = imagejpeg($img, $file, 86);
    imagedestroy($img);
    if (!$ok) return '';

    // Kartu versi lama undangan ini dibuang — tanpa ini tiap revisi data
    // meninggalkan satu berkas yatim selamanya.
    foreach ((array) glob("{$dir}/{$id}-*.jpg") as $lama) {
        if ($lama !== $file) @unlink($lama);
    }

    @chmod($file, 0644);
    return $url;
}

/**
 * Undangan dihapus → kartunya ikut dihapus.
 *
 * Pembersihan di atas hanya membuang versi LAMA dari undangan yang masih ada.
 * Saat undangan itu sendiri dihapus, kartunya tertinggal selamanya — ketahuan
 * 2026-08-07 saat membersihkan undangan uji: postnya hilang, `117-*.jpg` masih
 * ada di uploads.
 *
 * Bukan sekadar sampah disk. Kartu itu memuat NAMA MEMPELAI dan potongan foto
 * mereka, dan tetap bisa diakses publik lewat URL-nya setelah undangannya
 * dihapus — persis yang tidak boleh terjadi pada undangan yang diarsipkan atau
 * dihapus atas permintaan pelanggan.
 *
 * `before_delete_post`, bukan `trashed_post`: undangan di trash masih bisa
 * dipulihkan, jadi kartunya belum boleh hilang.
 */
add_action('before_delete_post', function ($post_id) {
    if (get_post_type($post_id) !== 'undangan') return;

    $up = wp_get_upload_dir();
    if (!empty($up['error'])) return;

    $dir = trailingslashit($up['basedir']) . 'harih-og';
    foreach ((array) glob("{$dir}/" . (int) $post_id . "-*.jpg") as $berkas) {
        @unlink($berkas);
    }
});
