# Runbook Operasional hariH (TASKS T4.9)

> Pegangan owner saat sistem berjalan — apa yang dicek, arti tiap alert, dan cara memperbaiki tanpa menunggu developer. Sistem dirancang "tanpa campur tangan manusia", dokumen ini untuk saat ada yang rusak.

## 1. Akses & lokasi penting

| Apa | Di mana |
|---|---|
| Situs & wp-admin | `https://harih.id` · `https://harih.id/wp-admin` |
| n8n (workflow & riwayat eksekusi) | `https://n8n.harih.id` |
| Dashboard WAHA (WhatsApp) | hanya via SSH tunnel — lihat §5 |
| Log operasional | Google Sheet: tab `orders`, `resellers`, `komisi` |
| SSH WordPress (Hostinger) | `ssh -p 65002 u803921702@147.93.80.20` → `domains/harih.id/public_html` |
| SSH VPS (n8n/WAHA) | `ssh root@31.97.50.197` → `/opt/harih` |
| Rahasia/env | `/opt/harih/.env` di VPS (salinan lokal dev: `vps/.env`, gitignored) |

## 2. Rutinitas harian (±5 menit)

1. Buka WA bisnis — balas pertanyaan CS & pesan testimoni.
2. Cek email/WA owner — ada alert? (kalau tidak ada alert, sistem sehat; semua monitor otomatis: error → WF-00, sesi WA → WF-07, order hilang → WF-08, backup gagal → email).
3. Sekilas Google Sheet `orders` — baris berstatus `MENUNGGU_DATA` > 2 hari? Follow-up manual via WA (nudge otomatis hanya 1× di 24–48 jam).
4. Kolom `wa_status` — ada `GAGAL_*`/`TIDAK_*`? → §5.2.

## 3. Arti alert & tindakannya

| Alert | Artinya | Tindakan |
|---|---|---|
| 🚨 *Workflow GAGAL* (WF-00) | Satu order/proses berhenti di tengah; customer mungkin sudah bayar tanpa menerima apa pun | Buka link eksekusi di alert → lihat node merah → §4 sesuai skenario. Prioritaskan hari itu juga |
| 🚨 *Sesi WhatsApp DOWN* (WF-07, via email) | Semua kiriman WA berhenti; email tetap jalan, order tetap diproses | Scan ulang QR — §5.1. Setelah pulih, kirim ulang pesan ber-`wa_status` gagal — §5.2 |
| ⚠️ *Rekonsiliasi order* (WF-08) | Ada order yang tidak masuk pipeline; **sudah otomatis diproses ulang** | Order aman. Tapi cari penyebabnya: cek webhook WC (§6) & apakah n8n sempat mati |
| 🚨 *Webhook WC tidak aktif* (WF-08 harian) | WooCommerce berhenti mengirim order baru ke n8n | Re-enable webhook — §6. Order selama mati tertangkap rekonsiliasi |
| 🚨 *Backup mingguan GAGAL* | Backup Minggu dini hari tidak selesai | SSH VPS → `tail -50 /opt/harih/backups/backup.log` → jalankan ulang `bash /opt/harih/backup-harih.sh`; masih gagal → hubungi dev |

## 4. Order bermasalah

**4.1 Customer bayar tapi tidak menerima link form** — cek baris di sheet `orders`:
- Baris tidak ada → tunggu ≤ 15 menit (rekonsiliasi WF-08 akan memprosesnya). Masih tidak ada → cek order di WooCommerce benar berstatus processing/completed, lalu hubungi dev.
- Baris ada, `wa_status` gagal → link form sudah terkirim via email; atau kirim manual — §5.2.

**4.2 Status macet `DIPROSES`** (WF-02 gagal di tengah — biasanya sudah ada alert WF-00):
1. Buka riwayat eksekusi n8n → pastikan penyebabnya (mis. WP down sesaat).
2. Di sheet `orders`, ubah status baris itu → `MENUNGGU_DATA`.
3. Minta customer membuka lagi link form & submit ulang (isian foto perlu dipilih ulang).

**4.3 Undangan jadi tapi WA tidak masuk** (`wa_status` = `GAGAL_KIRIM`/`TIDAK_TERDAFTAR`/`TIDAK_VALID`) — email delivery tetap terkirim. Kirim manual isi pesannya dari HP bisnis (link undangan ada di kolom `link_undangan`), atau perbaiki nomor di sheet lalu §5.2.

**4.4 Refund** — proses via dashboard Duitku/WooCommerce sesuai Kebijakan Refund; lalu di sheet: tandai baris `orders` (mis. tambah catatan di kolom status) dan bila ada baris `komisi` terkait yang belum dibayar, hapus/tandai agar tidak ikut payout Senin.

