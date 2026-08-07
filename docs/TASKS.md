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

- [ ] **A4** 🤖 `jam` — **Nomor WhatsApp wajib & tervalidasi di checkout blok**
  `woocommerce.php` memasang label, validasi, dan normalisasi nomor WA lewat hook yang **hanya difire checkout shortcode**. `cetak.php` sudah mencatat sendiri bahwa checkout yang dipakai adalah blok, tapi perbaikan F3.6 hanya menambal field **alamat** — `phone` tidak pernah ikut. Akibatnya nomor WA boleh kosong → WF-01 menolaknya → status `TIDAK_VALID` → link `/isi-data/` hanya lewat email. Pembeli yang sudah bayar tidak pernah menerima undangannya.
  **Selesai bila:** field telepon wajib di checkout blok, berlabel "Nomor WhatsApp" dengan penjelasan bahwa link dikirim ke sana, validasi + normalisasi lewat `woocommerce_store_api_checkout_update_order_from_request` (hook yang sudah terbukti jalan di `cetak.php:402`) memakai `undangan_normalize_phone()` + `undangan_is_valid_wa()` yang sudah ada.

- [ ] **A5** 🤝 `jam` — **Penjaga anti-checkout-kosong pada filter gateway + verifikasi ID gateway Duitku**
  `cetak.php:284-288` menghapus setiap gateway yang id-nya tidak memuat `_va_`, `briva`, `indomaret`, `alfamart` untuk keranjang > Rp 2 juta — **tanpa penjaga "kalau hasilnya kosong, kembalikan aslinya"**. Keempat substring itu tebakan; tidak ada satu berkas pun di repo yang mencatat ID gateway Duitku yang nyata.
  **Selesai bila:** penjaga terpasang (hasil penyaringan kosong → kembalikan `$gateways` utuh) **dan** ID nyata dicetak di server (`wp eval 'print_r(array_keys(WC()->payment_gateways->get_available_payment_gateways()));'`) lalu `str_contains` diganti allowlist eksplisit yang sudah diverifikasi.

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

- [ ] **A7** 🤝 `jam` — **Buka jalur bayar manual untuk paket digital — berhenti menunggu Duitku**
  Dokumen lama menyebut Duitku "gerbang tunggal", tapi F1.6 **sudah merestui** invoice WA + transfer manual untuk order Rp 2,9 juta. Jalur yang sah untuk produk 30× lebih mahal belum pernah diterapkan ke produk Rp 99–299 ribu yang 10 penjualannya adalah gerbang sesungguhnya. **Tidak butuh satu baris kode baru:** owner kirim rekening/QRIS → buat order WooCommerce → set `processing` → WF-01 menyala persis seperti biasa. Setiap hari menunggu adalah hari tanpa data attach rate, closing rate, dan distribusi tier.
  **Selesai bila:** tombol "Pesan lewat WhatsApp" di samping tombol checkout di katalog · runbook memuat langkah membuat order manual + set processing · satu penjualan uji tuntas sampai undangan terbit (menumpang A3).

- [ ] **A8** 👤 `menit` — **Tindak lanjuti Duitku: profil nominal, mekanisme refund, plafon per kanal**
  Diajukan 2026-08-04, belum keluar. Tiga hal yang bisa menggagalkan pembayaran di langkah terakhir: profil merchant menyebut Rp 99–299 ribu padahal paket cetak sampai Rp 5,9 juta · mekanisme refund (Garansi Tepat Waktu menjanjikan 100% — berapa lama, siapa menanggung fee kanal) · plafon per kanal (e-wallet/QRIS sering di bawah Rp 5,9 juta). Jawabannya juga menentukan bentuk akhir A5.
  **Selesai bila:** ketiga pertanyaan terkirim & jawabannya dicatat di sini. Bila approval keluar: kredensial plugin ke Production dan order uji dijalankan — **setelah A1–A6 tuntas**.

---

## P1 — Tutup lubang yang merugikan order pertama & janji yang sudah tayang

