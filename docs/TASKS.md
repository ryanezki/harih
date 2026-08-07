# TASKS — hariH

**Status:** aktif · **Ditulis ulang:** 2026-08-07 setelah audit menyeluruh 6 dimensi (keamanan · kode PHP · frontend · n8n/infra · produk-bisnis · benchmark pasar).

> Versi sebelumnya (553 baris, checkpoint bertumpuk) diarsipkan di [`arsip/TASKS-2026-08-07.md`](./arsip/TASKS-2026-08-07.md). Dokumen ini hanya memuat **yang berlaku sekarang**. Riwayat lengkap ada di git log.

**Cara pakai:** centang `- [x]` saat selesai. ID (`A1`) stabil. 👤 = butuh tangan owner · 🤖 = bisa dikerjakan asisten · 🤝 = keduanya. Urutan grup adalah urutan kerja — jangan lompat ke P1 sebelum P0 tuntas.

**Tingkat keyakinan temuan:** A1 (kedua bug WF-02) **diverifikasi langsung** terhadap file — bukan laporan. Sisanya lolos satu putaran verifikasi adversarial (55 lolos, 1 dibantah) tapi belum dikonfirmasi tangan kedua; **periksa file yang dirujuk sebelum mengeksekusi**, terutama sebelum menyentuh n8n.

---

## Diagnosis

Proyek ini **sudah terlalu banyak dibangun dan terlalu sedikit dijual.** Platform hidup end-to-end sejak 22 Juli — 104 commit, 9 workflow n8n, 7 mu-plugin, 5 halaman bertoken, 3 tema × 7 nuansa — dan **nol order WooCommerce, nol pembeli asing, ±2,5 minggu tanpa satu rupiah.**

Yang lebih tajam: **satu-satunya jalur pemenuhan otomatis pasti gagal hari ini.** Pembeli pertama yang sudah membayar akan mengisi form 10 menit, menekan Kirim, dan menerima HTTP 500 berapa kali pun ia coba.

Dokumen lama menyebut approval Duitku sebagai "gerbang tunggal semua uang". Itu keliru — ada **dua gerbang yang lebih rapat**:

1. **Hulu kosong.** Tidak ada satu mekanisme pun yang mendatangkan pengunjung. Nol commit menyentuh akuisisi, nol rencana dalam 2.833 baris dokumen. Satu-satunya cara orang sampai ke harih.id hari ini adalah owner mengirim link manual.
2. **Hilir mati.** Pipeline yang akan menerima order itu rusak (A1).

**Duitku bisa disetujui besok pagi dan angkanya tetap nol.** Yang kurang bukan cara menagih, melainkan orang yang ditagih.

---

## Lima hal yang paling merugikan bila diabaikan

**1. Pipeline pemenuhan mati di jalur pembeli sah — dan belum pernah diuji sekali pun.**
`WF-02` node `Cek Order & Validasi` memakai `adaCetak` yang tidak pernah dideklarasikan (deklarasinya ada di **WF-01:50**, tidak ikut tersalin). `ReferenceError` terjadi di jalur `route:'ok'` sebelum node Respond. Empat jalur `out()` lain return lebih dulu — itulah sebabnya seluruh uji negatif tetap hijau dan menutupinya sejak 6 Agustus. Begitu diperbaiki, cacat kedua langsung menggantikan (lihat A1).

**2. Gerbang uang punya dua mode gagal yang belum pernah disentuh.**
Nomor WhatsApp — satu-satunya kanal pengiriman link — tidak wajib dan tidak divalidasi di checkout **blok**, karena ketiga hook penjaganya hanya difire checkout shortcode. Terpisah dari itu, filter gateway untuk keranjang > Rp 2 juta menghapus setiap gateway yang id-nya tidak cocok dengan empat substring **tebakan**, tanpa penjaga anti-kosong. Paket Rp 2,9 jt & Rp 5,9 jt bisa menampilkan checkout tanpa satu tombol bayar, sementara Hormat Rp 1,19 jt lolos sehingga gejalanya tersamar.

**3. Tidak ada mekanisme yang mendatangkan pembeli — dan itu satu-satunya hal penting tanpa ID task.**
Gerbang F0.3 menuntut 10 pembeli asing, tapi isinya hanya "catat dari mana datangnya" — tanpa satu langkah *bagaimana*. Ironisnya jalur termurah sudah tertulis dan menganggur: mendekati 5 vendor/WO sudah dinyatakan bisa paralel, nol biaya, dan masih di urutan bawah daftar owner.

**4. Halaman yang sudah tayang menjanjikan hal yang kode dan keputusan bisnis tidak dukung.**
Tiga sekaligus, semuanya live: `/jadi-reseller/` menjanjikan komisi 30% "tiap order" di tujuh titik sementara keputusan terkunci membayar rupiah tetap untuk cetak (selisih **Rp 570.000/order Resepsi**); Kebijakan Privasi berjanji token halaman "tidak dikirim ke pihak ketiga mana pun" sementara GA4 aktif di empat halaman bertoken; S&K masih menjual kartu QR & label souvenir yang sudah diganti. Klaim tertulis yang tidak ditepati jauh lebih mahal daripada perbaikannya — dan semuanya berbiaya menit.

**5. Kegagalan senyap di titik kerja terberat pelanggan.**
Empat halaman bertoken tidak memanggil `nocache_headers()` sementara produksi mengirim `max-age=604800`. Nonce anonim hanya sah 24 jam → HTML ter-cache seminggu membuat nonce mati jadi kasus **normal**. Dan keduanya gagal tanpa suara: 300 nama tamu yang baru ditempel lenyap tanpa satu kalimat galat, dan tombol "setujui proof" jadi tombol mati sementara Antrean Cetak menampilkan "Menunggu persetujuan pelanggan" selamanya pada order yang tenggat garansinya berjalan.

---

## P0 — Buka jalan uang, dan pastikan order berbayar pertama tidak gagal

> Semuanya harus tuntas **sebelum** Duitku production dinyalakan. Jangan sampai order berbayar pertama yang jadi alat ujinya.

- [x] **A1** 🤖 `menit` — **WF-02: deklarasikan `adaCetak` + kupas sufiks `+cetak`** → **SELESAI & LIVE 2026-08-07**
  Diimpor bersama B3 dalam satu ronde. **Terverifikasi dari ekspor live** (bukan dari yang dikirim): `const adaCetak = raw.endsWith('+cetak') || raw === 'cetak'` ada di baris 22, `const tier = raw.replace('+cetak', '')` di baris 23, dan seluruh 41 node WF-02 + 29 node WF-01 kini identik dengan repo. Smoke test 21/21.
  Logikanya diuji lebih dulu **di runtime n8n yang sebenarnya** (`docker exec -i harih-n8n node`) atas 10 kasus: `premium+cetak`→premium · `favorit+cetak`→favorit · `hemat+cetak`→hemat · `PREMIUM+CETAK`→premium (huruf besar) · `cetak`/`''`/`null`→hemat. `adaCetak` benar di kesepuluhnya.
  **Dibuktikan end-to-end oleh A3** — dua order uji menghasilkan undangan tier premium yang identik, dengan `ada_cetak` benar di kedua cabang.
  <details><summary>Isi perubahan (untuk rujukan)</summary>

  ```js
  const raw = String(row.paket || '').toLowerCase();
  const adaCetak = raw.endsWith('+cetak') || raw === 'cetak';
  const tier = raw.replace('+cetak', '');
  const paket = ['hemat', 'favorit', 'premium'].includes(tier) ? tier : 'hemat';
  ```
  </details>

- [x] **A2** 🤝 `menit` — **Tarik WF-01 & WF-02 dari container SEBELUM import** → **SELESAI 2026-08-07**
  **Hasil: repo IDENTIK dengan yang hidup — nol drift.** Diekspor dari container `harih-n8n` lalu dibandingkan per node: WF-01 29/29 node, WF-02 41/41 node, **0 parameter berbeda** di keduanya. `updatedAt` live WF-01 `2026-08-06T00:32:47Z`, WF-02 `2026-08-06T21:32:39Z` (= 07-08 04:32 WIB, konsisten dengan landing-nya paket G1).
  **Konsekuensi:** aman meng-import A1 — tidak ada pekerjaan live yang akan tertimpa. Dan **bug A1 terkonfirmasi ada di produksi**, bukan hanya di repo: ekspor live menunjukkan `adaCetak` dipakai di baris 115 tanpa satu pun deklarasi, dan whitelist tier tetap tidak mengupas sufiks.
  **Temuan sampingan yang memperburuk A1:** `settings` WF-01 & WF-02 live = `{"executionOrder":"v1","timezone":"Asia/Jakarta"}` — **tidak ada `errorWorkflow`**. Jadi saat `ReferenceError` itu terjadi pada pembeli sungguhan, WF-00 tidak menyala dan tidak ada satu pun alert. Gagal senyap, persis seperti diduga B3.
  **9 workflow hidup, tidak ada duplikat** (`n8n list:workflow`) — jadi kerusakan yang ditakutkan B3 belum terjadi; ia baru akan terjadi pada import berikutnya atas kelima berkas tanpa `id`.

- [x] **A3** 🤝 `jam` — **Uji happy-path end-to-end** → **LULUS 2026-08-07**
  **Jalur `route:'ok'` akhirnya dieksekusi — pertama kali sejak proyek berdiri.** Dua kasus, keduanya tuntas:

  | | HTTP | sheet | wa_status | undangan | tier |
  |---|---|---|---|---|---|
  | `paket=premium` | 200 (1,5 dtk) | `SUDAH_JADI` 15 dtk | `TERKIRIM` | terbit, 3 foto | premium ✓ |
  | `paket=premium+cetak` | 200 (1,2 dtk) | `SUDAH_JADI` 10 dtk | `TERKIRIM` | terbit, 3 foto | **premium ✓** |

  **Bukti A1 benar-benar menutup kedua bug:** kedua undangan identik **38.099 byte** — galeri, kisah kami, amplop, RSVP, kedua lokasi. Meta post order 900002 berbunyi `paket=premium` padahal baris sheet-nya `premium+cetak`; sebelum A1 nilainya `hemat`. Dan data eksekusi n8n menunjukkan `ada_cetak=false` untuk kasus 1, `ada_cetak=true` untuk kasus 2 — identifier yang dulu melempar `ReferenceError` kini punya nilai yang benar di kedua cabang.
  **Bonus — B3 ikut terbukti tanpa error buatan.** Node `Set Order Completed (WC)` gagal di kedua eksekusi (`NodeApiError: ID tidak valid, 400`) karena skrip uji hanya membuat baris **sheet**, bukan order WooCommerce. Kegagalan itu terjadi **setelah** delivery sehingga tidak membatalkan apa pun — tapi ia menandai eksekusi `error` dan **memicu WF-00 dua kali** (id 4253 & 4255, keduanya `success`, isi alert menyebut *"Workflow GAGAL · WF-02"*). Ikatan `errorWorkflow` yang baru dipasang B3 terbukti bekerja pada error sungguhan.
  **Dijadikan bisa diulang:** [`../scripts/uji-happy-path.py`](../scripts/uji-happy-path.py) — `python3 scripts/uji-happy-path.py 628xxx` (jalankan + bersihkan) atau `--bersihkan` saja. Nama field di dalamnya diturunkan dari `body.*` yang benar-benar dibaca WF-02 dan atribut `name=` di `page-isi-data.php`, bukan tebakan — tebakan pertama saya salah di 6 field dan akan menghasilkan kegagalan palsu.
  **Produksi dikembalikan bersih:** 2 undangan + 6 media + 2 kartu OG dihapus permanen, 2 baris sheet dihapus. Sisa: 3 undangan demo · 0 ucapan · 0 order · sheet 0 baris · 9 workflow aktif · smoke 21/21.
  ⚠️ **Yang tetap belum diuji:** jalur dari WooCommerce (WF-01) — uji ini menyuntik baris sheet langsung, jadi checkout → webhook → intake belum tersentuh. Itu baru bisa diuji dengan order sungguhan, yakni saat F0.2/A7 berjalan.

