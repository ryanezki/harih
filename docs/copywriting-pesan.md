# Copywriting Pesan Otomatis (TASKS T2.23)

> **DRAF v1 (2026-07-07)** — review & sesuaikan gaya bahasa sebelum dipasang di workflow n8n. Format WA memakai `*tebal*` / `_miring_` (formatting WhatsApp). Variabel `{{…}}` diisi n8n — lihat legenda di bawah.

## Legenda Variabel

| Variabel | Sumber |
|---|---|
| `{{nama}}` | order WC → billing first name |
| `{{order_id}}` | order WC → id |
| `{{paket}}` | order WC → line item (Hemat/Favorit/Premium) |
| `{{link_form}}` | WF-01 → `{{WP_URL}}/isi-data/?order={{order_id}}&key={{token}}&paket={{paket}}` |
| `{{link_undangan}}` | WF-02 → permalink post undangan |
| `{{nama_pria}}` `{{nama_wanita}}` | form isi data |
| `{{tgl_acara}}` | sheet `orders` → tgl_acara |
| `{{masa_aktif}}` | dari paket: "sampai H+7" / "sampai H+30" / "sampai 1 tahun setelah acara" |
| ~~`{{nama_reseller}}` `{{kode_reseller}}` `{{bank}}` `{{norek}}`~~ | **dicabut R1** — tidak ada komisi, dan nomor rekening mitra tidak pernah dikumpulkan (`B2`) |
| ~~`{{rincian}}` `{{total_komisi}}` `{{periode}}`~~ | **dicabut R1** — WF-04 dipensiunkan |
| `{{link_katalog}}` | `https://harih.id/` |

---

## 1. Konfirmasi Order + Link Form (WF-01)

### 1a. Email — Subjek: `Pesanan #{{order_id}} diterima — yuk isi data undanganmu ✨`

> Halo {{nama}},
>
> Terima kasih! Pembayaran untuk pesanan **#{{order_id}}** (paket **{{paket}}**) sudah kami terima.
>
> Tinggal satu langkah lagi: isi data undanganmu (±10 menit) lewat tombol di bawah. Setelah dikirim, undangan langsung jadi otomatis dalam ±5 menit dan dikirim ke email & WhatsApp-mu.
>
> **[ISI DATA UNDANGAN]** → {{link_form}}
>
> Catatan penting:
> - Link ini **pribadi** — jangan dibagikan ke siapa pun.
> - Siapkan: nama kedua mempelai & orang tua, tanggal/lokasi acara, dan (sesuai paket) foto-foto terbaikmu.
>
> Butuh bantuan? Balas email ini atau hubungi CS kami.
>
> Salam hangat,
> Tim hariH · harih.id

### 1b. WhatsApp

```
Halo {{nama}}! 🤍

Pembayaran pesanan *#{{order_id}}* (paket *{{paket}}*) sudah kami terima. Terima kasih!

Tinggal satu langkah lagi — isi data undanganmu di sini (±10 menit):
{{link_form}}

⏱️ Setelah data dikirim, undangan jadi otomatis dalam ±5 menit.
🔒 Link ini pribadi, jangan dibagikan ya.

Ada pertanyaan? Balas saja pesan ini.
— hariH
```

---

## 2. Undangan Jadi (WF-02)

### 2a. Email — Subjek: `🎉 Undangan {{nama_pria}} & {{nama_wanita}} sudah jadi!`

> Halo {{nama}},
>
> Selamat! Undangan digitalmu sudah tayang:
>
> **{{link_undangan}}**
>
> **Cara membagikan dengan nama tamu** (nama muncul di halaman pembuka):
> tambahkan `?to=Nama%20Tamu` di belakang link — ganti spasi dengan `%20`.
>
> Contoh: `{{link_undangan}}?to=Budi%20Santoso`
>
> Terlampir **QR code** undanganmu — cocok untuk dicetak di kartu fisik atau standee.
>
> Info paket {{paket}}:
> - Masa aktif halaman: {{masa_aktif}}.
> - Revisi data: {{jatah_revisi}} via CS.
>
> Semoga lancar sampai hari H! 🤍
> Tim hariH · harih.id

### 2b. WhatsApp

```
🎉 Undanganmu sudah jadi, {{nama}}!

*{{nama_pria}} & {{nama_wanita}}*
{{link_undangan}}

📤 *Cara bagikan dengan nama tamu:*
tambahkan *?to=Nama%20Tamu* di belakang link
(ganti spasi dengan %20)

Contoh:
{{link_undangan}}?to=Budi%20Santoso

📩 QR code + panduan lengkap sudah dikirim ke emailmu.
🗓️ Masa aktif: {{masa_aktif}}.

Semoga lancar sampai hari H! 🤍
— hariH
```

---

