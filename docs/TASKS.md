# TASKS — Platform Undangan Digital (MVP 4 Minggu)

**Sumber:** [blueprint-undangan-digital.md](./blueprint-undangan-digital.md) · **Status:** aktif · **Dibuat:** 2026-07-07

**Cara pakai:** centang `- [x]` saat task selesai. ID task (`T1.4`, dst.) stabil — pakai untuk referensi di commit/diskusi. Referensi `§N` merujuk ke bagian blueprint.

**Legenda:** tag **`[+]`** = task tambahan hasil analisis celah blueprint (tidak ada di blueprint asli), selalu disertai alasan singkat setelah tanda `—`.

---

## ⏸ CHECKPOINT SESI — 2026-08-03 · PLATFORM LAUNCH-READY 🚀

**Kondisi: SEMUA sistem live, teruji end-to-end, dan berjalan otonom.** Riwayat rinci perjalanan deploy/uji ada di git log TASKS.md (commit 2026-07-19 s/d 2026-08-03).

- **Terbukti di produksi**: checkout HP → VA Duitku sandbox → callback → WF-01 (sheet+WA+email) → form → WF-02 (undangan terbit, enforcement paket, delivery WA+email+QR) → completed (T1.18 ✅, order #40). Idempotency dua arah teruji (#29). Sistem lalu berjalan sendiri 12 hari: reminder H-3/H+1, rekap komisi Senin 2×, backup Minggu 2×, monitor WAHA — tanpa intervensi.
- **Infra 100%**: 8 workflow aktif · WAHA `WORKING` · cron hPanel per menit terverifikasi (2026-08-03) · backup + alert mandiri · smoke test `scripts/cek-live.sh` 19/19 hijau · legal live · 10 metode Duitku sandbox aktif.
- **Pelajaran penting yang sudah dibakukan**: JSON workflow di repo membawa `schema` Sheets + ID credential (jangan import tanpa itu — n8n 2.29 menjodohkan credential by-name secara serampangan); aktivasi via `publish:workflow`; `$env` butuh `N8N_BLOCK_ENV_ACCESS_IN_NODE=false`; dump DB Hostinger pakai `mysqldump` langsung (bukan `wp db export`).

**Menunggu (urutan prioritas, mayoritas aksi owner):**
1. **Duitku production (T0.11)** — owner isi formulir aplikasi akun personal di web Duitku (BELUM diisi per 2026-08-03; blocker satu-satunya menerima uang riil) → setelah approval: ganti kredensial plugin ke mode production → uji order Rp 10.000 (T4.8/T4.12 — produk uji tersembunyi).
2. **Bersih-bersih artefak uji**: order #29 & #40, undangan `raka-sela-e998` & `raka-solehah-d445`, baris sheet terkait (bisa via CLI, minta saja "bersihkan").
3. **QA sisa T4**: uptime monitor (T4.3, UptimeRobot — browser), uji restore backup 1× (sisa T4.2), sisa uji perangkat riil T4.6 (autoplay musik, tombol salin, HEIC), QA checklist §13 formal (T4.7 — banyak yang de-facto sudah).
4. **Pra-launch bisnis**: review visual tema-02/03 + demo live per tema (T3.6), musik library (T1.15), keputusan masa aktif (T3.13), aset og:image default (sisa T1.13), review runbook (`docs/runbook.md`), rekrut 3 reseller + soft launch (T4.11).

**Akses:** Hostinger `ssh -p 65002 u803921702@147.93.80.20` (WP: `domains/harih.id/public_html`) · VPS `ssh root@31.97.50.197` (`/opt/harih`) · rahasia: `vps/.env` + `vps/google-sa.json` (lokal, gitignored) = server · panduan operasional: `docs/runbook.md` · panduan import n8n: `n8n/workflows/README.md`.
---

## T0 — Keputusan & Prasyarat Owner (sebelum / hari pertama Minggu 1)

### Keputusan (§16)
- [x] **T0.1** Tentukan nama brand + domain → **Keputusan (2026-07-07): brand `hariH`, domain `harih.id`.** ⚠️ Verifikasi apakah `.id` termasuk jatah domain gratis Hostinger (ccTLD `.id` umumnya tidak termasuk & lebih mahal dari `.com`) dan siapkan dokumen identitas sesuai syarat registri PANDI
- [x] **T0.2** Pilih payment gateway → **Keputusan: Duitku** (mendukung pendaftaran perorangan; QRIS/VA/e-wallet)
- [x] **T0.3** Sumber desain 3 tema → **Keputusan: desain in-house via Claude Code (opsional + Figma connector).** Tidak beli template — isu lisensi tema gugur; lisensi musik (T1.15) tetap wajib
- [x] **T0.4** Harga paket & komisi → **Keputusan: ikuti default blueprint** (Hemat 99rb / Favorit 179rb / Premium 299rb; komisi reseller 30%, diskon kupon 10%)
- [x] **T0.5** Nomor WA bisnis → **sudah tersedia.** **[+]** Pastikan nomor tetap dipakai wajar (warm-up) sampai dipakai WAHA di Minggu 2 — nomor yang lama tidak aktif berisiko saat mulai kirim otomatis
- [x] **T0.6** Rekening payout komisi reseller → **siap**

### Akun & kredensial (§3)
- [x] **T0.7** Buat akun Brevo. **[+]** Verifikasi domain pengirim (SPF/DKIM/DMARC) segera setelah domain aktif — propagasi DNS lambat dan mem-block pengujian email di Minggu 2 → **selesai** (2026-07-07)
- [x] **T0.8** Buat Google Cloud service account + simpan JSON key untuk n8n → **selesai**
- [x] **T0.9** Buat Google Sheet 3 tab (`orders`, `resellers`, `komisi`) sesuai skema §5, share ke email service account. **[+]** Format kolom nomor HP & tanggal sebagai *plain text* — Sheets otomatis menghapus `0` di depan nomor dan mengubah format tanggal → **selesai**
- [x] **T0.10** Daftar merchant **Duitku** mode **sandbox** (akun sandbox terpisah di sandbox.duitku.com) + simpan merchant code & API key → **selesai**
- [x] **T0.11 [+]** Ajukan pendaftaran merchant **production** (KYC + review situs) segera setelah katalog minimum + halaman legal live di Minggu 1 — approval butuh berhari-hari; ini risiko terbesar terhadap tanggal launch, sandbox tetap jalan paralel → **diajukan — pantau status approval Duitku**; pastikan situs live + halaman legal (T1.21) & katalog (T1.20) tersedia saat direview

---

## T1 — Minggu 1: Fondasi

### Infrastruktur & keamanan
- [x] **T1.1** Install plugin inti via WP-CLI §4: `woocommerce`, `litespeed-cache`, `fluent-smtp`, `limit-login-attempts-reloaded` + plugin resmi **Duitku** untuk WooCommerce (T0.2) → **script siap**: `scripts/setup-hostinger.sh` mencakup bagian CLI T1.1–T1.6 (plugin, theme, user bot editor, `FORM_TOKEN_SECRET`, `DISABLE_WP_CRON`, permalink/timezone/locale) — tinggal jalankan via SSH setelah upload `wp-content/` → **DIJALANKAN di server (2026-07-08)** — WooCommerce 10.9.4, FluentSMTP, Limit Login aktif; sisa: plugin Duitku (install bersama T1.18)
- [x] **T1.2** Buat user bot n8n + Application Password (§4). **[+]** Role `editor`, bukan `administrator` — cukup untuk create post CPT + upload media; bocornya app password tidak berarti takeover situs (kupon/order memakai key WC terpisah) → **selesai di server**; role final = `shop_manager` (editor tidak boleh kelola order via WC REST; tetap jauh di bawah administrator), password login bot di-reset, App Password tersimpan utk env n8n
- [x] **T1.3** Set `FORM_TOKEN_SECRET` di `wp-config.php` (nilai sama dengan env n8n) via `wp config set` (§4) → **selesai di server** (nilai dicatat di `vps/.env` lokal — gitignored)
- [x] **T1.4** Konfigurasi LiteSpeed: drop query string `to`, `utm_*`, `ref` (§4); permalink *Post name*; matikan XML-RPC → **selesai di server** — drop query string terpasang via `wp litespeed-option` (tanpa wp-admin); XML-RPC mati via mu-plugin; permalink + locale id_ID + timezone Asia/Jakarta aktif; siteurl sudah https
- [x] **T1.5** FluentSMTP → Brevo untuk email bawaan WooCommerce (§4) → **selesai** (owner, 2026-07-22)
- [x] **T1.6 [+]** `define('DISABLE_WP_CRON', true)` + cron job nyata di hPanel Hostinger tiap menit (`wp cron event run --due-now`) — delivery webhook WooCommerce lewat Action Scheduler yang bergantung traffic; tanpa cron nyata, order di jam sepi baru terkirim ke n8n saat ada pengunjung berikutnya → **TUNTAS TERVERIFIKASI (2026-08-03)**: cron 2026-07-08 ternyata tidak pernah aktif (ketahuan saat uji T1.18 — delivery menggantung); dipasang ulang owner via hPanel dan diverifikasi hidup: 25 event overdue → 1 dalam ±100 dtk tanpa intervensi, antrean Action Scheduler lewat-jadwal = 0
- [x] **T1.7 [+]** Hardening VPS: UFW (tutup port 3000), WAHA bind `127.0.0.1` + `WAHA_API_KEY`, subdomain + reverse proxy + TLS Let's Encrypt untuk n8n, aktifkan auth UI n8n — perintah §4 mem-bind WAHA ke semua interface tanpa auth: siapa pun bisa kirim WA atas nama bisnis / membajak sesi → **paket siap di `vps/`**: docker-compose (bind `127.0.0.1` + API key) + Caddy TLS otomatis + panduan UFW di `vps/README.md`; → **LIVE (2026-07-10)** di VPS existing (31.97.50.197) pakai varian `docker-compose.traefik.yml`: instance n8n hariH terpisah total dari n8n produksi owner, TLS via Traefik existing, WAHA localhost + API key (401 tanpa key ✓). ⚠️ Catatan: UFW di VPS ini inactive — tidak saya aktifkan sepihak (VPS produksi, bisa memutus layanan lain); eksposur hariH aman (WAHA localhost, n8n hanya via Traefik), tapi jadwalkan review firewall bersama owner
- [x] **T1.8 [+]** Inisialisasi git repo untuk mu-plugin & theme + tentukan alur deploy ke Hostinger (git pull / SFTP) — kode produksi harus ter-version-control sejak commit pertama → **selesai**: repo git + `README.md` (struktur, alur deploy rsync, dev lokal)

### Kode inti WordPress
- [x] **T1.9** Buat mu-plugin `wp-content/mu-plugins/undangan-core.php` sesuai §6: CPT `undangan` & `ucapan`, register 21 meta, endpoint RSVP (POST + GET), filter ukuran gambar & `big_image_size_threshold` → **kode di repo** (`undangan-core/` — sanitasi per jenis field: URL pakai `esc_url_raw`, `galeri` divalidasi JSON-array-URL max 10); verifikasi on-server saat deploy
- [x] **T1.10 [+]** Tutup kebocoran data REST di mu-plugin versi pertama: strip `meta` dari response untuk request tak terautentikasi (filter `rest_prepare_undangan`), blokir listing publik `wp/v2/undangan`, exclude CPT dari `wp-sitemap.xml`, `noindex` via `wp_robots`, matikan feed/oEmbed CPT — tanpa ini `GET /wp-json/wp/v2/undangan` tanpa auth membocorkan `rekening`, `wa_cp`, alamat & jadwal acara SEMUA pelanggan (`auth_callback` hanya membatasi tulis, bukan baca); template membaca meta server-side jadi REST baca publik memang tidak dibutuhkan → **kode di repo** (`undangan-core/rest.php` + blok search REST; `hardening.php` menambah blok enumerasi user & `?author=`); uji dengan QA tambahan saat deploy
- [x] **T1.11 [+]** Filter `woocommerce_max_webhook_delivery_failures` → naikkan ambang (mis. 25) — WooCommerce menonaktifkan webhook secara permanen & senyap setelah ~5 delivery gagal; VPS restart 10 menit bisa mematikan seluruh pipeline order → **kode di repo** (`undangan-core/woocommerce.php`)

### Tema & halaman undangan
- [x] **T1.12** Child theme + `single-undangan.php` sesuai §7: 10 section mobile-first, snippet nama tamu client-side (`?to=`), musik play setelah tap, section kondisional per paket. **[+]** Whitelist `template_id` (`tema-01|02|03`, fallback default) sebelum dipakai membentuk path aset — anti path traversal; **[+]** escape semua output meta (`esc_html`/`esc_url`) — defense-in-depth XSS → **kode di repo**: child theme `harih` (**parent: Astra** — dipakai halaman toko saja; halaman undangan dirender standalone dgn `wp_head`/`wp_footer` agar LiteSpeed/noindex/OG tetap jalan), 10 partial di `template-parts/undangan/`, arsitektur tema = skin CSS (`undangan/shared/` + `undangan/{tema}/`)
- [ ] **T1.13** Tema #1 selesai (desain + CSS + aset per keputusan T0.3); aset WebP ≤ 300 KB (§7) → **v1 di repo**: tema-01 "Botanical Elegan" (Playfair Display + Plus Jakarta Sans, sage/gading/emas, ornamen SVG inline tanpa file gambar). Sisa: review visual owner di perangkat nyata + aset cover default & og:image default. **Preview tersedia**: buka `preview/tema-01.html` di browser (pakai CSS/JS asli theme + data contoh). **Demo LIVE di server**: `/u/demo-tema-01/` (Raka & Sela, 3 foto, verifikasi eksternal 200 + OG + noindex ✓)
- [x] **T1.14 [+]** Cetak Open Graph tags di `single-undangan.php` + katalog (`og:title` "Rina & Bima", `og:image` cover ±1200×630 < 600 KB, URL absolut) — undangan didistribusikan via share WA; tanpa OG, preview link tampak murahan dan merusak nilai produk → **kode di repo** (`functions.php`): og:image = foto pertama galeri; og:image default per tema menyusul bersama T1.13; OG katalog terpasang di `page-katalog.php` (og:image default-nya juga menunggu aset T1.13)
- [ ] **T1.15 [+]** Mulai kurasi library musik: 5–10 track instrumental berlisensi komersial, arsipkan bukti lisensi, host file di uploads — "library musik" dijanjikan di semua paket tapi blueprint tidak punya task pengadaannya *(selesai paling lambat Minggu 2)*

### WooCommerce & toko
- [x] **T1.16** Buat 3 produk paket (Hemat/Favorit/Premium) tipe *simple* sesuai tabel §10. **[+]** Set **virtual non-downloadable** (downloadable memicu auto-complete yang melompati status `processing`) dan **Sold individually** (qty 2 = bayar 2× tapi hanya dapat 1 token/undangan) → **script siap**: `scripts/buat-toko.sh` (idempotent by SKU `HARIH-*`; nama produk memuat kata paket — dibutuhkan deteksi WF-01; + format harga IDR & guest checkout); sisa: jalankan via SSH di server
- [ ] **T1.17 [+]** Ramping checkout: guest checkout aktif, pangkas field via `woocommerce_checkout_fields` (nama, email, phone saja), `billing_phone` **wajib + tervalidasi format** (dipakai sebagai nomor delivery WA — field terpenting), hook `woocommerce_add_to_cart_validation` kosongkan cart sebelum add (1 order = 1 paket) — checkout default meminta alamat lengkap dan menjatuhkan konversi mobile → **kode di repo** (`undangan-core/woocommerce.php`, termasuk normalisasi nomor ke `628…` saat order dibuat); setting guest checkout kini otomatis via `scripts/buat-toko.sh`. Sisa: uji bareng plugin Duitku (T1.18 — kembalikan field bila plugin membutuhkannya)
- [x] **T1.18** Konfigurasi payment gateway sandbox + uji checkout sandbox end-to-end → **LULUS (order #40, 22 Jul)**: checkout dari HP → VA BCA sandbox → simulator → callback Duitku → `processing` → WF-01 (typo nomor WA tertangkap: `TIDAK_TERDAFTAR` → fallback email bekerja) → form diisi dari HP → undangan `/u/raka-solehah-d445/` terbit → delivery WA `TERKIRIM` → `completed`. Bonus 12 hari pasca-uji: reminder H-3, ucapan H+1, rekap Senin, dan 2 backup mingguan semuanya jalan otomatis. Temuan: mode "Coming soon" WooCommerce (dimatikan), 10 metode Duitku di-enable via CLI, **cron hPanel ternyata belum pernah terpasang** — delivery webhook menggantung sampai dipaksa; sementara ditopang WF-08 (SLA 15 mnt), cron per menit tetap WAJIB dipasang owner di hPanel
- [ ] **T1.19** Buat webhook WooCommerce → n8n (§4): Delivery URL `…/webhook/wc-order`, secret `WC_WEBHOOK_SECRET`. **[+]** Pakai topic **Action `woocommerce_order_status_processing`** alih-alih `Order updated` — fire tepat 1× per transisi status (bukan tiap update apa pun), mengecilkan race idempotency; n8n fetch detail order via WC REST → WF-01 sudah siap menerimanya; langkah setting lengkap di `n8n/workflows/README.md`
- [x] **T1.20** Halaman katalog versi minimum (cukup untuk pengajuan merchant T0.11; polish di Minggu 3) → **kode di repo**: `page-katalog.php` + `katalog.css` (standalone, token desain tema-01, mobile-first) — hero, cara pesan, 3 kartu paket dgn CTA `/checkout/?add-to-cart=` (resolve by SKU), link demo, FAQ mini, footer legal; dipasang sebagai front page oleh `buat-toko.sh`; OG katalog ikut terpasang. **Preview**: `preview/katalog.html`. Sisa: deploy + review visual owner (polish T3.8)
- [ ] **T1.21 [+]** Halaman legal: Syarat & Ketentuan (termasuk disclaimer hak cipta musik §7 + batasan masa aktif), Kebijakan Privasi, Kebijakan Refund, Kontak — prasyarat approval merchant gateway dan belum ada di sprint manapun → **draf 4 halaman siap** di `docs/konten-legal/` (S&K, Privasi — menyebut UU PDP 27/2022, Refund, Kontak). → **LIVE (2026-07-22)**: placeholder terisi (kontak hi@harih.id / +62 822-5197-5575, alamat Alam Sutera Tangerang, refund 7 hari/7 hari kerja default), 4 halaman dipublish via CLI dari `docs/konten-legal/` (md di repo = versi tayang), semuanya 200 ✓

---

## T2 — Minggu 2: Mesin Otomasi

### Infrastruktur n8n & WAHA
- [ ] **T2.1** Jalankan WAHA di VPS + scan QR sesi `default` dengan nomor bisnis (§4). **[+]** Pin image versi **≥ 2026.6.1** (fitur kirim media baru digratiskan ke Core sejak rilis itu; tag lama = teks saja) dan pastikan RAM VPS ≥ 2 GB/swap atau pakai engine ringan NOWEB/GOWS — engine default WEBJS menjalankan Chromium 0,5–1 GB → **WAHA LIVE**: versi 2026.6.2 (Core) ≥ 2026.6.1 ✓ fitur media gratis, engine WEBJS, auth aktif; sisa: **scan QR nomor bisnis** (SSH tunnel → dashboard) + pin tag image setelah stabil
- [ ] **T2.2 [+]** Set env n8n: `N8N_PAYLOAD_SIZE_MAX=64` (+ cek `N8N_FORMDATA_FILE_SIZE_MAX`), `GENERIC_TIMEZONE=Asia/Jakarta`, `EXECUTIONS_DATA_PRUNE=true` + max age ±168 jam, dan naikkan `client_max_body_size` reverse proxy — default 16 MB menolak upload 10 foto; default UTC menggeser semua cron WIB 7 jam; data eksekusi menyimpan binary foto dan memenuhi disk VPS → **semua env sudah di compose** (payload 64 MB, WIB, pruning 7 hari, binary mode filesystem, `WEBHOOK_URL`/`N8N_PROXY_HOPS`); reverse proxy final = Traefik existing (tanpa limit body default juga); **terpasang & live di `harih-n8n`** (2026-07-10)
- [ ] **T2.3** Simpan semua kredensial §3 sebagai environment variable / credentials n8n (tidak ada yang hardcode) → **env server terpasang lengkap** (kecuali `WC_WEBHOOK_SECRET` — menyusul di T1.19); sisa: credentials UI n8n (Google service account JSON + Brevo) diisi setelah akun owner n8n dibuat
- [x] **T2.4 [+]** Buat **WF-00 Error Workflow**: Error Trigger → alert email + WA ke owner (nama workflow, pesan error, order_id); set sebagai default error workflow SEMUA WF; aktifkan retry-on-fail (3×, backoff) di node HTTP kritis — tanpa ini kegagalan node (Brevo down, WP 500, quota Sheets) hanya terlihat kalau owner kebetulan membuka UI n8n, sementara customer sudah bayar → **JSON siap-import** (`n8n/workflows/WF-00-error-handler.json`, pesan sesuai copywriting 8a; butuh env baru `OWNER_EMAIL`/`OWNER_WA`); sisa: import + set sebagai error workflow di settings tiap WF (lihat README)
- [ ] **T2.5** Siapkan template email transaksional di Brevo (DNS sudah terverifikasi dari T0.7)

### Form isi data (§8)
- [x] **T2.6** Page template `page-isi-data.php`: verifikasi token HMAC server-side (kode §8), field lengkap §8, radio template bergambar, field mengikuti paket → **kode di repo**: token diverifikasi sebelum output (403 via `wp_die`), field per paket dari hint `&paket=` di URL (hanya tampilan — enforcement tetap di WF-02/T2.18), radio tema + link demo, dropdown musik otomatis muncul saat `harih_musik_library()` terisi (T1.15), webhook via konstanta `N8N_FORM_WEBHOOK_URL`; halaman `/isi-data/` dibuat otomatis oleh `scripts/setup-hostinger.sh`
- [x] **T2.7 [+]** Exclude `/isi-data/` dari cache LiteSpeed + kirim header no-cache dari template — validasi token adalah PHP per-request; halaman ter-cache berarti validasi tidak berjalan → **kode sudah kirim** `nocache_headers()` + action `litespeed_control_set_nocache`; exclude URI terpasang via `wp litespeed-option set cache-exc` di server ✓ (verifikasi header saat QA T4)
- [x] **T2.8 [+]** Kompresi/re-encode gambar client-side (canvas → JPEG ≤ 500 KB) + `accept="image/jpeg,image/png,image/webp"` pada input file — sekaligus menyelesaikan: foto HEIC iPhone yang ditolak WP (iOS auto-transcode untuk accept ini), payload total >16 MB, orientasi EXIF terputar, dan upload lambat di koneksi seluler → **kode di repo** (`isi-data.js`): JPEG max 1600px kualitas 0.82→0.65, QRIS di-encode **PNG** (artefak JPEG merusak pemindaian QR), bonus privasi: EXIF/GPS terbuang saat re-encode; uji riil iPhone di T4.6
- [x] **T2.9 [+]** Meta `Referrer-Policy: no-referrer` di halaman form — token order ada di URL dan bisa bocor lewat header `Referer` → **kode di repo** (+ `noindex` untuk template form via `wp_robots`)
- [x] **T2.10** Submit via `FormData` → webhook n8n + progress bar + halaman sukses (§8). **[+]** Disable tombol submit setelah klik — mencegah double-submit → **kode di repo**: XHR multipart (tanpa header custom = tanpa preflight CORS) + progress bar upload, seluruh fieldset di-disable saat mengunggah, guard `beforeunload`, panel sukses "cek WhatsApp ±5 menit", pesan error spesifik (429/timeout/putus); file dikirim sebagai `foto_0…foto_9`, `qris`, `jumlah_foto` — verifikasi end-to-end menunggu WF-02

### WF-01 — Order Intake (§9)
- [x] **T2.11** Bangun WF-01: webhook raw body + verifikasi HMAC → filter status → idempotency → generate token → append `orders` → deteksi kupon `RES-` → email + WA link form. **[+]** Handle **ping webhook WC** (body hanya `webhook_id` → balas 200, stop) — ping saat webhook disimpan akan gagal verifikasi HMAC dan tercatat sebagai delivery gagal; **[+]** terima status `processing` **ATAU** `completed` — sebagian gateway/konfigurasi menset order langsung `completed` → **JSON siap-import** (`n8n/workflows/WF-01-order-intake.json`; topic Action hanya kirim `order_id` → fetch detail via WC REST; token identik `page-isi-data.php`); uji end-to-end menunggu import + webhook T1.19
- [x] **T2.12 [+]** Idempotency kuat di Sheets: pola **append-then-verify** (append dulu → baca balik semua baris `order_id` → lanjut hanya bila baris miliknya yang pertama) — lookup→append tidak atomik; dua delivery paralel bisa sama-sama lolos lookup dan menghasilkan dobel email/WA/komisi → **di WF-01**: identifikasi baris via kolom baru `exec_id`; eksekusi yang kalah menghapus barisnya sendiri (anti dobel reminder WF-05). ⚠️ Butuh 2 header kolom baru di tab `orders`: `wa_status` + `exec_id`
- [x] **T2.13 [+]** Webhook WF-01 mode **Respond Immediately** — WooCommerce menghitung response lambat sebagai kegagalan delivery dan menyicil menuju auto-disable, padahal workflow-nya sukses → **di WF-01** (`responseMode: onReceived`)
- [ ] **T2.14 [+]** Fungsi normalisasi nomor HP dipakai bersama WF-01/02/03: strip non-digit, `0…`→`62…`, `8…`→`628…`, validasi panjang; cek `contact/check-exists` WAHA sebelum kirim; bila nomor tak terdaftar WA → tandai kolom `wa_status` di sheet dan andalkan email — `chatId` salah format = pesan hilang tanpa error → **terpasang di WF-01** (node `Siapkan Data Order` + `check-exists` + 5 status `wa_status`); sisa: salin identik ke WF-02/03 saat dibangun
- [x] **T2.15 [+]** Hitung komisi dari **line item minus diskon** dalam satu Code node (bukan field `total` payload — bisa termasuk fee kanal pembayaran); uji dengan beberapa payload sandbox nyata → **di WF-01** (`dasar_komisi = Σ line_items[].total` pasca-diskon, komisi = 30% dibulatkan); uji payload sandbox nyata saat T1.18

### WF-02 — Generate Undangan (§9)
- [x] **T2.16** Bangun WF-02: verifikasi token → lookup status → loop upload foto ke `wp/v2/media` → susun payload + slug → create post `undangan` → QR qrserver → email delivery + lampiran QR → WA delivery → update sheet `SUDAH_JADI` → set order `completed` → **JSON siap-import** (`n8n/workflows/WF-02-generate-undangan.json`, 41 node; QR gagal ≠ fatal — email tetap jalan tanpa lampiran); uji end-to-end menunggu import
- [x] **T2.17 [+]** Respond-to-Webhook **segera setelah** validasi token+status ("Data diterima — undangan dikirim ke WA ±5 menit") + langsung update status sheet ke `DIPROSES` sebelum proses berat — WF-02 berjalan menit-an; fetch mobile timeout → user submit ulang → dua eksekusi paralel sama-sama membaca `MENUNGGU_DATA` → undangan ganda → **di WF-02**: respond 200 → set `DIPROSES`; submit saat `DIPROSES` dapat 409, saat `SUDAH_JADI` dapat 200 + link lama (idempoten)
- [x] **T2.18 [+]** Enforcement server-side: `paket` & `template_id` dibaca dari sheet/line item order WC (BUKAN dari form/URL) + whitelist `template_id` per paket — parameter tak bertanda-tangan bisa diubah customer untuk upgrade paket gratis → **di WF-02** (`Cek Order & Validasi`): paket dari sheet (asal line item WF-01), fallback teraman `hemat`; konstanta `TEMA_PAKET` disamakan dgn `undangan_get_temas()`; paket hemat: file & field galeri/amplop/video diabaikan
- [x] **T2.19 [+]** Validasi file server-side di WF-02: maksimal 10 file, whitelist mime (jpeg/png/webp), batas ukuran — validasi JS client bisa dilewati siapa pun yang punya token (cukup curl), berisiko menghabiskan disk/inodes hosting → **di WF-02**: max 10 + mime + ≤ 2 MB → 422 dgn pesan spesifik; sanitasi final tetap di WP saat sideload
- [x] **T2.20 [+]** Set "Allowed Origins (CORS)" = `https://domain.com` di webhook form n8n; uji dari browser HP riil, bukan curl — tanpa header CORS, JS tidak bisa membaca respons lintas origin → user mengira gagal → submit ulang → **di WF-02**: `allowedOrigins: https://harih.id`; uji HP riil dijadwalkan bersama T4.6
- [x] **T2.21 [+]** Desain pesan WA agar berfungsi degradasi anggun ke teks+link (QR code cukup via email/halaman) — jaga-jaga perubahan model lisensi media WAHA → **di WF-02**: WA murni teks+link (template 2b), QR hanya lampiran email; node QR `continueRegularOutput`
- [x] **T2.22 [+]** Monitoring sesi WAHA: cron n8n tiap 10 menit `GET /api/sessions` → alert **via email** bila status ≠ `WORKING` (kanal WA-nya sedang mati); semua node WAHA continue-on-fail + tulis kegagalan ke kolom `wa_status` agar bisa di-retry — sesi bisa logout/banned diam-diam dan seluruh delivery WA gagal senyap → **JSON siap-import** (`n8n/workflows/WF-07-monitor-waha.json`, template email 8b, anti-spam 1×/jam via static data; WAHA tak terjangkau juga memicu alert); continue-on-fail + `wa_status` sudah di WF-01/02

### Konten
- [ ] **T2.23 [+]** Tulis copywriting semua pesan otomatis (email & WA): konfirmasi order + link form, delivery undangan + panduan share `?to=`, reminder H-3, ucapan H+1 + testimoni, welcome kit reseller, rekap komisi, nudge belum-isi-data — dibutuhkan WF-01/02/04/05 dan bukan pekerjaan 5 menit; minta review owner → **draf lengkap** di `docs/copywriting-pesan.md`: 8 kelompok pesan (email+WA) + legenda variabel n8n + 3 caption promosi reseller + 2 alert internal (WF-00, sesi WAHA via email). Sisa: review gaya bahasa oleh owner

---

## T3 — Minggu 3: Fitur & Reseller

### Fitur halaman undangan
- [ ] **T3.1** RSVP live: form + daftar ucapan via fetch ke endpoint §6, honeypot terpasang, handling 429 di client → **kode sudah ada dari scaffold T1** (`rsvp.php` + `undangan.js` — termasuk optimistic prepend karena GET ter-cache 60 dtk); tinggal verifikasi live saat deploy
- [ ] **T3.2 [+]** Kirim header `X-LiteSpeed-Cache-Control: public, max-age=60` dari endpoint GET RSVP — endpoint REST tidak ter-cache LiteSpeed secara default, sehingga SETIAP pageview undangan memicu 1 hit PHP dan mematahkan strategi cache A2; staleness 60 detik dapat diterima untuk buku tamu → **kode sudah ada dari scaffold T1** (`rest.php`: header + API LSCWP `set_cacheable`/`set_ttl`/`tag_add`, plus purge tag saat ucapan baru masuk); verifikasi header `x-litespeed-cache: hit` saat deploy
- [ ] **T3.3 [+]** Rate limit RSVP ramah CGNAT: kunci per `IP + undangan_id` dan longgarkan (mis. 5/menit) — operator seluler Indonesia berbagi 1 IP publik untuk ribuan pengguna; aturan per-IP murni membuat tamu sesama operator saling mengunci saat resepsi → **kode sudah ada dari scaffold T1** (`rest.php`: 1 kiriman/15 dtk per IP+undangan, transient di-set hanya setelah sukses)
- [ ] **T3.4** Amplop digital: tombol salin rekening (`navigator.clipboard`) + tampil gambar QRIS, sesuai paket (§7)
- [x] **T3.5** Tema #2 & #3 selesai *(buffer alami — bila jadwal terjepit, launch dengan 2 tema dapat diterima)* → **kode di repo**: tema-02 "Senja Terakota" (Cormorant Garamond + Karla, terakota/krem/tembaga, bingkai arch + gradasi senja) & tema-03 "Langit Malam" (Prata + Manrope, navy gelap + emas, cover bertabur bintang; termasuk override mode-gelap utk badge RSVP + QRIS berlatar putih agar tetap terpindai). Terdaftar di `undangan_get_temas()` + `harih_tema_fonts()`; whitelist WF-02 `TEMA_PAKET` sudah memuat keduanya sejak awal. **Preview**: `preview/tema-02.html` & `preview/tema-03.html` (varian cover tanpa foto — signature tema). Sisa: review visual owner di perangkat nyata + demo live per tema (T3.6)
- [ ] **T3.6 [+]** Buat 1 undangan demo publik per tema (`/u/demo-tema-01` dst.) + tautkan dari halaman produk & katalog — customer harus bisa preview sebelum membeli; implisit di blueprint (`/u/demo`) tapi tidak pernah jadi task → demo tema-01 **sudah live** (`/u/demo-tema-01/`); sisa: tautkan dari halaman produk + demo tema #2–#3 menyusul
- [ ] **T3.7 [+]** State pasca-acara di template (countdown selesai → pesan "acara telah berlangsung") + dokumentasikan asumsi zona waktu WIB (atau label waktu di detail acara) — countdown negatif terlihat rusak; acara WITA/WIT meleset 1–2 jam → **kode sudah ada dari scaffold T1** (`undangan.js` + target ISO `+07:00` dirakit server; label "WIB" tampil di detail acara)
- [ ] **T3.8** Polish halaman katalog. **[+]** Tambah halaman "Cara Pesan" + FAQ — menaikkan konversi & mengurangi beban CS

### Reseller
- [x] **T3.9** Landing "Jadi Reseller" + WF-03 (§9): form → kode `RES-XXXX` → create kupon WC → append `resellers` → welcome kit WA → **workflow siap-import** (`n8n/workflows/WF-03-onboarding-reseller.json`, 45 node — dua webhook: daftar + approve) + **landing di repo**: `page-jadi-reseller.php` + `reseller.js` (kontrak form WF-03: `nama`/`wa`/`bank`/`norek` + honeypot `website`; URL webhook diturunkan otomatis dari `N8N_FORM_WEBHOOK_URL`), halaman dibuat `buat-toko.sh`, preview `preview/jadi-reseller.html`; sisa: deploy + uji end-to-end
- [x] **T3.10 [+]** Anti-fraud reseller di WF-03: **approval manual owner** sebelum kupon dibuat (status `PENDING` di sheet), honeypot + rate limit di form, `usage_limit_per_user` pada kupon, idempotency by nomor WA, kebijakan tertulis soal self-deal — endpoint publik tanpa proteksi = mesin cetak kupon; reseller memakai kupon sendiri membocorkan ~37% margin (diskon 10% + komisi 30%) → **di WF-03**: approval via link ber-HMAC di WA/email owner (klik 2× idempoten), honeypot sukses-palsu, rate limit 5/jam/IP, cek `check-exists` WA saat daftar, kupon `individual_use` + `usage_limit_per_user=1`, larangan self-deal tertulis di welcome kit 6a (+ S&K T1.21). ⚠️ Butuh header kolom `status` di tab `resellers`
- [x] **T3.11** WF-04 rekap komisi (§9): cron Senin 09:00 WIB → rekap `UNPAID` per reseller via WA + total ke owner → **JSON siap-import** (`n8n/workflows/WF-04-rekap-komisi.json`; owner tetap menerima pesan saat tidak ada UNPAID — heartbeat mingguan; anomali data reseller dilaporkan di rekap owner); payout tetap manual, ubah `UNPAID`→`PAID` di sheet

### Reliabilitas pipeline
- [x] **T3.12 [+]** **WF rekonsiliasi order**: cron 15 menit `GET /wc/v3/orders?status=processing,completed` vs tab `orders` → proses order yang tertinggal; + cron harian cek `GET /wc/v3/webhooks` → alert bila status ≠ `active` — jaring pengaman universal terhadap auto-disable webhook, downtime n8n, dan delivery yang hilang; mode kegagalan paling berbahaya di seluruh sistem → **JSON siap-import** (`n8n/workflows/WF-08-rekonsiliasi-order.json`): order tertinggal di-POST ulang ke webhook WF-01 via **loopback ber-HMAC** — logika intake tetap satu pintu, tanpa duplikasi; tiap temuan meng-alert owner (temuan = insiden); + cek webhook harian 07:30 dgn deteksi webhook hilang total; uji dgn QA "matikan n8n 10 menit"
- [ ] **T3.13 [+]** Putuskan & implementasikan masa aktif paket: perluas WF-05 (set post ke `draft` via REST saat lewat masa aktif) ATAU hapus baris masa aktif dari tabel harga — masa aktif H+7/H+30/1 tahun saat ini dijual tanpa mekanisme penegakan

---

## T4 — Minggu 4: Hardening & Launch

- [ ] **T4.1** WF-05 reminder harian 08:00 WIB (§9): H-3 cek data + H+1 testimoni & kode referral. **[+]** Tambah nudge WA bila status `MENUNGGU_DATA` > 24 jam — customer sudah bayar tapi lupa mengisi data = revenue yang bisa diselamatkan; **[+]** jalankan enforcement masa aktif (dari T3.13) → **JSON siap-import** (`n8n/workflows/WF-05-reminder-harian.json`; nudge pakai jendela 24–48 jam = terkirim tepat 1× tanpa kolom penanda; personalisasi nama mempelai via kolom baru `mempelai` yang diisi WF-02); **enforcement masa aktif belum dipasang** — menunggu keputusan T3.13, titik pasang sudah ditandai di node `Susun Pesan Harian`
- [ ] **T4.2** WF-06 backup mingguan (§9). **[+]** Tambah: export workflows n8n (JSON → git), backup volume sesi WAHA, ganti backup uploads ke **rsync incremental** (tar penuh mingguan membengkak), uji restore 1× — WF-06 versi blueprint hanya melindungi WordPress; workflow n8n & sesi WA adalah aset kritis tanpa backup → **script siap**: `vps/backup-harih.sh` di host VPS (bukan n8n — rsync/volume Docker/SSH tak tersedia dari kontainer): dump DB terverifikasi, rsync incremental uploads, tar sesi WAHA + volume n8n (exclude binaryData), export workflow JSON, retensi 28 hari, alert Brevo mandiri saat gagal; sisa: pasang SSH key + cron di VPS (langkah di header script) + **uji restore 1×**
- [ ] **T4.3 [+]** Pasang uptime monitoring gratis (UptimeRobot): situs WP, endpoint n8n, healthcheck WAHA — deteksi dini untuk semua mode kegagalan senyap
- [ ] **T4.4** Uji idempotency (§12): kirim webhook 2×, submit form 2× → 1 baris, 1 email, 1 WA, tidak ada undangan ganda
- [ ] **T4.5** Uji cache & beban: `?to=Budi` tetap `x-litespeed-cache: hit`, endpoint RSVP ter-cache (T3.2), halaman undangan cepat di 3G
- [ ] **T4.6 [+]** Uji perangkat riil: iPhone Safari & Android Chrome — autoplay musik setelah tap, tombol salin rekening, upload foto dari galeri HP (HEIC), preview share WA (OG debugger; ingat WA meng-cache preview per URL) — perilaku Safari sering berbeda dari emulator
- [ ] **T4.7** Jalankan seluruh QA checklist §13 + **QA tambahan** (daftar di bawah) sampai hijau semua → **alat bantu siap**: `scripts/cek-live.sh` — smoke test curl dari mesin mana pun (katalog+OG, demo noindex, cache `?to=`, kebocoran REST, sitemap, 403 form, xmlrpc, halaman legal, 3 webhook n8n aktif, port 3000 tertutup); jalankan setelah tiap deploy
- [ ] **T4.8** Aktifkan payment gateway mode **production** (asumsi approval T0.11 sudah keluar) + uji nominal kecil
- [x] **T4.9 [+]** Tulis runbook operasional 1 halaman untuk owner (cek harian, menangani order stuck, re-enable webhook WC, re-scan sesi WA) + SOP CS & revisi manual (kanal, siapa pegang wp-admin, SLA revisi 1×/3× sesuai paket) — sistem "tanpa campur tangan manusia" tetap butuh manusia saat rusak; layanan revisi dijual di pricing tapi prosesnya belum didefinisikan → **draf lengkap**: `docs/runbook.md` (akses, rutinitas harian, tabel alert→tindakan utk WF-00/07/08+backup, 4 skenario order bermasalah, scan ulang QR via tunnel, re-enable webhook, ops reseller+payout, SOP revisi per paket, restore). Sisa: review owner + sesuaikan angka kebijakan (SLA, revisi berbayar Hemat)
- [ ] **T4.10 [+]** *(Opsional)* Analytics ringan (GA4/Plausible) + Google Search Console (katalog terindeks, undangan ter-noindex) — tanpa data, keputusan v2 §15 hanya menebak
- [ ] **T4.11** Rekrut 3 reseller pertama *(tugas owner, bukan dev)* + soft launch
- [ ] **T4.12** **DoD final** (§12): order riil Rp 10.000 (produk uji tersembunyi) dari HP → undangan diterima di WA < 15 menit

### QA tambahan [+] (melengkapi §13)
- [ ] `GET /wp-json/wp/v2/undangan` tanpa auth → meta kosong / listing ditolak
- [ ] CPT `undangan` tidak muncul di `wp-sitemap.xml`; single undangan punya meta robots `noindex`
- [ ] Simpan ulang webhook WC (memicu ping) → tidak ada error di n8n, webhook tetap `active`
- [ ] Ubah `paket=premium` di URL/form → undangan tetap sesuai paket yang dibayar
- [ ] Upload via curl langsung ke webhook: file ke-11 / >2 MB / non-gambar → ditolak WF-02
- [ ] Foto HEIC dari iPhone → masuk galeri sebagai JPEG dengan orientasi benar
- [ ] Submit form kedua saat WF-02 masih berjalan → tertolak (status `DIPROSES`), tidak ada undangan ganda
- [ ] Matikan n8n 10 menit saat ada order masuk → order tetap terproses setelah nyala (via WF rekonsiliasi T3.12)
- [ ] Nomor `08xx`, `+62 8xx`, `628xx` di checkout → WA terkirim ke semua format
- [ ] Share link undangan di WA → preview OG (judul + gambar) tampil benar
- [ ] Buka undangan yang acaranya sudah lewat → state pasca-acara, bukan countdown negatif
- [ ] Reseller memakai kupon sendiri → terdeteksi/tertolak sesuai kebijakan T3.10
- [ ] Port 3000 VPS tidak bisa diakses dari luar; UI n8n hanya via HTTPS + auth

---

## DoD per Minggu

| Minggu | Definition of Done |
|---|---|
| **1** | Checkout sandbox berhasil; `/u/demo` tampil sempurna di HP (§12). **[+]** REST publik tidak membocorkan meta (T1.10); pengajuan merchant production terkirim (T0.11); warm-up WA sudah berjalan (T0.5) |
| **2** | 1 order sandbox mengalir end-to-end → undangan terbit + email & WA terkirim tanpa sentuhan manusia (§12). **[+]** WF-00 error workflow aktif; kegagalan node memunculkan alert ke owner |
| **3** | Order berkupon `RES-*` otomatis tercatat komisinya + rekap WA terkirim (§12). **[+]** Uji downtime lulus: n8n mati 10 menit saat order masuk → order terproses setelah nyala (T3.12); demo semua tema live |
| **4** | Checklist §13 + QA tambahan hijau semua; order riil Rp 10.000 dari HP → undangan di WA < 15 menit (§12) |

---

## Backlog v2 (setelah 100 order pertama — §15, §10)

- [ ] Occasion baru dengan duplikasi tema: khitanan, aqiqah, wisuda (musiman Mei–September), e-card Lebaran
- [ ] Add-on WA blast ke daftar tamu — Rp 50rb/200 tamu (n8n loop, delay acak 20–45 detik, hanya daftar dari customer)
- [ ] Add-on buku tamu QR check-in — Rp 100rb
- [ ] Amplop digital ter-escrow via QRIS dengan fee platform 2–5% *(perubahan model bisnis paling bernilai)*
- [ ] Arsip otomatis undangan kedaluwarsa: set draft + hapus media H+90 untuk Hemat/Favorit *(pelengkap T3.13; menjaga disk/inodes)*
- [ ] Migrasi log operasional Google Sheets → Postgres di VPS *(saat > 500 order/bulan — sekaligus menuntaskan sisa race condition idempotency Sheets yang non-atomik)*
- [ ] Dashboard reseller; dashboard self-service customer untuk edit undangan
- [ ] Custom domain per undangan · multi-bahasa · tema builder drag-and-drop *(non-goals v1 §15)*
- [ ] Analytics & Search Console (bila T4.10 dilewati saat launch)

---

**Catatan known limitation (diterima untuk MVP):** idempotency Google Sheets tidak atomik — dimitigasi topic Action + append-then-verify (T1.19, T2.12); sisa risiko race sangat kecil dan hilang total saat migrasi Postgres di v2.