> Tidak menghambat rupiah pertama, tapi bila terjadi biayanya reputasi atau hukum — jenis kerugian yang tidak bisa ditambal belakangan. **B8 dan B9 menumpang ronde import WF-02 yang sama dengan A1** — jangan menyentuh WF-02 dua kali.

- [ ] **B1** 🤖 `jam` — **Endpoint RSVP publik bisa dipanen dengan loop nomor**
  `rest.php:19-23` mendaftarkan `/rsvp/(?P<id>\d+)` dengan `permission_callback => '__return_true'`, dan `undangan_rsvp_list()` mengembalikan nama, pesan, hadir, jumlah, sesi, waktu untuk 50 ucapan **tanpa memeriksa** bahwa post itu ada, bertipe `undangan`, atau berstatus publish. Seluruh strategi privasi bertumpu pada "slug tidak bisa ditebak" — dan endpoint ini justru memakai **ID post berurutan**. Satu loop `1..5000` memanen nama tamu & ucapan pribadi semua pelanggan tanpa pernah tahu satu link undangan. Ironis: file yang sama berjuang menutup semua jalur enumerasi lain.
  **Selesai bila:** endpoint dikunci ke slug (`get_page_by_path($slug, OBJECT, 'undangan')`), menolak 404 bila tipe bukan `undangan` atau status bukan `publish`; `undangan.js` diberi `cfg.slug`; pemeriksaan sama diterapkan di `undangan_rsvp_create`.

- [ ] **B2** 🤖 `menit` — **GA4 mengirim token HMAC pelanggan ke Google dari empat halaman bertoken**
  `functions.php:459-461` hanya mengecualikan undangan dan `/isi-data/`, sementara `functions.php:442-450` menulis alasannya sendiri: URL halaman ini memuat token order — sebuah bearer credential — dan GA4 mengirim URL lengkap sebagai `page_location`. Keempat halaman lain memakai token yang sama dan sama-sama memasang `<meta name="referrer" content="no-referrer">`: penulisnya tahu tokennya sensitif, hanya lupa pengecualian GA4-nya. Kebijakan Privasi **sudah tayang** berjanji halaman bertoken "tidak boleh dikirim ke pihak ketiga mana pun".
  **Selesai bila:** helper `harih_halaman_bertoken()` berisi kelima template, dipakai di blok GA4, filter `wp_robots`, dan `harih_halaman_utilitas()` — supaya halaman bertoken berikutnya otomatis ikut terlindungi.

- [x] **B3** 🤝 `menit` — **errorWorkflow terikat + 5 `id` ditanam** → **SELESAI & LIVE 2026-08-07**
  Diimpor bersama A1. **Terverifikasi dari ekspor live kesembilan workflow:** delapan memuat `"errorWorkflow":"sJ0vsHhFyPotbxMg"`, WF-06 mendapat `timezone: Asia/Jakarta` kembali, dan **9 workflow tetap 9 — nol duplikat**, karena kelima `id` sudah tertanam sebelum import.
  **Keputusan yang menyimpang dari rencana:** `errorWorkflow` **tidak** dipasang di WF-00 (rencana semula menyebut "kesembilan"). Error handler yang menunjuk dirinya sendiri berisiko loop saat WF-00 sendiri yang gagal. Delapan sisanya cukup.
  **Terbukti bekerja 2026-08-07 lewat A3** — dua error sungguhan di WF-02 memicu WF-00 (eksekusi 4253 & 4255, keduanya `success`, alert menyebut *"Workflow GAGAL · WF-02"*). Tidak perlu error buatan.
  <details><summary>ID live yang ditanam (untuk rujukan)</summary>

  WF-00 `sJ0vsHhFyPotbxMg` · WF-03 `k6LyfYoYds47al38` · WF-04 `539zvR4mzQ5PObJ6` · WF-07 `AbxU2iCdYmKRx5G0` · WF-08 `AI0ofPRSqBhbLbwO`
  </details>

