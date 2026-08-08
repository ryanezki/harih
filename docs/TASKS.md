# TASKS — hariH

**Status:** aktif · **Ditulis ulang:** 2026-08-07 setelah audit menyeluruh 6 dimensi · **Checkpoint terakhir:** 2026-08-08 (keamanan · kode PHP · frontend · n8n/infra · produk-bisnis · benchmark pasar) · **Ditambah 2026-08-08:** bagian [🎨 PU](#-pu--review-uiux-menyeluruh-8-agustus-2026) — review UI/UX 7 dimensi.

> Versi sebelumnya (553 baris, checkpoint bertumpuk) diarsipkan di [`arsip/TASKS-2026-08-07.md`](./arsip/TASKS-2026-08-07.md). Dokumen ini hanya memuat **yang berlaku sekarang**. Riwayat lengkap ada di git log.

**Cara pakai:** centang `- [x]` saat selesai. ID (`A1`) stabil. 👤 = butuh tangan owner · 🤖 = bisa dikerjakan asisten · 🤝 = keduanya. Urutan grup adalah urutan kerja — jangan lompat ke P1 sebelum P0 tuntas.

**Tingkat keyakinan temuan:** 27 dari 30 item sudah **dieksekusi dan diverifikasi di produksi** — bukan lagi laporan audit. Lima di antaranya ternyata **premisnya meleset** dan dikoreksi di entrinya masing-masing, jadi jangan menganggap teks audit asli sebagai kebenaran:

| Item | Yang dikira | Kenyataannya |
|---|---|---|
| A5 | checkout Rp 2,9 jt bisa tanpa tombol bayar | 6 gateway lolos saringan — sehat |
| B7 | S&K menjual produk yang sudah diganti | kesembilan produk masih dijual; daftarnya justru **tidak menyebut produk utama** |
| C4(c) | otoritas SKU sedang rusak | nol ketidakcocokan di 18 produk — murni pencegahan |
| C5 | harga hardcode di 3 tempat | **enam** tempat |
| D4 | disk 25 GB nyaris penuh | **193 GB, terpakai 12%**; `/health` WAHA ternyata hanya cek disk, bukan status engine |

**Selalu periksa file yang dirujuk sebelum mengeksekusi**, terutama sebelum menyentuh n8n.

---

## ⏸ CHECKPOINT — tutup sesi 8 Agustus 2026

**Mulai dari sini di sesi berikutnya.** `HARIH_VERSION 2.21.0` · 9 workflow aktif · smoke **32/32** · WAHA `healthy`, sesi `WORKING`.

**27 dari 30 item selesai.** Sisa tiga di bawah. Ditambah **31 item** dari review UI/UX ([🎨 PU](#-pu--review-uiux-menyeluruh-8-agustus-2026)) — **PU-A (U1–U9) SELESAI & LIVE** *(v2.22.0)* · **PU-B (U10–U20) LIVE** *(v2.24.2)* · **PU-C LIVE kecuali U22** · **SELURUH 31 ITEM PU LIVE** *(v2.33.0)*. Smoke 32/32.

> ### ▶ MULAI DARI SINI
> 1. **A8** — satu-satunya yang butuh tangan owner.
> 2. **C8** lalu **D3**.
> 3. **Foto produk cetak SUNGGUHAN** — kelima slot kini terisi render AI berlabel *"ilustrasi"*; memotret sampel `TEST-173` dan bengkel percetakan menggantinya dengan bukti nyata dan mencabut labelnya. Lalu **keputusan `maxDim`** untuk menutup sisa D3.
> 4. Pada order cetak sungguhan pertama: periksa dua cabang `/proof/` yang belum pernah dilewati data nyata (lihat catatan ⚠️ di kotak PU-A).

| | | |
|---|---|---|
| **C8** `hari` 🤖 | Retensi 90 hari & hak penghapusan **tanpa satu baris kode** — janji ini sudah tayang di Kebijakan Privasi. `masa-aktif.php` hanya mendraft post; **medianya tetap publik**. | *disarankan berikutnya* |
| **D3** `hari` 🤖 | Gambar undangan: nol `srcset`, nol `width/height`. Yang bisa dikerjakan tanpa menunggu keputusan resolusi cetak: atribut dimensi di enam template + `aspect-ratio` pada QRIS (sumber CLS terakhir). Keputusan `maxDim` masih menunggu — sampel cetak kemarin tidak memakai foto pemesan. | |
| **A8** `menit` 👤 | **Duitku** — kejar approval + tiga pertanyaan (profil nominal Rp 99–299rb vs paket Rp 5,9 jt · mekanisme refund · plafon per kanal). Satu-satunya yang butuh tangan owner. | |

**Data uji yang SENGAJA disimpan** (permintaan owner) — jangan dihapus tanpa konfirmasi:
- Pesanan **TEST-173** + undangan **174** (`/u/test-rangga-sekar/`) — sumber berkas sampel cetak
- Ditandai `_harih_uji=1` → **tidak menghitung kuota** (Agustus tetap 0 dari 8), tampil di Antrean Cetak berlabel **"UJI INTERNAL"**
- Baris sheet `orders` order_id 173, `premium+cetak`, `SUDAH_JADI`

**Yang berubah paling besar sesi ini:**
1. **Pipeline pemenuhan otomatis PASTI GAGAL sebelum diperbaiki** — `ReferenceError` di satu-satunya jalur pembeli sah. Pembeli pertama akan menerima HTTP 500. Ditutup (A1) lalu dibuktikan end-to-end (A3).
2. **Daftar tamu semua pelanggan bisa dipanen** lewat loop ID berurutan tanpa autentikasi. Ditutup (B1), dibuktikan dengan panen sungguhan lebih dulu.
3. **Angka produksi diukur, bukan lagi diestimasi** — mesin creasing sanggup, bobot jauh lebih ringan, jam tangan terpisah dari jam dinding. Seluruh estimasi lama dicabut.
4. **Jalur bayar manual dibuka** — Duitku masih sandbox, jadi tombol bayar lama mengarah ke halaman pembayaran uji. Semua CTA kini ke WhatsApp, dan kembali otomatis begitu kredensial production dipasang.
5. **Akuisisi dikeluarkan dari rencana** atas keputusan owner — lihat bagian 🚫.

**Yang perlu diingat sebelum menyentuh apa pun:** baca bagian *"⚠️ Wajib dibaca sebelum menyentuh n8n / deploy"* di bawah. Delapan jebakan, semuanya pernah menggigit.

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

- [x] **A7** 🤝 `jam` — **Buka jalur bayar manual untuk paket digital — berhenti menunggu Duitku**
  Dokumen lama menyebut Duitku "gerbang tunggal", tapi F1.6 **sudah merestui** invoice WA + transfer manual untuk order Rp 2,9 juta. Jalur yang sah untuk produk 30× lebih mahal belum pernah diterapkan ke produk Rp 99–299 ribu yang 10 penjualannya adalah gerbang sesungguhnya. **Tidak butuh satu baris kode baru:** owner kirim rekening/QRIS → buat order WooCommerce → set `processing` → WF-01 menyala persis seperti biasa. Setiap hari menunggu adalah hari tanpa data attach rate, closing rate, dan distribusi tier.
  **Selesai bila:** tombol "Pesan lewat WhatsApp" di samping tombol checkout di katalog · runbook memuat langkah membuat order manual + set processing · satu penjualan uji tuntas sampai undangan terbit (menumpang A3).
  → **SELESAI 2026-08-07.** Seluruh CTA beranda & `/satuan/` mengarah ke WhatsApp (nol tombol add-to-cart), `harih_bayar_online_siap()` mengembalikannya otomatis begitu Duitku production dipasang, dan langkah pesanan manual + template pesan lengkap ada di [`runbook.md`](./runbook.md) §7c.

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

- [x] **B5** 🤝 `jam` — **`/jadi-reseller/` & WF-03 menjanjikan komisi 30% "tiap order"**
  Ditulis di h1, hero-sub, langkah 3, daftar syarat, meta description, og:description, dan tagline kaki — hanya baris kaki memakai kata "digital". Berhadapan dengan keputusan terkunci: digital 30%, fisik rupiah tetap Rp 150/300/500rb. Reseller yang menjual Paket Resepsi mengharapkan Rp 870.000 dan menerima Rp 300.000 — **selisih Rp 570.000 per order** di kanal yang seluruh nilainya kepercayaan. Lebih cepat lagi: kupon `RES-` **ditolak di keranjang cetak** sehingga order terbesar berhenti di langkah terakhir. **Halaman ini live dan menerima pendaftar hari ini.**
  **Selesai bila:** setiap klaim 30% dikualifikasi jadi "30% untuk paket digital · Rp 150/300/500rb untuk paket cetak · item satuan tanpa komisi" di ketujuh titik + teks WF-03. Bila owner memilih menutup pendaftaran (lihat pertanyaan owner), halaman diturunkan sebagai gantinya. Kontradiksi rekrut-vs-tidak antara arsip TASKS dan `panduan-manual.md:159` ikut diselesaikan.
  → **SELESAI 2026-08-07 — halaman DITURUNKAN**, bukan dikoreksi (keputusan owner). Komisi 30% dari paket digital Rp 179rb hanya Rp 54.000; tidak menggerakkan siapa pun, sementara untuk cetak kuponnya memang diblokir. Nol pendaftar, jadi tidak ada yang perlu dihubungi. Kode & WF-03 tidak dihapus — menghidupkan kembali cukup menerbitkan halamannya.

- [x] **B6** 🤖 `menit` — **Kebijakan Privasi menyebut data yang benar-benar dikumpulkan** → **TERBIT & LIVE 2026-08-07**
  Yang paling telanjang adalah **nomor rekening**: formulir publik `/jadi-reseller/` mengumpulkan nama, WA, bank, dan norek; WF-03 mengirimkannya lewat WhatsApp + email ke owner lalu menyimpannya di tab `resellers` Google Sheets — kategori data keuangan tanpa satu kalimat pun dasar pemrosesan maupun retensi.
  **Lima bagian diperbarui:** §1 dapat sub-bagian **"Dari calon reseller"** (dengan dasar pemrosesan: pelaksanaan perjanjian kemitraan, dan pernyataan bahwa rekening hanya dipakai membayarkan komisi) dan butir **daftar nama tamu** di "Dari pemesan" (maks. 600 nama, data pihak ketiga, dipakai hanya untuk amplop & link personal) · §2 menambahkan pembayaran komisi sebagai tujuan · §4 memperluas keperluan Google Sheets · §5 memberi retensi pada daftar tamu & data reseller · §8 pengecualian GA4 diperluas dari "formulir pengisian data" jadi **kelima halaman berlink pribadi**, menyusul B2.
  **Diverifikasi sebelum ditulis, bukan diasumsikan:** klaim lama *"Kami tidak meminta kontak tamu"* diperiksa ke `rsvp.php` — form RSVP memang **tidak** punya input nomor, dan `undangan.js` tidak mengirim `wa`. `rsvp-wa` di sana adalah tombol "beri tahu mempelai" (G1.6), bukan field. Klaimnya akurat, jadi dibiarkan. *(`wa_rsvp` tinggal sisa field di API yang tidak pernah terisi — 0 dari 0 ucapan produksi.)*
  Terbit lewat `scripts/publish-legal.py`; terverifikasi live: 7.213 karakter, tabel pemroses ter-render sebagai `<table>`, tanggal jadi 7 Agustus 2026.

- [x] **B7** 🤝 `jam` — **Dokumen legal menyebut produk yang benar-benar dijual** → **SELESAI & TAYANG 2026-08-08**
  ⚠️ **Premis audit sebagian KELIRU, dan itu mengubah perbaikannya.** Audit menyimpulkan S&K "masih menjual produk yang sudah diganti" — kartu QR akses, label souvenir, kartu terima kasih, stiker segel. Diperiksa ke katalog WooCommerce: **kesembilan produk itu masih dijual**, termasuk `SATUAN-KARTU-QR` dan `SATUAN-KARTU-HOLO`. Yang berubah 6 Agustus hanya isi **paket**, bukan katalog satuan.
  Jadi daftarnya **bukan basi — melainkan tidak menyebut produk utamanya.** S&K sama sekali tidak menyebut **undangan cetak lipat + amplop bernama tamu**, padahal itulah yang dijual seharga Rp 1,19–5,9 juta dan yang ketiga garansinya lindungi. Kalau saya menghapus daftar lama seperti disarankan audit, saya justru mencabut perlindungan dari produk yang masih dijual.
  **Yang diperbaiki:** §1 S&K & §4 Refund kini menyebut undangan lipat + amplop sebagai produk **utama**, diikuti produk pendukung yang memang masih ada di katalog satuan.
  **Garansi QR Terbaca** — objeknya diperluas ke **QR pada undangan cetak lipat** (sebelumnya hanya "kartu QR"), dan remedinya dibuat **proporsional**: unit yang bermasalah dicetak & dikirim ulang gratis; **seluruh batch** hanya bila kegagalannya sistemik. Sebelumnya satu QR gagal mewajibkan mengganti seluruh batch — pada 150 undangan lipat premium, itu eksposur yang tidak berbatas.
  **Rujukan pasal yang salah dibetulkan:** Kebijakan Refund menyatakan *"Bagian 1–4 mengatur produk digital; bagian 5 mengatur produk cetak"* padahal produk cetak ada di **§4** dan §5 adalah cara mengajukan. Dalam sengketa, rujukan yang meleset ditafsirkan melawan penyusun dokumen.
  *(Klausul batas waktu pelanggan — bagian (b) B7 — sudah dipasang 7 Agustus atas keputusan owner, memakai bentuk **syarat mulai** alih-alih rumus geser.)*
  Terbit lewat `publish-legal.py`; terverifikasi tayang: produk utama muncul, garansi QR proporsional, rujukan bagian benar, nol sisa frasa lama.

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

- [x] **D1** 🤖 `jam` — **Rantai order upgrade** → **SELESAI & LIVE 2026-08-08** *(v2.21.0)*
  Pembelian `UPG-*` lahir sebagai order **baru** lewat checkout biasa, sementara undangannya melekat pada order **asal** lewat meta `order_id`. Rencana lama menyarankan menyambungkannya **manual** per pesanan; dikerjakan penuh sekarang karena tiap sambungan manual adalah langkah yang bisa terlupa tepat saat pesanan Rp 2,9 juta sedang berjalan.
  ⚠️ **Saya hampir menyimpulkan auditnya keliru.** Sekilas, `UPG-*` murni tampak jatuh ke `cetak_saja` sehingga tidak dikirimi link isi data. Diperiksa baris per baris: `jenisItem()` mengembalikan **`hybrid`** untuk `UPG-*`, dan `adaDigital` memperlakukan `hybrid` sebagai "ada digital" — jadi order upgrade **memang** jatuh ke `hybrid` dan **memang** dikirimi link isi data baru. Audit benar; pembacaan cepat saya yang salah.
  **Tiga lapis diperbaiki:**
  · **Rantai dicatat** — link beli di `/upsell/` membawa `upgrade_dari`, disimpan di sesi WooCommerce saat add-to-cart, lalu dituliskan ke meta `_upgrade_dari` lewat hook Store API (checkout blok tidak meneruskan parameter URL ke permintaan yang membuat order). Sesi dibersihkan setelah order jadi supaya pembelian berikutnya tidak ikut tertandai.
  · **Rantai diikuti** — `undangan_cari_undangan_order()` menelusuri `_upgrade_dari` bila pencarian langsung gagal. Batas 5 langkah + pencatatan order yang sudah dikunjungi: rantai melingkar (meta salah isi) tidak boleh berubah jadi loop tak berujung.
  · **WF-01 mengenali jenis `upgrade`** — order yang seluruhnya `UPG-*` tidak lagi dikirimi link isi data, statusnya `UPGRADE` (jadi reminder harian tidak menagih data), dan pesannya diganti: *"Data undanganmu tidak perlu diisi ulang — kami pakai yang sudah kamu isi sebelumnya."*
  **Kerusakan yang ditutup:** pembeli diminta mengisi ulang data yang halaman upsell justru janjikan tidak perlu; kalau ia menurut, terbit **undangan kedua dengan slug berbeda** — dan link yang mungkin sudah ia sebar ke tamu bukan lagi yang dicetak. Ditambah seluruh produksi (snapshot, proof, daftar tamu, rekap, antrean) gagal **diam-diam** pada order yang sudah dibayar.
  **Terverifikasi:** klasifikasi diuji 5 kasus di runtime n8n — upgrade murni → tanpa link, status `UPGRADE`; **keranjang campur digital+upgrade tetap dapat link** (benar: ada pembelian digital baru); tiga jenis lain tidak berubah. Rantai diuji di produksi: order `_upgrade_dari=173` → menemukan undangan 174; rantai melingkar → berhenti **2 ms**, tidak menggantung. Import: 9 → 8 → publish kesembilan → restart → **9**, diverifikasi dari ekspor live.

- [x] **D2** 🤝 `jam` — **Backup berhenti jadi replika, dan berhenti hanya ada di satu tempat** → **SELESAI & TERUJI 2026-08-07**
  **(a) `--delete` sendirian membuatnya replika, bukan cadangan.** Berkas yang terhapus di Hostinger — tak sengaja, salah hapus massal, atau ransomware — ikut terhapus di "cadangan" pada jalan berikutnya. Diganti `--backup --backup-dir` ke **loteng bertanggal**: cermin tetap akurat, yang terhapus/tertimpa tersimpan.
  **Langsung terbukti pada jalan pertama: 14 berkas masuk loteng** (foto & QRIS undangan uji lama `undangan-29-*`). Dengan aturan lama, keempat belasnya lenyap permanen tanpa jejak.
  **(b) Password DB tidak lagi di baris perintah.** `-p<password>` membuat sandi tampil di `ps aux` selama dump berjalan — dan ini shared hosting, tenant lain bisa melihatnya. Kini lewat `--defaults-extra-file` bermode 600 yang dihapus setelah selesai. **Diuji sebelum dipasang:** dump 190 KB, 56 tabel, gzip valid, nol berkas sementara tersisa, dan peringatan *"Using a password on the command line"* hilang.
  **(c) Titik gagal tunggal yang belum pernah dinamai.** WordPress & uploads **hidup** di Hostinger, jadi VPS lenyap ≠ data pelanggan lenyap. Tapi **sesi WAHA dan volume n8n hanya ada di VPS — dan cadangannya juga di VPS yang sama.** Satu kejadian menghapus keduanya. Kini ketiga artefak itu disalin ke Hostinger memakai SSH key yang sudah ada (nol biaya, nol kredensial baru); gagal menyalin **bukan** kegagalan backup, hanya diperingatkan.
  Retensi salinan luar **14 hari**, bukan 28: arsip sesi WAHA ±285 MB dan hampir identik tiap minggu, sementara sesi basi tetap menuntut scan QR ulang. Yang dijaga cuma "VPS lenyap hari ini". Menghemat ±0,5 GB di akun shared hosting.
  **Dijalankan sungguhan, bukan dry-run:** DB 190 KB · uploads 6,4 MB cermin + loteng · WAHA & n8n terarsip · **salinan luar mendarat di Hostinger** (285 MB + 6,9 MB + 153 KB). Jalur VPS→Hostinger diuji terpisah dengan key `/root/.ssh/id_harih` yang dipakai skrip.
  **Klaim enkripsi dicabut dari Kebijakan Privasi** — enkripsi belum diterapkan (ditunda atas keputusan owner), jadi kalimatnya dibuat jujur: *"penyimpanan berakses terbatas di dua lokasi terpisah"*. Membiarkan janji yang tidak ditepati lebih mahal daripada mencabutnya.

- [ ] **D3** 🤖 `hari` — **Ukuran gambar undangan: nol srcset, nol width/height**
  Grep `srcset|sizes` di seluruh tema: 0 hasil; tidak satu `<img>` di `template-parts/undangan/*.php` punya width/height. Foto dikompresi ke 1600px untuk kolom 480px — paket Favorit dengan 10 foto membawa ~6–9 MB, dan foto sampul ber-`loading="eager"` adalah LCP yang tidak tertolong lazy. Kuota adalah keberatan nyata pembeli Indonesia dan biayanya ditanggung ratusan tamu yang tidak memesan apa pun.
  Digerbang karena solusi termurahnya (turunkan `maxDim` ke 1280) **bukan perubahan bebas risiko**: foto yang sama adalah sumber untuk produk **cetak**, dan resolusi yang dibutuhkan cetak baru terjawab oleh sampel cetak pertama.
  **Selesai bila:** yang gratis dikerjakan sekarang sebagai tumpangan — `aspect-ratio: 1/1` pada `.qris-panel img` (satu-satunya sumber CLS tersisa) dan atribut width/height di keenam template. srcset penuh menunggu WF-02 disentuh untuk hal lain; keputusan `maxDim` menunggu sampel cetak.

- [x] **D4** 🤝 `jam` — **Infrastruktur VPS: batas log, healthcheck sesi, pemeriksaan disk** → **SELESAI & TERUJI 2026-08-08**
  ⚠️ **Dua premis audit meleset, diperiksa langsung:** disk VPS **193 GB dengan pemakaian 12%**, bukan 25 GB yang hampir penuh; dan log container cuma 124 KB & 16 KB. Jadi ini **pencegahan, bukan pemadam kebakaran** — penting untuk tidak diwariskan sebagai keadaan darurat.
  **Batas log dipasang** (`max-size: 10m`, `max-file: 3`) di kedua compose & kedua container. Driver `json-file` tumbuh **tanpa batas** secara bawaan; sekali ada loop galat, satu container bisa memenuhi disk dan menyeret n8n produksi lain milik owner yang berbagi VPS ini.
  **Healthcheck WAHA menguji SESI, bukan port.** Diperiksa dulu ke container sungguhan sebelum menulisnya: `/health` ternyata **hanya melaporkan ruang disk** — sama sekali bukan status engine — jadi ia akan lolos meski Chromium menggantung dan WhatsApp mati. `/ping` juga lolos. Satu-satunya sinyal benar: status sesi di `/api/sessions`. `start_period` 4 menit supaya container sehat tidak tampak unhealthy tiap restart.
  **Terbukti bukan lolos-kosong:** log healthcheck menunjukkan `exit=1` saat sesi masih `STARTING`, lalu `exit=0` begitu `WORKING`.
  **Pemeriksaan disk dititipkan ke `backup-harih.sh`** — satu-satunya yang punya **dua** hal sekaligus: cron di host (tetap jalan meski seluruh Docker mati) dan jalur alert Brevo mandiri dari n8n. Ambang 80%.
  **Batas memori SENGAJA tidak dipasang.** Diukur: WAHA 888 MB, n8n 357 MB, host memakai 2,9 GB dari 15,6 GB. Pada headroom sebesar itu, batas memori hanya menciptakan risiko OOM-kill tanpa manfaat — dan yang mati adalah container yang memegang sesi WhatsApp.
  **Auto-restart sesi juga tidak dipasang** — sudah ada di daftar "jangan dikerjakan": loop restart pada sesi bermasalah menaikkan risiko ban, dan alasan itu dipakai menolak ide lain. Docker tidak me-restart karena unhealthy, jadi healthcheck ini **sinyal, bukan pemicu**.
  **Bonus: drift pin n8n ikut tertutup.** Container berjalan dari tag `latest`, bukan dari `n8nio/n8n:2.29.10` yang tertulis di compose. Setelah di-recreate kini terikat pin — versinya kebetulan sama, jadi tidak ada perubahan perilaku.
  **Dijalankan di produksi, bukan disimulasikan:** WAHA di-recreate → sesi kembali **`WORKING` dalam 12 detik tanpa scan QR ulang** · n8n di-recreate → **9 workflow tetap aktif** · smoke 32/32. Sesi dicadangkan lebih dulu di dua lokasi (lokal + Hostinger, keduanya hari itu).
  ⚠️ **Sisa yang menunggu owner:** satu nomor WhatsApp merangkap sesi WAHA 9 workflow **dan** satu-satunya CTA penjualan cetak. Rencana penjualan yang berhasil menciptakan beban yang justru dipakai menolak ide RSVP-lewat-WA. Pemisahan nomor = keputusan owner, bukan pekerjaan kode.

---

## 🎨 PU — Review UI/UX menyeluruh, 8 Agustus 2026

> **91 temuan ditelaah lewat 7 dimensi paralel; 72 bertahan** setelah tiap temuan diadu dengan verifikator yang tugasnya *mematahkannya* — bukan menyetujuinya. Yang tidak bertahan tidak ditulis di sini. Di 18 temuan verifikator mengoreksi premis, angka, atau nomor baris pelapor; koreksi itu **sudah dijahit ke teks tiap task**, jadi yang tertulis di bawah adalah bentuk yang sudah dibetulkan. Satu temuan gugur total (ukuran alamat gedung tema-02/03 — ternyata penyesuaian keterbacaan yang disengaja).
>
> **Ini review antarmuka, bukan audit ulang keamanan/infrastruktur.** Tidak ada satu pun item di sini yang menghalangi rupiah pertama seperti A1–A8; yang dibela di sini adalah orang yang **sudah membayar** dan **ratusan tamu** yang membuka undangannya.

### Empat hal yang paling merugikan bila diabaikan

**1. Pembeli menekan Kirim sebelum kompresi foto selesai, delapan dari sepuluh fotonya lenyap, dan ia melihat "Data diterima!".**
Tidak ada kunci apa pun antara `change` dan `submit`: `state` hanya punya `{foto, qris, uploading, kotor}` ([isi-data.js:28](../wp-content/themes/harih/undangan/shared/isi-data.js:28)), dan `btnKirim.disabled = true` baru dieksekusi **setelah** payload dirakit. Diverifikasi dengan **menjalankan skripnya**, bukan membacanya: 10 foto dipilih, tunggu 2 detik, submit → `jumlah_foto = "2"`, hanya `foto_0` & `foto_1` di payload, `#panel-sukses` terbuka, form disembunyikan. Kompresi tetap berjalan di latar sampai 10 thumbnail — di balik form yang sudah hilang. → **U1**

**2. Enam ratus adalah batas yang menghapus data, dan tidak pernah diucapkan ke orang yang mengetiknya.**
`array_slice(..., 0, 600)` ([page-tamu.php:83](../wp-content/themes/harih/page-tamu.php:83)) memotong, lalu `sprintf('Tersimpan — %d nama tamu.', count($baris))` menghitung **setelah** pemotongan — jadi 650 nama tersimpan 600 dan dilaporkan *"Tersimpan — 600 nama tamu."* Halaman ini sudah tahu cara mengomunikasikan batas: ia menampilkan *"Menampilkan 300 pertama dari N"* untuk batas **tampilan** yang tidak merusak apa pun. Angka 600 di-grep ke seluruh PHP tema + mu-plugin: **nol string yang pernah sampai ke layar**. → **U5**

**3. Di orientasi lanskap, undangan tidak bisa dibuka sama sekali.**
`.gate { position: fixed; overflow: hidden }` mengklip alih-alih menggulir, dan isi gerbang lebih tinggi daripada layar: pada 640×360 tombol "Buka Undangan" duduk **124–150 px di bawah layar** di ketiga tema, sementara `body` terkunci `overflow: hidden`. Diukur di DOM live, lalu **dihitung ulang dengan aritmetika kotak CSS murni** dan cocok sampai 0,1 px. Tamu yang memegang HP miring — hal biasa saat membuka tautan sambil berjalan — menghadap layar mati. → **U10**

**4. Beranda menjanjikan pembayaran gateway berlisensi tiga kali; tombolnya membuka chat yang menanyakan cara bayar.**
Ketiga blok copy ([page-katalog.php:136](../wp-content/themes/harih/page-katalog.php:136), [:170](../wp-content/themes/harih/page-katalog.php:170), [:302](../wp-content/themes/harih/page-katalog.php:302)) berada di luar percabangan apa pun, sementara `harih_bayar_online_siap()` hanya membungkus `href`. Href live: `…?text=Halo hariH, saya mau pesan Paket Hemat… Boleh info cara pembayarannya?` — pesan pra-isinya sendiri menanyakan hal yang FAQ-nya baru saja jawab. *(Label tombolnya sendiri jujur — "Pesan Hemat lewat WhatsApp" — jadi yang bertentangan adalah copy vs alur, bukan label vs tujuan.)* → **U21**

---

### PU-A · Pembeli yang sudah membayar

> Kegagalan di sini terjadi **setelah uangnya masuk** — jenis kerusakan yang tidak bisa ditambal dengan diskon.

> ## ✅ SELURUH PU-A **SELESAI & LIVE** 2026-08-08 *(v2.22.0)*
>
> Sembilan item tuntas dalam enam commit (`0ae932c` · `9d1a0c7` · `3a5a7e4` · `e8aedb2` · `2967cac` · `6581c08`), di-deploy, dan **diverifikasi di produksi**. `php -l` **7/7 bersih** · smoke test **32/32** · LiteSpeed di-purge.
>
> **Terverifikasi LIVE, bukan dari membaca kode:**
> · `/isi-data/` order **173** (punya undangan) → panel *"sudah kami terima"*, **nol `<form>`, nol input foto** · order tanpa undangan → form utuh dengan `#tolak-foto`, `#foto-hitung`, `#panel-konfirmasi`, `#progress-pct`, `role="progressbar"`, legenda, 6× `autocapitalize="words"`, `min="2026-08-08"` di kedua tanggal (WIB benar), dan ketiga `pattern` — **U4 akhirnya teruji dua arah dengan order sungguhan**
> · `/tamu/` → `"/ 600 nama"`, `#tamu-belum-simpan`, `#tamu-lewat-batas`, `#tamu-peringatan-ekspor`, `bolehEkspor` 3×, `beforeunload`
> · `/proof/` → order 173 **sudah disetujui**, dan yang tampil justru bukti U7(c) bekerja: *"Baru menemukan kesalahan? …masih bisa kami tahan"*. Sebelumnya seluruh blok itu lenyap begitu proof disetujui
> · `/rekap/` → satu RSVP uji dikirim supaya tabelnya benar-benar dirender; `.satuan-tabel-wrap` membungkus `<table>` dengan bersarang benar (`<table>` di dalam, `</div>` menutup sesudah `</table>`), lalu **ucapan uji dihapus permanen — produksi kembali 0 ucapan**
> · aset tersaji dengan isi baru: `state.memproses` 5× · `tolak-foto` · `tampilKonfirmasi` · `btn-sampul` · `.tata-opsi { position: relative }` · `font: 400 16px/1.5` di isi-data.css **dan** undangan.css · **nol** aturan `.field input` aktif tersisa di tema-01
>
> ⚠️ **Dua cabang belum pernah dilewati data nyata** (bukan gagal — kodenya terpasang di server, keadaannya saja yang belum muncul): `fetchpriority="high"` + tautan unduh di `/proof/` hanya berlaku untuk proof ber-**gambar**, sementara order 173 memakai PDF; dan gerbang *"Data cetakmu belum kami kunci"* hanya menyala bila `_proof_url` terisi tapi snapshot kosong — order 173 punya snapshot lengkap. Periksa keduanya pada order cetak sungguhan pertama.
>
> **Cara verifikasinya:** berkas aslinya **dijalankan**, bukan dibaca — `isi-data.js` + `isi-data.css` + font asli dimuat di peramban lewat harness yang digenerasi mekanis dari `page-isi-data.php`, dengan XHR disadap untuk merekam payload. Skenario yang dulu dipakai membuktikan bug-nya dijalankan ulang, dan hasilnya berbalik:
>
> | Yang dulu terjadi | Sekarang |
> |---|---|
> | Kirim saat kompresi → `jumlah_foto=2`, panel "Data diterima!" | **0 XHR**, pesan *"Foto masih disiapkan…"*; setelah selesai → `jumlah_foto=10` |
> | 10 foto, no. 3 & 6 ditolak → 8 thumbnail, pesan **kosong** | 8 thumbnail, penghitung *"8 dari 10"*, **2 catatan bertahan** menyebut namanya |
> | `2025-03-08` → `checkValidity()` **true** | ditolak peramban; layar periksa menampilkan *"Sabtu, 12 Desember 2026"* |
> | `/isi-data/` meluber 57 px (360) & 663 px (1280) | `scrollWidth == clientWidth`, **nol** elemen meluber |
> | RSVP tema-02/03 input 15 px → zoom paksa iOS | seluruh kontrol teks terukur **16 px** |
> | Salin/CSV saat daftar belum disimpan → berkas diam-diam beda dari cetakan | ekspor **dihentikan** dengan sebab; pulih setelah Simpan |
> | 601 nama → baru ketahuan setelah 1 nama hilang | peringatan menyala **sebelum** Simpan ditekan |
>
> Logika penguraian daftar tamu diuji atas **7 kasus tepi** (650 ditolak · tepat 600 lolos · duplikat digabung · TAB & titik koma dipotong · koma **dipertahankan** untuk gelar akademik · baris kosong dibersihkan). Bersarang sintaks alternatif keempat template PHP ditelusuri token per token — kedalaman akhir 0.
>
> *Catatan proses: jaringan ke Hostinger & VPS sempat tertutup serentak berjam-jam sementara kontrol ke host luar normal — pola "Wajib dibaca" no. 8. Lint & deploy tertunda sampai jalurnya pulih, lalu dijalankan penuh. Jangan membaca `HTTP 000` sebagai situs mati.*

- [x] **U1** 🤖 `jam` — **Kirim dikunci selama kompresi, dan foto yang ditolak berhenti lenyap**
  Dua cacat di satu berkas, keduanya menghapus pekerjaan pembeli tanpa suara. **(a)** Tombol Kirim aktif selagi `await kompres()` berjalan ([isi-data.js:152](../wp-content/themes/harih/undangan/shared/isi-data.js:152), [:168](../wp-content/themes/harih/undangan/shared/isi-data.js:168)); handler submit hanya memeriksa `state.uploading` ([:293](../wp-content/themes/harih/undangan/shared/isi-data.js:293)). **(b)** Penolakan foto ditulis ke satu elemen `#pesan-foto` yang **ditimpa di awal iterasi berikutnya** ([:167](../wp-content/themes/harih/undangan/shared/isi-data.js:167)) lalu **dikosongkan** bila foto terakhir lolos ([:184](../wp-content/themes/harih/undangan/shared/isi-data.js:184)). Diuji dengan menjalankan berkasnya di Chrome atas berkas sintetis: 10 foto, no. 3 & 6 di bawah `MIN_SISI_TOLAK` → 8 thumbnail, `#pesan-foto` panjang **0 karakter**.
  **Langkah:** tambahkan `state.memproses` (set di awal handler `change`, dilepas di `finally`) yang men-`disable` tombol dan mengubah labelnya jadi *"Menyiapkan foto…"*, plus jaring kedua di handler submit. Kumpulkan penolakan ke array dan render **setelah** loop ke wilayah terpisah yang bertahan; tambahkan penghitung permanen *"8 dari 10 foto terpilih"* di bawah `.foto-grid`.
  **Selesai bila:** submit selama kompresi ditolak dengan pesan · foto ditolak tetap terbaca setelah seluruh loop selesai · penghitung cocok dengan jumlah thumbnail.

- [x] **U2** 🤖 `menit` — **`/isi-data/` berhenti menggulir horizontal, dan tiga form berhenti memicu zoom iOS**
  **(a)** Satu radio tak terlihat meluberkan halaman **57 px pada 360 px dan 663 px pada 1280 px**: `.tata-opsi input { position: absolute }` ([isi-data.css:256](../wp-content/themes/harih/undangan/shared/isi-data.css:256)) sementara `.tata-opsi` ([:245-255](../wp-content/themes/harih/undangan/shared/isi-data.css:245)) tidak punya `position: relative`, **dan** radio itu tersapu `.field input { width: 100% }` ([:159](../wp-content/themes/harih/undangan/shared/isi-data.css:159)) karena bersarang di dalam `.field`. Containing block-nya jatuh ke viewport. Polanya sudah benar tepat di atasnya — `.tema-card { position: relative }` ([:182](../wp-content/themes/harih/undangan/shared/isi-data.css:182)).
  **(b)** Input 15 px memicu zoom paksa iOS. Aturannya **sudah tertulis di repo** — `/* 16px: di bawah itu iOS memperbesar halaman sendiri */` ([katalog.css:486](../wp-content/themes/harih/katalog.css:486)) — tapi hanya menempel pada tiga field mainan di beranda. RSVP tema-02 & tema-03 terukur 15 px di situs live; tema-01 aman hanya karena menimpanya sendiri ([tema-01/style.css:105](../wp-content/themes/harih/undangan/tema-01/style.css:105)).
  **Langkah:** `position: relative` pada `.tata-opsi`, dan persempit selektor :159 jadi `.field > input:not([type=radio])…`. Naikkan 15 → 16 px di [undangan.css:984](../wp-content/themes/harih/undangan/shared/undangan.css:984) (menutup tema-02 & 03 sekaligus), [isi-data.css:165](../wp-content/themes/harih/undangan/shared/isi-data.css:165), [katalog.css:987](../wp-content/themes/harih/katalog.css:987).
  ⚠️ Yang benar-benar memicu zoom hanya input **teks & textarea** (±25 dari 42 kontrol); `type=date`/`time`/`select` membuka picker dan tidak memperbesar — jangan pakai "seluruh field" sebagai justifikasi.
  **Selesai bila:** `documentElement.scrollWidth === clientWidth` pada 360 & 1280 px · ketiga tema melaporkan 16 px pada field RSVP.

- [x] **U3** 🤖 `jam` — **Tanggal acara tidak lagi menerima tahun yang sudah lewat**
  `<input type="date" name="tanggal_akad">` ([page-isi-data.php:132](../wp-content/themes/harih/page-isi-data.php:132)) dan `tanggal_resepsi` ([:154](../wp-content/themes/harih/page-isi-data.php:154)) tanpa `min`/`max`. Diverifikasi lewat API validasi peramban: diisi `2025-03-08`, `form.checkValidity()` → **true**, jadi `reportValidity()` ([isi-data.js:297](../wp-content/themes/harih/undangan/shared/isi-data.js:297)) meloloskannya. Kedua jalur hilir juga tidak menjaring: `cpt.php:261-263` memetakan keduanya ke `sanitize_text_field` saja. Hasilnya baru terlihat saat **tamu** membaca *"Acara telah berlangsung"* ([countdown.php:33](../wp-content/themes/harih/template-parts/undangan/countdown.php:33)) — dan pembeli digital tidak pernah melihat layar periksa apa pun antara Kirim dan tayang (`/proof/` khusus cetak).
  **Langkah:** `min="<?php echo esc_attr(current_time('Y-m-d')); ?>"` pada kedua input. Lebih kuat: satu panel ringkas sebelum XHR berangkat — *"Sabtu, 14 November 2026 · 19.00 WIB · Graha Kencana"* + tombol "Ya, kirim". Tiga puluh enam field, satu aksi yang tidak bisa dibatalkan, nol layar konfirmasi.
  **Selesai bila:** tanggal lampau ditolak peramban · panel konfirmasi tampil dengan tanggal terformat manusia.

- [x] **U4** 🤖 `jam` — **Membuka ulang link isi-data berhenti menampilkan form kosong di atas data yang sudah masuk**
  Token HMAC tanpa komponen waktu ([woocommerce.php:48-51](../wp-content/mu-plugins/undangan-core/woocommerce.php:48)) → link berlaku selamanya, yang **bagus** untuk pemulihan. Tapi template tidak memeriksa status pengiriman sama sekali: setelah `undangan_token_sah()` lolos, ia langsung merender `<form>` bersih ([page-isi-data.php:75](../wp-content/themes/harih/page-isi-data.php:75)); `#panel-sukses` hanya elemen `hidden` yang dibuka JS pada sesi yang sama. Pembeli yang membuka ulang tautan dari WhatsApp untuk **memeriksa** apa yang ia kirim melihat form kosong — dan sebagian akan mengisinya lagi.
  **Langkah:** sebelum merender form, cek keberadaan undangan untuk order itu (pola `undangan_cari_undangan_order()` sudah ada sejak D1). Bila ada, ganti form dengan panel *"Data pesanan #N sudah kami terima pada {tanggal}"* + tautan ke undangan jadi + tombol "Ada yang perlu diperbaiki?" ke `/kontak/`.
  **Selesai bila:** membuka ulang link setelah kirim menampilkan panel, bukan form.

- [x] **U5** 🤖 `jam` — **Batas yang menghapus data berhenti bisu: 600 nama, nama ganda, 1.200 karakter**
  Tiga pemotongan senyap di dua halaman. **(a)** 600 nama dipotong lalu dihitung setelahnya ([page-tamu.php:83](../wp-content/themes/harih/page-tamu.php:83), [:87](../wp-content/themes/harih/page-tamu.php:87)); textarea tanpa `maxlength`, `.tamu-hitung` tidak pernah menyebut 600. **(b)** Komentar kodenya sendiri sudah mengenali salah-tempel multi-kolom ([:81-82](../wp-content/themes/harih/page-tamu.php:81)) tapi penanganannya cuma `array_filter(array_map('trim', …))` — nol `array_unique` di seluruh berkas, nol pembersihan tab/koma. **(c)** `turut_mengundang` & `rundown` ber-`maxlength="1200"` tanpa penghitung ([page-isi-data.php:125](../wp-content/themes/harih/page-isi-data.php:125), [:152](../wp-content/themes/harih/page-isi-data.php:152)) — padahal pola `#ls-count` sudah ada untuk `love_story` ([:180](../wp-content/themes/harih/page-isi-data.php:180)). Ditambah `'numberposts' => 500` tanpa catatan di [page-rekap.php:41](../wp-content/themes/harih/page-rekap.php:41).
  **Langkah:** tampilkan batas sejak awal (*"120 / 600 nama"*); bila kelebihan, **kembalikan seluruh ketikan ke textarea** memakai pola `$kembali` yang sudah ada ([:75-79](../wp-content/themes/harih/page-tamu.php:75)) alih-alih menyimpan separuh; normalkan pemisah lalu laporkan duplikat yang digabung; duplikasi `#ls-count` untuk kedua textarea.
  **Selesai bila:** menempel 650 nama menghasilkan galat + ketikan utuh kembali, bukan "Tersimpan — 600".

- [x] **U6** 🤖 `jam` — **Daftar tamu berhenti menampilkan dua angka berbeda di satu layar**
  `baris()` membaca `ta.value` — isi textarea **hidup** ([page-tamu.php:198](../wp-content/themes/harih/page-tamu.php:198)) — dan menyuapi penghitung, tombol "Salin semua link", dan CSV. Sementara `.tamu-daftar` dirender PHP dari `get_post_meta('daftar_tamu')`, yaitu data **tersimpan**. Setelah menambah 50 nama tanpa menekan Simpan: penghitung 170, CSV 170 baris, daftar di bawahnya 120 — dan **yang dicetak di amplop tetap 120**. Nol penanda "belum disimpan", nol `beforeunload`, padahal `/isi-data/` sudah punya keduanya ([isi-data.js:24](../wp-content/themes/harih/undangan/shared/isi-data.js:24), [:265](../wp-content/themes/harih/undangan/shared/isi-data.js:265)).
  Terpisah tapi satu berkas: mengubah daftar **setelah proof disetujui** dijawab *"Tersimpan — 125 nama tamu."* tanpa syarat ([:87](../wp-content/themes/harih/page-tamu.php:87)); peringatan bahwa versi itu mungkin bukan yang dicetak hanya masuk `add_order_note()` ([:94-101](../wp-content/themes/harih/page-tamu.php:94)) yang **cuma terlihat admin**. Halaman ini tidak pernah membaca `_proof_disetujui` untuk keperluan tampilan — grep mengembalikan tepat satu kemunculan, di dalam cabang penulisan catatan. *(Tidak mengunci keras adalah keputusan sadar dan benar — yang hilang kalimatnya, bukan kuncinya.)*
  **Selesai bila:** penanda "belum disimpan" muncul saat kotor · ekspor & penghitung sepakat dengan yang dicetak · pesan sukses pasca-persetujuan menyebut konsekuensinya + tautan WA.

- [x] **U7** 🤖 `menit` — **Halaman proof: tombol setujui menunggu snapshot, jalur revisi tidak ikut hilang, berkasnya dimuat lebih dulu**
  **(a)** Syarat tombol adalah `if (!$disetujui && $berkas)` ([page-proof.php:140](../wp-content/themes/harih/page-proof.php:140)) — **tanpa** `$snapshot`, sementara seluruh section pemeriksaan data bersyarat `if ($snapshot)` ([:112](../wp-content/themes/harih/page-proof.php:112)). Bila CS menempel URL proof tapi lupa mencentang "Bekukan snapshot" (kotak terpisah di [proof.php:175](../wp-content/mu-plugins/undangan-core/proof.php:175)), pembeli melihat gambar + paragraf tanggung jawab hukum + tombol setujui — **tanpa tabel data yang ia setujui**. `_proof_hash` lalu mengunci berkas saja, nol data ([proof.php:245](../wp-content/mu-plugins/undangan-core/proof.php:245)). Dipastikan tidak ada pembekuan otomatis di tempat lain: `undangan_bekukan_snapshot()` punya tepat satu pemanggil.
  **(b)** Tautan *"Ada yang perlu diperbaiki?"* berada **di dalam** blok `!$disetujui` ([:148](../wp-content/themes/harih/page-proof.php:148)) — begitu disetujui, satu-satunya jalur pemulihan ikut lenyap, tepat di jendela waktu ia paling dibutuhkan.
  **(c)** Berkas proof — satu-satunya alasan halaman ini ada — dimuat `loading="lazy"` ([:101](../wp-content/themes/harih/page-proof.php:101)) dan tidak punya tautan unduh.
  **Selesai bila:** tombol muncul hanya bila `$snapshot` ada, dengan cabang `else` yang jujur · peringatan admin bila `_proof_url` terisi tapi `_snapshot_hash` kosong · `.proof-batal` hidup di kedua keadaan dengan teks berbeda · gambar pertama `eager` + `fetchpriority="high"` + tautan `download`.

- [x] **U8** 🤖 `jam` — **Empat halaman bertoken berhenti jadi pulau**
  Hanya dua dari empat saling menautkan (`/tamu/` ↔ `/rekap/`); footer `/proof/` dan `/upsell/` cuma Kontak + S&K, jadi **keduanya jalan buntu**. Nol penanda langkah di keempat berkas (grep `langkah`/`step`: nihil), padahal linknya tiba terpencar lewat WhatsApp berhari-hari jarak. Ikut di ronde ini: tabel rekap terpotong di HP — `.rekap-tabel` tanpa pembungkus `overflow-x` ([katalog.css:1014](../wp-content/themes/harih/katalog.css:1014)) sementara `overflow-x: clip` pada `.katalog-body` naik ke viewport dan ditafsirkan `hidden`, jadi kolom Waktu **hilang total, bukan sekadar sempit** — sementara polanya sudah ada di berkas yang sama (`.satuan-tabel-wrap`, [:858](../wp-content/themes/harih/katalog.css:858)); `/upsell/` merender **2** kartu ke grid yang dikunci 4 kolom ([katalog.css:868](../wp-content/themes/harih/katalog.css:868)) sehingga kartunya menyusut sampai ±119 px dan badge 148 px menindih padding; dan blok hitung mundur tidak menyebut konsekuensinya, padahal kalimatnya **sudah ditulis** di cabang kedaluwarsa ([page-upsell.php:134](../wp-content/themes/harih/page-upsell.php:134)).
  **Langkah:** tautan silang bertoken memakai pola `add_query_arg` + `undangan_token_halaman()` yang sudah dipakai [page-tamu.php:162](../wp-content/themes/harih/page-tamu.php:162) · satu partial peta langkah dipakai keempatnya · bungkus tabel rekap · penimpa grid bercakup `.upsell-body` · pindahkan kalimat konsekuensi ke atas.
  **Selesai bila:** tiap halaman menautkan minimal satu saudaranya · tabel rekap bisa digeser di 360 px · `/upsell/` dua kolom.

- [x] **U9** 🤖 `jam` — **Sisa pekerjaan form `/isi-data/`: legenda, pola, sampul, timeout, progres**
  Delapan hal kecil di form yang menuntut ±10 menit dan 36 field. **(a)** Enam field `required` diberi `*` tapi grep `wajib` tidak mengembalikan apa pun — nol legenda; penanda opsional punya tiga konvensi, salah satunya (`.opsional`) **tanpa gaya sama sekali** di CSS. **(b)** Server menolak koordinat & warna dress code lewat regex ketat ([cpt.php:83-95](../wp-content/mu-plugins/undangan-core/cpt.php:83)) yang tidak dicerminkan di klien — Plus Code atau koma-desimal lolos submit lalu **dibuang senyap**. **(c)** Copy menyatakan taruhannya sendiri (*"Foto pertama menjadi sampul dan preview WhatsApp"*, [:185](../wp-content/themes/harih/page-isi-data.php:185)) tanpa cara memilihnya dan tanpa badge penanda. **(d)** `xhr.timeout = 180000` berlaku untuk seluruh round trip termasuk pemrosesan WF-02, dan pesannya menyuruh **mengirim ulang** sesuatu yang mungkin sudah masuk. **(e)** Persentase unggah ditulis ke `role="status"` → dibacakan puluhan sampai ratusan kali, sementara bar-nya sendiri tanpa `role="progressbar"`. **(f)** Empat celah atribut papan ketik: `autocapitalize="words"` pada `nama_pria`/`nama_wanita`/`ortu_*`, `autocapitalize="none" autocorrect="off"` pada `ig_*`. **(g)** `.tema-card` tanpa indikator fokus padahal `.tata-opsi` sudah punya ([isi-data.css:258](../wp-content/themes/harih/undangan/shared/isi-data.css:258)). **(h)** `.row-2` dua kolom di semua lebar ([isi-data.css:173](../wp-content/themes/harih/undangan/shared/isi-data.css:173)) padahal `katalog.css` memakai pola mobile-first yang benar untuk kelas bernama sama.
  ⚠️ Klaim "kolom date menyusut jadi 136 px dan teksnya terpotong" **tidak terbukti** — lebar min-content-nya 161 px dan `1fr` = `minmax(auto,1fr)` menahan di situ. Kerjakan (h) sebagai kerapian, bukan sebagai perbaikan kerusakan.
  **Selesai bila:** legenda tayang · `pattern` menolak koordinat salah di klien · tombol "Jadikan sampul" + badge · pesan timeout membedakan fase unggah dari fase proses · `aria-valuenow` di bar, bukan persen di live region.

---

### PU-B · Undangan — produk yang dilihat ratusan tamu per order

> Tamu tidak membeli apa pun, tapi merekalah bukti sosial produk. Tiap cacat di sini dikalikan 50–600 orang per pesanan.

> ## ✅ SELURUH PU-B **SELESAI & LIVE** 2026-08-08 *(v2.24.2)*
>
> Enam item aksesibilitas & semantik tuntas dalam satu commit (`dfe1d22`), di-deploy, dan **diukur pada elemen ter-render di KETIGA tema** — bukan dari berkas. Parser warnanya mengompositkan alpha dan mengenali sintaks `color(srgb …)`; dua jebakan yang pernah menghasilkan temuan palsu di proyek ini.
>
> | | tema-01 | tema-02 | tema-03 |
> |---|---|---|---|
> | teks diperiksa | 28 | 28 | 28 |
> | **gagal AA** | **0** | **0** | **0** |
> | rasio terendah | 4,62:1 | 4,64:1 | 6,46:1 |
> | batas kontrol form | — | 3,14:1 | 3,18:1 |
>
> Sebelumnya terendahnya **2,60:1**. Ikut terverifikasi live: heading **7 → 17** dengan hierarki h2/h3 benar · `<figcaption>` di dalam `<figure>` · `aria-pressed` terpasang termasuk pada keadaan awal · pil aktif solid `rgb(63,92,79)` dengan tanda centang `"✓ "` (teks 4,97:1, latar 4,80:1 terhadap section) · dot galeri **6×6 → 24×24** (aktif 42×24) tanpa berubah rupa · gambar galeri `tabindex="0" role="button"` · select "Jumlah Tamu" kini di dalam `<label>` · input RSVP 16px di ketiganya. Smoke **32/32**.
>
> **U10–U13 & U20 menyusul di ronde kedua** (`a110420`), diverifikasi live:
> · **U10** lanskap 640×360 **dan** 844×390, tema-01 & tema-03 → isi == layar, **kelebihan 0**, `elementFromPoint` mengembalikan tombolnya sendiri (dulu 124–150 px di bawah layar, mustahil ditekan). Potret tidak tersentuh: seal & Hijriah tetap tampil, gap 14px asli.
> · **U11** MP3 **0 byte sebelum DAN sesudah gerbang dibuka** (dulu 1,8–2,2 MB langsung terunduh; satu pemuatan tanpa MP3 hanya ±796 KB).
> · **U12** bilah 4 tautan, tinggi **44 px**, tidak bertumpuk tombol musik, nol luber horizontal. `sessionStorage` melewati gerbang pada kunjungan kedua dalam sesi yang sama.
> · **U13** `max-height: none`, gulir-dalam **6.497 → 0**, keadaan kosong tampil dan dibedakan dari keadaan gagal; endpoint mengembalikan `{items,total,hal,per}`.
> · **U20** dua acara, masing-masing Google + `.ics`; VEVENT diperiksa isinya — `DTSTART:20260912T010000Z` (= 08.00 WIB, konversi benar), `LOCATION` ter-escape.
>
> ⚠️ **Dua kesalahan saya sendiri, dicatat supaya tidak terulang:** media query lanskap sempat ditaruh **sebelum** aturan yang ditimpanya sehingga kalah pada spesifisitas yang sama — terukur sebagai `gap` yang tidak pernah berubah; dan `transform: scale()` **tidak** mengurangi tinggi layout sedikit pun. Pemangkasan akhirnya dipilih dari pengukuran anak-per-anak, bukan tebakan.
>
> *Catatan: satu jalan smoke sempat melaporkan 2 gagal. Diulang tiga kali berturut → 32/32 dengan kedua pemeriksaan RSVP lulus; itu gangguan jaringan sesaat yang sudah tercatat di "Wajib dibaca" no. 8, bukan regresi.*

- [x] **U10** 🤖 `jam` — **Gerbang bisa dibuka di orientasi lanskap**
  Tiga hal bersamaan menutupnya: `.gate { position: fixed; inset: 0; overflow: hidden }` ([undangan.css:211-219](../wp-content/themes/harih/undangan/shared/undangan.css:211)) mengklip alih-alih menggulir · `justify-content: space-between` + `padding: 7vh` ([:246-258](../wp-content/themes/harih/undangan/shared/undangan.css:246)) · `body` terkunci `overflow: hidden; height: 100dvh` ([:78](../wp-content/themes/harih/undangan/shared/undangan.css:78)). Pada 640×360 isi gerbang 532–557 px sementara layar 360 px; tombol duduk 124–150 px di bawah layar di **ketiga tema**. `elementFromPoint(320,350)` mengembalikan `.gate-bawah`, bukan tombolnya.
  **Langkah:** `.gate-inner { overflow-y: auto }` · `padding: clamp(16px, 7vh, 56px)` · blok `@media (orientation: landscape) and (max-height: 600px)` yang mengecilkan `.seal` 76→48, gap 14→8, dan `.cover-names` clamp atas 46→30 — dengan ketiganya isi turun ke ±340 px.
  **Selesai bila:** tombol terlihat & bisa ditekan pada 640×360, 844×390, dan 915×412 di ketiga tema.

- [x] **U11** 🤖 `jam` — **Menekan gerbang berhenti mengunduh 1,8–2,2 MB MP3 tanpa ditanya**
  `musicBtn.hidden = false` hanya dijalankan **di dalam** `playMusic()` ([undangan.js:106](../wp-content/themes/harih/undangan/shared/undangan.js:106), [:108](../wp-content/themes/harih/undangan/shared/undangan.js:108)), dan `playMusic()` hanya dipanggil dari handler klik gerbang ([:139](../wp-content/themes/harih/undangan/shared/undangan.js:139)). Jadi urutannya: gerbang ditekan → `audio.play()` → unduhan mulai → **barulah** kontrolnya muncul. Ukuran dari `curl -I` ke live: 1.908.919 · 1.781.026 · 2.214.866 byte. Total satu kali buka demo ≈ **796 KB tanpa MP3, ≈2,6 MB dengan**. Kuota adalah keberatan nyata pembeli Indonesia, dan di sini biayanya ditanggung ratusan orang yang tidak memesan apa pun.
  **Langkah:** balik urutannya — tampilkan `#music-toggle` (kelas `paused`) saat gerbang dibuka **tanpa** memanggil `playMusic()`; panggil hanya dari handler tombol musik. Bila autoplay dianggap wajib bagi kesan produk, potong berkasnya: 60–70 detik @96 kbps sudah cukup karena `loop` mengulang dari cache — memangkas ±1,2 MB per tamu tanpa mengubah yang terdengar.
  **Selesai bila:** `performance.getEntriesByType('resource')` menunjukkan nol byte MP3 sampai tombol musik ditekan.

- [x] **U12** 🤖 `jam` — **Alamat & jam acara berhenti berada empat layar dari puncak**
  `#acara` adalah section **ke-6 dari 12** (identik di ketiga tema, diverifikasi dari HTML terkirim): 2.977 px pada 360×740 = **4,0 layar**; dokumen penuh 9.125 px = 12,3 layar. Seluruh dokumen demo hanya memuat 9 tag `<a>`, dan satu-satunya ber-`href="#"` adalah placeholder RSVP-WA yang bawaannya `hidden` — jadi **nol tautan lompat**. Ditambah: nol `sessionStorage` di `undangan.js`, sehingga gerbang dipasang ulang **tiap kunjungan** — tamu yang membuka tautan lagi di dalam mobil untuk mengecek alamat harus menekan gerbang lalu mengulang empat layar gulir.
  **Langkah:** bilah aksi tetap di bawah layar setelah gerbang terbuka — Acara · Lokasi · RSVP · Amplop — memakai `#acara`/`#rsvp`/`#amplop` yang **sudah ada di markup**, jadi nol perubahan urutan section. Berpasangan dengan tombol musik yang sudah fixed. Terpisah: `sessionStorage` penanda gerbang, lewati pada kunjungan berikutnya di sesi yang sama.
  **Selesai bila:** alamat terjangkau satu ketukan dari mana pun · kunjungan kedua tidak menampilkan gerbang.

- [x] **U13** 🤖 `jam` — **Buku tamu: batas 50, jebakan gulir, keadaan kosong, dan kiriman yang merusak tata letak**
  **(a)** `'numberposts' => 50` tanpa parameter halaman ([rest.php:124](../wp-content/mu-plugins/undangan-core/rest.php:124)) + pemanggilan sekali tanpa tombol muat-lagi ([undangan.js:602-612](../wp-content/themes/harih/undangan/shared/undangan.js:602)). Karena `get_posts()` bawaannya `date DESC`, yang tampil adalah **50 terbaru** — pengirim awal benar-benar hilang. `/rekap/` memakai 500, jadi mempelai aman dan tamu tidak. **(b)** `.ucapan-list` dikurung `max-height: 460px; overflow-y: auto` ([undangan.css:1020-1032](../wp-content/themes/harih/undangan/shared/undangan.css:1020)) tanpa `overscroll-behavior` — jebakan gulir mulai menggigit pada **≥4 ucapan** (4×129 px > 460), tepat sebelum section penutup yang memuat "Bagikan via WhatsApp". **(c)** Nol cabang untuk `items.length === 0`, dan ketiga demo memang menampilkannya kosong melompong — `curl` ke endpoint mengembalikan `[]` di ketiganya. **(d)** Nol `overflow-wrap` pada `.ucapan-nama`/`.ucapan-pesan`, jadi satu nama 65 karakter tanpa spasi merusak tata letak. *(Ini soal moderasi & pembungkus kata, **bukan** celah keamanan — `rest.php:70-71` sudah memotong 100/1.500 karakter lewat `mb_substr` + sanitasi.)*
  **Langkah:** tampilkan 5 lalu tombol *"Lihat semua ucapan (N)"* dalam alir halaman (buang kotak gulir); parameter `?hal=` + `total` di REST; cabang kosong yang tenang + cabang gagal-jaringan yang berbeda; `overflow-wrap: anywhere`. Sekalian isi ketiga demo dengan 6–8 ucapan contoh — calon pembeli sedang menilai produk dari halaman itu.
  **Selesai bila:** ucapan ke-51 bisa dilihat tamu · gulir halaman tidak terperangkap · demo tidak lagi kosong.

- [x] **U14** 🤖 `jam` — **`--c-gold` berhenti dipakai sebagai warna teks di mode terang**
  Token yang mewarnai **sepuluh judul section**, jam tiap butir susunan acara, tanggal Hijriah, sumber ayat, label QRIS, dan **nama bank tempat tamu transfer** — semuanya 9–12 px. Dihitung dua kali dengan metode berbeda (computed style live, lalu komposit alpha eksplisit dari token di berkas), hasilnya cocok: tema-01 `#b4923f` → **2,59:1** di section ber-tint, 2,76:1 tanpa tint, 2,95:1 di atas putih; tema-02 `#be7a4a` → 3,03–3,46:1. Ambangnya 4,5:1. Bahkan angka **Detik** hitung mundur (34–40 px) gagal ambang teks besar di 2,59:1. Di katalog: badge "PALING LARIS" `#fff` di atas `--c-gold` = **2,95:1** — satu-satunya penanda paket unggulan. tema-03 lulus (6,68–7,31:1), jadi ini murni cacat mode terang.
  **Bukti bahwa ini bisa diperbaiki tanpa menyentuh desain:** mode gelap katalog **sudah menyelesaikan masalah yang sama persis** lewat `--d-gold` + `--d-on-gold` → 8,50:1 dan 8,57:1.
  **Langkah:** jangan ubah `--c-gold` (ia warna ornamen, garis, titik lini masa — di sana kontras tinggi tidak dibutuhkan). Tambahkan `--c-gold-teks` khusus peran `color:`: tema-01 ±`#7a6220`, tema-02 ±`#8a5028`; tema-03 tetap. Untuk `.paket-badge`, ubah `--c-on-gold` jadi tinta gelap seperti pola `--d-on-gold` yang sudah terbukti.
  ⚠️ Ikut gagal SC 1.4.11 (3:1 non-teks): border `.hadir-btn.aktif` = 2,59:1 di tema-01.
  **Selesai bila:** ketujuh selektor teks ≥4,5:1 di ketiga tema · desain tidak berubah selain gelapnya emas pada teks.

- [x] **U15** 🤖 `jam` — **Keadaan terpilih diekspos, dan kotak isian punya batas yang terlihat**
  `aria-pressed`, `aria-current`, `aria-selected` muncul **nol kali di seluruh tema**. Keadaan terpilih selalu hanya kelas CSS ([undangan.js:493](../wp-content/themes/harih/undangan/shared/undangan.js:493)) — untuk pil kehadiran, pil sesi, filter paket beranda, dan tombol mode gelap. Karena default-nya *"hadir"*, tamu berhalangan yang memakai pembaca layar bisa mengirim konfirmasi **HADIR tanpa sadar**, dan mempelai menghitung kursi & katering dari data yang salah. Sisi visualnya juga gagal: latar pil aktif dikomposit dengan benar = beda **1,13:1** dari yang tak aktif (syarat 3:1), teksnya 3,45:1 pada 10 px, bordernya 2,59:1.
  Terpisah tapi sekeluarga: kotak isian RSVP tidak punya batas yang terlihat — isian vs latar section **1,14:1**, border vs latar section **1,15:1** (tema-03: 1,03 dan 1,28).
  **Langkah:** `role="radiogroup"`/`aria-checked` untuk grup pilihan tunggal, atau minimal `aria-pressed` berbarengan dengan `classList.toggle`. Keadaan terpilih solid (`--c-accent` + `--c-accent-ink`, sudah terbukti 6,87:1 pada `.btn-ghost:hover`) plus penanda non-warna. Token `--c-line-input` terpisah dari `--c-line` dekoratif, target ≥3:1.
  **Selesai bila:** pembaca layar mengumumkan pilihan aktif · beda visual aktif/tak-aktif ≥3:1 · batas field ≥3:1 di ketiga tema.

- [x] **U16** 🤖 `menit` — **Semantik undangan: sepuluh judul jadi heading, figcaption masuk figure, select RSVP dapat label**
  **(a)** Semua judul section memakai `<p class="section-title">` — kecuali [rsvp.php:7](../wp-content/themes/harih/template-parts/undangan/rsvp.php:7) yang justru `<h2>`, membuktikan kelas itu memang dimaksudkan sebagai judul. HTML live hanya punya **tujuh heading** untuk dua belas section. *(Sepuluh judul, bukan sembilan — `acara.php` punya tiga di baris berbeda.)* **(b)** `<figcaption>` ditulis **di luar** `</figure>` ([amplop.php:85](../wp-content/themes/harih/template-parts/undangan/amplop.php:85)) sehingga keterkaitannya dengan gambar QRIS putus. **(c)** "Jumlah Tamu" dibungkus `<div>` dengan `<span>` tanpa `for` ([rsvp.php:31-36](../wp-content/themes/harih/template-parts/undangan/rsvp.php:31)) — nama aksesibelnya kosong, sementara dua field tetangganya sudah benar. **(d)** Teks bantuan panjang berada **di dalam** `<label>` di `/isi-data/` ([page-isi-data.php:163-165](../wp-content/themes/harih/page-isi-data.php:163), [:229-231](../wp-content/themes/harih/page-isi-data.php:229)) sehingga ikut jadi nama aksesibel field.
  Nol perubahan visual di keempatnya — `.section-title` sudah menetapkan `font`/`margin` sendiri, `.qris-cap` hanya mengatur margin/font/color.
  **Selesai bila:** dua belas section punya heading · `figcaption` anak `figure` · select punya nama aksesibel · petunjuk pindah ke `aria-describedby`.

- [x] **U17** 🤖 `menit` — **Tiga aksi tamu bisa dijalankan dengan keyboard**
  **(a)** Lightbox galeri hanya dipicu `img.addEventListener('click')` ([undangan.js:334](../wp-content/themes/harih/undangan/shared/undangan.js:334)) — tidak bisa dibuka sama sekali tanpa mouse, dan saat terbuka tidak memindahkan fokus maupun menonaktifkan latar. **(b)** Facade peta & video memakai `{once: true}` pada listener `keydown` ([undangan.js:478-480](../wp-content/themes/harih/undangan/shared/undangan.js:478), [:413-415](../wp-content/themes/harih/undangan/shared/undangan.js:413)) — opsi `once` mencopot listener setiap kali **dipanggil**, bukan setiap kali berhasil, jadi satu ArrowDown untuk menggulir menghabiskan satu-satunya kesempatan dan **Enter mati permanen**. Diverifikasi di DOM live: setelah ArrowDown lalu Enter, iframe tidak pernah termuat; klik mouse tetap bekerja. **(c)** Tombol "Salin Nomor" di amplop yang masih tertutup ([amplop.php:40](../wp-content/themes/harih/template-parts/undangan/amplop.php:40)) tetap menerima fokus — `.amplop-wrap` memakai `grid-template-rows: 0fr` tanpa `hidden`/`inert`.
  **Langkah:** tombol/keyboard handler untuk lightbox + `inert` pada latar · buang `{once:true}` dari kedua `keydown` dan jaga idempotensi di `muat()`, atau lebih baik ganti `<div role="button">` jadi `<button>` sungguhan · pasang/lepas `inert` seiring `aria-expanded`.
  **Selesai bila:** ketiganya bisa dioperasikan Tab + Enter tanpa mouse.

- [x] **U18** 🤖 `menit` — **RSVP berhenti menanyakan yang sudah diketahui dan berhenti menyimpan jawaban orang sebelumnya**
  **(a)** Nama tamu sudah diketahui dari `?to=` tapi hanya ditulis ke `.guest-name` di dalam gerbang ([undangan.js:19-21](../wp-content/themes/harih/undangan/shared/undangan.js:19)) — setelah gerbang naik ia hilang, dan `#rsvp-nama` dibiarkan kosong. Tamu mengetik ulang, mempelai menerima ejaan yang tidak cocok dengan daftar undangannya — padahal sisi pembeli sudah membangun tautan personal di **tiga** titik. Diperberat `autocomplete="off"` pada form ([rsvp.php:9](../wp-content/themes/harih/template-parts/undangan/rsvp.php:9)) yang ikut mematikan isian otomatis peramban. **(b)** Setelah kirim, `form.reset()` ([undangan.js:682](../wp-content/themes/harih/undangan/shared/undangan.js:682)) membersihkan **sebagian**: nama, pesan, dan jumlah kembali, tapi pil kehadiran & sesi tetap menyala pilihan orang sebelumnya — karena `undangan.js:492` menulis ke `.value` input `hidden`, yang ikut mengubah atribut `value` sehingga reset justru mengembalikannya ke pilihan itu. Diuji langsung di demo live; dugaan awal justru terbalik. **(c)** "Hadir Pada" & "Jumlah Tamu" dirender tanpa syarat, termasuk kepada yang menjawab **Berhalangan** dan pada undangan **resepsi-saja** — di sana RSVP tetap menawarkan Akad/Resepsi/Keduanya dengan "Keduanya" terpilih.
  **Selesai bila:** field nama terisi dari `?to=` dan tetap bisa disunting · reset mengembalikan seluruh kontrol ke keadaan awal · blok "Hadir Pada" hanya muncul bila kedua acara ada, dan menghilang saat Berhalangan dipilih.

- [x] **U19** 🤖 `menit` — **Sasaran sentuh, gerak, dan safe area**
  **(a)** `.galeri-dots button` 6×6 px dengan jarak antar-pusat 14 px ([undangan.css:777-787](../wp-content/themes/harih/undangan/shared/undangan.css:777)) — **gagal WCAG 2.2 SC 2.5.8 lewat kedua jalur** (ukuran maupun spacing); `.mempelai-ig` 74×14 px juga gagal. *(`.btn-copy` 115×30 px **lulus** ambang 24×24 — hanya meleset dari rekomendasi platform; jangan dijual sebagai pelanggaran.)* **(b)** Ken Burns pada foto galeri ([:774](../wp-content/themes/harih/undangan/shared/undangan.css:774)) adalah satu-satunya animasi yang lolos dari **dua** blok `prefers-reduced-motion` ([:814-816](../wp-content/themes/harih/undangan/shared/undangan.css:814) dan [:1168-1183](../wp-content/themes/harih/undangan/shared/undangan.css:1168)) yang mendaftar 17 selektor lain satu per satu. **(c)** `viewport-fit=cover` dipasang di **sebelas** template, tapi `env(safe-area-inset-*)` hanya dipakai dua aturan — keduanya elemen khusus demo. Tombol musik yang dilihat tamu memakai `bottom: 18px` polos di atas home indicator iPhone. *(Inkonsistensi yang layak ditutup, bukan kehilangan ketukan yang terbukti — jangan dinaikkan levelnya.)*
  **Langkah:** `padding: 9px; background-clip: content-box` pada dot (visual tetap 6 px, target jadi 24×24) · tambahkan `.galeri-slide img` ke daftar reduced-motion, atau ganti daftar eksplisit dengan satu aturan menyapu agar tidak terulang · `calc(18px + env(safe-area-inset-bottom, 0px))` pada `.music-toggle` · `overscroll-behavior: contain` pada kedua slider horizontal.
  **Selesai bila:** dot ≥24×24 tanpa berubah rupa · nol animasi lolos saat reduced-motion menyala.

- [x] **U20** 🤖 `jam` — **"Catat Tanggalnya" melayani kedua acara dan kedua platform**
  Satu `<a>` ke `calendar.google.com` saja ([countdown.php:36-38](../wp-content/themes/harih/template-parts/undangan/countdown.php:36)); pencarian `.ics`/`text/calendar`/`BEGIN:VCALENDAR` di seluruh `wp-content` **nol hasil** — padahal komentar [undangan.js:234](../wp-content/themes/harih/undangan/shared/undangan.js:234) sudah menyebut baris ini sebagai *"tombol Google + .ics"*, jadi niatnya ada dan separuhnya tidak pernah dibuat. Tombolnya juga hanya membawa target countdown, yaitu resepsi — tamu yang diundang akad tidak terlayani. *(Klaim "pemakai iPhone dilempar ke layar login Google" tidak diverifikasi; yang pasti cukup: nol berkas .ics = nol jalur ke Kalender bawaan iOS/Android.)*
  **Langkah:** pindahkan tombol kalender ke tiap kartu di `acara.php` sehingga akad & resepsi membawa jam dan venue-nya sendiri; bangun `.ics` sebagai `data:text/calendar;base64,` di PHP — nol endpoint baru, nol permintaan jaringan — dan tawarkan dua tautan berdampingan.
  **Selesai bila:** kedua acara punya tombolnya masing-masing · tautan .ics terbuka di Kalender bawaan iOS.

---

### PU-C · Halaman jualan

> ## ◐ PU-C — **U21 · U23 · U24 · U25 · U26 SELESAI & LIVE** 2026-08-08 *(v2.25.3)* · **U22 menunggu owner**
>
> Commit `ed9bb10`. Smoke 32/32, keempat halaman jualan 200, nol sisa klaim lama.
>
> 🔴 **U24 ternyata lebih buruk dari yang tercatat.** Dihitung ulang dari tabel satuan halaman itu sendiri, **ketiga paket lebih mahal** daripada beli satuan — bukan hanya angka "Rp 3.600.000 / hemat Rp 700.000" yang tak bersumber:
>
> | paket | dibeli satuan | harga paket | selisih |
> |---|---|---|---|
> | Hormat | Rp 1.049.000 | Rp 1.190.000 | **paket +141.000** |
> | Resepsi | Rp 2.699.000 | Rp 2.900.000 | **paket +201.000** |
> | Grand | Rp 4.298.000 | Rp 5.900.000 | **paket +1.602.000** |
>
> *(satuan = tabel `/satuan/` × isi paket + digital termahal Rp 299.000. "300 kupon souvenir" di Grand tidak ada di tabel satuan, jadi tidak bisa dihargai dari halaman itu.)*
>
> Klaim payung *"paket selalu lebih hemat per unit"* karena itu ikut dicabut, diganti pembeda yang bisa dibuktikan (satu proof · satu pengiriman · satu desain · gratis ongkir yang bernilai Rp 150.000 di struktur biaya · garansi). **Yang belum diputuskan: apakah harga satuan yang terlalu murah, atau harga paket yang terlalu mahal — itu keputusan owner, bukan keputusan kode.** Ditambahkan ke daftar keputusan menunggu owner.
>
> ⚠️ **Perbaikan breakpoint U25 gagal DUA KALI sebelum benar, dan keduanya terukur:** `auto-fit minmax(220px)` tetap memberi 3 kolom di 744px (4×220 + gap butuh ±928px), lalu aturannya sempat ditaruh di `@media (min-width: 760px)` — yang di 744px **tidak aktif sama sekali**, sehingga 3 kolom milik `.paket-grid` tetap menang. Yang benar: di media 720px tepat setelah `.paket-grid`, karena spesifisitasnya sama dan urutan sumber yang menentukan.
>
> **U22 — SELESAI sebagai infrastruktur, menunggu berkasnya.** `harih_foto_produk()` membaca `aset/produk/{slug}.{webp,jpg,png}`: begitu berkasnya ada ia langsung tampil, tanpa menyentuh kode. **Lima slot live** — `amplop-nama`, `undangan-terbuka`, `paket-hormat`, `paket-resepsi`, `paket-grand` — sementara ini menampilkan placeholder yang menyebut apa yang akan ada di situ. Hasil AI otomatis berlabel **"ilustrasi"**; labelnya jangan dicabut sampai fotonya sungguhan. Prompt siap pakai + daftar foto nyata (bengkel, mesin, tangan melipat) ada di [`prompt-gambar.md`](./prompt-gambar.md).
>
> **Sisa PU-C: nol.**


- [x] **U21** 🤖 `menit` — **Copy pembayaran berhenti menjanjikan yang belum menyala**
  Ketiga blok — hero-trust, langkah 1, dan FAQ ([page-katalog.php:136](../wp-content/themes/harih/page-katalog.php:136), [:170](../wp-content/themes/harih/page-katalog.php:170), [:302](../wp-content/themes/harih/page-katalog.php:302)) — berada **di luar percabangan apa pun**, sementara `harih_bayar_online_siap()` ([functions.php:598-600](../wp-content/themes/harih/functions.php:598)) hanya membungkus `href`. Ini kelas cacat yang sama persis dengan B5/B6/B7: halaman tayang menjanjikan hal yang keputusan atau kode tidak dukung.
  ⚠️ Jangan berlebihan: *"bukti pembayaran otomatis via email"* **tetap benar** — runbook §7c membuat order WooCommerce lalu set `processing`, dan WooCommerce memang mengirim email order otomatis. Yang salah spesifik frasa *"diproses payment gateway berlisensi resmi"*.
  **Langkah:** kondisikan ketiga blok pada `harih_bayar_online_siap()` sama seperti tombolnya. Versi sandbox yang jujur dan tetap menjual: hero → *"Mulai Rp 99rb, sekali bayar · konfirmasi & pembayaran lewat CS WhatsApp"*; langkah 1 → *"Chat WhatsApp — CS kirim rincian & rekening resmi hariH"*; FAQ → sebut transfer bank/QRIS ke rekening atas nama usaha. Cabang lama hidup lagi otomatis begitu Duitku production dipasang, tanpa deploy.
  **Selesai bila:** nol kalimat "gateway" tayang selama `duitku_environment !== 'production'` · kedua cabang diuji dengan membalik nilai opsinya.

- [x] **U22** 🤝 `jam` — **Produk cetak Rp 1,19–5,9 juta berhenti dijual tanpa satu pun fotonya**
  `document.images.length` = **0** di `/harga/` **dan** `/satuan/`; `<picture>` = 0; enumerasi computed `backgroundImage` seluruh elemen `/harga/` hanya mengembalikan satu gradien. Jadi tidak ada foto yang "terlewat lewat CSS". Beranda punya enam gambar tapi **tak satu pun memperlihatkan cetakan** — band cetak memakai foto cincin & sepatu. Sementara copy-nya menuntut pembeli **membayangkan**: *"dicetak langsung pada amplopnya, bukan stiker yang ditempel"*, *"A4 dilipat jadi A5"*, *"kertas lebih tebal · finishing khusus"*. Sampel `TEST-173` sudah dicetak sungguhan — bahannya ada.
  **Langkah:** 3–5 foto di `page-harga-hybrid.php` — close-up amplop bernama di hero, undangan terbuka dengan QR di halaman dalam, hamparan isi paket Resepsi (satu foto ini sekaligus menjawab "apa saja yang saya dapat"), satu foto per kartu paket.
  👤 **Butuh tangan owner:** memotret sampel yang sudah ada. Selama itu belum ada, mockup jujur berlabel "ilustrasi" jauh lebih baik daripada nol.
  **Selesai bila:** `/harga/` memuat foto produk cetak sungguhan · tiap kartu paket punya visual.

- [x] **U23** 🤝 `jam` — **Sinyal kepercayaan: nomor yang bisa dibaca, jam layanan, identitas usaha**
  Hitungan pada **teks tampak** (bukan HTML mentah) di beranda, `/harga/`, `/satuan/`: "testimoni" 0 · "ulasan" 0 · "rating" 0 · "portofolio" 0. Nomor CS muncul 3–9 kali di HTML mentah tapi **nol kali di teks tampak** — hanya di dalam `href="wa.me/…"`, jadi tidak bisa dibaca, disalin, atau dicek pengunjung. "Jam layanan"/"WIB"/"Senin"/"09.00"/`hi@harih.id` = 0 di ketiganya, padahal `/kontak/` yang tayang menyebut *"Senin–Sabtu, 09.00–18.00 WIB"*. Blok Identitas Usaha hanya ada di `/kontak/`, dan `nav.php` tidak menautkannya sama sekali — hanya footer. Satu-satunya bukti sosial adalah badge "Paling Laris" & "Paling Populer" pada produk yang **belum pernah terjual**.
  ⚠️ Rumusan yang tepat: informasinya **satu klik jauhnya** lewat footer, bukan "tidak ada di mana pun". Yang hilang adalah kehadirannya **di dekat CTA**, tepat saat orang memutuskan.
  **Langkah:** nomor WhatsApp sebagai **teks** di dekat tiap kelompok CTA + jam layanan · "Kontak" masuk `nav.php` · ringkasan Identitas Usaha ke `kaki.php`. Berikutnya: 3 kutipan pemesan pertama dengan izin, atau foto paket sungguhan sebelum dikirim. Ganti "Paling Populer" dengan penanda yang jujur untuk produk baru — mis. *"Rekomendasi kami untuk resepsi gedung"*.
  **Selesai bila:** nomor & jam layanan terbaca tanpa mengeklik apa pun di ketiga halaman jualan.

- [x] **U24** 🤖 `menit` — **Klaim "Anda hemat Rp 700.000" berhenti dibantah tabel di halaman yang sama**
  Isi Paket Resepsi dihargai dengan **tabel satuan halaman itu sendiri**: 100×15.000 + 200×2.000 + 100×3.500 + 100×1.500 = **Rp 2.400.000**, plus digital termahal Rp 299.000 = **Rp 2.699.000**. Klaim yang tayang: *"Dibeli satuan: Rp 3.600.000 — Anda hemat Rp 700.000"* ([page-harga-hybrid.php:60](../wp-content/themes/harih/page-harga-hybrid.php:60)). Selisih **Rp 901.000 tidak berasal dari harga mana pun yang situs terbitkan**, dan totalnya justru Rp 201.000 **lebih murah** daripada harga paketnya. Klaim payung *"Paket selalu lebih hemat per unit"* juga gugur untuk Hormat (satuan Rp 1.049.000 vs paket Rp 1.190.000). Diverifikasi silang dengan harga WooCommerce hidup — keduanya cocok persis. Klaim yang sama diulang sebagai h2 di `/satuan/`.
  *(Peringan yang layak disebut di perbaikan, bukan pembatalan: minimum Rp 1.000.000/transaksi membuat keranjang setara-Hormat memang tidak bisa di-checkout.)*
  **Langkah:** ganti angka gelondongan dengan rincian yang bisa dijumlahkan pembaca (baris per item × harga satuan) dan cantumkan nilai "undangan digital custom" sebagai satu baris berharga. Bila Rp 3.600.000 memang benar, komponen penutup selisihnya **harus muncul** di tabel satuan. Bila tidak, turunkan ke angka yang bisa dibuktikan — atau ganti sudutnya: *"satu proof, satu pengiriman, satu desain"*.
  **Selesai bila:** angka di halaman bisa dijumlahkan sendiri oleh pembaca dan hasilnya cocok.

- [x] **U25** 🤖 `jam` — **Layar pertama: nav 174 px, nol CTA menetap, harga di kedalaman 2.899 px**
  `.nav` tingginya **88 px di 1280 px tapi 174 px di 360 px** — tiga baris, karena `flex-wrap` tanpa satu pun media query maupun hamburger ([katalog.css:291-305](../wp-content/themes/harih/katalog.css:291)). Itu **27% layar pertama** di ponsel entry-level, dan mendorong foto produk serta harga ke bawah lipatan. Terpisah: pencacahan seluruh elemen `position: fixed|sticky` di beranda mengembalikan **array kosong** — `.nav` sendiri `static` sehingga hilang setelah 174 px pertama. `#paket` baru mulai di 2.899 px dari dokumen 6.288 px. Ikut di ronde ini: jurang breakpoint **720–759 px** (persis iPad mini portrait 744 px) — `.paket-grid` sudah 3 kolom di ≥720 px tapi `.paket-grid-hybrid` baru 4 kolom di ≥760 px ([katalog.css:814-819](../wp-content/themes/harih/katalog.css:814), [:866-869](../wp-content/themes/harih/katalog.css:866)), sehingga empat kartu pecah jadi 3 + 1 yatim yang terpisah ±1.000 px ke bawah.
  **Langkah:** ciutkan `.nav-tautan` di layar sempit (tautan Tema/Paket/Cetak toh cuma anchor ke bawah halaman) atau jadikan tombol menu · bilah CTA menempel di bawah layar berisi "mulai Rp …" + satu tombol WhatsApp, muncul setelah hero terlewati · samakan ambang kedua grid, atau ganti dengan `repeat(auto-fit, minmax(220px, 1fr))` sehingga jumlah kolom dihitung sendiri.
  **Selesai bila:** nav satu baris di 360 px · harga terbaca tanpa menggulir tiga layar · tidak ada kartu yatim di 720–759 px.

- [x] **U26** 🤖 `menit` — **Enam koreksi copy & tautan yang masing-masing berbiaya satu baris**
  **(a)** `<title>` `/harga/` masih *"Undangan Digital + Kartu Fisik Ber-QR"* ([functions.php:681](../wp-content/themes/harih/functions.php:681)) — produk yang FAQ halaman itu sendiri argumentasikan **bukan** tawaran utamanya; `og:title` sudah benar, hanya elemen title yang tertinggal. Periksa juga `/satuan/` ([:661](../wp-content/themes/harih/functions.php:661)). **(b)** Sedikitnya **empat** format rupiah tayang bersamaan: `99 rb` (kartu, **tanpa "Rp"**) · `Rp 99rb` (hero & marquee) · `Rp 99 ribu` (CTA penutup) · `Rp 1.190.000`. Bukan efek CSS yang terlewat — `katalog.css:556-558` tidak punya `::before { content: 'Rp' }`. **(c)** Kartu terakhir carousel tema menaut `href="#tema"` — ke section tempat tombol itu **sendiri berada** ([page-katalog.php:197-200](../wp-content/themes/harih/page-katalog.php:197)). **(d)** Label "Tambah 50 pcs" di `/satuan/` tidak dikondisikan padahal href-nya sudah ([page-satuan.php:80](../wp-content/themes/harih/page-satuan.php:80)); begitu pula kalimat aturan keranjang yang tidak pernah dilalui pembeli. **(e)** Masa aktif H+7 dicabut dari kartu Hemat (C3b) tapi masih **bocor** lewat pesan filter tamu ([page-katalog.php:358](../wp-content/themes/harih/page-katalog.php:358)) — sementara `masa-aktif.php:66-102` tetap menegakkannya dan S&K §4 tetap menyebutnya. **(f)** Jam layanan CS tidak pernah muncul di dekat CTA (lihat U23).
  **Selesai bila:** satu format rupiah di seluruh situs · nol tautan yang mendarat di tempatnya sendiri · label & href sama-sama dikondisikan.

---

### PU-D · Aset & sistem desain

> ## ◐ PU-D — **U27 · U28 · U29 SELESAI & LIVE** 2026-08-08 *(v2.28.1)* · **U30 sebagian** · **U31 digerbang**
>
> Commit `f8b9314` · `af74481` · `92d7428`. Smoke 32/32.
>
> **U27 — empat aset yatim, bukan tiga.** Emoji WordPress ikut ketemu. Terukur: HTML undangan **44.180 → 27.577 byte (−38%)**, beranda **41.622 → 23.879 (−43%)**, dan satu permintaan pemblokir render hilang dari `<head>` tiap halaman.
> ⚠️ **Mengejar jalur enqueue-nya terbukti tebak-tebakan** — tiga percobaan gagal berturut (`wp_enqueue_scripts`, `enqueue_block_assets`, `wp_print_styles`, semuanya meleset pada `wc-blocks-style`). Yang bekerja: menyaring di `style_loader_tag`/`script_loader_tag`, titik semua jalur bermuara. Halaman diuji tetap berfungsi penuh sesudahnya, bukan hanya diperiksa ukurannya.
>
> **U28** — versi aset kini per-berkas (`filemtime`); terverifikasi keempat aset punya versi berbeda sesuai kapan masing-masing benar-benar berubah. Font woff2 `max-age=31536000, immutable` — ini **setelan server**, `.htaccess` dicadangkan lebih dulu dan blok ditambahkan (bukan menimpa); langkah + verifikasinya dicatat di `scripts/setup-hostinger.sh`. `/isi-data/` akhirnya dapat preload font.
>
> 🔴 **U29 — divergensi yang diprediksi SUDAH TERJADI**, diukur sendiri: `--warn-bg` `#fdf3e4` vs `#fdf3e0` · `--warn-line` · `--warn-ink` · `--error`. Kuning peringatan & merah galat berbeda antara `/isi-data/` dan `/tamu/`, dua halaman yang dilihat pembeli yang sama dalam satu jam yang sama. Delapan belas token di-rename, nilai `katalog.css` jadi kanon (8 halaman vs 1).
>
> **U30 — bagian yang berdampak dikerjakan, remap skala tipografi TIDAK.** Yang dikerjakan: 12 literal `rgba()` di luar `:root` ternyata **mengunci warna mode terang** sehingga elemennya tidak ikut berganti di mode gelap — diverifikasi di mode gelap sungguhan, nol sisa. Plus token hantu `--c-ink-3` dan peran dua token tinta yang kini tertulis.
> **U30 — SELESAI 2026-08-08.** 40 nilai `font-size` berbeda di `katalog.css`, dalam px **dan** rem untuk ukuran yang sama: 12,48 di sebelah 12,5 · 14,4/14,5/14,72 · 16,8/16,96/17. Bukan tangga, melainkan derau. **105 deklarasi → 18 token, nol literal px/rem tersisa.**
> Skalanya sengaja **18 langkah, bukan 10**: memaksanya lebih pendek menuntut geser sampai 2 px, dan itu keputusan desain — bukan sesuatu yang boleh diambil diam-diam di tengah refaktor.
> **Terverifikasi dengan membandingkan ukuran font TIAP elemen berteks terhadap potret sebelum remap, pada viewport yang sama:** 31 selektor, 19 bergeser, **geser terbesar 0,50 px**, nol selektor hilang.
> ⚠️ Pengukuran pertama sempat menunjukkan `h2` turun **42 → 30 px**. Kalau dipercaya, itu regresi sungguhan. Diperiksa: aturan `clamp()` identik sebelum/sesudah, dan `innerWidth` panel peramban ternyata **0** — sehingga `4.5vw` = 0 dan clamp memilih batas bawahnya. Diukur ulang pada 1280 px: `h2` kembali 42 px. Jebakan sekelas transisi-beku-di-tab-tersembunyi dan parser `color(srgb …)` yang sudah tercatat.
>
> **U31 — SELESAI & LIVE lewat jalur LOKAL** (keputusan owner 2026-08-08). QUIC.cloud sempat didaftarkan, lalu **sengaja tidak dipakai**: layanan optimasi gambar bekerja dengan mengunggah isi media library ke server pihak ketiga, dan media library kita berisi **foto pranikah pelanggan serta gambar QRIS mempelai** — persis data yang B8 acak nama berkasnya karena dianggap sensitif. Itu pemroses data baru yang wajib masuk tabel Kebijakan Privasi lebih dulu, aturan yang proyek ini tetapkan sendiri untuk OpenRouter.
> `scripts/buat-webp.php` (dijalankan di server; Mac pengembang tidak punya encoder WebP) → **1.061.697 → 356.176 byte (−66%)**. Terukur live: beranda **967.844 → 328.184 byte** gambar, enam `<picture>`, peramban benar-benar memilih `.webp`, **nol JPEG demo terunduh**.
> · Skripnya menolak menulis bila WebP-nya justru lebih besar (terjadi pada QRIS demo PNG) · `harih_gambar()` turun anggun ke `<img>` bila `.webp` tidak ada · **`og:image` sengaja tetap `.jpg`** karena dukungan WebP di pratinjau WhatsApp/Facebook tidak andal.
>
> **Ikut selesai: bagian D3 yang memang digandeng** — `aspect-ratio: 1/1` + `object-fit` pada `.qris-panel img` (sumber CLS terakhir; galeri slider & kolase sudah dikunci CSS-nya sendiri) dan `width`/`height` pada thumbnail YouTube (`hqdefault` selalu 480×360). Foto pelanggan **tidak** diberi `width`/`height`: dimensinya tidak diketahui saat render.
> **Sisa D3: keputusan `maxDim` 1600 → 1280** — masih menunggu penilaian mutu cetak dari sampel `TEST-173`, dan itu keputusan owner.


- [x] **U27** 🤖 `jam` — **Tiga aset yatim berhenti dikirim ke setiap pengunjung**
  Komentar `functions.php:233-235` menyatakan niatnya — *"buang SEMUA aset tema/plugin lain… halaman harus ringan & bebas bentrok CSS"* — tapi tiga hal lolos, dan ketiganya sampai ke **halaman undangan** yang dibuka ratusan tamu:
  · **`wc-blocks.css` 14.006 byte, render-blocking di `<head>`**, ada di kedelapan halaman yang diperiksa termasuk ketiga demo undangan. Loop dequeue ([functions.php:245-250](../wp-content/themes/harih/functions.php:245)) berjalan di `wp_enqueue_scripts`, sementara WooCommerce Blocks meng-enqueue saat render — jadi handle-nya tiba setelah loop selesai. Nol blok WooCommerce di halaman undangan.
  · **`global-styles-inline-css` 14.627 byte**, identik di kelima halaman yang diambil — **33% dari HTML mentah undangan** (44.180 byte). Biaya terkompresinya diukur dengan membandingkan brotli HTML utuh (10.398 B) vs tanpa blok itu (8.147 B) = **2.251 byte, 21% dari yang benar-benar terkirim**.
  · **`<div id="ast-scroll-top" tabindex="0">`** disuntik Astra lewat `wp_footer()` sementara CSS **dan** JS Astra dibuang. Dicari di seluruh stylesheet tema dan di kedua blok `<style>` inline yang benar-benar terkirim: **nol aturan** untuk `#ast-scroll-top`. Hasilnya SVG chevron tanpa gaya, **bisa difokus keyboard**, tanpa handler klik, dengan teks pembaca layar berbahasa Inggris *"Scroll to Top"* di halaman `lang="id"`.
  **Langkah:** matikan penyuntiknya, jangan sembunyikan dengan CSS — `add_filter('astra_get_option_scroll-to-top-enable', '__return_false')` (pastikan dulu nama callback-nya dengan `has_action`) · `remove_action` untuk global styles pada halaman yang sudah dideteksi di `functions.php:237-242` · matikan dukungan aset blok WooCommerce di halaman non-toko.
  ⚠️ Mekanisme "enqueue saat render" tidak bisa dibaca dari repo (inti WordPress tidak ada di sini) — **hasilnya** yang pasti. Uji satu per satu di kelima halaman, jangan borongan.
  **Selesai bila:** halaman undangan hanya memuat dua stylesheet milik sendiri · nol `#ast-scroll-top` di HTML · HTML undangan turun ±14 KB mentah.

- [x] **U28** 🤖 `jam` — **Cache: versi per-berkas, font abadi, preload yang sampai ke `/isi-data/`**
  **(a)** `HARIH_VERSION` adalah satu tombol untuk seluruh aset. Dari 12 kenaikan versi terakhir, hanya **3** yang benar-benar menyentuh aset undangan — sembilan sisanya membatalkan cache undangan **tanpa satu byte pun berubah**, termasuk `2.21.0` (D1, murni PHP/n8n). Dengan ~1,2 kenaikan per hari, tamu yang kembali mengunduh ulang CSS+JS undangan berkali-kali tanpa alasan. **(b)** Font woff2 hanya di-cache **7 hari** (66.548 byte) sementara JPEG di origin yang sama 1 tahun dan CSS 30 hari — berkas yang paling tidak pernah berubah justru masa hidupnya paling pendek. **(c)** `/isi-data/` tidak pernah dapat `<link rel=preload>` font karena `harih_halaman_toko()` ([functions.php:315-319](../wp-content/themes/harih/functions.php:315)) tidak memuatnya, padahal hook mode gelap sudah menambahkan pengecualian khusus untuk halaman itu — dua daftar, satu maksud.
  **Langkah:** `harih_ver($rel)` berbasis `filemtime()` dengan `HARIH_VERSION` sebagai fallback, dipakai di `functions.php:256` & `:276-278` · tambahkan `page-isi-data.php` ke `$tpl` (satu baris, sekaligus menghapus kebutuhan pengecualian di `:340`) · `Cache-Control: max-age=31536000, immutable` untuk `*.woff2`.
  ⚠️ Untuk (b): **`find . -name .htaccess` di repo = 0 berkas.** Header ini setelan LiteSpeed/Hostinger, bukan patch yang bisa di-commit — kerjakan lewat SSH/wp-cli seperti preseden A6, dan catat perintahnya di `scripts/setup-hostinger.sh`.
  **Selesai bila:** menaikkan `HARIH_VERSION` tanpa menyentuh CSS undangan tidak mengubah URL aset undangan · `curl -sI` woff2 menunjukkan `immutable` · `/isi-data/` memuat preload font.

- [x] **U29** 🤖 `jam` — **`isi-data.css` dan `katalog.css` berhenti jadi dua sistem desain**
  Delapan belas token dengan nilai **identik** dan nama berbeda: `--accent` vs `--c-accent`, `--bg` vs `--c-bg`, `--ink` vs `--c-ink`, `--soft` vs `--c-ink-soft`, `--line` vs `--c-line`, `--gold` vs `--c-gold`; prefiks font `--f-*` vs `--font-*`.
  🔴 **Kerusakan yang diprediksi sudah terjadi:** empat pasangan yang jelas kembar **tidak lagi sama nilainya** — `--warn-bg #fdf3e4` vs `--c-warn-bg #fdf3e0`, `--warn-line #ecd9b3` vs `--c-warn-line`, dan seterusnya. Halaman yang sama-sama dilihat pembeli dalam satu jam sudah menampilkan dua kuning yang berbeda.
  Sekeluarga: `.btn` punya **tiga** definisi berbeda (padding 16/17/16 px, bobot 800/500/…, radius 999px/`var(--radius-btn)`, `:active` scale vs translateY) dan hanya versi `katalog.css` yang punya `:focus-visible`. `.field` juga tiga. *(Cakupan sebenarnya: `/isi-data/` hanya punya **satu** `.btn` — tombol submit — jadi benturan yang dirasakan pembeli adalah CTA `/harga/` vs tombol kirim itu.)*
  **Langkah:** rename 18 token di `isi-data.css` ke skema `--c-*`/`--font-*` (satu pass `sed`, berkas itu satu-satunya pemuatnya), **lalu** satukan `.btn`/`.field` dengan `katalog.css` sebagai kanon dan salin `:focus-visible` ke ketiga permukaan. Urutannya penting: rename dulu, baru satukan komponen.
  **Selesai bila:** nol token kembar bernilai beda · ketiga permukaan punya perlakuan fokus yang setara.

- [x] **U30** 🤖 `jam` — **Skala tipografi & dua token "teks pendukung"**
  **73 nilai `font-size` unik** di enam stylesheet bila shorthand `font:` ikut dihitung (28 px, 21 rem, 4 em, 12 clamp) — `katalog.css` sendirian menyumbang 43 dan mencampur px, rem, dan em untuk ukuran yang sama. Tidak ada skala; ada tangga acak. Terpisah: `--c-ink-2` (8,47:1) dan `--c-ink-soft` (5,29:1) sama-sama dipakai untuk peran "teks pendukung" di 51 titik, terbelah menurut **umur kode**, bukan menurut makna — beda 3,2 poin kontras, jelas terlihat bersebelahan. Varian gelapnya sama pincangnya. Ikut sekalian: **12** literal `rgba()` di `katalog.css` yang persis varian alpha dari token yang sudah ada, di berkas yang **sudah memakai `color-mix()` 13 kali**.
  **Langkah:** definisikan skala di `:root` (`--fs-3xs` … `--fs-lg`), petakan nilai literal ke tetangga terdekatnya dalam satu pass — mayoritas bergeser <0,5 px sehingga tidak butuh persetujuan desain. Pilih satu satuan. Putuskan satu makna per token tinta dan tulis di komentar definisinya (`--c-ink-2` = teks isi sekunder, `--c-ink-soft` = tersier), lalu audit 51 titik pemakaian. Ganti kedua belas `rgba()` jadi `color-mix()`.
  ⚠️ Ini kerapian, bukan kerusakan berjalan. Kerjakan setelah PU-A dan PU-B.
  **Selesai bila:** nol `font-size` literal di luar `:root` · tiap token tinta punya satu peran tertulis.

- [x] **U31** 🤖 `jam` — **Format gambar modern — dikerjakan bersama D3, bukan sendiri**
  `find` webp/avif di seluruh repo = **0 berkas**, dan negosiasi konten diuji langsung: `curl -H 'Accept: image/avif,image/webp'` tetap membalas `image/jpeg`, **tanpa header `Vary`**. Satu pemuatan undangan = 796 KB, **86,7% di antaranya gambar**.
  ⚠️ **Dua angka pelapor dikoreksi verifikator, dan koreksinya memperkuat argumennya:** LCP beranda **bukan** foto melainkan `<h1>` (diukur dengan `PerformanceObserver` pada 375×812 — kotak h1 lebih luas daripada gambarnya). Jadi foto 252 KB ber-`fetchpriority="high"` justru **merebut bandwidth dari LCP yang sesungguhnya** (teks + woff2) — alasan untuk menurunkannya, bukan menaikkannya.
  Ini **bukan** bagian dari D3: D3 soal `srcset`/`sizes`/`width`/`height` (ukuran & CLS), ini soal **format**. Tapi keduanya menyentuh markup `<img>` yang sama, jadi satu sentuhan.
  **Langkah:** aktifkan konversi WebP LiteSpeed di Hostinger (membentuk `Vary: Accept` tanpa mengubah markup), atau untuk aset tema yang dikontrol sendiri tambahkan `.webp` berdampingan + `<picture>` di `page-katalog.php` dan `galeri.php`. Tinjau ulang `fetchpriority="high"` pada foto hero.
  **Selesai bila:** dikerjakan dalam ronde yang sama dengan **D3** · `Vary: Accept` terbentuk atau `<picture>` terpasang · beranda mengirim WebP ke peramban yang mendukungnya.

---

### Yang TIDAK dikerjakan dari review ini — dan kenapa

| Temuan | Alasan |
|---|---|
| Ukuran alamat gedung berbeda antar tema (14 px tema-02/03 vs 17 px tema-01) | **Gugur di verifikasi** — itu penyesuaian keterbacaan yang disengaja per tipografi tema, bukan inkonsistensi. |
| Tombol hapus foto 22×22 px | **Lulus** WCAG 2.5.8 lewat pengecualian Spacing (jarak antar-pusat 94 px). Hanya di bawah pedoman platform — kerapian, bukan pelanggaran. |
| `format('woff2-variations')` pada Figtree | **Risiko fungsionalnya patah lewat uji diferensial langsung** — berkas yang sama dideklarasikan tiga kali dengan string format berbeda dan lebar render teksnya identik. Murni konsistensi penulisan. |
| Badge RSVP "Berhalangan" 4,49:1 | Meleset dari 4,5:1 **sebesar 0,001** — dan menjadi 4,4999 bila komposit dibulatkan 8-bit seperti peramban. Ikutkan saja saat U14 dikerjakan; jangan jadi task sendiri. |
| Moderasi ucapan tamu | Butuh keputusan produk (antrean tinjau = friksi bagi tamu). Yang dikerjakan sekarang hanya `overflow-wrap` di U13. |

---

## 🚫 Dikeluarkan dari rencana — keputusan owner 2026-08-08

**Akuisisi & penetapan pasar dikeluarkan dari daftar kerja.** Owner memilih fokus ke pengembangan platform lebih dulu; kedua poin di bawah dinilai terlalu jauh masuk ke sisi bisnis. Analisisnya disimpan sebagai **catatan keputusan, bukan tugas terbuka** — jangan dimunculkan lagi sebagai daftar kerja.

<details><summary><strong>C9</strong> — hulu akuisisi <em>(analisis asli, tidak dikerjakan)</em></summary>

~~**C9**~~ 👤 `hari` — **Isi hulu akuisisi** *(lubang terbesar dalam rencana)*
  Grep `tiktok|iklan|marketplace|blog` di seluruh docs: **nol rencana**. Dari 104 commit: **nol menyentuh akuisisi**. Tema tidak punya template artikel sama sekali, sementara SERP untuk kata kunci niat-beli dikuasai blog vendor pesaing — artikel merekalah yang menanamkan pagu "Rp 500rb–1jt" ke kepala pembeli yang lalu membuka halaman Hormat Rp 1,19 juta.
  **Selesai bila:** tiga aksi berjalan, tanpa kode — (1) **5 vendor/WO pertama didekati** (sudah tertulis sebagai F1.9, sudah dinyatakan bisa paralel, nol biaya — **naikkan ke urutan 2 daftar owner, di atas QA perangkat**); (2) 3 halaman statis ber-SEO lewat `page-teks.php` yang sudah ada ("harga undangan cetak vs digital 2026", "berapa undangan cetak yang sebenarnya dibutuhkan") — di sinilah harga per lembar dibenarkan di depan orang yang sedang membandingkan; (3) lapak di satu direktori pernikahan dengan harga tercantum, CTA tetap WhatsApp sehingga tidak menunggu payment gateway.

</details>

<details><summary><strong>D5</strong> — titik masuk di bawah Rp 1 juta <em>(analisis asli, tidak dikerjakan)</em></summary>

~~**D5**~~ 👤 `jam` — **Tidak ada titik masuk paket cetak di bawah Rp 1 juta**
  Paket cetak termurah adalah Hormat Rp 1.190.000, dan pembelian satuan dipatok minimum Rp 1.000.000/transaksi — jadi tidak ada satu pun titik masuk di bawah sejuta, sementara artikel harga yang menguasai SERP menanamkan pagu jauh di bawah itu. Menunggu data closing rate nyata sebelum diubah.

</details>

> Penilaian saya tetap tercatat apa adanya: selama hulu kosong, perbaikan platform tidak mengubah angka penjualan. Itu **catatan, bukan bantahan** — keputusan cakupan ada di owner, dan platform yang rapi tetap punya nilainya sendiri saat hulunya nanti diisi.

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

7. ~~**Harga satuan vs harga paket**~~ → **DIPUTUSKAN 2026-08-08: satuan yang terlalu murah.** Tabelnya dihargai waktu produknya masih kartu QR Rp 9.500 dan tidak ikut diperbarui. Undangan lipat 15rb → **35rb/pcs** (diubah di produk WooCommerce id 85 **dan** tabel `/harga/` — keduanya sumber terpisah, dan perbedaannya persis yang melahirkan U24). Paket kini menang di ketiga tingkat. **Perbaikan sesungguhnya bukan angka:** satuan kini **tidak termasuk undangan digital dan tidak mendapat Garansi Tepat Waktu**, jadi keduanya berhenti sebanding dan kalkulator pembeli tidak lagi bisa mengalahkan kita di tingkat mana pun.

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
| Halaman upgrade | **Hanya Resepsi & Grand.** Hormat dicabut — **ditinjau ulang 2026-08-08 dengan angka terukur, lalu DITEGASKAN.** ⚠️ Alasan aslinya (marjin ±1,1×) terbukti **keliru**: pengukuran nyata memberi ±Rp 241rb/jam dinding untuk versi upgrade Rp 890rb, masih di atas pekerjaan reguler. Yang menahannya bukan marjin melainkan **jangkar harga** — Rp 890rb di sebelah Rp 2,6 jt menarik pembeli ke yang termurah, dan itu tidak terbantah oleh pengukuran apa pun. `CETAK-HORMAT` tetap dijual penuh di katalog |
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
