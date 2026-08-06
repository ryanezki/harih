# Evaluasi ide "Konsep Desain Website Gen Z" — dan rencana penerapannya

**Sumber:** masukan teman owner (2026-08-06) · **Status:** analisis + rencana, belum ada kode yang ditulis
**Pembanding:** kondisi live per `TASKS.md` checkpoint 2026-08-06 (`HARIH_VERSION 2.2.1`)

> Dokumen ini menjawab dua pertanyaan: **mana yang bisa diterapkan**, dan **apa persisnya yang berubah kalau diterapkan**. Poin yang saya tolak tetap ditulis lengkap dengan alasannya — supaya kalau nanti owner tidak setuju dengan penolakan saya, yang diperdebatkan adalah alasannya, bukan ingatan.

---

## Ringkasan keputusan

| # | Ide | Putusan | Alasan satu kalimat |
|---|---|---|---|
| 1 | Mobile-first | ✅ **Sudah ada** | Undangan & toko sudah `clamp()`/`auto-fit`, diuji di 375px; sisanya QA perangkat riil (F0.4) |
| 2 | Visual sentris (video/animasi/gambar > teks) | 🟡 **Sebagian** | Sudah di undangan (slider swipe, reveal, partikel); yang kurang justru di **halaman jualan** |
| 3 | Ultra-cepat + swipe | 🟡 **Ukur dulu** | Klaim "cepat" belum pernah diukur; ada satu temuan konkret — halaman undangan masih menarik **Google Fonts** |
| 4 | Bold & dark mode otomatis | 🟢 **Ya untuk halaman toko** · ❌ **Tidak untuk undangan** | Token warna sudah terpusat di `katalog.css` → murah. Tapi tema undangan **adalah produknya** — tidak boleh dibalik sendiri |
| 5 | Bahasa autentik, to the point | ✅ **Sudah jadi kebijakan** | Sudah dieksekusi 2026-08-05 (jangkar "Rp 99 ribu" dibuang, FAQ dibersihkan) |
| 6a | Embed playlist Spotify sebagai musik latar | ❌ **Tidak bisa** | Embed Spotify tidak bisa autoplay, butuh login untuk lagu penuh (kalau tidak: 30 detik), dan berat — ganti: section "Playlist Kami" opsional |
| 6b | Galeri kolase | 🟢 **Ya** | Murni CSS grid, satu meta baru |
| 6c | Maps/Waze akurat | 🟢 **Ya (sebagian sudah ada)** | Peta tersemat sudah ada (FU.2); yang kurang: tombol Waze + koordinat presisi |
| 7 | Video-first invitation (vertikal ala Reels) | 🟡 **Terbatas** | Video **cover** vertikal = ya (fitur Premium). Undangan yang seluruhnya video = tidak — penerimanya termasuk orang tua |
| 8 | RSVP via WhatsApp + n8n + Supabase | ❌ **Jalur masuk ke nomor bisnis** · 🟢 **Ganti: deep link ke WA mempelai** | Ratusan nomor asing menghubungi sesi WAHA = risiko ban yang mematikan **seluruh** kanal delivery. Supabase = datastore ketiga |
| 9 | AI Copywriter Assistant | 🟢 **Ya** | Cocok, murah, dan menutup lubang nyata: pemesan mandek di form (WF-05 sudah harus menagih) |
| 10 | Personalisasi instan (page builder) | ❌ **Builder penuh** · 🟢 **Ganti: "coba nama kalian" di demo** | Builder drag-and-drop sudah ada di Backlog v2 — berminggu-minggu. Versi murahnya justru yang menaikkan konversi |
| 11 | Gifting digital via payment gateway | ❌ **Escrow** · 🟢 **Ganti: deep link e-wallet mempelai** | Menampung uang tamu atas nama mempelai = perilaku agregator/PJP; juga hampir pasti melanggar perjanjian merchant Duitku |

**Hitungan kasar:** dari 11 poin — 2 sudah ada, 5 diterapkan (sebagian dengan bentuk berbeda), 4 ditolak dengan pengganti yang menangkap ~80% nilainya dengan biaya jauh lebih kecil.

---

## Empat batasan yang membentuk seluruh penilaian di atas

Ini bukan keberatan estetis. Empat hal ini yang membuat sebagian ide bagus jadi mahal atau berbahaya **di proyek ini secara spesifik**.

**1. Urutan uang.** `TASKS.md` sudah mengunci disiplin: fitur yang *dijual* digerbang oleh **F0.3 — 10 pembeli asing**. Yang boleh dikerjakan sekarang hanya yang (a) murah, dan (b) langsung menaikkan konversi ke gerbang itu. Sebagian besar ide teman ini adalah **fitur yang dijual**, bukan perbaikan konversi — jadi urutannya penting, bukan cuma isinya.

**2. Desain undangan dibekukan.** Sudah dua putaran evaluasi (owner + eksternal), 17 poin diverifikasi baris per baris, dan catatan proyek menyebut eksplisit: *"jangan mengiterasi visual lagi tanpa diminta"*. Karena itu semua perubahan di bawah dirancang **aditif dan opsional per undangan** — tidak ada undangan lama yang berubah tampilan tanpa diminta.

**3. Sesi WhatsApp itu titik tunggal kegagalan.** WAHA memakai sesi WhatsApp Web. Kalau sesi itu di-ban, yang mati bukan cuma satu fitur: delivery undangan, reminder H-3, welcome kit reseller, notifikasi order, upsell H+3/H+12 — **sembilan workflow** ikut mati. `TASKS.md` sudah mencatat kebijakannya (*"jangan blast ke nomor tak dikenal"*). Ide RSVP-lewat-WA melanggar itu secara langsung.

**4. Penerima undangan bukan hanya Gen Z.** Ini yang paling sering terlewat. Produk cetak yang sedang dibangun ada **justru karena** penerima undangan sebagian besar orang tua & sesepuh — itu alasan tercatat kenapa produknya berubah dari kartu QR jadi undangan lipat. Pembelinya Gen Z; **pembacanya lintas usia**. Jadi: konsep Gen Z diterapkan penuh di halaman **jualan** (yang dibaca pembeli), dan diterapkan selektif di **undangan** (yang dibaca semua orang).

---

## Analisis per poin

### 1. Mobile-first — sudah ada

Undangan dan seluruh halaman toko sudah mobile-first: `clamp()` untuk ukuran, `auto-fit` untuk grid, `flex-wrap`, `scroll-snap`. Redesain beranda 2026-08-06 bahkan **tanpa satu pun media query**. Ukuran tipografi diukur di layar 375px agar nama bulan terpanjang tetap satu baris di ketiga tema.

**Yang benar-benar belum:** pengujian di perangkat fisik — itu **F0.4**, sudah ada checklistnya di `panduan-manual.md`, dan butuh tangan owner. Tidak ada kode yang perlu ditulis.