- [x] **A4** 🤖 `jam` — **Nomor WhatsApp wajib & tervalidasi di checkout blok** → **SELESAI & LIVE 2026-08-07**
  **Terkonfirmasi di server sebelum disentuh:** `woocommerce_checkout_phone_field` bernilai **`optional`** — nomor WA memang boleh kosong, tanpa validasi format, tanpa normalisasi. Ketiga mekanisme di `woocommerce.php` hanya difire checkout **shortcode**, dan perbaikan F3.6 hanya menambal field alamat.
  **Yang dibangun** (semuanya di `woocommerce.php`, tempat logika nomor WA sudah berada):
  · `option_woocommerce_checkout_phone_field` difilter jadi `required` **di kode**, bukan sekadar diset sebagai opsi — supaya ikut berpindah bersama repo dan tidak diam-diam kembali ke `optional` saat database dipulihkan. Opsinya ikut disamakan di DB agar tidak ada dua versi kebenaran.
  · Label lewat `woocommerce_get_country_locale` (prioritas 25, setelah filter locale `cetak.php`; tidak menyentuh keranjang jadi bebas risiko rekursi).
  · Validasi + normalisasi lewat `woocommerce_store_api_checkout_update_order_from_request` prioritas 5 — lebih dulu daripada gerbang cetak, karena syarat ini berlaku untuk **semua** pesanan. Melempar `RouteException` 400. **Tidak menyentuh order yang dibuat manual di wp-admin** (hook ini khusus Store API) — penting untuk jalur bayar manual A7.
  **Terverifikasi lewat Store API sungguhan** (order uji `pending` sehingga WF-01 tidak menyala, lalu dihapus): kosong → ditolak · `12345` → ditolak · `081519108008` → lolos dan **tersimpan sebagai `6281519108008`**.
  **Terverifikasi di checkout yang benar-benar dirender** (browser, bukan asumsi): `billing_phone` berlabel *"Nomor WhatsApp — link undangan dikirim ke sini"*, `required: true`, `type: tel`; field alamat tetap tersembunyi untuk keranjang digital (F3.6b utuh).
  *Catatan implementasi:* penjelasan ditaruh di **label**, bukan `description`/`placeholder` — blok terbukti tidak merender keduanya untuk field inti (input keluar tanpa `placeholder` dan tanpa `aria-describedby`). Baris `placeholder` yang sempat saya pasang dicabut lagi karena hanya jadi kode yang berbohong.
  ⚠️ **Tanpa penjaga otomatis.** Menguji ini menuntut sesi keranjang + nonce Store API, terlalu berat untuk `cek-live.sh` yang berjalan lewat HTTP polos. Regresi di sini **tidak akan ketahuan sendiri** — periksa manual bila menyentuh checkout.

- [x] **A5** 🤝 `jam` — **Penjaga anti-checkout-kosong + ID gateway Duitku diverifikasi** → **SELESAI & LIVE 2026-08-07**
  ⚠️ **Kekhawatiran audit sebagian TIDAK terbukti — dicatat supaya tidak diwariskan sebagai kebenaran.** Setelah 37 gateway Duitku yang benar-benar terdaftar diperiksa satu per satu, saringan `cetak.php` ternyata **sehat**: dari 10 gateway aktif, **6 lolos** untuk keranjang > Rp 2 juta — `duitku_va_permata` · `duitku_va_bni` · `duitku_va_bca` · `duitku_va_mandiri_h2h` · `duitku_indomaret` · `duitku_briva`. Yang dibuang tepat kanal berplafon rendah: `duitku_ovo`, `duitku_dana`, `duitku_shopeepay_applink`, `duitku_nobu_qris` — persis maksud keputusan owner. Skenario "checkout Rp 2,9 jt tanpa tombol bayar" **tidak pernah terjadi**.
  *(Koreksi atas catatan saya sendiri beberapa jam sebelumnya yang menyebut "hanya `duitku_briva` yang lolos, bergantung satu gateway" — itu disimpulkan dari keluaran `tail -15` yang memotong 22 gateway pertama. Salah, dan sudah dibetulkan di sini.)*
  **Pencocokan substring dipertahankan, bukan diganti allowlist** — keputusan yang berbalik dari rencana awal setelah melihat datanya: pola `_va_` sendirian menangkap **dua belas** VA yang terdaftar, sehingga VA bank baru yang diaktifkan owner ikut lolos otomatis. Allowlist ID tetap justru akan diam-diam membuangnya, dan kegagalannya senyap.
  **Yang benar-benar dibangun:** penjaga anti-kosong — bila saringan menghasilkan nol gateway (semua VA & gerai kebetulan nonaktif, atau Duitku mengganti skema penamaan), daftar asli dikembalikan utuh dan `error_log` mencatat sebabnya. Menawarkan kanal berplafon rendah *mungkin* gagal; tidak menawarkan apa pun *pasti* gagal. Logika saringan juga diangkat jadi fungsi bernama `undangan_gateway_nominal_besar()` supaya bisa diuji langsung, tidak terkubur di dalam closure.
  **Terverifikasi lewat Store API sungguhan** (order uji `pending` — WF-01 tidak menyala — lalu keempatnya dihapus):
  · keranjang Rp 2,9 jt: `duitku_dana` & `duitku_nobu_qris` → **dibuang saringan** · `duitku_va_bca` & `duitku_indomaret` → **lolos**
  · keranjang Rp 99 rb (di bawah ambang): `duitku_dana` → **lolos**, jadi ambangnya benar-benar dihormati
  · penjaga anti-kosong diuji atas daftar yang tidak satu pun cocok pola → menyala, daftar asli dikembalikan
  *Catatan uji:* produk fisik menuntut `shipping_address` terpisah di Store API; tanpa itu balasannya `woocommerce_rest_invalid_address` dan mudah disalahartikan sebagai gateway yang tersaring.

- [x] **A6** 🤝 `menit` — **nocache + cabang else saat nonce gagal** → **SELESAI & LIVE 2026-08-07** *(v2.10.0)*
  **Temuan yang lebih besar dari tugasnya sendiri: keempat halaman selain `/isi-data/` membalas 200, bukan 403, pada token salah.** LiteSpeed menyajikan halaman `wp_die` dari cache sebelum PHP dijalankan, jadi **gerbang 403 tidak pernah tegak di tingkat HTTP** — dan `cek-live.sh` tidak menangkapnya karena hanya menguji `/isi-data/`.
  **Akar yang sebenarnya bukan kode, melainkan setelan:** `wp litespeed-option get cache-exc` berisi **hanya** `/isi-data/`. Itulah satu-satunya alasan halaman itu no-cache selama ini — bukan dua baris PHP-nya. Setelah keempat URI ditambahkan, keduanya beres sekaligus: header jadi `no-cache, must-revalidate, max-age=0, no-store, private` **dan** token salah jadi 403.
  Tidak butuh tangan owner — bisa lewat wp-cli, perintahnya dicatat di [`../scripts/setup-hostinger.sh`](../scripts/setup-hostinger.sh).
  **Yang dibangun:** `nocache_headers()` + `do_action('litespeed_control_set_nocache')` di keempat template · nonce **dipisah** dari aksi di `page-tamu.php` & `page-proof.php` sehingga kegagalan berhenti senyap · kelas `.proof-galat` memakai token `--c-warn-*` yang sudah punya varian mode gelap (**bukan** `--c-error`: ini bukan kesalahan pemesan dan datanya tidak hilang).
  **Terverifikasi live** dengan order + undangan uji (dibuat `pending` agar webhook `processing` tidak menyala, lalu dihapus):
  · nonce mati di `/tamu/` → pesan galat tampil, **300 dari 300 nama kembali ke textarea**, nol tersimpan
  · nonce sah → *"Tersimpan — 300 nama"*, tanpa galat
  · nonce mati di `/proof/` → *"…persetujuanmu BELUM tercatat"*
  **Smoke test 21 → 30.** Sembilan pemeriksaan baru: kelima halaman bertoken diuji 403 **dan** `Cache-Control`, sehingga regresi setelan LiteSpeed ketahuan sendiri.
  *Ikut diperbaiki:* pemeriksaan WF-03 kini menerima **429** sebagai lulus. Endpoint `daftar-reseller` dibatasi 5 pendaftaran/jam/IP, jadi menjalankan smoke test lebih dari 5× dalam sejam — hal biasa saat sedang mengerjakan sesuatu — membuatnya **gagal palsu**. Yang menandakan workflow mati adalah 404; 429 justru bukti ia hidup dan rate limiter-nya bekerja.

- [ ] **A7** 🤝 `jam` — **Buka jalur bayar manual untuk paket digital — berhenti menunggu Duitku**
  Dokumen lama menyebut Duitku "gerbang tunggal", tapi F1.6 **sudah merestui** invoice WA + transfer manual untuk order Rp 2,9 juta. Jalur yang sah untuk produk 30× lebih mahal belum pernah diterapkan ke produk Rp 99–299 ribu yang 10 penjualannya adalah gerbang sesungguhnya. **Tidak butuh satu baris kode baru:** owner kirim rekening/QRIS → buat order WooCommerce → set `processing` → WF-01 menyala persis seperti biasa. Setiap hari menunggu adalah hari tanpa data attach rate, closing rate, dan distribusi tier.
  **Selesai bila:** tombol "Pesan lewat WhatsApp" di samping tombol checkout di katalog · runbook memuat langkah membuat order manual + set processing · satu penjualan uji tuntas sampai undangan terbit (menumpang A3).