- [ ] **B4** 🤖 `menit` — **WF-01 `Baca Ulang Orders` tanpa `alwaysOutputData`**
  Node punya `retryOnFail` tapi tidak `alwaysOutputData`, sementara node berikutnya dibuka dengan `throw new Error('Verifikasi idempotency gagal...')` yang jelas ditulis untuk kasus nol baris. Bila lookup mengembalikan 0 item, n8n tidak menjalankan node hilir sama sekali — throw itu **tidak pernah dieksekusi**, eksekusi berakhir SUKSES, email & WA tidak terkirim, dan barisnya tetap ada di sheet sehingga WF-08 tidak menganggapnya tertinggal. Baru berguna setelah B3 beres.
  **Selesai bila:** `alwaysOutputData: true` di `Baca Ulang Orders`, dan `Append Baris Order` — satu-satunya node Sheets di jalur uang tanpa retry — diberi `retryOnFail: true, maxTries: 3, waitBetweenTries: 3000`.

- [ ] **B5** 🤝 `jam` — **`/jadi-reseller/` & WF-03 menjanjikan komisi 30% "tiap order"**
  Ditulis di h1, hero-sub, langkah 3, daftar syarat, meta description, og:description, dan tagline kaki — hanya baris kaki memakai kata "digital". Berhadapan dengan keputusan terkunci: digital 30%, fisik rupiah tetap Rp 150/300/500rb. Reseller yang menjual Paket Resepsi mengharapkan Rp 870.000 dan menerima Rp 300.000 — **selisih Rp 570.000 per order** di kanal yang seluruh nilainya kepercayaan. Lebih cepat lagi: kupon `RES-` **ditolak di keranjang cetak** sehingga order terbesar berhenti di langkah terakhir. **Halaman ini live dan menerima pendaftar hari ini.**
  **Selesai bila:** setiap klaim 30% dikualifikasi jadi "30% untuk paket digital · Rp 150/300/500rb untuk paket cetak · item satuan tanpa komisi" di ketujuh titik + teks WF-03. Bila owner memilih menutup pendaftaran (lihat pertanyaan owner), halaman diturunkan sebagai gantinya. Kontradiksi rekrut-vs-tidak antara arsip TASKS dan `panduan-manual.md:159` ikut diselesaikan.

- [ ] **B6** 🤖 `menit` — **Kebijakan Privasi tidak menyebut nomor rekening reseller maupun daftar nama tamu**
  Yang benar-benar telanjang adalah **nomor rekening**: formulir publik `/jadi-reseller/` mengumpulkan nama, WA, bank, norek; WF-03 menaruhnya di pesan WhatsApp + email approval lalu menyimpannya di tab `resellers` — kategori data keuangan tanpa satu kalimat dasar pemrosesan atau retensi. Daftar nama tamu (sampai 600 nama pihak ketiga) sebagian tertutup semangat kebijakan tapi tetap perlu diklarifikasi. **Jangan tunggu C8 selesai** — menunda teksnya memperpanjang periode tanpa pemberitahuan.
  **Selesai bila:** bagian 1 memuat sub-bagian "Dari pemesan" (daftar nama tamu untuk amplop & link personal) dan "Dari calon reseller" (nama, WA, bank, nomor rekening untuk pembayaran komisi), diterbitkan lewat `scripts/publish-legal.py`.

- [ ] **B7** 🤝 `jam` — **S&K & Refund masih menjual produk yang sudah diganti + tidak ada batas waktu bagi pelanggan**
  (a) `syarat-ketentuan.md:9` masih menyebut produk fisik sebagai "kartu QR akses, label souvenir, kartu terima kasih, stiker segel" — model berubah jadi undangan lipat + amplop bernama pada 6 Agustus. "Garansi QR Terbaca" berjanji mengganti seluruh **batch**; bila "batch" kini ditafsirkan 150 undangan lipat premium, biayanya berlipat. Dalam sengketa, ambiguitas ditafsirkan **melawan penyusun dokumen**.
  (b) §12.2 hanya mengikat tanggal **pemesanan**; tidak ada satu kewajiban waktu bagi pelanggan, padahal 3 dari 6 tahap antrean menunggu pelanggan. Order H-21 yang pelanggannya lambat 10 hari = barang terlambat, refund Rp 2,9 juta, bahan + ongkir tetap keluar.
  **Selesai bila:** daftar produk fisik di S&K §1 dan Refund §4 diperbarui · Garansi QR ditulis ulang dengan objek "QR pada undangan cetak" dan remedy proporsional · rujukan bagian di Refund:7 dibetulkan · klausul waktu pelanggan ditambahkan ke §12.2 **setelah owner menyetujui** · diterbitkan lewat `publish-legal.py`.