## 3. Nudge Belum Isi Data — status `MENUNGGU_DATA` > 24 jam (WF-05)

```
Halo {{nama}} 👋

Pesanan *#{{order_id}}* kamu sudah aktif, tapi data undangannya belum diisi.

Isi sekarang (±10 menit), undangannya langsung jadi:
{{link_form}}

Ada kendala saat mengisi? Balas pesan ini, kami bantu sampai selesai.
— hariH
```

---

## 4. Reminder H-3 (WF-05)

```
Halo {{nama}}! Tinggal *3 hari* menuju hari bahagia {{nama_pria}} & {{nama_wanita}} 🎉

Yuk cek undangannya sekali lagi — pastikan tanggal, waktu, lokasi, dan nama sudah benar:
{{link_undangan}}

Ada yang perlu dikoreksi? Hubungi kami hari ini juga supaya sempat direvisi.

Semoga persiapannya lancar! 🤍
— hariH
```

---

## 5. Selamat H+1 + Testimoni (WF-05)

```
Selamat menempuh hidup baru, {{nama_pria}} & {{nama_wanita}}! 🎊

Terima kasih sudah mempercayakan undangan digital kalian ke hariH — semoga menjadi keluarga yang bahagia.

🙏 Kalau berkenan, balas pesan ini dengan kesan singkatmu memakai hariH. Testimonimu sangat berarti untuk kami.

💝 Punya kerabat yang akan menikah? Bagikan hariH ke mereka: {{link_katalog}}

— hariH
```

*(~~Opsional bila program referral aktif: kode diskon 10%~~ — **dicabut R1.** Kupon `RES-` dan seluruh mekanisme referral diganti harga grosir per role; jangan hidupkan kembali kalimat ini tanpa membuka `B1`.)*

---

## 6–7. ~~Welcome Kit Reseller~~ · ~~Rekap Komisi Mingguan~~ — **DICABUT R1 (2026-08-09)**

> ⛔ **Kedua bagian ini dihapus, bukan diarsipkan — isinya adalah janji yang tidak lagi kami lakukan.**
>
> Template lama menjanjikan *"komisi 30% dari setiap order"* dan *"payout ke {bank} {norek} diproses hari ini"*. Di model grosir **arah uang selalu mitra → hariH** (`B2`): tidak ada komisi yang dihitung, tidak ada payout, dan nomor rekening mitra tidak pernah dikumpulkan. WF-04 dipensiunkan, WF-03 menyusul di `M7`.
>
> Membiarkan teksnya di sini "sebagai arsip" berbahaya: berkas ini adalah tempat orang menyalin kalimat saat memasang workflow. Riwayat lengkapnya ada di git sampai commit `d6ec7ee`.
>
> Penggantinya: **bagian 9** di bawah — pesan pembuka ke calon mitra, memakai penawaran yang sudah dikunci (`B9` · `B13` · `B14` · `B15`).

---

## 8. Alert Internal (WF-00 & monitor WAHA — bukan untuk customer)

### 8a. Workflow gagal (WF-00 Error Workflow → WA + email owner)

```
🚨 *[hariH] Workflow GAGAL*
WF: {{nama_workflow}}
Order: #{{order_id}}
Error: {{pesan_error}}
Waktu: {{waktu}} WIB

Cek eksekusi: {{link_eksekusi}}
Customer mungkin sudah bayar tapi belum menerima apa pun — prioritaskan.
```

### 8b. Sesi WhatsApp bermasalah (monitor T2.22 → **email** owner, karena kanal WA sedang mati)

> **Subjek: 🚨 [hariH] Sesi WhatsApp DOWN — delivery WA berhenti**
>
> Status sesi WAHA: **{{status}}** (bukan WORKING) sejak {{waktu}} WIB.
>
> Dampak: semua pengiriman WA berhenti; email tetap berjalan. Order tetap diproses.
>
> Tindakan: SSH ke VPS → `ssh -L 3000:127.0.0.1:3000 user@vps` → buka `http://localhost:3000/dashboard` → login API key → restart/scan ulang QR sesi `default`.
>
> Setelah pulih, cek kolom `wa_status` di sheet `orders` untuk pesan yang perlu dikirim ulang.

---

## 9. Pembuka ke calon mitra — F0.3 *(ditulis tangan owner, bukan otomatis)*

> **Bukan copywriting yang dipoles, dan sengaja begitu.** Halaman jualan `/mitra/` (`M8`) memang harus dibangun dari **keberatan nyata** yang dikumpulkan `F0.5` — bukan dari tebakan. Tapi percakapan pertama harus terjadi lebih dulu supaya ada keberatan untuk dikumpulkan. Jadi yang di bawah hanya **penawaran terkunci yang dituliskan apa adanya**: nol bujukan, nol klaim yang belum ditepati alur. Ganti sendiri kalimatnya sesuai cara Anda bicara — yang tidak boleh berubah adalah isinya.
>
> Aturan yang mengikat teks ini: `B9` (Hormat tidak ditawarkan) · `B13` (yang dijual slot produksi) · `B14` (pembalik risiko, syaratnya bisa diamati) · `B15` (**jangan sebut langganan bulanan**).