### 2. Visual sentris — sebagian, dan yang kurang justru di halaman jualan

Sudah ada di undangan: slider galeri swipe (scroll-snap native), reveal ber-stagger, ken-burns, tilt/shine, partikel (tema-03), facade video & peta.

Yang **tidak** ada: bukti visual bergerak di halaman yang dilihat calon pembeli sebelum membayar. Beranda punya carousel tema (statis) — bagus, tapi calon pembeli tidak pernah melihat undangan **bergerak** sebelum ia klik demo.

Penerapannya bukan "tambah video ke mana-mana", melainkan satu hal spesifik: **rekaman layar pendek (5–8 detik, loop, tanpa suara)** dari tiap tema di carousel beranda, dengan `poster` gambar dan `preload="none"`. Ini masuk **G1.7** di bawah — tapi digerbang ke belakang, karena butuh aset yang harus direkam dulu.

### 3. Ultra-cepat — ada satu temuan konkret, sisanya harus diukur

Klaim "cepat" di proyek ini belum pernah diukur dengan angka. Yang sudah benar: font toko self-hosted, facade YouTube & Maps (nol permintaan pihak ketiga sebelum diklik), lazy loading, cache LiteSpeed.

**Temuan konkret:** `harih_tema_fonts()` di `functions.php` masih memuat **Google Fonts** untuk ketiga tema undangan. Halaman toko sudah self-hosted sejak redesain, tapi halaman undangan belum. Artinya: setiap tamu yang membuka undangan menunggu request render-blocking ke `fonts.googleapis.com`, lalu request kedua ke `fonts.gstatic.com`. Di jaringan seluler Indonesia itu bisa 200–600 ms sebelum teks muncul.

Kabar baiknya: **berkas fontnya sudah ada di repo** (`aset/font/CormorantGaramond.ttf`, `Karla.ttf`, `Prata.ttf`, `Manrope.ttf`) — dibawa masuk untuk generator kartu OG. Tinggal dikonversi ke woff2 + di-subset latin. Ini **G1.2**.

⚠️ **Catatan pengukuran:** `cek-live.sh` bisa memunculkan `HTTP 000` palsu dari lingkungan kerja saya (sudah didiagnosis 2026-08-06: hambatan di jalur jaringan saya, bukan di situs). Pengukuran performa harus lewat **PageSpeed Insights** (jalur Google → server, bukan lewat mesin saya), dan diulang per URL sebelum menyimpulkan.

**Swipe-friendly:** galeri sudah swipe. Navigasi seluruh halaman ala Reels (satu section = satu layar, swipe vertikal) **tidak** saya rekomendasikan untuk undangan — ia membuang scroll-reveal yang sudah dibangun, mematahkan tautan langsung ke `#rsvp`, dan menyulitkan pembaca yang ingin membaca ulang alamat gedung. Undangan bukan feed; ia dokumen rujukan yang dibuka berkali-kali.

### 4. Dark mode — ya untuk toko, tidak untuk undangan

**Kenapa toko: murah.** `katalog.css` dipakai **8 halaman** (beranda, `/harga/`, `/satuan/`, `/upsell/`, `/proof/`, `/tamu/`, `/rekap/`, `/jadi-reseller/`) dan seluruh warnanya didefinisikan di satu blok `:root` — 24 token, ditambah 12 `rgba()` bayangan dan 2 hex nyasar yang perlu ditokenkan. Menambahkan palet gelap = menduplikasi blok token itu di dalam `@media (prefers-color-scheme: dark)` + `:root[data-theme="dark"]`. Markup nol berubah, 8 halaman ikut sekaligus. Logo versi putih (`logo-harih-wordmark-putih.png`) juga sudah ada.

**Kenapa bukan undangan.** Tema **adalah barang yang dibeli**. Pembeli memilih "Tema 01 — Botanical Elegan" karena warnanya; membalikkannya jadi gelap karena HP tamu sedang dark mode = mengirimkan produk yang berbeda dari yang dibeli, ke ratusan tamu, tanpa sepengetahuan pembelinya. Lagi pula pilihan gelap **sudah ada sebagai produk**: Tema 03 "Langit Malam".

### 5. Bahasa autentik — sudah jadi kebijakan

Sudah dieksekusi 2026-08-05: jangkar harga dibuang dari lima lokasi, FAQ berhenti mengajari pelanggan bikin kartu QR sendiri, halaman kontak melepas basa-basi korporat. Tidak ada pekerjaan baru; yang tersisa cuma menjaga agar copy baru tidak melenceng.

### 6a. Spotify sebagai musik latar — tidak bisa, dan ini teknis

Empat penghalang, berurutan dari yang paling mematikan:

1. **Tidak ada autoplay.** Embed Spotify wajib ditekan tombol putarnya oleh pengguna, di dalam iframe. Gerbang "Buka Undangan" kita saat ini adalah gesture yang membuat `<audio>` kita legal diputar — trik itu **tidak bisa** menembus iframe pihak ketiga.
2. **Lagu penuh butuh login Spotify.** Tamu yang tidak login (atau tidak punya akun) mendapat pratinjau ~30 detik. Untuk undangan yang disebar ke ratusan orang, mayoritas akan mendapat pengalaman rusak.
3. **Berat.** Iframe Spotify menarik JS + CSS + artwork pihak ketiga — persis yang dihindari oleh keputusan facade YouTube dan facade Maps.
4. **Bertabrakan dengan kontrol keamanan yang sengaja dipasang.** `musik_url` di-whitelist ke pustaka kita sendiri (`harih_musik_library()`) justru supaya pemegang token form tidak bisa menyuntikkan URL audio eksternal ke halaman yang dibagikan ke ratusan tamu.

**Pengganti yang menangkap niatnya:** section opsional **"Playlist Kami"** — facade (cover + judul + tombol), iframe Spotify baru disuntik saat diklik, di bawah section Penutup. Pola sama persis dengan facade YouTube yang sudah ada. Nilainya sedang, biayanya kecil → **G2.4**, bukan prioritas.

### 6b. Galeri kolase — ya, murah

Sekarang galeri = slider swipe satu-per-satu. Tata letak kolase (mozaik grid) adalah **alternatif**, bukan pengganti: sebagian pasangan punya 8–10 foto dan ingin semuanya terlihat sekaligus; sebagian punya 3 foto bagus dan slider lebih pas. Satu meta baru (`galeri_tata`: `slider` | `kolase`), CSS grid, tanpa library. → **G1.4**

### 6c. Maps/Waze — peta sudah ada, akurasi & Waze belum

Peta tersemat per lokasi sudah live sejak FU.2 (facade; iframe baru disuntik saat diklik). Dua kekurangan nyata:

- **Akurasi.** Kueri peta dirakit dari *nama + alamat* venue, bukan koordinat. Untuk gedung terkenal itu tepat; untuk "Kediaman Bapak ... , Jl. ..." bisa meleset ke jalan yang salah.
- **Waze tidak ada.** Di Indonesia Waze masih dipakai luas untuk perjalanan yang menghindari macet.

