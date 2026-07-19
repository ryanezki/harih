# Workflow n8n — JSON Siap-Import

| File | Isi | Task |
|---|---|---|
| `WF-00-error-handler.json` | Error Trigger → alert email + WA ke owner | T2.4 |
| `WF-01-order-intake.json` | Webhook WC → HMAC → fetch order → append-then-verify sheet → komisi → email + WA link form | T2.11–T2.15 |
| `WF-02-generate-undangan.json` | Form isi data → validasi → upload media → publish undangan → QR → delivery email+WA → order completed | T2.16–T2.21 |
| `WF-03-onboarding-reseller.json` | Daftar reseller (PENDING) → approval owner via link HMAC → kupon WC → welcome kit WA | T3.9–T3.10 |
| `WF-04-rekap-komisi.json` | Cron Senin 09:00 WIB: rekap UNPAID per reseller via WA + total ke owner | T3.11 |
| `WF-05-reminder-harian.json` | Cron harian 08:00 WIB: nudge belum-isi-data (24–48 jam), reminder H-3, ucapan H+1 | T4.1 |
| `WF-07-monitor-waha.json` | Cron 10 menit: sesi WAHA ≠ `WORKING` → alert email owner (max 1×/jam) | T2.22 |
| `WF-08-rekonsiliasi-order.json` | Cron 15 menit: order WC yang tak ada di sheet → kirim ulang ke WF-01 (loopback ber-HMAC) + cron harian cek status webhook WC | T3.12 |

**WF-06 (backup mingguan) bukan workflow n8n** — diimplementasikan sebagai script host `vps/backup-harih.sh` (rsync incremental, arsip volume WAHA/n8n, export workflow JSON, retensi 4 minggu, alert Brevo mandiri). Alasan: rsync/akses volume Docker/SSH keluar tidak tersedia dari dalam kontainer n8n. Cara pasang ada di header script.

**Alur WF-01:** verifikasi signature HMAC + handle ping WC → ambil detail order via WC REST (topic *Action* hanya mengirim `order_id`) → terima status `processing`/`completed` → generate token form (identik `page-isi-data.php`) → append tab `orders` lalu **baca ulang untuk verifikasi idempotency** (baris duplikat dihapus otomatis) → catat komisi 30% bila kupon `RES-` → email Brevo → cek nomor via WAHA `check-exists` → kirim WA → catat `wa_status`.

**Alur WF-02:** verifikasi token → lookup status di sheet (`MENUNGGU_DATA` lanjut; `DIPROSES` → 409; `SUDAH_JADI` → 200 + link lama; tak ada → 404) → **validasi file server-side** (max 10, mime jpeg/png/webp, ≤ 2 MB — T2.19) → **respond 200 lebih dulu** lalu set `DIPROSES` (T2.17) → upload foto+QRIS ke `wp/v2/media` → publish post `undangan` (paket & tema di-enforce dari **sheet**, bukan form — T2.18; paket hemat: file & field galeri/amplop/video diabaikan) → QR qrserver (gagal ≠ fatal) → email delivery + lampiran QR → WA teks+link (T2.21) → update baris `SUDAH_JADI` + `link_undangan` + `tgl_acara` + `mempelai` → order WC `completed`.

**Alur WF-03 (dua webhook dalam satu workflow):**
- `POST /webhook/daftar-reseller` — honeypot `website` (bot dapat sukses palsu) → rate limit 5/jam/IP → normalisasi & `check-exists` nomor WA (welcome kit hanya via WA) → idempotency by nomor WA → generate kode `RES-XXXX` unik → append `resellers` status **PENDING** → notifikasi owner (WA + email) berisi **link approval ber-HMAC**. Kupon TIDAK dibuat di tahap ini (T3.10).
- `GET /webhook/approve-reseller?kode=…&key=…` — verifikasi HMAC → status masih PENDING (klik dua kali idempoten) → buat kupon WC (`percent 10`, `individual_use`, `usage_limit_per_user: 1`) → status `AKTIF` → welcome kit 6a + caption promosi 6b via WA.

