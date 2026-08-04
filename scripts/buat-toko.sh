#!/usr/bin/env bash
# =============================================================================
# Setup toko WooCommerce hariH — TASKS T1.16 (produk), sisa T1.17 (guest
# checkout), T1.20 (halaman katalog sebagai front page), prasyarat T1.21
# (halaman legal draft dengan slug final).
#
# Jalankan via SSH DARI DIREKTORI INSTALASI WP (public_html), setelah upload
# wp-content/ terbaru (butuh page-katalog.php + katalog.css di theme harih):
#   bash buat-toko.sh
#
# Aman dijalankan ulang: produk dicek by SKU, halaman dicek by slug.
# =============================================================================
set -euo pipefail

ADMIN="$(wp user list --role=administrator --field=ID | head -n1)"
if [ -z "$ADMIN" ]; then echo "Tidak ada user administrator — buat dulu."; exit 1; fi
echo "== Memakai user admin ID $ADMIN untuk perintah wp wc =="

echo "== 1. Format harga & mata uang (Rp 99.000 gaya id_ID) =="
wp option update woocommerce_currency IDR
wp option update woocommerce_price_thousand_sep '.'
wp option update woocommerce_price_decimal_sep ','
wp option update woocommerce_price_num_decimals 0

echo "== 2. Guest checkout (sisa T1.17) =="
wp option update woocommerce_enable_guest_checkout yes
wp option update woocommerce_enable_checkout_login_reminder no
wp option update woocommerce_enable_signup_and_login_from_checkout no

echo "== 3. Produk 3 paket (T1.16) =="
# PENTING: nama produk WAJIB memuat kata paketnya (Hemat/Favorit/Premium) —
# WF-01 mendeteksi paket dari nama line item; tanpa itu enforcement WF-02
# fallback ke hemat. Virtual non-downloadable (downloadable memicu
# auto-complete yang melompati status processing) + sold individually
# (qty 2 = bayar 2x tapi hanya dapat 1 token).
buat_produk() {
  local sku="$1" nama="$2" harga="$3" singkat="$4" deskripsi="$5"
  local ada
  ada="$(wp wc product list --user="$ADMIN" --sku="$sku" --field=id | head -n1)"
  if [ -n "$ada" ]; then
    echo "  - $sku sudah ada (ID $ada) — lewati"
    return
  fi
  local id
  id="$(wp wc product create --user="$ADMIN" --porcelain \
    --name="$nama" \
    --type=simple \
    --sku="$sku" \
    --regular_price="$harga" \
    --virtual=true \
    --sold_individually=true \
    --catalog_visibility=visible \
    --short_description="$singkat" \
    --description="$deskripsi")"
  echo "  - $sku dibuat (ID $id) — $nama @ Rp $harga"
}

buat_produk HARIH-HEMAT 'Undangan Digital — Paket Hemat' 99000 \
  'Undangan digital esensial: cover, countdown, detail acara + Maps, musik, RSVP & ucapan tamu. Masa aktif sampai H+7.' \
  '<ul><li>Cover, countdown, detail akad &amp; resepsi + tombol Google Maps</li><li>Musik latar instrumental</li><li>RSVP + ucapan tamu</li><li>Nama tamu otomatis di link (?to=Nama)</li><li>3 tema dasar</li><li>Revisi via CS: Rp 25.000 per pengajuan</li><li>Masa aktif sampai H+7</li></ul>'

buat_produk HARIH-FAVORIT 'Undangan Digital — Paket Favorit' 179000 \
  'Paling laris: semua fitur Hemat + galeri 10 foto, kisah cinta, amplop digital (rekening + QRIS), revisi 1x gratis. Masa aktif H+30.' \
  '<ul><li>Semua fitur paket Hemat</li><li>Galeri sampai 10 foto + kisah cinta</li><li>Amplop digital: rekening (tombol salin) + QRIS</li><li>Semua pilihan tema</li><li>Revisi data 1x gratis via CS</li><li>Masa aktif sampai H+30</li></ul>'

buat_produk HARIH-PREMIUM 'Undangan Digital — Paket Premium' 299000 \
  'Terlengkap: semua fitur Favorit + video/live streaming, revisi 3x gratis prioritas. Masa aktif 1 tahun.' \
  '<ul><li>Semua fitur paket Favorit</li><li>Video / live streaming embed</li><li>Akses tema premium (menyusul)</li><li>Revisi data 3x gratis + prioritas CS</li><li>Masa aktif sampai 1 tahun setelah acara</li></ul>'

echo "== 4. Halaman katalog sebagai front page (T1.20) =="
PAGE_ID="$(wp post list --post_type=page --name=beranda --field=ID | head -n1)"
if [ -z "$PAGE_ID" ]; then
  PAGE_ID="$(wp post create --post_type=page --post_title='hariH — Undangan Digital' \
    --post_name=beranda --post_status=publish --porcelain)"
fi
wp post meta update "$PAGE_ID" _wp_page_template page-katalog.php
wp option update show_on_front page
wp option update page_on_front "$PAGE_ID"
echo "  Front page → /beranda/ (ID $PAGE_ID, template page-katalog.php)"

echo "== 4b. Landing Jadi Reseller (T3.9) =="
RSL_ID="$(wp post list --post_type=page --name=jadi-reseller --field=ID | head -n1)"
if [ -z "$RSL_ID" ]; then
  RSL_ID="$(wp post create --post_type=page --post_title='Jadi Reseller hariH' \
    --post_name=jadi-reseller --post_status=publish --porcelain)"
fi
wp post meta update "$RSL_ID" _wp_page_template page-jadi-reseller.php
echo "  /jadi-reseller/ siap (ID $RSL_ID) — form aktif setelah N8N_FORM_WEBHOOK_URL diset & WF-03 Active"

echo "== 5. Halaman legal — draft dengan slug final (T1.21) =="
# Konten diambil owner dari docs/konten-legal/*.md (isi placeholder {{...}}
# dulu) lalu publish. Slug dibuat sekarang supaya link footer katalog stabil.
buat_halaman_draft() {
  local slug="$1" judul="$2"
  local id
  id="$(wp post list --post_type=page --name="$slug" --field=ID --post_status=any | head -n1)"
  if [ -n "$id" ]; then echo "  - /$slug/ sudah ada (ID $id)"; return; fi
  id="$(wp post create --post_type=page --post_title="$judul" --post_name="$slug" \
    --post_status=draft --porcelain)"
  echo "  - /$slug/ dibuat sebagai DRAFT (ID $id) — isi dari docs/konten-legal/$slug.md lalu publish"
}
buat_halaman_draft syarat-ketentuan 'Syarat & Ketentuan'
buat_halaman_draft kebijakan-privasi 'Kebijakan Privasi'
buat_halaman_draft kebijakan-refund 'Kebijakan Refund'
buat_halaman_draft kontak 'Kontak'

wp rewrite flush --hard

echo
echo "== Verifikasi produk =="
wp wc product list --user="$ADMIN" --fields=id,sku,name,price

echo
echo "================================================================"
echo "SELESAI. Langkah berikutnya:"
echo "1) Buka https://harih.id/ — katalog harus tampil dengan 3 paket"
echo "   (tombol beli aktif karena produk sudah ada)."
echo "2) Isi & publish 4 halaman legal dari docs/konten-legal/ (T1.21)."
echo "3) Install plugin Duitku + konfigurasi sandbox (T1.18), lalu uji"
echo "   checkout end-to-end dari HP."
echo "4) Ajukan review merchant production Duitku (T0.11) setelah katalog"
echo "   + halaman legal live."
echo "================================================================"
