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

## ⏸ CHECKPOINT SESI 2026-08-07 — fase G1 berjalan (5 dari 8 selesai & live)

**Mulai dari sini di sesi berikutnya.** Konteks lengkap + alasan tiap keputusan: [`evaluasi-ide-genz.md`](./evaluasi-ide-genz.md).

**Selesai & terverifikasi live** (`HARIH_VERSION 2.6.0`):

- **G1.7 baseline performa** — dicatat di [`runbook.md`](./runbook.md) §9b. ⚠️ PSI API tanpa kunci kena kuota harian; pakai UI pagespeed.web.dev. Jangan mengukur dari mesin developer (diagnosis jaringan 2026-08-06).
- **G1.2 font undangan di-selfhost** — menghapus rantai serial `HTML → CSS googleapis (0,34 dtk) → woff2 gstatic (0,33 dtk)`. Sumbernya ternyata **variable font**, jadi satu berkas menutup weight 400/500/600. Dipecah latin/latin-ext per `unicode-range`. Dua face **italic** ditambahkan (OFL) karena `undangan.css` memakai italic di empat tempat — tanpa itu ampersand 46px jadi oblique sintetis. Generator: `scripts/buat-font-web.py`.
- **G1.1 mode gelap** — 9 halaman toko + `/isi-data/`. **21 pasangan kontras diukur**, terendah 5,23:1. `--c-accent` dipecah jadi peran latar + `--c-accent-teks` (satu warna mustahil ≥4,5:1 di dua sisi). Anti-kedip lewat script inline `data-no-optimize` di `<head>`. **Halaman undangan sengaja tidak ikut** — temanya barang yang dibeli.
- **G1.3 "coba nama kalian"** — form di beranda → demo terbuka dengan nama pengunjung. Sisi klien, **demo saja** (`window.UNDANGAN.demo`), diuji dengan undangan uji non-demo bahwa parameternya diabaikan total.
- **G1.6 tombol konfirmasi RSVP ke WA mempelai** — arah tamu → mempelai, **nomor bisnis hariH tidak tersentuh** (sesi WAHA tidak berisiko).
- **Bonus, temuan sampingan:** menghapus undangan meninggalkan kartu `og:image`-nya di uploads selamanya — memuat nama mempelai + potongan foto, tetap bisa diakses publik. Ditutup dengan `before_delete_post` di `og.php`.

**Fase G1 SELESAI 8/8** — paket terakhir (G1.4 · G1.5 · G1.8 · G1.9 · rekap WF-05) dikerjakan 2026-08-07, `HARIH_VERSION 2.7.1`, 9 workflow tetap aktif:

- **G1.4 galeri kolase** — meta `galeri_tata` (`slider` bawaan | `kolase`). Grid mozaik tanpa media query; foto pertama mengambil dua kolom. Lightbox dipakai bersama kedua tata letak.
- **G1.5 koordinat + Waze** — meta `koordinat`/`koordinat_akad`, divalidasi **rentangnya** (bukan cuma bentuknya) + tolak `0,0`. Peta tersemat memakai koordinat bila ada; tombol Waze hanya muncul bila koordinatnya ada — tombol navigasi yang menyesatkan lebih buruk daripada tidak ada.
- **G1.8 dompet digital** — meta `dompet` (`Nama|URL` per baris), **host di-whitelist** (GoPay/DANA/OVO/ShopeePay/LinkAja, https saja). Terbukti: host asing & `http://` dibuang diam-diam.
- **G1.9 WF-02** — keempat field diteruskan dalam **satu kali** import.
- **Rekap RSVP harian** — endpoint baru `undangan/v1/rekap-harian` (butuh `edit_posts`) + node `Ambil Rekap RSVP` di WF-05. Dikirim ke **nomor pembeli** (kontak yang sudah berkorespondensi), hanya bila ada RSVP baru 24 jam terakhir.
- **Resolusi koordinat otomatis** dari `gmaps_url` dikerjakan **di WP saat meta ditulis**, bukan di WF-02 — menghindari bedah node bercabang, dan tamu pertama tidak menunggu permintaan ke Google. Host pemendek di-whitelist (anti-SSRF), nilai yang sudah ada tidak pernah ditimpa, gagal apa pun tidak fatal.

**Bug yang ketemu & ditutup saat mengujinya:** section amplop hanya dirender bila ada rekening/QRIS — pasangan yang **hanya** mengisi dompet digital (atau hanya alamat kado) kehilangan seluruh amplopnya.

### Audit mode gelap lintas halaman (2026-08-07) — menutup celah verifikasi G1.1

Saat G1.1 dikerjakan, mode gelap hanya diverifikasi di beranda & `/harga/`. Tujuh halaman lain memakai `katalog.css` — termasuk halaman bertoken yang justru dilihat **pembeli yang sudah membayar**. Audit dijalankan di peramban dengan pengukuran kontras yang mengompositkan alpha berlapis:

- **Diperiksa runtime & bersih:** `/satuan/` · `/jadi-reseller/` · `/kontak/` · `/rekap/` · `/isi-data/` (input, textarea, select semuanya 12,58:1).
- **Diperiksa statis:** 56 aturan komponen `.rekap-*` (13) · `.tamu-*` (23) · `.proof-*` (16) · `.upsell-*` (4) — **nol warna literal**, semuanya token. Jadi komponen berdata di halaman itu akan mengikuti palet gelap dengan benar.
- **BELUM diverifikasi runtime:** komponen *berdata* di `/tamu/`, `/proof/`, `/upsell/` — ketiganya menuntut order WooCommerce yang benar-benar ada. Membuat order berstatus `processing` akan **memicu WF-01** dan mengirim email + WhatsApp sungguhan, jadi sengaja tidak dilakukan. Cek berikutnya paling murah dilakukan **menumpang order uji F0.2** begitu Duitku disetujui.

**Dua "temuan" yang ternyata cacat alat ukur, bukan cacat CSS** — dicatat supaya tidak diulang:
1. Kontras dihitung tanpa mengompositkan alpha → `.paket-badge` terbaca 1,37:1 (padahal 6,13:1). Latar `rgba(...,.12)` harus dikomposit ke latar opaque di atasnya lebih dulu.
2. Parser warna tidak mengenal sintaks `color(srgb r g b / a)` yang komponennya **0–1**, bukan 0–255 → terbaca 7,72:1 (padahal 5,86:1).

**Satu perbaikan nyata:** dua `rgba()` literal di `isi-data.css` memakai nilai palet **mode terang** sehingga tidak ikut token saat gelap — diganti `color-mix()`. Kontrasnya memang sudah aman sejak awal; ini soal konsistensi palet, dan disebutkan supaya tidak terbaca sebagai perbaikan aksesibilitas.

### 🔴 Regresi yang saya buat sendiri di WF-05 — sudah ditutup, tapi polanya wajib diingat

Node `Ambil Rekap RSVP` diletakkan **di tengah rantai** WF-05. Endpoint-nya mengembalikan `[]` saat tidak ada RSVP baru — dan **node HTTP n8n memecah respons array jadi satu item per elemen**, jadi `[]` = **nol item**, dan node berikutnya **tidak dijalankan sama sekali**. Artinya: di hari tanpa RSVP baru (yaitu hampir setiap hari), SELURUH reminder harian ikut mati — nudge belum-isi-data, pengingat H-3, dan peringatan masa aktif. Tidak ada pesan galat; workflow-nya cuma berhenti diam-diam.

Ditutup dua lapis: endpoint kini **selalu** membalas objek `{"data": {...}}` (tidak pernah array telanjang), dan node diberi **`alwaysOutputData: true`**. **Aturan umum: jangan pernah menaruh node yang bisa menghasilkan nol item di tengah rantai** — taruh di ujung, atau pastikan bentuk responsnya selalu satu item.

**Diverifikasi empiris**, bukan diasumsikan: jadwal diubah sementara jadi tiap menit, dan eksekusi `4121` menunjukkan `Baca Orders → Ambil Rekap RSVP → Susun Pesan Harian` ketiganya berjalan dengan rekap kosong, workflow `status=success`. Jadwal `0 8 * * *` sudah dipulihkan & diverifikasi; 9 workflow aktif. Cara mengujinya dicatat di [`runbook.md`](./runbook.md) §9c.

### ✅ Google Sheet `orders` dibersihkan 2026-08-07 — sebelumnya ia MEMICU kiriman WhatsApp

Klaim "produksi bersih dari data uji" selama ini benar untuk WordPress (3 undangan demo) tapi **tidak untuk Google Sheet**: kelima barisnya sisa uji 6 Agustus (`uji@`, `b@`, `proof@`, `tamu@`, `rekap@example.com`, semuanya `MENUNGGU_DATA`, dua di antaranya bernomor `628123456789`) — sementara WooCommerce punya **nol order**.

