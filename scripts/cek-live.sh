#!/usr/bin/env bash
# =============================================================================
# hariH — Smoke test situs live (QA §13 + QA tambahan TASKS, bagian yang bisa
# diperiksa dari luar via curl). Jalankan dari mesin mana pun:
#   bash scripts/cek-live.sh
# Override target: BASE=https://staging.harih.id N8N=https://n8n.harih.id bash scripts/cek-live.sh
#
# Dipakai: setelah deploy (verifikasi cepat), sebelum launch (T4.7), dan
# kapan pun ada keraguan "situsnya sehat tidak ya". Exit code ≠ 0 bila ada
# check gagal — aman dipakai di cron/CI.
# =============================================================================
set -u

BASE="${BASE:-https://harih.id}"
N8N="${N8N:-https://n8n.harih.id}"
DEMO="${DEMO:-$BASE/u/demo-tema-01/}"

LULUS=0; GAGAL=0; PERINGATAN=0
hijau() { printf '\033[32m✓\033[0m %s\n' "$1"; LULUS=$((LULUS+1)); }
merah() { printf '\033[31m✗\033[0m %s\n' "$1"; GAGAL=$((GAGAL+1)); }
kuning() { printf '\033[33m!\033[0m %s\n' "$1"; PERINGATAN=$((PERINGATAN+1)); }

ambil() { curl -sS -m 20 -o "$1" -D "$2" -w '%{http_code}' "${@:3}" 2>/dev/null; }

BODY=$(mktemp); HDR=$(mktemp)
trap 'rm -f "$BODY" "$HDR"' EXIT

echo "=== Smoke test hariH — $BASE · $N8N ==="
echo

# --- 1. Katalog (front page) ---
kode=$(ambil "$BODY" "$HDR" "$BASE/")
if [ "$kode" = 200 ] && grep -qi 'paket' "$BODY"; then hijau "Katalog 200 + konten paket"; else merah "Katalog: HTTP $kode / konten paket tidak ditemukan"; fi
grep -qi 'og:title' "$BODY" && hijau "Katalog punya Open Graph" || kuning "Katalog tanpa og:title"

# --- 2. Demo undangan ---
kode=$(ambil "$BODY" "$HDR" "$DEMO")
if [ "$kode" = 200 ]; then hijau "Demo undangan 200 ($DEMO)"; else merah "Demo undangan: HTTP $kode"; fi
grep -qi 'og:title' "$BODY" && hijau "Demo punya Open Graph (preview share WA)" || merah "Demo tanpa og:title"
if grep -Eqi 'noindex' "$BODY" || grep -Eqi '^x-robots-tag:.*noindex' "$HDR"; then hijau "Demo ber-noindex (QA T1.10)"; else merah "Demo TIDAK noindex — undangan bisa terindeks Google"; fi

# --- 3. Cache LiteSpeed: ?to= tidak memecah cache (QA T4.5) ---
curl -sS -m 20 -o /dev/null "$DEMO?to=Budi" 2>/dev/null
cache=$(curl -sS -m 20 -o /dev/null -D - "$DEMO?to=Budi" 2>/dev/null | grep -i '^x-litespeed-cache:' | tr -d '\r' | awk '{print $2}')
case "${cache:-}" in
  hit*) hijau "Cache LiteSpeed HIT pada ?to=Budi" ;;
  miss*) kuning "Cache masih MISS pada ?to=Budi (coba ulang; pastikan Drop Query String 'to' terpasang)" ;;
  *) kuning "Header x-litespeed-cache tidak ada (LSCWS belum aktif / bypass)" ;;
esac

# --- 4. Kebocoran REST (QA T1.10) ---
kode=$(ambil "$BODY" "$HDR" "$BASE/wp-json/wp/v2/undangan")
if [ "$kode" = 401 ] || [ "$kode" = 403 ]; then
  hijau "Listing publik /wp/v2/undangan ditolak ($kode)"
elif grep -Eq 'rekening|wa_cp|qris_media_url' "$BODY"; then
  merah "REST MEMBOCORKAN meta undangan (rekening/wa_cp terlihat tanpa auth)!"
else
  kuning "Listing REST balas $kode tanpa meta sensitif — pastikan memang kosong"
fi

# --- 5. Sitemap: bebas undangan (QA T1.10) & tidak membocorkan username (P0.4) ---
kode=$(ambil "$BODY" "$HDR" "$BASE/wp-sitemap.xml")
if [ "$kode" = 200 ]; then
  if grep -q '/u/' "$BODY"; then merah "wp-sitemap.xml memuat URL /u/ (undangan terindeks)"; else hijau "Sitemap bersih dari CPT undangan"; fi
  if grep -q 'wp-sitemap-users' "$BODY"; then merah "Sitemap index memuat provider users (username tersiar)"; else hijau "Sitemap tanpa provider users"; fi
