<?php
/**
 * buat-webp.php — bangun berdampingan `.webp` untuk aset gambar tema (U31).
 *
 * KENAPA LOKAL, BUKAN QUIC.cloud (keputusan owner 2026-08-08):
 * layanan optimasi gambar pihak ketiga bekerja dengan MENGUNGGAH isi media
 * library ke server mereka — dan media library kita berisi foto pranikah
 * pelanggan serta gambar QRIS mempelai. Itu menambah pemroses data baru yang
 * wajib masuk tabel Kebijakan Privasi lebih dulu (aturan yang sudah kita
 * tetapkan sendiri untuk OpenRouter). Membangunnya sendiri: nol pemroses baru,
 * nol kuota, kendali penuh — mengikuti pola yang sudah dipakai
 * `buat-font-web.py` (woff2) dan `buat-aset-og.py` (kartu OG).
 *
 * DIJALANKAN DI SERVER, bukan lokal: Mac pengembang tidak punya encoder WebP
 * (nol Pillow, nol cwebp), sementara Hostinger punya GD ber-WebP DAN Imagick.
 * Hasilnya di-rsync balik ke repo supaya ikut version control, persis seperti
 * woff2 hasil buat-font-web.py.
 *
 *   scp scripts/buat-webp.php u803921702@147.93.80.20:~/  (port 65002)
 *   ssh … "php ~/buat-webp.php ~/domains/harih.id/public_html/wp-content/themes/harih/aset"
 *   rsync … :…/aset/ wp-content/themes/harih/aset/   # tarik .webp ke repo
 *
 * ⚠️ `aset/og/*.jpg` TETAP dipertahankan sebagai JPEG dan `og:image` tidak
 * pernah menunjuk .webp — dukungan WebP di pratinjau WhatsApp/Facebook tidak
 * andal, dan kartu OG justru satu-satunya gambar yang HARUS selalu tampil.
 * Berkas .webp untuk og/ tetap dibuat karena ketiganya JUGA dirender sebagai
 * <img> di carousel tema beranda; yang memakainya cuma <picture> di sana.
 */

declare(strict_types=1);

$dir = $argv[1] ?? __DIR__ . '/../wp-content/themes/harih/aset';
if (!is_dir($dir)) {
    fwrite(STDERR, "Folder aset tidak ditemukan: {$dir}\n");
    exit(1);
}

const KUALITAS = 82;   // sama dengan kompresi JPEG di isi-data.js — sengaja

$total_asal = 0;
$total_webp = 0;
$dibuat = 0;
$dilewati = 0;

/** Hanya folder yang gambarnya benar-benar dirender sebagai <img>. */
foreach (['demo', 'og'] as $sub) {
    $folder = rtrim($dir, '/') . '/' . $sub;
    if (!is_dir($folder)) continue;

    foreach (glob($folder . '/*.{jpg,jpeg,png}', GLOB_BRACE) ?: [] as $asal) {
        $webp = preg_replace('/\.(jpe?g|png)$/i', '.webp', $asal);

        // Lewati bila .webp sudah lebih baru daripada sumbernya — skrip ini
        // aman dijalankan berulang.
        if (file_exists($webp) && filemtime($webp) >= filemtime($asal)) {
            $dilewati++;
            continue;
        }

        $info = @getimagesize($asal);
        if (!$info) { fwrite(STDERR, "  ! tidak terbaca: {$asal}\n"); continue; }

        $img = match ($info[2]) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($asal),
            IMAGETYPE_PNG  => @imagecreatefrompng($asal),
            default        => false,
        };
        if (!$img) { fwrite(STDERR, "  ! gagal decode: {$asal}\n"); continue; }

        // PNG bisa punya alpha (mis. QRIS demo) — pertahankan.
        if ($info[2] === IMAGETYPE_PNG) {
            imagepalettetotruecolor($img);
            imagealphablending($img, false);
            imagesavealpha($img, true);
        }

        if (!imagewebp($img, $webp, KUALITAS)) {
            fwrite(STDERR, "  ! gagal encode: {$webp}\n");
            imagedestroy($img);
            continue;
        }
        imagedestroy($img);

        $a = filesize($asal);
        $b = filesize($webp);

        /* Kalau WebP-nya justru lebih besar — terjadi pada PNG kecil dengan
           sedikit warna, mis. QR — buang saja. Menyajikan berkas yang lebih
           besar sambil menyebutnya optimasi adalah kebohongan kecil yang
           mudah dihindari. */
        if ($b >= $a) {
            unlink($webp);
            printf("  = %-34s WebP lebih besar (%d >= %d) — dilewati\n", basename($asal), $b, $a);
            $dilewati++;
            continue;
        }

        $total_asal += $a;
        $total_webp += $b;
        $dibuat++;
        printf("  ✓ %-34s %7d → %7d byte  (-%d%%)\n", basename($asal), $a, $b, (int) round((1 - $b / $a) * 100));
    }
}

printf("\n%d berkas dibuat, %d dilewati.\n", $dibuat, $dilewati);
if ($total_asal > 0) {
    printf("Total: %d → %d byte, hemat %d byte (-%d%%).\n",
        $total_asal, $total_webp, $total_asal - $total_webp,
        (int) round((1 - $total_webp / $total_asal) * 100));
}
