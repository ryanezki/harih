# TASKS — hariH Hybrid · Digital + Cetak Format Kecil

**Sumber:** Rencana Bisnis v2.0 (Agustus 2026) · **Status:** perencanaan · **Dibuat:** 2026-08-05

> Jalur kerja **terpisah** dari [`TASKS.md`](./TASKS.md), yang mengurus platform digital sampai pendapatan pertama. Dokumen ini baru dimulai setelah platform digital benar-benar menerima uang (TASKS P0.1). ID `H0.1`, `H1.2`, dst. — tanda **👤** = butuh tangan owner.

**Prinsip yang mengatur seluruh urutan di bawah:**

> **Jual dan penuhi manual dulu, bangun engine belakangan.** Rencana bisnis §13.3 menyatakan sendiri bahwa attach rate, waktu produksi 4 jam, dan kesediaan bayar Rp 2,9 juta semuanya masih tebakan. Engine SVG→PDF→imposition→cut file adalah 2–3 minggu kerja; memenuhi 5 order pertama secara manual adalah ±5 jam. Membangun lebih dulu berarti bertaruh tiga minggu pada tiga tebakan sekaligus — dan kehilangan kesempatan mengukur waktu produksi sungguhan.

---

## H0 — Keputusan yang memblokir semuanya

*Tidak ada baris kode atau pembelian alat sebelum keempat ini dijawab.*

- [ ] **H0.1** 👤 **Selesaikan tabrakan harga pintu masuk**
  **Masalah:** rencana v2 menetapkan Paket Digital **Rp 299.000** sebagai pintu masuk. harih.id **sudah live** dengan Rp 99rb / 179rb / 299rb; katalog, deskripsi produk WooCommerce, dan kartu share WhatsApp semuanya bertuliskan *"mulai Rp 99 ribu"*. Pembeli paket Rp 99rb sudah menyeleksi dirinya sebagai pembeli hemat — mengupsell ke Rp 2,9 juta adalah lompatan **29×**. Asumsi attach rate 20–40% berdiri atau runtuh di sini.
  **Tiga jalan, pilih satu:**
  1. **Naikkan pintu masuk ke Rp 299rb saja** (satu paket digital, sesuai v2). Paling konsisten dengan penjangkaran harga, tapi mematikan segmen volume + program reseller yang sudah jalan.
  2. **Pertahankan tiga tingkat sekarang, tawarkan cetak hanya ke pembeli Favorit & Premium.** Pembeli Hemat tidak pernah dilihatkan penawaran cetak. Attach rate dihitung dari basis yang lebih kecil tapi jauh lebih realistis.
  3. **Dua pintu terpisah** — harih.id tetap volume/murah, dan cetak dijual lewat halaman/positioning sendiri dengan pintu masuk Rp 299rb. Paling bersih secara penjangkaran, paling mahal secara pemasaran (dua cerita, dua audiens).
  **Rekomendasi saya: opsi 2.** Alasannya: tidak membuang aset yang sudah jalan (reseller, SEO, katalog), dan pembeli Favorit/Premium sudah membuktikan kesediaan membayar di atas minimum — itu sinyal terbaik yang Anda punya secara gratis. Opsi 1 membuang program reseller yang baru dibangun; opsi 3 menggandakan beban pemasaran solo.
  **Selesai bila:** satu opsi dipilih, dan konsekuensinya pada katalog + program reseller tertulis.

