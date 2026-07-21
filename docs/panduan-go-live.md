# Panduan Go-Live hariH — Sisa Aksi Owner

> Posisi sekarang: **semua kode sudah live** (katalog, tema, form, landing reseller), 8 workflow otomasi sudah terimpor di n8n **tapi belum aktif**, dan backup mingguan sudah berjalan. Sisa 7 langkah di bawah (±40 menit, sebagian butuh HP nomor bisnis) — setelah itu bilang **"lanjut"** dan uji end-to-end dijalankan otomatis.
>
> Yang TIDAK perlu Anda lakukan (sudah ditangani dari CLI): produk & katalog, webhook WooCommerce (sudah `active` + secret terpasang), credential WC/WP REST di n8n, env server, cron backup.

---

## Langkah 1 — Amankan akun n8n (2 menit) ⚠️ kerjakan pertama

Buka `https://n8n.harih.id` dari browser.

- Bila muncul layar **"Set up owner account"** → isi SEGERA (email `hi@harih.id` + password kuat, simpan di password manager). Instance yang belum di-klaim bisa diambil siapa pun yang menemukannya.
- Bila muncul halaman login → akun sudah ada; cukup pastikan Anda bisa masuk.

**Verifikasi:** setelah login terlihat 8 workflow berawalan `WF-…` (semuanya Inactive — biarkan, jangan diaktifkan manual; nanti diaktifkan otomatis setelah credential lengkap).

## Langkah 2 — File kunci Google Service Account (5–10 menit)

Ini pembuka blokir terbesar — tanpa ini WF-01/02 tidak bisa menulis ke Google Sheet.

