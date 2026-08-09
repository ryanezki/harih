# Panduan Aksi Manual Owner — hariH

> Hal yang **hanya bisa Anda kerjakan**: butuh tangan di mesin, akun pihak ketiga, HP nomor bisnis, atau keputusan bisnis. Sisanya sudah otomatis atau bisa saya kerjakan.
>
> **Ditulis ulang 7 Agustus, diperbarui 8 Agustus** setelah pengukuran sampel selesai. Urutannya berubah total dari versi lama: dulu semuanya menunggu approval Duitku, sekarang tidak — pintu masuknya pelanggan percetakan & WO yang sudah kenal Anda, dan pembayarannya transfer manual seperti lazimnya vendor pernikahan.
>
> **Yang tersisa sekarang tinggal satu hal yang benar-benar menghambat: bicara ke lima orang.** Sisanya pendukung.
>
> Operasional harian: [`runbook.md`](./runbook.md) · daftar kerja teknis: [`TASKS.md`](./TASKS.md)

**Cara pakai:** kerjakan berurutan. Tiap langkah punya **cara memastikan ia benar-benar selesai**. Kalau tersendat, sebut nomornya saja.

---

# ✅ SUDAH SELESAI — pengukuran sampel (8 Agustus)

Sampel `TEST-173` dicetak & dilipat sungguhan. **Tidak perlu diulang.** Hasil lengkap: [`sampel-cetak-TEST-173.md`](./sampel-cetak-TEST-173.md).

| Pertanyaan | Jawaban |
|---|---|
| **Mesin creasing sanggup?** | **YA** — ±8 dtk/lembar termasuk lipat. 800 lipatan/bulan = ±1,8 jam. **Hambatan terbesar di seluruh rencana gugur.** |
| Waktu per pesanan | Resepsi **1,7 jam tangan** (4,0 jam dinding) — printer jalan sendiri ±90 dtk dari 118 dtk per unit |
| Bobot 1 set | **22 g** → Hormat 1,4 kg · Resepsi 2,6 kg · Grand 3,8 kg. Sudah diperbarui di WooCommerce. |
| Ongkir Rp 150.000 | **aman**, slack Rp 80–100rb/pesanan. Terburuk (Grand ke Indonesia Timur) ±Rp 200rb. |
| QR 31 mm | **cukup**, jangan diperbesar. Jaraknya 58,8 mm dari garis lipat — aman dari retak. |
| Bahan per unit | Rp 3.200 |

**Marjin per jam tangan:** Hormat Rp 713rb · Resepsi **Rp 1,41 jt** · Grand **Rp 2,35 jt**. Pembanding cetak reguler Rp 100–200rb/jam.

**Kuota tetap 8/bulan** untuk bulan pertama meski kapasitas jauh di atas itu — batasnya bukan mesin, melainkan belum pernah satu pesanan pun dikirim tepat waktu.

---

# MINGGU INI

## 🟢 0. Alat yang sudah siap: demo berlabel nama toko calon mitra — *30 detik*

Sebelum menelepon, siapkan satu link yang **sudah memakai nama toko mereka** di
kaki undangan. Menjelaskan white-label lewat kalimat jauh lebih lemah daripada
mengirim link yang tinggal dibuka.

```
ssh -p 65002 u803921702@147.93.80.20
cd domains/harih.id/public_html && bash demo-mitra.sh "Percetakan Melati"
```

Keluarannya satu URL: `https://harih.id/u/demo-mitra/` — kakinya berbunyi
**"Undangan digital oleh Percetakan Melati"**. Untuk calon berikutnya jalankan
lagi dengan nama lain; URL-nya tetap sama, isinya menyesuaikan. Tambahkan
alamat Instagram/web mereka sebagai argumen kedua bila mau namanya jadi tautan.

⚠️ Satu slot dipakai bergantian — jangan kirim ke dua calon dalam waktu
berdekatan, karena yang kedua akan melihat nama yang pertama.

