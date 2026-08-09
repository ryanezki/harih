#!/usr/bin/env bash
# =============================================================================
# hariH — demo undangan BERLABEL NAMA MITRA (R4). Alat jualan untuk F0.3.
#
# Jalankan via SSH DARI DIREKTORI INSTALASI WP (public_html):
#   bash demo-mitra.sh "Percetakan Melati"
#   bash demo-mitra.sh "Sekar Wedding Organizer" "https://instagram.com/sekarwo"
#
# Kenapa ada: menjelaskan white-label lewat kalimat jauh lebih lemah daripada
# mengirim SATU LINK yang sudah berlabel nama toko calon mitra. Ini dipakai di
# tengah percakapan — jadi harus selesai dalam hitungan detik, bukan menit.
#
# Aman dijalankan berulang: undangannya dibuat sekali (slug `demo-mitra`), lalu
# panggilan berikutnya hanya MENGGANTI NAMA MITRA-nya. Satu URL tetap, isinya
# menyesuaikan calon mitra yang sedang ditawari.
#
# Isinya menyalin `demo-tema-01` supaya tidak ada konten baru yang harus
# dirawat — alasan yang sama dengan buat-demo.sh: yang dibandingkan calon mitra
# adalah NAMA DI KAKI, bukan ceritanya.
#
# ⚠️ HTML undangan di-cache LiteSpeed 7 hari. Script ini sudah mem-purge, tapi
#    saat memeriksa sendiri buka dengan query pembeda: /u/demo-mitra/?cb=1
# =============================================================================
set -euo pipefail

NAMA="${1:-}"
URL_MITRA="${2:-}"

if [ -z "$NAMA" ]; then
  echo "Pemakaian: bash demo-mitra.sh \"Nama Toko Mitra\" [url-atau-instagram]" >&2
  exit 1
fi

SLUG_MITRA="demo"      # satu slot yang dipakai ulang; namanya yang berganti
SLUG_DEMO="demo-mitra"
SUMBER="demo-tema-01"

BASE_URL="$(wp option get siteurl)"

# --- 1. Daftar mitra (whitelist `mitra_id`) --------------------------------
# Ditulis lewat option, bukan meta: `undangan_get_mitra()` membacanya sebagai
# satu-satunya sumber kebenaran, dan `mitra_id` di luar daftar ini otomatis
# jatuh ke brand hariH.
# Nilai dilewatkan lewat ENV, bukan diinterpolasi ke dalam kode PHP: nama toko
# mitra datang dari tangan owner dan bisa memuat kutip, ampersand, atau apostrof
# ("Toko Bunga 'Melati' & Co") — interpolasi akan pecah persis di nama seperti itu.
HARIH_MITRA_SLUG="$SLUG_MITRA" HARIH_MITRA_NAMA="$NAMA" HARIH_MITRA_URL="$URL_MITRA" \
wp eval '
  update_option("harih_mitra_brand", [
    getenv("HARIH_MITRA_SLUG") => [
      "nama" => getenv("HARIH_MITRA_NAMA"),
      "url"  => getenv("HARIH_MITRA_URL"),
      "wa"   => "",
    ],
  ]);
' > /dev/null
echo "  Mitra terdaftar: $NAMA${URL_MITRA:+ · $URL_MITRA}"

# --- 2. Undangan demo ------------------------------------------------------
id="$(wp post list --post_type=undangan --name="$SLUG_DEMO" --post_status=any --field=ID | head -n1)"

if [ -z "$id" ]; then
  src="$(wp post list --post_type=undangan --name="$SUMBER" --post_status=any --field=ID | head -n1)"
  if [ -z "$src" ]; then
    echo "  ✗ $SUMBER tidak ada — jalankan buat-demo.sh dulu." >&2
    exit 1
  fi

  judul="$(wp post get "$src" --field=post_title)"
  id="$(wp post create --post_type=undangan --post_title="$judul" --post_name="$SLUG_DEMO" \
        --post_status=publish --porcelain)"

  # Salin seluruh meta demo sumber, kecuali yang harus berbeda.
  wp eval "
    \$src = $src; \$dst = $id;
    foreach (get_post_meta(\$src) as \$k => \$v) {
      if (in_array(\$k, ['mitra_id'], true) || strpos(\$k, '_') === 0) continue;
      update_post_meta(\$dst, \$k, maybe_unserialize(\$v[0]));
    }
    update_post_meta(\$dst, 'order_id', 'demo');   // dikecualikan masa aktif & retensi
  " > /dev/null
  echo "  Undangan demo dibuat (ID $id), disalin dari $SUMBER"
else
  echo "  Undangan demo sudah ada (ID $id) — nama mitranya saja yang diperbarui"
fi

wp post meta update "$id" mitra_id "$SLUG_MITRA" > /dev/null

# --- 3. Verifikasi: yang TERSIMPAN, bukan yang dikirim ----------------------
tersimpan="$(wp post meta get "$id" mitra_id 2>/dev/null || true)"
if [ "$tersimpan" != "$SLUG_MITRA" ]; then
  echo "  ✗ mitra_id tidak tersimpan (dapat '$tersimpan') — cek undangan_get_mitra()" >&2
  exit 1
fi
terbaca="$(wp eval "\$b = harih_mitra_brand($id); echo \$b['mitra'] ? \$b['nama'] : 'hariH';")"
if [ "$terbaca" != "$NAMA" ]; then
  echo "  ✗ kaki undangan akan menampilkan '$terbaca', bukan '$NAMA'" >&2
  exit 1
fi
echo "  ✓ kaki undangan akan berbunyi: \"Undangan digital oleh $terbaca\""

wp litespeed-purge all > /dev/null 2>&1 || true

echo
echo "Kirim link ini ke calon mitra:"
echo "  $BASE_URL/u/$SLUG_DEMO/"
echo
echo "Ganti untuk calon berikutnya: bash demo-mitra.sh \"Nama Toko Lain\""