**Alur WF-08 (dua cron dalam satu workflow):**
- **Tiap 15 menit** — ambil order WC 7 hari terakhir (status `processing`/`completed`) → banding dengan tab `orders` → yang tak ada di sheet & berumur > 10 menit di-POST ulang ke `http://localhost:5678/webhook/wc-order` dengan payload + signature HMAC persis seperti webhook WC asli → **seluruh logika intake tetap satu pintu di WF-01** (idempotency, komisi, email/WA — tidak ada duplikasi). Setiap temuan = insiden → owner di-alert WA+email; nihil temuan = senyap.
- **Harian 07:30 WIB** — `GET /wc/v3/webhooks`: webhook `…/webhook/wc-order` hilang atau berstatus bukan `active` → alert owner berisi langkah re-enable.

**Kontrak form landing "Jadi Reseller"** (implementasi: `page-jadi-reseller.php` + `reseller.js` di theme — T3.9): POST multipart/urlencoded ke `/webhook/daftar-reseller` dengan field `nama`, `wa`, `bank`, `norek`, plus input tersembunyi `website` yang HARUS kosong (honeypot). Respons JSON `{ok, message}` — tampilkan `message` apa adanya; 200 = sukses/duplikat, 422 = data kurang / bukan nomor WA, 429 = rate limit.

## Prasyarat (urut, sekali jalan)

1. **Google Sheet** — tambah header kolom baru:
   - tab `orders`, setelah `tgl_acara`: `wa_status` (M), `exec_id` (N), `mempelai` (O) — append-then-verify T2.12, monitor delivery T2.22, personalisasi reminder WF-05.
   - tab `resellers`, setelah `tgl_daftar`: `status` (G) — nilai `PENDING`/`AKTIF` (approval T3.10).
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
2. Import WF-01, WF-02, WF-03, WF-04, WF-05, WF-07, WF-08. Buka tiap node **Google Sheets** / **HTTP dengan Basic Auth** → pilih credential yang dibuat di atas (referensi credential tidak ikut terbawa saat import) → pada node Sheets, klik refresh mapping kolom agar schema terbaca.
3. Di **semua** WF selain WF-00: **Settings (⚙) → Error Workflow → `WF-00 — Error Handler (hariH)`** (T2.4).
4. **Activate** semuanya. URL produksi:
   - WF-01: `https://n8n.harih.id/webhook/wc-order`
   - WF-02: `https://n8n.harih.id/webhook/form-undangan` → set di WP: `wp config set N8N_FORM_WEBHOOK_URL 'https://n8n.harih.id/webhook/form-undangan' --type=constant`
   - WF-03: `https://n8n.harih.id/webhook/daftar-reseller` (form landing) + `…/webhook/approve-reseller` (hanya diklik owner)