## 5. WhatsApp (WAHA)

**5.1 Scan ulang QR** (sesi logout/`FAILED`):
```
ssh -L 3000:127.0.0.1:3000 root@31.97.50.197
```
Buka `http://localhost:3000/dashboard` → login user `harih`, password = nilai `WAHA_API_KEY` (di `/opt/harih/.env`) → sesi `default` → Restart / scan QR dengan HP nomor bisnis → status kembali `WORKING`.

**5.2 Kirim ulang pesan yang gagal** — cara termudah: kirim manual dari HP bisnis (isi pesan tinggal salin: link form = `https://harih.id/isi-data/?order=<order_id>&key=<token>&paket=<paket>` — semua ada di kolom sheet; link undangan di `link_undangan`). Setelah terkirim, ubah `wa_status` → `TERKIRIM_MANUAL`.

## 6. Webhook WooCommerce mati / order tidak mengalir

wp-admin → WooCommerce → Settings → Advanced → Webhooks → buka webhook `wc-order` → Status: **Active** → Save. Cek juga daftar *Deliveries*-nya untuk melihat kenapa sempat gagal (n8n down? timeout?). Ambang auto-disable sudah dinaikkan ke 25 kegagalan (T1.11), dan rekonsiliasi WF-08 menampung order selama webhook mati — tidak ada order hilang, hanya tertunda ≤ 15 menit.

## 7. Reseller

- **Approve**: klik link "AKTIFKAN" di WA/email notifikasi pendaftar. Idempoten — klik dua kali aman. **Menolak**: abaikan link, lalu hapus/tandai barisnya di sheet `resellers`.
- **Payout tiap Senin**: setelah WF-04 mengirim rekap (±09:00 WIB), transfer komisi sesuai rekap → di sheet `komisi`, ubah baris terkait `UNPAID` → `PAID`. Tidak ada rekap masuk padahal ada penjualan → cek eksekusi WF-04 di n8n.
- **Kode disalahgunakan** (self-deal / spam): hapus kuponnya di WooCommerce → Marketing → Coupons, tandai baris reseller di sheet, informasikan via WA.

## 7b. Masa aktif undangan

Halaman undangan otomatis dinonaktifkan (jadi `draft`) setelah masa aktif paketnya lewat, dihitung **sejak tanggal acara**: Hemat H+7 · Favorit H+30 · Premium 1 tahun. Cron WP harian ±03:00 WIB yang mengerjakannya; undangan **demo dikecualikan** dan tidak pernah mati.

- Customer menerima **peringatan WA 3 hari sebelumnya** (dari WF-05), berisi tanggal berakhir + saran menyimpan screenshot & daftar ucapan.
- Tamu yang membuka link kedaluwarsa mendapat halaman penjelasan ber-status 410 (bukan 404 telanjang) plus tautan ke Kontak.
- **Mengaktifkan kembali / memperpanjang:** wp-admin → Undangan → cari judulnya → ubah status `Draft` → **Publish**. Halaman langsung hidup lagi. Kalau ingin permanen aktif (mis. kompensasi), ubah `tanggal_resepsi` ke tanggal lebih baru **atau** ubah meta `paket` ke `premium` — keduanya menggeser tanggal kedaluwarsa.
- Cek apa yang akan mati hari ini tanpa efek apa pun:
  ```
  wp eval 'print_r(undangan_jalankan_masa_aktif(true));'
  ```
- Aturan hari ada di `wp-content/mu-plugins/undangan-core/masa-aktif.php` (otoritas). Nilainya juga tersalin di WF-05 hanya untuk menghitung waktu peringatan.

## 8. Revisi manual (layanan sesuai paket)

- Jatah: **Hemat berbayar Rp 25.000/pengajuan · Favorit 1× gratis · Premium 3× gratis + prioritas**; di luar jatah, Rp 25.000/pengajuan. Target pengerjaan **≤ 2×24 jam**; Premium didahulukan.
- **Kesalahan sistem/kesalahan kita selalu digratiskan**, semua paket, tanpa batas — jangan menagih untuk hal yang bukan salah customer. Penagihan hanya untuk perubahan yang customer minta.
- Kanal: customer membalas WA delivery → dicatat siapa/kapan (cukup di chat).
- Cara edit: wp-admin → **Undangan** → cari by judul/order_id → ubah field meta (nama, tanggal, lokasi, dst.) → Update. Ganti foto: upload di Media, salin URL barunya ke meta `galeri` (format array JSON). Perubahan tampil setelah cache purge otomatis; kalau belum, LiteSpeed Cache → Purge All.
- Yang TIDAK dilayani revisi: ganti paket (arahkan order baru), ganti tema setelah terbit > 1× (kebijakan CS).

