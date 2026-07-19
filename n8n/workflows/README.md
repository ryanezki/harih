# Workflow n8n — JSON Siap-Import

| File | Isi | Task |
|---|---|---|
| `WF-00-error-handler.json` | Error Trigger → alert email + WA ke owner | T2.4 |
| `WF-01-order-intake.json` | Webhook WC → HMAC → fetch order → append-then-verify sheet → komisi → email + WA link form | T2.11–T2.15 |

Alur WF-01: verifikasi signature HMAC + handle ping WC → ambil detail order via WC REST (topic *Action* hanya mengirim `order_id`) → terima status `processing`/`completed` → generate token form (identik `page-isi-data.php`) → append tab `orders` lalu **baca ulang untuk verifikasi idempotency** (baris duplikat dihapus otomatis) → catat komisi 30% bila kupon `RES-` → email Brevo → cek nomor via WAHA `check-exists` → kirim WA → catat `wa_status`.

## Prasyarat (urut, sekali jalan)

1. **Google Sheet** — tambah 2 header kolom di tab `orders` setelah `tgl_acara`:
   `wa_status` (kolom M) dan `exec_id` (kolom N). Dipakai pola append-then-verify (T2.12) dan monitor delivery WA (T2.22).
2. **Env server** — samakan `/opt/harih/.env` dengan `vps/.env` lokal (ada 4 key baru:
   `OWNER_EMAIL`, `OWNER_WA`, `BREVO_SENDER_EMAIL`, `BREVO_SENDER_NAME` — isi nilai asli, jangan placeholder),
   salin `vps/docker-compose.traefik.yml` terbaru ke `/opt/harih/docker-compose.yml`
   (menambah `NODE_FUNCTION_ALLOW_BUILTIN=crypto` — tanpa ini Code node gagal `require('crypto')`), lalu:
   ```bash
   cd /opt/harih && docker compose up -d n8n
   ```
3. **Credentials di UI n8n** (sekali, dipakai semua WF):
   - **`Google SA hariH`** — tipe *Google Service Account API*: isi email SA + private key dari JSON key (T0.8). Centang *Impersonate*? Tidak perlu.
   - **`WC REST hariH`** — tipe *Basic Auth*: username = nilai `WC_CK`, password = nilai `WC_CS`.
   - Brevo & WAHA **tidak** butuh credential UI — dipakai via `$env` di node HTTP.

## Import

1. n8n → **Workflows → ⋯ → Import from File** → pilih `WF-00-error-handler.json`, simpan.
2. Import `WF-01-order-intake.json`. Buka tiap node **Google Sheets** dan **Ambil Detail Order** → pilih credential yang dibuat di atas (referensi credential tidak ikut terbawa saat import) → buka mapping kolom Sheets, klik refresh agar schema terbaca.
3. Di WF-01: **Settings (⚙) → Error Workflow → `WF-00 — Error Handler (hariH)`** (T2.4 — ulangi untuk WF-02 dst. nanti).
4. **Activate** WF-01 → URL produksi webhook: `https://n8n.harih.id/webhook/wc-order`.

## Sambungkan WooCommerce (T1.19)

1. Generate secret: `openssl rand -hex 24` → isi `WC_WEBHOOK_SECRET` di `/opt/harih/.env` **dan** `vps/.env` lokal → `docker compose up -d n8n`.
2. wp-admin → WooCommerce → Settings → Advanced → Webhooks → Add:
   - Status **Active** · Topic **Action** · Action event: `woocommerce_order_status_processing`
   - Delivery URL: `https://n8n.harih.id/webhook/wc-order` · Secret: nilai `WC_WEBHOOK_SECRET` · API version v3

## Uji (urutan disarankan)

1. **Ping**: simpan webhook di WC → eksekusi WF-01 harus berhenti di `Stop — Ping WC`, webhook tetap `active`.
2. **Order sandbox**: checkout Duitku sandbox → cek: 1 baris di tab `orders` (status `MENUNGGU_DATA`, token terisi), email masuk, WA masuk, `wa_status=TERKIRIM`, link form terbuka tanpa 403.
3. **Idempotency** (T4.4): WC → webhook → Deliveries → *Redeliver* pada delivery yang sama → eksekusi kedua berhenti di `Stop — Duplikat`, baris duplikat terhapus, tidak ada email/WA kedua.
4. **Komisi**: order dengan kupon `RES-XXXX` → baris di tab `komisi`, nilai = 30% × line item setelah diskon.
5. **WF-00**: Execute WF-01 manual dengan data rusak (atau matikan sementara env `BREVO_SENDER_EMAIL`) → owner menerima alert email + WA.

## Catatan desain

- Webhook WF-01 mode **Respond Immediately** (T2.13) — WC menganggap respons lambat sebagai delivery gagal.
- Baris duplikat (race dua delivery paralel) dihapus oleh eksekusi yang kalah, agar WF-05 tidak mengirim reminder dobel.
- Email dikirim **sebelum** WA: kanal paling andal didahulukan; kegagalan WA tidak menghentikan workflow (T2.22), hanya tercatat di `wa_status` (`TERKIRIM` / `TIDAK_VALID` / `TIDAK_TERDAFTAR` / `GAGAL_CEK` / `GAGAL_KIRIM`).
- Fungsi normalisasi nomor HP di `Siapkan Data Order` = fungsi bersama T2.14 — **salin identik** ke WF-02/WF-03 saat dibangun.
