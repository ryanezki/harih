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

## 8. Revisi manual (layanan sesuai paket)

- Jatah: Hemat — (berbayar, kebijakan CS) · Favorit 1× · Premium 3× + prioritas. Target pengerjaan ≤ 1×24 jam; Premium didahulukan.
- Kanal: customer membalas WA delivery → dicatat siapa/kapan (cukup di chat).
- Cara edit: wp-admin → **Undangan** → cari by judul/order_id → ubah field meta (nama, tanggal, lokasi, dst.) → Update. Ganti foto: upload di Media, salin URL barunya ke meta `galeri` (format array JSON). Perubahan tampil setelah cache purge otomatis; kalau belum, LiteSpeed Cache → Purge All.
- Yang TIDAK dilayani revisi: ganti paket (arahkan order baru), ganti tema setelah terbit > 1× (kebijakan CS).

## 9. Backup & restore

- Otomatis tiap Minggu ±02:00 WIB di VPS: `/opt/harih/backups/` (db, uploads mirror, sesi WAHA, data+workflow n8n; retensi 4 minggu). Gagal → alert email.
- **Restore DB WP**: `gunzip -c db-YYYY-MM-DD.sql.gz | ssh -p 65002 u803921702@147.93.80.20 "cd domains/harih.id/public_html && wp db import -"`.
- **Restore n8n/WAHA**: stop container → extract tar ke volume terkait → start. Butuh `N8N_ENCRYPTION_KEY` yang sama (simpan nilainya di password manager, jangan hanya di VPS).
- Uji restore minimal 1× sebelum launch (T4.2) — backup yang tak pernah diuji dianggap tidak ada.

## 10. Kapan menghubungi developer

Alert WF-00 beruntun pada order berbeda · rekonsiliasi menemukan order tertinggal berulang kali tanpa sebab jelas · restore gagal · perubahan kode/tema/workflow apa pun. Sertakan: link eksekusi n8n dari alert + order_id.
