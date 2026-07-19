# Workflow n8n — JSON Siap-Import

| File | Isi | Task |
|---|---|---|
| `WF-00-error-handler.json` | Error Trigger → alert email + WA ke owner | T2.4 |
| `WF-01-order-intake.json` | Webhook WC → HMAC → fetch order → append-then-verify sheet → komisi → email + WA link form | T2.11–T2.15 |
| `WF-02-generate-undangan.json` | Form isi data → validasi → upload media → publish undangan → QR → delivery email+WA → order completed | T2.16–T2.21 |
| `WF-07-monitor-waha.json` | Cron 10 menit: sesi WAHA ≠ `WORKING` → alert email owner (max 1×/jam) | T2.22 |

**Alur WF-01:** verifikasi signature HMAC + handle ping WC → ambil detail order via WC REST (topic *Action* hanya mengirim `order_id`) → terima status `processing`/`completed` → generate token form (identik `page-isi-data.php`) → append tab `orders` lalu **baca ulang untuk verifikasi idempotency** (baris duplikat dihapus otomatis) → catat komisi 30% bila kupon `RES-` → email Brevo → cek nomor via WAHA `check-exists` → kirim WA → catat `wa_status`.

**Alur WF-02:** verifikasi token → lookup status di sheet (`MENUNGGU_DATA` lanjut; `DIPROSES` → 409; `SUDAH_JADI` → 200 + link lama; tak ada → 404) → **validasi file server-side** (max 10, mime jpeg/png/webp, ≤ 2 MB — T2.19) → **respond 200 lebih dulu** lalu set `DIPROSES` (T2.17) → upload foto+QRIS ke `wp/v2/media` → publish post `undangan` (paket & tema di-enforce dari **sheet**, bukan form — T2.18; paket hemat: file & field galeri/amplop/video diabaikan) → QR qrserver (gagal ≠ fatal) → email delivery + lampiran QR → WA teks+link (T2.21) → update baris `SUDAH_JADI` + `link_undangan` + `tgl_acara` → order WC `completed`.

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
   - **`Google SA hariH`** — tipe *Google Service Account API*: isi email SA + private key dari JSON key (T0.8).
   - **`WC REST hariH`** — tipe *Basic Auth*: username = nilai `WC_CK`, password = nilai `WC_CS`.
   - **`WP REST hariH`** — tipe *Basic Auth*: username = nilai `WP_APP_USER`, password = nilai `WP_APP_PASSWORD` (Application Password user bot).
   - Brevo & WAHA **tidak** butuh credential UI — dipakai via `$env` di node HTTP.
4. **Nama produk WC harus memuat kata paketnya** (`Hemat`/`Favorit`/`Premium`, T1.16) — WF-01 mendeteksi paket dari nama line item; bila tak terdeteksi, enforcement WF-02 fallback ke **hemat** (tidak pernah upgrade gratis).

## Import

1. n8n → **Workflows → ⋯ → Import from File** → `WF-00-error-handler.json`, simpan.
2. Import `WF-01-order-intake.json`, `WF-02-generate-undangan.json`, `WF-07-monitor-waha.json`. Buka tiap node **Google Sheets** / **HTTP dengan Basic Auth** → pilih credential yang dibuat di atas (referensi credential tidak ikut terbawa saat import) → pada node Sheets, klik refresh mapping kolom agar schema terbaca.
3. Di WF-01 **dan** WF-02: **Settings (⚙) → Error Workflow → `WF-00 — Error Handler (hariH)`** (T2.4).
4. **Activate** WF-01, WF-02, WF-07. URL produksi:
   - WF-01: `https://n8n.harih.id/webhook/wc-order`
   - WF-02: `https://n8n.harih.id/webhook/form-undangan` → set di WP: `wp config set N8N_FORM_WEBHOOK_URL 'https://n8n.harih.id/webhook/form-undangan' --type=constant`
5. CORS webhook WF-02 sudah di-set `https://harih.id` (T2.20) — ubah di node `Webhook Form` bila domain berbeda.

