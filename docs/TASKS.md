# TASKS — hariH

**Status:** aktif · **Ditulis ulang:** 2026-08-09 dari cetak biru konsultan bisnis (model grosir/mitra), setelah audit repo menyeluruh · **Kondisi:** smoke **33/33** live · **8 workflow aktif** *(WF-04 dipensiunkan di R1)* · `HARIH_VERSION 2.33.0` · **nol order.**

> Versi sebelumnya (891 baris — rencana B2C, 58 dari 61 item tuntas) diarsipkan di [`arsip/TASKS-2026-08-09.md`](./arsip/TASKS-2026-08-09.md). Dokumen ini hanya memuat **yang berlaku sekarang**. Riwayat lengkap ada di git log.

**Cara pakai:** centang `- [x]` saat selesai. ID stabil. 👤 = butuh tangan owner · 🤖 = bisa dikerjakan asisten · 🤝 = keduanya. Urutan grup adalah urutan kerja. **Gerbang (🚪) adalah uang masuk, bukan kode selesai** — dilarang memulai fase berikutnya sebelum gerbangnya lolos.

> ⚠️ **Namespace berubah.** `B1–B13` di dokumen ini = **keputusan terkunci model grosir**, sejajar 1:1 dengan cetak biru konsultan. Itu **bukan** seri P1 lama (`B1` endpoint RSVP, `B9` token bercakup, …) yang sudah diarsipkan tapi ID-nya masih hidup di komentar kode. Rujukan ke seri lama ditulis sebagai anotasi, mis. `(eks-C8)`.