## 9. Backup & restore

- Otomatis tiap Minggu ±02:00 WIB di VPS: `/opt/harih/backups/` (db, uploads mirror, sesi WAHA, data+workflow n8n; retensi 4 minggu). Gagal → alert email.
- **Restore DB WP**: `gunzip -c db-YYYY-MM-DD.sql.gz | ssh -p 65002 u803921702@147.93.80.20 "cd domains/harih.id/public_html && wp db import -"`.
- **Restore n8n/WAHA**: stop container → extract tar ke volume terkait → start. Butuh `N8N_ENCRYPTION_KEY` yang sama (simpan nilainya di password manager, jangan hanya di VPS).
- ✅ **Restore sudah diuji 2026-08-05** memakai dump 2026-08-01: 56 tabel, `siteurl` benar, 3 undangan + 3 produk + 2 order pulih utuh; arsip WAHA (1.659 entri, `webjs/default/` ada) & n8n (`database.sqlite` ada) terbaca; export 8 workflow JSON valid. Diuji di MySQL container sementara di VPS — **produksi tidak tersentuh**.
- **Cara mengulang uji restore** (aman, tidak menyentuh produksi):
  ```
  ssh root@31.97.50.197
  docker run -d --name uji-restore -e MYSQL_ROOT_PASSWORD=x -e MYSQL_DATABASE=ujiwp mysql:8
  gunzip -c /opt/harih/backups/db/db-YYYY-MM-DD.sql.gz | docker exec -i uji-restore mysql -uroot -px ujiwp
  docker exec uji-restore mysql -uroot -px -N -B ujiwp -e 'SELECT COUNT(*) FROM wp_posts;'
  docker rm -f uji-restore
  ```
- ⚠️ **Backup berjalan mingguan (Minggu ±02:00 WIB), jadi selalu ada jendela sampai 7 hari yang belum tercakup.** Yang menyelamatkan: hampir semua bisa dibangun ulang dari repo — halaman legal via `scripts/publish-legal.py`, undangan demo via `scripts/buat-demo.sh`, produk & kategori via `scripts/buat-toko.sh`, aset via generator. Yang TIDAK bisa dibangun ulang: data order & undangan pelanggan riil. Itulah yang sesungguhnya dilindungi backup ini.

## 9b. Baseline performa (G1.7)

**Cara mengukur — dan kenapa bukan dari mesin developer.** Diagnosis 2026-08-06 membuktikan jalur jaringan lingkungan kerja developer bisa menggantung di jabat tangan TLS ke harih.id **dan** n8n.harih.id serentak, sementara host luar baik-baik saja. Artinya angka dari mesin itu tidak bisa dipercaya. Ukur lewat jalur Google → server:

- **PageSpeed Insights** — buka <https://pagespeed.web.dev/> untuk tiap URL (mobile). API `pagespeedonline/v5` tanpa kunci sering kena *"Quota exceeded … Queries per day"* (terjadi 2026-08-07); pakai UI-nya, atau daftarkan kunci API gratis di Google Cloud Console bila mau otomatis.
- **Rantai request** bisa diukur dari mana saja dengan `curl -w` karena yang dibandingkan relatif, bukan absolut.

**Terukur 2026-08-07 — rantai font halaman undangan, sebelum vs sesudah G1.2** (UA iPhone, tema-01):

| | Sebelum (Google Fonts) | Sesudah (self-hosted) |
|---|---|---|
| Permintaan sebelum teks tampil benar | HTML → CSS `googleapis` → woff2 `gstatic` — **serial, 3 langkah, 2 origin baru** | HTML + woff2 **paralel, 1 origin, sudah ter-preload** |
| CSS font | 0,34 dtk · 12,3 KB (mendeklarasikan 30 varian) | — (tidak ada) |
| woff2 | 0,33 dtk · 24,6 KB | 0,19 dtk · 65 KB (menutup weight 400/500/600 sekaligus) |
| Diunduh per tema (latin) | bervariasi per weight yang terpakai | tema-01 **113 KB** · tema-02 **70 KB** · tema-03 **34 KB** |

**Diverifikasi di peramban sungguhan:** ketiga tema nol permintaan ke `googleapis`/`gstatic`; face latin `loaded`, latin-ext `unloaded` (benar — nama demo tidak memuat karakternya); `.mempelai-amp` 46px memakai face **italic sungguhan**, bukan oblique sintetis.

## 10. Kapan menghubungi developer

Alert WF-00 beruntun pada order berbeda · rekonsiliasi menemukan order tertinggal berulang kali tanpa sebab jelas · restore gagal · perubahan kode/tema/workflow apa pun. Sertakan: link eksekusi n8n dari alert + order_id.
