# Bahan diskusi — hariH, 7 Agustus 2026

> **Untuk siapa dokumen ini.** Ditulis supaya bisa dibaca orang yang **belum pernah melihat produk atau kodenya**. Tidak ada istilah teknis yang tidak dijelaskan. Tujuannya satu: membantu memutuskan delapan hal yang tidak bisa diputuskan dari dalam kode.
>
> Tiap poin memuat: **duduk perkara · pilihan · saran saya · bukti apa yang akan menyelesaikannya**. Saran saya bukan keputusan — beberapa di antaranya bersandar pada asumsi yang belum saya uji, dan itu saya tandai.

---

## Kondisi apa adanya

hariH menjual **undangan pernikahan digital** (halaman web, Rp 99–299 ribu) dan **undangan cetak** (undangan lipat + amplop bernama tamu, Rp 1,19–5,9 juta). Keduanya dari satu kali isi data.

| | |
|---|---|
| Platform hidup sejak | 22 Juli 2026 (±2,5 minggu) |
| Pembeli | **nol** |
| Pesanan | **nol** |
| Yang sudah dibangun | 9 alur otomatis, 3 tema undangan, halaman harga, katalog, halaman legal |
| Gerbang pembayaran | Duitku, **diajukan 4 Agustus, belum disetujui** |

**Diagnosis yang saya pegang:** proyek ini **terlalu banyak dibangun, terlalu sedikit dijual.** Mesinnya siap; tidak ada yang lewat.

Sepanjang 7 Agustus saya menutup 15 cacat — termasuk satu yang membuat **setiap pembeli menerima error dan tidak pernah mendapat undangannya**, dan satu lagi yang membuat **nama tamu semua pelanggan bisa dipanen orang luar**. Itu semua memperbaiki mesin. **Tidak satu pun mendatangkan pembeli.**

---

## 1. Dari mana pembeli datang? 🔴 *pertanyaan terbesar*

**Duduk perkara.** Rencana menetapkan gerbang: **10 penjualan ke orang yang tidak dikenal** sebelum modal dikeluarkan. Tapi tidak ada satu pun rencana tentang **bagaimana orang asing menemukan hariH**. Nol dari 100+ perubahan kode menyentuh soal ini. Satu-satunya cara orang sampai ke situs hari ini adalah Ryan mengirim link secara manual.

Artinya: **Duitku bisa disetujui besok pagi dan angkanya tetap nol.** Yang kurang bukan cara menagih, melainkan orang yang ditagih.

**Pilihan:**

| | Cara | Biaya | Kecepatan |
|---|---|---|---|
| a | **Dekati 5 wedding organizer / vendor** — tawarkan undangan digital gratis untuk 3 event, ditukar testimoni + izin pakai nama | nol rupiah, butuh waktu bicara | minggu ini |
| b | **Tulis 3 artikel** yang dicari calon pengantin ("harga undangan cetak vs digital", "berapa undangan yang sebenarnya dibutuhkan") | nol rupiah, ±1 hari nulis | hasil 1–3 bulan |
| c | **Buka lapak di marketplace / direktori pernikahan** dengan harga tercantum | biaya kecil | 1–2 minggu |
| d | **Konten TikTok/IG** — demo undangan yang bergerak | nol rupiah, butuh konsisten | tidak pasti |

**Saran saya: (a) dulu, minggu ini.** Sudah tertulis di rencana, nol biaya, tidak menyentuh kapasitas produksi sama sekali — dan sudah dinyatakan bisa jalan paralel, tapi tetap tergeletak di urutan bawah. Satu WO membawa banyak pasangan sekaligus; 10 pembeli asing jadi masuk akal dalam hitungan minggu, bukan bulan.

**Yang perlu didiskusikan dengan teman:** mana yang realistis dijalankan **Ryan sendirian** sambil tetap mengurus percetakan? Kalau harus pilih satu saja, mana?

---

## 2. Mulai jualan sekarang tanpa menunggu Duitku?