5. CORS webhook WF-02 & WF-03 sudah di-set `https://harih.id` (T2.20) — ubah di node webhook bila domain berbeda.
6. **WF-06**: salin `vps/backup-harih.sh` ke `/opt/harih/`, ikuti langkah pemasangan di header script (SSH key → uji manual → cron → uji restore).

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
5. **WF-02 happy path**: isi form dari link → respons cepat (< 3 dtk), baris sheet `DIPROSES` lalu `SUDAH_JADI` (+ kolom `mempelai` terisi), post `/u/<slug>` tayang sesuai paket, email delivery + lampiran QR, WA masuk, order WC jadi `completed`.
6. **WF-02 idempotency** (T4.4): submit form 2× beruntun → submit kedua dapat 409 saat masih proses / 200+link lama setelah jadi; tidak ada undangan ganda.
7. **WF-02 abuse** (QA T4): via curl — file ke-11 / > 2 MB / non-gambar → 422; ubah `paket=premium` di form → undangan tetap sesuai paket dibayar; token salah → 403.
8. **WF-03**: daftar via curl (`nama`, `wa`, `bank`, `norek`) → baris PENDING + owner terima link approval; klik link → kupon muncul di WC, status `AKTIF`, welcome kit + caption masuk WA; klik link **kedua kali** → "sudah aktif", tidak ada kupon ganda; daftar ulang nomor sama → "sudah terdaftar"; isi field `website` → sukses palsu tanpa baris baru; cek kupon di WC: 10%, individual use, limit 1×/user.
9. **WF-04**: jalankan manual (Execute) dengan baris UNPAID di tab `komisi` → reseller & owner terima rekap; tanpa UNPAID → owner tetap terima "tidak ada komisi minggu ini".
10. **WF-05**: siapkan baris uji (MENUNGGU_DATA umur 24–48 jam / `tgl_acara` = H+3 / kemarin) → Execute manual → pesan sesuai template; baris di luar jendela tidak dikirimi.
11. **WF-00**: paksa error (mis. kosongkan sementara `BREVO_SENDER_EMAIL`) → owner menerima alert email + WA.
12. **WF-07**: stop kontainer WAHA sebentar → dalam ≤ 10 menit owner menerima email "Sesi WhatsApp DOWN" (dan tidak di-spam tiap 10 menit sesudahnya).
13. **WF-06**: jalankan `bash /opt/harih/backup-harih.sh` → 4 artefak muncul di `/opt/harih/backups/`; uji restore dump + `tar -tzf` arsip.
14. **WF-08** (= QA "matikan n8n 10 menit"): nonaktifkan webhook WC di wp-admin → buat order sandbox → aktifkan lagi webhook → dalam ≤ 15 menit order terproses via rekonsiliasi (baris sheet + email/WA masuk) dan owner menerima alert "order tertinggal"; keesokan 07:30 owner menerima alert webhook non-aktif bila lupa diaktifkan.

## Catatan desain

- Webhook WF-01 mode **Respond Immediately** (T2.13); WF-02 merespons via node Respond **sebelum proses berat** (T2.17) — klien mobile timeout memicu submit ulang.
- Baris duplikat WF-01 (race dua delivery paralel) dihapus oleh eksekusi yang kalah, agar WF-05 tidak mengirim reminder dobel.
- Email dikirim **sebelum** WA: kanal paling andal didahulukan; kegagalan WA tidak menghentikan workflow (T2.22), hanya tercatat di `wa_status` (`TERKIRIM` / `TIDAK_VALID` / `TIDAK_TERDAFTAR` / `GAGAL_CEK` / `GAGAL_KIRIM`).
- Fungsi normalisasi nomor HP dipakai identik di WF-01 (`Siapkan Data Order`) dan WF-03 (`Validasi Pendaftaran`) — T2.14 selesai; WF-02/04/05 memakai nomor dari sheet yang sudah dinormalisasi.
- Whitelist tema per paket ada di WF-02 node `Cek Order & Validasi` (konstanta `TEMA_PAKET`) — samakan dengan `undangan_get_temas()` di `cpt.php` setiap menambah tema.
- Nudge WF-05 memakai jendela umur 24–48 jam sehingga cron harian mengirimnya tepat **sekali** tanpa kolom penanda.
- **Enforcement masa aktif (T3.13) sengaja belum dipasang** di WF-05 — keputusan bisnis (enforce vs hapus dari pricing) masih terbuka; titik pasangnya sudah ditandai di node `Susun Pesan Harian`.
- Menolak reseller = abaikan link approval + hapus/tandai barisnya di sheet (tidak ada tombol tolak — jalur jarang, manual cukup).
- Bila WF-02 gagal di tengah (status sheet macet `DIPROSES`): WF-00 sudah memberi alert; pulihkan dengan ubah status baris ke `MENUNGGU_DATA` lalu minta customer submit ulang (masuk runbook T4.9).
- Batasan WF-08: jendela rekonsiliasi 7 hari & 100 order per siklus (cukup untuk volume MVP; menyentuh batas 100 dilaporkan di alert), order lebih muda dari 10 menit dilewati (delivery normal mungkin masih jalan), dan loopback mensyaratkan **WF-01 Active** — kegagalan loopback tercantum di alert owner.