Perbaikannya bertumpu pada satu hal: **simpan koordinat**. Pemesan menempel link Google Maps (sering berupa short link `maps.app.goo.gl` yang tidak memuat koordinat). Menyelesaikan short link di sisi WordPress = satu HTTP request per render — buruk, dan bertabrakan dengan cache. Tempat yang benar untuk menyelesaikannya adalah **WF-02**, sekali saja, saat undangan dibuat. → **G1.5**

### 7. Video-first invitation — terbatas pada cover, dan digerbang

Yang layak: **video vertikal pendek sebagai cover/gate** — `<video autoplay muted playsinline loop poster>`, mulai diputar tepat setelah tamu menekan "Buka Undangan" (gesture yang sama yang sudah melegalkan musik). Sebelum ditekan, yang termuat cuma `poster` — jadi tamu yang bounce tidak menghabiskan kuota siapa pun.

Yang **tidak** layak: mengganti format undangan jadi feed video. Undangan dibuka berkali-kali untuk mencari **alamat, jam, dan link RSVP**. Video buruk untuk semua itu — tidak bisa disalin, tidak bisa dicari, tidak bisa dibaca ulang cepat, dan tidak terbaca sama sekali oleh penerima yang paling penting (orang tua, sesepuh).

**Penghalang yang harus diputuskan sebelum dibangun — hosting.** Video 10 MB × 300 tamu = 3 GB bandwidth **per undangan**. Hostinger shared hosting punya batas wajar-pakai dan batas CPU; ini bukan sesuatu yang boleh ditebak. Ada tiga jalur (VPS sendiri, object storage/CDN, atau tetap di Hostinger dengan batas ukuran ketat), dan ketiganya butuh angka nyata dulu. Karena itu poin ini masuk **G2.1**, digerbang di belakang F0.3, dengan satu titik keputusan eksplisit.

### 8. RSVP lewat WhatsApp — tolak jalur masuknya, ambil bentuk lain

**Yang saya tolak dengan tegas:** tamu mengirim RSVP ke **nomor WA bisnis kita**, diparsing n8n/WAHA. Alasannya bukan teknis — teknisnya bisa. Alasannya risiko: itu persis pola yang membuat sesi WhatsApp Web tidak resmi di-ban (ratusan nomor asing menghubungi satu nomor dalam waktu singkat, berulang tiap ada acara). Kalau sesi itu mati, yang ikut mati adalah **seluruh kanal delivery** — 9 workflow. Menukar kenyamanan satu fitur dengan risiko matinya tulang punggung operasional adalah pertukaran yang buruk pada rasio berapa pun.

**Premis "hindari formulir kaku" juga perlu diuji.** Form RSVP kita sekarang: nama (ketik) → pil Hadir/Berhalangan/Belum Pasti (satu tap) → jumlah tamu (satu tap) → sesi (satu tap) → kirim. Lima interaksi, tanpa keluar dari halaman. Alur WhatsApp: keluar aplikasi → aplikasi WA terbuka → kirim pesan → tunggu balasan bot → kembali. Yang "kaku" bukan formulirnya.

**Yang saya ambil:** setelah RSVP terkirim, muncul tombol **"Beri tahu mempelai lewat WhatsApp"** — `wa.me` ke nomor CP mempelai (meta `wa_cp` sudah ada), teks sudah terisi ("Halo, saya Budi. Insya Allah hadir bersama 2 orang di resepsi 🙏"). Pesan berjalan **dari nomor tamu ke nomor pribadi mempelai** — nomor bisnis kita tidak tersentuh sama sekali, jadi risiko ban nol. Nilainya justru lebih tinggi: yang mau tahu siapa yang datang adalah mempelai, bukan kita. → **G1.6**

**Supabase: tidak.** Kita sudah punya dua tempat data (WordPress CPT untuk data undangan & RSVP, Google Sheets untuk log operasional), dan `TASKS.md` sudah punya rencana **migrasi Sheets → Postgres** di Backlog v2. Menambahkan Supabase sekarang berarti **tiga** sumber kebenaran yang harus tetap konsisten, ditambah satu vendor lagi, ditambah pertanyaan privasi baru (data tamu keluar dari server kita). Kalau memang perlu database sungguhan, jalannya adalah migrasi yang sudah direncanakan — bukan datastore ketiga yang ditempel di samping.

### 9. AI Copywriter — ya, ini yang paling saya setujui dari seluruh daftar

Ini satu-satunya ide di daftar yang **menutup lubang nyata yang sudah kita ketahui ada**. Buktinya: WF-05 punya cron khusus untuk menagih pemesan yang belum mengisi data 24–48 jam setelah bayar. Orang mandek di form itu, dan bagian yang membuat mandek bukan "nama pengantin" — melainkan kolom **Kisah Kami**, **salam pembuka**, dan **kata penutup**: kotak kosong yang menuntut orang tiba-tiba jadi penulis.

**Arsitektur (penting — kunci API tidak boleh ada di browser):**

```
Form /isi-data/  ──POST(order,key,jenis,bahan)──►  n8n WF-10  ──►  Claude API
   (browser)         token HMAC yang sudah ada        (VPS,           (kunci di
                                                    kunci di env)      env n8n)
```

Kunci API hidup di `/opt/harih/.env` bersama rahasia lain — **tidak pernah** menyentuh browser. Gerbangnya token HMAC yang sudah dipakai `/isi-data/` (`substr(hash_hmac('sha256', order_id, FORM_TOKEN_SECRET), 0, 16)`), plus batas jumlah generasi per order supaya tidak bisa dijadikan mesin teks gratis oleh pemegang link.

**Model & biaya (diverifikasi ke referensi API, bukan ingatan):**

| Model | ID | Input /1 jt token | Output /1 jt token |
|---|---|---|---|
| Claude Opus 5 | `claude-opus-5` | $5 | $25 |
| Claude Haiku 4.5 | `claude-haiku-4-5` | $1 | $5 |

Sekali generasi (3 varian) ≈ 800 token masuk + 400 token keluar:
- **Opus 5:** ≈ $0,014 → **±Rp 230**
- **Haiku 4.5:** ≈ $0,003 → **±Rp 45**

Dengan batas 20 generasi per order, biaya terburuk **±Rp 4.600 per order** di Opus 5 — pada order termurah sekalipun (Rp 99 ribu) itu **4,6% dari harga**, dan pada paket cetak Rp 2,9 juta itu derau. **Rekomendasi: pakai `claude-opus-5`.** Kualitas bahasa Indonesia yang terasa manusiawi adalah seluruh nilai fitur ini — menghemat Rp 185 per generasi dengan menukar kualitas tulisan adalah penghematan di tempat yang salah. Kalau nanti volume meledak dan ternyata mahal, turun ke Haiku adalah perubahan **satu baris** di WF-10.

