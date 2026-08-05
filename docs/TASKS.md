# TASKS — hariH · Platform Undangan Hybrid

**Sumber:** [blueprint teknis](./blueprint-undangan-digital.md) + Rencana Bisnis Hybrid v2.0 · **Status:** aktif · **Ditulis ulang:** 2026-08-05

> Menggantikan dua dokumen sebelumnya (`TASKS.md` jalur digital + `TASKS-hybrid.md`). Hybrid bukan lagi cabang — **hybrid adalah rencananya sekarang**. Riwayat rinci ada di git log.

**Cara pakai:** centang `- [x]` saat selesai. ID (`F1.6`) stabil. Anotasi `(eks-P0.1)` menunjuk ID lama yang masih dirujuk komentar kode & `n8n/workflows/README.md`. **👤** = butuh tangan owner. **🔒** = gerbang: fase berikutnya tidak boleh dimulai sebelum ini lolos.

---

## Prinsip yang mengatur seluruh urutan

> **1. Bukti pasar mendahului modal.** Order uji ke diri sendiri membuktikan kabel tersambung, bukan bahwa ada yang mau membeli. Tidak ada rupiah keluar untuk alat sebelum ada pembeli asing yang membayar.
>
> **2. Jual dan penuhi manual dulu, bangun mesin belakangan.** Attach rate, waktu produksi 4 jam, dan kesediaan bayar Rp 2,9 juta semuanya masih tebakan (Rencana Bisnis §13.3). Lima order pertama disubkontrakkan; mesin dibeli dari laba, bukan dari tabungan.
>
> **3. Jangan bongkar yang belum perlu dibongkar.** Penjualan manual tidak melewati WooCommerce maupun n8n, jadi seluruh pembedahan checkout menunggu sampai harga terbukti laku.

---

## Kondisi saat ini (diverifikasi langsung 2026-08-05)

**Platform digital hidup dan terbukti end-to-end.** Checkout → pembayaran → undangan terbit otomatis → delivery WA+email, berjalan sendiri sejak 22 Juli. 8 workflow aktif, backup mingguan jalan, smoke test `scripts/cek-live.sh` 21/21 hijau, produksi bersih dari data uji (hanya 3 undangan demo).

**Sudah selesai** *(ringkas — rinciannya di git log)*: infrastruktur & hardening · 3 tema + demo per tema · form isi data + kompresi foto · 8 workflow otomasi · halaman legal · masa aktif otomatis · aset visual berlisensi + kartu OG per tema · logo · pustaka musik 3 track · SEO dasar · GA4 · backup repo ke GitHub private · QA formal yang menemukan 3 cacat live (countdown tidak pernah jalan, atribut `hidden` dikalahkan CSS, daftar ucapan ter-cache 7 hari).

**2026-08-06 — evaluasi desain eksternal diterapkan penuh** *(HARIH_VERSION 1.2.0)*: salam islami + QS. Ar-Rum 21 + tanggal Hijriah (Intl, sisi klien) · RSVP diperluas (jumlah tamu, sesi akad/resepsi/keduanya, WA opsional — **WA disimpan untuk mempelai, tidak pernah keluar di API publik**) · dinding ucapan scrollable · alamat kado + salin · urutan anak + tautan Instagram · dress code ber-swatch · turut mengundang · rundown per jam · tombol Apple/.ics · judul lagu di penutup & tombol musik · catatan maaf nama/gelar · embed YouTube → facade thumbnail (klik baru memuat iframe) · kontras gate + overflow nama panjang + WCAG `--c-ink-soft` tema-03 dibereskan. Semua toggle-able per undangan (`salam_islami`, field opsional). Terverifikasi visual + fungsional live, smoke 21/21.

**2026-08-05 — perbaikan positioning:** jangkar "Mulai Rp 99 ribu" dibuang dari **lima** lokasi (title, meta description, og:description, hero, **dan kartu OG** — yang terakhir ter-render ke dalam gambar yang muncul di preview WhatsApp) · FAQ berhenti mengajari pelanggan membuat kartu QR sendiri · pertanyaan "Perkiraan jumlah tamu?" dipasang sebelum tabel harga, di atas 200 tamu paket Hemat disembunyikan.

