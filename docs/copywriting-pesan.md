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
| `{{nama_reseller}}` `{{kode_reseller}}` `{{bank}}` `{{norek}}` | sheet `resellers` |
| `{{rincian}}` `{{total_komisi}}` `{{periode}}` | WF-04 agregasi sheet `komisi` |
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

*(Opsional bila program referral aktif: tambahkan baris "Gunakan kode `{{kode_referral}}` untuk diskon 10%.")*

---

## 6. Welcome Kit Reseller (WF-03)

### 6a. Pesan utama

```
Selamat bergabung sebagai reseller hariH, {{nama_reseller}}! 🎉

Kode kuponmu: *{{kode_reseller}}*

Cara kerjanya:
1️⃣ Bagikan katalog: {{link_katalog}}
2️⃣ Pembeli memasukkan kode *{{kode_reseller}}* saat checkout → mereka hemat *10%*
3️⃣ Kamu dapat komisi *30%* dari setiap order 💸

Rekap + payout komisi dikirim tiap *Senin pagi*.

Aturan singkat:
• Promosikan ke kenalan & media sosialmu sendiri (bukan spam grup acak)
• Kode tidak berlaku untuk pembelian sendiri

Caption siap pakai menyusul di pesan berikutnya 👇
— hariH
```

### 6b. Contoh caption promosi (pesan lanjutan, siap di-forward)

```
Contoh caption — tinggal salin & sesuaikan:

1) Buat kamu yang mau nikah tapi budget undangan mepet 👰🤵
Undangan digital mulai *99rb*, jadi dalam *hitungan menit*, bisa dibuka semua tamu dari WhatsApp. Ada RSVP, galeri foto, amplop digital.
Cek: {{link_katalog}} — pakai kode *{{kode_reseller}}* biar hemat 10%!

2) Nikahan makin praktis ✨ Nggak perlu cetak ratusan undangan — kirim 1 link, semua tamu kebagian, nama tamu bisa otomatis muncul. Mulai 99 ribu aja.
{{link_katalog}} · kode diskon: *{{kode_reseller}}*

3) [Untuk story] Bantu share dong 🙏 Temen/saudara ada yang mau nikah? Undangan digital cantik + cepet + murah meriah. DM aku atau langsung ke {{link_katalog}}, jangan lupa kode *{{kode_reseller}}* ya!
```

---

## 7. Rekap Komisi Mingguan (WF-04, Senin 09:00 WIB)

### 7a. Ke tiap reseller

```
💸 *Rekap Komisi hariH* — {{periode}}

Halo {{nama_reseller}}! Ini hasil penjualanmu minggu lalu:

{{rincian}}

Total komisi: *Rp {{total_komisi}}*
Payout ke {{bank}} {{norek}} diproses hari ini.

Terima kasih & semangat terus! 🚀
— hariH
```

*(Format baris `{{rincian}}`: `• #1023 · Favorit · komisi Rp 48.300`)*

### 7b. Ke owner

```
📊 *[hariH] Rekap komisi — {{periode}}*

{{jumlah_reseller}} reseller aktif · total payout *Rp {{total_semua}}*

{{rincian_per_reseller}}

✅ Setelah transfer, ubah status di sheet `komisi` → PAID.
```

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