**Duduk perkara.** Duitku (pemroses pembayaran) diajukan 4 Agustus, belum keluar. Selama belum, tidak ada tombol bayar otomatis.

Tapi **rencana sudah merestui cara manual** untuk pesanan cetak Rp 2,9 juta: kirim invoice lewat WhatsApp, pembeli transfer, pesanan dibuat manual. Cara yang sama **belum pernah diterapkan ke paket digital Rp 99–299 ribu** — padahal justru 10 penjualan digital itulah gerbangnya.

Secara teknis **tidak butuh kode baru sama sekali.** Transfer masuk → pesanan dibuat manual → seluruh otomasi berjalan seperti biasa.

**Pilihan:**
- **(a)** Buka jalur manual sekarang, tombol "Pesan lewat WhatsApp" di samping tombol bayar
- **(b)** Tunggu Duitku

**Saran saya: (a).** Setiap hari menunggu adalah hari tanpa data — dan yang dicari dari 10 penjualan pertama bukan uangnya, melainkan **apakah orang asing mau membayar sama sekali**, dan tier mana yang mereka pilih. Itu tidak butuh gerbang pembayaran otomatis.

**Keberatan yang jujur:** transfer manual terasa kurang meyakinkan bagi pembeli yang belum kenal, dan menambah pekerjaan tangan per pesanan. Pada 10 pesanan pertama, menurut saya itu harga yang murah.

---

## 3. Reseller: janji komisi 30% yang tidak akan kita bayar ⚠️ *sedang berjalan sekarang*

**Duduk perkara.** Halaman `/jadi-reseller/` **hidup dan menerima pendaftar hari ini**. Ia menjanjikan **komisi 30% tiap order** di tujuh tempat berbeda.

Tapi keputusan yang sudah dikunci berbunyi lain: 30% hanya untuk **digital**; untuk **cetak** komisinya rupiah tetap.

| Reseller menjual | Ia harapkan | Ia terima | Selisih |
|---|---|---|---|
| Paket Resepsi (Rp 2,9 jt) | Rp 870.000 | Rp 300.000 | **−Rp 570.000** |

Lebih cepat lagi: kode kupon reseller **ditolak** di keranjang cetak, jadi pesanan terbesar berhenti di langkah terakhir.

Ini kanal yang seluruh nilainya **kepercayaan**. Reseller pertama yang merasa dijanjikan lalu tidak dibayar akan bercerita.

**Pilihan:**
- **(a)** Koreksi klaimnya jadi *"30% untuk paket digital · Rp 150/300/500 ribu untuk paket cetak"*
- **(b)** Turunkan halamannya sampai reseller memang diinginkan

**Saran saya: (a), hari ini juga.** Perbaikannya hitungan menit dan saya bisa langsung kerjakan. Tapi ada pertanyaan yang lebih dalam di baliknya — lihat di bawah.

**Yang perlu didiskusikan:** apakah **sekarang** waktunya punya reseller sama sekali? Rencana sempat mencabut "rekrut 3 reseller" karena ekonominya berubah, tapi halamannya tetap hidup. Merekrut orang untuk menjual produk yang belum pernah terjual ke satu pun orang asing terasa terbalik urutannya. *(Catatan: satu panduan internal masih menyuruh merekrut reseller — dua dokumen saling bertentangan.)*

---

## 4. Paket Hormat: dipertahankan, dinaikkan, atau dihentikan?

**Duduk perkara.** Tiga paket cetak, dan marjin per jam kerjanya **jauh berbeda**:

| Paket | Harga | Unit | Perkiraan jam | Marjin per jam |
|---|---|---|---|---|
| **Hormat** | Rp 1,19 jt | 50 | ±3,0 | **±Rp 322 ribu** |
| Resepsi | Rp 2,9 jt | 100 | ±4,5 | ±Rp 578 ribu |
| Grand | Rp 5,9 jt | 150 | ±6,0 | ±Rp 921 ribu |