| Sehat ✓ | Bermasalah / belum ✗ |
|---|---|
| Katalog, legal, landing reseller, 3 demo → 200 | **Belum ada satu pun pembeli asing** *(F0.3)* |
| Musik 3 track + whitelist `musik_url` | Duitku production menunggu approval *(F0.1)* |
| Masa aktif otomatis, demo dikecualikan | Checkout **menolak barang fisik** secara arsitektur *(F3.4–F3.6)* |
| GA4 live; `/u/*` & `/isi-data/` sengaja tidak dilacak | WF-01 salah deteksi paket hybrid → bug tier *(F3.2)* |
| Kode ter-backup + **restore backup terbukti berhasil** ✓ *(F0.5)* | — |
| Docker terpin · rahasia di password manager | Monitor n8n hanya hidup di dalam n8n *(ditunda)* |
| `xmlrpc` 403 · port WAHA tertutup · REST 401 | QA perangkat riil belum dijalankan *(F0.4)* |

**Akses:** Hostinger `ssh -p 65002 u803921702@147.93.80.20` (`domains/harih.id/public_html`) · VPS `ssh root@31.97.50.197` (`/opt/harih`) · rahasia: `vps/.env` + `vps/google-sa.json` (lokal, gitignored) = cermin server · aksi owner: [`panduan-manual.md`](./panduan-manual.md) · operasional: [`runbook.md`](./runbook.md) · import n8n: [`../n8n/workflows/README.md`](../n8n/workflows/README.md).

---

## Keputusan yang sudah terkunci

| Topik | Keputusan |
|---|---|
| Tangga digital | Pertahankan tiga tingkat — yang rusak copy-nya, bukan tier-nya |
| Attach rate | Diukur **per tingkat**, tidak pernah digabung. Hipotesis: Hemat 2–5% · Favorit 10–15% · Premium 30–40% |
| Komisi | Digital tetap **30%**. Fisik **rupiah tetap**: Rp 150rb (Hormat) · Rp 300rb (Resepsi) · Rp 500rb (Grand) |
| Reseller vs vendor | Satu orang **tidak boleh** jadi dua-duanya. Berlangganan vendor → akun reseller berhenti |
| Katalog | Tetap menjual **paket penuh** — pembeli resepsi gedung bisa langsung beli tanpa lewat digital |
| Upsell pasca-bayar | **Tiga SKU upgrade berharga tetap.** Kredit berlaku **14 hari dengan hitung mundur tampil** |
| À la carte | **Dilarang muncul di halaman upsell.** Tempatnya di katalog produk satuan |
| Pengiriman | **Satu metode: gratis se-Indonesia.** Rencana zona dibatalkan |
| Ongkir di struktur biaya | **Rp 150.000** (naik dari 50rb) → marjin Paket Resepsi **Rp 2,6 juta** |
| Resi | Wajib dicatat di sistem; pakai layanan ber-SLA |
| Minimum à la carte | **Rp 1.000.000 per transaksi**; minimum per produk tetap berlaku di atasnya |

**Konsekuensi angka yang perlu diingat:** pada marjin Rp 2,6 juta dan target ≥ Rp 600rb/jam, **batas waktu produksi adalah 4 jam 20 menit**. Lewat dari itu metrik pengendali gagal — itulah yang membuat pengukuran waktu nyata di F1.7 menentukan apakah harga Rp 2,9 juta bertahan.

---

## F0 — Bukti pasar 🔒 *gerbang keras untuk semua fase berikutnya*

- [~] **F0.1** 👤 **Duitku production** *(eks-P0.1)* — **diajukan 2026-08-04, menunggu approval**
  Blocker satu-satunya untuk menerima uang. Akun tetap di email pribadi owner (Duitku memakai struktur 1 akun → banyak project; akun perorangan secara hukum milik orangnya, dan email ber-domain produk berbahaya saat akun ini nanti menaungi SaaS lain). Nama Usaha `hariH`, URL `https://harih.id`.
  **Setelah approval:** ganti kredensial plugin ke mode Production → beri tahu saya → lanjut F0.2.

- [ ] **F0.2** **Uji order riil Rp 10.000** *(eks-P0.1 lanjutan)*
  Produk uji tersembunyi, dipesan dari HP, verifikasi undangan sampai di WA < 15 menit, lalu produk ujinya dihapus.
  ⚠️ **Ini bukti kabel tersambung, BUKAN bukti pasar.** Jangan diperlakukan sebagai validasi — order ke diri sendiri tidak memberi tahu apa pun tentang kesediaan orang membayar.
  **Perlu diperhatikan:** lihat nama merchant yang **benar-benar tampil ke pembayar** di layar kanal pembayaran. Sebagian kanal menampilkan nama pemilik rekening, bukan nama usaha.

