#!/usr/bin/env bash
# =============================================================================
# Setup awal WordPress hariH di Hostinger — TASKS T1.1–T1.6 (bagian CLI).
# Jalankan via SSH DARI DIREKTORI INSTALASI WP (public_html), setelah
# meng-upload wp-content/ dari repo (lihat README bagian Deploy).
#
# Yang TIDAK dicakup script ini (manual di wp-admin / hPanel — lihat TASKS):
#   - hPanel: cron job tiap menit (perintahnya dicetak di akhir script)
#   - LiteSpeed Cache: Drop Query String to/utm_*/ref (T1.4)
#   - FluentSMTP → Brevo (T1.5)
#   - WooCommerce: guest checkout, produk 3 paket, webhook → n8n (T1.16–T1.19)
#   - Hardening VPS n8n/WAHA (T1.7 — server terpisah)
# =============================================================================
set -euo pipefail

echo "== 1. Plugin inti (T1.1) =="
wp plugin install woocommerce litespeed-cache fluent-smtp limit-login-attempts-reloaded --activate
# Plugin Duitku: cek slug terbaru di wp.org atau unduh dari dashboard Duitku, lalu:
# wp plugin install <slug-atau-zip-duitku> --activate

echo "== 2. Theme: parent Astra + child harih (T1.12) =="
wp theme install astra
wp theme activate harih   # pastikan folder themes/harih sudah ter-upload

echo "== 3. User bot n8n — role shop_manager, bukan administrator (T1.2) =="
# shop_manager, BUKAN editor (P1.5): editor cukup untuk create post CPT + upload
# media, tapi TIDAK boleh mengelola order via WC REST — WF-02 gagal saat menset
# order ke `completed`. Tetap jauh di bawah administrator: bocornya App Password
# tidak berarti takeover situs.
wp user create n8nbot bot@harih.id --role=shop_manager || echo "(user n8nbot sudah ada)"
echo "WP_APP_PASSWORD (salin ke credentials n8n):"
wp user application-password create n8nbot n8n --porcelain

echo "== 4. Secret token form (T1.3) =="
SECRET="$(openssl rand -hex 16)"
wp config set FORM_TOKEN_SECRET "$SECRET" --type=constant
echo "FORM_TOKEN_SECRET: $SECRET   <-- salin ke env n8n (nilai HARUS sama)"

echo "== 5. Cron nyata (T1.6) =="
wp config set DISABLE_WP_CRON true --raw --type=constant

echo "== 6. Pengaturan umum (T1.4 + prasyarat theme) =="
wp rewrite structure '/%postname%/' --hard
wp option update blog_public 1
wp option update timezone_string 'Asia/Jakarta'
wp language core install id_ID --activate || true
wp rewrite flush --hard

echo "== 7. Halaman Form Isi Data (T2.6) =="
PAGE_ID="$(wp post list --post_type=page --name=isi-data --field=ID | head -n1)"
if [ -z "$PAGE_ID" ]; then
  PAGE_ID="$(wp post create --post_type=page --post_title='Lengkapi Data Undangan' \
    --post_name=isi-data --post_status=publish --porcelain)"
fi
wp post meta update "$PAGE_ID" _wp_page_template page-isi-data.php
echo "Halaman /isi-data/ siap (ID $PAGE_ID)"

echo
echo "================================================================"
echo "SELESAI. Langkah manual berikutnya:"
echo "1) hPanel → Advanced → Cron Jobs → tiap menit:"
echo "   cd $(pwd) && wp cron event run --due-now >/dev/null 2>&1"
echo "2) wp-admin → LiteSpeed Cache → Cache → Drop Query String:"
echo "   to, utm_source, utm_medium, utm_campaign, ref"
echo "   dan Cache → Excludes → URI — KELIMA halaman bertoken (T2.7 + A6)."
echo "   Tidak perlu lewat wp-admin, satu perintah cukup:"
echo "     wp litespeed-option set cache-exc \"\$(printf '/isi-data/\\n/upsell/\\n/proof/\\n/tamu/\\n/rekap/\\n')\""
echo "     wp litespeed-purge all"
echo "   ⚠️ nocache_headers() di template TIDAK cukup — terukur 2026-08-07:"
echo "     tanpa exclude ini keempat halaman selain /isi-data/ membalas"
echo "     'public, max-age=604800' DAN membalas 200 alih-alih 403 pada token"
echo "     salah, karena LiteSpeed menyajikan dari cache sebelum PHP jalan."
echo "     Akibat lanjutannya: nonce (sah <=24 jam) mati pada HTML yang"
echo "     di-cache berhari-hari — pemesan menekan Simpan/Setujui, tidak"
echo "     terjadi apa-apa, dan ketikannya hilang."
echo "     Diverifikasi otomatis oleh scripts/cek-live.sh."
echo "3) wp-admin → FluentSMTP → sambungkan Brevo (T1.5)"
echo "4) Simpan WP_APP_PASSWORD & FORM_TOKEN_SECRET di env n8n"
echo "5) Setelah WF-02 dibuat di n8n, pasang URL webhooknya (T2.6):"
echo "   wp config set N8N_FORM_WEBHOOK_URL 'https://n8n.harih.id/webhook/…' --type=constant"
echo "================================================================"

# --- U28 (2026-08-08): font woff2 di-cache abadi ---------------------------
# Header ini TIDAK bisa di-commit sebagai patch repo — ia setelan server, dan
# `find . -name .htaccess` di repo mengembalikan nol berkas. Dicatat di sini
# supaya ikut terpasang lagi setelah rebuild hosting.
#
#   ssh -p 65002 u803921702@147.93.80.20
#   cd domains/harih.id/public_html
#   cp -n .htaccess .htaccess.bak-sebelum-u28
#   cat >> .htaccess <<'BLOK'
#   <FilesMatch "\.woff2$">
#       Header set Cache-Control "public, max-age=31536000, immutable"
#   </FilesMatch>
#   BLOK
#
# Verifikasi:
#   curl -sI https://harih.id/wp-content/themes/harih/aset/font/Figtree-latin.woff2 | grep -i cache-control
#   -> harus: public, max-age=31536000, immutable