- [ ] **A8** 👤 `menit` — **Tindak lanjuti Duitku: profil nominal, mekanisme refund, plafon per kanal**
  Diajukan 2026-08-04, belum keluar. Tiga hal yang bisa menggagalkan pembayaran di langkah terakhir: profil merchant menyebut Rp 99–299 ribu padahal paket cetak sampai Rp 5,9 juta · mekanisme refund (Garansi Tepat Waktu menjanjikan 100% — berapa lama, siapa menanggung fee kanal) · plafon per kanal (e-wallet/QRIS sering di bawah Rp 5,9 juta). Jawabannya juga menentukan bentuk akhir A5.
  **Selesai bila:** ketiga pertanyaan terkirim & jawabannya dicatat di sini. Bila approval keluar: kredensial plugin ke Production dan order uji dijalankan — **setelah A1–A6 tuntas**.

---

## P1 — Tutup lubang yang merugikan order pertama & janji yang sudah tayang

> Tidak menghambat rupiah pertama, tapi bila terjadi biayanya reputasi atau hukum — jenis kerugian yang tidak bisa ditambal belakangan. **B8 dan B9 menumpang ronde import WF-02 yang sama dengan A1** — jangan menyentuh WF-02 dua kali.

- [x] **B1** 🤖 `jam` — **Endpoint RSVP dikunci ke slug** → **SELESAI & LIVE 2026-08-07** *(v2.11.0)*
  **Dibuktikan dulu, bukan diasumsikan.** Sebelum menyentuh kode: dua RSVP dikirim ke undangan demo lewat endpoint publik, lalu `for id in 1 12 13 43 44 90 999` dijalankan dari luar — `id 13` mengembalikan **nama tamu, pesan, kehadiran, jumlah tamu, sesi, dan waktu**, tanpa autentikasi dan tanpa pernah tahu satu link undangan pun. ID post berurutan, sementara seluruh model privasi undangan bertumpu pada "slug tidak bisa ditebak". (`wa_rsvp` memang tidak pernah ikut — bagian itu dirancang benar sejak awal.)
  **Yang diperbaiki, dan ternyata ada tiga lubang, bukan satu:**
  · rute `GET /rsvp/(?P<id>\d+)` → `/rsvp/(?P<slug>[a-z0-9-]+)`, diresolusi lewat `undangan_dari_slug()`
  · `POST` berhenti menerima `undangan_id` — dengan ID berurutan siapa pun bisa **membanjiri buku tamu semua undangan** lewat satu loop
  · status `publish` **ditegakkan di kueri**. Sebelumnya hanya tipe post yang diperiksa, jadi undangan yang sudah kedaluwarsa — `masa-aktif.php` menonaktifkannya dengan mengubah jadi `draft` — tetap menyerahkan daftar tamunya **dan** tetap menerima RSVP baru.
  Undangan tak ditemukan membalas **404, bukan daftar kosong**: daftar kosong memberi tahu penebak bahwa slug-nya benar dan undangannya sekadar belum punya ucapan.
  **Tanpa masa transisi, dan itu aman:** `13` cocok dengan pola slug, tidak ada undangan ber-slug itu, jadi permintaan lama jatuh ke 404 sendirinya — dan `undangan.js` sudah lama memperlakukan non-200 sebagai daftar kosong.
  **Terverifikasi live:** `/rsvp/{13,43,44,1,999}` → semuanya **404** · `/rsvp/demo-tema-01` → 2 entri · slug tak dikenal → 404 · POST slug sah → `ok` · POST `undangan_id` cara lama → 400 · POST ke undangan draft → 400 · GET undangan draft → 404. Buku tamu diperiksa di undangan sungguhan lewat browser: `cfg.slug` terpakai, 2 ucapan ter-render normal.
  **Smoke test 30 → 32.** Dua pemeriksaan berpasangan yang harus lulus bersama: `/rsvp/13` → 404 (lubang tertutup) **dan** `/rsvp/demo-tema-01` → 200 (buku tamu tidak ikut mati). Yang pertama sendirian bisa dipuaskan dengan mematikan seluruh fitur.
  Data uji dibersihkan: 3 ucapan + 1 undangan draft dihapus permanen.

- [x] **B2** 🤖 `menit` — **GA4 berhenti mengirim token pelanggan ke Google** → **SELESAI & LIVE 2026-08-07** *(v2.12.0)*
  **Dibuktikan di produksi lebih dulu:** order uji dibuat, token dihitung, lalu HTML kelima halaman bertoken diperiksa — `/proof/`, `/tamu/`, dan `/rekap/` memuat `googletagmanager.com/gtag`. GA4 mengirim URL lengkap sebagai `page_location`, jadi **token order ikut terkirim ke Google** — sekaligus membatalkan `Referrer-Policy: no-referrer` yang dipasang khusus untuk mencegah kebocoran itu. `/upsell/` lolos pengamatan hanya karena order ujinya membalas 403 sebelum halaman sempat dirender; ia sama terdampaknya.
  Yang membuatnya lebih dari cacat teknis: `kebijakan-privasi.md` **sudah tayang** menyatakan token halaman ini tidak dikirim ke pihak ketiga mana pun. Komentar di `functions.php` bahkan menuliskan alasannya sendiri dengan benar — penulisnya tahu tokennya sensitif, hanya lupa mendaftarkan empat halaman sisanya.
  **Yang dibangun:** `harih_template_bertoken()` — satu daftar kelima template — plus `harih_halaman_bertoken()`, dipakai di blok GA4 **dan** filter `wp_robots`. Menambah halaman bertoken berikutnya cukup di satu tempat; akar cacat ini memang tiap tempat menulis daftarnya sendiri.
  *Ikut terbetulkan:* keempat halaman selain `/isi-data/` sebelumnya hanya kebagian `noindex` lewat daftar utilitas, tanpa `nofollow`. Sekarang kelimanya `noindex, nofollow`.
  **Terverifikasi live:** kelima halaman bertoken → **nol `gtag`** · `/`, `/harga/`, `/satuan/`, `/jadi-reseller/`, `/kontak/` → **tetap terlacak**, jadi funnel tidak ikut mati · robots `noindex, nofollow` terpasang.
  ⚠️ **Tanpa penjaga otomatis.** Mengujinya menuntut token order yang sah, dan `cek-live.sh` sengaja berjalan tanpa rahasia. Menambahkan uji atas jalur 403 akan sia-sia — halaman `wp_die` memang tidak pernah memuat gtag, jadi ujinya lulus tanpa membuktikan apa pun. Periksa manual bila menyentuh blok GA4.

- [x] **B3** 🤝 `menit` — **errorWorkflow terikat + 5 `id` ditanam** → **SELESAI & LIVE 2026-08-07**
  Diimpor bersama A1. **Terverifikasi dari ekspor live kesembilan workflow:** delapan memuat `"errorWorkflow":"sJ0vsHhFyPotbxMg"`, WF-06 mendapat `timezone: Asia/Jakarta` kembali, dan **9 workflow tetap 9 — nol duplikat**, karena kelima `id` sudah tertanam sebelum import.
  **Keputusan yang menyimpang dari rencana:** `errorWorkflow` **tidak** dipasang di WF-00 (rencana semula menyebut "kesembilan"). Error handler yang menunjuk dirinya sendiri berisiko loop saat WF-00 sendiri yang gagal. Delapan sisanya cukup.
  **Terbukti bekerja 2026-08-07 lewat A3** — dua error sungguhan di WF-02 memicu WF-00 (eksekusi 4253 & 4255, keduanya `success`, alert menyebut *"Workflow GAGAL · WF-02"*). Tidak perlu error buatan.
  <details><summary>ID live yang ditanam (untuk rujukan)</summary>

  WF-00 `sJ0vsHhFyPotbxMg` · WF-03 `k6LyfYoYds47al38` · WF-04 `539zvR4mzQ5PObJ6` · WF-07 `AbxU2iCdYmKRx5G0` · WF-08 `AI0ofPRSqBhbLbwO`
  </details>

- [x] **B4** 🤖 `menit` — **Penjaga idempotency WF-01 akhirnya bisa menyala** → **SELESAI & LIVE 2026-08-07**
  `alwaysOutputData: true` pada `Baca Ulang Orders`, sehingga `throw new Error('Verifikasi idempotency gagal…')` di node berikutnya benar-benar dieksekusi saat lookup mengembalikan nol baris. Sebelumnya n8n melewati seluruh node hilir, eksekusi berakhir **SUKSES**, email & WA tidak terkirim, dan barisnya tetap ada di sheet sehingga WF-08 tidak menganggapnya tertinggal. `Append Baris Order` — satu-satunya node Sheets di jalur uang tanpa retry — diberi `retryOnFail`, `maxTries: 3`, `waitBetweenTries: 3000`.
  Baru bermakna setelah B3: tanpa ikatan `errorWorkflow`, `throw` pun tetap senyap.

- [ ] **B5** 🤝 `jam` — **`/jadi-reseller/` & WF-03 menjanjikan komisi 30% "tiap order"**
  Ditulis di h1, hero-sub, langkah 3, daftar syarat, meta description, og:description, dan tagline kaki — hanya baris kaki memakai kata "digital". Berhadapan dengan keputusan terkunci: digital 30%, fisik rupiah tetap Rp 150/300/500rb. Reseller yang menjual Paket Resepsi mengharapkan Rp 870.000 dan menerima Rp 300.000 — **selisih Rp 570.000 per order** di kanal yang seluruh nilainya kepercayaan. Lebih cepat lagi: kupon `RES-` **ditolak di keranjang cetak** sehingga order terbesar berhenti di langkah terakhir. **Halaman ini live dan menerima pendaftar hari ini.**
  **Selesai bila:** setiap klaim 30% dikualifikasi jadi "30% untuk paket digital · Rp 150/300/500rb untuk paket cetak · item satuan tanpa komisi" di ketujuh titik + teks WF-03. Bila owner memilih menutup pendaftaran (lihat pertanyaan owner), halaman diturunkan sebagai gantinya. Kontradiksi rekrut-vs-tidak antara arsip TASKS dan `panduan-manual.md:159` ikut diselesaikan.