### 9a. Pesan pertama — WA, ke pelanggan percetakan & WO yang sudah kenal

```
Halo Pak/Bu {{nama}}, ini {{owner}} dari {{percetakan}}.

Saya lagi buka satu layanan baru: *undangan digital + undangan cetak
lipat beramplop nama tamu* — dan saya cari beberapa partner untuk
menjualnya pakai nama tokonya sendiri.

Jadi undangannya tampil atas nama *{{nama_toko}}*, bukan nama saya.
Bapak/Ibu ambil harga partner, mau dijual berapa pun terserah — selisihnya
diambil sendiri, saya tidak perlu tahu.

Ini contohnya, sudah pakai nama toko Bapak/Ibu:
{{link_demo}}

Boleh saya kirim daftar harga partnernya?
```

*`{{link_demo}}` dibuat lewat `bash demo-mitra.sh "{{nama_toko}}"` — lihat [`panduan-manual.md`](./panduan-manual.md) langkah 0.*

### 9b. Susulan — daftar harga + pembalik risiko

```
Ini harga partnernya, {{nama}}:

📦 *Paket Resepsi* — 100 undangan cetak lipat + amplop nama tamu
   + undangan digital
   Harga partner *Rp 1.650.000* · harga saya ke publik Rp 2.900.000

📦 *Paket Grand* — 150 undangan cetak premium + undangan digital
   Harga partner *Rp 3.400.000* · harga saya ke publik Rp 5.900.000

Bapak/Ibu bebas menentukan harga jual sendiri.

Harga partner ini *ambil di tempat* (Jabodetabek). Kalau mau saya
kirimkan, tambah *Rp 150.000* flat — saya sebutkan di depan supaya tidak
ada biaya yang muncul belakangan.

Yang saya jamin:
*Order pertama telat dari H-14 sebelum acara, atau kliennya menolak
hasilnya — uangnya saya kembalikan penuh, cetakannya tetap diambil.*

Dan ini yang biasanya belum sempat ditanyakan orang:
*undangan klien Bapak/Ibu tidak akan pernah saya matikan* — masa
aktifnya jalan terus, dan nama {{nama_toko}} tetap ada di kakinya,
juga kalau nanti kita berhenti kerja sama.

Kapasitas saya *8 order cetak per bulan*, jadi saya pegang urutan masuk.
```

> ⚠️ **Yang TIDAK boleh masuk pesan ini:**
> · Paket Hormat Rp 1,19 jt — dicabut dari daftar partner (`B9`)
> · langganan/retainer bulanan — belum waktunya (`B15`)
> · *"tawarkan ke 5 pengantin"* — syarat lama yang dicabut, tidak bisa diverifikasi siapa pun (`B14`)
> · janji checkout otomatis — Duitku masih sandbox, semua pembayaran manual (`A7`)

### 9c. Saat mitra kembali untuk order kedua — *baru di sini* retainer disebut

> Momen yang tepat menurut `B15`: mitra kembali, dan slot bulan itu sudah diambil orang lain. Satu kalimat, tanpa tanda bintang.

```
Bulan ini slotnya sudah penuh, {{nama}} — kepakai lima order yang masuk duluan.

Kalau mau, saya bisa kunci slot atas nama {{nama_toko}} tiap bulan:
*Rp {{retainer}}/bulan, seluruhnya jadi saldo order bulan itu.*
Kalau bulan itu tidak dipakai, saldonya hangus.

Jadi bulan depan Bapak/Ibu tidak perlu antre.
```

**Catat jawabannya — ya maupun tidak — beserta alasannya (`F0.6`).** Itu data pertama tentang apakah retainer punya pembeli.

### 9d. Variabel bagian 9

| Variabel | Isi |
|---|---|
| `{{nama_toko}}` | nama toko/usaha calon mitra — dipakai juga di `demo-mitra.sh` |
| `{{link_demo}}` | `https://harih.id/u/demo-mitra/` setelah skrip dijalankan |
| ~~`{{grosir_resepsi}}` `{{grosir_grand}}`~~ | **sudah terkunci 9 Agu, ditulis langsung di teks:** Resepsi Rp 1.650.000 · Grand Rp 3.400.000, ambil di tempat (`B16`) |
| `{{retainer}}` | angka retainer — kunci harganya berlaku **12 bulan**, bukan selamanya (`B18`), dan hanya ditawarkan ke mitra yang sudah order dua kali |