## 🔴 1. Hubungi 5 WO + pelanggan percetakan Anda — *inilah akuisisinya*

**Kenapa ini, bukan iklan atau SEO.** Anda tidak mulai dari nol. Yang sudah pernah membayar Anda sudah percaya Anda bisa mengirim tepat waktu — dan **kepercayaan itulah bagian tersulit dari jualan Rp 2,9 juta.**

**Yang dijual: paket cetak, bukan digital Rp 179rb.** Aritmatikanya:

| | Marjin |
|---|---|
| 10 penjualan digital | ±Rp 1,8 juta |
| **1 pesanan Paket Resepsi** | **±Rp 2,6 juta** |

**Langkah:**
1. Daftar **pelanggan percetakan** yang menikah, atau punya anak/saudara yang menikah tahun ini.
2. Daftar **WO** yang pernah mencetak di tempat Anda.
3. Tunjukkan **sampel fisik** yang sudah Anda cetak — itu yang menjual, bukan link.
4. Sudut penawaran untuk WO: *"undangannya tampil dengan nama Anda, dan Anda ambil marjinnya."*

**Satu pagar supaya tidak menipu diri sendiri:** teman yang beli karena kasihan **tidak dihitung**. Pelanggan yang sudah pernah bayar — dihitung.

**Catat per penjualan:** paket apa · dari mana datangnya · bertanya dulu atau langsung setuju.

**Selesai bila:** 5 percakapan terjadi. Bukan 5 penjualan — 5 percakapan.

## 🔴 2. Duitku — kejar approval + tanyakan tiga hal

Diajukan **4 Agustus**, belum keluar. Tidak lagi menghalangi penjualan (lihat langkah 3), tapi tiga hal ini bisa menggagalkan pembayaran **tepat di langkah terakhir**, dan hanya Anda yang bisa menanyakannya:

1. **Profil merchant menyebut Rp 99–299 ribu**, padahal paket cetak sampai Rp 5,9 juta. Beri tahu mereka — kalau tidak, transaksi besar bisa ditolak.
2. **Mekanisme refund.** Garansi Tepat Waktu menjanjikan uang kembali 100% atas pesanan Rp 2,9 juta. Bisa dari dashboard sendiri atau harus tiket? Berapa lama? Fee kanal ikut kembali?
3. **Plafon per kanal.** E-wallet & QRIS sering berplafon di bawah Rp 5,9 juta.

**Selesai bila:** ketiganya terkirim dan jawabannya Anda kirimkan ke saya.

> Begitu kredensial production dipasang, situs **otomatis** kembali ke tombol bayar online — tidak perlu minta saya deploy apa pun.

## 🟠 3. Latih sekali alur pesanan manual — *sebelum pelanggan pertama*

Sekarang seluruh tombol "Pesan" di situs mengarah ke WhatsApp. Alasannya: gerbang Duitku masih **sandbox**, jadi tombol bayar lama mengarah ke halaman pembayaran uji — pembeli tidak mungkin benar-benar membayar.

DP transfer manual memang norma di pasar pernikahan Indonesia (WO, MUA, katering, dekorasi semuanya begitu), jadi ini bukan penurunan kelas.

**Panduan lengkapnya — termasuk template pesan WhatsApp siap salin-tempel untuk
tiap tahap, angka DP per paket, dan daftar "jangan dilakukan" — ada di
[`runbook.md`](./runbook.md) §7c.** Ringkasnya:

**Digital (Rp 99–299 ribu) — lunas di muka:**
kirim rekening → dana masuk → buat pesanan di wp-admin (nama, email, **nomor WhatsApp**, produk `HARIH-*`) → set **Processing** → otomasi menyala sendiri.