- [x] **B6** 🤖 `menit` — **Kebijakan Privasi menyebut data yang benar-benar dikumpulkan** → **TERBIT & LIVE 2026-08-07**
  Yang paling telanjang adalah **nomor rekening**: formulir publik `/jadi-reseller/` mengumpulkan nama, WA, bank, dan norek; WF-03 mengirimkannya lewat WhatsApp + email ke owner lalu menyimpannya di tab `resellers` Google Sheets — kategori data keuangan tanpa satu kalimat pun dasar pemrosesan maupun retensi.
  **Lima bagian diperbarui:** §1 dapat sub-bagian **"Dari calon reseller"** (dengan dasar pemrosesan: pelaksanaan perjanjian kemitraan, dan pernyataan bahwa rekening hanya dipakai membayarkan komisi) dan butir **daftar nama tamu** di "Dari pemesan" (maks. 600 nama, data pihak ketiga, dipakai hanya untuk amplop & link personal) · §2 menambahkan pembayaran komisi sebagai tujuan · §4 memperluas keperluan Google Sheets · §5 memberi retensi pada daftar tamu & data reseller · §8 pengecualian GA4 diperluas dari "formulir pengisian data" jadi **kelima halaman berlink pribadi**, menyusul B2.
  **Diverifikasi sebelum ditulis, bukan diasumsikan:** klaim lama *"Kami tidak meminta kontak tamu"* diperiksa ke `rsvp.php` — form RSVP memang **tidak** punya input nomor, dan `undangan.js` tidak mengirim `wa`. `rsvp-wa` di sana adalah tombol "beri tahu mempelai" (G1.6), bukan field. Klaimnya akurat, jadi dibiarkan. *(`wa_rsvp` tinggal sisa field di API yang tidak pernah terisi — 0 dari 0 ucapan produksi.)*
  Terbit lewat `scripts/publish-legal.py`; terverifikasi live: 7.213 karakter, tabel pemroses ter-render sebagai `<table>`, tanggal jadi 7 Agustus 2026.

- [ ] **B7** 🤝 `jam` — **S&K & Refund masih menjual produk yang sudah diganti + tidak ada batas waktu bagi pelanggan**
  (a) `syarat-ketentuan.md:9` masih menyebut produk fisik sebagai "kartu QR akses, label souvenir, kartu terima kasih, stiker segel" — model berubah jadi undangan lipat + amplop bernama pada 6 Agustus. "Garansi QR Terbaca" berjanji mengganti seluruh **batch**; bila "batch" kini ditafsirkan 150 undangan lipat premium, biayanya berlipat. Dalam sengketa, ambiguitas ditafsirkan **melawan penyusun dokumen**.
  (b) §12.2 hanya mengikat tanggal **pemesanan**; tidak ada satu kewajiban waktu bagi pelanggan, padahal 3 dari 6 tahap antrean menunggu pelanggan. Order H-21 yang pelanggannya lambat 10 hari = barang terlambat, refund Rp 2,9 juta, bahan + ongkir tetap keluar.
  **Selesai bila:** daftar produk fisik di S&K §1 dan Refund §4 diperbarui · Garansi QR ditulis ulang dengan objek "QR pada undangan cetak" dan remedy proporsional · rujukan bagian di Refund:7 dibetulkan · klausul waktu pelanggan ditambahkan ke §12.2 **setelah owner menyetujui** · diterbitkan lewat `publish-legal.py`.

- [x] **B8** 🤖 `menit` — **Nama berkas foto & QRIS diacak** → **SELESAI & LIVE 2026-08-07**
  `undangan-${order_id}-${acak}-foto-N.png` dengan `crypto.randomBytes(4)` per order. Sebelumnya `undangan-142-qris.png` bisa ditebak dari nomor order yang berurutan — padahal slug halaman undangannya justru **sengaja** diacak. Yang bocor bukan cuma foto pranikah melainkan **gambar QRIS**, instrumen pembayaran mempelai, plus pemetaan nomor order ke identitas pasangan.
  **Terverifikasi live:** dua undangan uji menghasilkan `undangan-900001-cee1e122-foto-1` dan `undangan-900002-a57ccb4e-foto-1` — berkas tetap tampil normal di halaman.
  ⚠️ Berkasnya **tetap hidup** setelah masa aktif habis; itu bagian C8, belum ditutup di sini.

- [x] **B9** 🤖 `jam` — **Token bercakup per halaman** → **SELESAI & LIVE 2026-08-07** *(v2.13.0)*
  Bahan HMAC berubah dari `order_id` jadi **`"{order_id}|{halaman}"`**. Meneruskan link `/rekap/` ke wedding organizer — perilaku yang **pasti** terjadi karena halaman itu memang untuk dipakai bersama — tidak lagi menyerahkan wewenang menekan "setujui proof".
  **Cakupannya lebih luas dari yang diperkirakan: 13 titik, bukan 10.** Selain 5 template + `proof.php` (2×) + WF-01 + WF-02 (4×, bukan 3 — verifikasi submit form ikut), ada dua yang tidak tercatat di rencana dan akan mematikan link kalau terlewat:
  · **WF-06** menghitung token `/upsell/` sendiri untuk pengingat H+3/H+12
  · **dua tautan silang** di `page-tamu.php` → `/rekap/` dan `page-rekap.php` → `/tamu/` yang memakai ulang `$key` halaman asal — justru tautan inilah yang pertama patah oleh B9
  **Rumusnya dipusatkan** di `undangan_token_halaman()` + `undangan_token_sah()` (gagal-tertutup: token/kunci kosong → selalu false). Nol rumus `hash_hmac` mentah tersisa di luar fungsi itu.
  **Terverifikasi live dengan matriks 5×5** (token × halaman) — hanya diagonal yang membuka, seluruh 20 kombinasi silang lainnya **403**:

  | token ↓ / halaman → | isi-data | upsell | proof | tamu | rekap |
  |---|---|---|---|---|---|
  | **isi-data** | BUKA | 403 | 403 | 403 | 403 |
  | **proof** | 403 | 403 | BUKA | 403 | 403 |
  | **tamu** | 403 | 403 | 403 | BUKA | 403 |
  | **rekap** | 403 | 403 | 403 | 403 | BUKA |

  *(baris `upsell` seluruhnya 403 karena order uji belum dibayar — penjaga `is_paid()`, bukan soal token.)*
  Rantai penuh diuji ulang lewat [`../scripts/uji-happy-path.py`](../scripts/uji-happy-path.py): dua order → undangan terbit tier premium, WA terkirim. **Skrip ujinya sendiri ikut diperbarui** — rumus tokennya masih yang lama dan akan membalas 403 yang mudah disalahartikan sebagai pipeline rusak.


---

## P2 — Naikkan konversi & keandalan

> Tidak ada yang gagal hari ini karena nol pengunjung dan nol order. Yang masuk di sini dibenarkan oleh **biayanya** (menit, di berkas yang toh sudah dibuka), bukan besarnya dampak. **C3 dan C9 layak lebih dulu** — keduanya menyentuh alasan orang datang & membeli.

- [ ] **C9** 👤 `hari` — **Isi hulu akuisisi** *(lubang terbesar dalam rencana)*
  Grep `tiktok|iklan|marketplace|blog` di seluruh docs: **nol rencana**. Dari 104 commit: **nol menyentuh akuisisi**. Tema tidak punya template artikel sama sekali, sementara SERP untuk kata kunci niat-beli dikuasai blog vendor pesaing — artikel merekalah yang menanamkan pagu "Rp 500rb–1jt" ke kepala pembeli yang lalu membuka halaman Hormat Rp 1,19 juta.
  **Selesai bila:** tiga aksi berjalan, tanpa kode — (1) **5 vendor/WO pertama didekati** (sudah tertulis sebagai F1.9, sudah dinyatakan bisa paralel, nol biaya — **naikkan ke urutan 2 daftar owner, di atas QA perangkat**); (2) 3 halaman statis ber-SEO lewat `page-teks.php` yang sudah ada ("harga undangan cetak vs digital 2026", "berapa undangan cetak yang sebenarnya dibutuhkan") — di sinilah harga per lembar dibenarkan di depan orang yang sedang membandingkan; (3) lapak di satu direktori pernikahan dengan harga tercantum, CTA tetap WhatsApp sehingga tidak menunggu payment gateway.

- [x] **C3** 🤖 `menit` — **Empat kalimat copy yang melawan penjualan sendiri** → **SELESAI & LIVE 2026-08-07** *(v2.14.0)*
  **(a) "8 dari 8 slot masih tersedia" berhenti disiarkan.** Pada nol order kalimat itu memberi tahu calon pembeli bahwa **belum ada seorang pun yang memesan** — di halaman yang tugasnya menutup Rp 2,9 juta. Kelangkaan hanya bekerja bila sebagian sudah terpakai. Ambangnya jadi konstanta `UNDANGAN_SLOT_TAMPIL = 4` bersebelahan dengan `UNDANGAN_KUOTA_BULAN`, dan angka `8` yang tadinya hardcode di template kini dibaca dari konstanta itu. Cabang "slot penuh" **dipertahankan** — itu justru kabar yang menaikkan urgensi. Diuji untuk kesembilan nilai: sisa 8–5 diam · 4–1 tampil *"Tinggal N dari 8 slot"* · 0 tampil *"sudah penuh"*.
  **(b) "Masa aktif sampai H+7" dihapus** dari kartu Hemat — satu-satunya baris di katalog yang isinya kabar buruk, dipasang di paket jangkar harga terendah. `masa-aktif.php` tetap menegakkan H+7 diam-diam; yang dihapus tampilannya, bukan aturannya. H+30 & 1 tahun tetap tampil karena itu kabar baik.
  **(c) Hero `/harga/` berhenti memimpin dengan konsep hybrid.** Dari *"Satu desain, dua wujud"* jadi **"Nama tiap tamu tercetak di amplopnya — tiba H-14, atau uang kembali."** Konsep hybrid tidak hilang: hero-sub sudah membawanya, dan section garansi tepat di bawahnya memperkuat separuh kedua kalimat itu.
  **(d) Lawan yang sebenarnya dibetulkan.** *"bukan tulis tangan"* mengalahkan lawan yang tidak ada — jadi **"dicetak langsung pada amplopnya, bukan stiker yang ditempel"**. Premis keliru yang melahirkannya juga dibetulkan di komentar `page-tamu.php`: percetakan konvensional justru sering memberi label nama **gratis**, jadi pembedanya bukan harga melainkan tercetak-vs-ditempel.
  ⚠️ **Premis pasar (c) & (d) berasal dari dimensi riset audit, tidak saya verifikasi sendiri** ke situs pesaing. Perubahan (d) benar terlepas dari itu — tercetak vs stiker adalah fakta produk kita. Perubahan (c) bertumpu pada klaim "pesaing melempar digital gratis"; kalau klaim itu meleset, h1 lama layak dipertimbangkan lagi.
  **Terverifikasi live:** blok sisa-slot ter-render **0 kali** pada 8/8 · h1 baru tayang · nol sisa frasa "tulis tangan" · "H+7" hilang dari beranda sementara "H+30" tetap.