⚠️ **Konsekuensi privasi yang tidak boleh dilewat:** nama mempelai, tanggal, dan cerita pribadi dikirim ke API pihak ketiga. `docs/konten-legal/kebijakan-privasi.md` punya tabel pemroses data — Anthropic harus masuk ke sana, dan halaman diterbitkan ulang lewat `scripts/publish-legal.py` **sebelum** fiturnya menyala. Ini bukan formalitas: kita baru saja menambahkan kurir ke tabel yang sama untuk alasan yang persis sama.

→ **G2.2**

### 10. Personalisasi instan — builder tidak, "coba nama kalian" ya

**Page builder drag-and-drop** sudah tercatat di Backlog v2 (*"tema builder drag-and-drop"*). Jujur soal ukurannya: itu berminggu-minggu kerja, ia menggantikan alur `/isi-data/` → WF-02 yang sudah terbukti end-to-end sejak 22 Juli, dan ia membangun **kompleksitas terbesar di seluruh sistem** sebelum ada bukti ada yang mau membayar untuknya. Persis kesalahan urutan yang sudah dikoreksi penasihat bisnis.

**Tapi ada versi murahnya yang justru lebih bernilai sekarang**, karena ia bekerja di sisi **akuisisi**, bukan sisi pasca-bayar:

> Di beranda: dua kolom nama + tanggal → tombol **"Lihat undangan kami"** → demo terbuka **dengan nama mereka**.

Semuanya sisi klien, **hanya di undangan demo** (`order_id === 'demo'`), tanpa penyimpanan apa pun, tanpa endpoint baru. Polanya sudah ada dan terbukti: `?to=Nama` sudah bekerja begitu, dan pemilih nuansa di halaman demo sudah membuktikan parameter-URL-khusus-demo aman. Efeknya: calon pembeli melihat **namanya sendiri** dalam produk sebelum membayar — itulah "personalisasi instan" yang dimaksud, dengan biaya beberapa jam. → **G1.3**

Pratinjau langsung di dalam form `/isi-data/` (yang butuh refactor `single-undangan.php` supaya bisa dirender dari data POST, bukan dari meta) tetap ide bagus — tapi ia melayani orang yang **sudah membayar**. Digerbang: **G2.3**.

### 11. Gifting digital — escrow tidak, deep link e-wallet ya

Ide di baliknya benar dan sudah kita akui: Backlog v2 menyebut *"amplop digital ter-escrow via QRIS dengan fee platform 2–5%"* sebagai **perubahan model bisnis paling bernilai**. Tapi menampung uang tamu untuk diteruskan ke mempelai adalah **menampung dana pihak ketiga** — perilaku agregator pembayaran. Di Indonesia itu wilayah izin PJP dari BI, dan hampir pasti melanggar perjanjian merchant Duitku (akun merchant diberikan untuk menerima pembayaran **atas barang/jasa kita sendiri**). Menjalankannya lewat akun Duitku hariH berisiko bukan cuma denda, tapi **penutupan akun** — akun yang sama yang saat ini menjadi gerbang tunggal seluruh pemasukan. Approval production-nya bahkan belum keluar.

Ada jalur sahnya (bermitra dengan PJP berizin yang mendukung split payment / escrow marketplace), tapi itu keputusan tingkat bisnis dengan uji tuntas hukum — **bukan tugas kode**, dan bukan sekarang. Saya catat sebagai keputusan owner, bukan backlog teknis.

**Pengganti yang menangkap ~80% pengalamannya, dengan nol risiko lisensi:** uang mengalir **langsung dari tamu ke mempelai**, kita cuma menyediakan tombolnya. Selain gambar QRIS statis yang sudah ada, tambahkan **tautan dompet digital milik mempelai** (GoPay/DANA/OVO/ShopeePay) sebagai tombol — sekali tap membuka aplikasi dompet. Nol fee platform, nol dana singgah, nol izin.

⚠️ **Wajib di-whitelist per host.** Field ini datang dari pemesan dan berakhir sebagai `<a href>` di halaman yang dibagikan ke ratusan tamu — tanpa whitelist ia jadi **open redirect** di halaman yang orang percayai. Disiplinnya sama persis dengan `musik_url`. → **G1.8**

---

## Rencana

Format mengikuti konvensi `TASKS.md`: **apa · kenapa · langkah+file · selesai bila**. 👤 = butuh tangan owner. 🔒 = digerbang.

### Prinsip urutan

**G1 dikerjakan sekarang** karena ketiga syaratnya terpenuhi: murah, tidak menyentuh desain undangan yang dibekukan (kecuali aditif & opsional), dan melayani gerbang F0.3. **G2 menunggu F0.3** karena isinya fitur yang dijual — persis yang disiplin dokumen ini larang dibangun sebelum ada pembeli asing.

### ⚠️ Satu aturan lintas-task yang menghemat berjam-jam

Setiap **field undangan baru** menyentuh lima tempat: `cpt.php` (registrasi+sanitasi) → `page-isi-data.php` (form) → `isi-data.js` (kalau perlu logika) → **WF-02 JSON** (teruskan form → meta) → import n8n + `publish:workflow` + **restart container**. Ritual n8n itu yang mahal dan rawan.

**Maka: G1.4, G1.5, dan G1.8 memperkenalkan 4 field baru sekaligus** (`galeri_tata`, `koordinat`, `koordinat_akad`, `dompet`) dan **WF-02 disentuh satu kali** untuk keempatnya (**G1.9**). Jangan pernah kerjakan satu per satu.

---

### G1 — sekarang · murah · melayani gerbang F0.3

#### G1.1 · Dark mode halaman toko (otomatis + toggle manual)

**Apa:** palet gelap untuk 8 halaman pengguna `katalog.css`, mengikuti setelan sistem secara otomatis, dengan tombol timpa manual di nav yang pilihannya diingat.

**Kenapa:** ini satu-satunya poin desain di daftar teman yang murah **dan** langsung terasa. Token warna sudah terpusat, jadi biayanya sekali dan hasilnya delapan halaman. Untuk audiens yang memang membuka HP di malam hari (waktu orang merencanakan pernikahan), halaman ivory terang itu menyilaukan.

**Langkah:**
1. `themes/harih/katalog.css` — di bawah blok `:root` yang ada, tambah:
   - `@media (prefers-color-scheme: dark) { :root:not([data-theme="light"]) { …token gelap… } }`
   - `:root[data-theme="dark"] { …token gelap yang sama… }` (toggle manual harus menang dua arah)