**Cetak (Rp 1,19–5,9 juta) — DP 50%:**
konfirmasi slot & tanggal **dulu** → pastikan acara **≥ H-21** → DP masuk → buat pesanan dengan alamat lengkap + tanggal acara → set **Processing** → pelunasan sebelum barang dikirim.

**Selesai bila:** Anda pernah sekali membuat pesanan manual dan melihat WhatsApp otomatisnya masuk.

---

# SEBELUM PESANAN PERTAMA

## 🟠 4. Buka undangan di HP sungguhan — *20 menit*

Satu-satunya produk yang dilihat calon pembeli sebelum membeli, dan belum pernah disentuh tangan manusia di HP asli.

Buka **iPhone Safari** dan **Android Chrome**, salah satu demo (`harih.id/u/demo-tema-01/`):

- [ ] Musik mulai setelah tap tombol
- [ ] Hitung mundur berjalan
- [ ] Tombol salin rekening — **dan cek isinya benar-benar tersalin**
- [ ] Mode gelap (tombol ☾/☀, dan apakah ikut setelan HP)
- [ ] Galeri kolase · tombol Waze · tombol "beri tahu mempelai lewat WhatsApp" setelah RSVP
- [ ] Kirim linknya ke WA sendiri → preview-nya muncul dengan gambar & nama?

⚠️ WhatsApp meng-cache preview per URL — uji dengan `?x=1` di belakang link supaya dianggap URL baru.

**Selesai bila:** keenamnya lolos, atau yang gagal Anda sebutkan ke saya.

## 🟡 5. Pasang monitor uptime eksternal — *15 menit*

WF-07 & WF-08 mengawasi sistem, tapi **keduanya hidup di dalam n8n** — kalau n8n mati, pengawasnya ikut mati.