- [x] **C1** 🤖 `jam` — **Tiga cacat pada undangan itu sendiri** → **SELESAI & LIVE 2026-08-07** *(v2.15.0)*
  **(a) `color-scheme` dipasang** — `only light` di tema-01 & 02, `only dark` di tema-03. Sebelumnya nol di seluruh CSS undangan (padahal `katalog.css` dan `isi-data.css` sudah memakainya), sehingga mode gelap **paksa** Samsung Internet/Chrome Android bebas membalik warna undangan sendiri dan membatalkan kontras yang sudah dihitung.
  **(b) `--c-ink-soft` digelapkan** — tema-01 `#837f6e` → `#6b6758`, tema-02 `#8d7c6e` → `#77685a`. Rasio saya hitung sendiri, cocok persis dengan audit: **3,76:1** dan **3,73:1** — keduanya gagal WCAG AA, di atas `--c-bg` maupun `--c-surface`. Token itu mewarnai `.lokasi-alamat` — **alamat gedung**, informasi yang menentukan tamu bisa datang atau tidak. Sekarang 5,30:1 dan 5,00:1. tema-03 sudah lolos (9,04:1) dan tidak disentuh.
  **(c) Gerbang tidak lagi mengunci undangan saat JS gagal.** `is-locked` dicabut dari markup; script sinkron setelah `<body>` menyalakannya (tanpa kedip), dan **arloji penjaga 8 detik** mencabutnya bila `undangan.js` tak pernah sempat memasang handler tombol (`js-siap`). Kunci scroll & `position: fixed` gerbang kini sama-sama menuntut kelas `js-gate` — jadi saat dilepas, `.gate` turun jadi blok setinggi satu layar dan undangan **terbaca penuh dengan menggulir**.
  ⚠️ **Menyimpang dari rencana yang melarang `setTimeout`.** Keberatannya — *"halaman menggulir di belakang overlay fixed = tampak hang"* — benar bila yang dilepas **hanya** kunci scroll. Di sini `js-gate` ikut dicabut sehingga gerbangnya berhenti jadi overlay, dan keberatan itu tidak berlaku. Tanpa arloji mustahil punya dua-duanya: tampil terkunci seketika **dan** pulih sendiri saat JS tak datang. `<noscript>` memang tidak menolong — ia hanya menangkap "skrip dimatikan", bukan skrip yang gagal diunduh atau melempar galat, dan justru itu kasusnya.
  **Terverifikasi di browser, bukan dari membaca kode:** jalur normal → `is-locked js-gate js-siap`, gerbang `fixed`, `overflow: hidden` (identik perilaku lama) · jalur gagal (arloji dijalankan tanpa `js-siap`) → kelas tercabut, gerbang jadi `relative`, `overflow: visible`, **8.521 px konten bisa digulir** · `.lokasi-alamat` diukur pada elemen ter-render: `rgb(107,103,88)` di atas putih = **5,67:1, lolos AA** · ketiga demo tetap 200 dan utuh.

- [x] **C2** 🤖 `menit` — **Tombol salin berhenti berbohong, label berhenti tersangkut** → **SELESAI & LIVE 2026-08-07** *(v2.16.0)*
  **(1) `.then(selesai, selesai)`** — argumen kedua itu handler **penolakan**, diisi fungsi sukses. Di WebView WhatsApp (dari mana mayoritas tamu Indonesia membuka undangan) `writeText` bisa menolak, dan justru di kondisi itu tombolnya menampilkan "Tersalin ✓": tamu menempel isi clipboard **lama** ke aplikasi m-banking lalu mengira sudah benar. Sekarang punya jalur gagal sendiri: *"Gagal menyalin — tekan lama nomornya"*.
  **(2) Label tersangkut permanen** — `var asli = btn.textContent` dibaca **setiap** klik, jadi ketukan kedua dalam 1800 ms menyimpan "Tersalin ✓" sebagai teks asli. Tidak butuh kegagalan apa pun dan terjadi di semua peramban. Label kini disimpan sekali di `btn.dataset.label`, di luar handler.
  **(3) Cacat ketiga yang tidak tercatat di rencana:** jalur cadangan `document.execCommand('copy')` mengembalikan **`false` saat gagal tanpa melempar**, jadi `try/catch` saja tidak menangkapnya dan `selesai()` tetap dipanggil — pola bug yang sama persis. Kini nilai kembaliannya diperiksa.
  **(4) Buku tamu** berhenti menampilkan *"Berhalangan · 3 tamu"* — jumlah tamu hanya bermakna bila hadir. Pemeriksaan yang sama sudah dipakai baris berikutnya untuk sesi; ini menyamakannya.
  **(5) `beforeunload`** dilonggarkan dari `state.uploading` jadi **kotor ATAU sedang mengunggah**. Kehilangan paling menyakitkan justru sebelum unggah dimulai: form ini ±10 menit pengisian. Penanda `kotor` dipasang lewat `input`/`change` ber-`capture` di form, ikut ditandai saat foto dihapus, dan **dibersihkan saat kirim berhasil** supaya tidak menahan pemesan yang sudah selesai.
  *Gaya `.gagal-salin` memakai token yang memang ada di CSS undangan (`--c-ink-soft`/`--c-ink`) — token peringatan hanya hidup di `katalog.css` yang tidak dimuat halaman undangan, jadi mengimpornya akan tabrakan di tema-03 yang gelap.*
  **Terverifikasi di browser, dan kegagalannya nyata bukan simulasi:** penyalinan benar-benar ditolak di konteks uji → tombol menampilkan *"Gagal menyalin…"*, bukan "Tersalin ✓" · dua ketukan cepat → label tetap pulih ke "Salin Nomor" · jalur sukses (clipboard distub) → "Tersalin ✓" + kelas `copied`, pulih bersih · ucapan `hadir=tidak, jumlah=3` → ter-render tanpa "3 tamu" · `beforeunload` → **tidak** dicegah saat form belum disentuh, **dicegah** setelah diketik.

- [x] **C4** 🤖 `menit` — **Waktu WIB, jendela kuota, dan otoritas SKU** → **SELESAI & LIVE 2026-08-07**
  **(a) Empat titik `gmdate` → `wp_date`/`current_datetime()`.** Terkonfirmasi di server: timezone situs `Asia/Jakarta` tapi **runtime PHP UTC** — `gmdate` 13:46 sementara `wp_date` 20:46. Disimulasikan lintas jam, dan selisihnya nyata:

  | jam WIB | cara lama | cara baru |
  |---|---|---|
  | 02:00 | 22 hari | **21 hari** |
  | 06:59 | 22 hari | **21 hari** |
  | 07:00 ke atas | 21 hari | 21 hari |

  Antara **00:00–06:59 WIB**, order H-20 lolos sebagai H-21 — menggerus penyangga produksi yang justru menopang Garansi Tepat Waktu. Ikut dibetulkan: kunci transient & jendela awal bulan (pada tanggal 1 dini hari keduanya menunjuk bulan lalu) dan tampilan sisa hari di halaman order admin.
  **(b) `'limit' => 50` → `-1`.** Dengan urutan tanggal menurun, begitu ada 50 order **digital** dalam sebulan — target wajar bila akuisisi berhasil — order cetak terdorong keluar jendela dan checkout menerima pesanan **melewati kapasitas, diam-diam**. Terukur: kueri `-1` hanya **5 ms**, dan transient 10 menit sudah menahan biayanya.
  **(c) SKU jadi otoritas** di `undangan_cart_ada_fisik()`, `is_virtual()` tinggal cadangan lewat `undangan_jenis_produk()`. Produk digital yang dibuat manual di wp-admin bawaannya non-virtual — begitu itu terjadi, pembeli Rp 99rb dimintai alamat lengkap, wajib mengisi tanggal acara, dan diblokir kuota cetak. Ditambah **notice admin** (di-cache sejam) bila ada `HARIH-*` yang lupa dicentang Virtual.
  ⚠️ **(c) sifatnya pencegahan, bukan menambal kerusakan berjalan** — diperiksa ke seluruh 18 produk produksi: nol ketidakcocokan antara jenis-dari-SKU dan `is_virtual()` hari ini.
  **Terverifikasi lewat Store API sungguhan** (batas gerbang, order uji dihapus): H-20 → **ditolak**, pesannya menyebut *"Acara Anda 20 hari lagi"* (hitungan benar) · H-21 → lolos · H-22 → lolos.

- [x] **C7** 🤖 `menit` — **WF-08 berhenti buta, WF-05 berhenti salah hitung** → **SELESAI & LIVE 2026-08-07**
  **(a) WF-08 mengambil `status=any`** lalu baru menyaring ke processing/completed — jadi jendela 100 order dikonsumsi juga oleh `pending`/`failed` dari checkout terbengkalai, dan order berbayar yang webhooknya gagal bisa terdorong keluar jangkauan. Sekarang disaring **di sumbernya** (`processing,completed`); catatan node menyebut multi-status tidak didukung, padahal WF-06 melakukannya persis.
  **(b) Kesunyian palsu jadi alert.** `if (!tertinggal.length) return []` mematikan seluruh cabang — dan owner membaca kesunyian itu sebagai "sistem sehat" sesuai runbook §2. Kini: jendela penuh **dan** nol temuan → melempar galat yang memicu WF-00 (ikatannya dipasang B3). Bila **ada** temuan, tetap tidak melempar — mengirim ulang order yang tertinggal lebih penting, dan penanda `truncated` sudah terbawa ke hilir.
  **(c) WF-05 mengupas sufiks `+cetak`** sebelum tabel masa aktif. Tanpa itu `premium+cetak` jatuh ke fallback `hemat` = **7 hari** untuk pembeli Rp 2,9 juta — pasangan dari bug A1 yang tertinggal.
  **(d) WF-01 menulis status `CETAK_SAJA`** untuk order à la carte murni. Sebelumnya barisnya `MENUNGGU_DATA` meski komentarnya menyatakan tidak, sehingga pembelinya menerima nudge *"isi data undanganmu"* untuk undangan yang tidak pernah ia beli. Diperiksa ke semua pembaca kolom `status`: WF-02 → jatuh ke `diproses` (benar) · WF-05:43 nudge tidak menyala (tujuannya) · WF-05:53 dilewati (benar, tidak ada undangan).
  🔴 **REGRESI B9 DITEMUKAN & DITUTUP DI RONDE INI.** WF-05 membangun link `/rekap/` dari **`r.token` tersimpan di sheet** — dan token itu bercakup `isi-data` sejak B9, jadi **setiap link rekap RSVP harian akan membalas 403**. Lolos dari pemetaan 13-titik B9 karena titik ini *memakai ulang token tersimpan*, bukan memanggil `createHmac`, sehingga tidak tertangkap grep. Disisir ulang: hanya dua pemakaian token tersimpan di seluruh workflow, dan hanya yang ini salah.
  **Terbukti dengan order uji:** `/rekap/` dengan token tersimpan (cara lama) → **403** · dengan token dihitung bercakup `rekap` (cara baru) → **200 terbuka**.
  Ritual import diikuti penuh: verifikasi dari dalam container · import (aktif 9 → 6) · publish kesembilannya · restart · hitung ulang **9** · diverifikasi dari ekspor **live**.