- [ ] **B8** 🤖 `menit` — **Nama berkas foto & QRIS pelanggan bisa ditebak dari nomor order berurutan**
  WF-02 membuat `undangan-${order_id}-foto-${i+1}.${ext}` dan `undangan-${order_id}-qris.png`. Ironisnya slug halaman undangan justru **sengaja diacak**. URL `/wp-content/uploads/2026/08/undangan-142-qris.png` bisa dicoba satu per satu. Yang bocor bukan cuma foto pranikah melainkan **gambar QRIS** — instrumen pembayaran mempelai — dan pemetaan nomor order ke identitas pasangan. Berkasnya tetap hidup setelah masa aktif habis karena `masa-aktif.php` hanya mendraft post.
  **Selesai bila:** node `Pisahkan File` menyisipkan `crypto.randomBytes(4).toString('hex')` ke nama foto dan QRIS; A3 membuktikan berkasnya tetap tampil benar.

- [ ] **B9** 🤖 `jam` — **Beri cakupan pada token: satu token seumur hidup membuka kelima halaman**
  Rumusnya identik dan hanya dari `order_id`, tanpa komponen waktu maupun cakupan. Meneruskan link `/rekap/` ke wedding organizer untuk menghitung porsi katering — perilaku yang **pasti terjadi** karena halaman itu memang untuk dipakai bersama — otomatis menyerahkan wewenang menekan "setujui proof", dan `proof.php` mencatatnya sebagai bukti yang memindahkan tanggung jawab typo ke pemesan. Saat sengketa datang, bukti itu runtuh. Kriptografinya benar; **cakupannya yang salah**. Kerjakan sekarang selagi nol pelanggan — begitu link beredar, migrasi token jadi mahal.
  **Luasnya:** 5 template + `proof.php` dua tempat + WF-01 + **tiga** perhitungan token di node `Siapkan Pesan Delivery` WF-02 → wajib satu ronde dengan A1 & B8.
  **Selesai bila:** bahan HMAC memuat nama halaman (`$order_id . '|proof'`, `'|tamu'`, dst.) di seluruh titik; A3 membuktikan kelima link dari pesan delivery masih terbuka **dan** link `/rekap/` tidak bisa membuka `/proof/`.

---

## P2 — Naikkan konversi & keandalan

> Tidak ada yang gagal hari ini karena nol pengunjung dan nol order. Yang masuk di sini dibenarkan oleh **biayanya** (menit, di berkas yang toh sudah dibuka), bukan besarnya dampak. **C3 dan C9 layak lebih dulu** — keduanya menyentuh alasan orang datang & membeli.

- [ ] **C9** 👤 `hari` — **Isi hulu akuisisi** *(lubang terbesar dalam rencana)*
  Grep `tiktok|iklan|marketplace|blog` di seluruh docs: **nol rencana**. Dari 104 commit: **nol menyentuh akuisisi**. Tema tidak punya template artikel sama sekali, sementara SERP untuk kata kunci niat-beli dikuasai blog vendor pesaing — artikel merekalah yang menanamkan pagu "Rp 500rb–1jt" ke kepala pembeli yang lalu membuka halaman Hormat Rp 1,19 juta.
  **Selesai bila:** tiga aksi berjalan, tanpa kode — (1) **5 vendor/WO pertama didekati** (sudah tertulis sebagai F1.9, sudah dinyatakan bisa paralel, nol biaya — **naikkan ke urutan 2 daftar owner, di atas QA perangkat**); (2) 3 halaman statis ber-SEO lewat `page-teks.php` yang sudah ada ("harga undangan cetak vs digital 2026", "berapa undangan cetak yang sebenarnya dibutuhkan") — di sinilah harga per lembar dibenarkan di depan orang yang sedang membandingkan; (3) lapak di satu direktori pernikahan dengan harga tercantum, CTA tetap WhatsApp sehingga tidak menunggu payment gateway.