## Sambungkan WooCommerce (T1.19)

1. Generate secret: `openssl rand -hex 24` → isi `WC_WEBHOOK_SECRET` di `/opt/harih/.env` **dan** `vps/.env` lokal → `docker compose up -d n8n`.
2. wp-admin → WooCommerce → Settings → Advanced → Webhooks → Add:
   - Status **Active** · Topic **Action** · Action event: `woocommerce_order_status_processing`
   - Delivery URL: `https://n8n.harih.id/webhook/wc-order` · Secret: nilai `WC_WEBHOOK_SECRET` · API version v3

## Uji (urutan disarankan)

1. **Ping**: simpan webhook di WC → eksekusi WF-01 harus berhenti di `Stop — Ping WC`, webhook tetap `active`.
2. **Order sandbox**: checkout Duitku sandbox → cek: 1 baris di tab `orders` (status `MENUNGGU_DATA`, token terisi), email masuk, WA masuk, `wa_status=TERKIRIM`, link form terbuka tanpa 403.
3. **Idempotency WF-01** (T4.4): WC → webhook → Deliveries → *Redeliver* delivery yang sama → eksekusi kedua berhenti di `Stop — Duplikat`, baris duplikat terhapus, tidak ada email/WA kedua.
4. **Komisi**: order dengan kupon `RES-XXXX` → baris di tab `komisi`, nilai = 30% × line item setelah diskon.
5. **WF-02 happy path**: isi form dari link → respons cepat (< 3 dtk), baris sheet `DIPROSES` lalu `SUDAH_JADI`, post `/u/<slug>` tayang sesuai paket, email delivery + lampiran QR, WA masuk, order WC jadi `completed`.
6. **WF-02 idempotency** (T4.4): submit form 2× beruntun → submit kedua dapat 409 saat masih proses / 200+link lama setelah jadi; tidak ada undangan ganda.
7. **WF-02 abuse** (QA T4): via curl — file ke-11 / > 2 MB / non-gambar → 422; ubah `paket=premium` di form → undangan tetap sesuai paket dibayar; token salah → 403.
8. **WF-00**: paksa error (mis. kosongkan sementara `BREVO_SENDER_EMAIL`) → owner menerima alert email + WA.
9. **WF-07**: stop kontainer WAHA sebentar → dalam ≤ 10 menit owner menerima email "Sesi WhatsApp DOWN" (dan tidak di-spam tiap 10 menit sesudahnya).

## Catatan desain

- Webhook WF-01 mode **Respond Immediately** (T2.13); WF-02 merespons via node Respond **sebelum proses berat** (T2.17) — klien mobile timeout memicu submit ulang.
- Baris duplikat WF-01 (race dua delivery paralel) dihapus oleh eksekusi yang kalah, agar WF-05 tidak mengirim reminder dobel.
- Email dikirim **sebelum** WA: kanal paling andal didahulukan; kegagalan WA tidak menghentikan workflow (T2.22), hanya tercatat di `wa_status` (`TERKIRIM` / `TIDAK_VALID` / `TIDAK_TERDAFTAR` / `GAGAL_CEK` / `GAGAL_KIRIM`).
- Fungsi normalisasi nomor HP di WF-01 `Siapkan Data Order` = fungsi bersama T2.14 — **salin identik** ke WF-03 saat dibangun (WF-02 memakai nomor dari sheet yang sudah dinormalisasi WF-01).
- Whitelist tema per paket ada di WF-02 node `Cek Order & Validasi` (konstanta `TEMA_PAKET`) — samakan dengan `undangan_get_temas()` di `cpt.php` setiap menambah tema.
- Bila WF-02 gagal di tengah (status sheet macet `DIPROSES`): WF-00 sudah memberi alert; pulihkan dengan ubah status baris ke `MENUNGGU_DATA` lalu minta customer submit ulang (masuk runbook T4.9). Jaring pengaman otomatis menyusul di WF rekonsiliasi (T3.12).
