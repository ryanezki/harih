# Arsip Lisensi Aset Visual

Bukti lisensi setiap aset pihak ketiga yang dipakai hariH. **Jangan menambah aset visual tanpa mencatatnya di sini** — kalau suatu saat ada klaim hak cipta atau hak citra, dokumen inilah pembelaannya.

Terakhir diperbarui: 2026-08-04 (TASKS P0.3)

---

## Foto stok — Pexels

Dipakai sebagai galeri 3 undangan demo (`/u/demo-tema-01|02|03/`) dan sebagai latar kartu `og:image`.

| Berkas di repo | Sumber | ID Pexels | Isi |
|---|---|---|---|
| `wp-content/themes/harih/aset/demo/harih-cincin-buket.jpg` | pexels.com/photo/romantic-wedding-moment-with-rings-and-bouquet-29175678/ | 29175678 | Tangan bercincin memegang buket putih |
| `wp-content/themes/harih/aset/demo/harih-gaun-detail.jpg` | pexels.com/photo/back-view-of-bride-11474246/ | 11474246 | Detail tali & renda gaun sedang diikat |
| `wp-content/themes/harih/aset/demo/harih-cincin-sepatu.jpg` | pexels.com/photo/silver-ring-on-white-peep-toe-shoe-4364769/ | 4364769 | Cincin kawin di atas sepatu putih & tile |