- [x] **C6** 🤖 `menit` — **Daftar nama tamu ikut dibekukan ke snapshot proof** → **SELESAI & LIVE 2026-08-07** *(v2.17.0)*
  `daftar_tamu` masuk ke 19 kunci snapshot (jadi 20). Sebelumnya `_proof_hash` tidak pernah mengunci satu pun nama tamu — padahal amplop bernama adalah **pembeda yang dijual**, 50–150 keping per order, dan tidak ada jejak versi mana yang pemesan setujui. `sort()` yang sudah ada menjaga urutan tetap, jadi hash tetap stabil.
  **Dikerjakan sekarang justru karena nol snapshot ada di produksi** — menambah kunci tidak membatalkan hash siapa pun. Setelah ada order berjalan, perubahan ini mahal.
  **Sengaja TIDAK mengunci keras.** Pemesan yang baru menemukan satu nama salah ketik akan terjebak, dan CS-nya cuma satu orang. Yang dilakukan: `add_order_note()` mencatat perubahan pasca-persetujuan berikut jumlah sebelum/sesudah, supaya sebelum masuk mesin ketahuan bahwa yang dicetak mungkin bukan yang disetujui.
  Halaman proof menampilkan **jumlah** nama, bukan 600 baris — tabel itu untuk memeriksa sekilas, dan menumpahkan seluruh daftar justru mengubur data lain yang harus diperiksa.
  **Terverifikasi end-to-end** dengan order + undangan uji (keduanya dihapus): `daftar_tamu` ada di snapshot (3 nama) · tambah satu nama → hash berubah `64682353b985f933` → `3af97c90b95cb4e4`, jadi nama tamu benar-benar terkunci · bekukan ulang setelah disetujui → **ditolak** · ubah daftar lewat `/tamu/` pasca-persetujuan → **tetap tersimpan** (tidak terjebak) dan catatan order terekam *"sekarang 5 nama (sebelumnya 4)"* · halaman proof menampilkan *"4 nama — ikut dibekukan"* (angka snapshot beku, bukan 5 yang hidup) dengan **nol** nama tumpah ke halaman.

- [x] **C5** 🤖 `menit` — **Harga dibaca dari WooCommerce, bukan ditulis ulang** → **SELESAI & LIVE 2026-08-07** *(v2.20.0)*
  **Enam tempat hidup, bukan tiga.** Rencana hanya mencatat kartu paket; angka `99` juga tertulis di hero, stiker, marquee, dan CTA penutup beranda, plus kartu Digital di `/harga/`. Kalau suatu hari Hemat didiskon, kartunya ikut berubah sementara **lima kalimat "mulai Rp 99rb" tetap menyebut angka lama** — halamannya jadi saling bertentangan, lebih buruk daripada keadaan sebelumnya.
  **Tiga helper:** `harih_harga_ribu()` · `harih_harga_tampil()` (markup kartu) · `harih_harga_mulai()` (termurah, untuk kalimat "mulai Rp …"). Harga **bukan kelipatan seribu** tidak dipaksa ke format "rb" — ditampilkan penuh lewat `wc_price()`; tampilan yang sedikit berbeda lebih baik daripada angka yang salah.
  🔴 **Cacat yang saya buat sendiri, ketahuan karena menguji kasus tepi.** Versi pertama `harih_harga_mulai()` memakai `harih_harga_ribu()` — yang **menolak** harga tak bulat. Diuji dengan promo Rp 89.500: Hemat terlempar dari hitungan dan kalimatnya jadi *"Mulai Rp 179rb"*, **melebihkan harga masuk dua kali lipat**. Dibetulkan: harga termurah apa adanya, dibulatkan **ke bawah**.
  **Terverifikasi dengan mengubah harga sungguhan lalu dikembalikan**, bukan dengan membaca kode:

  | harga Hemat | kartu | kalimat "mulai" |
  |---|---|---|
  | 79.000 (promo) | **79** | Mulai Rp **79**rb |
  | 89.500 (tak bulat) | `wc_price()` penuh | Mulai Rp **89**rb |
  | 99.000 (normal) | **99** | Mulai Rp **99**rb |

  Ketiga produk dikembalikan ke harga reguler, nol promo tersisa.

- [x] **C10** 🤖 `jam` — **Dokumen pemulihan berhenti berbohong** → **SELESAI 2026-08-07**
  `README.md` melompat dari WF-05 ke WF-07 dan daftar impornya hanya menyebut delapan berkas — karena label "WF-06" dipakai skrip backup, sementara `WF-06-reminder-upsell.json` nyata dan aktif. Setelah rebuild VPS, orang yang mengikutinya akan mengimpor delapan, melihat delapan aktif, dan menganggap selesai. Yang hilang justru pengingat H+3/H+12 yang **menjual paket cetak** — tanpa satu galat pun, hanya attach rate diam-diam nol yang mudah disalahartikan "upsell tidak laku".
  **Diperbaiki sampai akarnya:** label dicabut juga dari header `vps/backup-harih.sh` sendiri (itu sumber penamaannya), bukan cuma dari README. Kini `BACKUP-MINGGUAN`.
  **Tujuh perbaikan di README:** WF-06 masuk tabel (9 berkas) · daftar impor menyebut sembilan · langkah "setel Error Workflow lewat UI" dicabut — sudah ditanam di JSON oleh B3, diganti perintah verifikasinya · "Activate semuanya" kini mengingatkan bahwa `import:workflow` **menonaktifkan** workflow · catatan bahwa webhook WF-03 tidak lagi terjangkau sejak `/jadi-reseller/` diturunkan.
  **Runbook §2 dapat pemeriksaan mingguan** — satu perintah SSH menghitung workflow aktif, harus **9**. Ditulis di sana, **bukan** di `cek-live.sh`: skrip itu berjalan dari mesin developer lewat HTTP polos tanpa kredensial SSH, dan hanya bisa melihat tiga workflow yang punya URL publik — enam workflow bercron tidak punya, jadi matinya tidak akan terlihat dari sana. Perintahnya diuji: mengembalikan **9**.
  ⚠️ Jenis kegagalan ini **tidak memicu alert apa pun**: WF-00 hanya menyala saat workflow *berjalan lalu gagal*, bukan saat ia tidak pernah berjalan.

- [ ] **C8** 🤖 `hari` — **Janji retensi 90 hari & hak penghapusan tidak punya satu baris kode pun**
  `kebijakan-privasi.md:53` berjanji data & foto dihapus paling lambat 90 hari setelah masa aktif berakhir, `:63` menjanjikan hak penghapusan ditanggapi ≤7 hari kerja. Satu-satunya penegakan (`masa-aktif.php:66-102`) hanya mengubah post jadi draft. **Tidak ada kode** yang menghapus post `ucapan`, meta `daftar_tamu`, berkas foto & QRIS di uploads, maupun kartu OG (`og.php` hanya bersih saat post dihapus permanen — yang tidak pernah terjadi). Jadi janji "halaman dinonaktifkan" hanya benar untuk HTML-nya; **medianya tetap publik**. Sekaligus masalah inode: Hostinger dibatasi 200rb inode.
  Di P2 karena dengan nol pelanggan belum ada data yang harus dihapus — **tapi jangan melunakkan kalimat kebijakannya sebagai jalan pintas**; kalimat itu sudah tayang dan sudah dibaca.
  **Selesai bila:** pass kedua di cron `masa-aktif.php` mencari draft dengan `nonaktif_sejak` > 90 hari lalu menghapus post ucapan terkait, meta `daftar_tamu`, attachment galeri + qris, dan `wp_delete_post($id, true)` agar hook `og.php` ikut membersihkan. Ada mode `--dry-run` dan jumlahnya dicatat ke `error_log` sebagai bukti kepatuhan.

---

## P3 — Digerbang skala atau menunggu data yang belum ada

- [ ] **D1** 🤖 `jam` — **Rantai order upgrade: `/upsell/` membuat order BARU**
  `page-upsell.php:103` memakai checkout biasa sehingga lahir order dengan ID baru, sementara `undangan_cari_undangan_order()` mencari post undangan lewat meta `order_id` milik order **asal**. Konsumennya: bekukan snapshot, hitung nama tamu, Antrean Cetak, `/tamu/`, `/rekap/`. Kerusakannya bukan "selamanya menunggu": WF-01 mengklasifikasi `UPG-*` sebagai hybrid sehingga **link `/isi-data/` baru tetap dikirim**, dan pembeli diminta mengisi ulang data yang halaman upsell janjikan tidak perlu diisi ulang; bila ia menurut, terbit **undangan kedua** dengan slug berbeda — link yang mungkin sudah ia sebar ke tamu bukan lagi yang dicetak.
  Ditunda karena tiga SKU `UPG-*` sudah live sebagai keputusan terkunci dan upgrade pertama tetap ditangani manual.
  **Selesai bila:** sampai upgrade nyata pertama muncul, runbook memuat langkah menyambungkan tangan (`update_post_meta(undangan_id,'order_id',order_baru)`). Setelah itu baru bangun rantai `_upgrade_dari`.

- [ ] **D2** 🤝 `jam` — **Backup: `--delete` membuatnya replika, bukan cadangan**
  Klaim terburuk yang beredar **keliru**: WordPress, DB, dan uploads hidup di Hostinger — VPS cuma memegang salinannya, jadi VPS lenyap ≠ data pelanggan lenyap. Yang benar-benar hanya ada di VPS: sesi WAHA (bisa scan ulang) dan volume n8n (workflow sudah di git).
  Yang **justru lebih layak diperhatikan dan tidak disadari:** `rsync -az --delete` di `backup-harih.sh:73` membuat mirror uploads mengikuti sisi Hostinger — penghapusan atau ransomware di produksi ikut menghapus "backup"-nya. Baris 61 juga menaruh password DB di baris perintah remote sehingga tampil di daftar proses shared hosting. Dan gzip ≠ enkripsi, padahal `kebijakan-privasi.md:55` menyatakan backup "disimpan terenkripsi".
  **Selesai bila:** `--delete` dicabut atau diganti mode bertanggal · satu langkah salin ke luar VPS (rsync balik ke Hostinger memakai SSH key yang sudah ada — nol biaya, nol kredensial baru) · password DB dipindah ke `--defaults-extra-file`. Bila enkripsi ditunda, **kalimat kebijakan privasi dilunakkan lebih dulu**.

- [ ] **D3** 🤖 `hari` — **Ukuran gambar undangan: nol srcset, nol width/height**
  Grep `srcset|sizes` di seluruh tema: 0 hasil; tidak satu `<img>` di `template-parts/undangan/*.php` punya width/height. Foto dikompresi ke 1600px untuk kolom 480px — paket Favorit dengan 10 foto membawa ~6–9 MB, dan foto sampul ber-`loading="eager"` adalah LCP yang tidak tertolong lazy. Kuota adalah keberatan nyata pembeli Indonesia dan biayanya ditanggung ratusan tamu yang tidak memesan apa pun.
  Digerbang karena solusi termurahnya (turunkan `maxDim` ke 1280) **bukan perubahan bebas risiko**: foto yang sama adalah sumber untuk produk **cetak**, dan resolusi yang dibutuhkan cetak baru terjawab oleh sampel cetak pertama.
  **Selesai bila:** yang gratis dikerjakan sekarang sebagai tumpangan — `aspect-ratio: 1/1` pada `.qris-panel img` (satu-satunya sumber CLS tersisa) dan atribut width/height di keenam template. srcset penuh menunggu WF-02 disentuh untuk hal lain; keputusan `maxDim` menunggu sampel cetak.

