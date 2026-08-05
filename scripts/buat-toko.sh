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

# =============================================================================
# F3.1 · F3.7 · F3.9 — produk CETAK (fisik) + kategori + pengiriman.
# Dijalankan ulang aman: semuanya dicek by slug/SKU lebih dulu.
#
# Konvensi SKU adalah sumber kebenaran jenis produk (dipakai mu-plugin cetak.php
# DAN WF-01 yang hanya melihat payload REST):
#   HARIH-*  digital · CETAK-* paket hybrid · SATUAN-* à la carte · UPG-* upgrade
# =============================================================================

echo "== F3.1. Kategori produk =="
buat_kategori() {
  local slug="$1" nama="$2" ada
  ada="$(wp term list product_cat --slug="$slug" --field=term_id | head -n1)"
  if [ -n "$ada" ]; then echo "  - kategori $slug sudah ada (ID $ada)"; return; fi
  wp term create product_cat "$nama" --slug="$slug" --porcelain > /dev/null
  echo "  - kategori $slug dibuat"
}
buat_kategori digital 'Undangan Digital'
buat_kategori cetak   'Cetak & Hybrid'

# Produk digital lama masuk kategori `digital` — dasar pembatasan kupon RES-.
# `--categories` HANYA menerima ID (slug memicu "Undefined array key id").
TERM_DIGITAL="$(wp term list product_cat --slug=digital --field=term_id | head -n1)"
TERM_CETAK="$(wp term list product_cat --slug=cetak --field=term_id | head -n1)"
for sku in HARIH-HEMAT HARIH-FAVORIT HARIH-PREMIUM; do
  pid="$(wp wc product list --user="$ADMIN" --sku="$sku" --field=id | head -n1)"
  [ -n "$pid" ] && wp wc product update "$pid" --user="$ADMIN" --categories="[{\"id\":$TERM_DIGITAL}]" > /dev/null 2>&1 && echo "  - $sku → kategori digital"
done

