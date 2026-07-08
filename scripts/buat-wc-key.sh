#!/usr/bin/env bash
# =============================================================================
# Membuat API key WooCommerce (user n8nbot, permission read/write) untuk n8n —
# TANPA menampilkan nilainya di terminal. Hasil ditulis langsung ke vps/.env
# (file gitignored). Yang tampil hanya 7 karakter terakhir key sebagai referensi
# (sama dengan kolom "truncated key" di wp-admin).
#
# Jalankan sendiri dari root repo:   bash scripts/buat-wc-key.sh
# =============================================================================
set -euo pipefail
cd "$(dirname "$0")/.."

ENV_FILE="vps/.env"
[ -f "$ENV_FILE" ] || { echo "ERROR: $ENV_FILE tidak ditemukan (salin dari vps/.env.example dulu)"; exit 1; }

echo "Membuat key di server via SSH…"
KEYS=$(ssh -p 65002 u803921702@147.93.80.20 'bash -s' <<'REMOTE'
cd domains/harih.id/public_html
wp eval '
$user = get_user_by("login", "n8nbot");
if (!$user) { fwrite(STDERR, "user n8nbot tidak ada\n"); exit(1); }
$ck = "ck_" . wc_rand_hash();
$cs = "cs_" . wc_rand_hash();
global $wpdb;
$ok = $wpdb->insert($wpdb->prefix . "woocommerce_api_keys", [
    "user_id"         => (int) $user->ID,
    "description"     => "n8n",
    "permissions"     => "read_write",
    "consumer_key"    => wc_api_hash($ck),
    "consumer_secret" => $cs,
    "truncated_key"   => substr($ck, -7),
]);
if (!$ok) { fwrite(STDERR, "insert gagal\n"); exit(1); }
echo $ck . " " . $cs;
'
REMOTE
)

CK="${KEYS%% *}"
CS="${KEYS##* }"
case "$CK" in ck_*) : ;; *) echo "ERROR: key tidak terbentuk ($KEYS)"; exit 1 ;; esac

# Tulis ke vps/.env tanpa menampilkan nilai (sed -i '' = macOS)
sed -i '' "s|^WC_CK=.*|WC_CK=$CK|" "$ENV_FILE"
sed -i '' "s|^WC_CS=.*|WC_CS=$CS|" "$ENV_FILE"

echo "OK — WC_CK & WC_CS tersimpan di vps/.env"
echo "Referensi (ekor key, sama dgn kolom wp-admin): …${CK#"${CK%???????}"}"
