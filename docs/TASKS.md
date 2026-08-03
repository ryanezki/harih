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
| Katalog, 4 halaman legal, landing reseller → 200 | **Musik** dijanjikan di pricing ketiga paket, belum ada *(P0.6)* |
| Demo ketiga tema → 200, skin terbedakan ✓ *(P0.2)* | Backup mingguan belum pernah diuji restore *(P1.2)* |
| Foto stok berlisensi + `og:image` berbrand per tema ✓ *(P0.3)* | Duitku production belum diajukan — belum bisa terima uang *(P0.1)* |
| `wp-sitemap-users-1.xml` → **404**, username tertutup ✓ *(P0.4)* | Kontras cover: aman untuk foto terang ✓ — tapi belum diuji di HP nyata *(P2.2)* |
| `/wp-json/wp/v2/undangan` → **401** tanpa auth (meta tidak bocor) | `N8N_ENCRYPTION_KEY` & `vps/.env` belum di password manager *(P1.1)* |
| Undangan `noindex` ✓ · cache LiteSpeed **hit** pada `?to=` ✓ | QA perangkat riil (iPhone/Android) belum dijalankan *(P2.2)* |
| Kode ter-backup: `github.com/ryanezki/harih` (private) ✓ *(P1.1)* | Monitor n8n hanya hidup **di dalam** n8n — tak ada pengawas eksternal *(P1.3)* |
| Image Docker terpin: n8n 2.29.10 · WAHA 2026.6.2 ✓ *(P1.4)* | Analytics & Search Console belum ada — funnel tak terukur *(P2.5)* |
| Judul/description SEO & sitemap bersih ✓ *(P2.4)* | — |
| Masa aktif ditegakkan otomatis, demo dikecualikan ✓ *(P2.1)* | — |
| Produksi **nol data uji** ✓ *(P0.5)* — sheet tinggal header | — |
| `xmlrpc.php` → 403 · port 3000 WAHA tidak terjangkau publik ✓ | — |
| n8n `/healthz` → 200 · WF aktif · smoke test **21/21 hijau** | — |

**Blocker tunggal untuk menerima uang riil:** akun Duitku production belum diajukan (P0.1).

**Akses:** Hostinger `ssh -p 65002 u803921702@147.93.80.20` (WP: `domains/harih.id/public_html`) · VPS `ssh root@31.97.50.197` (`/opt/harih`) · rahasia: `vps/.env` + `vps/google-sa.json` (lokal, gitignored) = cermin server · aksi owner: [`docs/panduan-manual.md`](./panduan-manual.md) · operasional: [`docs/runbook.md`](./runbook.md) · import n8n: [`n8n/workflows/README.md`](../n8n/workflows/README.md).

---

## P0 — Blocker pendapatan & cacat yang dilihat customer

*Selesaikan sebelum order riil pertama masuk.*

- [ ] **P0.1** 👤 **Duitku production** *(eks-T0.11 → T4.8 → T4.12)*
  **Kenapa:** blocker **satu-satunya** untuk menerima uang riil. Selama ini tidak beres, semua item lain hanya persiapan.
  **Langkah:** (1) isi formulir aplikasi merchant personal di dashboard Duitku — situs live + 4 halaman legal + katalog sudah siap direview; (2) pantau approval (butuh berhari-hari); (3) setelah approve: WooCommerce → Settings → Payments → Duitku → ganti mode **Production** + Merchant Code & API Key production; (4) buat produk uji tersembunyi Rp 10.000 (`catalog_visibility=hidden`), pesan dari HP, verifikasi undangan sampai di WA < 15 menit, lalu hapus produknya.
  **Selesai bila:** 1 order riil Rp 10.000 lolos end-to-end dari HP dan dana masuk ke rekening merchant.