- [ ] **H0.2** 👤 **Selesaikan tabrakan reseller vs vendor**
  **Masalah:** hariH sudah punya program reseller (pembeli diskon 10%, reseller komisi **30%**). Rencana v2 memperkenalkan program vendor (WO/MUA) dengan langganan bulanan. Keduanya menyasar orang yang sama, dan dokumen tidak menjawab: **apakah reseller mendapat 30% dari order cetak Rp 2,9 juta juga?** Kalau ya, itu Rp 870 ribu per order — sepertiga marjin, untuk penjualan yang pekerjaannya ada di tangan Anda.
  **Yang perlu diputuskan:** (a) komisi reseller berlaku **hanya untuk paket digital**, tidak untuk cetak — atau komisi cetak dipatok nominal jauh lebih kecil (mis. Rp 150rb/order); (b) apakah satu orang boleh jadi reseller **dan** vendor sekaligus, dan mana yang berlaku bila keduanya melekat pada satu order.
  **Kenapa penting sekarang:** kupon `RES-` yang sudah beredar mengikat 30% ke seluruh nilai order. Kalau produk cetak nanti masuk sebagai produk WooCommerce biasa, kupon reseller **otomatis berlaku ke sana** kecuali dikecualikan sejak awal. Ini bocor diam-diam, bukan keputusan.
  **Selesai bila:** aturan tertulis, dan kupon reseller dibatasi ke kategori produk digital di WooCommerce.

- [ ] **H0.3** 👤 **Uji fisik QR: cetak → laminasi doff → pindai di ruangan remang**
  **Kenapa jadi blocker, bukan QA biasa:** Garansi QR Terbaca (v2 §5.6) tidak boleh dipasang di halaman harga sebelum uji ini lolos. Laminasi doff menurunkan kontras dan bisa membuat QR gagal terbaca — persis skenario yang dijanjikan garansi itu.
  **Langkah:** cetak kartu contoh dengan QR di beberapa ukuran (mis. 15/20/25 mm), laminasi doff, lalu pindai dengan 3 HP berbeda di ruangan remang seperti gedung resepsi. Catat ukuran minimum yang lolos konsisten.
  **Selesai bila:** ada ukuran QR minimum yang terbukti, tertulis, dan jadi aturan tetap di template.

- [ ] **H0.4** 👤 **Verifikasi harga alat & beli versi bertahap (Rp 9–10 juta)**
  Rencana v2 §8.5 sudah benar: tunda Cameo 5, mulai dari produk berpotongan lurus (kartu QR, kartu terima kasih, ID card) yang cukup memakai trimmer. **Yang tidak boleh ditunda: laminator** (menentukan produk awet) dan **sample kit** (menentukan dapat klien).
  ⚠️ **Jangan beli sebelum TASKS P0.1 tuntas** — yaitu sebelum ada satu order digital riil yang benar-benar menghasilkan uang. Modal Rp 9–10 juta untuk melayani funnel yang belum terbukti mengonversi adalah urutan yang salah.

---

## H1 — Jual dulu, penuhi manual (validasi sebelum rekayasa)

*Target fase ini: **5 order cetak nyata, dipenuhi tanpa satu baris kode engine.*** Yang dicari bukan efisiensi — yang dicari angka pengganti tebakan.

- [ ] **H1.1** **S&K + Kebijakan Refund untuk barang fisik** — *prasyarat hukum, kerjakan sebelum menjual*
  **Kenapa duluan:** S&K sekarang ditulis khusus produk digital (*"produk digital yang diproses otomatis"*, *"refund tidak tersedia setelah undangan diterbitkan"*). Tidak ada satu kata pun soal pengiriman, kerusakan dalam pengiriman, siapa menanggung ongkir, atau retur salah cetak. Menjual barang fisik dengan S&K ini berarti berjualan tanpa dasar — dan **Garansi Tepat Waktu menciptakan kewajiban refund 100% yang belum punya payung sama sekali**.
  **Isi yang harus ditambahkan:** ruang lingkup produk fisik · estimasi & tanggung jawab pengiriman · aturan proof (setelah disetujui pelanggan, salah ketik jadi tanggung jawab pelanggan) · ketiga garansi sebagai klausul, bukan sekadar copy pemasaran · retur/penggantian barang rusak · kuota bulanan sebagai pembatasan ketersediaan.
  **Selesai bila:** keempat halaman legal diterbitkan ulang lewat `scripts/publish-legal.py` dan ketiga garansi punya rumusan hukumnya di S&K, bukan hanya di halaman harga.