Konsekuensinya nyata, bukan sekadar kotor: saat WF-05 diuji, ia membangun pesan `nudge #82` dan **benar-benar mencoba mengirim WhatsApp** ke nomor palsu itu; WAHA membalas `500 · No LID for user`. Mengirim berulang ke nomor tak dikenal adalah persis pola yang catatan kebijakan kita sendiri larang, karena itulah yang membuat sesi WhatsApp Web di-ban — dan sesi itu memikul seluruh kanal delivery.

**Dihapus atas permintaan owner** (baris 2–6 dalam satu `deleteDimension`, dengan penjagaan: batal bila ada satu saja baris non-`example.com`). Tab `orders` kini **hanya header**; `resellers` & `komisi` memang sudah kosong. Klaim "produksi bersih" sekarang benar untuk kedua sisi.

**Konsekuensi yang perlu diketahui:** dengan sheet kosong, `Baca Orders` menghasilkan nol item sehingga **WF-05 jadi no-op** — itu benar dan tidak berbahaya (tidak ada order = tidak ada yang perlu diingatkan), dan sembuh sendiri begitu order asli pertama masuk.

*Catatan desain untuk nanti:* WF-01 memverifikasi nomor lewat WAHA `check-exists` sebelum mengirim; **WF-05 tidak**. Menambahkannya akan mencegah kiriman ke nomor tidak terdaftar sama sekali — layak dipertimbangkan, meski penyebab sesungguhnya kali ini adalah data uji yang tertinggal.

### ⚠️ Dua temuan operasional dari sesi ini — baca sebelum menyentuh n8n lagi

1. **Import n8n bisa diam-diam memakai berkas BASI.** `scp` ke `/tmp` server gagal (`Permission denied` — ada berkas milik uid lain + sticky bit) **tapi `import:workflow` tetap melaporkan sukses**, memakai berkas 6 Agustus yang tergeletak di sana. WF-02 live sempat mundur ke versi lama tanpa satu pun pesan galat. Prosedur yang benar sekarang ada di [`n8n/workflows/README.md`](../n8n/workflows/README.md) — unggah ke `/root/wf-import/`, nama berkas baru di container, **periksa isinya dari dalam container sebelum import**, lalu verifikasi hasil ekspor yang HIDUP. Sudah dipulihkan & diverifikasi identik dengan repo.
2. **✅ Pin WAHA dibetulkan & dinaikkan ke 2026.7.2 (2026-08-07).** `docker compose up -d` sempat **selalu gagal** karena pin `devlikeapro/waha:2026.6.2`. **Koreksi atas diagnosis awal saya:** tag itu bukan dihapus — skema tag WAHA adalah `<varian>-<versi>` (`latest-2026.6.2`, `chrome-2026.6.2`, `noweb-*`, `gows-*`, `arm-*`), jadi **versi polos tidak pernah ada** dan pin itu tidak pernah resolve sejak ditulis; container sebenarnya berjalan dari tag bergerak `latest`. Varian kita: **`latest-*`** (Chromium + engine WEBJS).
   **Upgrade dijalankan atas permintaan owner**, dengan urutan yang bisa dibalik: volume sesi (331 MB) dicadangkan ke `/opt/harih/backups/waha-preupgrade/` → image ditarik lebih dulu → `docker compose up -d --no-deps waha` (n8n tidak disentuh). Hasil: **versi 2026.7.2, engine/tier/browser sama, sesi `default` kembali `WORKING` dalam ±25 detik TANPA scan QR ulang.** Uji fungsional `check-exists` benar (nomor owner ada + ber-LID; nomor palsu `numberExists:false`). Retag lokal sementara sudah dihapus. Pin n8n (`2.29.10`) sah dan cocok dengan versi berjalan.

**Keputusan owner 2026-08-07:** AI copywriter (G2.2) memakai **Gemini Flash lewat OpenRouter**, bukan Claude — slug model wajib diverifikasi ke daftar OpenRouter saat implementasi, simpan sebagai env `OPENROUTER_MODEL`; **disk 25 GB bebas** (tapi yang masih harus dicek untuk G2.1 adalah **bandwidth**, bukan disk); **escrow tidak jadi**; **rekap RSVP harian dipasang**.

**Kebersihan produksi — TUNTAS 2026-08-07.** Ucapan lama `ID 55` ("ryan / amin", 2026-08-05) di buku tamu demo tema-02 sudah dihapus atas permintaan owner; ia satu-satunya `ucapan` di seluruh produksi, dan tampil ke calon pembeli yang membuka demo. Sekarang: **0 ucapan · 3 undangan (semuanya demo) · 3 kartu OG · sheet `orders` hanya header · 0 order WooCommerce.** Dinding ucapan di demo diverifikasi kosong lewat REST dan lewat peramban; form RSVP & tombol WA mempelai tetap berfungsi.

---

## Kondisi sebelumnya (checkpoint sesi 2026-08-06)

**Yang berubah paling besar sesi ini:** owner ternyata punya **percetakan & peralatan sendiri** → fase subkontrak gugur, dan produk cetak berubah dari kartu QR jadi **undangan lipat (A4→A5) + amplop bernama tamu** (50/100/150 per paket, harga tetap). Marjin per jam sudah diukur: percetakan reguler Rp 100–200rb/jam vs undangan hybrid **±Rp 580rb/jam** → gerbang kelayakan lulus 3–6× lipat, dengan ruang membengkak sampai ±13 jam/order.

**Jalur cetak sudah lengkap secara kode & operasional** (F3 tuntas 11/11; F4.1, F4.5, F4.6, F4.7 selesai): produk & kategori, checkout beralamat + gratis ongkir se-Indonesia, gerbang H-21 & kuota 8/bulan, snapshot beku + persetujuan proof ber-hash, daftar tamu → amplop bernama + link personal massal, rekap RSVP + CSV, antrean produksi, komisi per jenis produk, upsell bertoken + pengingat H+3/H+12. **9 workflow n8n aktif.**

**Yang menahan uang, semuanya di tangan owner:**
1. **Approval Duitku production** — gerbang tunggal semua uang. ⚠️ profil merchant menyebut Rp 99–299 ribu, padahal paket cetak sampai Rp 5,9 juta — beri tahu Duitku.
2. **Cetak satu sampel lengkap** — menjawab bobot nyata (sekarang masih tebakan 2/4/7 kg), waktu lipat, uji pindai QR, dan **apakah mesin creasing sanggup** (800 lipatan/bulan; kalau manual, hitungan marjin batal).
3. **Cek mekanisme refund Duitku** — Garansi Tepat Waktu sudah mengikat sejak halaman harga tayang.

**Catatan performa 2026-08-06 — SUDAH TERJAWAB, bukan masalah situs.** Sepanjang sesi, permintaan ke situs berkali-kali timeout. Dugaan pertama saya (kepadatan shared hosting) **KELIRU**; setelah diukur bertahap:
- DNS selalu **0,002 dtk** · TCP tersambung normal · yang menggantung adalah **jabat tangan TLS**;
- kegagalan mengenai **harih.id (Hostinger) DAN n8n.harih.id (VPS) serentak** — dua server, dua penyedia;
- kontrol ke host luar (`google.com/generate_204`) **tidak pernah gagal** di jendela yang sama;
- melewati DNS (langsung ke IP) tetap gagal.
Kesimpulan: hambatan ada di **jalur jaringan lingkungan kerja saya**, bukan di situs atau VPS. Saat merespons, situs menjawab 0,2–0,5 dtk. **Jangan naikkan paket hosting atas dasar ini.** Konsekuensi praktis: hasil uji otomatis (`cek-live.sh`) bisa memunculkan `HTTP 000` palsu — **ulangi per URL sebelum menyimpulkan ada regresi**.

**Gerbang yang masih dipasang sengaja:** produk cetak & satuan **disembunyikan dari `/shop/`** sampai satu order uji internal tuntas penuh; halaman `/harga/` & `/satuan/` tetap menampilkannya.

**Yang belum dibangun & memang menunggu order nyata:** engine render SVG→PDF (F4.3), imposition (F4.4), upsell otomatis penuh (F4.8), fase vendor/white-label (F5). Membangunnya sekarang = menebak bagian mana dari produksi yang layak diotomasi.

**Halaman bertoken (semua memakai HMAC yang sama, 403 tanpa token):** `/isi-data/` · `/upsell/` · `/proof/` · `/tamu/` · `/rekap/`.

---

## Kondisi sebelumnya (diverifikasi langsung 2026-08-05)