- [x] **P0.2** **Demo tema-02 & tema-03** *(eks-T3.6)* → **SELESAI 2026-08-03**
  Bug live tertutup: `page-isi-data.php:92` menautkan `/u/demo-{tema}/` untuk ketiga tema, tapi tema-02 & tema-03 masih 404 — customer yang sudah bayar dapat halaman error tepat saat memilih tema.
  **Hasil:** `scripts/buat-demo.sh` (idempotent by slug, bisa dijalankan ulang setelah rebuild) membuat `demo-tema-02` (Bima & Ayu) & `demo-tema-03` (Damar & Kirana); katalog kini menaut **ketiga** demo, daftarnya diturunkan dari `undangan_get_temas()` sehingga tema baru otomatis ikut. Ketiga URL 200, skin CSS benar per tema, 10 section, `og:image` ada. Diverifikasi visual di viewport HP: tema-02 & tema-03 tampil jelas berbeda.
  **Keputusan yang dicatat:** isi ketiga demo sengaja identik (selain nama & lokasi) supaya yang terbandingkan adalah skin-nya, dan semuanya paket `premium` supaya seluruh section terlihat. Konsekuensi: karena `cover_image` = foto pertama galeri, cover demo berfoto — ornamen khas cover tanpa foto (arch tema-02, bingkai emas tema-03) hanya muncul di `preview/tema-0{2,3}.html` dan pada undangan customer yang tidak mengunggah foto.

- [x] **P0.3** **Aset visual: foto demo + `og:image` default** *(sisa eks-T1.13/T1.14)* → **SELESAI 2026-08-04**
  Dua masalah, satu akar: foto demo ternyata **placeholder Picsum** (judul medianya literal "Picsum ID: 340") — batu berlumut sebagai sampul undangan pernikahan; dan `og:image` kosong di katalog **dan** di seluruh undangan paket Hemat (WF-02 sengaja mengabaikan galeri untuk hemat, sementara OG hanya dicetak bila `galeri[0]` ada).
  **Keputusan owner:** foto stok berlisensi komersial, **detail tanpa wajah**. Alasannya dicatat di `docs/aset-lisensi.md`: Pexels/Unsplash mengizinkan pemakaian komersial dari sisi hak cipta tapi **tidak menyediakan model release** — memakai foto pernikahan berwajah dikenali untuk mengiklankan jasa berbayar persis kasus yang membutuhkannya. Foto detail menihilkan risiko itu sekaligus tidak bersaing dengan tipografi nama mempelai.
  **Hasil:** 3 foto Pexels (tangan+cincin+buket, detail gaun, cincin di sepatu) ≤ 300 KB + 4 kartu OG 1200×630 berbrand per tema, dibangun `scripts/buat-aset-og.py` (kartu HTML memakai tipografi & token warna asli tiap tema → Chrome headless). Semua aset tinggal **di dalam tema** (`themes/harih/aset/`) supaya ikut version control & rsync, bukan di media library yang bisa terhapus. `harih_og_default()` memberi fallback per tema; attachment Picsum lama dihapus.
  **Verifikasi:** `og:image` katalog → kartu katalog; demo → foto galerinya; **jalur fallback diuji sungguhan** dengan undangan Hemat sementara tanpa galeri bertema tema-02 → benar mendapat `og-tema-02.jpg` (post uji lalu dihapus).
  **Dua cacat yang ketahuan gara-gara foto baru — sudah diperbaiki:**
  - **Kontras cover.** Foto terang (gaun putih, buket, outdoor siang) membuat teks kecil di cover hilang; eyebrow tema-01 & tema-03 memakai emas mid-tone yang lenyap total di atas putih. Diperbaiki di `shared/undangan.css` **hanya untuk cover berfoto**: bayangan teks + eyebrow memakai tinta cover terang (perilaku yang sudah dipakai tema-02 by design). Ini mengenai **setiap customer** yang mengunggah foto terang, bukan demo saja.
  - **`HARIH_VERSION` tidak pernah dinaikkan.** Perbaikan CSS terlihat "tidak berpengaruh" karena cache-buster aset tidak berubah — browser & LiteSpeed tetap menyajikan berkas lama meski file di server sudah baru. Dinaikkan ke `0.2.1` dan aturannya dicatat di README + komentar konstanta.

