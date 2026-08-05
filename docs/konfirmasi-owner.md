# Yang perlu dikonfirmasi owner — per 6 Agustus 2026

> **SUDAH DIJAWAB 6 Agustus 2026.** Seluruh poin A–E terjawab dan sudah diterapkan; rinciannya di git log dan `TASKS.md`. Dokumen ini disimpan sebagai catatan keputusan, bukan daftar tugas aktif.
>
> Perubahan terbesar dari jawaban itu: **percetakan & alat milik sendiri** (fase subkontrak gugur), dan **produk cetak berubah jadi undangan lipat + amplop bernama** dengan jumlah per paket turun jadi 50/100/150. Tiga hal yang masih menunggu owner: **hitung marjin per jam percetakan yang sekarang**, **cetak satu sampel lengkap** (menjawab bobot, waktu lipat, uji QR, dan pertanyaan mesin creasing sekaligus), dan **cek mekanisme refund Duitku**.

Daftar ini dibuat setelah F3 (bedah WooCommerce untuk barang fisik) selesai. Isinya **hanya hal yang tidak bisa saya putuskan sendiri**: keputusan bisnis, angka nyata, dan hal yang perlu dicek ke pihak ketiga. Tiap poin sudah disertai saran saya — kalau setuju, cukup jawab "ikuti saran" untuk nomor itu.

Urutannya menurut seberapa cepat ia menghambat uang masuk.

---

## A. Mendesak — ada celah yang terbuka sekarang

### A1. Paket cetak sudah bisa dibeli langsung tanpa lewat WhatsApp 🔴

Waktu halaman `/harga/` diterbitkan, pengamannya adalah **semua CTA mengarah ke WhatsApp** supaya slot produksi dikonfirmasi sebelum ada uang berpindah. Tapi F3 membuat ketiga paket jadi produk WooCommerce sungguhan, dan produk itu **muncul di `/shop/` serta bisa dimasukkan keranjang dan dibayar langsung**. Artinya seseorang bisa membayar Rp 2.900.000 hari ini — sebelum ada percetakan terpilih (F1.2) dan sebelum uji pindai QR (F1.1) — dan ketiga garansi di S&K §12 langsung mengikat.

**Pilihan:**
1. **Sembunyikan produk cetak dari katalog & pencarian toko** sampai F1.1+F1.2 beres. Produk tetap ada (dipakai halaman upsell & link manual dari CS), tapi tidak bisa ditemukan sendiri oleh pengunjung.
2. Biarkan terbuka — terima risikonya, dan siapkan diri menolak/menjadwal ulang order yang masuk.
3. Buka, tapi tambahkan langkah "konfirmasi slot" wajib sebelum tombol bayar aktif.

**Saran saya: nomor 1.** Satu perintah, bisa dibalik kapan saja, dan tidak ada yang hilang — halaman `/harga/` tetap tayang lengkap dengan harganya.

### A2. Berapa kuota produksi cetak per bulan?

S&K §12.2 sudah menjanjikan "kapasitas produksi per bulan terbatas" dan halaman harga menyebutnya juga. Angkanya belum ada, padahal itu yang membuat Garansi Tepat Waktu bisa ditepati.

**Saran saya:** mulai dari **4 order/bulan** selama fase subkontrak, naikkan setelah F1.7 (waktu & biaya nyata) terukur.

### A3. Status Duitku production (F0.1)

Sudah approved atau masih menunggu? Ini masih gerbang tunggal untuk semua uang riil, termasuk digital.

---

## B. Angka yang saya butuhkan untuk menyalakan fitur yang sudah jadi

### B1. Harga tiga SKU upgrade + besaran kredit (F1.3)

Halaman upsell sudah live dan berfungsi, tapi sementara memakai rumus "harga paket − yang sudah dibayar" dan ditutup lewat WhatsApp. Begitu SKU `UPG-HORMAT/RESEPSI/GRAND` dibuat dengan harga tetap, halaman otomatis memakainya — tanpa saya sentuh kodenya.

**Saran saya:** kredit **tetap Rp 299.000** untuk semua tier, sehingga:

| SKU | Harga upgrade |
|---|---|
| `UPG-HORMAT` | Rp 891.000 |
| `UPG-RESEPSI` | Rp 2.601.000 |
| `UPG-GRAND` | Rp 5.601.000 |

Konsekuensinya pembeli Hemat (bayar 99rb) mendapat kredit Rp 200rb lebih besar dari yang ia bayar. Pada paket Rp 2,9 juta itu derau, dan justru mendorong pembeli tier bawah naik kelas. **Ini perlu keputusan sadar, bukan diasumsikan.**