- [ ] **F0.3** 🔒 👤 **10 penjualan digital berbayar dari orang yang tidak dikenal owner**
  **Ini gerbang yang sesungguhnya.** Teman, keluarga, dan diri sendiri tidak dihitung — yang dicari adalah bukti funnel mengonversi orang asing.
  **Yang dicatat per penjualan:** tier yang dibeli · dari mana datangnya (organik/reseller/WA) · apakah bertanya-tanya dulu atau langsung bayar.
  **Selesai bila:** 10 pembayaran dari orang asing, dan distribusi tier-nya diketahui — itu basis attach rate per tingkat nanti.
  > **Sebelum ini tercapai: nol rupiah untuk alat, nol baris kode cetak.**

- [ ] **F0.4** 👤 **QA perangkat riil** *(eks-P2.2)*
  iPhone Safari & Android Chrome: musik mulai setelah tap · countdown berjalan (baru diperbaiki) · tombol salin rekening · upload foto HEIC · preview share WA. Checklist lengkap di [`panduan-manual.md`](./panduan-manual.md) langkah 5.
  ⚠️ WA meng-cache preview per URL — uji dengan `?x=1` supaya dianggap URL baru.

- [x] **F0.5** **Uji restore backup 1×** *(eks-P1.2)* → **SELESAI 2026-08-05**
  Backup sudah jalan 3× dan integritasnya terverifikasi, tapi belum pernah dibuktikan bisa dipulihkan — backup yang tak pernah diuji dianggap tidak ada.
  **Cara uji:** MySQL container sementara di VPS, **produksi tidak tersentuh** sama sekali (container dihapus setelahnya, image ikut dibersihkan).
  **Hasil dump 2026-08-01:** 56 tabel · `siteurl` = `https://harih.id` · 39 post · 3 undangan publish · 3 produk · 2 order HPOS · 2 user — persis kondisi produksi tanggal itu. Arsip WAHA terbaca (1.659 entri, `webjs/default/` utuh) · arsip n8n terbaca (`database.sqlite` ada) · export workflow JSON valid, 8 workflow lengkap dengan jumlah node benar.
  **Temuan sampingan yang menenangkan:** backup mingguan meninggalkan jendela sampai 7 hari, tapi hampir semua isi yang hilang **bisa dibangun ulang dari repo** (halaman legal, undangan demo, produk, aset — semuanya punya generator). Yang benar-benar hanya ada di backup: data order & undangan pelanggan riil. Disiplin "semua reproducible dari repo" terbayar persis di sini.
  Prosedur mengulang uji dicatat di [`runbook.md`](./runbook.md) §9.

---

## F1 — Validasi hybrid: tanpa mesin, tanpa bedah checkout

*Target fase ini: **5 order cetak nyata, disubkontrakkan, dengan biaya & waktu tercatat.*** Yang dicari bukan efisiensi — yang dicari angka pengganti tebakan.

- [ ] **F1.1** 👤 **Uji fisik QR: cetak → laminasi doff → pindai di ruangan remang**
  **Gerbang untuk Garansi QR Terbaca** — garansi itu tidak boleh dipasang di halaman harga sebelum uji ini lolos. Laminasi doff menurunkan kontras dan bisa membuat QR gagal terbaca, persis skenario yang dijanjikan garansi.
  **Langkah:** cetak QR di beberapa ukuran (15/20/25 mm), laminasi doff, pindai dengan 3 HP berbeda di ruangan remang seperti gedung resepsi.
  **Catatan:** ujinya memakai **hasil percetakan subkontrak** (F1.2), bukan mesin sendiri — itu yang akan benar-benar dikirim ke pelanggan di fase ini.
  **Selesai bila:** ada ukuran QR minimum yang terbukti dan jadi aturan tetap.

- [ ] **F1.2** 👤 **Cari & uji 2–3 percetakan subkontrak**
  **Kenapa subkontrak dulu:** lima order pertama tidak butuh mesin. Marjin turun (perkiraan Rp 1,6–1,8 juta vs Rp 2,6 juta produksi sendiri) tapi **masih di atas marjin rencana v1 yang Rp 1,12 juta** — dan modal Rp 9–10 juta tidak keluar sebelum ada yang membayar.
  **Langkah:** minta kuotasi untuk isi Paket Resepsi (150 kartu QR art carton 260gsm laminasi doff · 200 label souvenir · 100 kartu terima kasih · 100 stiker segel), minta **sample fisik**, cek SLA & konsistensi warna.
  ⚠️ **Angka marjin Rp 1,6–1,8 juta itu estimasi saya, bukan kuotasi.** Wajib dikonfirmasi dengan angka nyata sebelum harga dikunci.
  **Selesai bila:** ada 1 percetakan terpilih dengan harga tertulis, sample yang lolos F1.1, dan kesepakatan waktu kerja.