Daftar gratis di [uptimerobot.com](https://uptimerobot.com), buat 3 monitor HTTP(s) interval 5 menit, alert ke email + WhatsApp:

| Monitor | URL | Sehat bila |
|---|---|---|
| Situs | `https://harih.id/` | 200 |
| n8n | `https://n8n.harih.id/healthz` | 200 |
| Webhook order | `https://n8n.harih.id/webhook/wc-order` | 200 |

**Selesai bila:** ketiganya hijau, dan Anda pernah menerima satu alert uji.

## 🟡 6. Dua rahasia ke password manager — *5 menit*

`vps/.env` dan `vps/google-sa.json` **hanya ada di disk laptop Anda dan di server**. Tidak ada di git (disengaja), tidak ada backup lain. Laptop hilang + server bermasalah = pipeline harus dibangun ulang dari nol.

Simpan isi keduanya sebagai secure note di password manager.

**Selesai bila:** keduanya bisa Anda buka dari HP.

## 🟢 7. Baca sekali & putuskan

- **Gaya bahasa pesan otomatis** — [`copywriting-pesan.md`](./copywriting-pesan.md). Ini yang dibaca pelanggan Anda; pastikan terdengar seperti Anda.
- **Tema 02 & 03 di HP** sebagai calon pembeli — ada yang tidak enak dilihat?
- **Tiga angka kebijakan** yang sudah tayang di S&K: biaya revisi paket Hemat · SLA revisi 1×24 jam kerja (sanggup?) · batas pengajuan refund 7 hari.

---

# SAAT PESANAN CETAK MASUK

Layar utama Anda: **WooCommerce → Antrean Cetak**

Diurutkan berdasarkan **tenggat acara**, bukan tanggal pesanan — pesanan yang masuk belakangan bisa saja acaranya lebih dulu. Tiap baris hanya menampilkan yang mengubah keputusan hari ini: sisa hari (memerah di bawah 14), jumlah nama tamu terkumpul, satu langkah penghambat, dan resi.

**Urutan tahapnya:**

| Tahap | Yang Anda lakukan |
|---|---|
| Menunggu data undangan | Pemesan belum isi form. Follow-up via WA. |
| Menunggu daftar tamu | Kirimkan link `/tamu/` dari halaman pesanan. |
| Siapkan proof | Bekukan snapshot (tombol di halaman pesanan), buat berkas proof, tempel URL-nya. |
| Menunggu persetujuan | Kirim link `/proof/` ke pemesan. |
| **SIAP CETAK** | Cetak. Data yang dipakai adalah **snapshot beku**, bukan data hidup. |
| Terkirim | Isi nomor resi di halaman pesanan → pemesan diberi tahu otomatis. |

**Yang berjalan tanpa Anda sentuh:** email & WA konfirmasi pembayaran · link isi data · undangan terbit otomatis · link daftar tamu & rekap RSVP · pengingat H-3 · rekap kehadiran harian · pengingat upsell H+3/H+12.

⚠️ **Jaminan H-14 baru mulai berjalan** sejak data undangan, daftar tamu, **dan** persetujuan proof lengkap diterima (S&K §12.2). Beri tahu pemesan tanggal itu — bukan tanggal pesanan.

---

# RITME RUTIN

**Harian (±5 menit)** — [`runbook.md`](./runbook.md) §2:
balas WA · cek alert · sekilas sheet `orders` · cek kolom `wa_status`.

**Mingguan (10 detik):**
```bash
ssh root@31.97.50.197 'docker exec harih-n8n n8n list:workflow --active=true | grep -c "|"'
```
Harus **8** *(sejak R1, 9 Agustus 2026 — WF-04 Rekap Komisi dipensiunkan; tidak ada komisi di model grosir)*. Workflow yang mati diam-diam **tidak memicu alert apa pun**.

**Nomor WA bisnis:** jangan logout dari HP, pakai wajar, jangan blast ke nomor tak dikenal. Sesi ter-ban = seluruh kanal pengiriman WA mati.

---

# YANG TIDAK PERLU ANDA LAKUKAN

**Sudah selesai & terverifikasi 7 Agustus:**
musik 3 track hidup · GA4 aktif · cron dipindah ke hPanel · plugin lengkap (Duitku, FluentSMTP, LiteSpeed, Limit Login, Site Kit) · halaman legal tayang · backup mingguan jalan & restore-nya pernah diuji · 8 workflow aktif · pengecualian cache LiteSpeed untuk kelima halaman bertoken.

**Sudah dihentikan, jangan dikerjakan:**
- ~~Rekrut reseller berkomisi~~ — **program komisi dihentikan seluruhnya di R1, 9 Agustus.** Halamannya dihapus, bukan cuma diturunkan; WF-04 dipensiunkan; nomor rekening berhenti dikumpulkan. Alasannya bukan cuma Rp 54.000 yang tidak menggerakkan siapa pun — komisi membuat mitra merasa jadi pegawai Anda. Penggantinya **harga grosir**: mitra menentukan harga jualnya sendiri dan mengambil seluruh selisih (Rp 1,15 juta di paket yang sama). Lihat `TASKS.md` bagian F0.
- ~~Beli alat baru~~ — tidak ada rupiah keluar sebelum ada pesanan berbayar.
- ~~Naikkan paket hosting~~ — timeout yang pernah terlihat berasal dari jaringan lingkungan kerja, bukan dari situs.

**Yang saya kerjakan, bukan Anda:** semua kode, deploy, workflow n8n, halaman legal (sumbernya di repo), dan pemeriksaan teknis.

---

# CATATAN: data uji yang sengaja disimpan

Pesanan **TEST-173** & undangan **174** (`/u/test-rangga-sekar/`) sengaja **tidak dihapus** — itu sumber berkas sampel Anda.

Ditandai `_harih_uji=1`, jadi **tidak menghitung kuota produksi** (Agustus tetap 0 dari 8). Di Antrean Cetak ia tampil berlabel **"UJI INTERNAL"** supaya tidak tercetak untuk pelanggan.

Hapus setelah pengukuran selesai — beri tahu saya.