### B2. Bobot paket untuk ongkir

Saya isi tebakan: Hormat **2 kg**, Resepsi **5 kg**, Grand **9 kg**. Angka ini dipakai kurir menghitung ongkos — yang kita tanggung karena ongkir gratis. Perlu ditimbang saat sample fisik dari percetakan datang (F1.2).

### B3. Kurir mana yang dipakai?

S&K menjanjikan "kurir ber-SLA" + nomor resi. Perlu satu nama supaya field resi di halaman order punya konteks, dan supaya alokasi ongkir Rp 150rb bisa diuji nyata.

### B4. Komisi reseller untuk item à la carte = Rp 0 — setuju?

Keputusan terkunci hanya menyebut rupiah tetap untuk **paket** (150/300/500rb). Untuk item satuan saya set nol karena marjinnya paling tipis. Kalau mau ada, sebutkan angkanya.

---

## C. Perlu dicek ke pihak ketiga (saya tidak bisa memverifikasi)

### C1. Batas nilai transaksi Duitku per kanal

Paket Grand Rp 5.900.000 dan Resepsi Rp 2.900.000 jauh di atas order digital biasa. Sebagian kanal (e-wallet, QRIS) punya batas nominal per transaksi. Perlu dipastikan kanal mana yang sanggup, supaya pembeli tidak menemui pembayaran gagal di langkah terakhir.

### C2. Mekanisme refund Duitku untuk Garansi Tepat Waktu

Garansi itu menjanjikan **uang kembali 100%** untuk order sebesar Rp 2,9 juta. Perlu dipastikan refund bisa dilakukan dari dashboard Duitku, berapa lama prosesnya, dan apakah fee kanal ikut kembali. Ini sudah tercatat sebagai risiko di TASKS tapi belum pernah diperiksa.

### C3. Pajak & faktur

Order Rp 5,9 juta wajar diminta bukti/faktur oleh pembeli korporat atau WO. Status usaha (PKP / non-PKP) menentukan apa yang boleh dan harus dikeluarkan.

---

## D. Keputusan cepat (bisa dijawab satu kata)

| # | Pertanyaan | Saran saya |
|---|---|---|
| D1 | Checkout digital sekarang **tidak lagi meminta alamat** (hanya email, nama, WhatsApp). Setuju? | Ya — ini yang selama ini dimaksud "checkout ramping", ternyata tidak pernah aktif |
| D2 | Link upsell dikirim **otomatis** oleh WF-01 setelah undangan jadi? | Ya, tapi **H+1** setelah undangan terkirim — bukan menit yang sama, supaya tidak terasa langsung dijualin lagi |
| D3 | Produk cetak tampil di `/shop/`? | Tidak — lihat A1 |
| D4 | Halaman `/satuan/` ditautkan dari `/harga/`? | Ya, satu tautan kecil di bagian tabel satuan |
| D5 | Batas H-21 untuk pesanan cetak: tetap? | Tetap sampai F1.7 memberi angka waktu produksi nyata |

---

## E. Yang sudah saya putuskan sendiri — mohon dikoreksi kalau tidak setuju

Semuanya sudah live dan tercatat di TASKS; saya sebutkan supaya tidak ada yang lewat begitu saja:

1. **Paket hybrid → tier undangan `premium`.** Halaman harga menjanjikan "digital custom"/"setara Premium", jadi pembeli Rp 1,19 jt ke atas menerima undangan tier tertinggi.
2. **Komisi paket cetak: rupiah tetap 150/300/500rb**, dihitung per line item, bukan persentase.
3. **Order cetak murni tetap membuat baris di sheet `orders`**, ditandai `cetak` di kolom paket. Rencana awal: tidak dibuat sama sekali. Saya pertahankan karena barisnya berguna sebagai catatan operasional dan menjaga logika anti-duplikat tetap utuh — yang berbahaya (link isi data) sudah ditutup.
4. **Minimum à la carte Rp 1.000.000/transaksi** hanya berlaku bila keranjang **tidak** memuat paket.
5. **Alamat dasar toko diubah dari `US:CA` ke `ID:JK`.** Ini bawaan instalasi yang tidak pernah terasa selama produk virtual.

---

## Yang tidak perlu dijawab sekarang

F1.6 (5 order manual), F1.7 (catat waktu & biaya), F1.8 (protokol uji harga), F1.9 (dekati vendor), F0.3 (10 pembeli asing) — semuanya menunggu A1–A3 dan B1–B3 beres dulu.