- [ ] **F1.3** 👤 **Tetapkan tiga harga SKU upgrade + besaran kredit**
  Katalog menjual paket penuh; halaman upsell menjual **tiga SKU upgrade berharga tetap** untuk yang sudah membeli digital.
  **Yang perlu diputuskan:** besaran kredit digital. Cara paling sederhana — kredit tetap Rp 299.000 untuk semua tier, sehingga harga upgrade = harga paket − 299rb, satu angka per SKU tanpa matriks. Konsekuensinya pembeli Hemat (bayar 99rb) mendapat kredit Rp 200rb lebih besar dari yang ia bayar; pada paket Rp 2,9 juta itu derau, dan justru mendorong pembeli Hemat naik kelas. **Perlu keputusan sadar, bukan diasumsikan.**

- [ ] **F1.4** **S&K + Refund + Privasi untuk barang fisik** — *prasyarat hukum, sebelum menjual meski manual*
  S&K sekarang ditulis khusus produk digital (*"produk digital yang diproses otomatis"*, *"refund tidak tersedia setelah undangan diterbitkan"*). Tidak ada satu kata pun soal pengiriman, kerusakan di jalan, ongkir, atau retur salah cetak — sementara **Garansi Tepat Waktu menciptakan kewajiban refund 100% yang belum punya payung sama sekali**.
  **Yang ditambahkan:** ruang lingkup produk fisik · pengiriman gratis se-Indonesia + tanggung jawab & resi · aturan proof (setelah disetujui pelanggan, salah ketik jadi tanggung jawab pelanggan) · **ketiga garansi sebagai klausul**, bukan sekadar copy pemasaran · retur/penggantian barang rusak · kuota bulanan sebagai pembatasan ketersediaan.
  Terbit lewat `scripts/publish-legal.py` (repo = sumber kebenaran halaman legal).

- [ ] **F1.5** **Halaman harga hybrid — statis, CTA WhatsApp**
  Empat paket · **tiga garansi tampil di halaman harga**, bukan disembunyikan di FAQ (Rencana Bisnis §11.2 menempatkan ini sebagai penyebab nomor satu closing rate rendah) · Paket Grand sebagai jangkar · **angka penghematan paket vs à la carte ditampilkan eksplisit**.
  Bahasa mengikuti §5.9: jual hasil, bukan gramatur. Spesifikasi teknis di bawah sebagai bukti.
  **Belum ada produk WooCommerce di fase ini** — tombolnya mengarah ke WhatsApp. Pola sudah ada: `page-katalog.php` + `katalog.css`, token tema-01.
  ⚠️ Klaim "kartu fisik" baru boleh muncul setelah F1.1 lolos dan ada percetakan terpilih. Menjanjikannya lebih awal mengulang kesalahan musik — tercantum di halaman harga berbulan-bulan sebelum barangnya ada.

- [ ] **F1.6** 👤 **Jual & penuhi 5 order pertama sepenuhnya manual**
  Invoice via WhatsApp · transfer manual · alamat & resi dicatat di Google Sheet · desain dari data yang **sudah ada di sheet** · cetak disubkontrakkan.
  **Kenapa manual:** tidak melewati WooCommerce maupun WF-01, sehingga ketiga bug otomasi (F3.2, F3.3) tidak bisa terpicu — dan tidak ada satu jam pun dihabiskan membangun mesin untuk harga yang belum terbukti.

- [ ] **F1.7** **Catat waktu & biaya nyata per order**
  Per tahap: desain · koordinasi percetakan · QC & uji pindai · packing · pengiriman. Plus biaya nyata: cetak, packaging, ongkir.
  **Task paling berharga di seluruh dokumen.** Menggantikan tebakan "4 jam". Kalau ternyata di atas **4 jam 20 menit** pada produksi sendiri nanti, marjin per jam jatuh di bawah target dan **seluruh tangga harga perlu dihitung ulang** — jauh lebih murah ketahuan sekarang daripada setelah mesin dibeli dan engine dibangun.