- [ ] **H1.2** **Halaman harga hybrid** *(v2 §15 butir 1 — "selesai dalam satu hari")*
  Empat paket, tiga garansi tampil **di halaman harga** (bukan disembunyikan di FAQ — v2 §11.2 menempatkan ini sebagai penyebab nomor satu closing rate rendah), Paket Grand sebagai jangkar, dan **angka penghematan paket vs à la carte ditampilkan eksplisit** (Rp 625.000).
  Bahasanya mengikuti v2 §5.9: jual hasil, bukan gramatur. Spesifikasi teknis tetap ada, tapi di bawah sebagai bukti.
  **Catatan implementasi:** halaman ini bisa dibangun dengan pola yang sudah ada (`page-katalog.php` + `katalog.css`, token tema-01) — bukan pekerjaan dari nol.

- [ ] **H1.3** **Penawaran upsell pasca-bayar — versi manual dulu**
  **Kenapa manual:** membangun flow upsell otomatis di checkout WooCommerce butuh kerja nyata, sementara yang diuji adalah **apakah orang mau membeli sama sekali**. Untuk 5 order pertama, kirim penawaran lewat WA setelah pembayaran digital berhasil — WF-01 sudah mengirim pesan ke customer di titik itu, tinggal tambah satu blok pesan.
  Pesan harus memuat kalimat kunci v2 §5.8: **"Data Anda sudah lengkap. Tinggal pilih jumlah."**
  **Yang diukur:** berapa yang membalas, berapa yang membeli, keberatan apa yang muncul. Ini data yang menentukan seluruh desain flow otomatis nanti.

- [ ] **H1.4** 👤 **Penuhi 5 order pertama secara manual & catat waktunya**
  Desain di Inkscape/Silhouette Studio dari data yang **sudah ada di Google Sheet**. Untuk tiap order catat: waktu desain · waktu cetak · waktu potong & finishing · waktu packing · bahan terpakai · yang gagal/terbuang.
  **Kenapa ini task paling berharga di seluruh dokumen:** menggantikan tebakan "4 jam per order" dengan angka nyata. Kalau ternyata 7 jam, marjin per jam turun dari Rp 675rb ke Rp 385rb — dan seluruh tangga harga v2 perlu dihitung ulang **sebelum** ada engine yang terlanjur dibangun di atas angka salah.
  **Selesai bila:** 5 order terkirim tepat waktu, dan ada tabel waktu nyata per tahap.

- [ ] **H1.5** 👤 **Dekati 5 vendor pertama** *(v2 §6.4 — paralel, tidak memakan kapasitas produksi)*
  Tiga event digital gratis ditukar testimoni tertulis + izin memakai nama. Sudut penawaran v2 §6.4: *"undangannya tampil dengan nama Anda, dan Anda ambil marjinnya."*
  Bisa dimulai **sekarang** — sisi digital tidak menyentuh kapasitas cetak sama sekali. Tapi selesaikan H0.2 dulu supaya tidak menjanjikan sesuatu yang bertabrakan dengan program reseller.

- [ ] **H1.6** **Protokol uji harga** *(v2 §11)*
  10 prospek berikutnya di harga penuh, tanpa mencampur harga lama. Yang diukur **closing rate, bukan komentar**. Batas keputusan sudah ditetapkan di v2 §11.1 — patuhi, jangan dirasionalisasi setelah melihat data.
  **Selesai bila:** 10 prospek terkumpul dan keputusan harga diambil sesuai tabel batas.

---

## H2 — Engine cetak (baru dibangun setelah H1 memberi angka)

*Gerbang masuk fase ini: **≥ 5 order terpenuhi, waktu produksi nyata terukur, closing rate terkunci.*** Sebelum itu, jangan mulai.

- [ ] **H2.1** **Snapshot data cetak** *(v2 §7.1 — fondasi semua yang lain)*
  Saat order cetak dikonfirmasi, bekukan data undangan jadi snapshot bernomor versi; seluruh produksi membaca snapshot, bukan data live. Pelanggan yang mengedit setelahnya mendapat peringatan bahwa perubahan tidak berlaku untuk cetakan yang sudah diproses.
  **Cocok dengan yang sudah ada:** meta undangan sudah terstruktur rapi di CPT `undangan`. Snapshot cukup berupa JSON beku + hash, disimpan sebagai meta order.

