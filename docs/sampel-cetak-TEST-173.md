# Sampel cetak TEST-173 — lembar pencatatan

**Berkas:** `sampel-cetak-TEST-173.pdf` (3 halaman, A4 lanskap)
**Dibangkitkan:** 7 Agustus 2026 dari snapshot beku pesanan #173
**Hash snapshot:** `84400719dc88c9d6` · **hash proof:** `88993c978212ba14`

> Sumber datanya **snapshot beku**, bukan data undangan yang hidup — sama seperti
> yang akan dibaca produksi nanti. Jadi apa yang tercetak di sini persis apa yang
> akan tercetak pada pesanan sungguhan dengan data yang sama.

---

## Cara mencetak

1. **Halaman 1 & 2 bolak-balik** pada satu lembar A4.
   - Orientasi **lanskap**, skala **100%** — jangan "fit to page", itu menggeser posisi lipatan.
   - Bolak-balik **flip on short edge** (sisi pendek), supaya panel dalam sejajar dengan sampul.
2. **Lipat vertikal di tengah** mengikuti tik kecil di tepi atas & bawah → jadi A5 potret.
3. **Halaman 3** untuk menguji nama amplop — cetak ke amplop sungguhan bila bisa,
   atau ke kertas dulu untuk menilai keterbacaan.

---

## Yang dicatat

### A. Waktu per tahap

Catat untuk **satu unit** dulu, lalu untuk **10 unit** (biar ongkos setup kelihatan terpisah dari waktu per-unit — itu yang menentukan apakah Paket Hormat 50 unit masuk akal).

| Tahap | 1 unit | 10 unit | Catatan |
|---|---|---|---|
| Setup mesin (sekali) | — | | |
| Cetak sisi 1 | | | |
| Balik & cetak sisi 2 | | | |
| **Creasing / lipat** | | | ⚠️ yang paling menentukan |
| Cetak nama amplop | | | |
| Masukkan ke amplop | | | |
| **TOTAL** | | | |

### B. Mesin creasing 🔴 *pertanyaan terpenting*

| | |
|---|---|
| Mesin sanggup melipat A4 → A5? | ☐ ya ☐ tidak |
| Kalau ya, berapa lembar per menit? | |
| Hasil lipatannya rapi (tidak pecah/retak)? | ☐ ya ☐ tidak |
| Kalau harus manual, berapa detik per lembar? | |

> **Kenapa ini yang paling menentukan.** 100 lipatan × 8 pesanan = **800 lipatan
> per bulan**. Kalau harus tangan, seluruh hitungan marjin per jam batal — bukan
> cuma untuk Paket Hormat, tapi untuk ketiga paket.

### C. Bobot (untuk ongkir yang kita tanggung)

| | Gram |
|---|---|
| 1 undangan lipat (tanpa amplop) | |
| 1 amplop kosong | |
| **1 set lengkap (undangan + amplop)** | |
| Perkiraan 50 set + packaging *(Hormat)* | |
| Perkiraan 100 set + packaging *(Resepsi)* | |
| Perkiraan 150 set + packaging *(Grand)* | |

> Dokumen sempat mencatat **tiga versi berbeda** (2/5/9 kg dan 2/4/7 kg); produk
> di sistem memakai 2/4/7. Angka dari timbangan menggantikan ketiganya.

### D. Uji pindai QR

| | |
|---|---|
| Terpindai **sebelum** dilipat? | ☐ ya ☐ tidak |
| Terpindai **setelah** dilipat? | ☐ ya ☐ tidak |
| Terpindai setelah **dilaminasi** (bila dipakai)? | ☐ ya ☐ tidak |
| Jarak pindai wajar (±15–25 cm)? | ☐ ya ☐ tidak |
| Ukuran QR sekarang **31 mm** — perlu diperbesar? | ☐ cukup ☐ perbesar jadi ___ mm |

QR memakai generator & parameter **yang sama persis** dengan produksi
(`ecc=H`, `qzone=4`) — jadi hasil uji ini berlaku untuk pesanan sungguhan.

### E. Ongkir nyata

Ambil dari tarif kurir yang **sudah Anda pakai selama ini**, untuk bobot di bagian C:

| Tujuan | Bobot | Tarif |
|---|---|---|
| Dalam Jawa | | |
| Sumatera | | |
| Indonesia Timur | | |

> Struktur biaya sekarang memakai **Rp 150.000 gratis se-Indonesia**, dan angka
> itulah yang menghasilkan marjin Rp 2,6 juta untuk Paket Resepsi. Tidak ada
> catatan dari mana angka itu berasal.

### F. Mutu — penilaian mata

| | |
|---|---|
| Amplop bernama tercetak: rapi & sepadan harga? | ☐ ya ☐ tidak |
| Ada yang terpotong / posisi lipatan meleset? | |
| Kertas yang dipakai (jenis & gramatur) | |
| Yang perlu diubah di desainnya | |

---

## Setelah diisi

Tiga hal ini langsung terjawab begitu tabel di atas terisi:

1. **Paket Hormat dipertahankan, dinaikkan, atau dihentikan** — butuh waktu untuk 50 unit (bagian A)
2. **Ongkir Rp 150.000 realistis atau tidak** — bagian C + E
3. **Apakah kuota 8/bulan masuk akal** — total jam (bagian A) × 8 vs jam mesin yang tersedia

Kalau mesin creasing **tidak** sanggup (bagian B), berhenti dulu — itu mengubah
seluruh hitungan, dan lebih baik ketahuan sebelum ada pesanan yang disanggupi.

---

## Data uji yang sengaja DISIMPAN

Ditandai `TEST-` supaya mudah dikenali. Jangan dihapus sebelum pengukuran selesai.

| | |
|---|---|
| Pesanan WooCommerce | **#173** — `TEST-Sampel Cetak`, status `processing` |
| Undangan | **ID 174** — `/u/test-rangga-sekar/` |
| Baris sheet `orders` | order_id 173, `paket=premium+cetak`, `SUDAH_JADI` |
| Kuota bulan berjalan | **tidak dihitung** — lihat di bawah |

✅ **Pesanan ini TIDAK menghitung kuota produksi.** Ditandai meta `_harih_uji=1`,
dan `undangan_kuota_terpakai()` melewatinya. Kuota Agustus tetap **0 dari 8**,
jadi delapan slot sungguhan masih utuh.

Di **Antrean Produksi Cetak** ia tetap tampil — justru supaya kelihatan sedang
ada di jalur — tapi berlabel **"UJI INTERNAL · SIAP CETAK"** berwarna berbeda,
supaya tidak ada yang mencetaknya untuk pelanggan.

> Penandanya meta eksplisit, **bukan pencocokan nama**: pesanan pelanggan yang
> kebetulan memuat kata "TEST" tidak akan ikut terlewat dari hitungan kuota.
> Pesanan uji berikutnya cukup diberi meta yang sama.

⚠️ Saat pesanan diset `processing`, **WF-01 menyala dan benar-benar mengirim**
satu WhatsApp ke 0815-1910-8008 dan satu email ke hi@harih.id. Itu memang jalur
sungguhan — bukan kesalahan.