**Platform digital hidup dan terbukti end-to-end.** Checkout → pembayaran → undangan terbit otomatis → delivery WA+email, berjalan sendiri sejak 22 Juli. 8 workflow aktif, backup mingguan jalan, smoke test `scripts/cek-live.sh` 21/21 hijau, produksi bersih dari data uji (hanya 3 undangan demo).

**Sudah selesai** *(ringkas — rinciannya di git log)*: infrastruktur & hardening · 3 tema + demo per tema · form isi data + kompresi foto · 8 workflow otomasi · halaman legal · masa aktif otomatis · aset visual berlisensi + kartu OG per tema · logo · pustaka musik 3 track · SEO dasar · GA4 · backup repo ke GitHub private · QA formal yang menemukan 3 cacat live (countdown tidak pernah jalan, atribut `hidden` dikalahkan CSS, daftar ucapan ter-cache 7 hari).

**2026-08-05 — evaluasi desain owner (putaran 2) diterapkan** *(v1.4.0)*: **nuansa keagamaan** — undangan kini punya 7 pilihan (Islam · Kristen · Katolik · Hindu · Buddha · Konghucu · **tanpa unsur agama**), masing-masing dengan salam pembuka, kutipan, salam penutup, dan tampil-tidaknya tanggal Hijriah; ketiga teks bisa ditimpa per undangan lewat meta (`salam_teks`/`ayat_teks`/`ayat_sumber`) sehingga CS bisa menyesuaikan tanpa kode · tombol `.ics` **dihapus** (tidak lazim di Indonesia & tidak jalan di banyak peramban HP), tombol kalender diberi jarak 34px dan diganti jadi **"Catat Tanggalnya"** · tautan Instagram mempelai diberi gaya (sebelumnya mewarisi biru bawaan peramban dengan ikon kebesaran) · **`<meta name="format-detection" content="telephone=no">`** — akar masalah "nomor rekening biru muda hampir tak terbaca": iOS mengubah deret angka jadi tautan telepon, bukan salah warna tema · field WhatsApp di RSVP dihapus (undangan disebar via WA, kontak tamu sudah dipegang) · Kisah Kami: parser lini masa dilonggarkan (label bebas ≤22 karakter, minimal 2 baris) + tombol contoh "lini masa"/"paragraf" di form · nuansa **tidak melekat pada tema** (dua meta terpisah, template salam dipakai bersama; diverifikasi matriks 3 tema × 7 nuansa) — demo kembali memakai nuansa yang sama agar yang terbandingkan hanya temanya, dan `?nuansa=` pada **link demo saja** dipakai memperlihatkan nuansa lain di tema mana pun · **QRIS demo jadi aset lokal** — supaya etalase tidak menunggu domain pihak ketiga saat menampilkan fitur yang dijualnya. *(Koreksi 2026-08-06: alasan awal "layanan mengembalikan PNG kosong" KELIRU — QR-nya valid, 498 byte wajar untuk PNG 1-bit. Perubahannya tetap dipertahankan atas alasan kemandirian, bukan kerusakan.)*

**2026-08-05 — pemilih nuansa di halaman demo** *(v1.5.2)*: kotak mengambang di kiri-bawah undangan demo (berpasangan dengan tombol musik di kanan) berisi 7 pilihan nuansa + kapsul "pratinjau demo" supaya jelas ia alat bantu, bukan bagian undangan tamu. Berpindah nuansa memuat ulang halaman dengan `?nuansa=…&buka=1` sehingga gerbang langsung terbuka dan halaman mendarat tepat di blok salam — tanpa itu pembanding harus menekan "Buka Undangan" dan menggulir ulang tiap kali. **Tidak pernah dirender di undangan pelanggan** (diverifikasi dengan undangan uji non-demo). Countdown juga dikembalikan ke **setelah halaman pembuka, sebelum salam** atas keputusan owner: kalimat salam yang berakhir "…pada pernikahan kami:" tetap bersambung ke nama mempelai, dan hitung mundur jadi kait di layar kedua.

**2026-08-05 — urutan & hierarki dibetulkan** *(v1.4.2)*: **countdown dipindah ke SETELAH rangkaian acara**. Salam ditutup kalimat *"…mengundang Bapak/Ibu/Saudara/i pada pernikahan kami:"* — titik dua itu menjanjikan nama mempelai, bukan angka hitung mundur; urutan sekarang mengikuti pertanyaan pembaca: salam → **siapa** (mempelai) → **kapan & di mana** (acara) → tinggal berapa hari (countdown). Tanggal & jam acara juga diperbesar: tanggal `clamp(20px, 5.4vw, 25px)` font display (sebelumnya seukuran teks biasa) dan jam 17px ber-tracking (sebelumnya 14px, **lebih kecil dari nama gedung**) — pertanyaan pertama penerima undangan setelah "siapa" adalah "kapan", karena itu yang menentukan ia bisa hadir. Ukuran diukur di HP 375px agar nama bulan terpanjang tetap satu baris di ketiga tema, termasuk Prata yang paling lebar.

**2026-08-06 — evaluasi desain eksternal diterapkan penuh** *(HARIH_VERSION 1.2.0)*: salam islami + QS. Ar-Rum 21 + tanggal Hijriah (Intl, sisi klien) · RSVP diperluas (jumlah tamu, sesi akad/resepsi/keduanya, WA opsional — **WA disimpan untuk mempelai, tidak pernah keluar di API publik**) · dinding ucapan scrollable · alamat kado + salin · urutan anak + tautan Instagram · dress code ber-swatch · turut mengundang · rundown per jam · tombol Apple/.ics · judul lagu di penutup & tombol musik · catatan maaf nama/gelar · embed YouTube → facade thumbnail (klik baru memuat iframe) · kontras gate + overflow nama panjang + WCAG `--c-ink-soft` tema-03 dibereskan. Sisa 4 poin evaluasi + cacat `og:image` (rasio salah → preview WA terpotong) dirinci di **FU**. Semua toggle-able per undangan (`salam_islami`, field opsional). Terverifikasi visual + fungsional live, smoke 21/21.

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

**2026-08-06 — PERUBAHAN BESAR: percetakan & alat milik sendiri, produk cetak jadi undangan lipat.** Owner ternyata sudah punya bisnis percetakan dengan peralatan lengkap, jadi **fase subkontrak (F1.2) gugur** — tidak ada modal alat yang perlu dikeluarkan dan tidak ada pihak ketiga yang perlu dicari. Produk cetak juga berubah dari kartu QR 55×85mm menjadi **undangan lipat dua (A4→A5) beserta amplop bernama tamu**: penerima undangan cetak umumnya orang tua & sesepuh, dan kartu yang isinya hanya QR menuntut mereka membuka HP dulu. Jumlah per paket **turun** (150/150/200 → 50/100/150) sementara harga paket tetap — mutu naik, jam mesin turun.

> ✅ **Metrik pengendali sudah terjawab (F1.7a, 6 Agu):** percetakan reguler Rp 100–200rb/jam, undangan hybrid ±Rp 580rb/jam — **lulus 3–6× lipat**, dengan ruang membengkak sampai ±13 jam/order sebelum kalah dari pekerjaan reguler. Catatan aslinya:
> ⚠️ **Metrik pengendali berubah total.** Patokan lama "≥ Rp 600rb/jam" tidak lagi relevan; kapasitas bukan tak terbatas melainkan **jam mesin yang selama ini dipakai order percetakan reguler**. Undangan wajib **mengalahkan marjin per jam bisnis cetak yang sekarang** — kalau tidak, ini hanya memindahkan uang dari kantong kiri ke kanan sambil menambah risiko deadline pernikahan. **Angka itu belum ada dan harus dihitung lebih dulu** (F1.7a).

**2026-08-06 — homepage diredesain (handoff "Gen-Z editorial", v2.0.1).** Front page ditulis ulang mengikuti berkas handoff: DM Serif Display beraksen italic berwarna + Figtree (keduanya **self-hosted**, bukan CDN Google), foto arch bersticker, strip marquee, kartu chunky, **carousel tema scroll-snap** (bukti visual produk di atas fold), band undangan cetak, dan CTA penutup. Responsif **tanpa satu pun media query** — `clamp()`, `auto-fit`, `flex-wrap`, `scroll-snap`. Lima temuan review UX ikut dieksekusi: bukti visual produk (A1), baris trust pembayaran di hero (A2), kontras teks **5,29–9,06:1** — semuanya lolos AA (A3), filter tamu **bisa di-reset dengan klik kedua** (A4), dan CTA penutup (A5). `katalog.css` dipakai 8 halaman, jadi paletnya dipusatkan di `:root` — `/harga/`, `/satuan/`, dan halaman bertoken ikut memakai tampilan baru tanpa perubahan markup. Gerak dimatikan penuh saat `prefers-reduced-motion`.