- [ ] **C3** 🤖 `menit` — **Empat kalimat copy yang melawan penjualan sendiri**
  (a) `page-harga-hybrid.php:186` menyiarkan "**8 dari 8 slot** produksi Agustus masih tersedia" — pada nol order itu memberi tahu calon pembeli bahwa **belum ada seorang pun yang memesan**, di halaman yang menutup Rp 2,9 juta. Kelangkaan hanya bekerja bila sebagian sudah terpakai.
  (b) `page-katalog.php:36` "Masa aktif sampai H+7" adalah satu-satunya baris di katalog yang isinya **kabar buruk**, di paket jangkar harga terendah — sementara pesaing Rp 50.000 berbayar menjual "Masa Aktif Selamanya". Hapus barisnya; `masa-aktif.php` tetap menegakkan H+7.
  (c) Hero memimpin dengan "satu desain, dua wujud" — padahal percetakan pesaing sudah melempar undangan digital sebagai **bonus gratis**. Pimpin dengan yang tidak diberikan gratis siapa pun: **amplop bernama tercetak + tiga garansi**.
  (d) "bukan tulis tangan" mengalahkan lawan yang tidak ada; lawan sebenarnya adalah **stiker label yang ditempel**, dan hampir semua percetakan memberi label nama gratis. Ubah jadi "dicetak langsung pada amplop — bukan stiker label yang ditempel".
  **Selesai bila:** sisa slot hanya tampil bila `> 0 && <= 4` (ambang jadi konstanta bersebelahan dengan `UNDANGAN_KUOTA_BULAN`, cabang "slot penuh" dipertahankan) · baris H+7 hilang dari kartu harga · H1 hybrid memimpin dengan garansi + amplop tercetak · frasa "bukan tulis tangan" diganti di tiga tempat.

- [ ] **C1** 🤖 `jam` — **Undangan bisa tampil rusak atau tidak terbuka sama sekali di HP tamu**
  Tiga cacat pada satu-satunya produk yang dilihat calon pembeli sebelum membeli.
  (a) `color-scheme` nol di `undangan.css` dan ketiga skin, padahal sudah dipakai di halaman toko — mode gelap paksa Samsung Internet/Chrome membalik krem tema-01/02 jadi abu.
  (b) `--c-ink-soft` tema-01 (3,76:1) dan tema-02 (3,73:1) **gagal WCAG AA** — dan token itulah yang mewarnai **alamat lokasi** pada 14px. tema-03 sudah membawa komentar bahwa auditnya dilakukan, lalu berhenti di satu tema; **tema-01 adalah tema bawaan**.
  (c) `is-locked` ditulis **statis** di `single-undangan.php:112` dan hanya bisa dibuka JS di footer — satu kegagalan JS = gerbang layar penuh dengan tombol mati **dan** halaman tak bisa digulir, nol persen isi terbaca.
  **Selesai bila:** `color-scheme: only light` di tema-01 & 02, `only dark` di tema-03 · `--c-ink-soft` digelapkan ke ±`#6b6758` dan ±`#77685a` (lolos 4,5:1) · `is-locked` dipasang lewat script inline sinkron di head (pola `data-no-optimize="1"`) sehingga kegagalan JS = halaman tidak pernah terkunci. **Jangan** pakai `<noscript>` (tidak menolong 4G putus atau galat runtime) dan **jangan** pakai `setTimeout` pelepas (halaman menggulir di belakang overlay fixed = tampak hang).