2. Tokenkan yang belum: **12 `rgba()`** bayangan (jadi `--shadow-*` yang sudah ada + varian gelap) dan **2 hex nyasar** (baris ~606 `.kirim-msg.error`, ~786 `.proof-item img`).
3. `undangan/shared/isi-data.css` — 20 hex; ikutkan supaya pemesan yang baru bayar tidak berpindah tema di tengah alur.
4. `themes/harih/functions.php` — script inline **di `wp_head`, sebelum CSS**: baca `localStorage.harihTheme` → set atribut `data-theme` di `<html>`. Wajib inline & sinkron; kalau tidak, ada kedip putih sebelum tema gelap dipasang. Aman terhadap cache LiteSpeed karena HTML-nya identik untuk semua orang — keputusannya di sisi klien.
5. `template-parts/toko/nav.php` — tombol toggle (☾/☀), `aria-pressed`, tulis ke `localStorage`.
6. Logo: pakai `logo-harih-wordmark-putih.png` (sudah ada) saat gelap.
7. **Naikkan `HARIH_VERSION`.**

**Selesai bila:** 8 halaman terbaca di terang & gelap; kontras diukur ulang **≥ 4,5:1** untuk teks (patokan sekarang 5,29–9,06:1 di terang, semuanya lolos AA — angka gelap harus diukur, bukan diasumsikan); muat ulang tidak berkedip; kunjungan pertama mengikuti setelan sistem; klik toggle menang atas setelan sistem dan bertahan setelah muat ulang.

**Perkiraan:** ½ hari.

---

#### G1.2 · Self-host font halaman undangan

**Apa:** hentikan `harih_tema_fonts()` memanggil `fonts.googleapis.com`; sajikan Cormorant Garamond, Karla, Prata, Manrope dari server sendiri lewat `@font-face`.

**Kenapa:** ini isi sebenarnya dari poin "ultra-cepat" — bukan opini, tapi dua request lintas-domain yang memblokir render di **setiap** pembukaan undangan, oleh **setiap** tamu, di jaringan seluler. Halaman toko sudah self-hosted sejak redesain; undangan tertinggal. Bonus: satu ketergantungan pihak ketiga hilang dari produk yang dikirim ke ratusan orang — alasan yang sama yang membuat QRIS demo dijadikan aset lokal.

**Langkah:**
1. Konversi TTF yang **sudah ada di repo** → woff2, subset latin + latin-ext (`pyftsubset` dari `fonttools`; belum terpasang di Mac ini — `pip install fonttools brotli`). Rekam perintahnya di `docs/aset-lisensi.md` supaya reproducible seperti generator aset lain.
2. Simpan ke `themes/harih/aset/font/` bersebelahan dengan sumber TTF-nya. Lisensi OFL sudah tercatat.
3. `undangan/shared/undangan.css` — blok `@font-face` per keluarga, `font-display: swap`.
4. `functions.php` — `harih_tema_fonts()` mengembalikan `''` (atau dihapus + pemanggilnya dibersihkan); tambahkan `<link rel="preload">` untuk **satu** woff2 yang pasti dipakai di layar pertama per tema, mengikuti pola preload halaman toko yang sudah ada.
5. **Naikkan `HARIH_VERSION`.**

**Selesai bila:** HTML undangan live tidak lagi memuat `fonts.googleapis.com` maupun `fonts.gstatic.com` (dicek lewat DOM/`curl`, **bukan** tangkapan layar — pelajaran yang sudah tiga kali terjadi); ketiga tema tampil identik dengan sebelumnya; ukuran woff2 tercatat.

**Perkiraan:** ½ hari (paling lama di konversi & verifikasi visual 3 tema).

---

#### G1.3 · "Coba nama kalian" — personalisasi instan di beranda

**Apa:** dua kolom nama + tanggal di beranda → demo terbuka dengan nama & tanggal mereka. Sisi klien, **demo saja**, tanpa penyimpanan.

**Kenapa:** ini penerjemahan "personalisasi instan" yang bekerja **sebelum** uang berpindah. Melihat nama sendiri di dalam produk adalah pemicu keinginan yang paling langsung yang bisa kita berikan, dan biayanya beberapa jam — bukan berminggu-minggu seperti page builder.

**Langkah:**
1. `page-katalog.php` — blok kecil di dekat carousel tema: `input` nama pria, nama wanita, `input[type=date]`, tombol. Tanpa `<form>` submit; JS merakit URL demo `?pria=&wanita=&tgl=&buka=1`.
2. `functions.php` — tambahkan `'demo' => (get_post_meta($id,'order_id',true) === 'demo')` ke `$cfg` `window.UNDANGAN`, supaya JS tahu ia sedang di demo.
3. `undangan/shared/undangan.js` — **hanya bila `UNDANGAN.demo`**: baca `?pria/?wanita/?tgl`, timpa `.cover-names`, `.hero-names`, `.cover-date` lewat `textContent` (XSS-safe, pola sama dengan `?to=`), batas 40 karakter per nama, tanggal wajib cocok `^\d{4}-\d{2}-\d{2}$`. Nilai tak sah → abaikan diam-diam, tampilkan demo apa adanya.
4. Kapsul kecil "pratinjau — bukan undangan Anda" seperti kapsul pemilih nuansa yang sudah ada, supaya tidak ada yang mengira undangannya sudah jadi.
5. **Naikkan `HARIH_VERSION`.**

**Selesai bila:** mengetik nama di beranda membuka demo dengan nama itu, gerbang sudah terbuka, tanpa permintaan jaringan tambahan; **undangan non-demo mengabaikan parameter yang sama sepenuhnya** (wajib diuji dengan undangan uji, seperti verifikasi pemilih nuansa dulu); nama ber-`&`/emoji/HTML tidak merusak apa pun.

**Perkiraan:** ½ hari.

---

#### G1.4 · Galeri: tata letak kolase sebagai pilihan

**Apa:** meta baru `galeri_tata` (`slider` bawaan | `kolase`). Kolase = grid mozaik CSS, ketuk untuk memperbesar (lightbox yang sudah ada dipakai ulang).

**Kenapa:** poin "galeri kolase" dari daftar teman, dan ia menjawab kebutuhan nyata — pemilik 8–10 foto ingin semuanya terlihat sekaligus. Dibuat sebagai **pilihan** supaya undangan lama tidak berubah tanpa diminta (aturan desain beku).

**Langkah:**
1. `mu-plugins/undangan-core/cpt.php` — daftarkan `galeri_tata`, sanitasi `sanitize_key` + whitelist dua nilai, bawaan `slider`.
2. `template-parts/undangan/galeri.php` — cabang render; kelas `.galeri-kolase` saat kolase (dots & hint disembunyikan).
3. `undangan/shared/undangan.css` — `grid-template-columns: repeat(auto-fit, minmax(140px,1fr))`, item pertama `grid-column: span 2` untuk ritme mozaik, `aspect-ratio` dikunci agar tidak melompat saat foto termuat.
4. `undangan/shared/undangan.js` — lightbox menerima sumber grid (sekarang mengasumsikan slider).
5. `page-isi-data.php` — dua radio bergambar mini, bukan `<select>`.
6. WF-02 → **digabung ke G1.9**.