- [ ] **F1.8** **Protokol uji harga** *(Rencana Bisnis §11)*
  10 prospek di harga penuh, tanpa mencampur harga lama. Yang diukur **closing rate, bukan komentar** — orang yang bilang "mahal" tapi tetap membeli adalah pembeli.
  Batas keputusan sudah ditetapkan di muka: >30% naikkan 20% · 15–30% kunci · 8–15% tahan & perkuat bukti sosial · <8% turunkan ke Rp 2,2 jt.
  ⚠️ **Jangan menurunkan harga setelah dua-tiga penolakan.** Sepuluh prospek adalah sampel minimum; menurunkan lebih awal membuang informasi dan sulit dinaikkan kembali. Sebelum menurunkan, periksa dulu lima hal di §11.2 — empat di antaranya gratis.

- [ ] **F1.9** 👤 **Dekati 5 vendor pertama** *(Rencana Bisnis §6.4)*
  Tiga event digital gratis ditukar testimoni tertulis + izin memakai nama. Sudut penawaran: *"undangannya tampil dengan nama Anda, dan Anda ambil marjinnya."*
  Bisa berjalan **paralel sejak sekarang** — sisi digital tidak menyentuh kapasitas produksi sama sekali. Pastikan menjelaskan aturan reseller-vs-vendor (tidak boleh dua-duanya) sejak percakapan pertama.

> **Gerbang keluar F1:** 5 order terkirim tepat waktu · biaya & waktu nyata terukur · closing rate terkunci.

---

## F2 — Beli alat, dibiayai laba 🔒

- [ ] **F2.1** 👤 **Laminator + sample kit lebih dulu** — Rp 1–2 juta
  Dua hal yang tidak boleh ditunda menurut Rencana Bisnis §8.5: laminator menentukan produk awet atau tidak, sample kit menentukan dapat klien atau tidak. Keduanya berguna bahkan selagi masih subkontrak.

- [ ] **F2.2** 👤 **Cameo 5 + printer dari laba order keempat**
  Bukan dari tabungan. Verifikasi ulang harga sebelum membeli (Rencana Bisnis §8.1 mencatat varian jebakan: listing Cameo Rp 3,6 jt bukan unit lengkap; Epson L8050 ada listing tanpa tinta; garansi hangus bila tidak didaftarkan di my.epson.co.id).
  > **Gerbang masuk F2: ≥ 4 order cetak sudah terbayar.**

---

## F3 — Masukkan ke WooCommerce *baru setelah harga terbukti laku*

*Semua penghalang arsitektur yang ditemukan pada audit 2026-08-05 ditangani di fase ini.*

- [ ] **F3.1** **Kategori produk `digital` & `cetak` + batasi kupon `RES-` ke digital**
  **Wajib sebelum produk cetak pertama masuk WooCommerce.** Kupon `RES-` yang sudah beredar mengikat 30% ke **seluruh nilai order**; begitu produk cetak jadi produk biasa, kupon itu otomatis berlaku ke sana — Rp 870.000 pada order Rp 2,9 juta, bocor tanpa pernah diputuskan.
  Saat ini di server hanya ada satu kategori (`Uncategorized`, 3 produk), jadi tidak ada tempat menggantungkan pembatasan. Kategori dibuat lebih dulu.

- [ ] **F3.2** **WF-01: kenali jenis order** — *menutup bug tier*
  WF-01 mendeteksi paket dengan `['hemat','favorit','premium'].find(p => namaItem.includes(p))`. Nama paket hybrid — *Hormat, Resepsi, Grand* — tidak memuat satu pun kata itu, jadi `paket` = `''` dan WF-02 jatuh ke fallback teraman **`hemat`**. Akibatnya **pembeli Paket Resepsi Rp 2,9 juta menerima undangan paket Hemat**: tanpa galeri, tanpa amplop, masa aktif H+7.
  Sekaligus: order cetak murni (à la carte) tidak boleh dikirimi link form isi data dan tidak boleh membuat baris `orders` baru — pelanggannya sudah punya undangan.

- [ ] **F3.3** **WF-01: komisi per jenis produk**
  Sekarang `dasarKomisi × 0.3` tanpa syarat. Ubah: 30% hanya untuk line item **digital**; produk fisik memakai tabel rupiah tetap (150/300/500rb).

- [ ] **F3.4** **`sold_individually` jadi kondisional per produk**
  Sekarang `add_filter('woocommerce_is_sold_individually', '__return_true')` berlaku **global**. Kuantitas terkunci di 1 — à la carte 100 pcs tidak mungkin dipesan. Digital tetap satuan; produk cetak bebas kuantitas.