**Tingkat keyakinan:** cetak biru konsultan **benar di strategi, meleset di belasan premis teknis** — dua di antaranya mengubah angka bisnisnya. Seluruh koreksi ada di bagian [⚠️ Koreksi](#-koreksi-terhadap-cetak-biru-konsultan), masing-masing dengan rujukan `berkas:baris`. **Baca bagian itu sebelum mengeksekusi task apa pun.**

---

## ⏸ CHECKPOINT — mulai dari sini

**Titik masuk sesi berikutnya.** Sesi 9 Agustus: dokumen ini ditulis ulang dari cetak biru konsultan, **seluruh P0 (R1–R5) dikerjakan sampai live**, lalu penawarannya dikunci di putaran kedua konsultan. Rencana lama tuntas — `C8` diserap ke `R2`, `A8` jadi keputusan owner, `D3` dibekukan.

> ### ▶ MULAI DARI SINI
> **Tidak ada lagi kode yang menahan apa pun. Yang tersisa: mengangkat telepon.**
>
> 1. **F0 — jual.** Satu-satunya yang memindahkan angka. Alat & kata-katanya sudah siap:
>    · `bash demo-mitra.sh "Nama Toko"` → undangan live berlabel nama toko calon mitra
>    · pesan pembukanya di [`copywriting-pesan.md`](./copywriting-pesan.md) **bagian 9**
>    · langkah owner di [`panduan-manual.md`](./panduan-manual.md) langkah 0–1
> 2. **F0.1 menahan pengiriman daftar harga** — dua angka grosir (Resepsi & Grand) belum dikunci. Itu satu jam kerja owner, dan tanpanya bagian 9b tidak bisa dikirim.
> 3. M1–M8 **terkunci** sampai [Gerbang 0](#-f0--jual-dulu-nol-fitur-baru-minggu-ini) lolos: 3 mitra membayar order grosir pertama.
>
> ✅ **Seluruh P0 (R1–R5) selesai & live 9 Agustus.** Norek berhenti dikumpulkan · retensi 90 hari punya penegak · kaki undangan bisa berlabel nama mitra · harga per-role terbukti tidak bocor lewat cache · drift harga provisioning ditutup. Smoke **33/33** · workflow aktif 9 → 8 · meta CPT 45 → 46 · 6 commit.

**Empat kali sesi ini hampir mengirim yang salah, dan semuanya tertangkap sebelum deploy** — dicatat karena polanya berulang, bukan untuk gaya-gayaan:

| Nyaris | Ketahuan karena |
|---|---|
| Blok CSS "Form reseller" dihapus — padahal satu-satunya definisi `.field`/`.aturan`, dipakai beranda & `/satuan/` | selektor yang terhapus dicocokkan ke templat yang masih hidup **sebelum** deploy |
| Uji "`mitra_id` palsu" lulus tanpa menguji apa pun — `update_post_meta` menyisakan baris kosong, nilai palsu jadi baris kedua | prasyarat ujinya sendiri ikut diperiksa (`COUNT(*)` baris meta) |
| Harness R3 melaporkan "KEBOCORAN TERBUKTI" — `set -u` mematikan seluruh permintaan anonim | tiap permintaan juga mencetak kode HTTP; yang anonim kosong |
| `_harih_uji` terbaca kosong → penjaga `TEST-173` seolah tidak terpasang | dibaca ulang lewat `$order->get_meta()`; ternyata **HPOS** |

**Aturannya sekarang eksplisit:** harness yang gagal **tidak boleh** menghasilkan kesimpulan — ia harus menolak menyimpulkan.

**Apa yang berubah dari rencana lama.** Pelanggan utama berpindah dari **pengantin** ke **mitra (WO & percetakan)**; mitra dibayar lewat **harga grosir**, bukan komisi; yang dijual adalah **kapasitas + sistem**, bukan undangan. Platform tidak dibangun ulang — sekitar 8 task, sebagian besar hitungan jam.

**Yang TIDAK berubah dan tidak boleh disentuh:** pipeline WF-01 → WF-02 · arsitektur tema sebagai *skin* + whitelist `template_id` · Antrean Cetak, alur proof, `_proof_hash`, `_harih_uji` · hardening & rate limit · Garansi Tepat Waktu H-14 · pembeda inti **nama tiap tamu tercetak langsung di amplopnya**.

**Data uji yang SENGAJA disimpan** — jangan dihapus tanpa konfirmasi; `R2` sudah mengecualikannya lewat `undangan_dikecualikan_hapus()`: pesanan `TEST-173` + undangan `174` (`/u/test-rangga-sekar/`), ditandai `_harih_uji=1`, baris sheet `orders` order_id 173.
⚠️ Untuk meta ORDER, `wp post meta get` **berbohong** — HPOS aktif, metanya tidak di `wp_postmeta`. Pakai `wp eval '$o=wc_get_order(173); var_export($o->get_meta("_harih_uji"));'`.

**Yang lahir sesi ini dan akan dipakai terus:** `/u/demo-mitra/` (demo white-label, satu slot dipakai bergantian) · [`scripts/demo-mitra.sh`](../scripts/demo-mitra.sh) · `undangan_hapus_data_undangan()` untuk permintaan penghapusan pelanggan ([`runbook.md`](./runbook.md) §7b-2) · `undangan_get_mitra()` sebagai whitelist mitra.

---

## Diagnosis

Repo ini sudah menulis kalimatnya sendiri sejak 7 Agustus: **"terlalu banyak dibangun dan terlalu sedikit dijual."** Konsultan mengulanginya dan melanjutkannya. Lanjutannya benar.

**166 commit, Rp 0.** Platform hidup end-to-end sejak 22 Juli — 9 workflow, 8 modul mu-plugin, 5 halaman bertoken, 3 tema × 7 nuansa, smoke 32/32, review UI/UX 7 dimensi 31 item selesai. Nol order.

**Yang salah bukan platformnya, tapi ke siapa ia dijual.** Undangan Rp 99–299rb dijual ke **pengantin** — pembeli sekali seumur hidup, yang harus dicari ulang tiap bulan, di pasar paling berdarah di Indonesia. Sementara kanal yang bisa membawa pembeli berulang (WO & percetakan) dimatikan karena aritmetika komisi yang memang tidak menggerakkan siapa pun: 30% × Rp 179rb = **Rp 54.000**.

**Selisihnya bukan 20%, tapi 21×.** Di paket yang sama, selisih harga grosir untuk mitra adalah **Rp 1,15 juta**.

**Yang dibutuhkan bukan ratusan pelanggan — empat.** Empat mitra dengan slot cetak terisi ≈ Rp 21,9jt bruto/bulan. Itu bukan proyeksi optimis; itu aritmetika dari harga yang sudah ada dan kapasitas yang sudah diukur.

**Dan A8 bukan penghalang.** `A7` sudah membuka jalur bayar manual: owner kirim QRIS → buat order → set `processing` → WF-01 menyala normal. Payment gateway adalah masalah orang yang **punya** order.

---

## ⚠️ Koreksi terhadap cetak biru konsultan

Cetak biru ditulis dari luar repo. Semua di bawah diverifikasi terhadap kode dan terhadap pengukuran produksi 8 Agustus. **Yang dua pertama mengubah keputusan bisnis, bukan cuma peta berkas.**

### 🔴 K1 — HPP tidak perlu ditebak; sudah diukur. Dan satu paket jatuh di harga grosir usulan.

Konsultan menandai HPP "perlu diverifikasi" dan memakai ±Rp 700rb, lalu mengunci aturan **grosir ≥ 2× HPP**. Aturan itu tidak melihat **jam** sama sekali — padahal owner punya mesin sendiri, jadi yang langka adalah jam, bukan modal. Metrik pengendali repo memang sudah **marjin per jam tangan**.

HPP nyata = harga eceran − marjin terukur ([📏 DIUKUR 2026-08-08](#-diukur-2026-08-08--dasar-b9b10)):

| Paket | Eceran | **HPP terukur** | Grosir usulan konsultan | Marjin grosir | **per jam tangan** | **per jam dinding** |
|---|---|---|---|---|---|---|
| Hormat (50) | 1,19 jt | **335rb** | 690rb | 355rb | 296rb | **154rb** 🔴 |
| Resepsi (100) | 2,9 jt | **500rb** | 1,75 jt | 1,25 jt | **735rb** ✅ | 313rb ✅ |
| Grand (150) | 5,9 jt | **720rb** | 3,6 jt | 2,88 jt | **1,31 jt** ✅ | 505rb ✅ |

Pembanding pekerjaan cetak reguler: **Rp 100–200rb/jam**.

**Dua dari tiga lolos nyaman. Hormat jatuh.** Di grosir Rp 690rb, Paket Hormat menghasilkan **Rp 154rb/jam dinding** — di bawah patokan tertinggi pekerjaan cetak biasa. Menerima order itu berarti rugi dibanding mengerjakan job reguler. Hormat memang sudah tercatat sebagai tingkat terlemah (1,9× di harga eceran) dan sudah dicabut dari halaman upgrade karena jangkar harga. → **B10**

**Usul lantai Rp 850rb saya SENDIRI tidak selamat** — lihat **K8**. Hormat dicabut seluruhnya dari grosir (**B9**).

Efek sampingnya justru baik: §3 konsultan **meremehkan** hasilnya. HPP nyata 8 × 500rb = Rp 4jt (bukan 5,6jt) → laba kotor ≈ **Rp 17,9jt/bulan**, bukan Rp 16jt.

### 🔴 K2 — "8 slot adalah aset paling langka" tidak benar, dan itu mencabut satu task

Seluruh §2.4 (sewa kapasitas) berdiri di atas premis bahwa 8 slot/bulan adalah batas fisik. Pengukuran 8 Agustus menyatakan sebaliknya: 8 × 1,7 jam tangan ≈ **14 jam**, dan kuota 8 ditahan bukan karena mesin, melainkan **karena belum pernah satu pesanan pun dikirim tepat waktu**.

Akibatnya: **M9 prematur** — ruang internal masih ±5× lipat sebelum perlu partner fulfillment. Dan menjual "hanya 8 slot" sebagai kelangkaan sementara kapasitasnya jauh di atas itu adalah klaim yang tidak ditepati alur — persis yang dilarang disiplin `C3`/`U21`. Retainer tetap boleh **mengunci** slot; yang dicabut adalah bingkai kelangkaannya. → **B11**

M9 juga bertabrakan dengan keputusan terkunci lama *"satu orang tidak boleh jadi reseller sekaligus vendor"*: §2.5 merekrut percetakan sebagai **kanal**, M9 merekrut percetakan sebagai **vendor**.

### 🟠 K3 — kaki atribusi sudah ada sejak hari pertama, dan 4.000 tayangan itu tidak pernah ada

§2.6 menyebut atribusi sebagai "iklan yang belum dipungut" dan menghitung 4.000 tayangan/bulan yang *"sudah lewat di depanmu sejak 22 Juli"*.

[`penutup.php:35`](../wp-content/themes/harih/template-parts/undangan/penutup.php:35) sudah merender *"Undangan digital oleh hariH"* di **setiap** undangan — tanpa gerbang paket, ber-UTM, tidak bisa dimatikan. Dan tayangannya nol: nol order, hanya 3 undangan demo. Trafik itu tidak pernah lewat.

Pekerjaannya bukan **menambah** kaki, tapi **membuatnya bisa dikondisikan**. Itu memangkas M5 jadi tinggal rewrite `/mitra/{slug}/` + penghitung. → **R4**

### 🟠 K4 — harga grosir Rp 990rb/bulan punya langit-langit yang sudah tercatat di repo

Catatan lama, dibawa apa adanya karena langsung mengenai §2.4: pemain digital besar **sudah merekrut percetakan sebagai partner white-label mulai ±Rp 50rb/bulan**. Kalau yang dijual adalah portal white-label, Rp 990rb adalah premi 20× terhadap tawaran yang sudah beredar.

Yang tidak bisa di-white-label siapa pun — dan karena itu satu-satunya hal yang membenarkan angka retainer — adalah **mutu cetak, amplop bernama tercetak, dan tiga garansi tertulis**. Retainer harus dijual sebagai **slot produksi terkunci**, bukan sebagai akses portal. *(Angka Rp 50rb berasal dari catatan repo, belum diverifikasi ulang ke situs pesaing — verifikasi masuk F0.2.)*

### 🟡 K5 — kupon `RES-` yang "ditolak di keranjang cetak" adalah penjaga sengaja, bukan bug

§1.3 menyimpulkan kanal reseller tidak pernah benar-benar diuji karena kuponnya diblokir di order terbesar. Blokirnya memang **disengaja** ([`cetak.php:198-213`](../wp-content/mu-plugins/undangan-core/cetak.php:198)): kupon 30% pada order Rp 2,9 juta akan menyedot **Rp 870rb** sementara komisi yang terkunci hanya Rp 300rb. Yang rusak adalah model komisinya, dan itu memang yang diganti — jadi kesimpulannya tetap sama lewat jalan yang benar.

⚠️ Aturannya juga bukan seperti yang ditulis: penolakan terjadi bila keranjang **tidak punya satu pun item `HARIH-*`**. Keranjang campuran tetap menerima kupon.

### 🟡 K6 — peta berkas & angka repo

| Klaim cetak biru | Kenyataan |
|---|---|
| `mu-plugins/undangan-core/kuota.php` | **Tidak ada.** Kuota ada di [`cetak.php:380-533`](../wp-content/mu-plugins/undangan-core/cetak.php:380) |
| "helper waktu yang sudah diperbaiki di `C4(a)`" | **Tidak ada helper.** `wp_date()` / `wp_timezone()` / `current_datetime()` dipakai inline di 4 tempat |
| CPT `undangan` punya 21 meta | **45** ([`cpt.php:253-314`](../wp-content/mu-plugins/undangan-core/cpt.php:253)). Yang 20 adalah kunci snapshot `_proof_hash` |
| `harih_harga_*` ada di mu-plugin | Ada di [`functions.php:683-739`](../wp-content/themes/harih/functions.php:683) (tema) dan **membaca harga dari WooCommerce**, tidak menyimpan angka |
| role `harih_mitra` "sudah ada, lebih aman" | **Nol `add_role` di seluruh repo.** Ini akan jadi role kustom pertama; nol halaman ber-login juga |
| "naikkan `HARIH_VERSION` tiap ubah CSS/JS" | Digantikan `filemtime()` per berkas sejak `U28`. Konstanta tinggal fallback |
| WF-04 satu-satunya pembawa komisi | **WF-01 juga menghitung komisi** — 30% digital + peta rupiah tetap cetak, di node `Siapkan Data Order` |
| "arsipkan WF-04, jangan dihapus" | **JSON workflow tidak punya field `active`.** Menonaktifkan hanya bisa lewat n8n, bukan dengan mengedit berkas |
| `M8`: harga dibaca lewat helper | Benar untuk digital. **`page-harga-hybrid.php` meng-hardcode seluruh harga cetak** (1.190.000 / 2.900.000 / 5.900.000 + tabel satuan) — justru produk termahal yang angkanya tidak dibaca dari WooCommerce |
| `M5`: `?ref=` tidak sampai ke PHP | Benar. Tapi UTM **tetap terbaca GA4** (sisi klien membaca URL asli); yang di-drop hanya kunci cache. Saran path-based tetap dipakai karena lebih tahan |
| "130 commit · 7 mu-plugin" | **166 commit · 8 modul + 1 loader.** `v2.33.0` adalah `HARIH_VERSION`, **bukan tag git** — repo tidak punya tag sama sekali |

### 🟡 K7 — tiga lubang model; dua sudah ditutup di putaran kedua konsultan

1. ✅ **Retainer hangus (B5) vs jaminan "seluruh retainer dikembalikan" di hari ke-60.** **Ditutup 9 Agustus — dan cara menutupnya lebih baik daripada usul saya.** Saya menawarkan *mempersempit* jaminan (berlaku bulan pertama saja). Konsultan **memindahkannya**: pembalik risiko turun ke **grosir** (pintu masuk, ke orang asing), retainer cukup satu kalimat tanpa tanda bintang. Alasannya lebih dalam daripada kontradiksinya — dua alat komitmen tidak boleh menumpuk di satu tempat, dan di order ke-2 mitra **tidak butuh jaminan, dia butuh slotnya**. → **B14**, **B15**
   Sekalian: syarat lama *"tawarkan ke 5 pengantin"* dicabut karena **tidak bisa diverifikasi siapa pun**. Penggantinya bisa diamati — telat dari H-14, atau klien menolak hasilnya.
2. ⏳ **Undangan mitra yang berhenti bayar.** Masih terbuka, dan **menahan M6**. M6 memberi masa aktif 1 tahun, tapi retainernya bulanan. Mitra berhenti di bulan ke-2 → undangan kliennya mati di depan klien itu.
3. ⏳ **"10 mitra pertama Rp 590rb selamanya"** — kewajiban permanen yang dijanjikan sebelum satu pelanggan pun ada. Kini kurang mendesak: **B15** memindahkan retainer keluar dari percakapan pertama, jadi kalimat kelangkaan itu tidak lagi diucapkan ke orang asing.

→ sisanya di [Keputusan yang menunggu owner](#-keputusan-yang-menunggu-owner).

### 🔴 K8 — lantai Rp 850rb untuk Hormat tidak selamat, dan alasan sebenarnya bukan tarif per jam

Usul saya di K1 — "Hormat dicabut, **atau** lantai Rp 850rb" — diuji konsultan terhadap metrik pengendali yang kita kunci sendiri, dan **jatuh**. Diverifikasi ulang di sini terhadap pengukuran 8 Agustus:

| Harga grosir Hormat | Marjin | /jam dinding | **/jam tangan** | Lolos lantai Rp 600rb? | Spread mitra |
|---|---|---|---|---|---|
| Rp 690rb *(usul konsultan)* | 355rb | 154rb | 296rb | ❌ | 42,0% |
| **Rp 850rb** *(usul saya)* | 515rb | 224rb | **429rb** | ❌ | 28,6% |
| Rp 1,055 jt | 720rb | 313rb | 600rb | ✅ | **11,3%** — tidak ada mitra yang mau |

**Tidak ada harga yang lolos lantai DAN menyisakan margin yang mau dijual mitra.** Hormat memang tidak punya ruang.

**Dan alasan sebenarnya bukan tarif per jam, melainkan SLOT.** Kuota 8/bulan dihitung per *order*, bukan per jam — jadi Hormat memakai satu slot penuh untuk marjin Rp 515rb sementara Grand memakai slot yang sama untuk **Rp 2,88jt**. Biaya peluangnya **Rp 2,37jt per slot** *(konsultan menulis 2,17jt; dengan HPP Grand terukur Rp 720rb selisihnya justru lebih besar)*.

**Risiko jangkar harga lebih parah di jalur mitra daripada di eceran** — dan itu yang mengunci keputusannya. Pembeli eceran memilih sekali seumur hidup; mitra memilih **20–40 kali setahun**. Satu kebiasaan buruk berulang sepanjang tahun. Hormat tetap dijual penuh di katalog eceran, di mana tugasnya memang jadi pembanding — dan pembanding tidak dimasukkan ke daftar grosir. → **B9**

---

## Keputusan terkunci

Jangan dibuka ulang saat coding. `B1–B8` dari cetak biru konsultan, sejajar 1:1. `B9–B13` dari koreksi di atas. **`B14–B15` dari putaran kedua konsultan, 9 Agustus** — menutup lubang K7 #1 dengan cara yang lebih baik daripada usul saya.

| # | Keputusan | Alasan |
|---|---|---|
| **B1** | Mitra dibayar lewat **harga grosir per role**, bukan komisi | Komisi 30% × Rp 179rb = Rp 54.000, terbukti tidak menggerakkan siapa pun |
| **B2** | Tidak ada pembayaran komisi ke mitra — **arah uang selalu mitra → hariH** | Menghapus seluruh kategori data keuangan mitra (nomor rekening) secara struktural, bukan ditambal → **R1** |
| **B3** | Otentikasi mitra = **login WordPress role `harih_mitra`**, bukan halaman bertoken | Menghindari bug nonce/cache yang sudah pernah terjadi. ⚠️ role-nya belum ada — lihat K6 |
| **B4** | Cetak fisik **hanya Jabodetabek**. Luar Jabodetabek = digital + berkas siap cetak | Jangan menjamin logistik yang tidak dikontrol |
| **B5** | Retainer **hangus bila tidak dipakai**, bukan diakumulasi — **dan tidak dijual di percakapan pertama** | Retainer adalah alat komitmen, bukan deposit. Kontradiksi dengan jaminan §2.7 diselesaikan di **B14/B15**: jaminannya yang pindah, bukan hangusnya yang dilunakkan |
| **B6** | Toko B2C **tetap hidup**, tapi tidak menerima anggaran iklan | Etalase & bukti sosial. Bukan mesin uang |
| **B7** | Slot cetak dialokasikan ke mitra **lebih dulu**; publik kebagian sisa | Aset terbatas diberikan ke pembeli berulang |
| **B8** | ~~Harga grosir ≥ 2× HPP~~ → **digantikan B10** | Aturan HPP tidak melihat jam; pada Hormat ia meloloskan harga yang merugi. Lihat K1 |
| **B9** | **Paket Hormat DICABUT dari daftar grosir.** Titik — tidak ada lantai penyelamat. Tetap dijual penuh di katalog eceran sebagai pembanding | Bukan soal tarif per jam, melainkan **slot**: Hormat memakai 1 dari 8 slot untuk marjin Rp 515rb, Grand memakai slot yang sama untuk Rp 2,88jt. **Biaya peluang Rp 2,37jt per slot.** Lihat K8 |
| **B10** | Lantai harga grosir = **marjin per jam tangan ≥ Rp 600rb** (3× patokan tertinggi cetak reguler) | Yang langka adalah jam, bukan modal — owner punya mesin sendiri. Dinaikkan dari 2× ke 3× mengikuti konsultan; Resepsi (735rb) & Grand (1,31jt) tetap lolos nyaman, jadi tidak ada harga yang perlu diubah |
| **B11** | Kuota 8/bulan adalah **cap sementara, bukan batas kapasitas.** Tidak dijual sebagai kelangkaan | Kapasitas nyata ±5× lipat. Klaim langka yang tidak ditepati alur dilarang (`C3`/`U21`) |
| **B12** | **Gerbang 0 = 3 mitra berbeda sudah membayar order grosir pertama** | Sejak **B15** retainer tidak lagi ditawarkan di percakapan pertama, jadi gerbangnya otomatis diuji oleh grosir. Dua hipotesis tetap terpisah — "grosir laku" diuji di gerbang ini, "retainer laku" diuji sesudahnya |
| **B13** | Retainer dijual sebagai **slot produksi terkunci**, bukan sebagai akses portal | Portal white-label sudah beredar mulai ±Rp 50rb/bulan (K4). Yang tidak bisa ditiru: mutu cetak, amplop bernama, tiga garansi |
| **B14** | **Pembalik risiko ada di GROSIR, bukan di retainer** — dan syaratnya wajib **bisa diamati**: *"Order pertamamu telat dari H-14 atau kliennya menolak hasilnya — uangmu kembali penuh, cetakannya tetap kamu ambil."* | Syarat lama *"tawarkan ke 5 pengantin"* **tidak bisa diverifikasi siapa pun** — jaminan seperti itu berakhir jadi sengketa, atau jadi jaminan tanpa syarat yang ditanggung diam-diam. Keduanya menempatkan owner pada posisi kalah |
| **B15** | **Retainer ditawarkan di order ke-2/ke-3, bukan di percakapan pertama** — satu kalimat tanpa tanda bintang: *"Slot dikunci atas namamu. Saldo hangus akhir bulan."* | Dua alat komitmen tidak boleh menumpuk di satu tempat. Di order kedua mitra bukan orang asing lagi: dia sudah membeli, sudah melihat hasilnya, dan baru saja kehilangan slot. **Dia tidak butuh jaminan — dia butuh slotnya** |

**Metrik yang dilacak — dan hanya ini:** mitra aktif berbayar (target F1: **3**) · isian slot cetak (≥ 50%) · attach rate cetak (≥ 15%) · retensi mitra bulan ke-2 (≥ 80%).
**Berhenti dilacak sampai ada 8 mitra:** pageview · bounce rate · skor Lighthouse · jumlah tema.

---

## 🔴 P0 — utang & risiko yang tidak menunggu gerbang

> Cetak biru meminta nol kode selama masa jualan. Yang di bawah dikecualikan karena **hidup sekarang**: satu endpoint yang masih menerima nomor rekening, dan satu janji tertulis yang sudah tayang tanpa penegak. Sisanya (R3–R5) membuat F0 dan F1 lebih murah, dan totalnya di bawah satu hari.

- [x] **R1** 🤖 `jam` — **Norek & komisi berhenti dikumpulkan dan disiarkan** → **SELESAI & LIVE 2026-08-09**
  Halaman `/jadi-reseller/` 404, tapi **webhook `daftar-reseller` masih aktif** — smoke test sendiri membuktikannya (*"WF-03 aktif (data kosong → 422)"*). Nomor rekening yang masuk disimpan **plaintext** di Sheets tab `resellers`, lalu **disiarkan ulang** ke WA dan email owner (`Payout: ${bank} ${norek}`); WF-04 menyiarkannya lagi tiap Senin. Di model grosir arah uang selalu mitra → hariH (**B2**), jadi seluruh kategori data ini tidak punya alasan berdiri — ini bukan menambal temuan privasi, ini menghapus permukaannya.
  **Langkah:**
  · `WF-01-order-intake.json` node `Siapkan Data Order` — cabut `KOMISI_CETAK` + kalkulasi `* 0.3`, dan cabang `RES-` yang menulis ke tab `komisi`
  · `WF-03-onboarding-reseller.json` — cabut field `bank` & `norek` dari validasi, dari append Sheets, **dan dari teks WA + HTML email ke owner**
  · `WF-04-rekap-komisi.json` — **nonaktifkan lewat n8n**, jangan dihapus dari repo. ⚠️ JSON tidak punya field `active` (K6); mematikannya hanya bisa dari n8n
  · `page-jadi-reseller.php` + [`functions.php`](../wp-content/themes/harih/functions.php) — cabut 7 titik klaim "komisi 30%" dan field bank/norek di formulir + `reseller.js` *(kedua berkas itu akhirnya DIHAPUS, bukan disunting — lihat catatan hasil di bawah; markup lamanya ada di git sampai commit `826c8d2`)*
  · [`kebijakan-privasi.md:62`](./konten-legal/kebijakan-privasi.md:62) — cabut klausa *"Data reseller (termasuk nomor rekening)…"*, terbitkan ulang lewat `scripts/publish-legal.py`
  · variabel mati `$harih_ada_reseller` di `page-katalog.php:101` & `page-teks.php:22`
  ⚠️ **Jangan sentuh meta `rekening` pada CPT `undangan`** (`cpt.php:277`) — itu rekening **mempelai** untuk amplop digital, bukan data mitra.
  **Selesai bila:** `grep -ri 'norek\|bank' n8n/ wp-content/` nol di jalur aktif (di luar meta mempelai) · halaman Kebijakan Privasi live tidak lagi menyebut nomor rekening reseller · `n8n list:workflow --active=true` = **8** · nol kemunculan "30%" sebagai janji komisi di basis kode aktif · tab Sheets `komisi` dibekukan (tidak dihapus).

  → **SELESAI & LIVE 2026-08-09.** Smoke **33/33** (satu pemeriksaan baru: Kebijakan Privasi live wajib bersih dari klausa komisi). Workflow aktif **9 → 8**. Yang dikerjakan melebihi rencana di tiga titik, masing-masing karena memeriksa berkasnya lebih dulu:
  1. **Program reseller DIHAPUS, bukan dibersihkan copy-nya.** `harih_reseller_aktif()` memakai status terbit halaman sebagai sakelar seluruh program — satu klik "Publish" di wp-admin akan menghidupkan kembali tautan footer, formulir norek, dan janji "komisi 30%" sekaligus, tanpa satu baris kode berubah. Menyunting kalimatnya tidak menutup itu. `page-jadi-reseller.php` + `reseller.js` dihapus dari repo **dan dari server**; enam sambungan di `functions.php`, footer, katalog, dan `page-teks.php` ikut dicabut. PHP lint bersih di keempat berkas.
  2. **`buat-toko.sh` ternyata akan MENERBITKAN ULANG halaman itu** (`--post_status=publish`) — sekarang tanpa template, jadi ia jatuh ke tema induk dan menayangkan halaman kosong yang terindeks. Bloknya dicabut. Ini kembaran `R5`, ditemukan bukan dicari.
  3. **WF-04 dipindah ke `n8n/workflows/arsip/`.** Menonaktifkannya di n8n saja tidak cukup: ritual impor di README menyuruh mengimpor "sembilan berkas", jadi rebuild VPS berikutnya akan menghidupkannya kembali — lengkap dengan kode yang menyiarkan nomor rekening lewat WhatsApp. README, runbook, dan panduan manual diperbarui ke **8**.

  ⚠️ **Nyaris merusak dua halaman jualan.** Blok CSS berjudul *"Form reseller (page-jadi-reseller.php)"* di `katalog.css` sempat saya hapus seluruhnya — padahal itu **satu-satunya** definisi `.field`, `.aturan`, dan `.hero .brand a` di berkas itu, dan keduanya dipakai beranda serta `/satuan/`. Ketahuan karena selektor yang terhapus dicocokkan ke templat yang masih hidup sebelum di-deploy, bukan sesudah. Blok dipulihkan; hanya judulnya yang dibetulkan. Hanya `.form-kartu` yang kini yatim — dibersihkan bersama M8.

  **Sisa yang sengaja dibiarkan:** webhook `daftar-reseller` tetap aktif (ditulis ulang jadi onboarding mitra di **M7**) dan node `Buat Kupon WC` masih ada di WF-03 — jalur itu hanya bisa berjalan lewat klik approval owner, dan runbook §7 kini melarang mengkliknya. Penjaga kupon `RES-` di `cetak.php` **sengaja dipertahankan** sampai M1 mencabut mekanismenya; mencabutnya sekarang justru membuka jendela kupon 30% menghantam keranjang cetak.

- [x] **R2** 🤖 `hari` — **Retensi 90 hari akhirnya punya penegak** *(eks-`C8`)* → **SELESAI & LIVE 2026-08-09**
  [`kebijakan-privasi.md:59`](./konten-legal/kebijakan-privasi.md:59) menjanjikan data & foto dihapus **paling lambat 90 hari** setelah masa aktif berakhir; [`:71`](./konten-legal/kebijakan-privasi.md:71) menjanjikan hak penghapusan ditanggapi ≤7 hari kerja. Satu-satunya penegak, [`masa-aktif.php:85-88`](../wp-content/mu-plugins/undangan-core/masa-aktif.php:85), hanya `post_status => 'draft'`. **Medianya tetap publik.** Ini bukan fitur — ini janji tertulis yang belum ditepati, dan UU PDP 27/2022 adalah kerangkanya.
  **Langkah:** pass kedua di cron `undangan_cek_masa_aktif` (`masa-aktif.php:114-116`), membaca `nonaktif_sejak` yang **sudah ditulis** di `:87`. Yang dihapus: lampiran galeri & QRIS di uploads · post `ucapan` terkait · meta `daftar_tamu` · kartu OG *(`og.php:225/256` sudah punya `unlink` di `before_delete_post` — hook itu selama ini tidak pernah menyala karena tidak ada yang menghapus permanen)*. Pakai flag `$dry_run` yang sudah terpasang di `:66/:85/:92` sebelum menyalakannya.
  ⚠️ **Kecualikan `_harih_uji=1` dan undangan demo** — pengecualian demo sudah ada di `:57-60`, uji belum. Menghapus `TEST-173`/undangan 174 berarti kehilangan sumber berkas sampel cetak.
  **Selesai bila:** satu undangan uji yang dilewatkan 90 hari kehilangan medianya — **URL media membalas 404**, post `ucapan` hilang, kartu OG hilang — sementara `TEST-173` dan ketiga demo utuh.

  → **SELESAI & LIVE 2026-08-09.** Dua harness dijalankan **di produksi** dengan data yang dibuat & dibersihkan sendiri: **31 + 13 pemeriksaan, nol gagal**, dan produksi kembali persis ke keadaan semula (4 undangan, 1 order). Yang paling penting terbukti langsung, bukan disimpulkan: **URL foto galeri dan QRIS membalas 404** setelah pass berjalan — itu isi janjinya.
  **Bentuknya berbeda dari rencana di tiga titik, semuanya karena membaca kodenya lebih dulu:**
  1. **Allowlist, bukan daftar-yang-dihapus.** CPT ini punya **45** meta dan akan bertambah (M2 menambah `mitra_id`). Daftar hapus akan tertinggal diam-diam tiap kali meta baru lahir — dan yang tertinggal justru data pribadi. Sekarang meta baru terhapus secara bawaan; yang perlu bertahan harus disebut sengaja. Enam yang bertahan: `order_id`, `paket`, `template_id`, `tanggal_resepsi`, `nonaktif_sejak`, `data_dihapus`.
  2. **Media ternyata attachment tanpa `post_parent`.** WF-02 mengunggahnya lewat `POST /wp/v2/media` lalu menyimpan `source_url`-nya sebagai meta — jadi tidak ada relasi induk-anak yang bisa dipakai. Resolusi URL→attachment dibuat dua lapis: `attachment_url_to_postid()`, lalu cadangan yang mencocokkan path relatif ke `_wp_attached_file` (satu-satunya nilai yang tidak ikut berubah bila URL situs bergeser). Bila keduanya gagal, berkasnya tetap dihapus langsung — dibatasi ke dalam `uploads/` lewat perbandingan `realpath()`.
  3. **`_snapshot` di order ternyata menyimpan `daftar_tamu` juga** — jadi tanpa langkah tambahan, daftar 600 nama tamu tetap tersimpan di order meski sudah hilang dari undangannya. Kini `_snapshot` & `_proof_ip` ikut dihapus sementara `_snapshot_hash`, `_proof_hash`, dan `_proof_disetujui` **dipertahankan**: cukup membuktikan pelanggan pernah menyetujui proof (S&K §12.1) tanpa menyimpan isinya lagi. Hash-nya bertahan, datanya tidak.

  ⚠️ **`wp post meta get` berbohong soal pesanan uji.** Ia mengembalikan kosong untuk `_harih_uji` pada order 173 — sempat terbaca seperti penjaga `TEST-173` tidak terpasang. Penyebabnya **HPOS aktif**: meta order tidak lagi di `wp_postmeta`. Lewat `$order->get_meta()` nilainya `'1'` dan penjaganya benar. Untuk meta order, `wp post meta get` bukan alat yang sah.

  **Sekalian menutup janji §7** (hak penghapusan ≤7 hari kerja): `undangan_hapus_data_undangan($id)` adalah satu fungsi yang dipakai dua jalur — cron 90 hari dan permintaan pelanggan. Prosedurnya di [`runbook.md`](./runbook.md) §7b-2.

- [x] **R3** 🤝 `jam` — **Kebocoran harga grosir diuji SEBELUM M1 ditulis, bukan sesudah** → **LULUS 2026-08-09**
  Ini kegagalan paling mahal di seluruh rencana: LiteSpeed menyajikan halaman katalog ter-cache milik user login ke publik → daftar harga grosir bocor ke pengantin. Produksi mengirim `max-age=604800` sebagai default menyeluruh, dan `?to=` memang sengaja tidak memecah cache — jadi asumsi "cache per-user" tidak boleh dipakai tanpa bukti.
  **Langkah:** buat satu user uji, buka katalog dalam **tiga sesi berdampingan** (publik · user biasa · admin), bandingkan header `x-litespeed-cache` **dan** harga yang benar-benar ter-render. Periksa setelan LiteSpeed *"Do not cache logged-in users"*. Ulangi 2–3 kali — `cek-live.sh` bisa memberi gagal palsu.
  **Hasilnya menentukan bentuk M1:** bila cache tidak memisahkan user login → grosir **hanya** dihitung di keranjang/checkout (Store API, tidak pernah ter-cache) dan ditampilkan di portal mitra; `woocommerce_get_price_html` **tidak** disentuh di katalog publik.
  **Selesai bila:** ketiga sesi terbukti tidak saling mencemari, atau ketidakmampuannya terdokumentasi di sini beserta konsekuensinya pada M1.

  → **LULUS 2026-08-09 — M1 boleh memakai harga per-role di katalog.** Diuji dengan mu-plugin sementara yang menanam penanda berbeda menurut status login, lalu permintaan HTTP nyata lewat edge LiteSpeed dari luar (bukan dari server ke dirinya sendiri). Tiga putaran, masing-masing sampai cache benar-benar **HIT** di kedua sisi:

  | | `x-litespeed-cache` | `Cache-Control` | isi |
  |---|---|---|---|
  | anonim | `hit` | `public, max-age=604800` | uid=0 · harga publik |
  | login | `hit,private` | `no-cache, must-revalidate, max-age=0, no-store, private` | uid=3 · harga grosir |

  Ember publik **tidak pernah** memuat body milik user login, dan sebaliknya. Setelan yang membuatnya bekerja: **`cache-priv = 1`** (TTL privat 1800 dtk). `/cart/`, `/checkout/`, `/my-account/` tidak mengirim header cache sama sekali. Jadi rencana cadangan di M1 — "grosir hanya di keranjang, jangan sentuh katalog" — **tidak diperlukan**; `woocommerce_get_price_html` aman difilter.

  ⚠️ **Tapi ada syaratnya, dan ini harus masuk M1:** yang memisahkan keduanya adalah **satu setelan LiteSpeed**, bukan properti kode kita. Bila `cache-priv` suatu saat mati — pembaruan plugin, migrasi hosting, tombol "reset" di wp-admin — harga grosir langsung mengalir ke ember publik tanpa satu pun galat. **M1 wajib menambahkan pemeriksaan ini ke `cek-live.sh`** supaya regresinya ketahuan oleh smoke test, bukan oleh mitra yang mengirim tangkapan layar.

  🟠 **Temuan sampingan yang mengenai M8:** beranda dikirim dengan `public, max-age=604800` — **peramban pengunjung anonim menyimpannya 7 hari**. `wp litespeed-purge all` hanya membersihkan cache server; yang sudah ada di peramban tidak bisa dijangkau. Artinya setiap perubahan harga baru sampai ke pengunjung lama **sampai seminggu kemudian**. Bukan cacat baru (undangan sudah lama begitu), tapi jadi penting begitu ada dua tingkat harga.

  ⚠️ **Putaran pertama melaporkan "KEBOCORAN TERBUKTI" — dan itu palsu.** Harness-nya memakai array kosong di bawah `set -u`, sehingga **seluruh permintaan anonim gagal senyap** dan hasil kosong terbaca sebagai "bukan uid=0". Ketahuan karena tiap permintaan juga mencetak kode HTTP, dan yang anonim kosong. Pelajaran yang sama untuk kelima kalinya: hasil pertama yang tampak seperti bug diperiksa ulang dengan metode berbeda. Harness sekarang menolak menyimpulkan apa pun bila permintaannya sendiri gagal.
  Sisa uji dibersihkan: mu-plugin sementara dihapus, user uji dihapus, cache di-purge, penanda nol di HTML live.

- [x] **R4** 🤖 `jam` — **Kaki undangan bisa dikondisikan, dan owner punya demo white-label untuk menawar** → **SELESAI & LIVE 2026-08-09**
  Inti M2, dimajukan karena inilah setengah hari dengan pengaruh terbesar pada closing: menunjukkan undangan **live berlabel nama toko calon mitra** mengalahkan menjelaskannya. Kakinya sudah ada — [`penutup.php:35`](../wp-content/themes/harih/template-parts/undangan/penutup.php:35) merender *"Undangan digital oleh hariH"* di setiap undangan (K3); yang dikerjakan adalah membuatnya bercabang.
  **Langkah:** helper `harih_mitra_brand( $undangan_id ) : array{nama, logo_url, wa}` dengan **whitelist wajib** persis pola `undangan_get_temas()` — nilai meta tidak pernah dipercaya apa adanya. Cabang: order mitra → *"Undangan oleh **[Nama Mitra]**"* · order publik → teks sekarang + ajakan jadi mitra. Lalu satu undangan demo berlabel nama toko sungguhan untuk dipakai di F0.3.
  ⚠️ HTML undangan di-cache **7 hari** — purge LiteSpeed setelah deploy dan verifikasi dengan query pembeda (`?cb=…`), jangan menilai dari muatan pertama.
  **Selesai bila:** demo mitra menampilkan nama toko, demo publik menampilkan hariH, dan `mitra_id` yang dipalsukan tidak mengubah apa pun.

  → **SELESAI & LIVE 2026-08-09.** Ketiganya diperiksa pada HTML yang benar-benar dikirim, dengan query pembeda (`?cb=`) supaya cache 7 hari tidak menipu: `/u/demo-mitra/` berbunyi *"Undangan digital oleh **Percetakan Melati**"*, ketiga demo publik tetap *"oleh hariH"*, keempatnya `noindex`. Smoke **33/33**.
  **Alat jualannya nyata, bukan sekadar kodenya:** [`scripts/demo-mitra.sh`](../scripts/demo-mitra.sh) — `bash demo-mitra.sh "Percetakan Melati"` menghasilkan satu URL berlabel nama toko calon mitra dalam hitungan detik, dipakai di tengah percakapan F0.3. Satu slot dipakai bergantian; URL-nya tetap, isinya menyesuaikan. Prosedurnya jadi langkah 0 di [`panduan-manual.md`](./panduan-manual.md).
  **Slug, bukan user ID.** Rencana M2 menyebut `mitra_id` = user ID ber-role `harih_mitra`, tapi role itu baru lahir di M1 — sementara R4 harus jalan sekarang. `mitra_id` jadi **slug** yang divalidasi terhadap `undangan_get_mitra()` (option `harih_mitra_brand`), persis peran `undangan_get_temas()` untuk `template_id`. Saat M1/M7 membuat user mitra sungguhan, `user_nicename`-nya langsung jadi slug — bentuk metanya tidak perlu berubah. Meta 45 → **46**, dan `mitra_id` sengaja ditambahkan ke allowlist retensi R2.
  **Whitelist-nya dua lapis, dan lapis kedua yang benar-benar menahan:** sanitizer menolak saat menulis, tapi nilai yang sudah tersimpan tidak ikut berubah ketika mitranya kemudian dicabut dari daftar — kaki yang masih menyebut nama mitra yang sudah tidak bekerja sama persis kegagalan yang harus dicegah. Karena itu `harih_mitra_brand()` memvalidasi ulang **saat render**. Terbukti: mitra dihapus dari daftar sementara metanya tertinggal → kaki jatuh ke hariH.
  **17 pemeriksaan lulus** — termasuk `javascript:` dibuang dari `url` mitra, entri tanpa nama dibuang, dan path traversal ditolak.

  ⚠️ **Satu uji sempat lulus palsu, dan itu yang paling penting dicatat.** Pemeriksaan "nilai `mitra_id` palsu" awalnya menulis lewat `update_post_meta()` lebih dulu — yang tersanitasi jadi `''` dan **meninggalkan satu baris kosong**. Baris palsu yang ditulis langsung ke DB sesudahnya jadi baris KEDUA, sementara `get_post_meta(..., true)` membaca yang pertama. Jadi tiga asersi berikutnya lulus tanpa pernah menguji apa pun. Diulang dengan baris lama dihapus dulu → nilai palsu benar-benar efektif di DB, dan render **tetap** hariH. Tanpa memeriksa prasyaratnya sendiri, laporan ini akan menyatakan penjaga yang tidak pernah diuji.

  **Sisa untuk M2:** whitelist disambungkan ke role `harih_mitra`, dan WF-01 mengisi `mitra_id` dari pemilik order. Ajakan *"jadi mitra"* di kaki undangan publik **sengaja belum dipasang** — halaman `/mitra/` baru lahir di M8, dan disiplin `C3`/`U21` melarang menaut ke alur yang belum ada.

- [x] **R5** 🤖 `menit` — **`buat-toko.sh` berhenti bisa memutar balik harga yang sudah diputuskan** → **SELESAI 2026-08-09**
  [`buat-toko.sh:238`](../scripts/buat-toko.sh:238) masih menulis `SATUAN-UNDANGAN-LIPAT` di **15.000**, sementara harga live sudah **35.000** sejak keputusan owner 8 Agustus (`U24`). Menjalankan ulang skrip provisioning akan mengembalikannya diam-diam. Di model dua tingkat harga, jenis drift ini berlipat.
  **Selesai bila:** skrip menulis 35.000, dan tidak ada lagi harga di skrip yang berbeda dari harga live.

  → **SELESAI 2026-08-09.** Kedelapan belas SKU di skrip dibandingkan satu per satu terhadap harga live (`wp wc product list`): **`SATUAN-UNDANGAN-LIPAT` satu-satunya yang meleset**, sekarang 35.000 dan **18 dari 18 cocok**.
  ⚠️ **Bahayanya lebih sempit dari yang saya tulis di rencana.** `buat_satuan` melewati SKU yang sudah ada, jadi menjalankan ulang skrip terhadap toko yang hidup **tidak** membalik harga. Yang berbahaya adalah **pembangunan ulang dari nol** — pindah hosting, restore DB kosong, staging: di sana harga yang salah lahir kembali tanpa satu pun peringatan. Tetap diperbaiki, dengan alasannya ditulis di sebelah angkanya supaya tidak dikembalikan orang berikutnya.

---

## 🟡 F0 — jual dulu. Nol fitur baru. *(minggu ini)*

> Tidak ada task fitur di fase ini. Kalau ada fitur baru dikerjakan minggu ini, fase ini gagal. P0 di atas adalah pengecualian yang sudah dibatasi — utang, risiko, dan satu alat jualan.

- [ ] **F0.1** 👤 `jam` — **Kunci DUA angka grosir.** Hormat sudah dicabut (**B9**), jadi yang tersisa Resepsi & Grand — dan keduanya sudah lolos lantai **B10** di harga usulan konsultan (735rb & 1,31jt per jam tangan, lantai 600rb). Yang benar-benar perlu diverifikasi tinggal **bahan terkini** dan **ongkir ke mitra Jabodetabek** (bisa jauh di bawah alokasi Rp 150rb, atau nol bila mitra ambil sendiri).
  **Selesai bila:** dua angka grosir tertulis di dokumen ini — Resepsi & Grand — masing-masing lolos ≥ Rp 600rb/jam tangan dengan HPP terkini.
- [ ] **F0.2** 👤 `jam` — **Susun daftar 20 WO + 10 percetakan** di Tangerang & Jakarta Barat. Sekalian verifikasi K4: cek satu-dua tawaran white-label pemain besar, catat harga & isinya apa adanya.
  **Selesai bila:** 30 nama + nomor WA tertulis di satu tempat, dan angka pembanding K4 sudah diganti dengan yang benar-benar dilihat.
- [ ] **F0.3** 👤 `hari` — **Hubungi semuanya. Yang dijual hanya GROSIR.** Manual sepenuhnya. Tawaran = harga grosir (dua paket) + pembalik risiko **B14** + demo dari **R4**, undangan live berlabel nama toko mereka.
  ⛔ **Jangan sebut retainer sama sekali di percakapan ini** (**B15**). Menawarkan langganan bulanan ke orang yang belum pernah membeli apa pun adalah permintaan tersulit yang bisa diajukan, dan ia menenggelamkan tawaran grosir yang justru mudah diterima.
  Kalimat pembalik risikonya, apa adanya: *"Order pertamamu telat dari H-14 atau kliennya menolak hasilnya — uangmu kembali penuh, cetakannya tetap kamu ambil."*
  **Selesai bila:** 30 percakapan terjadi. Bukan 30 penjualan — 30 percakapan. Teman yang setuju karena tidak enak **tidak dihitung**.
- [ ] **F0.4** 👤 `jam` — **Mitra yang setuju:** buat order manual (jalur `A7`), bayar di muka harga grosir, undangan pertama dikerjakan tangan. Nol kode baru dibutuhkan untuk ini.
  **Selesai bila:** uang masuk rekening dan undangan pertama mitra tayang — bukan "sudah sepakat".
- [ ] **F0.6** 👤 `menit` — **Retainer ditawarkan saat order KEDUA, bukan sebelumnya** (**B15**). Momen yang tepat: mitra kembali dan slot bulan itu sudah diambil orang lain. Satu kalimat, tanpa tanda bintang: *"Slot dikunci atas namamu. Saldo hangus akhir bulan."*
  **Selesai bila:** minimal satu mitra sudah ditawari retainer di momen itu, dan jawabannya — ya maupun tidak — dicatat beserta alasannya. Itu data pertama tentang apakah tesis retainer hidup.
- [ ] **F0.5** 👤 `menit` — **Catat keberatan yang muncul, kata demi kata.** Itu bahan halaman `/mitra/` (M8) — dibangun dari kalimat nyata, bukan tebakan.
  **Selesai bila:** daftar keberatan tertulis di sini, dengan kalimat mereka — bukan ringkasan saya.

> ### 🚪 GERBANG 0 → 1
> **3 mitra berbeda sudah membayar order grosir pertama. Uang masuk rekening.** *(**B12**)*
> Bukan "3 mitra tertarik". Bukan "sedang mempertimbangkan". Terbayar.
> Sebelum ini terpenuhi, **tidak ada satu pun task M1–M8 yang boleh dimulai.**
> **Retainer tidak dihitung di gerbang ini** — ia memang belum ditawarkan (**B15**). Tesis retainer diuji sesudahnya, saat mitra yang sama kembali untuk order kedua dan menemukan slotnya sudah diambil orang lain.

---

## F1 — infrastruktur mitra *(setelah gerbang 0)*

Estimasi: 5–8 hari kerja. Urutan grup adalah urutan kerja.

- [ ] **M1** 🤖 `hari` — **Role `harih_mitra` + harga per role.** Berkas: `mu-plugins/undangan-core/mitra.php` (baru) · ubah `woocommerce.php`.
  `add_role('harih_mitra')` kapabilitas setara `customer` · field produk `_harga_mitra` · filter `woocommerce_product_get_price` + `get_regular_price`. **Cabut seluruh logika kupon `RES-`** ([`cetak.php:193-213`](../wp-content/mu-plugins/undangan-core/cetak.php:193)) — mekanismenya diganti, jadi penjaganya ikut hilang.
  ✅ **R3 sudah menjawab: harga per-role AMAN di katalog.** Cache publik dan cache privat terbukti tidak pernah bercampur, jadi `woocommerce_get_price_html` boleh difilter seperti biasa — rencana cadangan "grosir hanya di keranjang" tidak diperlukan.
  ⚠️ **Tapi yang memisahkannya adalah satu setelan LiteSpeed (`cache-priv = 1`), bukan properti kode kita.** Bila setelan itu mati — pembaruan plugin, migrasi hosting, tombol reset di wp-admin — harga grosir mengalir ke ember publik **tanpa satu pun galat**. Karena itu M1 **wajib menambahkan pemeriksaan tiga sesi ke `cek-live.sh`**: regresinya harus ketahuan oleh smoke test, bukan oleh mitra yang mengirim tangkapan layar.
  ⚠️ Checkout memakai **WooCommerce Blocks** — `woocommerce_checkout_fields` & `woocommerce_after_checkout_validation` tidak berlaku. Pelajaran ini sudah terulang tiga kali.
  ⚠️ Deteksi paket WF-01 berbasis **SKU**, bukan harga — pastikan `_harga_mitra` tidak mengubah tier yang terdeteksi.
  **Selesai bila:** produk yang sama membebankan Rp 179rb ke publik dan Rp 99rb ke mitra di checkout · nol kemunculan `RES-` di basis kode aktif · tiga sesi berdampingan tidak saling mencemari.

- [ ] **M2** 🤖 `jam` — **`mitra_id` terisi sendiri dari order, bukan disetel tangan.** Meta, whitelist, helper brand, dan kaki bercabang **sudah selesai di R4** (45 → 46). Yang tersisa dua: (1) `undangan_get_mitra()` disambungkan ke user ber-role `harih_mitra` — slug = `user_nicename`, jadi bentuk metanya tidak berubah; (2) **WF-01 mengisi `mitra_id` dari pemilik order**.
  **Selesai bila:** order dari akun mitra menghasilkan undangan berlabel nama mitra tanpa satu pun langkah manual.

- [ ] **M3** 🤖 `hari` — **Portal mitra.** Berkas: `themes/harih/page-mitra-portal.php` (baru). Isi: daftar order + status · sisa slot bulan ini · daftar harga grosir · tombol order baru · link form isi data per order.
  Otentikasi `is_user_logged_in()` + cek role (**B3**), bukan token — ini halaman ber-login pertama di proyek.
  ⚠️ **`nocache_headers()` + `do_action('litespeed_control_set_nocache')` wajib**, mengikuti pola kelima halaman bertoken. Halaman personal yang ter-cache seminggu sudah pernah menyebabkan kegagalan senyap.
  **Selesai bila:** dua mitra login bersamaan melihat data masing-masing, dan nol header cache di responsnya.

- [ ] **M6** 🤖 `jam` — **Order mitra tidak mati minggu depan.** `masa-aktif.php`: order ber-`mitra_id` → masa aktif minimal 1 tahun, abaikan aturan H+7 tanpa memandang tier *(peta tier ada di `:29-31`)*.
  **Alasan bisnis:** undangan yang mati bukan komplain ke hariH — itu komplain ke mitra, di depan kliennya. Sekali kejadian, mitranya hilang.
  ⚠️ Bergantung pada keputusan owner #1 (nasib undangan mitra yang berhenti bayar).
  **Selesai bila:** order mitra bertahan 1 tahun; order publik tetap kedaluwarsa sesuai tiernya.

- [ ] **M7** 🤝 `jam` — **WF-03 jadi onboarding mitra.** Buat user WordPress role `harih_mitra` + kirim kredensial via WA & email. Tab Sheets `resellers` → `mitra`: `user_id · nama · wa · jatah_slot · tgl_mulai · status` — **tanpa kolom bank & norek** (sudah dicabut di R1).
  ⚠️ `import:workflow` **menonaktifkan** workflow setelah impor, tanpa peringatan. Jalankan `publish:workflow` untuk **setiap** id, lalu hitung ulang.
  **Selesai bila:** satu pendaftaran uji menghasilkan user ber-role yang bisa login ke portal M3.

- [ ] **M8** 🤖 `hari` — **Halaman jualan `/mitra/` + `/harga/` berhenti meng-hardcode harga cetak.** Berkas: `themes/harih/page-mitra.php` (baru) · `page-harga-hybrid.php`.
  Susunan: judul (hasil transformasi, bukan fitur) → matematika untung **dua paket** (Hormat tidak muncul — **B9**) → cara kerja 3 langkah → pembalik risiko **B14** apa adanya → satu CTA. **Dibangun dari keberatan nyata F0.5, bukan tebakan.**
  ⛔ **Halaman ini menjual grosir, bukan retainer** (**B15**). Retainer tidak punya tempat di halaman yang dibaca orang asing.
  Berlaku disiplin `C3`/`U21`: nol klaim yang tidak ditepati alur — kalau tombolnya membuka WhatsApp, jangan menulis "checkout otomatis". Dan **B11**: jangan menjual "hanya 8 slot" sebagai kelangkaan.
  Sekalian: harga cetak di `page-harga-hybrid.php` dibaca lewat `harih_harga_*`, bukan ditulis ulang (K6) — sekarang ada **dua** tingkat harga yang bisa saling menyimpang.
  **Selesai bila:** nol angka harga hardcode di kedua halaman · setiap klaim di halaman bisa ditunjuk alurnya.

> ### 🚪 GERBANG 1 → 2
> **8 slot cetak terisi penuh dalam satu bulan kalender.**

---

## F2 — kapasitas & distribusi *(setelah gerbang 1)*

- [ ] **M4** 🤖 `hari` — **Ledger alokasi slot per mitra.** Berkas: [`cetak.php`](../wp-content/mu-plugins/undangan-core/cetak.php:380) — **bukan** `kuota.php`, berkas itu tidak ada (K6).
  Option `harih_slot_alokasi` = `{ user_id: jatah }` · slot publik = `UNDANGAN_KUOTA_BULAN` − Σ alokasi · cek di `undangan_cart_ada_fisik()` (`:38`) · pergantian bulan mengikuti pola WIB yang sudah dipakai (`wp_date('Y-m')` di `:412`, `wp_date('Y-m-01')` di `:445`) — **jangan menulis logika waktu baru** · order `_harih_uji=1` tetap tidak menghitung kuota.
  **Selesai bila:** mitra berjatah 2 diblokir di order ke-3 · slot publik menyusut sesuai alokasi · pergantian bulan tepat tengah malam WIB.

- [ ] **M5** 🤖 `jam` — **Pelacakan rujukan tahan-cache.** Kakinya sudah selesai di R4; sisanya rute + penghitung.
  **Pakai path, bukan query:** `/mitra/{slug-mitra}/`. LiteSpeed men-*drop* `ref`/`utm_*` dari kunci cache, jadi `?ref=` tidak sampai ke PHP. ⚠️ Ini akan jadi **`add_rewrite_rule` pertama di proyek** — `/u/{slug}` selama ini hanya `rewrite` bawaan CPT; jadwalkan `wp rewrite flush` setelah deploy. Penghitung kunjungan di `option`, bukan GA4.
  **Selesai bila:** buka undangan mitra → klik kaki → `/mitra/{slug}/` terbuka dengan nama mitra tampil, dan header cache halaman undangan tetap `hit`.

- [ ] **M9** ⏸ **DITAHAN** 👤 — *rekrut 2 percetakan Jabodetabek sebagai partner fulfillment.*
  Ditahan karena **K2**: kuota 8 bukan batas kapasitas (±14 jam tangan dari ruang yang jauh lebih besar), jadi menaikkan kuota belum butuh orang lain. Dan bertabrakan dengan keputusan terkunci lama *"satu orang tidak boleh jadi reseller sekaligus vendor"* — §2.5 sudah merekrut percetakan sebagai **kanal**.
  **Dibuka kembali bila:** slot terisi penuh dua bulan berturut-turut **dan** owner sudah mengukur ulang jam tangan pada volume itu.

> ### 🚪 GERBANG 2 → 3
> **20 mitra aktif ATAU Rp 25jt pendapatan berulang bulanan.**

---

## F3 — skala kanal *(setelah gerbang 2)*

Buka mitra luar Jabodetabek (digital + berkas siap cetak saja — **B4**) · jaringan percetakan lokal per kota sebagai fulfillment, sistem & pelanggan tetap dipegang hariH · occasion baru (khitanan, aqiqah, wisuda) — sekarang masuk akal karena mitranya **sudah ada**, tinggal ditawari produk kedua.

## F4 — belum dijadwalkan: rekap amplop digital

⛔ **Menahan dana orang lain adalah kegiatan berizin (PJP Bank Indonesia).** Jangan pernah membangun escrow tanpa nasihat hukum — ini bukan risiko produk, ini risiko pidana.
✅ Versi patuh yang bisa dibangun: **QRIS tetap milik mempelai, dana tidak pernah lewat hariH.** Yang dijual adalah **rekap otomatis siapa memberi berapa + ucapan terima kasih otomatis via WA.** Pengantin menghabiskan berminggu-minggu merekonsiliasi ini manual. Marginnya 100%, nol sentuhan regulasi. Jadwalkan setelah 20 mitra.

---

## 🚫 Yang TIDAK dibangun sampai gerbang 2

❌ Dashboard editor undangan self-service · ❌ tema builder drag-and-drop · ❌ escrow amplop digital · ❌ domain kustom per undangan · ❌ multi-bahasa · ❌ occasion baru · ❌ migrasi Sheets → Postgres · ❌ tema ke-4 dan seterusnya · ❌ aplikasi mobile · ❌ CI/CD, unit test, APM.

**Dibekukan dari rencana lama:** `D3` — `srcset` penuh & keputusan `maxDim` 1600→1280. Bagian bebasnya sudah selesai bersama `U31`; sisanya menunggu satu cetakan uji memakai foto pemesan, dan itu bukan penghalang uang.

Kalau ada yang mengusulkan salah satu di atas, jawabannya: *"tunjukkan mitra yang menolak membayar karena ketiadaannya."*

---

## 👤 Keputusan yang menunggu owner

1. **Undangan mitra yang berhenti bayar — dimatikan, dibekukan, atau tetap hidup sampai masa aktifnya habis?** Saran: **tetap hidup sampai masa aktifnya habis.** Mematikan undangan klien orang lain adalah cara tercepat kehilangan seluruh jaringan mitra, dan biaya marginalnya mendekati nol. **Menahan M6** — tapi tidak menahan F0.
2. **"10 mitra pertama Rp 590rb selamanya"** — dikunci selamanya, atau berjangka (mis. 24 bulan)? Kurang mendesak sejak **B15**: retainer tidak lagi diucapkan ke orang asing, jadi kalimat kelangkaan itu belum akan dipakai.
3. **Duitku (`A8`)** — kejar approval + tiga pertanyaan (profil nominal Rp 99–299rb vs paket Rp 5,9jt · mekanisme refund · plafon per kanal). **Tidak memblokir apa pun**; jalur manual `A7` cukup untuk 8 order/bulan.
4. **Ongkir Indonesia Timur** — dibiarkan ditanggung slack, atau diberi syarat? Terburuk (Grand ke Indonesia Timur) ±Rp 200rb vs alokasi Rp 150rb; mayoritas Jawa ±Rp 35–50rb.

### ✅ Terjawab 9 Agustus — putaran kedua konsultan

| Yang ditanyakan | Diputuskan | Yang menyelesaikannya |
|---|---|---|
| Retainer hangus vs jaminan refund 60 hari — mana yang menang | **Keduanya menang, di tempat berbeda.** Pembalik risiko pindah ke **grosir**; retainer jadi satu kalimat tanpa jaminan. Usul saya (mempersempit jaminan) ditolak dan digantikan yang lebih baik | **B14** · **B15** · K7 #1 |
| Syarat jaminan *"tawarkan ke 5 pengantin"* | **Dicabut** — tidak bisa diverifikasi siapa pun; berakhir jadi sengketa atau jadi jaminan tanpa syarat yang ditanggung diam-diam. Diganti syarat yang bisa diamati: telat dari H-14, atau klien menolak hasilnya | **B14** |
| Paket Hormat: dicabut dari grosir, atau lantai Rp 850rb | **DICABUT.** Lantai Rp 850rb pun gagal lantai B10 (429rb/jam tangan), dan satu-satunya harga yang lolos menyisakan spread mitra 11,3% — tidak ada yang mau menjualnya | **B9** · K8 |
| Lantai marjin per jam tangan | Dinaikkan **2× → 3×** patokan (Rp 400rb → **Rp 600rb**). Resepsi & Grand tetap lolos, jadi nol harga berubah | **B10** |
| Kapan retainer ditawarkan | **Order ke-2/ke-3**, bukan percakapan pertama. Gerbang 0 otomatis jadi murni grosir | **B15** · **B12** |

**Terbawa dari dokumen lama, masih berlaku:** jalur bayar manual = ya, seluruh CTA ke WhatsApp · Garansi Tepat Waktu punya syarat mulai, bukan rumus geser · bobot terukur 1,4/2,6/3,8 kg sudah hidup di WooCommerce · harga satuan 15rb → 35rb, pembedanya struktural · badge "Paling Populer" dicabut · WebP dibangun sendiri, bukan QUIC.cloud · foto produk = render AI berlabel *"ilustrasi"* sampai foto asli ada.

---

## 👤 Aksi owner — yang masih menunggu tangan Anda

> Ini **pekerjaan**, bukan keputusan. Diurutkan menurut yang paling menahan uang.

1. **F0.3 — hubungi 30 orang.** Satu-satunya hal yang memindahkan angka. Semua di bawah ini bisa menunggu.
2. **Duitku** — kejar approval → `A8`.
3. **Foto produk sungguhan.** Lima slot terisi render AI berlabel *"ilustrasi"*. Memotret sampel `TEST-173` yang sudah tercetak — plus bengkel, mesin, dan tangan yang sedang melipat — menggantinya dengan bukti dan mencabut labelnya. **Untuk mitra ini lebih penting lagi:** percetakan menilai vendor dari hasil cetak, bukan dari halaman web.
4. **QA perangkat riil** — iPhone Safari & Android Chrome. 31 item UI/UX tayang sejak 8 Agustus, belum satu pun disentuh tangan manusia. ⚠️ WA meng-cache preview per URL — uji dengan `?x=1`.
5. **Kebijakan nomor WA bisnis** *(berlaku terus)* — jangan logout, pakai wajar, jangan blast ke nomor tak dikenal. Sesi ter-ban = seluruh delivery mati.

---

## ⚠️ Wajib dibaca sebelum menyentuh n8n / deploy

1. **Import n8n bisa diam-diam memakai berkas BASI.** `scp` ke `/tmp` server gagal (uid lain + sticky bit) **tapi `import:workflow` tetap melaporkan sukses** — WF-02 live sempat mundur tanpa satu pesan galat. Prosedur benar ada di [`../n8n/workflows/README.md`](../n8n/workflows/README.md).
2. **`import:workflow` MENONAKTIFKAN workflow yang diimpor — tanpa peringatan.** Terukur: sebelum import 9 aktif, sesudah **0 aktif**, sementara `list:workflow` tetap menampilkan kesembilannya. Selama jendela itu webhook `wc-order` & `form-undangan` mati — **order yang masuk hilang tanpa jejak**. `publish:workflow` wajib untuk **setiap** id yang diimpor, lalu `docker restart harih-n8n`, lalu **hitung ulang** `list:workflow --active=true`. Kerjakan saat tidak ada order berjalan.
3. **Container hariH bernama `harih-n8n`.** VPS yang sama menjalankan **`root-n8n-1`** — n8n produksi lain milik owner. Jangan pernah menjalankan perintah n8n tanpa menyebut container.
4. **Jangan taruh node yang bisa menghasilkan NOL item di tengah rantai.** Node HTTP n8n memecah respons array jadi satu item per elemen, jadi `[]` = nol item = seluruh cabang berikutnya tidak dijalankan, **tanpa galat**.
5. **Restart n8n:** `docker restart harih-n8n`. Hindari bare `docker compose up -d` — berisiko me-recreate WAHA.
6. **OPcache:** perubahan mu-plugin butuh sampai ±60 dtk sebelum web SAPI menyajikannya, dan `wp eval` **tidak** memperlihatkannya. Lihat [`runbook.md`](./runbook.md) §9d.
7. **Cache-buster aset sudah per-berkas** lewat `filemtime()` (`U28`); `HARIH_VERSION` tinggal fallback. Yang **masih** perlu diingat: **HTML undangan di-cache 7 hari** — purge LiteSpeed dan verifikasi dengan query pembeda, jangan menilai dari muatan pertama.
8. **`cek-live.sh` bisa memunculkan `HTTP 000` palsu** — hambatan ada di jalur jaringan lingkungan kerja, bukan di situs. **Ulangi per URL sebelum menyimpulkan ada regresi. Jangan naikkan paket hosting atas dasar ini.**
9. **Jalankan berkasnya, jangan dibaca.** Empat kali pada 8 Agustus metode ini menyelamatkan dari "bug" yang tidak ada: `innerWidth` panel = 0 · CSS "tidak berpengaruh" (cache 7 hari) · smoke "2 gagal" (gangguan jaringan) · media query yang kalah **urutan sumber**, bukan tidak terpasang. Hasil pertama yang tampak seperti bug **diverifikasi ulang dengan metode berbeda** sebelum ada yang diperbaiki.

---

## 📏 DIUKUR 2026-08-08 — dasar B9/B10

Sampel `TEST-173` dicetak & dilipat sungguhan. Angka di bawah **pengukuran, bukan model.** Ini yang membuat F0.1 tidak perlu mengulang perhitungan HPP dari nol.

Bahan **Rp 3.200/unit** (kertas 900 · amplop 1.500 · label 150 · tinta 500 · gagal 5% 150). Setup per pesanan **±40 menit**. Dari ±118 detik per unit, **±90 detik printer jalan sendiri** — karena itu **jam dinding** dipisah dari **jam tangan**.

| Paket | Unit | Jam dinding | **Jam tangan** | Marjin (eceran) | per jam dinding | **per jam tangan** |
|---|---|---|---|---|---|---|
| Hormat | 50 | 2,3 | **1,2** | Rp 855rb | Rp 372rb | **Rp 713rb** |
| Resepsi | 100 | 4,0 | **1,7** | Rp 2,40 jt | Rp 599rb | **Rp 1,41 jt** |
| Grand | 150 | 5,7 | **2,2** | Rp 5,18 jt | Rp 908rb | **Rp 2,35 jt** |

Pembanding pekerjaan cetak reguler: **Rp 100–200rb/jam**. HPP turunan: Hormat **Rp 335rb** · Resepsi **Rp 500rb** · Grand **Rp 720rb**.

**Mesin creasing bukan hambatan** (±8 detik/lembar termasuk lipat → 800 lipatan/bulan ≈ 1,8 jam). **Bobot** 1,4 / 2,6 / 3,8 kg, sudah hidup di WooCommerce. **Alokasi ongkir Rp 150rb aman** — mayoritas Jawa ±Rp 35–50rb, terburuk ±Rp 200rb.

**Kuota 8/bulan jauh di bawah kapasitas** (8 × 1,7 jam tangan ≈ 14 jam). Ditahan di 8 untuk bulan pertama karena belum pernah satu pesanan pun dikirim tepat waktu — **bukan karena mesinnya penuh** (**B11**). ⚠️ Kuota per bulan **musim, bukan rata**: pernikahan menumpuk, 8×12 akan meleset jauh.

---

## Akses

Hostinger `ssh -p 65002 u803921702@147.93.80.20` (`domains/harih.id/public_html`) · VPS `ssh root@31.97.50.197` (`/opt/harih`) · rahasia: `vps/.env` + `vps/google-sa.json` (lokal, gitignored, tidak pernah masuk riwayat git) = cermin server.

Deploy: `rsync -az --no-perms --no-times --checksum` → `wp litespeed-purge all` → tunggu ±60 dtk OPcache untuk mu-plugin.

Aksi owner: [`panduan-manual.md`](./panduan-manual.md) · operasional: [`runbook.md`](./runbook.md) · import n8n: [`../n8n/workflows/README.md`](../n8n/workflows/README.md) · cetak biru konsultan: berkas asli di luar repo · riwayat & konteks lama: [`arsip/TASKS-2026-08-09.md`](./arsip/TASKS-2026-08-09.md) dan [`arsip/TASKS-2026-08-07.md`](./arsip/TASKS-2026-08-07.md).