- [ ] **D4** 🤝 `jam` — **Infrastruktur VPS: healthcheck WAHA, batas log & disk, satu nomor WA merangkap dua peran**
  (a) `docker-compose.traefik.yml:81-104` memakai `restart: unless-stopped` **tanpa `healthcheck:`** pada engine WEBJS berbasis Chromium — yang khasnya menggantung sambil container tetap "up", jadi Docker tidak akan menyentuhnya.
  (a2) **Container n8n tidak dibuat dari pin-nya** — kedua compose menulis `n8nio/n8n:2.29.10`, tapi `docker inspect` 2026-08-07 menunjukkan container dibuat dari **`n8nio/n8n:latest`**. Versi yang berjalan kebetulan **memang** 2.29.10 (`n8n --version`), jadi tidak ada masalah hari ini — tapi yang melindunginya kebetulan, bukan pin: satu `docker pull n8nio/n8n:latest` + recreate memindahkan n8n ke versi mayor baru tanpa menyentuh compose. WAHA tidak punya masalah ini (dibuat dari tag terpin). Perbaikan: recreate n8n dari compose (`docker compose up -d --no-deps n8n` — **jangan** bare `up -d`, berisiko me-recreate WAHA) supaya container terikat ke tag yang tertulis.
  (b) Kedua compose tidak memuat `logging:` maupun batas memori, dan volume `harih_waha_media` tidak punya pembersih, sementara `backup-harih.sh:97` sengaja tidak pernah menghapus mirror uploads — semua penumpuk tumbuh monoton di disk 25 GB yang dipakai bersama n8n produksi lain milik owner. (Pemakaian sekarang 0,8 GB — belum mendesak.)
  (c) Satu nomor adalah sekaligus sesi WAHA 9 workflow **dan** satu-satunya CTA penjualan cetak — sementara `evaluasi-ide-genz.md:40` menolak RSVP-lewat-WA justru karena "ratusan nomor asing menghubungi satu nomor" adalah pola yang membuat sesi di-ban. **Rencana penjualan yang berhasil menciptakan beban itu.**
  **Selesai bila:** `logging: {max-size: 10m, max-file: 3}` di kedua compose · cek disk dititipkan ke `backup-harih.sh` yang sudah punya cron host & jalur alert mandiri (`[ "$PAKAI" -lt 80 ] || alert_gagal disk`) · healthcheck WAHA dipasang **hanya setelah** endpoint `/health` dan ketersediaan `wget` diverifikasi di dalam container (healthcheck salah perintah = Docker membunuh container sehat) · auto-restart sesi hanya bila ada rem maks 1×/jam dan hanya untuk status `STOPPED`/`FAILED`. Pemisahan nomor menunggu keputusan owner.

- [ ] **D5** 👤 `jam` — **Tidak ada titik masuk paket cetak di bawah Rp 1 juta**
  Paket cetak termurah adalah Hormat Rp 1.190.000, dan pembelian satuan dipatok minimum Rp 1.000.000/transaksi — jadi tidak ada satu pun titik masuk di bawah sejuta, sementara artikel harga yang menguasai SERP menanamkan pagu jauh di bawah itu. Menunggu data closing rate nyata sebelum diubah.

---

## Jangan dikerjakan sekarang — dan kenapa

| Hal | Alasan |
|---|---|
| CI/CD, unit test, TypeScript, APM | Nol pembeli, satu operator, satu deploy per sesi. Yang menutupi cacat WF-02 bukan tiadanya pipeline melainkan tiadanya **satu uji happy-path manual** (A3). Bangun ritual uji 10 menit, bukan infrastruktur. |
| Auto-restart sesi WAHA tiap 10 menit | Loop restart otomatis pada sesi bermasalah **menaikkan** risiko ban — risiko yang dokumen sendiri sudah pakai untuk menolak ide lain. Email sudah jadi kanal utama dan kegagalan WA sudah diperlakukan non-fatal. |
| Konfirmasi identitas sebelum tombol setujui proof | Menambah friksi di titik tenggat H-21 pada pembeli Rp 2,9 juta, demi sengketa yang belum pernah terjadi. **B9 sudah menyelesaikan masalah teknisnya.** |
| Autosave penuh form `/isi-data/` | Yang benar-benar hilang saat tab dibuang adalah 10 foto hasil kompresi canvas — localStorage teks-saja **tidak menyelamatkannya** (butuh IndexedDB, pekerjaan jam-an). Cukup longgarkan `beforeunload` (C2). |
| Turunkan `maxDim` kompresi 1600 → 1280 | Foto yang sama adalah sumber untuk produk **cetak**. Menukar mutu cetak dengan byte web sebelum sampel pertama dicetak = menebak ke arah yang tidak bisa dibatalkan. |
| Hapus SKU Hemat / ubah H+7 jadi 1 tahun | Keputusan bisnis berjam-jam yang menyentuh WooCommerce, deskripsi produk, dan S&K §4. Yang merugikan hari ini cuma **satu baris copy** (C3b). ⚠️ Kunci `'hemat'` **tidak boleh** dihapus dari kode — `masa-aktif.php` memakainya sebagai default paket tak dikenal. |
| Pecah kolom sheet `paket` jadi dua kolom | Benar secara desain, tapi mengubah skema sheet + JSON di banyak node sekaligus — tepat jenis pembongkaran yang mahal saat ritual import n8n sudah terbukti rawan. Kupas sufiksnya saja (A1, C7). |
| Kejar paritas fitur dengan pemain digital besar | Mereka merekrut percetakan sebagai partner white-label mulai ±Rp 50rb/bulan. Sisi digital **tidak bisa dan tidak perlu** dimenangkan. Yang tidak bisa di-white-label siapa pun: mutu cetak, amplop bernama tercetak, tiga garansi tertulis. |

---

## 👤 Keputusan yang menunggu owner

> 📄 **Versi siap diskusi ada di [`diskusi-owner-2026-08-07.md`](./diskusi-owner-2026-08-07.md)** — ditulis supaya bisa dibaca orang yang belum pernah melihat produk maupun kodenya, lengkap dengan duduk perkara, pilihan, dan bukti apa yang menyelesaikan tiap poin. Daftar di bawah adalah versi ringkasnya untuk kerja sehari-hari.


1. **Duitku** — sudah ada kabar sejak 2026-08-04? Bila belum, **apakah kita membuka jalur bayar manual** (invoice WA + transfer, order WooCommerce dibuat tangan lalu di-set `processing`) untuk paket digital Rp 99–299 ribu sekarang? Itu satu-satunya cara gerbang 10-penjualan bergerak minggu ini, dan mekanismenya **sudah Anda setujui** untuk order cetak Rp 2,9 juta. → A7

2. **Reseller** — `/jadi-reseller/` hidup dan menerima pendaftar dengan janji komisi 30% tiap order, sedangkan keputusan terkunci membayar rupiah tetap untuk cetak. Pilih **sekarang**: (a) koreksi klaimnya, atau (b) tutup pendaftaran sampai reseller memang diinginkan. Arsip TASKS sudah mencabut "rekrut 3 reseller" tapi `panduan-manual.md` masih menyuruhnya. → B5

3. **Garansi Tepat Waktu** — tidak punya satu klausul pun yang membatasi waktu **pelanggan**, padahal 3 dari 6 tahap antrean menunggu pelanggan. Setuju menambahkan *"jaminan bergeser hari-per-hari bila data/daftar tamu/persetujuan proof terlambat lebih dari 4 hari"*? Ini mengubah dokumen legal yang sudah tayang. → B7

4. **Bobot paket** dicatat **tiga versi berbeda** di dokumen (2/5/9 kg vs 2/4/7 kg), sementara produk di WooCommerce memakai 2/4/7 dan ongkir dikunci Rp 150.000 se-Indonesia **tanpa sumber tercatat**. Angka mana yang benar, dan apakah Rp 150.000 pernah diuji ke tarif kurir nyata untuk 7 kg ke luar Jawa?

5. **Backup tidak terenkripsi**, padahal Kebijakan Privasi yang tayang menyatakan "disimpan terenkripsi". Sementara enkripsi belum dipasang: lunakkan kalimat kebijakannya, atau langsung pasang enkripsi? → D2

6. **Akuisisi** — mana yang diambil lebih dulu: mendekati 5 vendor/WO (sudah tertulis, nol biaya, sudah dinyatakan bisa paralel) atau daftar lapak di direktori pernikahan? **Saran: vendor dulu.** Keduanya butuh tangan Anda, bukan kode. → C9

---

## 👤 Aksi owner yang tetap berlaku dari rencana lama

- **Cetak satu sampel lengkap** (undangan lipat + amplop bernama) — menjawab bobot nyata, waktu lipat per unit, uji pindai QR, mutu amplop, dan **apakah mesin creasing sanggup** (800 lipatan/bulan; kalau manual, hitungan marjin batal). Sekalian catat waktu untuk **50 unit**, bukan hanya 100 — paket Hormat menanggung setup yang sama dengan pendapatan terkecil.
- **QA perangkat riil** — iPhone Safari & Android Chrome. Checklist di `panduan-manual.md` langkah 5, ditambah yang belum pernah disentuh tangan manusia: mode gelap · galeri kolase · tombol Waze · tombol WA mempelai setelah RSVP · penolakan foto resolusi rendah. ⚠️ WA meng-cache preview per URL — uji dengan `?x=1`.
- **Review gaya bahasa** pesan otomatis di `copywriting-pesan.md` · **review visual tema-02 & tema-03 di HP** sebagai calon pembeli.
- **Kebijakan nomor WA bisnis** — jangan logout, pakai wajar, jangan blast ke nomor tak dikenal. Sesi ter-ban = seluruh delivery mati.

---

## ⚠️ Wajib dibaca sebelum menyentuh n8n / deploy