**2026-08-06 (lanjutan) — konsep desain diseragamkan ke SEMUA halaman publik + identitas pribadi dilepas dari halaman kontak** *(v2.2.1)*. Halaman kontak & 3 halaman legal sebelumnya dirender tema induk Astra — satu-satunya bagian situs yang tampil beda dari brand, padahal justru ke sanalah pembeli pergi mencari kepastian sebelum membayar. Kini memakai template `page-teks.php` bergaya sama. Nav & footer diangkat jadi komponen bersama (`template-parts/toko/`) yang dipakai 5 halaman, supaya tidak pelan-pelan melenceng lagi seperti sebelumnya. Form `/isi-data/` ikut memakai token & font yang sama — pemesan yang baru membayar tidak boleh merasa berpindah situs. Google Fonts kini **hanya** dipakai halaman undangan; seluruh halaman toko self-hosted.
  **Halaman kontak:** nama pribadi & alamat domisili dihapus (usaha berjalan online) — diganti identitas usaha, wilayah layanan, dan pernyataan bahwa tidak ada kantor tatap muka, dengan catatan bahwa data lengkap penanggung jawab tersedia bagi pihak berwenang & mitra pembayaran lewat email. ⚠️ **Perlu diingat saat mengurus merchant:** Duitku dan sebagian mitra biasanya meminta alamat usaha di dokumen aplikasi — yang dihapus di sini hanya tampilan publik, bukan data yang diberikan ke mitra.

**Menunggu keputusan owner:** daftar lengkap + saran per poin ada di [`konfirmasi-owner.md`](./konfirmasi-owner.md) — dibuat 2026-08-06 setelah F3, dipimpin satu celah mendesak (paket cetak sudah bisa dibeli langsung tanpa konfirmasi slot).

**2026-08-06 — evaluasi ide "konsep Gen Z" dari teman owner:** analisis 11 poin + rencana per perubahan ada di [`evaluasi-ide-genz.md`](./evaluasi-ide-genz.md). Ringkasnya: 2 poin sudah ada, **5 diterapkan** (fase **G1**, ±3–3,5 hari, nol rupiah, tidak menyentuh desain undangan yang dibekukan — dark mode halaman toko · self-host font undangan · "coba nama kalian" di beranda · galeri kolase · koordinat+Waze · tombol WA mempelai di RSVP · dompet digital di amplop), **3 digerbang F0.3** (fase **G2** — AI copywriter, video cover Premium, pratinjau langsung), dan **4 ditolak dengan pengganti**: Spotify sebagai musik latar (tidak bisa autoplay + menembus whitelist `musik_url`), RSVP masuk ke nomor WA bisnis (**risiko ban sesi WAHA = 9 workflow mati**), Supabase (datastore ketiga; migrasi Postgres sudah direncanakan), escrow amplop digital (perilaku agregator/PJP + risiko akun Duitku — gerbang tunggal semua uang). 👤 Empat keputusan owner tercantum di akhir dokumen itu.

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

## FU — Undangan: sisa evaluasi & preview WhatsApp *(produk digital, tanpa modal)*

*Sumber: evaluasi eksternal. Seluruh 17 poinnya diverifikasi baris-per-baris ke situs live pada 2026-08-05 — bukan diasumsikan.*

**Sudah live, 13 dari 17 poin** *(v1.2.0, dideploy 2026-08-05; bukti = HTML live demo tema-03)*: salam Assalamu'alaikum · ayat QS. Ar-Rum 21 · Wassalamu'alaikum penutup · tanggal Hijriah · RSVP jumlah tamu + sesi + WhatsApp · dinding ucapan yang bisa di-scroll · alamat kirim kado + tombol salin · "Putra kedua dari…" + ikon Instagram · dress code ber-swatch · turut mengundang · rundown per jam · tombol Apple/.ics · judul lagu · catatan maaf nama & gelar · state setelah countdown habis (`#countdown-done`, sudah ada sejak awal).
> **Kenapa pengevaluasi tidak melihatnya:** WhatsApp meng-cache preview per URL dan LiteSpeed mengirim header cache panjang ke browser tamu. Saat menunjukkan ulang, **selalu tambahkan `?v=2`** di belakang link. Ini bukan kasus sekali ini saja — sudah dua kali terjadi (evaluasi harga "Rp 99 ribu" juga sudah diperbaiki saat dilaporkan).

**Ditemukan saat verifikasi & langsung diperbaiki** *(v1.2.2)*: JS menyembunyikan tombol kalender lewat selektor `.cd-cal` yang sudah berganti nama jadi `.cd-cal-baris` saat tombol `.ics` ditambahkan — akibatnya setelah acara lewat, countdown hilang tapi tombol kalender tetap tampil. Regresi dari perubahan hari yang sama.

**Kenapa empat sisanya belum:** bukan terlewat, tapi beda kelas pekerjaan. Tiga belas yang selesai adalah *section template* — data sudah ada, tinggal dirender. Sisanya butuh hal yang belum ada: pembangkit gambar, halaman berautentikasi untuk mempelai/panitia, atau keputusan model data baru. Urutannya di bawah mengikuti disiplin uang dokumen ini: yang murah & langsung menaikkan konversi dikerjakan sekarang (justru itu yang dibutuhkan gerbang F0.3), yang berupa fitur jualan digerbang.

- [x] **FU.1** **Kartu `og:image` per undangan** *(poin 14)* → **SELESAI & LIVE 2026-08-05**
  **Masalahnya:** undangan bergaleri memakai **foto pertama apa adanya** sebagai `og:image`. Foto pemesan hampir selalu potret (contoh produksi: 1073×1600, rasio 0,67) sementara WhatsApp memotongnya ke ±1,91:1 — yang tampil hanya pita tengah foto, tanpa nama, tanpa tanggal. *(Koreksi klaim awal saya: halaman undangan **tidak** mengirim `og:image:width/height` sama sekali — bukan salah angka, tapi tidak ada angka. Sekarang dikirim dan benar.)*
  **Kenapa nomor satu:** ini gambar yang muncul **setiap kali pemesan menyebar undangannya** — pengganda paling langsung untuk klik, dan biayanya nol. Kartu berbrand 1200×630 yang sudah kita punya hanya dipakai bila galeri kosong, jadi justru undangan berbayar-berfoto yang tampil paling buruk.
  **Yang dibangun:** `mu-plugins/undangan-core/og.php` — komposit 1200×630 dengan GD: foto di-cover-crop (**potret dipotong dari 28% atas**, bukan tengah — subjek foto pernikahan ada di paruh atas) + gradasi khas tema + nama mempelai, tanggal, dan wordmark memakai **font tema yang sebenarnya** (Cormorant · Karla · Prata+Manrope, ikut di `aset/font/`, lisensi OFL dicatat di `aset-lisensi.md`). Teks memakai bayangan lembut: gradasi saja gagal saat gaun/buket putih berada tepat di belakang nama.
  **Invalidasi tanpa hook:** nama berkas memuat hash isi kartu (`{id}-{hash}.jpg`) — data berubah, berkas baru; berkas versi lama undangan itu dihapus otomatis. Ini disengaja karena WF-02 menulis meta lewat REST, jalur yang tidak lewat hook wp-admin.
  **Terverifikasi:** ketiga tema + jalur tanpa galeri (paket Hemat → kartu berwarna tema, blok teks ditengahkan, bingkai rambut emas) menghasilkan 1200×630 (~45–90 KB, ±55 ms sekali per versi), `og:image:width/height/alt` kini dikirim dan benar, halaman live memuat URL kartu. Undangan uji tanpa foto sudah dihapus kembali.

- [x] **FU.2** **Peta tersemat + info parkir/valet** *(poin 10)* → **SELESAI & LIVE 2026-08-05** *(v1.3.0)*
  **Apa:** sekarang hanya tombol yang melempar tamu keluar ke aplikasi Maps. Tambahkan peta tersemat per lokasi (akad & resepsi bisa beda) + kolom catatan bebas untuk parkir/valet/pintu masuk.
  **Kenapa:** tamu yang belum kenal gedung memutuskan berangkat berdasar dua hal — jauhnya dan parkirnya. Keluar aplikasi = keluar undangan.
  **Yang dibangun:** facade peta per kartu acara (akad & resepsi punya petanya masing-masing) dengan pola yang sama seperti live streaming — `iframe` Google baru disuntik saat **diklik/Enter**; kueri dirakit dari nama+alamat venue (embed tanpa API key). Rasio dikunci 16/10 supaya tata letak tidak melompat saat iframe menggantikan facade. Field baru `catatan_lokasi` + `catatan_lokasi_akad` (cpt → form → WF-02 → demo).
  **Terverifikasi:** muat segar = **nol permintaan ke Google** (dua kemunculan `maps.google.com` di HTML hanyalah `href` tombol, bukan request); klik → iframe muncul. Tombol luar diganti namanya jadi "Petunjuk Arah" karena tugasnya sekarang navigasi, bukan "lihat peta" — label lama membungkus dua baris di HP.

