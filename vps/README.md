# hariH — Setup VPS (n8n + WAHA)

Satu perintah `docker compose up -d` menjalankan seluruh mesin otomasi. Mencakup TASKS **T1.7** (hardening), **T2.1** (WAHA), **T2.2** (env n8n).

> **Dua varian compose — pilih sesuai kondisi VPS:**
> - `docker-compose.yml` — VPS kosong (stack lengkap termasuk Caddy TLS).
> - `docker-compose.traefik.yml` — VPS yang **sudah punya Traefik + n8n lain** (kasus VPS 31.97.50.197): instance n8n hariH terpisah menumpang Traefik existing, tanpa Caddy. Salin file ini sebagai `/opt/harih/docker-compose.yml` di server. Langkah UFW & Caddy di bawah tidak berlaku untuk varian ini.

## Prasyarat

- VPS Ubuntu/Debian, **RAM ≥ 2 GB** (engine WEBJS memakai Chromium ±1 GB; kalau sempit: set `WAHA_ENGINE=NOWEB` di `.env` atau tambahkan swap)
- Docker + plugin compose (`curl -fsSL https://get.docker.com | sh`)
- **DNS A record** `n8n.harih.id` → IP VPS (tunggu propagasi sebelum lanjut — dibutuhkan Caddy untuk sertifikat TLS)

## Langkah Setup

```bash
# 1. Firewall — hanya SSH + HTTP/HTTPS yang terbuka (T1.7)
sudo ufw allow OpenSSH
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable

# 2. Salin folder vps/ ini ke VPS (scp/git), lalu:
cp .env.example .env
nano .env          # isi SEMUA nilai (lihat komentar per baris)

# 3. Jalankan
docker compose up -d
docker compose logs -f caddy   # tunggu "certificate obtained" untuk n8n.harih.id
```

> **Catatan keamanan:** Docker menerbitkan port melewati UFW — itulah mengapa n8n & WAHA di-bind ke `127.0.0.1` di compose (bukan mengandalkan firewall). Satu-satunya pintu publik adalah Caddy 80/443. Jangan pernah mengubah bind `127.0.0.1:3000` / `127.0.0.1:5678`.

## Aktivasi n8n

1. Buka `https://n8n.harih.id` → buat **akun owner** (email + password kuat).
2. Verifikasi env: `docker compose exec n8n printenv | grep -E 'PAYLOAD|TIMEZONE|PRUNE'`.
3. Lanjut TASKS T2.3–T2.4: masukkan credentials (Google service account, Brevo) di UI n8n, buat **WF-00 Error Workflow**, lalu set sebagai default error workflow.

## Aktivasi WhatsApp (WAHA)

Dashboard WAHA **tidak** dipublikasikan — akses via SSH tunnel:

```bash
ssh -L 3000:127.0.0.1:3000 USER@IP_VPS
# lalu buka di browser laptop:
#   http://localhost:3000/dashboard  → login dengan WAHA_API_KEY
```

1. Start sesi `default` → scan QR dengan **nomor WA bisnis** (T0.5).
2. Pastikan status sesi `WORKING`.
3. Uji kirim dari n8n: HTTP Request `POST http://waha:3000/api/sendText` dengan header `X-Api-Key: {{WAHA_API_KEY}}`, body `{"session":"default","chatId":"628xxx@c.us","text":"tes hariH"}`.
4. Cek versi image ≥ **2026.6.1** (fitur kirim media gratis di Core): `docker compose exec waha printenv WAHA_VERSION` atau lihat dashboard.

## Operasional

- **Pin versi image** setelah semuanya stabil: lihat `docker compose images`, ganti `latest` di compose dengan tag tersebut (mencegah update mendadak mengubah perilaku).
- **Update terkontrol:** `docker compose pull && docker compose up -d` (lakukan sadar, bukan otomatis).
- **Backup (T4.2):** volume `n8n_data` + `waha_sessions` + export workflows (`docker compose exec n8n n8n export:workflow --all --output=/home/node/.n8n/backup-wf.json`) → simpan JSON-nya ke repo git.
- **Monitor:** WF monitoring sesi WAHA (T2.22) + UptimeRobot ke `https://n8n.harih.id/healthz` (T4.3).