1. **Import n8n bisa diam-diam memakai berkas BASI.** `scp` ke `/tmp` server gagal (uid lain + sticky bit) **tapi `import:workflow` tetap melaporkan sukses** — WF-02 live sempat mundur tanpa satu pesan galat. Prosedur benar (unggah ke `/root/wf-import`, nama baru di container, **periksa isi dari dalam container**, verifikasi hasil ekspor) ada di [`../n8n/workflows/README.md`](../n8n/workflows/README.md). → lihat juga A2 & B3.
2. **`import:workflow` MENONAKTIFKAN workflow yang diimpor — tanpa peringatan.** Terukur 2026-08-07: sebelum import 9 aktif, sesudah **0 aktif**, sementara `list:workflow` tetap menampilkan kesembilannya seolah tidak ada yang berubah. Selama jendela itu webhook `wc-order` & `form-undangan` mati — **order yang masuk hilang tanpa jejak**. Jadi `publish:workflow` wajib dijalankan untuk **setiap** id yang diimpor, bukan hanya yang isinya berubah, lalu `docker restart harih-n8n`, lalu **hitung ulang** `list:workflow --active=true` (harus kembali ke 9). Kerjakan saat tidak ada order berjalan.
3. **Container hariH bernama `harih-n8n`.** VPS yang sama menjalankan **`root-n8n-1`** — n8n produksi lain milik owner. Jangan pernah menjalankan perintah n8n tanpa menyebut container.
4. **Jangan taruh node yang bisa menghasilkan NOL item di tengah rantai.** Node HTTP n8n memecah respons array jadi satu item per elemen, jadi `[]` = nol item = seluruh cabang berikutnya tidak dijalankan, **tanpa galat**. Ini sempat mematikan seluruh reminder harian, dan pola yang sama menyembunyikan alert di WF-08 (C7).
5. **Restart n8n:** `docker restart harih-n8n`. Hindari bare `docker compose up -d` — berisiko me-recreate WAHA.
6. **OPcache:** perubahan mu-plugin butuh sampai ±60 dtk sebelum web SAPI menyajikannya, dan `wp eval` **tidak** memperlihatkannya (CLI punya cache sendiri). Lihat [`runbook.md`](./runbook.md) §9d.
7. **Naikkan `HARIH_VERSION`** setiap menyentuh CSS/JS.
8. **`cek-live.sh` bisa memunculkan `HTTP 000` palsu** — hambatan ada di jalur jaringan lingkungan kerja, bukan di situs (didiagnosis 2026-08-06: DNS 0,002 dtk, TCP normal, TLS menggantung, mengenai Hostinger **dan** VPS serentak, kontrol ke host luar tidak pernah gagal). **Ulangi per URL sebelum menyimpulkan ada regresi. Jangan naikkan paket hosting atas dasar ini.**

---

## Keputusan yang sudah terkunci

| Topik | Keputusan |
|---|---|
| Tangga digital | Pertahankan tiga tingkat — yang rusak copy-nya, bukan tier-nya |
| Attach rate | Diukur **per tingkat**, tidak pernah digabung. Hipotesis: Hemat 2–5% · Favorit 10–15% · Premium 30–40% |
| Komisi | Digital **30%**. Fisik **rupiah tetap**: Rp 150rb (Hormat) · Rp 300rb (Resepsi) · Rp 500rb (Grand). Satuan: nol |
| Reseller vs vendor | Satu orang **tidak boleh** jadi dua-duanya |
| Katalog | Tetap menjual **paket penuh** — pembeli resepsi gedung bisa langsung beli tanpa lewat digital |
| Upsell pasca-bayar | Tiga SKU upgrade berharga tetap. Kredit **Rp 300.000 rata**, berlaku **14 hari dengan hitung mundur tampil** |
| Halaman upgrade | **Hanya Resepsi & Grand.** Hormat dicabut (marjin ±1,1× pekerjaan reguler — setelah disesuaikan risiko, lebih buruk daripada tidak mengambilnya). `CETAK-HORMAT` tetap dijual penuh di katalog |
| Bingkai penawaran | **Kredit, bukan diskon.** "Paket digitalmu sudah dibayar — tinggal cetaknya" |
| À la carte | **Dilarang muncul di halaman upsell.** Minimum **Rp 1.000.000/transaksi** |
| Pengiriman | **Satu metode: gratis se-Indonesia.** Ongkir dalam struktur biaya: Rp 150.000 *(belum diuji ke tarif kurir nyata — lihat pertanyaan owner #4)* |
| Kuota produksi | **8 order cetak/bulan.** ⚠️ Kuota per bulan **musim, bukan rata** — pernikahan menumpuk; 8×12 akan meleset jauh |
| Gerbang `/shop/` | Produk cetak & satuan **disembunyikan** sampai satu order uji internal tuntas penuh |
| Urutan perbaikan bila attach rate jelek | Foto undangan lipat asli di dalam amplop bernama · salinan soal orang tua & sesepuh · garansi · hitung mundur. **Angka kredit paling akhir** |

**Metrik pengendali** *(urutannya penting)*: marjin per **jam tangan** → closing rate → attach rate per tingkat → distribusi paket → tingkat reprint → pendapatan berulang vendor.

### 📏 DIUKUR 2026-08-08 — menggantikan seluruh estimasi sebelumnya

Sampel `TEST-173` dicetak & dilipat sungguhan. Angka di bawah **pengukuran, bukan model** — semua estimasi lama (4,5 jam/100 unit, bahan Rp 1.500/unit, bobot 2/4/7 kg) dicabut.

**Temuan yang mengubah cara membaca kapasitas: pisahkan jam DINDING dari jam TANGAN.** Dari ±118 detik per unit, **±90 detik printer jalan sendiri** — batch berikutnya bisa dicetak sambil batch sebelumnya dilipat. Estimasi lama menghitung semuanya sebagai waktu owner, dan itu yang membuat paket kecil terlihat jauh lebih buruk daripada kenyataannya.

Setup per pesanan **±40 menit** (muat kertas + cetak uji registrasi · set posisi creaser · QC lembar pertama · packing & label kurir). Bahan **Rp 3.200/unit** (kertas 900 · amplop 1.500 · label 150 · tinta 500 · gagal 5% 150).

| Paket | Unit | Jam dinding | **Jam tangan** | Marjin | per jam dinding | **per jam tangan** |
|---|---|---|---|---|---|---|
| Hormat | 50 | 2,3 | **1,2** | Rp 855rb | Rp 372rb | **Rp 713rb** |
| Resepsi | 100 | 4,0 | **1,7** | Rp 2,40 jt | Rp 599rb | **Rp 1,41 jt** |
| Grand | 150 | 5,7 | **2,2** | Rp 5,18 jt | Rp 908rb | **Rp 2,35 jt** |

Pembanding pekerjaan cetak reguler: **Rp 100–200rb/jam**. Terhadap patokan tertinggi, per jam dinding: Grand 4,5× · Resepsi 3,0× · Hormat **1,9×**.

**Mesin creasing bukan hambatan.** ±8 detik/lembar termasuk lipat → 800 lipatan/bulan = **±1,8 jam**. Pertanyaan terbesar di rencana lama gugur.

**Bobot jauh lebih ringan dari yang tercatat** — 1 set = 22 gram (A4 220gsm 14g + amplop 8g). Sudah diperbarui di WooCommerce; ketiga SKU `UPG-*` ternyata **tidak punya bobot sama sekali** dan kini terisi.

| Paket | Bobot terukur | Tercatat sebelumnya |
|---|---|---|
| Hormat | **1,4 kg** | 2 kg |
| Resepsi | **2,6 kg** | 4 kg |
| Grand | **3,8 kg** | 7 kg |

**Alokasi ongkir Rp 150.000 aman, dengan slack besar.** Terburuk (Grand ke Indonesia Timur) ±Rp 200rb; mayoritas pesanan di Jawa ±Rp 35–50rb. Ada ruang **Rp 80–100rb per pesanan** yang selama ini tidak terhitung.

**QR 31 mm cukup, jangan diperbesar** — tanpa laminasi, pada `ecc=H` modulnya ±0,9 mm, terbaca nyaman di 15–25 cm. Diperiksa juga di berkas sampel: QR duduk **58,8 mm dari garis lipatan**, jadi risiko retak-lipatan tidak berlaku pada tata letak ini.

**Kuota 8/bulan jauh di bawah kapasitas** (8 × 1,7 jam tangan ≈ 14 jam) — **tetap ditahan di 8** untuk bulan pertama. Batasnya sekarang bukan kapasitas mesin, melainkan belum pernah satu pesanan pun dikirim tepat waktu.

---

## Ditunda sampai ada pembeli / order cetak nyata

**Digerbang 10 pembeli asing:** AI copywriter (Gemini Flash lewat OpenRouter — ⚠️ slug model wajib diverifikasi, dan OpenRouter **wajib** masuk tabel pemroses data di Kebijakan Privasi sebelum menyala) · video cover Premium (⚠️ penggerbangnya **bandwidth**, bukan disk: 10 MB × 300 tamu = 3 GB per undangan) · pratinjau langsung di form · section playlist Spotify (facade) · QR check-in tamu di venue.

**Menunggu order cetak nyata:** engine render SVG→PDF (wajib di VPS — Hostinger tidak bisa Inkscape) · imposition + cut file · upsell otomatis penuh · catat waktu-biaya nyata per order · protokol uji harga.

**Tidak digerbang, bisa dikerjakan kapan saja diminta:** sesi kedatangan tamu (`?sesi=1` di link personal — penghalangnya sudah selesai; butuh satu field baru → satu ritual WF-02) · batas panjang field & pembulatan kuantitas (menunggu template cetak benar-benar ada).

**Belum terverifikasi (bukan belum dikerjakan):** komponen **berdata** di `/tamu/`, `/proof/`, `/upsell/` belum pernah dilihat dalam mode gelap — ketiganya menuntut order WooCommerce yang benar-benar ada. **Paling murah menumpang A3.**

**Backlog v2:** occasion baru (khitanan, aqiqah, wisuda, e-card Lebaran) · add-on WA blast · amplop digital ter-escrow via QRIS · migrasi log operasional Sheets → Postgres · tema premium eksklusif · arsip otomatis undangan kedaluwarsa (bertumpang dengan C8) · custom domain per undangan · multi-bahasa · tema builder.

---

## Akses

Hostinger `ssh -p 65002 u803921702@147.93.80.20` (`domains/harih.id/public_html`) · VPS `ssh root@31.97.50.197` (`/opt/harih`) · rahasia: `vps/.env` + `vps/google-sa.json` (lokal, gitignored, **tidak pernah masuk riwayat git** — diverifikasi) = cermin server.

Aksi owner: [`panduan-manual.md`](./panduan-manual.md) · operasional: [`runbook.md`](./runbook.md) · import n8n: [`../n8n/workflows/README.md`](../n8n/workflows/README.md) · riwayat & konteks lama: [`arsip/TASKS-2026-08-07.md`](./arsip/TASKS-2026-08-07.md).

**Known limitation (diterima):** idempotency Google Sheets tidak atomik — dimitigasi topic Action `woocommerce_order_status_processing` + pola append-then-verify di WF-01. Sisa risiko race kecil dan hilang total saat migrasi Postgres. *(Catatan: penjaga verifikasinya saat ini tidak bisa menyala — lihat B4.)*