- [x] **FU.3** **Kisah Kami sebagai lini masa bertanggal** *(poin 9)* → **SELESAI & LIVE 2026-08-05** *(v1.3.2)*
  **Apa:** `love_story` sekarang satu blok teks (`love-story.php` merender `nl2br` apa adanya). Ubah jadi daftar bertanggal: "2019 — Pertama bertemu", "2023 — Lamaran".
  **Kenapa:** timeline dibaca; paragraf panjang di HP dilewati — persis alasan italic panjang tadi dikeluhkan.
  **Yang dibangun:** parser baris (`2019 — Pertama bertemu`, juga menerima `Mei 2019 · …`) → lini masa bergaris dengan titik emas per babak; **satu baris saja tanpa tahun → seluruhnya kembali jadi prosa**, jadi undangan lama tidak berubah tampilan tanpa diminta. Tanpa migrasi, tanpa field baru. Form mengajarkan formatnya lewat placeholder + catatan.
  **Terverifikasi** dengan undangan uji terpisah (bukan demo): prosa → prosa · berformat tahun → 4 item lini masa · campur → prosa. Gutter diperbaiki setelah verifikasi visual: section ini tanpa padding samping (foto full-bleed) sehingga titik emas sempat terpotong tepi layar.

- [ ] **FU.4** **Sesi kedatangan tamu (shift)** *(poin 8b)*
  **Apa:** berbeda dari pilihan sesi di RSVP (yang sudah ada — tamu memilih akad/resepsi). Ini **mempelai membagi tamu ke jam kedatangan** ("Sesi 1: 11.00–13.00") supaya gedung tidak menumpuk.
  **Kenapa ditunda di bawah FU.1–FU.3:** hanya relevan untuk resepsi besar, dan baru benar-benar berguna kalau link personal per tamu sudah bisa dibuat massal (FU.6) — tanpa itu mempelai harus menyalin link satu per satu.
  **Langkah:** parameter link personal (`?sesi=1`) menimpa tampilan jam di kartu acara; daftar sesi disimpan sebagai field baru.
  **Selesai bila:** satu link personal menampilkan jam kedatangan tamu tersebut, sementara halaman tanpa parameter tetap menampilkan jam umum.

> **Gerbang: tiga di bawah dikerjakan setelah F0.3 (10 pembeli asing).** Ketiganya fitur yang *dijual*, bukan perbaikan tampilan — membangunnya sebelum ada yang membayar mengulang kesalahan urutan uang yang sudah dikoreksi penasihat bisnis. Ketiganya juga bahan Tier B rencana hybrid, jadi biayanya terbayar dua kali.

- [x] **FU.5** **Rekap RSVP + ekspor** *(poin 16)* → **SELESAI & LIVE 2026-08-06**
  **Apa:** halaman bertoken untuk mempelai: total tamu per sesi, daftar hadir/tidak/ragu, unduh CSV. **Satu-satunya tempat nomor WhatsApp tamu (`wa_rsvp`) boleh tampil** — endpoint publik sengaja tidak pernah mengembalikannya.
  **Kenapa dikerjakan meski digerbang F0.3:** ini bukan fitur baru yang dijual, tapi **lubang pada fitur yang sudah dijual di semua paket**. RSVP ada sejak awal, tapi pembelinya tidak pernah punya cara memakainya — dinding ucapan di undangan hanya menampilkan pesan, tanpa hitungan, tanpa pembagian sesi, dan tidak bisa dibawa ke katering.
  **Yang dibangun:** `/rekap/?order=&key=` bertoken. Angka besar dulu (mempelai membukanya untuk menjawab satu pertanyaan: berapa orang), lalu tabel. **Yang dihitung adalah jumlah tamu yang dibawa**, bukan jumlah pengisi form — dan dipecah jadi **hadir di akad** vs **hadir di resepsi** (yang memilih "keduanya" masuk ke dua-duanya), persis angka yang dipakai memesan porsi katering & kursi. CSV ekspor menyertakan **pesan tamu** meski tidak ditampilkan di tabel (di layar ia membuat baris jadi panjang, tapi mempelai sering ingin membacanya utuh). Bertaut dua arah dengan `/tamu/`.
  **Catatan privasi:** kolom kontak sengaja **tidak ada** — sejak nomor WA tamu berhenti dikumpulkan di form RSVP (keputusan owner), tidak ada kontak yang perlu ditampilkan.
  **Terverifikasi live** dengan 5 RSVP contoh bercampur status/jumlah/sesi: total 9 tamu · akad 7 · resepsi 6 · 5 responden · pil status 3/1/1 — semuanya cocok dengan hitungan manual. Data uji dihapus.

- [x] **FU.6** **Daftar tamu → link personal massal + CSV** *(poin 15)* → **SELESAI & LIVE 2026-08-06**
  **Apa:** mempelai menempel daftar nama → dapat tabel link `?to=Nama` siap kirim + teks broadcast yang sudah tersapa nama. Parameter `?to=` **sudah bekerja**; yang belum ada pembuat massalnya.
  **Kenapa dikerjakan sekarang, bukan menunggu gerbang F0.3:** satu daftar ini melayani **tiga** hal sekaligus — **amplop bernama tamu** (pembeda paket cetak yang percetakan konvensional jual mahal, sementara bagi kita biayanya nol karena datanya sudah ada), link personal massal, dan kelak QR check-in. Begitu produk cetak berubah jadi undangan lipat + amplop, daftar tamu berhenti jadi fitur tambahan dan jadi **prasyarat produksi**.
  **Yang dibangun:** halaman bertoken `/tamu/?order=&key=` (HMAC yang sama dengan `/isi-data/`, `/upsell/`, `/proof/`) — tempel nama satu per baris, hitungan langsung dibandingkan **jatah amplop paket** (Hormat 50 · Resepsi 100 · Grand 150; item satuan mengikuti kuantitas), **Salin semua link** (nama + link, dipisah tab — siap tempel ke aplikasi pengirim), dan **Unduh CSV** ber-BOM yang dipakai dua arah: mencetak nama di amplop **dan** kirim massal. Batas 600 nama, tampilan dipangkas 300 agar ringan di HP. Link daftar tamu + jumlah nama terkumpul tampil di halaman order, bersebelahan dengan proof.
  **Terverifikasi live** dengan order + undangan uji: 5 nama tersimpan, link personal terbentuk benar termasuk nama ber-`&` (`?to=Bapak%20Hendra%20%26%20Ibu%20Sari`), tombol salin & CSV berfungsi. Data uji dihapus.

- [ ] **FU.7** 🔒 **QR check-in tamu di venue** *(poin 7)*
  **Apa:** tiap link personal membawa QR; panitia memindai di pintu → tercatat hadir. Pasangan alami kartu QR fisik di paket cetak (F1) — QR yang sama, dua wujud.
  **Langkah:** halaman pemindai untuk panitia (berperan, bukan publik) + endpoint check-in + tampilan rekap di FU.5.
  **Selesai bila:** satu tamu dipindai dua kali tidak terhitung dua; rekap kehadiran tampil di dashboard mempelai.

---

## F1 — Validasi hybrid: tanpa mesin, tanpa bedah checkout

*Target fase ini: **5 order cetak nyata, disubkontrakkan, dengan biaya & waktu tercatat.*** Yang dicari bukan efisiensi — yang dicari angka pengganti tebakan.

- [x] **F1.7a** **Marjin per jam bisnis cetak yang sekarang** → **TERJAWAB 2026-08-06**
  **Percetakan reguler: Rp 100.000–200.000/jam marjin kotor. Undangan hybrid Rp 2,9 juta: ±Rp 580.000/jam.**
  **Artinya gerbang kelayakan LULUS dengan jarak lebar — 3–6× lipat.** Ini bukan sekadar "untung", tapi lebih untung daripada pekerjaan yang jamnya ia rebut, yang memang satu-satunya pertanyaan yang relevan begitu mesinnya milik sendiri.
  **Ruang aman yang dibeli angka itu:** pada marjin ±Rp 2,6 juta per Paket Resepsi, ±Rp 580rb/jam berarti waktu produksi ±4,5 jam/order. Supaya tetap **di atas** patokan tertinggi pekerjaan reguler (Rp 200rb/jam), waktu produksi boleh membengkak sampai **±13 jam/order** — hampir tiga kali lipat dari estimasi sekarang. Jadi meleset satu-dua jam tidak membatalkan apa pun; yang perlu diawasi hanya pembengkakan ekstrem (mis. lipat manual 800 lembar/bulan).
  **Kuota 8/bulan konsisten dengan ini:** 8 × 4,5 jam ≈ **36 jam mesin per bulan** — porsi yang masuk akal diambil dari jadwal reguler, dan justru pemakaian jam yang paling menguntungkan.
  ⚠️ **Yang tetap harus diukur (F1.7):** angka 4,5 jam masih estimasi. Yang membuatnya bisa runtuh cuma satu: **lipat manual**. Karena itu pemeriksaan mesin creasing di F1.1 tetap wajib sebelum kuota dinaikkan.

