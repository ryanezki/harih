# Panduan Aksi Manual Owner — hariH

> Daftar hal yang **hanya bisa Anda kerjakan**: butuh akun pihak ketiga, kartu identitas, HP nomor bisnis, keputusan bisnis, atau password. Semua hal lain di platform ini sudah otomatis atau bisa saya kerjakan dari CLI.
>
> Disusun 2026-08-04, menggantikan `panduan-go-live.md` yang langkah-langkahnya sudah selesai semua. Untuk operasional harian setelah launch, pegangannya [`runbook.md`](./runbook.md).

**Cara pakai:** kerjakan berurutan dari atas. Tiap langkah punya **verifikasi** — kalau verifikasinya lolos, langkah itu benar-benar selesai. Kalau tersendat, sebut nomornya saja ke saya.

---

## 🔴 1. Ajukan merchant Duitku production — *blocker satu-satunya*

**Kenapa duluan:** ini satu-satunya hal yang menghalangi hariH menerima uang sungguhan. Semua yang sudah dibangun — katalog, 3 tema, otomasi, undangan otomatis — belum menghasilkan satu rupiah pun sampai ini beres. Approval-nya juga makan waktu berhari-hari, jadi makin cepat diajukan makin baik; sisa daftar di bawah bisa dikerjakan sambil menunggu.

**Langkah** (±30 menit + masa tunggu):

1. Login dashboard Duitku (akun production, **bukan** sandbox) → menu pendaftaran merchant.
2. Isi formulir aplikasi merchant **perorangan**. Siapkan: KTP, NPWP (bila diminta), rekening bank atas nama sendiri, dan URL situs `https://harih.id`.
3. Saat ditanya jenis usaha/produk: *jasa pembuatan undangan pernikahan digital*, produk digital, sekali bayar Rp 99.000–Rp 299.000.
4. Kirim, lalu pantau statusnya. Duitku biasanya mereview situs — situs Anda sudah siap: katalog dengan harga jelas, 4 halaman legal live, 3 demo produk yang bisa dibuka, kontak yang bisa dihubungi.

**Setelah approval keluar** — beri tahu saya, lalu:

5. wp-admin → WooCommerce → Settings → Payments → Duitku → ubah mode **Sandbox → Production**, isi Merchant Code + API Key production, Save.
6. Bilang ke saya **"Duitku production sudah aktif"** — saya buat produk uji tersembunyi Rp 10.000, Anda pesan dari HP, dan kita verifikasi undangannya sampai di WhatsApp < 15 menit. Produk ujinya saya hapus setelah lolos.

**Verifikasi:** 1 order Rp 10.000 dari HP lolos penuh (bayar → link form → isi → undangan terbit → WA masuk) **dan** dananya benar-benar masuk ke rekening merchant Anda.

---

## 🟠 2. Pilih musik latar — fitur yang sudah dijual tapi belum ada

**Kenapa:** "musik latar instrumental" tercantum di **ketiga** paket di katalog, di deskripsi produk WooCommerce, dan diatur di S&K §7 — tapi pustakanya masih kosong. Sekarang form isi data hanya menulis "akan ditambahkan tim kami", artinya tiap order jadi kerja manual CS. Ini janji yang belum ditepati sejak hari pertama.

**Langkah** (±45 menit):

1. Pilih **5–10 track instrumental** dari sumber berlisensi komersial. Rekomendasi, dari yang paling mudah:
   - **Pixabay Music** — gratis, lisensi komersial, tanpa atribusi.
   - **Uppbeat** — gratis dengan atribusi, atau berlangganan murah tanpa atribusi.
   - **Artlist / Epidemic Sound** — berlangganan, kualitas paling konsisten.
2. Kriteria praktis: instrumental murni (tanpa vokal berbahasa apa pun), tenang, 2–4 menit, dan **enak saat di-loop** — tamu bisa membuka undangan lama-lama.
3. Unduh versi MP3-nya, lalu **simpan bukti lisensinya**: screenshot halaman lisensi atau invoice, satu per track. Simpan di Google Drive Anda, jangan di repo.
4. Kirim file-file MP3 + bukti lisensinya ke saya.