- [x] **P0.4** **Tutup kebocoran username lewat sitemap** *(temuan baru — keamanan)* → **SELESAI 2026-08-03**
  `wp-sitemap-users-1.xml` menyiarkan `n8nbot` (pemegang Application Password n8n) dan `hiharih-id` ke publik — jalur enumerasi yang tersisa setelah REST `/wp/v2/users` & arsip author ditutup di T1.10/T1.11.
  **Hasil:** provider `users` dibuang via `wp_sitemaps_add_provider`, **plus** 404 eksplisit untuk query var `sitemap=users` — rewrite rule-nya tetap terdaftar meski provider dibuang, dan tanpa itu WP membalas 200 berisi HTML biasa (soft-404 yang bisa terindeks sebagai duplikat homepage, dan ikut mencetak tautan `/author/` dari byline post). 2 check baru di `cek-live.sh`.
  **Verifikasi:** `wp-sitemap-users-1.xml` → 404 tanpa username · `/author/hiharih-id/` → 404 · sitemap index tinggal pages + products + product_cat · smoke test 21/21 hijau.

- [x] **P0.5** **Bersihkan artefak uji dari produksi** → **SELESAI 2026-08-03**
  Data uji ikut diproses otomasi yang sudah aktif: baris order #29 ber-`tgl_acara` 2026-09-12 akan memicu reminder H-3 WF-05 ke nomor owner pada 9 September.
  **Dihapus permanen** (setelah verifikasi backup DB 2026-08-01 + mirror uploads ada): 2 order WC (#29, #40 — via `wc_get_order()->delete(true)`, HPOS), 2 undangan uji (`raka-sela-e998`, `raka-solehah-d445`), 3 media milik order #29 (2 foto + QRIS), 2 baris tab `orders`, serta post & komentar sample bawaan WordPress.
  **Tersisa di produksi: nol data uji** — 3 undangan demo, 3 foto demo + placeholder WC, `wp_wc_orders` kosong, tab `orders`/`komisi`/`resellers` tinggal header. Tidak ada file yatim di uploads. Demo tetap 200 dengan galeri & `og:image` utuh; smoke test 21/21.
  **Catatan alat:** tab sheet bisa dibaca/diedit dari CLI tanpa n8n — service account `vps/google-sa.json` + JWT RS256 yang ditandatangani `openssl` (tanpa library Google). Berguna untuk skenario pemulihan di runbook §4.

- [ ] **P0.6** **Kurasi library musik** *(eks-T1.15 — keputusan 2026-08-03: kurasi, bukan hapus klaim)*
  **Kenapa:** "musik latar instrumental" dijual di **ketiga** paket (katalog, deskripsi produk WC) dan diatur di S&K §7, tapi `harih_musik_library()` (`functions.php:65`) masih kosong — form isi data hanya menulis "akan ditambahkan tim kami". Fitur terjual tapi belum ada = beban CS tiap order + risiko klaim.
  **Langkah:** pilih 5–10 track instrumental berlisensi komersial (Pixabay Music / Uppbeat / pembelian sekali bayar), **arsipkan bukti lisensinya** di folder terpisah (bukan di repo publik), host file di `wp-content/uploads/musik/`, isi array `harih_musik_library()`. Dropdown di form muncul otomatis begitu array terisi.
  **Selesai bila:** form isi data menampilkan dropdown musik, 1 undangan uji memutar musik pilihan di HP, bukti lisensi tersimpan.

---

## P1 — Risiko yang bisa mematikan bisnis

*Kerjakan di minggu pertama setelah launch, idealnya sebelum.*

- [ ] **P1.1** **Backup repo & rahasia** *(keputusan 2026-08-03 — kode ✅, rahasia masih sisa)*
  ✅ **Kode ter-backup**: <https://github.com/ryanezki/harih> — **private**, 36 commit, `origin/main` identik dengan HEAD lokal. Sebelum push, seluruh **riwayat** disisir (bukan cuma kondisi sekarang): `vps/.env` & `vps/google-sa.json` tidak pernah tercatat; scan semua blob untuk pola rahasia (`xkeysib-`, `ck_`/`cs_`, PEM private key, Google API key) → nihil; blok `credentials` di workflow JSON hanya berisi ID + nama referensi. Diverifikasi lagi via API setelah push: kedua berkas rahasia memang tidak ada di remote.
  **Sisa (👤, tidak bisa saya kerjakan — menyangkut nilai rahasia):** simpan di password manager (1) `N8N_ENCRYPTION_KEY` — tanpa ini arsip backup n8n **tidak bisa di-restore**, dan nilainya sekarang hanya ada di VPS yang sama dengan backupnya (runbook §9 mensyaratkan disimpan terpisah); (2) seluruh isi `vps/.env` — satu-satunya salinan ada di disk Mac ini dan di VPS.
  **Selesai bila:** kedua rahasia tersimpan di password manager. Rutinitas baru: `git push` setiap selesai sesi.

- [ ] **P1.2** **Uji restore backup 1×** *(sisa eks-T4.2)*
  **Kenapa:** backup mingguan sudah jalan 2× dan memverifikasi integritas (`gunzip -t` + ambang ukuran), tapi **belum pernah di-restore**. Backup yang tak pernah diuji dianggap tidak ada.
  **Langkah:** ambil dump terbaru dari `/opt/harih/backups/db/`, restore ke database kosong (lokal/staging, **bukan** produksi), pastikan tabel `wp_posts` berisi undangan; `tar -tzf` arsip WAHA & n8n untuk memastikan isinya utuh.
  **Selesai bila:** restore DB sukses & terverifikasi isinya; langkahnya dicatat di runbook §9 sebagai "sudah diuji <tanggal>".

- [ ] **P1.3** **Uptime monitoring eksternal** *(eks-T4.3)*
  **Kenapa:** **blind spot nyata** — WF-07 (monitor sesi WAHA) dan WF-08 (monitor webhook WC) berjalan sebagai cron **di dalam n8n**. Kalau n8n sendiri mati, kedua monitor mati bersamanya dan tidak ada yang memberi tahu siapa pun. Satu-satunya pemeriksaan eksternal saat ini adalah `cek-live.sh` yang dijalankan manual.
  **Langkah:** UptimeRobot (gratis) → 3 monitor: `https://harih.id/` (keyword "paket"), `https://n8n.harih.id/healthz`, dan heartbeat/keyword untuk backup mingguan. Alert ke `hi@harih.id` + WA owner.
  **Selesai bila:** ketiga monitor hijau dan uji matikan-sebentar memicu alert yang benar-benar diterima.

- [x] **P1.4** **Pin tag image Docker** → **SELESAI 2026-08-03**
  `latest` + `restart: unless-stopped` berarti `docker compose pull` berikutnya bisa menaikkan versi diam-diam; proyek ini sudah dua kali tertampar perubahan perilaku n8n (penjodohan credential by-name saat import, `$env` diblokir default di Code node).
  **Hasil:** dipin ke **n8n 2.29.10** & **WAHA 2026.6.2** (versi yang memang sedang berjalan — jadi tidak ada perubahan perilaku) di kedua varian compose repo **dan** `/opt/harih/docker-compose.yml`; digest image dicatat di komentar untuk reproduksi persis.
  **Catatan:** container **tidak** di-recreate — tidak ada gunanya karena versinya identik, dan recreate berarti WAHA memuat ulang sesi WhatsApp di sistem produksi. Pin sudah aktif: `docker compose pull/up -d` berikutnya membaca tag yang dipin, dan reboot host hanya menyalakan ulang container yang sudah ada. Verifikasi: `docker compose config` menampilkan kedua tag, sesi WAHA tetap `WORKING`, n8n `/healthz` 200.

- [x] **P1.5** **Perbaiki drift `scripts/setup-hostinger.sh`** → **SELESAI 2026-08-03**
  Script membuat `n8nbot` dengan `--role=editor`, padahal produksi memakai `shop_manager` (dikonfirmasi di server: user ID 2 = `shop_manager`). Editor tidak boleh mengelola order via WC REST → rebuild dari script menghasilkan sistem yang gagal saat WF-02 menset order `completed`. Sudah diperbaiki + alasannya dikomentari di script.

- [ ] **P1.6** 👤 **Review runbook + finalisasi angka kebijakan** *(sisa eks-T4.9)*
  **Kenapa:** [`docs/runbook.md`](./runbook.md) adalah pegangan owner saat sistem rusak, tapi belum pernah dibaca-ulang oleh owner dan beberapa angka masih default: SLA revisi, tarif revisi berbayar untuk paket Hemat, batas hari pengajuan refund.
  **Selesai bila:** runbook dibaca dari atas ke bawah, angka kebijakan final, dan angka yang sama muncul konsisten di S&K + Kebijakan Refund.

---

## P2 — Kualitas produk & fondasi pertumbuhan

- [x] **P2.1** **Masa aktif otomatis** *(eks-T3.13)* → **SELESAI 2026-08-04**
  Masa aktif H+7 / H+30 / 1 tahun tertulis di katalog, deskripsi produk WC, **dan S&K §4** — tanpa mekanisme apa pun. Akibatnya: disk & inodes tumbuh selamanya, dan "aktif 1 tahun" bukan pembeda Premium karena paket Hemat pun hidup abadi.
  **Keputusan arsitektur — penegakan di WP, bukan n8n:** sumber kebenaran `paket` + `tanggal_resepsi` ada di meta post itu sendiri, jadi tidak butuh REST/auth/nomor baris sheet yang bisa drift; kueri `post_status=publish` membuatnya idempoten dengan sendirinya; dan bila satu hari terlewat (n8n mati, cron gagal) ia menyusul sendiri besoknya — bukan aksi sekali-jalan yang hilang kalau momennya lewat. WF-05 tetap memegang **peringatan WA 3 hari sebelum nonaktif** (butuh nomor WA dari sheet).
  **Hasil:** `mu-plugins/undangan-core/masa-aktif.php` + cron WP harian 03:00 WIB. Undangan **demo dikecualikan** (`order_id` kosong/`demo`) — tanpa ini katalog akan menautkan halaman mati. Link kedaluwarsa membalas **410 berpesan** + tautan Kontak, bukan 404 telanjang. Tanggal resepsi tak terbaca → tidak pernah dinonaktifkan (lebih baik menyisakan halaman hidup daripada mematikan undangan customer karena datanya tak terparse). Prosedur aktifkan-kembali masuk runbook §7b.
  **Verifikasi end-to-end di produksi:** undangan uji hemat kedaluwarsa → jadi `draft`, URL-nya 410 berpesan; undangan favorit yang belum lewat → tetap 200; ketiga demo → tetap 200; jalan kedua kali → nihil (idempoten); cron terjadwal (`undangan_cek_masa_aktif`, harian). Post uji dihapus.
  **Catatan scope:** penghapusan media H+90 **tidak** termasuk di sini — itu memang sudah tercatat sebagai item backlog v2 ("arsip otomatis undangan kedaluwarsa"), dan tidak mendesak selagi volume masih nol.

- [ ] **P2.2** 👤 **QA perangkat riil** *(eks-T4.6)*
  iPhone Safari & Android Chrome: autoplay musik setelah tap, tombol salin rekening, upload foto HEIC dari galeri, preview share WA (ingat WA meng-cache preview per URL — pakai URL baru saat menguji ulang). Perilaku Safari sering berbeda dari emulator.

- [x] **P2.3** **QA checklist §13 formal** *(eks-T4.7)* → **SELESAI 2026-08-04** *(bagian yang bisa diuji tanpa perangkat/akun; sisanya di P2.2 & P0.1)*
  Putaran formal ini **menemukan 3 cacat live** yang tidak terlihat dari smoke test — dua di antaranya mematikan fitur yang dijual di ketiga paket:
  1. **Countdown tidak pernah berjalan di undangan mana pun.** `window.UNDANGAN.target` terisi benar, tapi `countdown.php` membaca `$args['target']` yang tidak pernah ada di array `$undangan`, sementara `undangan.js` membaca atribut `data-target` — jadi `data-target` selalu kosong dan hitungan tidak pernah mulai. Diperbaiki dengan satu helper `harih_target_countdown()` yang dipakai kedua konsumen.
  2. **Atribut `hidden` dikalahkan CSS.** `.countdown-grid { display: grid }` (selektor kelas) mengalahkan `[hidden]` bawaan browser, sehingga state pasca-acara menampilkan "0 0 0 0" berdampingan dengan pesan "acara telah berlangsung". Ditambahkan `[hidden] { display: none !important }` di ketiga stylesheet — ini juga menutup kelas bug yang sama pada panel sukses form, progress bar, dan tombol musik.
  3. **Daftar ucapan ter-cache browser 7 hari.** Endpoint GET RSVP mengirim `Cache-Control: public, max-age=604800` (setelan Browser Cache LiteSpeed), jadi tamu yang membuka ulang undangan tidak melihat ucapan baru selama seminggu. Header 60 detik kini dikirim eksplisit dari `rest.php`.
  **Yang diverifikasi lulus:** RSVP end-to-end (kirim → tersimpan → tampil di daftar), rate limit CGNAT (kiriman kedua → 429), honeypot (sukses palsu, tidak masuk DB), cache LiteSpeed endpoint RSVP (`hit`), normalisasi nomor 8 format (`08xx`/`+62 8xx`/`628xx`/spasi/strip → `628…`; nomor rumah & terlalu pendek ditolak), countdown berjalan, state pasca-acara, cover tanpa foto (ornamen khas tema tampil), masa aktif (P2.1), plus 21 check `cek-live.sh`. Artefak QA dibersihkan.
  **Belum bisa diuji tanpa order riil / perangkat:** enforcement paket via curl ber-token, tolak file ke-11/>2 MB, submit ganda saat WF-02 jalan, ping webhook WC, HEIC iPhone, preview WA sungguhan → tersisa di P2.2 & P0.1.

- [x] **P2.4** **SEO dasar** → **SELESAI 2026-08-03**
  Judul front page sebelumnya literal `harih.id` — tanpa satu pun kata yang dicari calon customer, dan itu juga judul yang muncul saat link disalin ke luar WhatsApp.
  **Hasil:**
  - `document_title_parts` untuk katalog & landing reseller → "Undangan Digital Otomatis, Mulai Rp 99 Ribu – hariH" dan "Jadi Reseller — Komisi 30% Tiap Order – hariH"
  - `<meta name="description">` di katalog (±155 karakter) & landing reseller
  - nama situs `harih.id` → **hariH** (ikut memperbaiki header/footer email WooCommerce) + tagline terisi
  - halaman utilitas (`isi-data`, cart, checkout, my-account, shop) dikeluarkan dari sitemap **dan** di-`noindex`. `/isi-data/` membalas 403 tanpa token — mendaftarkannya di sitemap hanya melahirkan error Search Console; `/shop/` duplikat katalog front page. Halaman produk tetap terindeks (bisa punya nilai cari sendiri).
  **Verifikasi:** sitemap halaman tinggal 6 URL konten asli (katalog, reseller, 4 legal); index tinggal pages + products + product_cat; smoke test 21/21 hijau.

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
