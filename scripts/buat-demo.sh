#!/usr/bin/env bash
# =============================================================================
# hariH — Undangan demo publik per tema (TASKS P0.2, eks-T3.6).
#
# Jalankan via SSH DARI DIREKTORI INSTALASI WP (public_html):
#   bash buat-demo.sh
#
# Aman dijalankan ulang: post dicek by slug (`demo-tema-01` dst.), yang sudah
# ada dilewati. Untuk membuat ulang dari nol: hapus dulu postnya, lalu jalankan.
#
# Kenapa ketiga demo isinya sama persis (selain nama & lokasi): demo dipakai
# customer untuk MEMBANDINGKAN tema. Konten identik membuat perbedaan skin
# terbaca; kalau isinya beda-beda, yang terbandingkan jadi kontennya.
#
# Kenapa semuanya paket `premium`: demo harus memperlihatkan seluruh section
# (love story, galeri, video, amplop) — ini etalase, bukan simulasi paket.
#
# Catatan: karena `cover_image` = foto pertama galeri (single-undangan.php),
# demo bergaleri otomatis memakai cover berfoto. Varian cover TANPA foto —
# ornamen khas tema-02 (bingkai arch) & tema-03 (bingkai emas) — bisa dilihat
# di preview/tema-02.html & preview/tema-03.html, dan muncul sendiri pada
# undangan customer yang tidak mengunggah foto.
# =============================================================================
set -euo pipefail

BASE_URL="$(wp option get siteurl)"
# Foto demo = stok berlisensi komersial, tinggal DI DALAM TEMA (bukan media
# library) supaya ikut version control + rsync dan tidak bisa terhapus tak
# sengaja saat membersihkan media. Provenance & lisensi: docs/aset-lisensi.md.
ASET="$BASE_URL/wp-content/themes/harih/aset/demo"
GALERI="[\"$ASET/harih-cincin-buket.jpg\",\"$ASET/harih-gaun-detail.jpg\",\"$ASET/harih-cincin-sepatu.jpg\"]"
QRIS="https://api.qrserver.com/v1/create-qr-code/?size=480x480&data=$(printf '%s' "$BASE_URL" | sed 's|:|%3A|g; s|/|%2F|g')"
VIDEO="https://www.youtube.com/watch?v=M7lc1UVf-VE"

buat_demo() {
  local slug="$1" tema="$2" judul="$3" pria="$4" wanita="$5" ortu_p="$6" ortu_w="$7" \
        tgl="$8" jam_akad="$9" jam_resepsi="${10}" lokasi="${11}" alamat="${12}" \
        kisah="${13}" rekening="${14}" lokasi_akad="${15:-}" alamat_akad="${16:-}" \
        dresscode="${17:-Sage & Krem}" dresscode_warna="${18:-#3f5c4f, #f4f1e7}"

  local id
  id="$(wp post list --post_type=undangan --name="$slug" --post_status=any --field=ID | head -n1)"
  if [ -n "$id" ]; then
    echo "  - /u/$slug/ sudah ada (ID $id) — dilewati"
    return
  fi

  id="$(wp post create --post_type=undangan --post_title="$judul" --post_name="$slug" \
        --post_status=publish --porcelain)"

  wp post meta update "$id" paket premium           > /dev/null
  wp post meta update "$id" template_id "$tema"     > /dev/null
  wp post meta update "$id" order_id demo           > /dev/null
  wp post meta update "$id" nama_pria "$pria"       > /dev/null
  wp post meta update "$id" nama_wanita "$wanita"   > /dev/null
  wp post meta update "$id" ortu_pria "$ortu_p"     > /dev/null
  wp post meta update "$id" ortu_wanita "$ortu_w"   > /dev/null
  wp post meta update "$id" tanggal_akad "$tgl"     > /dev/null
  wp post meta update "$id" waktu_akad "$jam_akad"  > /dev/null
  wp post meta update "$id" tanggal_resepsi "$tgl"  > /dev/null
  wp post meta update "$id" waktu_resepsi "$jam_resepsi" > /dev/null
  wp post meta update "$id" lokasi_nama "$lokasi"   > /dev/null
  wp post meta update "$id" lokasi_alamat "$alamat" > /dev/null
  if [ -n "$lokasi_akad" ]; then
    wp post meta update "$id" lokasi_akad_nama "$lokasi_akad"     > /dev/null
    wp post meta update "$id" lokasi_akad_alamat "$alamat_akad"   > /dev/null
    wp post meta update "$id" gmaps_akad_url "https://maps.google.com/?q=$(printf '%s' "$lokasi_akad" | tr ' ' '+')" > /dev/null
  fi
  wp post meta update "$id" gmaps_url "https://maps.google.com/?q=$(printf '%s' "$lokasi" | tr ' ' '+')" > /dev/null
  wp post meta update "$id" love_story "$kisah"     > /dev/null
  wp post meta update "$id" galeri "$GALERI"        > /dev/null
  wp post meta update "$id" rekening "$rekening"    > /dev/null
  wp post meta update "$id" qris_media_url "$QRIS"  > /dev/null
  wp post meta update "$id" video_url "$VIDEO"      > /dev/null
  wp post meta update "$id" wa_cp 6281234567890     > /dev/null

  # Konvensi Indonesia (evaluasi 2026-08-06) — memperlihatkan section opsional:
  # urutan anak + IG, turut mengundang, alamat kado, rundown, dress code.
  wp post meta update "$id" anak_ke_pria   "kedua"    > /dev/null
  wp post meta update "$id" anak_ke_wanita "pertama"  > /dev/null
  wp post meta update "$id" ig_pria   "harih.id"      > /dev/null
  wp post meta update "$id" ig_wanita "harih.id"      > /dev/null
  wp post meta update "$id" turut_mengundang "Keluarga Besar H. Wicaksono
Keluarga Besar Maheswara" > /dev/null
  wp post meta update "$id" alamat_kado "$pria & $wanita
Jl. Kenanga No. 5, Kebayoran Baru, Jakarta Selatan 12120
0812-3456-7890" > /dev/null
  wp post meta update "$id" rundown "18.00 Pembukaan
18.30 Penyambutan tamu
19.00 Resepsi & jamuan
21.00 Penutupan" > /dev/null
  wp post meta update "$id" dresscode       "$dresscode"       > /dev/null
  wp post meta update "$id" dresscode_warna "$dresscode_warna" > /dev/null

  echo "  - /u/$slug/ dibuat (ID $id) — $tema · $judul"
}