- [ ] **C2** 🤖 `menit` — **Tombol salin berbohong saat gagal, label tersangkut**
  `undangan.js:510-511` memakai `.then(selesai, selesai)` — argumen kedua adalah handler **penolakan**, diisi fungsi yang sama dengan jalur sukses → tombol menampilkan "Tersalin ✓" walaupun penyalinan gagal. Di WebView WhatsApp (dari mana mayoritas tamu Indonesia membuka undangan) `clipboard.writeText` bisa menolak, dan justru di kondisi itu kode ini berbohong — tamu menempel isi clipboard lama di aplikasi m-banking. Yang lebih pasti terjadi: `var asli = btn.textContent` dibaca **setiap klik**, jadi ketukan kedua dalam 1800 ms menyimpan "Tersalin ✓" sebagai teks asli dan label tersangkut permanen.
  **Selesai bila:** `.then(selesai, gagalSalin)` dengan label "Gagal menyalin — tekan lama nomornya" · label asli disimpan di `btn.dataset.label` di luar handler klik · `u.jumlah` dibungkus `u.hadir === 'hadir'` (buku tamu publik kini menampilkan "Berhalangan · 3 tamu") · `beforeunload` di `isi-data.js:255` dilonggarkan dari `if (state.uploading)` jadi "form kotor **atau** sedang mengunggah".

- [ ] **C4** 🤖 `menit` — **`cetak.php`: waktu UTC dipakai untuk acara WIB, dan kuota hanya membaca 50 order terakhir**
  (a) `cetak.php:380` menghitung selisih H-21 dengan `gmdate('Y-m-d')` — WordPress memaksa timezone PHP ke UTC, jadi antara 00:00–07:00 WIB tanggalnya masih kemarin dan order H-20 lolos sebagai H-21. Pola sama di `:341` dan `:327`, padahal `masa-aktif.php:67` sudah memakai `wp_date()` dengan benar.
  (b) `cetak.php:338-343` memakai `'limit' => 50` urutan menurun — begitu ada 50 order digital dalam sebulan (target wajar bila akuisisi berhasil), order cetak **terdorong keluar jendela** dan checkout menerima pesanan melewati kapasitas, diam-diam.
  (c) `cetak.php:38-45` menggerbangkan seluruh alur cetak dari `!is_virtual()` padahal `cetak.php:15` menyatakan **SKU adalah sumber kebenaran** — produk digital yang dibuat manual di wp-admin bawaannya non-virtual, dan begitu itu terjadi pembeli Rp 99rb dapat form alamat, wajib isi tanggal acara, dan diblokir kuota cetak.
  **Selesai bila:** `gmdate` → `wp_date` di tiga tempat · `'limit' => -1` (transient 10 menit sudah menahan biayanya) · `undangan_cart_ada_fisik()` memakai `undangan_jenis_produk()` sebagai otoritas dengan `is_virtual()` sebagai cadangan, plus notice admin bila SKU `HARIH-*` tidak virtual.

- [ ] **C7** 🤖 `menit` — **WF-08 & WF-05: jaring pengaman yang buta dan nudge palsu**
  (a) WF-08 mengambil 100 order terbaru dengan `status=any` lalu baru menyaring — checkout terbengkalai meninggalkan order pending/failed yang normal dalam volume besar, sehingga **order berbayar yang webhooknya gagal bisa terdorong keluar jendela**, dan justru di skenario itu `tertinggal` kosong sehingga `return []` mematikan **seluruh cabang alert**. Owner membaca kesunyian itu sebagai "sistem sehat". Catatan node menyebut multi-status tidak didukung, padahal WF-06 melakukannya persis.
  (b) WF-05 memakai `MASA_AKTIF_HARI[row.paket]` tanpa mengupas sufiks → `premium+cetak` jatuh ke 7 hari (pasangan dari A1).
  (c) Order cetak à la carte tetap menulis baris `MENUNGGU_DATA` meski komentarnya menyatakan tidak → pembelinya menerima nudge "isi data undanganmu" untuk undangan yang tidak pernah ia beli. Aktif begitu produk cetak dibuka dari `/shop/`.
  **Selesai bila:** WF-08 memakai `status=processing,completed` dan mengeluarkan alert terpisah bila jendela penuh tapi tidak ada temuan · WF-05 mengupas sufiks `+cetak` · WF-01 menulis status `CETAK_SAJA` untuk `jenis_order=cetak_saja`.