- [ ] **F1.1** 🔴 👤 **Cetak satu undangan lipat + amplop lengkap — hari ini**
  Satu sampel menjawab empat hal sekaligus: **bobot nyata** (untuk ongkir yang kita tanggung), **waktu lipat per unit**, **uji pindai QR** (di A5 lipat, QR bisa dicetak jauh lebih besar dan tanpa laminasi doff — hampir pasti lolos), dan mutu amplop bernama.
  ⚠️ **Yang paling menentukan: mesin creasing.** 100 lipatan × 8 order = 800 lipatan/bulan. Bila harus dilipat tangan, seluruh hitungan marjin per jam batal — periksa ini sebelum harga dikunci.

- [x] ~~**F1.2** Cari & uji 2–3 percetakan subkontrak~~ → **GUGUR 2026-08-06** — owner punya percetakan & alat sendiri. Estimasi marjin subkontrak Rp 1,6–1,8 jt tidak lagi relevan; yang menggantikannya adalah F1.7a (marjin per jam pekerjaan yang jamnya direbut).

- [ ] **F1.3** 👤 **Tetapkan tiga harga SKU upgrade + besaran kredit**
  Katalog menjual paket penuh; halaman upsell menjual **tiga SKU upgrade berharga tetap** untuk yang sudah membeli digital.
  **Yang perlu diputuskan:** besaran kredit digital. Cara paling sederhana — kredit tetap Rp 299.000 untuk semua tier, sehingga harga upgrade = harga paket − 299rb, satu angka per SKU tanpa matriks. Konsekuensinya pembeli Hemat (bayar 99rb) mendapat kredit Rp 200rb lebih besar dari yang ia bayar; pada paket Rp 2,9 juta itu derau, dan justru mendorong pembeli Hemat naik kelas. **Perlu keputusan sadar, bukan diasumsikan.**

- [x] **F1.4** **S&K + Refund + Privasi untuk barang fisik** → **SELESAI & LIVE 2026-08-05**
  S&K §12 (proses & proof · batas H-21 + kuota · pengiriman gratis + resi · **tiga garansi sebagai klausul** · rusak/hilang di jalan) · Refund §4 produk cetak (refund 100% sebelum proof / kuota penuh / Garansi Tepat Waktu; penggantian barang; pengecualian) · Privasi (alamat kirim di data, kurir masuk tabel pemroses). Terbit via `publish-legal.py`, terverifikasi live.
  S&K sekarang ditulis khusus produk digital (*"produk digital yang diproses otomatis"*, *"refund tidak tersedia setelah undangan diterbitkan"*). Tidak ada satu kata pun soal pengiriman, kerusakan di jalan, ongkir, atau retur salah cetak — sementara **Garansi Tepat Waktu menciptakan kewajiban refund 100% yang belum punya payung sama sekali**.
  **Yang ditambahkan:** ruang lingkup produk fisik · pengiriman gratis se-Indonesia + tanggung jawab & resi · aturan proof (setelah disetujui pelanggan, salah ketik jadi tanggung jawab pelanggan) · **ketiga garansi sebagai klausul**, bukan sekadar copy pemasaran · retur/penggantian barang rusak · kuota bulanan sebagai pembatasan ketersediaan.
  Terbit lewat `scripts/publish-legal.py` (repo = sumber kebenaran halaman legal).

- [x] **F1.5** **Halaman harga hybrid — statis, CTA WhatsApp** → **TERBIT & LIVE di `/harga/` 2026-08-05**
  `page-harga-hybrid.php` + blok `katalog.css` (v1.2.1) ter-deploy & teruji render via halaman pratinjau sementara (sudah dihapus): hero "satu desain dua wujud" · **tiga garansi di atas** · 4 kartu (Digital mulai 99rb → beranda · Hormat 1,19jt · Resepsi 2,9jt ⭐ + pill "hemat Rp 625.000" · Grand 5,9jt jangkar) · proses 4 langkah + H-21 · tabel satuan (8 item, min Rp 1jt/transaksi) · FAQ · CTA `wa.me` prefill per paket. **Diterbitkan atas keputusan owner 2026-08-05, mendahului gerbang F1.1/F1.2** ("alat cetak pasti akan tersedia"). Konsekuensinya tidak diabaikan tapi **dipindahkan ke alur pemesanan**: halaman ini tidak punya tombol bayar sama sekali — semua CTA ke WhatsApp, dan slot produksi + tanggal dikonfirmasi manual **sebelum ada uang berpindah**. Itu yang menjaga ketiga garansi (sudah jadi klausul S&K §12) tetap bisa ditepati. **Per 2026-08-06** produk cetak juga disembunyikan dari `/shop/` sampai **satu order uji internal tuntas penuh** — bukan lagi menunggu F1.2 (yang gugur), melainkan menunggu bukti bahwa kita sanggup memenuhi satu order dari ujung ke ujung. **Kuota produksi: 8 order/bulan** — cukup untuk mendapat data dalam satu bulan, cukup kecil untuk menyerap kesalahan. Beranda menautkannya lewat band "Undangan Cetak" setelah tangga harga digital + tautan footer; masuk sitemap.
  ⚠️ **Yang berubah maknanya:** F1.1 & F1.2 tidak lagi menggerbang *penayangan halaman*, tapi kini menggerbang **penerimaan order pertama** — jangan menyanggupi tanggal ke pemesan sebelum ada percetakan terpilih dan ukuran QR yang terbukti terpindai.
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

**Dikerjakan 2026-08-06.** Ringkasan hasil + tiga temuan yang tidak terduga:

- **F3.1** kategori `digital`/`cetak` dibuat & dipasang ke 6 produk; kupon `RES-` dijaga **di kode** (`cetak.php`), bukan hanya di pengaturan kupon — guard berlaku otomatis untuk semua kupon berawalan `RES-`, jadi kebocoran Rp 870rb/order tidak bisa terjadi karena lupa mencentang kategori.
- **F3.4/F3.5** `sold_individually` & pengosongan cart jadi kondisional lewat **konvensi SKU** (`HARIH-` digital · `CETAK-` hybrid · `SATUAN-` à la carte · `UPG-` upgrade). Paket saling menggantikan (1 order = 1 undangan), item satuan menumpuk & bebas kuantitas.
- **F3.6** ⚠️ **Temuan besar:** halaman checkout memakai **blok `woocommerce/checkout`**, bukan shortcode klasik — sehingga filter `woocommerce_checkout_fields` (T1.17) **tidak pernah berlaku**: pembeli undangan Rp 99rb selama ini tetap dimintai alamat lengkap dari HP. Diperbaiki lewat jalur yang dihormati Blocks (`woocommerce_get_country_locale`, `hidden`+`required=false` saat keranjang tanpa barang fisik). Terverifikasi di browser: cart digital = email, nama, WA saja; cart cetak = alamat lengkap + pilihan pengiriman.
- **F3.7** ⚠️ **Temuan kedua:** alamat dasar toko masih `US:CA` bawaan instalasi. Tidak pernah terasa selama produk virtual, tapi untuk barang fisik zona Indonesia tidak pernah cocok → *"No shipping options are available"* dan metode pembayaran ikut hilang. Diperbaiki jadi `ID:JK`, `default_customer_address=base`, zona Indonesia + gratis ongkir.
- **F3.2/F3.3** WF-01 kini membaca **SKU**, bukan nama line item. Paket hybrid → tier **premium** (menutup bug pembeli Rp 2,9 jt menerima undangan Hemat), order à la carte murni → `jenis_order=cetak_saja` sehingga **tidak dikirimi link isi data** (pesan WA/email diganti "tim akan menghubungi untuk proof & jadwal"), komisi dihitung **per jenis**: digital 30%, hybrid rupiah tetap 150/300/500rb, satuan 0. Diuji 7 skenario di dalam container n8n sebelum dipasang.
  *Catatan penyimpangan:* baris di sheet `orders` **tetap dibuat** untuk order cetak murni (rencana awal: tidak dibuat) — barisnya berguna sebagai catatan operasional dan menjaga logika dedup tetap utuh; kolom `paket` ditandai `cetak` supaya tidak pernah disalahartikan sebagai undangan yang menunggu data.
