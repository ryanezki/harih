# TASKS — hariH · Jalur ke Pendapatan Pertama

**Sumber:** [blueprint-undangan-digital.md](./blueprint-undangan-digital.md) · **Status:** aktif · **Ditulis ulang:** 2026-08-03

> Dokumen ini menggantikan daftar sprint 4 minggu (T0–T4) yang sudah selesai. **Riwayat lengkapnya aman di git log** (`git log --follow docs/TASKS.md`, commit 2026-07-07 s/d 2026-08-03) — jangan disalin balik ke sini.

**Cara pakai:** centang `- [x]` saat selesai. ID (`P0.1`, dst.) stabil — pakai di commit & diskusi. Anotasi `(eks-T1.15)` menunjuk ID lama yang masih dirujuk komentar kode dan `n8n/workflows/README.md`. Tanda **👤** = butuh tangan owner, tidak bisa dikerjakan dari CLI.

---

## Kondisi saat ini (diverifikasi langsung 2026-08-03, bukan asumsi dokumen)

**Platform live dan terbukti end-to-end.** Checkout HP → VA Duitku sandbox → callback → WF-01 → form → WF-02 (undangan terbit, enforcement paket, delivery WA+email+QR) → `completed` (order #40). Lalu berjalan sendiri 12 hari tanpa intervensi: reminder H-3/H+1, rekap komisi Senin 2×, backup Minggu 2×, monitor WAHA.

Hasil pemeriksaan langsung hari ini:

| Sehat ✓ | Bermasalah ✗ |
|---|---|
| Katalog, 4 halaman legal, landing reseller → 200 | `/u/demo-tema-02/` & `/u/demo-tema-03/` → **404** (ditautkan dari form berbayar) |
| `/wp-json/wp/v2/undangan` → **401** tanpa auth (meta tidak bocor) | `wp-sitemap-users-1.xml` **menyiarkan username** `n8nbot` & `hiharih-id` |
| Undangan `noindex` ✓ · cache LiteSpeed **hit** pada `?to=` ✓ | Katalog **tanpa `og:image`**; undangan paket Hemat juga (galeri kosong by design) |
| `xmlrpc.php` → 403 · port 3000 WAHA tidak terjangkau publik ✓ | `<title>` front page literal **`harih.id`**, tanpa meta description |
| n8n `/healthz` → 200 · WF-03 aktif (approve-reseller → 403 tanpa HMAC) | `/hello-world/` masih tayang & masuk sitemap |
| Rahasia tidak pernah ter-commit (`vps/.env`, `google-sa.json` gitignored) ✓ | Repo **tanpa remote** — satu-satunya salinan ada di Mac ini |

**Blocker tunggal untuk menerima uang riil:** akun Duitku production belum diajukan (P0.1).

**Akses:** Hostinger `ssh -p 65002 u803921702@147.93.80.20` (WP: `domains/harih.id/public_html`) · VPS `ssh root@31.97.50.197` (`/opt/harih`) · rahasia: `vps/.env` + `vps/google-sa.json` (lokal, gitignored) = cermin server · operasional: [`docs/runbook.md`](./runbook.md) · import n8n: [`n8n/workflows/README.md`](../n8n/workflows/README.md).

---

## P0 — Blocker pendapatan & cacat yang dilihat customer

*Selesaikan sebelum order riil pertama masuk.*

- [ ] **P0.1** 👤 **Duitku production** *(eks-T0.11 → T4.8 → T4.12)*
  **Kenapa:** blocker **satu-satunya** untuk menerima uang riil. Selama ini tidak beres, semua item lain hanya persiapan.
  **Langkah:** (1) isi formulir aplikasi merchant personal di dashboard Duitku — situs live + 4 halaman legal + katalog sudah siap direview; (2) pantau approval (butuh berhari-hari); (3) setelah approve: WooCommerce → Settings → Payments → Duitku → ganti mode **Production** + Merchant Code & API Key production; (4) buat produk uji tersembunyi Rp 10.000 (`catalog_visibility=hidden`), pesan dari HP, verifikasi undangan sampai di WA < 15 menit, lalu hapus produknya.
  **Selesai bila:** 1 order riil Rp 10.000 lolos end-to-end dari HP dan dana masuk ke rekening merchant.

- [ ] **P0.2** **Demo tema-02 & tema-03** *(eks-T3.6)*
  **Kenapa:** **bug live di jalur berbayar.** `page-isi-data.php:92` menautkan `/u/demo-{tema}/` untuk ketiga tema; tema-02 & tema-03 masih 404. Customer yang sudah membayar mengklik "Lihat contoh ↗" dan mendapat halaman error tepat saat sedang memilih tema.
  **Langkah:** buat 2 post `undangan` demo via WP-CLI dengan slug `demo-tema-02` & `demo-tema-03` (meta lengkap + galeri, mengikuti pola `demo-tema-01`), lalu tautkan ketiganya dari section demo di `page-katalog.php` (sekarang hanya menaut tema-01).
  **Selesai bila:** ketiga URL `/u/demo-tema-0{1,2,3}/` → 200 dan tertaut dari katalog + form.

- [ ] **P0.3** **Aset `og:image` default** *(sisa eks-T1.13/T1.14)*
  **Kenapa:** preview share WhatsApp adalah etalase produk, dan WA adalah kanal distribusi utama (reseller membagikan katalog; tamu membagikan undangan). Sekarang: katalog **tidak punya** `og:image` sama sekali, dan `functions.php:190` hanya mencetak `og:image` bila `galeri[0]` ada — sementara WF-02 sengaja mengabaikan galeri untuk paket **Hemat**, sehingga **seluruh undangan Hemat** (paket volume, 99rb) di-share tanpa gambar.
  **Langkah:** buat 4 aset 1200×630 WebP/JPEG ≤ 300 KB (1 katalog + 1 per tema), simpan di uploads; tambahkan fallback di `functions.php` (`og:image` = galeri[0] → aset default tema) dan `og:image` statis di `page-katalog.php`.
  **Selesai bila:** share `/` dan `/u/demo-tema-0{1,2,3}/` ke WA menampilkan gambar; undangan paket Hemat tanpa foto tetap punya `og:image`.

- [ ] **P0.4** **Tutup kebocoran username lewat sitemap** *(temuan baru — keamanan)*
  **Kenapa:** `https://harih.id/wp-sitemap-users-1.xml` menyiarkan `n8nbot` dan `hiharih-id` ke publik. `hardening.php` sudah menutup REST `/wp/v2/users` dan me-404-kan arsip author, **tapi provider `users` di sitemap masih terbuka** — username bot pemegang Application Password (jalur n8n membuat post & upload media) jadi target brute force yang sudah diketahui penyerang. Limit-login melindungi, tapi separuh kredensial seharusnya tidak gratis.
  **Langkah:** tambah `add_filter('wp_sitemaps_add_provider', …)` di `wp-content/mu-plugins/undangan-core/hardening.php` untuk membuang provider `users`; tambahkan check baru di `scripts/cek-live.sh` (sitemap index tidak memuat `wp-sitemap-users`, dan URL langsungnya 404).
  **Selesai bila:** `/wp-sitemap-users-1.xml` → 404 dan `cek-live.sh` menguji hal ini.

- [ ] **P0.5** **Bersihkan artefak uji dari produksi**
  **Kenapa:** data uji ikut diproses otomasi yang sudah aktif — WF-05 akan mengirim reminder H-3/H+1 ke baris uji, WF-04 memasukkan komisi palsu ke rekap Senin, dan `/hello-world/` (post bawaan WordPress) tayang serta terindeks di situs komersial.
  **Langkah:** hapus order #29 & #40 (WC), undangan `raka-sela-e998` & `raka-solehah-d445` beserta media-nya, baris terkait di sheet `orders` & `komisi`, dan post `hello-world`. Sebagian besar bisa via WP-CLI dari sini.
  **Selesai bila:** sheet `orders`/`komisi` hanya berisi data riil, `/hello-world/` → 404, tidak ada undangan uji tersisa.

- [ ] **P0.6** **Kurasi library musik** *(eks-T1.15 — keputusan 2026-08-03: kurasi, bukan hapus klaim)*
  **Kenapa:** "musik latar instrumental" dijual di **ketiga** paket (katalog, deskripsi produk WC) dan diatur di S&K §7, tapi `harih_musik_library()` (`functions.php:65`) masih kosong — form isi data hanya menulis "akan ditambahkan tim kami". Fitur terjual tapi belum ada = beban CS tiap order + risiko klaim.
  **Langkah:** pilih 5–10 track instrumental berlisensi komersial (Pixabay Music / Uppbeat / pembelian sekali bayar), **arsipkan bukti lisensinya** di folder terpisah (bukan di repo publik), host file di `wp-content/uploads/musik/`, isi array `harih_musik_library()`. Dropdown di form muncul otomatis begitu array terisi.
  **Selesai bila:** form isi data menampilkan dropdown musik, 1 undangan uji memutar musik pilihan di HP, bukti lisensi tersimpan.

---

## P1 — Risiko yang bisa mematikan bisnis

*Kerjakan di minggu pertama setelah launch, idealnya sebelum.*

- [ ] **P1.1** **Backup repo → GitHub private** *(keputusan 2026-08-03)*
  **Kenapa:** seluruh kode, 8 workflow JSON, script deploy, dan dokumentasi hanya ada di Mac ini — `git remote -v` kosong. Server hanya menyimpan hasil deploy, bukan riwayat git. Disk rusak = mulai dari nol.
  **Langkah:** `gh repo create harih --private --source . --push`. Verifikasi ulang bahwa `vps/.env` & `vps/google-sa.json` tidak ikut (`git ls-files | grep -E 'env$|sa\.json'` harus kosong — sudah diperiksa bersih hari ini). Lalu **simpan terpisah di password manager**: `N8N_ENCRYPTION_KEY` (tanpa ini arsip backup n8n tidak bisa di-restore — disyaratkan runbook §9) dan seluruh isi `vps/.env`.
  **Selesai bila:** `git push` berhasil, repo private berisi commit terakhir, tidak ada rahasia di dalamnya, dan kedua rahasia tersimpan di password manager.

- [ ] **P1.2** **Uji restore backup 1×** *(sisa eks-T4.2)*
  **Kenapa:** backup mingguan sudah jalan 2× dan memverifikasi integritas (`gunzip -t` + ambang ukuran), tapi **belum pernah di-restore**. Backup yang tak pernah diuji dianggap tidak ada.
  **Langkah:** ambil dump terbaru dari `/opt/harih/backups/db/`, restore ke database kosong (lokal/staging, **bukan** produksi), pastikan tabel `wp_posts` berisi undangan; `tar -tzf` arsip WAHA & n8n untuk memastikan isinya utuh.
  **Selesai bila:** restore DB sukses & terverifikasi isinya; langkahnya dicatat di runbook §9 sebagai "sudah diuji <tanggal>".

- [ ] **P1.3** **Uptime monitoring eksternal** *(eks-T4.3)*
  **Kenapa:** **blind spot nyata** — WF-07 (monitor sesi WAHA) dan WF-08 (monitor webhook WC) berjalan sebagai cron **di dalam n8n**. Kalau n8n sendiri mati, kedua monitor mati bersamanya dan tidak ada yang memberi tahu siapa pun. Satu-satunya pemeriksaan eksternal saat ini adalah `cek-live.sh` yang dijalankan manual.
  **Langkah:** UptimeRobot (gratis) → 3 monitor: `https://harih.id/` (keyword "paket"), `https://n8n.harih.id/healthz`, dan heartbeat/keyword untuk backup mingguan. Alert ke `hi@harih.id` + WA owner.
  **Selesai bila:** ketiga monitor hijau dan uji matikan-sebentar memicu alert yang benar-benar diterima.

- [ ] **P1.4** **Pin tag image Docker**
  **Kenapa:** `vps/docker-compose.traefik.yml` masih memakai `n8nio/n8n:latest` dan `devlikeapro/waha:latest` dengan `restart: unless-stopped`. Proyek ini sudah dua kali tertampar perubahan perilaku n8n (penjodohan credential by-name, `$env` diblokir default) — reboot host atau `docker compose pull` bisa menaikkan versi diam-diam dan mematikan pipeline order tanpa perubahan kode apa pun.
  **Langkah:** catat versi yang sekarang berjalan (`docker compose images`), ganti kedua `latest` dengan tag eksplisit di compose repo **dan** `/opt/harih/docker-compose.yml`. Update jadi tindakan sadar: `pull` → uji `cek-live.sh` → commit tag baru.
  **Selesai bila:** tidak ada `:latest` di compose mana pun; versi terpasang tercatat di commit.

- [ ] **P1.5** **Perbaiki drift `scripts/setup-hostinger.sh`**
  **Kenapa:** script membuat `n8nbot` dengan `--role=editor` (baris 27), padahal produksi memakai **`shop_manager`** — editor tidak boleh mengelola order via WC REST, jadi menjalankan ulang script saat rebuild akan menghasilkan sistem yang gagal di langkah "set order completed". Script pemulihan bencana harus mencerminkan produksi.
  **Langkah:** ubah role ke `shop_manager`, tambahkan komentar alasannya; sekalian sisir sisa script terhadap kondisi server sekarang (plugin Duitku, langkah LiteSpeed, cron hPanel).
  **Selesai bila:** menjalankan script di instalasi bersih menghasilkan konfigurasi yang identik dengan produksi.

- [ ] **P1.6** 👤 **Review runbook + finalisasi angka kebijakan** *(sisa eks-T4.9)*
  **Kenapa:** [`docs/runbook.md`](./runbook.md) adalah pegangan owner saat sistem rusak, tapi belum pernah dibaca-ulang oleh owner dan beberapa angka masih default: SLA revisi, tarif revisi berbayar untuk paket Hemat, batas hari pengajuan refund.
  **Selesai bila:** runbook dibaca dari atas ke bawah, angka kebijakan final, dan angka yang sama muncul konsisten di S&K + Kebijakan Refund.

---

## P2 — Kualitas produk & fondasi pertumbuhan

- [ ] **P2.1** **Masa aktif otomatis** *(eks-T3.13 — keputusan 2026-08-03: terapkan)*
  **Kenapa:** masa aktif H+7 / H+30 / 1 tahun tertulis di katalog, deskripsi produk WC, **dan S&K §4** ("halaman dinonaktifkan dan media dapat dihapus") — tanpa mekanisme apa pun. Dua akibat: (a) disk & inodes hosting (10 GB / 200rb) tumbuh selamanya, ±6 MB per undangan; (b) "aktif 1 tahun" tidak jadi pembeda Premium karena paket Hemat pun hidup abadi — upsell tergerus.
  **Langkah:** tambah cabang di WF-05 (titik pasangnya sudah ditandai di node `Susun Pesan Harian`): baca `tgl_acara` + `paket` → lewat masa aktif → `PATCH /wp/v2/undangan/{id}` status `draft`; media milik Hemat & Favorit dihapus pada H+90. Beri pesan WA H-3 sebelum nonaktif ("perpanjang lewat CS") supaya tidak mengagetkan.
  **Selesai bila:** baris uji dengan tanggal dimundurkan membuat undangan jadi draft tepat sesuai paket, dan media H+90 terhapus.

- [ ] **P2.2** 👤 **QA perangkat riil** *(eks-T4.6)*
  iPhone Safari & Android Chrome: autoplay musik setelah tap, tombol salin rekening, upload foto HEIC dari galeri, preview share WA (ingat WA meng-cache preview per URL — pakai URL baru saat menguji ulang). Perilaku Safari sering berbeda dari emulator.

- [ ] **P2.3** **QA checklist §13 formal** *(eks-T4.7)*
  Jalankan `scripts/cek-live.sh` (perluas dulu: check sitemap-users dari P0.4 dan keberadaan `og:image` dari P0.3) + sisa langkah manual di checklist §13 blueprint dan daftar QA tambahan (bagian bawah dokumen ini). Banyak yang sudah de-facto lolos saat uji T1.18 — yang perlu adalah satu putaran formal dan tercatat.

- [ ] **P2.4** **SEO dasar**
  **Kenapa:** judul front page sekarang literal `harih.id` — tanpa kata kunci apa pun di hasil Google; tidak ada meta description; `/isi-data/` masuk sitemap padahal 403 tanpa token; `/shop/` terindeks dan bersaing dengan katalog untuk kata kunci yang sama.
  **Langkah:** isi tagline situs + judul/description khusus di `page-katalog.php`; keluarkan halaman utilitas dari sitemap; putuskan `noindex` untuk `/shop/` (halaman produk WC boleh tetap terindeks).
  **Selesai bila:** `<title>` dan meta description katalog memuat "undangan digital", sitemap hanya berisi halaman yang memang untuk publik.

- [ ] **P2.5** **Analytics + Search Console** *(eks-T4.10)*
  Tanpa data, kebocoran funnel (katalog → checkout → isi data → terbit) hanya bisa ditebak, dan keputusan v2 §15 jadi tebakan. Plausible/GA4 + verifikasi Search Console (pastikan katalog terindeks & undangan tetap `noindex`).

- [ ] **P2.6** **Polish katalog + FAQ** *(eks-T3.8)* — konversi & pengurangan beban CS; halaman "Cara Pesan" terpisah bila perlu.

- [ ] **P2.7** 👤 **Review visual tema-02 & tema-03 di perangkat nyata** *(sisa eks-T3.5)* — keduanya sudah live di kode tapi belum pernah dilihat owner di HP; sekalian saat P0.2 membuat demo-nya.

---

## P3 — Go-to-market (owner)

- [ ] **P3.1** 👤 Rekrut 3 reseller pertama + soft launch *(eks-T4.11)*
- [ ] **P3.2** 👤 Review gaya bahasa seluruh pesan otomatis di [`docs/copywriting-pesan.md`](./copywriting-pesan.md) *(sisa eks-T2.23)*
- [ ] **P3.3** 👤 Kebijakan nomor WA bisnis: jaga tetap aktif & wajar, jangan logout dari HP, hindari blast ke nomor tak dikenal — sesi banned = seluruh kanal delivery WA mati

---

## QA tambahan (pelengkap §13 — dipakai di P2.3)

- [ ] `GET /wp-json/wp/v2/undangan` tanpa auth → ditolak *(terverifikasi 401, 2026-08-03)*
- [ ] `wp-sitemap-users-1.xml` → 404 (P0.4)
- [ ] CPT `undangan` tidak muncul di sitemap; single undangan `noindex` *(terverifikasi ✓)*
- [ ] Simpan ulang webhook WC (memicu ping) → tidak ada error di n8n, webhook tetap `active`
- [ ] Ubah `paket=premium` di URL/form → undangan tetap sesuai paket yang dibayar
- [ ] Upload via curl ke webhook: file ke-11 / >2 MB / non-gambar → ditolak WF-02
- [ ] Foto HEIC dari iPhone → masuk galeri sebagai JPEG, orientasi benar
- [ ] Submit form kedua saat WF-02 berjalan → 409, tidak ada undangan ganda
- [ ] Matikan n8n 10 menit saat ada order → order tetap terproses via rekonsiliasi WF-08
- [ ] Nomor `08xx`, `+62 8xx`, `628xx` di checkout → WA terkirim ke semua format
- [ ] Share link undangan **dan katalog** di WA → preview OG (judul + gambar) tampil (P0.3)
- [ ] Undangan yang acaranya sudah lewat → state pasca-acara, bukan countdown negatif
- [ ] Reseller memakai kupon sendiri → terdeteksi/tertolak sesuai kebijakan
- [ ] Port 3000 VPS tidak terjangkau dari luar *(terverifikasi ✓)*; UI n8n hanya via HTTPS + auth

---

## Backlog v2 (setelah 100 order pertama — §15, §10)

- [ ] Occasion baru dengan duplikasi tema: khitanan, aqiqah, wisuda (musiman Mei–September), e-card Lebaran
- [ ] Add-on WA blast ke daftar tamu — Rp 50rb/200 tamu (n8n loop, delay acak 20–45 detik, hanya daftar dari customer)
- [ ] Add-on buku tamu QR check-in — Rp 100rb
- [ ] Amplop digital ter-escrow via QRIS dengan fee platform 2–5% *(perubahan model bisnis paling bernilai)*
- [ ] Migrasi log operasional Google Sheets → Postgres di VPS *(saat > 500 order/bulan — sekaligus menuntaskan sisa race condition idempotency Sheets)*
- [ ] Dashboard reseller; dashboard self-service customer untuk edit undangan
- [ ] Tema premium eksklusif Premium *(sudah dijanjikan "menyusul" di deskripsi produk)*
- [ ] Custom domain per undangan · multi-bahasa · tema builder drag-and-drop *(non-goals v1 §15)*

---

**Known limitation (diterima untuk MVP):** idempotency Google Sheets tidak atomik — dimitigasi topic Action `woocommerce_order_status_processing` + pola append-then-verify di WF-01. Sisa risiko race sangat kecil dan hilang total saat migrasi Postgres di v2.