**Selesai bila:** satu undangan uji merender kolase; undangan tanpa meta tetap slider persis seperti sekarang; lightbox jalan di kedua mode; di 375px tidak ada overflow horizontal.

**Perkiraan:** ½ hari.

---

#### G1.5 · Koordinat presisi + tombol Waze

**Apa:** simpan koordinat lokasi (`koordinat`, `koordinat_akad`); peta tersemat memakainya bila ada; tambah tombol **Waze** di samping "Petunjuk Arah".

**Kenapa:** poin "Maps/Waze akurat". Peta sudah ada tapi kueri-nya berbasis teks — untuk lokasi non-gedung ia bisa meleset, dan tamu yang mengikuti pin salah adalah tamu yang tidak datang. Waze dipakai luas di Indonesia untuk menghindari macet.

**Langkah:**
1. `cpt.php` — dua meta baru, sanitasi ketat regex `^-?\d{1,3}\.\d+,-?\d{1,3}\.\d+$` (nilai tak cocok → kosong).
2. `template-parts/undangan/acara.php` — bila koordinat ada: kueri embed pakai `?q=lat,lng`; tombol Waze `https://waze.com/ul?ll=<lat>,<lng>&navigate=yes` (fallback `?q=<nama venue>` bila tak ada koordinat).
3. **WF-02 (di G1.9)** — ikuti redirect `gmaps_url` sekali, ekstrak koordinat dari URL hasil (`@lat,lng` atau `!3d…!4d…`), simpan ke meta. Gagal ekstrak **bukan** fatal — jatuh ke perilaku sekarang. Diselesaikan sekali saat undangan dibuat, bukan per render (menjaga cache tetap bersih).
4. `page-isi-data.php` — field opsional "Koordinat (opsional)" + petunjuk cara menyalinnya dari Google Maps, untuk pemesan yang mau presisi tanpa bergantung pada penyelesaian short link.
5. **Naikkan `HARIH_VERSION`.**

**Selesai bila:** undangan uji dengan koordinat menampilkan pin tepat di titik itu; tombol Waze membuka aplikasi Waze di Android & iOS; undangan tanpa koordinat berperilaku persis seperti sekarang; muat segar tetap **nol permintaan ke Google** sebelum peta diklik (facade tidak boleh rusak).

**Perkiraan:** ½ hari (di luar bagian WF-02).

---

#### G1.6 · RSVP → konfirmasi ke WhatsApp mempelai

**Apa:** setelah RSVP terkirim, muncul tombol "Beri tahu mempelai lewat WhatsApp" — `wa.me/<wa_cp>` dengan teks terisi dari jawaban tamu.

**Kenapa:** ini yang bisa diambil dari ide "RSVP via WhatsApp" **tanpa** menyentuh nomor bisnis kita. Pesan berjalan dari nomor tamu ke nomor pribadi mempelai; sesi WAHA kita tidak terlibat sama sekali → risiko ban nol. Dan yang sebenarnya ingin tahu siapa yang datang memang mempelai.

**Langkah:**
1. `template-parts/undangan/rsvp.php` — tombol `hidden`, muncul setelah kirim sukses; hanya dirender bila `wa_cp` terisi.
2. `undangan/shared/undangan.js` — pada respons sukses, rakit teks: `"Halo, saya {nama}. {Insya Allah hadir|Mohon maaf berhalangan|Belum bisa memastikan} {bersama N orang }di {akad|resepsi|acara}. 🙏"` → `encodeURIComponent`.
3. **Tanpa endpoint baru, tanpa field baru, tanpa perubahan REST.**
4. **Naikkan `HARIH_VERSION`.**

**Selesai bila:** setelah kirim, tombol muncul dengan teks yang cocok dengan jawaban; undangan tanpa `wa_cp` tidak menampilkan tombol; nomor WA bisnis hariH tidak menerima apa pun.

**Perkiraan:** 2 jam.

**Tambahan opsional (murah, jelaskan ke owner):** WF-05 sudah cron harian — bisa mengirim **rekap sekali sehari** ke mempelai ("hari ini 12 RSVP baru · total 87 tamu · lihat rekap: <link /rekap/>"). Satu pesan/hari ke satu nomor yang **sudah** kontak kita = pola yang aman, kebalikan dari blast. Kerja ±2 jam di WF-05.

---

#### G1.7 · Ukur performa dengan angka

**Apa:** baseline Core Web Vitals untuk 4 URL (beranda, `/harga/`, demo tema-01, demo tema-03), lewat PageSpeed Insights; dicatat di `runbook.md`.

**Kenapa:** "ultra-cepat" saat ini adalah klaim tanpa angka. Tanpa baseline kita tidak bisa tahu apakah G1.2 benar-benar menolong, dan tidak bisa tahu kalau nanti ada regresi. Juga: pengukuran dari mesin saya tidak bisa dipercaya (didiagnosis 2026-08-06 — hambatan di jalur jaringan lingkungan kerja saya), jadi harus lewat jalur Google → server.

**Langkah:** jalankan PSI (mobile + desktop) untuk 4 URL **sebelum** dan **sesudah** G1.2; catat LCP/CLS/INP + total transfer di `runbook.md` §baru, bertanggal.

**Selesai bila:** tabel sebelum/sesudah ada di `runbook.md`, dan efek G1.2 terlihat sebagai angka — bukan perasaan.

**Perkiraan:** 1 jam.

---

#### G1.8 · Amplop: tautan dompet digital mempelai

**Apa:** meta baru `dompet` (satu per baris, `Nama|URL`) → tombol di dalam amplop digital, di samping kartu bank & QRIS yang sudah ada.

**Kenapa:** ini bentuk "gifting digital" yang bisa dikirim tanpa izin PJP dan tanpa menyentuh perjanjian merchant Duitku — uang mengalir langsung tamu → mempelai; kita cuma menyediakan tombolnya.

**Langkah:**
1. `cpt.php` — sanitasi khusus: pecah baris → `esc_url_raw` → **whitelist host** (`gopay.co.id`, `link.dana.id`, `wallet.dana.id`, `ovo.id`, `shopee.co.id`, `s.shopee.co.id`, `qris.…` — daftar final ditetapkan saat implementasi). Host di luar daftar **dibuang diam-diam**, seperti perlakuan `musik_url`.
2. `template-parts/undangan/amplop.php` — render tombol di dalam `.amplop-isi`, gaya mengikuti `.btn-copy`/kartu bank yang ada. `rel="noopener"`.
3. `page-isi-data.php` — textarea + contoh format + catatan bahwa hanya dompet resmi yang diterima.
4. WF-02 → **digabung ke G1.9**.
5. **Naikkan `HARIH_VERSION`.**

**Selesai bila:** tautan sah muncul & membuka aplikasi dompet di HP; tautan ke host di luar whitelist **tidak pernah** ter-render (diuji langsung dengan URL jahat, bukan diasumsikan); undangan tanpa `dompet` tidak berubah.