- **F3.8** field kurir + nomor resi di halaman order (HPOS-aman) + kolom Resi di daftar pesanan.
- **F3.9** 3 paket hybrid dibuat sebagai produk fisik (CETAK-HORMAT/RESEPSI/GRAND, non-virtual, berbobot, kategori `cetak`). **Sisa:** 3 SKU `UPG-*` menunggu keputusan harga & kredit di **F1.3**.
- **F3.10** halaman upsell bertoken `/upsell/?order=&key=` (HMAC yang sama dengan `/isi-data/`, `noindex`, `no-referrer`, 403 tanpa token). Hitung mundur kredit **14 hari** tampil sebagai angka berjalan; à la carte **tidak muncul sama sekali** di halaman ini. Tiga status diuji dengan order sungguhan lalu dihapus: aktif (kredit Rp 179.000, "13 hari 23 jam lagi", 3 kartu paket), kedaluwarsa (>14 hari → tawaran hilang, diarahkan ke harga normal), dan order yang sudah memuat cetak (tidak ditawari lagi). Selama SKU `UPG-*` belum ada (menunggu F1.3), harga = harga paket − yang sudah dibayar dan penutupan lewat WhatsApp; begitu `UPG-*` dibuat, halaman otomatis memakainya tanpa perubahan kode.
- **F3.11** 8 produk `SATUAN-*` + halaman `/satuan/` yang membaca harga & minimum **langsung dari WooCommerce** (harga di halaman tidak mungkin berbeda dari yang ditagihkan). Minimum ditegakkan dua lapis: per produk (`_min_qty`, kuantitas awal ikut menyesuaikan) dan **Rp 1.000.000 per transaksi** bila keranjang hanya berisi item satuan. Ditegakkan di jalur klasik **dan Store API** — pelajaran F3.6. Terbukti live: keranjang Rp 150.000 ditolak dengan pesan "kurang Rp 850.000 lagi", setelah ditambah jadi Rp 1.100.000 galat hilang.

- ⚠️ **Temuan ketiga (dicatat, bukan bug baru):** `id` workflow tidak ada di JSON WF-01 sehingga `n8n import` gagal `SQLITE_CONSTRAINT`. Kini di-bake seperti WF-02.


*Semua penghalang arsitektur yang ditemukan pada audit 2026-08-05 ditangani di fase ini.*

- [ ] **F3.1** **Kategori produk `digital` & `cetak` + batasi kupon `RES-` ke digital**
  **Wajib sebelum produk cetak pertama masuk WooCommerce.** Kupon `RES-` yang sudah beredar mengikat 30% ke **seluruh nilai order**; begitu produk cetak jadi produk biasa, kupon itu otomatis berlaku ke sana — Rp 870.000 pada order Rp 2,9 juta, bocor tanpa pernah diputuskan.
  Saat ini di server hanya ada satu kategori (`Uncategorized`, 3 produk), jadi tidak ada tempat menggantungkan pembatasan. Kategori dibuat lebih dulu.

- [x] **F3.2** **WF-01: kenali jenis order** — *menutup bug tier*
  WF-01 mendeteksi paket dengan `['hemat','favorit','premium'].find(p => namaItem.includes(p))`. Nama paket hybrid — *Hormat, Resepsi, Grand* — tidak memuat satu pun kata itu, jadi `paket` = `''` dan WF-02 jatuh ke fallback teraman **`hemat`**. Akibatnya **pembeli Paket Resepsi Rp 2,9 juta menerima undangan paket Hemat**: tanpa galeri, tanpa amplop, masa aktif H+7.
  Sekaligus: order cetak murni (à la carte) tidak boleh dikirimi link form isi data dan tidak boleh membuat baris `orders` baru — pelanggannya sudah punya undangan.

- [x] **F3.3** **WF-01: komisi per jenis produk**
  Sekarang `dasarKomisi × 0.3` tanpa syarat. Ubah: 30% hanya untuk line item **digital**; produk fisik memakai tabel rupiah tetap (150/300/500rb).

- [x] **F3.4** **`sold_individually` jadi kondisional per produk**
  Sekarang `add_filter('woocommerce_is_sold_individually', '__return_true')` berlaku **global**. Kuantitas terkunci di 1 — à la carte 100 pcs tidak mungkin dipesan. Digital tetap satuan; produk cetak bebas kuantitas.

- [x] **F3.5** **Pengosongan cart jadi kondisional**
  `woocommerce_add_to_cart_validation` sekarang **mengosongkan cart** setiap penambahan produk. Aturan "1 order = 1 paket" hanya boleh berlaku antar produk digital, supaya digital + cetak bisa berada di satu keranjang.

- [x] **F3.6** **Alamat + `shipping_*` muncul hanya bila cart memuat produk fisik**
  `billing_address_1/2`, `city`, `state`, `postcode`, `country` semuanya di-`unset` sekarang — **tidak ada alamat kirim di mana pun**. Kembalikan **hanya** saat ada barang fisik di keranjang, supaya checkout digital tetap seramping sekarang (itu yang menjaga konversi mobile).

- [x] **F3.7** **Satu metode pengiriman: gratis se-Indonesia**
  Di server sekarang hanya ada zona fallback dan `ship_to_countries` kosong. Rencana zona **dibatalkan** — satu metode free shipping, dan "gratis ongkir se-Indonesia" dipakai sebagai nilai jual di halaman harga.

- [x] **F3.8** **Field & pencatatan nomor resi**
  Wajib per keputusan owner. Disimpan di order + ikut ke sheet, dan dikirimkan ke pelanggan saat paket berangkat.

- [~] **F3.9** **Produk cetak + 3 SKU upgrade di WooCommerce** — harga dari F1.3

- [x] **F3.10** **Halaman upsell pasca-bayar**
  Bertoken seperti `/isi-data/` · **hitung mundur kredit 14 hari tampil** — tanpa batas waktu tidak ada alasan memutuskan hari ini · **à la carte dilarang muncul di halaman ini**.
  Kedaluwarsa ditegakkan server-side, bukan hanya disembunyikan di tampilan.

- [x] **F3.11** **Katalog produk satuan (à la carte)** — minimum **Rp 1.000.000/transaksi**, minimum per produk tetap berlaku di atasnya. Harga per unit sengaja tinggi: fungsinya pembanding yang membuat paket terlihat murah.

> **Gerbang keluar F3:** order uji berisi produk fisik lolos checkout dengan alamat · WF-01 tidak salah kirim link form · komisi terhitung benar · kupon `RES-` tidak menyentuh produk cetak.

---

## F4 — Otomasi cetak *setelah volume membenarkannya*

*Perkiraan jujur: F4.3–F4.5 adalah 2–3 minggu kerja fokus sebagai satu kesatuan. Imposition dengan bleed/gutter/registration mark adalah bagian paling fiddly.*

- [x] **F4.1** **Snapshot beku** → **SELESAI & LIVE 2026-08-06** — — fondasi semua yang lain. Saat order cetak dikonfirmasi, data dibekukan; seluruh produksi membaca snapshot, bukan data live. Pelanggan yang mengedit setelahnya diberi peringatan bahwa perubahan tidak berlaku untuk cetakan yang sudah diproses. Meta undangan sudah terstruktur rapi — snapshot cukup JSON beku + hash sebagai meta order.

- [~] **F4.2** **Validasi wajib di FORM, bukan di proof** — *bagian QR sudah dikerjakan 2026-08-06:*
  **QR undangan kini `ecc=H` + `qzone=4`.** Sebelumnya tanpa keduanya: koreksi kesalahan level L (hanya ±7% modul boleh rusak) dan **quiet zone praktis nol** — pola menempel ke tepi gambar. Itu aman-aman saja sebagai lampiran email, tapi QR ini sekarang **dicetak di undangan lipat**: laminasi, lipatan, dan tinta yang sedikit meleber bisa mematikan pemindaian, dan pemindai sering gagal mengunci bila QR dicetak berdampingan dengan ornamen tanpa quiet zone.
  ⚠️ **Jebakan yang terukur:** parameter `margin` pada generator dihitung dalam **piksel** (`margin=4` = 4px, nyaris nol pada gambar 600px). Yang benar `qzone`, dihitung dalam **modul** — terukur 59px ≈ 4 modul. Diverifikasi dengan membandingkan lebar baris putih di ketiga varian, bukan dengan melihat sekilas.
  *Sisa F4.2:* resolusi foto minimum, batas panjang field untuk template cetak, pembulatan kuantitas.

