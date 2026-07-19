#!/usr/bin/env bash
# =============================================================================
# hariH — Backup mingguan (WF-06 / TASKS T4.2)
#
# Dijalankan via cron di HOST VPS (bukan workflow n8n): rsync incremental,
# akses volume Docker (sesi WAHA, data n8n), dan SSH keluar tidak tersedia
# dari dalam kontainer n8n — karena itu WF-06 diimplementasikan sebagai
# script host. Gagal → alert email langsung via Brevo (mandiri dari n8n,
# supaya backup yang gagal justru saat n8n bermasalah tetap terlaporkan).
#
# Pemasangan (sekali):
#   1) SSH key tanpa passphrase di VPS:
#        ssh-keygen -t ed25519 -f /root/.ssh/id_harih -N ''
#      lalu daftarkan isi id_harih.pub di hPanel Hostinger → SSH Access.
#      Uji: ssh -p 65002 -i /root/.ssh/id_harih u803921702@147.93.80.20 'echo ok'
#   2) Salin script ini ke /opt/harih/backup-harih.sh + chmod +x.
#   3) Uji manual penuh: bash /opt/harih/backup-harih.sh
#   4) Cron mingguan — cek zona waktu server dengan `date`:
#        server UTC : 0 19 * * 6  → Minggu 02:00 WIB
#        server WIB : 0 2  * * 0
#      Baris crontab:
#        0 19 * * 6 /opt/harih/backup-harih.sh >> /opt/harih/backups/backup.log 2>&1
#   5) Uji restore minimal 1× (T4.2): gunzip -t + import dump ke DB kosong,
#      tar -tzf arsip WAHA/n8n. Backup yang tak pernah diuji = tidak ada backup.
#
# Catatan restore n8n: arsip n8n-data-*.tar.gz berisi credentials TERENKRIPSI
# dengan N8N_ENCRYPTION_KEY — simpan nilai key itu di tempat terpisah
# (password manager), bukan hanya di VPS yang sama.
# =============================================================================
set -euo pipefail

BASE=/opt/harih/backups
ENV_FILE=/opt/harih/.env
SSH_TUJUAN="u803921702@147.93.80.20"
SSH_OPSI="-p 65002 -i /root/.ssh/id_harih -o BatchMode=yes -o StrictHostKeyChecking=accept-new"
WP_PATH="domains/harih.id/public_html"
RETENSI_HARI=28
TGL=$(date +%F)

# --- Alert email via Brevo bila ada langkah yang gagal ---
alert_gagal() {
  local baris=$1
  # shellcheck disable=SC1090
  source "$ENV_FILE" 2>/dev/null || true
  if [ -n "${BREVO_API_KEY:-}" ] && [ -n "${OWNER_EMAIL:-}" ]; then
    curl -sS -m 30 -X POST https://api.brevo.com/v3/smtp/email \
      -H "api-key: $BREVO_API_KEY" -H 'content-type: application/json' \
      -d "{\"sender\":{\"name\":\"${BREVO_SENDER_NAME:-hariH}\",\"email\":\"${BREVO_SENDER_EMAIL:-noreply@harih.id}\"},\"to\":[{\"email\":\"$OWNER_EMAIL\"}],\"subject\":\"🚨 [hariH] Backup mingguan GAGAL\",\"htmlContent\":\"<p>backup-harih.sh gagal di baris $baris pada $(date '+%F %T %Z').</p><p>Cek log: <code>/opt/harih/backups/backup.log</code></p>\"}" \
      > /dev/null || true
  fi
}
trap 'alert_gagal $LINENO' ERR

mkdir -p "$BASE"/db "$BASE"/uploads "$BASE"/waha "$BASE"/n8n
echo "=== Backup hariH $TGL — mulai $(date '+%T %Z') ==="

# 1) Database WordPress (dump penuh → gzip, verifikasi integritas + ukuran)
ssh $SSH_OPSI "$SSH_TUJUAN" "cd $WP_PATH && wp db export - --single-transaction --quick" \
  | gzip > "$BASE/db/db-$TGL.sql.gz"
gunzip -t "$BASE/db/db-$TGL.sql.gz"
UKURAN=$(stat -c %s "$BASE/db/db-$TGL.sql.gz")
if [ "$UKURAN" -lt 10000 ]; then
  echo "Dump DB mencurigakan kecil ($UKURAN B) — kemungkinan wp-cli error"
  false
fi
echo "DB: $UKURAN B → db/db-$TGL.sql.gz"

# 2) Uploads — rsync incremental mirror (T4.2: tar penuh mingguan membengkak;
#    rsync hanya mentransfer file baru/berubah)
rsync -az --delete -e "ssh $SSH_OPSI" \
  "$SSH_TUJUAN:$WP_PATH/wp-content/uploads/" "$BASE/uploads/"
echo "Uploads: $(du -sh "$BASE/uploads" | cut -f1) (mirror)"

# 3) Sesi WAHA — aset kritis: hilang = scan ulang QR + risiko jeda delivery
VOL_WAHA=$(docker volume ls -q | grep -E 'harih_waha_sessions$' | head -1)
if [ -z "$VOL_WAHA" ]; then echo "Volume sesi WAHA tidak ditemukan"; false; fi
docker run --rm -v "$VOL_WAHA":/data:ro -v "$BASE/waha":/backup alpine \
  tar czf "/backup/waha-sessions-$TGL.tar.gz" -C /data .
echo "WAHA: waha/waha-sessions-$TGL.tar.gz"

# 4) n8n — export semua workflow (JSON, gampang di-diff/di-commit ke git)
#    + arsip volume (DB sqlite: credentials terenkripsi, riwayat eksekusi;
#    binaryData dikecualikan — besar & sudah di-prune 7 hari)
docker exec harih-n8n n8n export:workflow --all --output=/tmp/workflows-export.json > /dev/null
docker cp harih-n8n:/tmp/workflows-export.json "$BASE/n8n/workflows-$TGL.json" > /dev/null
docker exec harih-n8n rm -f /tmp/workflows-export.json
VOL_N8N=$(docker volume ls -q | grep -E 'harih_n8n_data$' | head -1)
if [ -z "$VOL_N8N" ]; then echo "Volume n8n tidak ditemukan"; false; fi
docker run --rm -v "$VOL_N8N":/data:ro -v "$BASE/n8n":/backup alpine \
  tar czf "/backup/n8n-data-$TGL.tar.gz" --exclude='./binaryData' -C /data .
echo "n8n: n8n/workflows-$TGL.json + n8n/n8n-data-$TGL.tar.gz"

# 5) Retensi 4 minggu (uploads = mirror hidup, tidak ikut dihapus)
find "$BASE/db" "$BASE/waha" "$BASE/n8n" -type f -mtime +"$RETENSI_HARI" -delete

echo "=== Selesai $(date '+%T') ==="