**Lisensi:** [Pexels License](https://www.pexels.com/license/) — bebas dipakai untuk keperluan komersial, tanpa kewajiban atribusi, boleh dimodifikasi. Larangan utamanya: tidak boleh menjual ulang foto dalam bentuk apa adanya, dan tidak boleh dipakai membangun layanan yang menyaingi Pexels. Penggunaan kita (latar undangan demo & kartu OG) tidak menyentuh keduanya.

**Kenapa semuanya foto detail, tanpa wajah** — ini keputusan sadar, bukan kebetulan:

> Pexels (juga Unsplash) **tidak menyediakan model release**. Lisensinya mengizinkan pemakaian komersial dari sisi *hak cipta*, tapi tidak menyelesaikan *hak citra* orang yang tampak di foto. Memakai foto pernikahan orang lain yang wajahnya dikenali untuk mengiklankan jasa berbayar adalah persis kasus yang butuh model release — pasangan di foto bisa mempermasalahkannya. Ketiga foto di atas tidak memuat wajah yang dapat dikenali, sehingga risiko itu nol.
>
> Aturan untuk aset berikutnya: **kalau ada wajah yang bisa dikenali, jangan dipakai** kecuali kita punya model release tertulis.

Unduhan dilakukan 2026-08-04, versi terkompresi lebar 1600 px dari CDN Pexels, lalu di-re-encode JPEG ≤ 300 KB (aturan aset blueprint §7).

---

## Kartu `og:image` — buatan sendiri

`wp-content/themes/harih/aset/og/og-{katalog,tema-01,tema-02,tema-03}.jpg`

Dibangun oleh `scripts/buat-aset-og.py`: kartu HTML memakai tipografi & token warna asli tiap tema, dirender Chrome headless pada 1200×630. Foto latarnya berasal dari tabel di atas. Font (Playfair Display, Plus Jakarta Sans, Cormorant Garamond, Karla, Prata, Manrope) semuanya **Google Fonts — SIL Open Font License 1.1**, bebas dipakai komersial termasuk untuk ditanam pada gambar.

Menambah tema baru → tambahkan entrinya di `TEMA` dalam script, jalankan ulang, aset barunya otomatis dipakai `harih_og_default()`.

---

## Musik latar — Pixabay

Berkas di server: `wp-content/uploads/musik/*.mp3` (bukan media library — tidak perlu jadi attachment, ikut ter-backup lewat mirror rsync mingguan, dan tidak bisa terhapus tak sengaja saat membersihkan media).

| Berkas di server | Judul asli | ID Pixabay | Artis |
|---|---|---|---|
| `harih-klasik-modern.mp3` | Wedding | 485932 | [paulyudin](https://pixabay.com/users/paulyudin-27739282/) |
| `harih-romantis-hangat.mp3` | Romantic Inspiring | 159847 | paulyudin |
| `harih-piano-romantis.mp3` | Romantic Piano Inspiring | 155910 | paulyudin |

**Lisensi:** [Pixabay Content License](https://pixabay.com/service/terms/) — bebas komersial, tanpa kewajiban atribusi. Sertifikat unduhan atas nama `ryanezki` tertanggal 2026-08-04 diarsipkan di `music/*-license.txt` (repo) — **jangan dihapus**, itu satu-satunya bukti kalau suatu saat ada klaim.

Diproses ulang dari 256 kbps (3,8–5,0 MB) ke **128 kbps** (1,7–2,2 MB) via ffmpeg: tamu membuka undangan dari kuota seluler, dan berkas aslinya membuat musik baru mulai puluhan detik setelah tombol ditekan. Durasi & isi tidak diubah, metadata lama dibersihkan.

Menambah track: taruh MP3 di `uploads/musik/`, daftarkan di `harih_musik_library()` (`themes/harih/functions.php`) — array itu sekaligus **whitelist** meta `musik_url`, jadi track yang tidak terdaftar akan ditolak saat undangan dibuat.

---

## Aset yang TIDAK boleh dipakai

- Foto/ilustrasi hasil pencarian gambar biasa tanpa lisensi jelas.
- Foto pernikahan customer — kecuali ada izin tertulis dari yang bersangkutan. Data customer diproses hanya untuk membuat undangannya sendiri (lihat Kebijakan Privasi).
- Lagu populer berhak cipta sebagai musik latar (lihat S&K §7).

---

## Logo hariH — buatan sendiri

`wp-content/themes/harih/aset/logo/logo-harih-*.png`

Dibangun oleh `scripts/buat-logo.py` (Chrome headless, sama seperti kartu OG). Wordmark memakai **Playfair Display** — Google Fonts, **SIL Open Font License 1.1**, bebas dipakai komersial termasuk untuk ditanam pada gambar dan dijadikan logo. Warna diturunkan dari token tema-01: sage `#3f5c4f`, gading `#f7f4ea`, emas `#c9a24d`.

Konsep: nama "hariH" adalah permainan kata *hari H*; huruf H terakhir diberi warna emas agar maknanya terbaca. Tidak memakai ikon cincin/hati stok — jadi tidak ada aset pihak ketiga di dalam logo, dan hak atasnya sepenuhnya milik hariH.

| Berkas | Ukuran | Untuk |
|---|---|---|
| `logo-harih-kotak-1000.png` | 1000×1000 | **Unggahan merchant Duitku** (utama) |
| `logo-harih-kotak-500.png` | 500×500 | Bila ada batas ukuran lebih kecil |
| `logo-harih-kotak-putih-1000.png` | 1000×1000 | Bila diminta latar terang |
| `logo-harih-wordmark.png` | 1200×400, transparan | Dokumen & situs, latar terang |
| `logo-harih-wordmark-putih.png` | 1200×400, transparan | Latar gelap |

## QRIS demo

`aset/demo/harih-qris-demo.png` — QR code 480×480 berisi URL `https://harih.id`, dibuat lewat generator QR (QuickChart) dan **disimpan sebagai aset**, bukan dipanggil saat runtime. QR code sendiri bukan objek berhak cipta; yang penting di sini kemandirian: halaman demo tidak perlu menunggu domain pihak ketiga untuk menampilkan fitur amplop digital yang sedang dijualnya. *(Koreksi 2026-08-06: alasan awal yang saya catat — "generator lama mengembalikan PNG kosong" — keliru. 498 byte adalah ukuran wajar PNG 1-bit dan QR-nya valid.)*

## Font (kartu og:image server-side, FU.1)

Kartu `og:image` per undangan dirender di server dengan GD + FreeType, jadi berkas font harus **ikut di dalam tema** (bukan diambil dari Google Fonts saat runtime — tidak ada koneksi keluar saat render, dan preview WhatsApp tidak boleh bergantung pihak ketiga).

| Berkas | Keluarga | Lisensi | Sumber |
|---|---|---|---|
| `aset/font/CormorantGaramond.ttf` | Cormorant Garamond (variable) | SIL OFL 1.1 | github.com/google/fonts `ofl/cormorantgaramond` |
| `aset/font/Karla.ttf` | Karla (variable) | SIL OFL 1.1 | github.com/google/fonts `ofl/karla` |
| `aset/font/Prata.ttf` | Prata Regular | SIL OFL 1.1 | github.com/google/fonts `ofl/prata` |
| `aset/font/Manrope.ttf` | Manrope (variable) | SIL OFL 1.1 | github.com/google/fonts `ofl/manrope` |
| `aset/font/DMSerifDisplay-regular.woff2` | DM Serif Display 400 | SIL OFL 1.1 | fonts.gstatic.com (subset latin) |
| `aset/font/DMSerifDisplay-italic.woff2` | DM Serif Display 400 italic | SIL OFL 1.1 | fonts.gstatic.com (subset latin) |
| `aset/font/Figtree-latin.woff2` | Figtree variable 400–800 | SIL OFL 1.1 | fonts.gstatic.com (subset latin) |

Tiga berkas woff2 terakhir dipakai **halaman toko** (katalog, harga, satuan, upsell, proof, tamu, rekap, reseller) sejak redesain 2026-08-06 dan **di-selfhost**, bukan dipanggil dari CDN Google: satu permintaan lintas-domain lebih sedikit dan tampilan tidak bergantung layanan pihak ketiga. Lisensinya di `OFL-DMSerifDisplay.txt` & `OFL-Figtree.txt`.

Teks lisensi keempat font undangan digabung di `aset/font/OFL.txt` (OFL mensyaratkan lisensi ikut disertakan saat font didistribusikan ulang). Keempatnya **font yang memang dipakai skin undangan**, jadi kartu preview memakai tipografi yang sama dengan produk yang dijual: tema-01 Cormorant · tema-02 Karla · tema-03 Prata + Manrope.