**Yang saya kerjakan setelah itu:** unggah ke server, isi `harih_musik_library()`, catat provenance-nya di `docs/aset-lisensi.md`, dan dropdown pilihan musik otomatis muncul di form isi data.

**Verifikasi:** buka form isi data → ada dropdown "Pilih musik instrumental" berisi daftar track; satu undangan uji memutar musiknya di HP setelah tombol *Buka Undangan* ditekan.

---

## 🟠 3. Simpan dua rahasia ke password manager — *5 menit, risiko besar*

**Kenapa:** `N8N_ENCRYPTION_KEY` adalah kunci yang mengenkripsi seluruh credential n8n di dalam arsip backup. Nilainya **sekarang cuma ada di VPS yang sama dengan backup-nya**. Kalau VPS itu hilang, backup n8n-nya ikut jadi tidak berguna — tidak bisa di-restore sama sekali. Ini persis skenario yang bikin backup terasa aman padahal tidak.

**Langkah:**

1. Ambil nilainya (jalankan di Terminal, dari folder project):
   ```bash
   grep -E 'N8N_ENCRYPTION_KEY|WAHA_API_KEY' vps/.env
   ```
2. Simpan di password manager (1Password/Bitwarden/iCloud Keychain) sebagai entri **"hariH — kunci infrastruktur"**.
3. Sekalian simpan **seluruh isi** `vps/.env` sebagai catatan aman di entri yang sama — file itu tidak ada di GitHub (memang sengaja) dan satu-satunya salinannya ada di Mac ini + VPS.

**Verifikasi:** buka password manager di HP Anda, kedua nilai terbaca.

---

## 🟡 4. Pasang uptime monitor eksternal — *15 menit*

**Kenapa:** monitor yang ada sekarang (WF-07 untuk sesi WhatsApp, WF-08 untuk webhook) **berjalan di dalam n8n**. Kalau n8n sendiri yang mati, kedua monitor mati bersamanya dan tidak ada yang memberi tahu siapa pun. Butuh satu pengawas dari luar. Akun harus Anda yang buat.