**Perkiraan:** ½ hari.

---

#### G1.9 · WF-02: satu kali sentuh untuk empat field baru

**Apa:** teruskan `galeri_tata`, `koordinat`, `koordinat_akad`, `dompet` dari form → meta post, plus resolusi koordinat dari `gmaps_url` (G1.5 langkah 3).

**Kenapa:** ritual n8n (import → `publish:workflow` → restart container) mahal dan rawan; melakukannya empat kali untuk empat field adalah pemborosan yang bisa dihindari sepenuhnya.

**Langkah:** ikuti `n8n/workflows/README.md` — jangan import tanpa `schema` Sheets + ID credential yang sudah dibake di JSON repo; ID workflow harus ikut di JSON supaya menimpa, bukan menduplikasi; setelah import `publish:workflow --id=…` lalu **restart container**. Deploy via ssh/rsync butuh `dangerouslyDisableSandbox`.

**Selesai bila:** satu order uji ujung-ke-ujung (isi form → undangan terbit) membawa keempat field ke meta dengan benar; order tanpa field baru tetap terbit seperti sekarang; smoke test `cek-live.sh` hijau (ulangi per URL bila muncul `HTTP 000` — bisa palsu).

**Perkiraan:** ½ hari.

---

**Total G1: ±3–3,5 hari kerja.** Tidak ada rupiah keluar. Tidak ada yang menyentuh alur uang. Tidak ada undangan lama yang berubah tampilan.

---

### G2 — setelah F0.3 (10 pembeli asing) 🔒

Ketiganya fitur yang **dijual** atau berbiaya berjalan. Membangunnya sebelum ada pembeli asing mengulang kesalahan urutan yang sudah dikoreksi.

#### G2.1 🔒 👤 · Video cover vertikal (fitur Premium)

**Apa:** pemesan Premium mengunggah video vertikal pendek; jadi latar gerbang pembuka, mulai berputar tepat setelah "Buka Undangan" ditekan.

**Titik keputusan yang harus dijawab lebih dulu — hosting.** 10 MB × 300 tamu = **3 GB per undangan**. Tiga jalur:
- **(a) Hostinger apa adanya** + batas ketat (≤ 8 MB, ≤ 12 detik, 720×1280): nol biaya baru, tapi kuota wajar-pakai & CPU shared hosting harus dicek dulu.
- **(b) VPS sendiri** (`31.97.50.197`, sudah ada Caddy/Traefik): kendali penuh, tapi menambah tanggung jawab bandwidth & retensi ke server yang menjalankan n8n produksi milik owner.
- **(c) Object storage + CDN** (mis. R2): paling benar secara teknis, biaya kecil tapi bukan nol, dan menambah vendor.

**Rekomendasi saya: (a) dengan batas ketat**, dinaikkan hanya kalau angka pemakaian nyata menuntutnya. Yang tidak boleh: memilih tanpa mengecek kuota Hostinger dulu.

**Langkah (setelah hosting diputuskan):** meta `video_cover` · validasi client-side di `isi-data.js` (durasi & ukuran, tolak sebelum unggah — pola sama dengan kompresi foto) · validasi server-side di WF-02 (mime, ukuran) · `cover.php` render `<video autoplay muted playsinline loop preload="none" poster>` · `undangan.js` mulai memutar pada gesture pembukaan gerbang · `prefers-reduced-motion` → poster diam saja.

**Selesai bila:** video berputar mulus di iPhone Safari & Android Chrome tanpa suara; tamu yang tidak membuka gerbang tidak mengunduh video sama sekali (diverifikasi lewat panel jaringan); poster tampil saat video gagal/lambat.

**Perkiraan:** 1–1,5 hari + keputusan hosting.

---

#### G2.2 🔒 · AI Copywriter (WF-10 + Claude API)

**Apa:** tombol "Bantu tuliskan" di `/isi-data/` untuk **Kisah Kami**, **salam pembuka**, dan **kata penutup**. Pilih gaya: puitis · kasual · humoris · formal. Kembalikan **3 varian**, bisa disunting.

**Langkah:**
1. **Kredensial** — `ANTHROPIC_API_KEY` ke `vps/.env` **dan** `/opt/harih/.env` (cermin manual, seperti semua env lain), lalu `docker compose up -d n8n`.
2. **WF-10 baru** — webhook `POST /webhook/ai-teks`, CORS `https://harih.id`:
   - verifikasi token HMAC yang **sama** dengan `/isi-data/` (`substr(hash_hmac('sha256', order_id, FORM_TOKEN_SECRET), 0, 16)`) — jangan bikin rumus baru;
   - batas **20 generasi per order** (hitungan disimpan di sheet/static data n8n), plus rate limit per menit;
   - HTTP node → `https://api.anthropic.com/v1/messages`, header `x-api-key: {{$env.ANTHROPIC_API_KEY}}`, `anthropic-version: 2023-06-01`;
   - body: `model: "claude-opus-5"`, `max_tokens: 2000`, `output_config: { "effort": "low", "format": { "type": "json_schema", "schema": … } }` — skema JSON mengunci bentuk keluaran jadi 3 varian, jadi tidak ada parsing rapuh. **Effort `low`** karena tugasnya pendek dan pemesan sedang menunggu di form;
   - **Error Workflow → WF-00** (wajib, seperti semua workflow lain).
3. **Prompt** — sistem prompt berbahasa Indonesia, memuat konvensi undangan Indonesia (jangan menyebut nama tamu, jangan berlebihan, hormati nuansa keagamaan yang dipilih), diberi data: nama mempelai, tanggal, nuansa, gaya yang dipilih, dan poin mentah dari pemesan bila ada.
4. **UI** — `page-isi-data.php` + `isi-data.js`: tombol per field, kartu pilihan gaya, 3 kartu hasil, tombol "pakai ini". **Gagal AI tidak boleh memblokir submit form** — tombol dinonaktifkan dengan pesan, form tetap jalan.
5. **Privasi (wajib, sebelum menyala)** — tambahkan Anthropic ke tabel pemroses data di `docs/konten-legal/kebijakan-privasi.md`, terbitkan lewat `scripts/publish-legal.py`, verifikasi live.

**Selesai bila:** 3 varian kembali dalam ≤ 5 detik; batas 20/order ditegakkan **di server**, bukan disembunyikan di UI; kunci API tidak pernah muncul di HTML/JS (dicek lewat view-source); halaman privasi sudah tayang dengan Anthropic tercantum; WF-10 punya Error Workflow.

**Perkiraan:** 1,5 hari. **Biaya berjalan:** ±Rp 230/generasi, plafon ±Rp 4.600/order.

---

#### G2.3 🔒 · Pratinjau langsung di form isi data