- [ ] **F3.5** **Pengosongan cart jadi kondisional**
  `woocommerce_add_to_cart_validation` sekarang **mengosongkan cart** setiap penambahan produk. Aturan "1 order = 1 paket" hanya boleh berlaku antar produk digital, supaya digital + cetak bisa berada di satu keranjang.

- [ ] **F3.6** **Alamat + `shipping_*` muncul hanya bila cart memuat produk fisik**
  `billing_address_1/2`, `city`, `state`, `postcode`, `country` semuanya di-`unset` sekarang — **tidak ada alamat kirim di mana pun**. Kembalikan **hanya** saat ada barang fisik di keranjang, supaya checkout digital tetap seramping sekarang (itu yang menjaga konversi mobile).

- [ ] **F3.7** **Satu metode pengiriman: gratis se-Indonesia**
  Di server sekarang hanya ada zona fallback dan `ship_to_countries` kosong. Rencana zona **dibatalkan** — satu metode free shipping, dan "gratis ongkir se-Indonesia" dipakai sebagai nilai jual di halaman harga.

- [ ] **F3.8** **Field & pencatatan nomor resi**
  Wajib per keputusan owner. Disimpan di order + ikut ke sheet, dan dikirimkan ke pelanggan saat paket berangkat.

- [ ] **F3.9** **Produk cetak + 3 SKU upgrade di WooCommerce** — harga dari F1.3

- [ ] **F3.10** **Halaman upsell pasca-bayar**
  Bertoken seperti `/isi-data/` · **hitung mundur kredit 14 hari tampil** — tanpa batas waktu tidak ada alasan memutuskan hari ini · **à la carte dilarang muncul di halaman ini**.
  Kedaluwarsa ditegakkan server-side, bukan hanya disembunyikan di tampilan.

- [ ] **F3.11** **Katalog produk satuan (à la carte)** — minimum **Rp 1.000.000/transaksi**, minimum per produk tetap berlaku di atasnya. Harga per unit sengaja tinggi: fungsinya pembanding yang membuat paket terlihat murah.

> **Gerbang keluar F3:** order uji berisi produk fisik lolos checkout dengan alamat · WF-01 tidak salah kirim link form · komisi terhitung benar · kupon `RES-` tidak menyentuh produk cetak.

---

## F4 — Otomasi cetak *setelah volume membenarkannya*

*Perkiraan jujur: F4.3–F4.5 adalah 2–3 minggu kerja fokus sebagai satu kesatuan. Imposition dengan bleed/gutter/registration mark adalah bagian paling fiddly.*

- [ ] **F4.1** **Snapshot beku bernomor versi** — fondasi semua yang lain. Saat order cetak dikonfirmasi, data dibekukan; seluruh produksi membaca snapshot, bukan data live. Pelanggan yang mengedit setelahnya diberi peringatan bahwa perubahan tidak berlaku untuk cetakan yang sudah diproses. Meta undangan sudah terstruktur rapi — snapshot cukup JSON beku + hash sebagai meta order.

- [ ] **F4.2** **Validasi wajib di FORM, bukan di proof** — hari vs tanggal harus cocok — **hanya relevan untuk template cetak**. Diperiksa 2026-08-05: di sisi digital ini mustahil terjadi, karena nama hari **diturunkan** dari tanggal lewat `wp_date('l, j F Y')` dan formnya memakai `<input type="date">`, bukan teks bebas. Baru jadi risiko bila template cetak menerima tanggal yang diketik manual · batas panjang field · resolusi foto minimum ±650×1000px ditolak di titik upload · QR error correction H + quiet zone + short URL sendiri agar slug bisa diubah tanpa cetak ulang · pembulatan kuantitas ("tambah 9 pcs gratis" saat 90→99 sama-sama 11 lembar).

- [ ] **F4.3** **Engine render SVG → PDF** — template diisi data snapshot, dirender via Inkscape/librsvg CLI, teks tetap vektor.
  ⚠️ **Wajib di VPS.** Hostinger shared hosting tidak bisa memasang Inkscape/librsvg. Konsekuensi: PDF siap cetak berisi data pribadi pelanggan → butuh aturan retensi & akses, sejajar dengan Kebijakan Privasi.
  Warna **sRGB**, bukan CMYK — driver Epson desktop adalah driver RGB; file CMYK justru dikonversi balik dan warnanya kusam. CMYK hanya untuk order yang disubkontrakkan.