**Langkah:** daftar gratis di [uptimerobot.com](https://uptimerobot.com), lalu buat 3 monitor:

| # | Tipe | URL | Setelan tambahan |
|---|---|---|---|
| 1 | HTTP(s) — Keyword | `https://harih.id/` | Keyword: `paket` · harus **ada** |
| 2 | HTTP(s) | `https://n8n.harih.id/healthz` | interval 5 menit |
| 3 | HTTP(s) — Keyword | `https://harih.id/u/demo-tema-01/` | Keyword: `Buka Undangan` · harus **ada** |

Alert dikirim ke **hi@harih.id** dan (kalau UptimeRobot mendukung di paket gratis) nomor WA/Telegram Anda.

**Kenapa monitor #3 pakai keyword:** situs bisa membalas 200 tapi isinya halaman error WordPress. Keyword memastikan halaman undangan benar-benar ter-render, bukan sekadar "server hidup".

**Verifikasi:** ketiganya berstatus hijau di dashboard UptimeRobot.

---

## 🟡 5. Uji undangan di HP sungguhan — *20 menit*

**Kenapa:** perilaku Safari iOS kerap berbeda dari emulator, dan tiga hal di bawah tidak bisa saya uji dari sini sama sekali. Semuanya fitur yang dijual.

Buka **https://harih.id/u/demo-tema-01/** dari **iPhone (Safari)** dan **Android (Chrome)**, lalu cek:

- [ ] Tekan **Buka Undangan** → halaman ter-scroll & **musik mulai** (musik baru ada setelah langkah 2; sebelum itu cukup pastikan tidak error)
- [ ] **Countdown berjalan** dan angkanya masuk akal (baru saja saya perbaiki — sebelumnya diam di 0)
- [ ] Scroll ke *Amplop Digital* → tekan **Salin** → tempel di aplikasi lain, nomor rekeningnya benar
- [ ] Isi **RSVP** → ucapan Anda muncul di daftar
- [ ] Tombol **Bagikan via WhatsApp** membuka WA dengan link benar
- [ ] Coba ketiga tema: `/u/demo-tema-02/` dan `/u/demo-tema-03/`

**Uji preview share WhatsApp** (ini yang paling menentukan konversi reseller):

- [ ] Kirim link `https://harih.id/` ke chat WA pribadi Anda → muncul **gambar kartu hariH** + judul, bukan link telanjang
- [ ] Kirim link `https://harih.id/u/demo-tema-02/` → muncul gambar juga

> ⚠️ WhatsApp **meng-cache preview per URL**. Kalau Anda pernah mengirim link ini sebelum hari ini, previewnya bisa masih yang lama/kosong. Uji dengan menambahkan `?x=1` di belakang link supaya WA menganggapnya URL baru.

**Verifikasi:** semua kotak tercentang di kedua HP. Ada yang aneh → screenshot dan kirim ke saya.

---

## 🟡 6. Baca runbook & putuskan angka kebijakan — *20 menit*

**Kenapa:** [`runbook.md`](./runbook.md) adalah pegangan Anda saat sistem rusak di tengah malam. Kalau baru dibaca pertama kali saat panik, gunanya hilang. Beberapa angka di dalamnya juga masih default saya, bukan keputusan Anda.

**Langkah:** baca dari atas ke bawah (±10 menit), lalu putuskan dan beri tahu saya:

1. **Revisi paket Hemat** — berbayar berapa? (S&K sekarang menulis "berbayar, kebijakan CS" tanpa angka)
2. **SLA revisi** — sekarang tertulis maksimal 1×24 jam kerja. Sanggup? Kalau tidak, ubah sekarang selagi belum ada customer.
3. **Batas pengajuan refund** — sekarang 7 hari sejak pembayaran, diproses maksimal 7 hari kerja. Setuju?

**Verifikasi:** Anda bisa menjawab tanpa membuka dokumen: "kalau WhatsApp mati, saya harus apa?" (jawabannya runbook §5.1).

---

## 🟢 7. Pasang analytics & Search Console — *20 menit*

**Kenapa:** sekarang **nol** data. Kalau nanti pengunjung datang tapi tidak ada yang membeli, tidak akan ada cara tahu mereka berhenti di mana — di katalog, di checkout, atau di form isi data. Keputusan perbaikan jadi tebak-tebakan.

**Langkah:**

1. **Google Search Console** → [search.google.com/search-console](https://search.google.com/search-console) → tambah properti `harih.id` → pilih verifikasi **DNS TXT** (paling awet) atau tag HTML → kirim kodenya ke saya, saya pasang.
2. Setelah terverifikasi: Sitemaps → kirim `https://harih.id/wp-sitemap.xml`.
3. **Analytics** — pilih salah satu, beri tahu saya mana:
   - **Plausible** (±$9/bulan) — ringan, tanpa cookie banner, cukup untuk kebutuhan ini.
   - **GA4** (gratis) — lebih lengkap, tapi menambah beban halaman dan konsekuensi privasi (Kebijakan Privasi perlu disesuaikan).

**Verifikasi:** Search Console menampilkan sitemap "Success" dengan jumlah URL terbaca.

---

## 🟢 8. Review visual tema di HP — *10 menit*

Buka ketiganya dari HP dan nilai sebagai **calon pembeli**, bukan sebagai pemilik:

- https://harih.id/u/demo-tema-01/ — Botanical Elegan (sage & gading)
- https://harih.id/u/demo-tema-02/ — Senja Terakota (terakota & tembaga)
- https://harih.id/u/demo-tema-03/ — Langit Malam (navy & emas)

Pertanyaannya cuma satu: **layakkah ini dibayar Rp 99–299 ribu?** Kalau ada yang terasa kurang, sebutkan spesifik (mis. "tema-03 terlalu gelap di HP saya", "jarak antar section terlalu renggang") — perbaikan tampilan itu murah.

---

## 🟢 9. Setelah semua di atas: soft launch

1. **Rekrut 3 reseller pertama** — arahkan ke https://harih.id/jadi-reseller/. Anda menerima notifikasi WA berisi link approval; klik untuk mengaktifkan kupon mereka.
2. **Review gaya bahasa** pesan otomatis di [`copywriting-pesan.md`](./copywriting-pesan.md) — ini yang akan dibaca customer Anda, pastikan terdengar seperti Anda.
3. **Nomor WA bisnis:** jangan logout dari HP, pakai wajar, jangan blast ke nomor tak dikenal. Sesi terbanned = seluruh kanal pengiriman WA mati.

---

## Ritme rutin setelah launch

| Kapan | Apa | Berapa lama |
|---|---|---|
| Tiap hari | Buka WA bisnis, balas CS. Cek ada alert masuk atau tidak — **tidak ada alert = sistem sehat** | 5 menit |
| Tiap hari | Sekilas Google Sheet `orders`: ada `MENUNGGU_DATA` > 2 hari? follow-up manual | 2 menit |
| Tiap Senin | WF-04 mengirim rekap komisi ±09:00 → transfer payout → ubah `UNPAID` → `PAID` di sheet | 15 menit |
| Tiap bulan | Cek backup mingguan benar-benar ada di VPS (`/opt/harih/backups/`) | 5 menit |
| Saat ada alert | Buka [`runbook.md`](./runbook.md) §3 — tiap alert ada tabel tindakannya | sesuai kasus |

---

## Yang **tidak** perlu Anda lakukan

Sudah otomatis, jangan dikerjakan manual:

- Mencatat order, membuat token form, mengirim link form → WF-01
- Membuat undangan, upload foto, QR code, kirim ke WA & email, set order `completed` → WF-02
- Membuat kupon reseller & welcome kit → WF-03 (Anda cukup klik link approval)
- Reminder H-3, ucapan H+1, nudge yang belum isi data, peringatan masa aktif → WF-05
- Menonaktifkan undangan yang lewat masa aktif → cron WP harian
- Backup mingguan (DB, uploads, sesi WA, workflow n8n) → script VPS
- Mengawasi sesi WhatsApp & webhook WooCommerce → WF-07 & WF-08
- Menangkap order yang lolos dari webhook → WF-08 rekonsiliasi, maksimal tertunda 15 menit

Kalau salah satu di atas ternyata **tidak** jalan, itu insiden — lihat runbook, jangan dikerjakan manual diam-diam.

## Menunjukkan pilihan agama ke calon pemesan

Nuansa keagamaan (**Islam · Kristen · Katolik · Hindu · Buddha · Konghucu · tanpa unsur agama**) **tidak melekat pada tema** — keduanya dua setelan terpisah, jadi tema mana pun bisa dipasangkan dengan nuansa mana pun. Undangan demo sengaja memakai nuansa yang sama supaya yang terbandingkan hanyalah temanya.

**Cara termudah:** buka undangan demo mana pun, lalu pakai **pemilih “Nuansa” yang mengambang di kiri-bawah** — calon pemesan bisa mencoba sendiri tiap agama tanpa dipandu. Pemilih ini hanya ada di halaman demo, tidak pernah muncul di undangan pelanggan.

Atau langsung lewat URL, tambahkan `?nuansa=` di belakang link demo:

- `https://harih.id/u/demo-tema-01/?nuansa=kristen`
- `https://harih.id/u/demo-tema-03/?nuansa=hindu`
- `https://harih.id/u/demo-tema-02/?nuansa=umum` (tanpa unsur agama)

Nilai yang tersedia: `islam`, `kristen`, `katolik`, `hindu`, `buddha`, `konghucu`, `umum`. Parameter ini **hanya bekerja pada undangan demo** — undangan pelanggan tidak bisa diubah lewat URL.