- [ ] **H2.2** **Aturan validasi wajib** *(v2 §7.4)* — pasang di **form**, bukan di proof
  Hari vs tanggal harus cocok (kesalahan paling sering & paling memalukan di undangan Indonesia) · batas panjang karakter per field · resolusi foto minimum ±650×1000px ditolak **di titik upload** · QR error correction level H + quiet zone + arahkan ke short URL sendiri agar slug bisa diubah tanpa cetak ulang · pembulatan kuantitas ("tambah 9 pcs gratis" saat 90→99 sama-sama 11 lembar).
  Sebagian bisa dipasang lebih awal dan murah — validasi hari-vs-tanggal layak masuk form isi data digital **sekarang juga**, karena kesalahan itu sama memalukannya di undangan digital.

- [ ] **H2.3** **Engine render: SVG template → PDF** *(v2 §7.2 butir 4)*
  Template SVG diisi data snapshot, dirender via Inkscape/librsvg CLI, teks tetap vektor.
  ⚠️ **Arsitektur:** engine ini **tidak bisa tinggal di Hostinger** — shared hosting tidak mengizinkan memasang Inkscape/librsvg. Tempatnya **di VPS bersama n8n**, dipanggil sebagai langkah workflow. Konsekuensi lain: PDF siap cetak berisi data pribadi pelanggan → butuh aturan retensi & akses, sejajar dengan yang sudah berlaku di Kebijakan Privasi.
  **Perkiraan jujur:** 2–3 minggu kerja fokus untuk H2.3–H2.5 sebagai satu kesatuan. Imposition dengan bleed/gutter/registration mark adalah bagian paling fiddly.

- [ ] **H2.4** **Imposition + cut file** *(v2 §7.2 butir 5–6)*
  Susun N kartu di area cetak efektif ±190×270mm, bleed 3mm per kartu, gutter, registration mark 4 sudut. Cut file sebagai layer terpisah (contour path + crease path untuk produk lipat).
  **Batas yang harus disadari sejak awal** *(v2 §7.5)*: Silhouette Studio tidak punya API/CLI. Otomasi berhenti di PDF + cut file; impor ke Studio dan operasional mesin tetap manual. Jangan bangun ekspektasi "sepenuhnya otomatis" — yang realistis adalah **waktu desain jadi nol menit**.

- [ ] **H2.5** **Proof + persetujuan pelanggan** *(v2 §7.2 butir 3)*
  Render preview, kirim, wajib disetujui sebelum masuk antrean. **Simpan timestamp persetujuan + hash file yang disetujui.** Tanpa tahap ini setiap typo jadi biaya perusahaan; dengan tahap ini, pembagian tanggung jawab di S&K (H1.1) punya bukti.

- [ ] **H2.6** **Deadline mundur + kuota di checkout** *(v2 §7.2 butir 2, §5.7)*
  Tanggal acara − 21 hari = batas kirim internal; order yang tidak mungkin dikejar **ditolak otomatis di checkout**, bukan dinegosiasikan belakangan. Buffer 7 hari antara deadline internal (H-21) dan janji ke pelanggan (H-14) itulah yang membiayai Garansi Tepat Waktu.
  Tampilkan sisa slot secara jujur: *"Slot Desember 2026: tersisa 6."*
  ⚠️ **Kuota harus per bulan musim, bukan rata.** Pernikahan Indonesia menumpuk di bulan tertentu; kapasitas 20/bulan yang laku hanya 5 bulan setahun berarti kapasitas tahunan ±100 order, bukan 240. Proyeksi pendapatan yang memakai 20×12 akan meleset jauh.

- [ ] **H2.7** **Antrean produksi** *(v2 §7.2 butir 7)*
  Diurutkan berdasarkan **deadline, bukan tanggal order**; batch dikelompokkan berdasarkan **bahan & finishing, bukan per pelanggan**.