Pembandingnya: pekerjaan percetakan reguler yang jam mesinnya direbut = **Rp 100–200 ribu/jam**.

Jadi Hormat hanya **±1,6×** pekerjaan reguler — padahal ia membawa tenggat pernikahan, garansi uang kembali 100%, dan risiko cetak ulang. **Setelah disesuaikan risiko, pesanan Hormat mungkin lebih buruk daripada tidak mengambilnya.**

Penyebabnya: **setup mesin sama untuk 50 maupun 150 unit**, jadi paket terkecil menanggung ongkos setup yang sama dengan pendapatan terkecil.

**Pilihan:**
- **(a)** Pertahankan sebagai pintu masuk termurah — sengaja tipis, demi menarik orang naik kelas
- **(b)** Naikkan harganya (mis. Rp 1,49 jt) supaya marjinnya sepadan
- **(c)** Hentikan; paket termurah jadi Resepsi

**Saran saya: jangan putuskan dulu.** Angka "3 jam" itu **tebakan, bukan pengukuran** — dan seluruh kesimpulan bergantung padanya. **Cetak satu sampel dan catat waktunya**, termasuk untuk 50 unit, bukan hanya 100.

⚠️ **Yang paling menentukan dan belum diperiksa: apakah mesin creasing sanggup.** 100 lipatan × 8 pesanan = **800 lipatan per bulan**. Kalau harus dilipat tangan, **seluruh hitungan di tabel ini batal** — bukan cuma Hormat.

---

## 5. Tidak ada titik masuk di bawah Rp 1 juta

**Duduk perkara.** Paket cetak termurah Rp 1.190.000, dan pembelian satuan dipatok minimum Rp 1.000.000 per transaksi. Jadi **tidak ada satu pun pintu masuk di bawah sejuta.**

Sementara itu riset pasar menunjukkan calon pembeli datang dengan pagu harga jauh di bawah itu — mereka membaca artikel yang menyebut angka ratusan ribu, lalu membuka halaman kita.

**Pilihan:**
- **(a)** Biarkan — kita memang bukan main di kelas murah, dan pagar itu menyaring
- **(b)** Buat paket kecil (mis. 25 undangan + amplop, ±Rp 690 ribu) sebagai pintu masuk
- **(c)** Turunkan minimum satuan

**Saran saya: (a) untuk sekarang**, tinjau ulang setelah ada data penolakan nyata. Menambah paket kecil memperparah masalah nomor 4 — makin kecil, makin buruk marjin per jamnya.

⚠️ **Klaim "pembeli datang dengan pagu ratusan ribu" berasal dari riset otomatis yang saya jalankan, dan saya TIDAK memverifikasinya sendiri** ke situs pesaing. Ini justru poin di mana teman Ryan mungkin punya pengetahuan yang lebih baik daripada riset saya.

---

## 6. Garansi Tepat Waktu tidak punya rem

**Duduk perkara.** Kita berjanji tertulis: **barang tiba paling lambat H-14, atau uang kembali 100%.**

Tapi dari 6 tahap produksi, **3 tahap menunggu pelanggan**: mengisi data undangan, menempelkan daftar nama tamu, menyetujui hasil pratinjau. **Tidak ada satu klausul pun yang membatasi waktu pelanggan.**

Skenario nyata: pesanan masuk H-21, pelanggan lambat 10 hari mengirim daftar tamu → barang terlambat → **refund Rp 2,9 juta**, sementara bahan dan ongkir tetap keluar.

**Pilihan:**
- **(a)** Tambahkan klausul: *"jaminan bergeser hari-per-hari bila data, daftar tamu, atau persetujuan pratinjau terlambat lebih dari 4 hari"*
- **(b)** Biarkan — anggap risiko yang ditanggung, demi janji yang bersih tanpa syarat

**Saran saya: (a).** Ini adil dan lazim; pelanggan pun paham bahwa kita tidak bisa mencetak tanpa datanya. Tanpa rem ini, garansi terkuat kita sekaligus jadi lubang terbesar.