else
  kuning "wp-sitemap.xml: HTTP $kode"
fi
kode=$(curl -sS -m 20 -o "$BODY" -w '%{http_code}' "$BASE/wp-sitemap-users-1.xml" 2>/dev/null)
if [ "$kode" = 404 ]; then
  hijau "wp-sitemap-users-1.xml → 404 (enumerasi username tertutup)"
else
  merah "wp-sitemap-users-1.xml balas $kode — username bot BOCOR ke publik (P0.4)"
fi

# --- 6. Form isi data terkunci token ---
# KELIMA halaman bertoken, bukan hanya /isi-data/. Sampai 2026-08-07 hanya
# /isi-data/ yang diuji di sini, dan keempat sisanya diam-diam membalas 200:
# LiteSpeed menyajikan halaman `wp_die` dari cache sehingga gerbang 403 tidak
# pernah tegak di tingkat HTTP. Cache-Control ikut diperiksa karena itu akar
# masalahnya — halaman ini memuat token order dan nonce yang hanya sah ≤24 jam,
# jadi HTML-nya tidak boleh di-cache sedetik pun (A6).
for hal in isi-data upsell proof tamu rekap; do
  kode=$(ambil "$BODY" "$HDR" "$BASE/$hal/?order=1&key=salah&nc=$RANDOM")
  if [ "$kode" = 403 ]; then hijau "/$hal/ token salah → 403"; else merah "/$hal/ token salah → HTTP $kode (harus 403)"; fi
  if grep -qiE '^cache-control:.*(no-store|no-cache)' "$HDR"; then
    hijau "/$hal/ tidak di-cache (Cache-Control no-store)"
  else
    merah "/$hal/ BOLEH di-cache — $(grep -i '^cache-control:' "$HDR" | tr -d '\r' | head -1). Tambahkan URI-nya ke LiteSpeed → Cache → Excludes"
  fi
done

# --- 7. XML-RPC mati (T1.4) ---
kode=$(curl -sS -m 20 -o /dev/null -w '%{http_code}' -X POST "$BASE/xmlrpc.php" 2>/dev/null)
if [ "$kode" = 403 ] || [ "$kode" = 404 ] || [ "$kode" = 405 ]; then hijau "xmlrpc.php ditolak ($kode)"; else merah "xmlrpc.php balas $kode (harus ditolak)"; fi

# --- 8. Halaman toko lain ---
for p in jadi-reseller kontak syarat-ketentuan kebijakan-privasi kebijakan-refund; do
  kode=$(curl -sS -m 20 -o /dev/null -w '%{http_code}' "$BASE/$p/" 2>/dev/null)
  if [ "$kode" = 200 ]; then hijau "/$p/ 200"; else kuning "/$p/ HTTP $kode (belum publish?)"; fi
done

# --- 9. Webhook n8n aktif ---
kode=$(curl -sS -m 20 -o "$BODY" -w '%{http_code}' -X POST "$N8N/webhook/wc-order" -d 'webhook_id=0' 2>/dev/null)
if [ "$kode" = 200 ]; then hijau "WF-01 aktif (ping wc-order → 200)"; else merah "WF-01 webhook wc-order → HTTP $kode (workflow belum Active?)"; fi
kode=$(curl -sS -m 20 -o "$BODY" -w '%{http_code}' -X POST "$N8N/webhook/form-undangan" -F 'order=1' -F 'key=salah' 2>/dev/null)
if [ "$kode" = 403 ]; then hijau "WF-02 aktif (token salah → 403)"; else merah "WF-02 webhook form-undangan → HTTP $kode (harus 403; 404 = belum Active)"; fi
kode=$(curl -sS -m 20 -o "$BODY" -w '%{http_code}' -X POST "$N8N/webhook/daftar-reseller" -d 'nama=' 2>/dev/null)
if [ "$kode" = 422 ]; then hijau "WF-03 aktif (data kosong → 422)"; else merah "WF-03 webhook daftar-reseller → HTTP $kode (harus 422; 404 = belum Active)"; fi

# --- 10. Port WAHA tidak terekspos publik (QA T1.7) ---
if curl -sS -m 6 -o /dev/null "http://31.97.50.197:3000/" 2>/dev/null; then
  merah "Port 3000 VPS TERBUKA dari publik — WAHA harus localhost saja!"
else
  hijau "Port 3000 VPS tidak terjangkau dari publik"
fi

echo
echo "=== Hasil: $LULUS lulus · $PERINGATAN peringatan · $GAGAL gagal ==="
[ "$GAGAL" -eq 0 ]