- [ ] **H2.8** **Flow upsell otomatis di checkout** — menggantikan H1.3 manual
  Baru dibangun setelah H1.3 memberi tahu keberatan apa yang sebenarnya muncul dan paket mana yang benar-benar dipilih orang.

---

## H3 — Vendor & white-label (pendapatan berulang)

- [ ] **H3.1** **Tiga tingkat vendor sebagai produk** *(v2 §6.2)* — Per-event Rp 349rb · Starter Rp 990rb/bln · Pro Rp 2,9jt/bln.
  Prinsipnya: **langganan menjual akses digital (kapasitas tak terbatas); produk fisik dibeli terpisah berdiskon dan tetap dihitung terhadap kuota yang sama.** Ini yang memperbaiki kontradiksi v1.
  Butuh langganan berulang di WooCommerce — cek apakah Duitku mendukung penagihan berulang, atau tagih manual per bulan dulu.

- [ ] **H3.2** **White-label + subdomain vendor** — pekerjaan paling besar di H3. Undangan tampil dengan merek vendor. Sistem tema bertoken yang sudah ada (`undangan_get_temas()` + custom properties per tema) adalah fondasi yang tepat; yang perlu ditambah adalah lapisan identitas per vendor.

- [ ] **H3.3** **Prioritas antrean vendor Pro** — bernilai tinggi justru karena kuota terbatas, dan biayanya nol.

---

## Metrik pengendali *(v2 §12 — urutannya penting)*

1. **Marjin per jam produksi** — target ≥ Rp 600.000/jam. Setiap keputusan produk & harga diuji ke angka ini.
2. **Closing rate di harga baru** — menentukan harga dikunci, dinaikkan, atau diturunkan.
3. **Attach rate** — penting, tapi nomor tiga: attach rate 80% tidak berguna kalau kapasitas hanya 20 order.
4. **Distribusi paket** — apakah Paket Resepsi benar-benar paling laku.
5. **Tingkat reprint** — dan karena kesalahan siapa. Ini biaya langsung dari garansi.
6. **Pendapatan berulang vendor** — target Rp 10 juta/bulan pada bulan 6.

---

## Risiko yang perlu diawasi lebih ketat dari yang tertulis di rencana

- **Eksposur garansi terkonsentrasi di musim ramai.** v2 §5.6 memperkirakan 5% order gagal memenuhi Garansi Tepat Waktu. Tapi kegagalan tidak acak — ia berkorelasi dengan bulan tersibuk, persis saat kapasitas paling tertekan. Perkiraan 5% kemungkinan optimistis tepat ketika biayanya paling menyakitkan. Mitigasi: kuota lebih ketat di bulan puncak.
- **Mekanisme refund Rp 2,9 juta lewat Duitku belum diperiksa.** Garansi Tepat Waktu menjanjikan refund 100%. Perlu dipastikan: apakah kanal pembayaran yang dipakai mendukung refund, berapa lama, dan siapa menanggung fee kanalnya. Jangan sampai garansi dijanjikan sebelum jalurnya ada.
- **Musim menumpuk, bukan terdiversifikasi** *(v2 §13.2)* — digital dan cetak ramai di bulan yang sama dan sepi di bulan yang sama. Ini konsentrasi risiko. Siapkan kas untuk bulan sepi.
- **Tinta dye L8050 tidak tahan air dan rentan pudar** — laminasi bukan opsi tambahan melainkan keharusan; pada kertas uncoated seperti kraft hasilnya kusam.

---

## Tiga hal yang bisa dikerjakan minggu ini *(v2 §15, disesuaikan urutan)*

1. **Jawab H0.1 & H0.2** — dua keputusan, tidak butuh kode maupun modal, tapi memblokir semua yang lain.
2. **Uji fisik QR (H0.3)** — sampai lolos. Sebelum lolos, Garansi QR Terbaca tidak boleh dipasang.
3. **Telepon lima WO (H1.5)** — nol biaya marjinal, nol pemakaian kapasitas produksi.

Perhatikan: **tidak satu pun dari ketiganya membutuhkan modal alat atau baris kode.**