**Apa:** tombol "Lihat pratinjau" di `/isi-data/` → undangan dirender dari data yang sedang diketik, sebelum submit.

**Kenapa digerbang:** ia melayani orang yang **sudah membayar**, jadi tidak menaikkan konversi ke F0.3 — dan ia butuh refactor `single-undangan.php` agar menerima array `$undangan` yang disuntikkan, bukan membaca meta. Refactor itu bagus dan berumur panjang, tapi bukan pekerjaan yang mendesak.

**Langkah (garis besar):** pisahkan perakitan array `$undangan` dari `single-undangan.php` ke fungsi yang bisa dipanggil ulang → endpoint `undangan/v1/pratinjau` (POST, bertoken HMAC, `noindex`, tanpa menyimpan apa pun) → render template dengan array dari POST. **Versi pertama memakai foto contoh** — mengalirkan foto asli ke pratinjau berarti mengunggah dua kali; foto sudah dipratinjau di form dan diverifikasi di tahap proof untuk order cetak.

**Perkiraan:** 2 hari.

---

#### G2.4 🔒 · Section "Playlist Kami" (Spotify facade)

**Apa:** section opsional di bawah Penutup: cover + judul playlist + tombol; iframe Spotify baru disuntik saat diklik.

**Kenapa paling belakang:** nilainya paling kecil dari seluruh daftar, dan **bukan** pengganti musik latar (lihat 6a). Ia section tambahan yang beberapa pasangan akan suka.

**Perkiraan:** ½ hari.

---

### Yang sengaja TIDAK dikerjakan — dan alasannya

| Ide | Kenapa tidak |
|---|---|
| **Spotify sebagai musik latar undangan** | Tidak bisa autoplay, lagu penuh butuh login (kalau tidak: 30 detik), berat, dan menembus whitelist `musik_url` yang dipasang justru untuk mencegah audio eksternal di halaman yang disebar ke ratusan tamu |
| **RSVP masuk ke nomor WA bisnis** | Ratusan nomor asing menghubungi sesi WAHA = pola yang membuat sesi WhatsApp Web di-ban. Sesi mati = **9 workflow** mati, termasuk seluruh delivery. Diganti G1.6 (arah sebaliknya: tamu → mempelai) |
| **Supabase** | Datastore **ketiga** di samping WP CPT + Google Sheets. Migrasi ke Postgres sudah ada di Backlog v2 — kalau butuh DB sungguhan, jalannya itu, bukan menempel vendor baru |
| **Escrow amplop digital lewat payment gateway** | Menampung dana pihak ketiga = perilaku agregator (wilayah izin PJP), dan hampir pasti melanggar perjanjian merchant Duitku — akun yang jadi gerbang tunggal seluruh pemasukan, yang approval production-nya bahkan belum keluar. Diganti G1.8 (deep link ke dompet mempelai) |
| **Page builder drag-and-drop** | Berminggu-minggu; menggantikan alur yang sudah terbukti end-to-end sejak 22 Juli; membangun kompleksitas terbesar di sistem sebelum ada bukti pasar. Sudah tercatat di Backlog v2. Diganti G1.3 (+ G2.3 nanti) |
| **Dark mode otomatis pada undangan** | Tema **adalah** produk yang dibeli. Pilihan gelap sudah tersedia sebagai produk: Tema 03 "Langit Malam" |
| **Navigasi swipe penuh (satu section satu layar) di undangan** | Undangan adalah dokumen rujukan yang dibuka berkali-kali untuk mencari alamat/jam/RSVP — bukan feed. Mematahkan tautan `#rsvp` dan menyulitkan pembacaan ulang |
| **Undangan yang seluruhnya video** | Penerima undangan lintas usia — itu alasan tercatat kenapa produk cetak ada. Alamat & jam tidak bisa disalin, dicari, atau dibaca ulang dari video |

---

## Urutan eksekusi yang saya sarankan

**Sekarang (±3–3,5 hari, nol rupiah):**
1. **G1.7** ukur baseline — dulu, supaya efek G1.2 terlihat sebagai angka
2. **G1.2** self-host font undangan — perbaikan performa terbesar per jam kerja
3. **G1.1** dark mode toko — poin desain paling terasa, biaya sekali untuk 8 halaman
4. **G1.3** "coba nama kalian" — satu-satunya G1 yang langsung menyerang gerbang F0.3
5. **G1.4 + G1.5 + G1.8** tiga field baru sekaligus
6. **G1.9** WF-02 satu kali sentuh untuk ketiganya
7. **G1.6** tombol WA mempelai (2 jam, bisa diselipkan kapan saja)
8. **G1.7 ulang** — bandingkan angka sesudahnya

**Setelah F0.3:** G2.2 (AI copywriter) → G2.1 (video cover) → G2.3 (pratinjau) → G2.4 (playlist).

## Keputusan owner — TERJAWAB 2026-08-07 👤

1. **Model AI → Google Gemini Flash 3.5 lewat OpenRouter**, bukan Claude. Konsekuensi untuk G2.2: WF-10 memanggil `https://openrouter.ai/api/v1/chat/completions` (skema OpenAI-compatible), bukan Anthropic Messages API. **Slug model harus diverifikasi ke daftar model OpenRouter saat implementasi** — jangan ditebak dari ingatan; simpan sebagai env `OPENROUTER_MODEL` supaya slug bisa dikoreksi tanpa menyentuh JSON workflow. Kredensial `OPENROUTER_API_KEY` mengikuti pola rahasia lain: `vps/.env` + `/opt/harih/.env` lalu `docker compose up -d n8n`. Kewajiban privasi tidak berubah — OpenRouter (dan penyedia model di belakangnya) masuk tabel pemroses data di Kebijakan Privasi sebelum fiturnya menyala.
2. **Disk → 0,8 GB terpakai, 25 GB tersedia.** Ruang bukan penghalang untuk G2.1 (video cover). ⚠️ Yang **belum** terjawab dan tetap harus dicek sebelum membangun: **kuota/wajar-pakai bandwidth**, bukan disk. 10 MB × 300 tamu = 3 GB **per undangan** — yang habis lebih dulu adalah transfer bulanan, bukan penyimpanan.
3. **Escrow → tidak jadi.** Sesuai analisis; yang dibangun tetap G1.8 (deep link dompet milik mempelai).
4. **Rekap RSVP harian ke WA mempelai → dipasang.** Ditambahkan ke WF-05 (cron harian yang sudah ada), satu pesan/hari ke nomor mempelai yang memang sudah kontak kita.

## Status eksekusi (2026-08-07)

**Selesai & live:** G1.7 baseline · G1.2 self-host font undangan · G1.1 mode gelap · G1.3 "coba nama kalian" · G1.6 tombol WA mempelai.
**Sisa G1:** G1.4 galeri kolase · G1.5 koordinat + Waze · G1.8 dompet digital · G1.9 WF-02 satu kali sentuh · rekap harian di WF-05.