- [ ] **F4.4** **Imposition + cut file** — N kartu di area efektif ±190×270mm, bleed 3mm, gutter, registration mark 4 sudut; cut file sebagai layer terpisah.
  **Batas yang harus disadari:** Silhouette Studio tidak punya API/CLI. Otomasi berhenti di PDF + cut file; impor ke Studio dan operasional mesin tetap manual. Yang realistis: **waktu desain jadi nol menit**, bukan "sepenuhnya otomatis".

- [ ] **F4.5** **Proof + persetujuan ber-hash** — render preview, wajib disetujui sebelum masuk antrean, **simpan timestamp + hash file yang disetujui**. Tanpa tahap ini setiap typo jadi biaya perusahaan; dengan tahap ini, pembagian tanggung jawab di S&K punya bukti.

- [ ] **F4.6** **Deadline H-21 + kuota musiman di checkout** — order yang tidak mungkin dikejar **ditolak otomatis**, bukan dinegosiasikan belakangan. Buffer 7 hari antara deadline internal (H-21) dan janji ke pelanggan (H-14) itulah yang membiayai Garansi Tepat Waktu. Tampilkan sisa slot jujur.
  ⚠️ **Kuota per bulan musim, bukan rata.** Pernikahan Indonesia menumpuk di bulan tertentu; kapasitas 20/bulan yang laku hanya 5 bulan setahun = **±100 order setahun, bukan 240**. Proyeksi 20×12 akan meleset jauh.

- [ ] **F4.7** **Antrean produksi** — diurutkan berdasarkan **deadline, bukan tanggal order**; batch dikelompokkan berdasarkan **bahan & finishing, bukan per pelanggan**.

- [ ] **F4.8** **Upsell otomatis** menggantikan versi manual — baru dibangun setelah F3.10 memberi tahu keberatan apa yang sebenarnya muncul.

---

## F5 — Vendor & white-label (pendapatan berulang)

- [ ] **F5.1** **Tiga tingkat vendor sebagai produk** — Per-event Rp 349rb · Starter Rp 990rb/bln · Pro Rp 2,9jt/bln.
  Prinsipnya: **langganan menjual akses digital (kapasitas tak terbatas); produk fisik dibeli terpisah berdiskon dan tetap dihitung terhadap kuota yang sama.** Ini yang memperbaiki kontradiksi rencana v1 (satu vendor Pro menghabiskan seluruh kapasitas hanya dengan Rp 1,5 juta).
  Butuh langganan berulang — cek dukungan Duitku, atau tagih manual per bulan dulu.

- [ ] **F5.2** **White-label + subdomain vendor** — pekerjaan terbesar di F5. Sistem tema bertoken yang sudah ada adalah fondasinya; yang perlu ditambah lapisan identitas per vendor. *(Menyerap rencana "dashboard reseller" dari backlog lama.)*

- [ ] **F5.3** **Prioritas antrean vendor Pro** — bernilai tinggi justru karena kuota terbatas, dan biayanya nol.

---

## Metrik pengendali *(urutannya penting)*

1. **Marjin per jam produksi** — target ≥ Rp 600.000/jam. Setiap keputusan produk & harga diuji ke angka ini. Pada marjin Rp 2,6 juta, batasnya 4 jam 20 menit.
2. **Closing rate di harga baru** — menentukan harga dikunci, dinaikkan, atau diturunkan.
3. **Attach rate — dipecah per tingkat**, tidak pernah digabung. Angka campuran akan melaporkan "20%" dan menyesatkan setiap keputusan berikutnya.
4. **Distribusi paket** — apakah Paket Resepsi benar-benar paling laku. Kalau Hormat yang mendominasi, jangkar harganya kurang kuat.
5. **Tingkat reprint** — dan karena kesalahan siapa. Ini biaya langsung dari garansi.
6. **Pendapatan berulang vendor** — target Rp 10 juta/bulan pada bulan 6.

---

## Risiko yang perlu diawasi lebih ketat dari dokumen bisnis