echo "== Undangan demo per tema (P0.2) =="

buat_demo demo-tema-01 tema-01 'Raka & Sela' \
  'Raka Pratama' 'Sela Ananda' \
  'Bapak Hendra Pratama & Ibu Sari Wulandari' 'Bapak Budi Santoso & Ibu Rina Melati' \
  2026-09-12 08:00 11:00 \
  'Graha Kencana' 'Jl. Melati No. 12, Bandung' \
  'Kami pertama kali bertemu di sebuah kedai kopi kecil di Bandung, dipertemukan oleh teman yang sama. Empat tahun kemudian, di tempat yang sama, Raka mengajukan pertanyaan paling penting dalam hidupnya — dan Sela menjawab iya.' \
  'BCA 1234567890 a.n. Raka Pratama' \
  'Masjid Al-Ukhuwwah' 'Jl. Wastukencana No. 27, Bandung' \
  'Sage & Krem' '#3f5c4f, #f4f1e7'

buat_demo demo-tema-02 tema-02 'Bima & Ayu' \
  'Bima Saputra' 'Ayu Lestari' \
  'Bapak Slamet Saputra & Ibu Endang Puspita' 'Bapak Harjono & Ibu Retno Wulandari' \
  2026-10-17 09:00 13:00 \
  'Pendopo Senja' 'Jl. Cendana No. 8, Yogyakarta' \
  'Berawal dari satu perjalanan sore ke arah pantai selatan, obrolan kami tidak pernah benar-benar selesai sejak hari itu. Tiga tahun kemudian, di bawah langit senja yang sama, kami memutuskan untuk melanjutkannya seumur hidup.' \
  'BNI 0987654321 a.n. Bima Saputra' \
  'Masjid Gedhe Kauman' 'Jl. Kauman, Ngupasan, Yogyakarta' \
  'Terakota & Krem' '#a15436, #faf3ea'

buat_demo demo-tema-03 tema-03 'Damar & Kirana' \
  'Damar Wicaksono' 'Kirana Maheswari' \
  'Bapak Sutrisno Wicaksono & Ibu Lastri Handayani' 'Bapak Agung Maheswara & Ibu Dewi Anggraini' \
  2026-11-21 08:30 19:00 \
  'Balai Kartini' 'Jl. Gatot Subroto No. 37, Jakarta Selatan' \
  'Kami bertemu saat sama-sama bertahan di kantor sampai larut. Dari obrolan tentang langit malam Jakarta yang nyaris tak berbintang, tumbuh kebiasaan pulang bersama — lalu rencana yang jauh lebih panjang dari sekadar perjalanan pulang.' \
  'Mandiri 1122334455 a.n. Damar Wicaksono' \
  'Masjid Agung Al-Azhar' 'Jl. Sisingamangaraja, Kebayoran Baru, Jakarta Selatan' \
  'Navy & Emas' '#131b2e, #c9a45c'

wp litespeed-purge all > /dev/null 2>&1 || true

echo
echo "== Verifikasi =="
wp post list --post_type=undangan --post_status=publish --fields=ID,post_name,post_title
echo
echo "Buka & bandingkan dari HP:"
for t in 01 02 03; do echo "  $BASE_URL/u/demo-tema-$t/"; done