- [ ] **C6** 🤖 `menit` — **Daftar nama tamu berada di luar snapshot yang dipakai S&K §12.1**
  `proof.php:38-44` mendaftar 19 kunci snapshot; `daftar_tamu` tidak ada, jadi `_proof_hash` tidak pernah mengunci nama tamu — sementara `page-tamu.php` menulis meta itu tanpa memeriksa `_proof_disetujui`. Amplop bernama adalah pembeda yang dijual, 50–150 keping per order, tanpa jejak versi mana yang disetujui. **Alasan mengerjakannya sekarang:** nol snapshot ada di produksi, jadi mengubah daftar kunci tidak membatalkan hash apa pun — nanti mahal.
  **Selesai bila:** `daftar_tamu` masuk array `$kunci` (hash ikut otomatis karena `sort`), dan `page-tamu.php` mencatat `add_order_note()` bila daftar berubah setelah proof disetujui. **Jangan mengunci keras** — pemesan yang baru menemukan salah ketik satu nama akan terjebak, dan CS-nya cuma satu orang.

- [ ] **C5** 🤖 `menit` — **Harga tiga paket digital di-hardcode di halaman depan**
  `page-katalog.php` menulis "99", "179", "299" sebagai teks; `harih_url_beli()` hanya mengambil ID produk dari SKU dan tidak pernah membaca harganya. Bandingkan `page-satuan.php` yang menuliskan alasannya sendiri: "harga di halaman ini tidak akan pernah berbeda dari harga yang ditagihkan checkout". Halaman depan adalah satu-satunya etalase produk digital **dan** satu-satunya halaman harga yang tidak terhubung ke sumber harganya. Jendelanya sempit hari ini, tapi terbuka lebar pada hari owner memasang sale — dan drift ini **sudah pernah terjadi**.
  **Selesai bila:** `harih_url_beli()` mengembalikan juga `get_price()` mentah, dirender dengan pembagian 1000; angka hardcode disisakan sebagai fallback pre-deploy. *Jebakan:* formatnya angka + `<span class="rb"> rb</span>`, jadi `wc_price()` tidak bisa dipasang begitu saja.

- [ ] **C10** 🤖 `jam` — **Dokumen pemulihan berbohong**
  `n8n/workflows/README.md:3-12` melompat dari WF-05 ke WF-07 dan baris 14 menegaskan "WF-06 bukan workflow n8n" karena label itu dipakai untuk script backup — sementara `WF-06-reminder-upsell.json` nyata dan aktif. Daftar impor melewatkannya. **Setelah rebuild VPS, orang yang mengikuti README akan mengimpor delapan, melihat delapan aktif, dan menganggap selesai** — yang hilang justru pengingat H+3/H+12 yang menjual paket cetak, tanpa satu error pun, hanya attach rate diam-diam nol yang disalahartikan "upsell tidak laku".
  **Selesai bila:** README memuat WF-06 di tabel & daftar impor · script backup dinamai ulang (mis. `BACKUP-MINGGUAN`) di baris 14/91/114 · hitungan "9 workflow aktif" ditambahkan ke runbook §2 sebagai langkah SSH (bukan ke `cek-live.sh` yang berjalan dari mesin developer lewat HTTP).

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

**Metrik pengendali** *(urutannya penting)*: marjin per jam produksi (target ≥ Rp 600rb/jam; pada marjin Rp 2,6 juta batasnya **4 jam 20 menit**) → closing rate → attach rate per tingkat → distribusi paket → tingkat reprint → pendapatan berulang vendor.

⚠️ **Klaim "marjin lulus 3–6× lipat" hanya berlaku untuk Resepsi & Grand.** Terhadap patokan tertinggi pekerjaan reguler (Rp 200rb/jam): Grand 4,6× · Resepsi 2,9× · **Hormat cuma 1,6× di harga penuh**. Setup mesin sama untuk 50 maupun 150 unit, jadi paket terkecil menanggung setup yang sama dengan pendapatan terkecil. Pemisahan setup/per-unit adalah **model, bukan pengukuran** — repo hanya punya satu titik data (4,5 jam untuk 100 unit) dan itu pun estimasi. Yang kokoh adalah arahnya, bukan angka desimalnya.

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