- **Eksposur garansi terkonsentrasi di musim ramai.** Rencana memperkirakan 5% order gagal Garansi Tepat Waktu, tapi kegagalan tidak acak — ia berkorelasi dengan bulan tersibuk, persis saat kapasitas paling tertekan. Perkiraan 5% kemungkinan optimistis tepat ketika biayanya paling menyakitkan. Mitigasi: kuota lebih ketat di bulan puncak.
- **Mekanisme refund Rp 2,9 juta lewat Duitku belum diperiksa.** Garansi Tepat Waktu menjanjikan refund 100%. Pastikan kanal pembayarannya mendukung refund, berapa lama, dan siapa menanggung fee-nya — **sebelum** garansinya dipasang di halaman harga.
- **Musim menumpuk, bukan terdiversifikasi.** Digital dan cetak ramai di bulan yang sama dan sepi di bulan yang sama. Ini konsentrasi risiko. Siapkan kas untuk bulan sepi.
- **Tinta dye L8050 tidak tahan air dan rentan pudar** — laminasi keharusan, bukan opsi. Pada kertas uncoated seperti kraft hasilnya kusam.
- **Monitor n8n hidup di dalam n8n** *(eks-P1.3, ditunda atas keputusan owner)* — kalau n8n mati, WF-07 & WF-08 mati bersamanya. Spesifikasi 3 monitor UptimeRobot siap pakai di [`panduan-manual.md`](./panduan-manual.md) langkah 4.

---

## Sisa jalur digital (tetap berlaku, prioritas rendah)

- [ ] 👤 Review gaya bahasa pesan otomatis di [`copywriting-pesan.md`](./copywriting-pesan.md) *(eks-P3.2)*
- [ ] 👤 Review visual tema-02 & tema-03 di HP sebagai calon pembeli *(eks-P2.7)*
- [ ] 👤 Kebijakan nomor WA bisnis — jangan logout, pakai wajar, jangan blast ke nomor tak dikenal. Sesi terbanned = seluruh kanal delivery WA mati *(eks-P3.3)*

---

## Backlog v2

- [ ] Occasion baru dengan duplikasi tema: khitanan, aqiqah, wisuda (musiman Mei–September), e-card Lebaran
- [ ] Add-on WA blast ke daftar tamu — Rp 50rb/200 tamu
- [ ] Amplop digital ter-escrow via QRIS dengan fee platform 2–5% *(perubahan model bisnis paling bernilai)*
- [ ] Migrasi log operasional Google Sheets → Postgres *(makin diperlukan begitu order cetak, resi, dan status produksi ikut dicatat)*
- [ ] Tema premium eksklusif Premium *(sudah dijanjikan "menyusul" di deskripsi produk)*
- [ ] Arsip otomatis undangan kedaluwarsa: hapus media H+90 untuk Hemat/Favorit
- [ ] Custom domain per undangan · multi-bahasa · tema builder drag-and-drop
- [ ] *(evaluasi 2026-08-06)* **Link personal per tamu + QR check-in** — `?tamu=` sudah ada; tinggal generator massal (CSV/Sheets → daftar link) + halaman scan panitia. Satu paket dengan produk Tier B hybrid
- [ ] *(evaluasi 2026-08-06)* **Template broadcast WA** per tamu (nama tersapa otomatis) — menyusul generator link personal
- [ ] *(evaluasi 2026-08-06)* **Dashboard rekap RSVP untuk mempelai** — hitung total tamu per sesi + ekspor; nomor WA tamu hanya tampil di sini (bukan publik). *Catatan: OG image per undangan, state countdown habis, dan dinding ucapan sudah ada — pengevaluasi melihat versi lama.*

---

## Dihapus dari rencana, dengan alasan

| Dihapus | Alasan |
|---|---|
| **P2.6** Polish katalog + FAQ | Katalog dirombak untuk hybrid di F1.5. Memoles versi digital-saja = kerja terbuang |
| **P3.1** Rekrut 3 reseller | Ekonominya berubah (komisi fisik rupiah tetap) dan kupon `RES-` belum dibatasi — merekrut sekarang justru mencetak kebocoran. Digantikan F1.9: vendor dulu |
| **Rencana zona pengiriman** | Dibatalkan: satu metode gratis se-Indonesia |
| **Backlog** buku tamu QR check-in | Bukan lagi ide v2 — itu produk Tier B di rencana hybrid |
| **Backlog** dashboard reseller | Diserap F5.2 (white-label vendor) |
| **Checklist QA tambahan** | Sudah lulus di putaran QA formal 2026-08-04; yang benar-benar belum diuji dipindah ke F0.4 |

---

**Known limitation (diterima):** idempotency Google Sheets tidak atomik — dimitigasi topic Action `woocommerce_order_status_processing` + pola append-then-verify di WF-01. Sisa risiko race sangat kecil dan hilang total saat migrasi Postgres.