echo "== F3.9. Paket hybrid (fisik, butuh pengiriman) =="
# WAJIB non-virtual: `virtual=true` membuat WooCommerce menganggap order tidak
# perlu dikirim, sehingga field alamat & metode pengiriman tidak pernah muncul.
buat_produk_cetak() {
  local sku="$1" nama="$2" harga="$3" berat="$4" singkat="$5" deskripsi="$6"
  local ada
  ada="$(wp wc product list --user="$ADMIN" --sku="$sku" --field=id | head -n1)"
  if [ -n "$ada" ]; then echo "  - $sku sudah ada (ID $ada) — lewati"; return; fi
  local id
  id="$(wp wc product create --user="$ADMIN" --porcelain \
    --name="$nama" --type=simple --sku="$sku" --regular_price="$harga" \
    --virtual=false --sold_individually=true --catalog_visibility=visible \
    --weight="$berat" --categories="[{\"id\":$TERM_CETAK}]" \
    --short_description="$singkat" --description="$deskripsi")"
  echo "  - $sku dibuat (ID $id) — $nama @ Rp $harga"
}

buat_produk_cetak CETAK-HORMAT 'Paket Hormat — Digital + 150 Kartu QR' 1190000 2 \
  'Undangan digital lengkap + 150 kartu undangan fisik ber-QR + 100 stiker segel. Gratis ongkir se-Indonesia.' \
  '<ul><li>Undangan digital lengkap (setara paket Premium)</li><li>150 kartu undangan fisik ber-QR — tamu memindai, undangan digital terbuka</li><li>100 stiker segel amplop</li><li>Art carton 260gsm, laminasi doff, uji pindai per batch</li><li>Proof disetujui dulu, baru dicetak</li><li>Gratis ongkir ke seluruh Indonesia</li></ul><p>Pesanan diterima paling lambat H-21 sebelum acara.</p>'

buat_produk_cetak CETAK-RESEPSI 'Paket Resepsi — Digital + Kartu QR + Souvenir' 2900000 5 \
  'Undangan digital custom + 150 kartu QR + 200 label souvenir + 100 kartu terima kasih + 100 stiker segel.' \
  '<ul><li>Undangan digital custom</li><li>150 kartu undangan fisik ber-QR</li><li>200 label souvenir bernama &amp; bertanggal</li><li>100 kartu terima kasih</li><li>100 stiker segel amplop</li><li>Proof disetujui dulu, baru dicetak</li><li>Gratis ongkir ke seluruh Indonesia</li></ul><p>Pesanan diterima paling lambat H-21 sebelum acara.</p>'

buat_produk_cetak CETAK-GRAND 'Paket Grand — Digital + Set Lengkap Resepsi Besar' 5900000 9 \
  'Untuk resepsi besar berpanitia: 200 kartu QR holographic, 300 label souvenir, 300 kupon, hangtag, label seserahan, 15 ID card panitia.' \
  '<ul><li>Undangan digital custom</li><li>200 kartu undangan ber-QR holographic</li><li>300 label souvenir + 300 kupon souvenir</li><li>150 hangtag + tali</li><li>Set label seserahan</li><li>15 ID card PVC panitia</li><li>Proof disetujui dulu, baru dicetak</li><li>Gratis ongkir ke seluruh Indonesia</li></ul><p>Pesanan diterima paling lambat H-21 sebelum acara.</p>'

echo "== F3.7. Pengiriman: satu metode gratis se-Indonesia =="
# Keputusan terkunci: TIDAK ada zona per wilayah. Ongkir sudah dialokasikan
# Rp 150rb di dalam harga paket; menambah zona hanya menambah gesekan checkout.
# Alamat DASAR toko. Sampai F3 nilainya masih bawaan instalasi (US:CA) dan itu
# tidak pernah terasa selama semua produk virtual — tanpa pengiriman, negara
# basis tidak dipakai. Begitu ada barang fisik, akibatnya fatal: zona kirim
# Indonesia tidak pernah cocok dan checkout menjawab "No shipping options are
# available for this address", bahkan metode pembayaran ikut hilang.
wp option update woocommerce_default_country 'ID:JK' > /dev/null
wp option update woocommerce_store_city 'Jakarta Selatan' > /dev/null
wp option update woocommerce_currency IDR > /dev/null
# Pengunjung dimulai dari alamat basis (Indonesia), bukan geolokasi: toko ini
# hanya mengirim ke Indonesia, dan geolokasi keliru = pembeli melihat "tidak
# ada metode pengiriman" sebelum sempat mengisi alamatnya.
wp option update woocommerce_default_customer_address base > /dev/null

wp option update woocommerce_ship_to_countries specific > /dev/null
wp option update woocommerce_specific_ship_to_countries '["ID"]' --format=json > /dev/null
wp option update woocommerce_default_customer_address geolocation > /dev/null

ZONA_ID="$(wp wc shipping_zone list --user="$ADMIN" --fields=id,name --format=csv 2>/dev/null | awk -F, '$2 ~ /Indonesia/ {print $1; exit}')"
if [ -z "$ZONA_ID" ]; then
  ZONA_ID="$(wp wc shipping_zone create --user="$ADMIN" --name=Indonesia --order=1 --porcelain)"
  wp wc shipping_zone_method create "$ZONA_ID" --user="$ADMIN" --method_id=free_shipping \
    --settings='{"title":"Gratis ongkir ke seluruh Indonesia"}' > /dev/null
  echo "  - zona Indonesia + metode gratis ongkir dibuat (zona $ZONA_ID)"
else
  echo "  - zona Indonesia sudah ada (zona $ZONA_ID)"
fi
# `wp wc shipping_zone_location` hanya punya subcommand `list` — lokasi zona
# harus di-set lewat API kelas WC, bukan CLI.
wp eval "
\$z = new WC_Shipping_Zone($ZONA_ID);
if (!\$z->get_zone_locations()) { \$z->set_locations([['code' => 'ID', 'type' => 'country']]); \$z->save(); }
" > /dev/null
echo "  - lokasi zona: Indonesia (country=ID)"
