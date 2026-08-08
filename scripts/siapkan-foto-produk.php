<?php
/**
 * siapkan-foto-produk.php — kecilkan & re-encode foto produk untuk web (U22).
 *
 * Keluaran generator gambar (GPT Image 2) berukuran penuh — 1,5–2,7 MB per
 * berkas. Halaman `/harga/` dibuka calon pembeli dari kuota seluler; mengirim
 * 11 MB gambar ke sana membatalkan seluruh kerja U27/U31 yang baru memangkas
 * beranda 66%.
 *
 * Dijalankan DI SERVER karena Mac pengembang tidak punya encoder WebP —
 * alasan yang sama dengan buat-webp.php. Hasilnya di-rsync balik ke repo.
 *
 *   scp -P 65002 img/*.webp scripts/siapkan-foto-produk.php USER@HOST:~/foto/
 *   ssh … "php ~/foto/siapkan-foto-produk.php ~/foto ~/keluar"
 *   rsync … :~/keluar/ wp-content/themes/harih/aset/produk/
 */

declare(strict_types=1);

$masuk  = rtrim($argv[1] ?? '', '/');
$keluar = rtrim($argv[2] ?? '', '/');
if (!is_dir($masuk)) { fwrite(STDERR, "Folder masuk tidak ada: {$masuk}\n"); exit(1); }
if (!is_dir($keluar) && !mkdir($keluar, 0755, true)) { fwrite(STDERR, "Gagal membuat: {$keluar}\n"); exit(1); }

/* Ukuran disesuaikan PERUNTUKANNYA, bukan satu angka untuk semua — itu bedanya
   antara 1,5 MB dan 0,6 MB pada halaman yang sama:
     hero  dirender ≤570 CSS px  → 1400 sudah 2,4x, cukup untuk layar retina
     kartu dirender ≤333 CSS px  → 1000 sudah 3x
   Argumen ketiga menimpanya bila perlu. */
$maks_sisi = (int) ($argv[3] ?? 1400);
const KUALITAS = 80;

$total_a = 0; $total_b = 0;

foreach (glob($masuk . '/*.{webp,jpg,jpeg,png}', GLOB_BRACE) ?: [] as $asal) {
    $nama = pathinfo($asal, PATHINFO_FILENAME);
    $info = @getimagesize($asal);
    if (!$info) { fwrite(STDERR, "  ! tidak terbaca: {$asal}\n"); continue; }

    [$w, $h] = $info;
    $img = match ($info[2]) {
        IMAGETYPE_WEBP => @imagecreatefromwebp($asal),
        IMAGETYPE_JPEG => @imagecreatefromjpeg($asal),
        IMAGETYPE_PNG  => @imagecreatefrompng($asal),
        default        => false,
    };
    if (!$img) { fwrite(STDERR, "  ! gagal decode: {$asal}\n"); continue; }

    $skala = min(1, $maks_sisi / max($w, $h));
    $nw = (int) round($w * $skala);
    $nh = (int) round($h * $skala);

    if ($skala < 1) {
        $baru = imagecreatetruecolor($nw, $nh);
        imagealphablending($baru, false);
        imagesavealpha($baru, true);
        imagecopyresampled($baru, $img, 0, 0, 0, 0, $nw, $nh, $w, $h);
        imagedestroy($img);
        $img = $baru;
    }

    $tujuan = $keluar . '/' . $nama . '.webp';
    if (!imagewebp($img, $tujuan, KUALITAS)) {
        fwrite(STDERR, "  ! gagal encode: {$tujuan}\n");
        imagedestroy($img);
        continue;
    }
    imagedestroy($img);

    $a = filesize($asal); $b = filesize($tujuan);
    $total_a += $a; $total_b += $b;
    printf("  ✓ %-24s %dx%d → %dx%d   %8d → %7d byte  (-%d%%)\n",
        $nama, $w, $h, $nw, $nh, $a, $b, (int) round((1 - $b / $a) * 100));
}

if ($total_a > 0) {
    printf("\nTotal: %d → %d byte, hemat %d byte (-%d%%).\n",
        $total_a, $total_b, $total_a - $total_b, (int) round((1 - $total_b / $total_a) * 100));
}