**Catatan:** ini mengubah dokumen legal yang **sudah tayang**, jadi perlu keputusan sadar.

---

## 7. Ongkir Rp 150.000 — angka dari mana?

**Duduk perkara.** Struktur biaya memakai ongkir **Rp 150.000 gratis se-Indonesia**, dan angka itulah yang menghasilkan marjin Rp 2,6 juta untuk Paket Resepsi. Tapi **tidak ada catatan dari mana angka itu berasal**, dan tidak ada bukti ia pernah diuji ke tarif kurir nyata.

Bobot paket bahkan tercatat **tiga versi berbeda** di dokumen: 2/5/9 kg, 2/4/7 kg, dan produk di sistem memakai 2/4/7.

Kalau ongkir sebenarnya Rp 300.000 untuk 7 kg ke luar Jawa, marjin Grand tergerus Rp 150.000 per pesanan — dan kita sudah menjanjikan gratis ongkir.

**Yang menyelesaikan:** timbang sampel yang dicetak (poin 4), lalu cek tarif nyata ke **tiga tujuan**: dalam Jawa, Sumatera, Indonesia Timur.

**Saran saya:** ini menempel pada poin 4 — satu sampel menjawab keduanya sekaligus.

---

## 8. Backup: kebijakan bilang terenkripsi, kenyataannya tidak

**Duduk perkara.** Kebijakan Privasi yang tayang menyatakan cadangan data "disimpan terenkripsi". Kenyataannya cadangan itu hanya dikompres, tidak dienkripsi.

Ini janji tertulis yang tidak ditepati — kecil, tapi jenis yang mahal kalau ditanya pihak berwenang atau mitra pembayaran.

**Pilihan:**
- **(a)** Pasang enkripsi (±1 jam kerja, kuncinya disimpan di pengelola kata sandi)
- **(b)** Lunakkan kalimat kebijakannya jadi "disimpan dengan akses terbatas"

**Saran saya: (a).** Sekalian ada temuan lain yang lebih penting: cadangan saat ini **bukan cadangan sungguhan** — ia menyalin persis kondisi server, jadi kalau ada yang terhapus di server, salinannya ikut terhapus. Itu lebih layak diperbaiki daripada enkripsinya.

---

## Yang **tidak** perlu didiskusikan lagi

Supaya diskusi tidak berputar — hal-hal ini sudah diputuskan dan sudah berjalan:

- **Harga digital** Rp 99/179/299 ribu — tiga tingkat, dipertahankan
- **Kredit upgrade** Rp 300.000 rata untuk semua tier
- **Gratis ongkir se-Indonesia**, satu metode, tanpa zona
- **Kuota 8 pesanan cetak per bulan**
- **Pesanan cetak paling lambat H-21** sebelum acara
- **Satu orang tidak boleh jadi reseller sekaligus vendor**
- Produk cetak masih **disembunyikan** dari toko sampai satu pesanan uji tuntas

---

## Kalau waktunya cuma untuk satu hal

**Nomor 1 — dari mana pembeli datang.**

Tujuh pertanyaan lain menentukan seberapa untung tiap pesanan. Nomor 1 menentukan **apakah ada pesanan sama sekali.** Semua yang lain bisa dijawab belakangan, dengan data nyata, setelah orang pertama membayar.

---

## Tiga hal yang hanya bisa dijawab dengan tangan, bukan diskusi

1. **Cetak satu sampel lengkap** — menjawab bobot, waktu produksi, mutu amplop, uji pindai QR, dan pertanyaan mesin creasing sekaligus. Ini prasyarat poin 4 dan 7.
2. **Tanya Duitku tiga hal**: apakah profil merchant (tertulis Rp 99–299 ribu) sanggup memproses transaksi Rp 5,9 juta · bagaimana mekanisme refund · berapa plafon per kanal pembayaran.
3. **Buka undangan di HP sungguhan** — iPhone dan Android — dan periksa seperti calon pembeli.