- [ ] **F4.2-lama** **Validasi wajib di FORM, bukan di proof** — hari vs tanggal harus cocok — **hanya relevan untuk template cetak**. Diperiksa 2026-08-05: di sisi digital ini mustahil terjadi, karena nama hari **diturunkan** dari tanggal lewat `wp_date('l, j F Y')` dan formnya memakai `<input type="date">`, bukan teks bebas. Baru jadi risiko bila template cetak menerima tanggal yang diketik manual · batas panjang field · resolusi foto minimum ±650×1000px ditolak di titik upload · QR error correction H + quiet zone + short URL sendiri agar slug bisa diubah tanpa cetak ulang · pembulatan kuantitas ("tambah 9 pcs gratis" saat 90→99 sama-sama 11 lembar).

- [ ] **F4.3** **Engine render SVG → PDF** — template diisi data snapshot, dirender via Inkscape/librsvg CLI, teks tetap vektor.
  ⚠️ **Wajib di VPS.** Hostinger shared hosting tidak bisa memasang Inkscape/librsvg. Konsekuensi: PDF siap cetak berisi data pribadi pelanggan → butuh aturan retensi & akses, sejajar dengan Kebijakan Privasi.
  Warna **sRGB**, bukan CMYK — driver Epson desktop adalah driver RGB; file CMYK justru dikonversi balik dan warnanya kusam. CMYK hanya untuk order yang disubkontrakkan.

- [ ] **F4.4** **Imposition + cut file** — N kartu di area efektif ±190×270mm, bleed 3mm, gutter, registration mark 4 sudut; cut file sebagai layer terpisah.
  **Batas yang harus disadari:** Silhouette Studio tidak punya API/CLI. Otomasi berhenti di PDF + cut file; impor ke Studio dan operasional mesin tetap manual. Yang realistis: **waktu desain jadi nol menit**, bukan "sepenuhnya otomatis".

- [x] **F4.5** **Proof + persetujuan ber-hash** → **SELESAI & LIVE 2026-08-06** — — render preview, wajib disetujui sebelum masuk antrean, **simpan timestamp + hash file yang disetujui**. Tanpa tahap ini setiap typo jadi biaya perusahaan; dengan tahap ini, pembagian tanggung jawab di S&K punya bukti.

**F4.1 + F4.5 dikerjakan 2026-08-06, mendahului engine render (F4.3).** Alasannya sama seperti F4.6: S&K §12.1 sudah menyatakan *"setelah proof Anda setujui, kebenaran seluruh teks menjadi tanggung jawab Anda"* — klausul itu tayang, tapi sistemnya belum bisa membuktikan apa pun. Selama kosong, tiap sengketa typo otomatis jadi biaya kita.
- **Snapshot** (`_snapshot` + `_snapshot_hash`): data undangan dibekukan jadi JSON berurutan tetap saat proof dibuat, sehingga hash-nya stabil. Produksi membaca snapshot, bukan data live. **Snapshot yang sudah disetujui tidak bisa ditimpa** — diuji: percobaan bekukan ulang ditolak.
- **Halaman `/proof/?order=&key=`** bertoken (HMAC yang sama dengan `/isi-data/` & `/upsell/`): menampilkan berkas proof + tabel data beku, dan konsekuensi persetujuan ditulis **terang di atas tombol**, bukan disembunyikan di tautan S&K. Ada jalan keluar "ada yang perlu diperbaiki" ke WhatsApp supaya pemesan tidak merasa terpojok menyetujui.
- **Persetujuan tercatat**: waktu, IP, hash berkas proof **dan** hash snapshot dikunci jadi satu — salah satunya berubah, hash berbeda. Catatan order ditulis lengkap dengan rujukan §12.1.
- **Halaman order**: kolom URL proof, tombol bekukan snapshot, status persetujuan, dan link siap salin untuk dikirim ke pemesan.
- Diuji end-to-end dengan order + undangan sungguhan (dibuat, disetujui lewat halaman publik, diverifikasi di meta & catatan order, lalu dihapus).

- [x] **F4.6** **Deadline H-21 + kuota bulanan di checkout** → **SELESAI & LIVE 2026-08-06** *(dikerjakan mendahului urutan F4)*
  **Kenapa didahulukan:** dua janji di S&K §12 sudah mengikat sejak halaman harga tayang. Order yang mustahil dikerjakan harus ditolak **sebelum uang berpindah** — menolak sesudah dibayar berarti refund, dan refund atas order Rp 2,9 juta bukan kerugian kecil.
  **Yang dibangun:** field **tanggal acara di checkout** khusus keranjang berisi cetak (Additional Checkout Fields API, WooCommerce 10.9) — pesanan digital tidak terpengaruh, tanggalnya tetap diisi belakangan di `/isi-data/` karena di sana tidak ada tenggat produksi · **tenggat H-21** ditegakkan di **Store API** (jalur yang benar-benar dipakai checkout blok) + jalur klasik sebagai cadangan · **kuota 8 pesanan cetak/bulan** dihitung dari order berjalan, dan kuota penuh diberitahukan **sejak di keranjang** — bukan setelah pembeli mengisi alamat dan memilih pembayaran · tanggal acara + sisa hari tampil di halaman order bersebelahan dengan resi.
  **Diuji end-to-end lewat Store API sungguhan:** acara 10 hari lagi → ditolak (pesannya menyebut Garansi H-14 secara eksplisit) · tanggal kosong → ditolak · format salah → ditolak · tanggal sah → lolos gerbang. Order draft sisa uji dibersihkan.
  *Sisa yang belum:* menampilkan **sisa slot** secara jujur di halaman harga (sekarang hanya disebut "kuota terbatas") — menunggu F1.7a memastikan angka 8 memang realistis.
  ⚠️ **Kuota per bulan musim, bukan rata.** Pernikahan Indonesia menumpuk di bulan tertentu; kapasitas 20/bulan yang laku hanya 5 bulan setahun = **±100 order setahun, bukan 240**. Proyeksi 20×12 akan meleset jauh.

- [x] **F4.7** **Antrean produksi** → **SELESAI & LIVE 2026-08-06** — halaman admin **WooCommerce → Antrean Cetak**. Diurutkan **berdasarkan tenggat acara, bukan tanggal pesanan** (pesanan yang masuk belakangan bisa saja acaranya lebih dulu). Tiap baris hanya menampilkan yang mengubah keputusan hari ini: sisa hari (memerah di bawah 14), jumlah nama tamu terkumpul, **satu langkah penghambat** (Menunggu data undangan → Menunggu daftar tamu → Siapkan proof → Menunggu persetujuan → SIAP CETAK → Terkirim), dan resi. Kuota bulan berjalan tampil di kepala halaman. Diuji dengan 3 order pada tahap berbeda: urutan tenggat & label tahap tepat, lalu data uji dihapus.
- [~] **F4.7-lama** **Antrean produksi** — diurutkan berdasarkan **deadline, bukan tanggal order**; batch dikelompokkan berdasarkan **bahan & finishing, bukan per pelanggan**.

- [x] **F4.8a** **Pengingat upsell otomatis (WF-06)** → **SELESAI & LIVE 2026-08-06** — workflow terjadwal 09:00 WIB, **9 workflow aktif** sekarang. Menyapu order 40 hari terakhir lewat WC REST, memilih **pembeli digital murni yang belum pernah memesan cetak** (dicek lintas riwayat by nomor WA **dan** email — satu orang bisa memesan dengan email berbeda), lalu mengirim WA tepat di **H+3** ("bagaimana dengan orang tua yang kurang terbiasa HP?") dan **H+12** (kredit tinggal 2 hari). Dua ketukan saja; lebih dari itu berubah jadi gangguan. Gagal kirim ke satu orang tidak menghentikan penerima lain. Logika pemilihannya diuji 7 skenario di dalam container sebelum dipasang — termasuk kasus orang yang sudah beli cetak belakangan (tidak ditawari) dan order tanpa nomor WA valid.
- [x] **Perbaikan pesan pengiriman 2026-08-06** — cacat yang saya buat sendiri sehari sebelumnya: pembeli **paket cetak** ikut menerima ajakan *"mau undangan cetaknya juga?"*. Sekarang WF-01 menandai order hybrid di kolom `paket` yang sudah ada (`premium+cetak`, **tanpa menambah kolom** — skema Sheets ikut dibake di JSON dan sheet-nya milik owner), WF-02 mengupas tier-nya, lalu pesan bercabang: pembeli digital → tawaran upsell · **pembeli cetak → link daftar tamu + penjelasan proof menyusul**. Itu memang langkah berikutnya yang ia butuhkan, bukan tawaran baru.

- [ ] **F4.8** **Upsell otomatis penuh** menggantikan versi manual — baru dibangun setelah F3.10 memberi tahu keberatan apa yang sebenarnya muncul.

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