**2a. Cek dulu: pernah dibuat atau belum?** Buka Google Sheet hariH → tombol **Share** → lihat daftar yang punya akses:
- **Ada email berakhiran `…iam.gserviceaccount.com`** → SA pernah dibuat. Bagian setelah `@` (sebelum `.iam`) adalah **PROJECT ID**-nya. Di [console.cloud.google.com](https://console.cloud.google.com), klik **pemilih project di bar atas** (sebelah logo — ini penyebab umum "kosong": Console membuka project lain) → pilih project itu → lanjut ke 2c.
- **Tidak ada** → SA memang belum dibuat → lanjut ke 2b.

**2b. Buat dari nol** (login dengan akun Google pemilik Sheet):
1. [console.cloud.google.com](https://console.cloud.google.com) → pemilih project (bar atas) → **New Project** → nama `harih` → Create → pastikan project `harih` yang terpilih.
2. Menu ☰ → **APIs & Services → Library** → cari **Google Sheets API** → **Enable**. Ulangi untuk **Google Drive API** (dipakai n8n untuk membuka spreadsheet).
3. Menu ☰ → **IAM & Admin → Service Accounts** → **Create service account** → nama `n8n-harih` → Create and continue → bagian role **lewati saja** (akses cukup lewat share Sheet) → Done.

**2c. Unduh kunci JSON:**
1. Klik service account-nya → tab **Keys** → **Add key → Create new key → JSON** → Create → file `.json` terunduh.
2. Pindahkan & ganti namanya menjadi **`vps/google-sa.json`** di folder proyek `harih` di Mac. File ini sudah di-gitignore — tidak akan pernah ter-commit.
3. Buka filenya sebentar, salin nilai `client_email` (…@…iam.gserviceaccount.com).

**2d. Share Sheet ke SA:** Google Sheet → **Share** → tempel `client_email` → role **Editor** → hilangkan centang "Notify" → Share.

**Verifikasi:** `client_email` muncul di daftar share sebagai Editor, dan file `vps/google-sa.json` ada di Mac.

## Langkah 3 — Kolom baru di Google Sheet (2 menit)

Tambahkan header berikut di **baris 1** (huruf = kolom):

| Tab | Kolom | Header |
|---|---|---|
| `orders` | M | `wa_status` |
| `orders` | N | `exec_id` |
| `orders` | O | `mempelai` |
| `resellers` | G | `status` |

Urutan tab `orders` kolom A–L harus tetap: `order_id · tgl_order · nama · email · wa · paket · kupon · total · token · status · link_undangan · tgl_acara`. Tulis persis huruf kecil semua.

## Langkah 4 — Mailbox `hi@harih.id` (3 menit)

hPanel → **Emails** → buat akun `hi@harih.id` (termasuk paket hosting). Semua alert sistem (workflow gagal, WA down, rekonsiliasi, backup gagal) dikirim **ke** alamat ini.

**Verifikasi:** kirim email percobaan dari Gmail pribadi ke `hi@harih.id` → masuk.

## Langkah 5 — Scan QR WhatsApp (5 menit — butuh HP nomor bisnis)

1. Di terminal Mac (biarkan jendelanya terbuka selama proses):
   ```
   ssh -L 3000:127.0.0.1:3000 root@31.97.50.197
   ```
2. Buka browser: `http://localhost:3000/dashboard` → login user `harih`, password = nilai `WAHA_API_KEY` (lihat dengan: `grep WAHA_API_KEY vps/.env` di folder proyek).
3. Menu **Sessions** → sesi `default` → Start/Restart → muncul QR code.
4. Di HP nomor bisnis: WhatsApp → titik tiga → **Perangkat tertaut** → **Tautkan perangkat** → scan QR di layar.
5. Tunggu status berubah **`WORKING`**.

**Penting:** jangan logout WhatsApp di HP; nomor ini jadi kanal pengiriman semua pesan otomatis.

## Langkah 6 — FluentSMTP → Brevo (5 menit)

wp-admin → **FluentSMTP** → Add Another Connection → pilih **Brevo** → tempel API key Brevo → From Email `noreply@harih.id`, From Name `hariH` → simpan → kirim **Test Email** ke `hi@harih.id`.

Catatan: ini hanya untuk email bawaan WooCommerce (invoice, dsb.). Email otomatis dari n8n (konfirmasi order, delivery undangan) memakai API Brevo langsung — sudah jalan tanpa langkah ini.

## Langkah 7 — Halaman legal + Duitku (15 menit)

**Legal** — pilih salah satu:
- **Opsi cepat:** kirim via chat ke saya: alamat usaha + angka kebijakan refund (mis. batas hari pengajuan) → placeholder saya isi dan 4 halaman saya publish via CLI.
- **Manual:** wp-admin → Pages → Drafts → untuk tiap halaman, salin konten dari `docs/konten-legal/<slug>.md`, ganti semua `{{…}}`, lalu **Publish** (4 halaman: syarat-ketentuan, kebijakan-privasi, kebijakan-refund, kontak).

**Duitku (sandbox):** unduh plugin dari dashboard Duitku (atau wp-admin → Plugins → Add New → cari "Duitku") → aktifkan → WooCommerce → Settings → **Payments** → Duitku → mode **Sandbox**, isi Merchant Code + API Key sandbox (dari T0.10) → aktifkan metodenya → Save.

---

## Setelah semua selesai → bilang **"lanjut"**

Yang akan dijalankan otomatis dari sini (tanpa Anda sentuh):

1. Import credential `Google SA hariH` dari `vps/google-sa.json` via CLI.
2. Tautkan ketiga credential ke semua node workflow (by ID) + set **WF-00 sebagai Error Workflow** di 7 workflow.
3. **Aktivasi** seluruh workflow (WF-07 monitor terakhir, setelah sesi WA `WORKING`).
4. **Uji end-to-end**: order uji dibuat via WP-CLI atas nama Anda (WA 6282251975575, email hi@harih.id) → Anda akan menerima WA+email link form → form diisi otomatis → undangan terbit → order `completed` → cek WA+email delivery + QR → uji idempotency (webhook dobel & submit dobel) → `cek-live.sh` harus hijau.
5. Sisanya tinggal: uji checkout Duitku sandbox dari HP (T1.18), lalu ajukan review production (T0.11).

Kendala di salah satu langkah? Sebut nomor langkahnya saja — bagian yang bisa diambil alih CLI akan saya kerjakan.
