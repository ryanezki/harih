<?php
/**
 * Template Name: Katalog hariH
 *
 * Halaman katalog/landing — dipasang sebagai front page oleh
 * scripts/buat-toko.sh. Dirender standalone (tanpa CSS Astra/WooCommerce,
 * lihat dequeue di functions.php) supaya ringan & bebas bentrok CSS.
 *
 * REDESAIN 2026-08-06 — konsep "Gen-Z editorial" dari handoff desain:
 * serif display beraksen italic berwarna, foto arch bersticker, strip marquee,
 * kartu chunky ber-radius besar, carousel tema scroll-snap, dan layout fluid
 * TANPA media query (clamp + auto-fit + flex-wrap + scroll-snap).
 * Sekaligus mengeksekusi temuan review UX: bukti visual produk di atas fold,
 * baris trust pembayaran, kontras teks lolos WCAG AA, filter tamu yang bisa
 * di-reset, dan CTA penutup.
 *
 * Tombol beli → /checkout/?add-to-cart=<id> — mu-plugin mengganti paket lain
 * di cart sebelum add (F3.5), jadi 1 klik = 1 paket, langsung ke checkout.
 */

if (!defined('ABSPATH')) exit;

$harih_paket = [
    [
        'sku'    => 'HARIH-HEMAT',
        'nama'   => 'Hemat',
        'harga'  => '99',
        'sub'    => 'Semua esensi undangan digital.',
        'badge'  => '',
        'fitur'  => [
            'Cover, countdown, akad &amp; resepsi + Google Maps',
            'Musik latar instrumental',
            'RSVP + ucapan tamu',
            'Nama tamu otomatis di link',
            '3 tema dasar',
        ],
    ],
    [
        'sku'    => 'HARIH-FAVORIT',
        'nama'   => 'Favorit',
        'harga'  => '179',
        'sub'    => 'Paling laris — lengkap untuk hari bahagiamu.',
        'badge'  => 'Paling Laris ✦',
        'fitur'  => [
            'Semua fitur paket Hemat',
            'Galeri sampai 10 foto + kisah cinta',
            'Amplop digital: rekening + QRIS',
            'Semua pilihan tema',
            'Revisi data 1× gratis via CS',
            'Masa aktif sampai H+30',
        ],
    ],
    [
        'sku'    => 'HARIH-PREMIUM',
        'nama'   => 'Premium',
        'harga'  => '299',
        'sub'    => 'Terlengkap, termasuk video &amp; prioritas.',
        'badge'  => '',
        'fitur'  => [
            'Semua fitur paket Favorit',
            'Video / live streaming embed',
            'Akses tema premium (menyusul)',
            'Revisi data 3× gratis + prioritas CS',
            'Masa aktif sampai 1 tahun',
        ],
    ],
];

/** URL beli: langsung checkout. Kosong bila produk belum dibuat (pre-deploy). */
function harih_url_beli(string $sku): string {
    if (!function_exists('wc_get_product_id_by_sku') || !function_exists('wc_get_checkout_url')) return '';
    $id = (int) wc_get_product_id_by_sku($sku);
    if (!$id) return '';
    return add_query_arg('add-to-cart', $id, wc_get_checkout_url());
}

/**
 * Demo per tema — daftarnya diturunkan dari `undangan_get_temas()` supaya tema
 * baru otomatis ikut tertaut di sini (slug demo: `demo-{tema}`).
 */
$harih_temas = function_exists('undangan_get_temas')
    ? undangan_get_temas()
    : ['tema-01' => 'Tema 01 — Botanical Elegan'];

/** "Tema 01 — Botanical Elegan" → "Botanical Elegan" (label kartu tema). */
function harih_nama_tema(string $label): string {
    $bagian = preg_split('/\s*—\s*/u', $label, 2);
    return trim($bagian[1] ?? $label);
}

/** Keterangan kecil per tema — sifat visualnya, bukan daftar fiturnya. */
$harih_tema_sifat = [
    'tema-01' => 'klasik · sage &amp; emas',
    'tema-02' => 'modern · terakota &amp; krem',
    'tema-03' => 'dramatis · navy &amp; emas',
];

$harih_ada_reseller = (bool) get_page_by_path('jadi-reseller');
$harih_ada_harga    = (bool) get_page_by_path('harga');
$harih_aset         = get_stylesheet_directory_uri() . '/aset';
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<?php /* Deskripsi SERP — ±155 karakter, memuat kata kunci + kanal */ ?>
<meta name="description" content="Undangan pernikahan digital yang jadi otomatis dalam hitungan menit dan langsung terkirim ke WhatsApp — plus undangan cetak beramplop nama untuk yang paling Anda hormati.">
<?php /* Open Graph — reseller & pemesan membagikan link ini di WA */ ?>
<meta property="og:type" content="website">
<meta property="og:site_name" content="hariH">
<meta property="og:locale" content="id_ID">
<meta property="og:title" content="hariH — Undangan Pernikahan Digital">
<meta property="og:description" content="Undangan pernikahan digital yang jadi otomatis dalam hitungan menit dan langsung terkirim ke WhatsApp. RSVP, galeri foto, amplop digital.">
<meta property="og:url" content="<?php echo esc_url(home_url('/')); ?>">
<meta property="og:image" content="<?php echo esc_url(harih_og_default()); ?>">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta name="twitter:card" content="summary_large_image">
<?php wp_head(); ?>
</head>
<body class="katalog-body">

<?php get_template_part('template-parts/toko/nav', null, ['beranda' => true]); ?>

<section class="hero hero-utama">
    <div class="hero-teks">
        <p class="hero-badge">Undangan Digital · No Ribet</p>
        <h1>Undangan nikah yang <em class="aksen-emas">aesthetic</em>, jadi dalam <em class="aksen-sage">hitungan menit</em>.</h1>
        <p class="hero-sub">Pilih paket, bayar, isi data dari HP — undangan cantikmu langsung terkirim ke WhatsApp &amp; email. Tanpa antre desainer, tanpa nunggu berhari-hari.</p>
        <div class="hero-cta">
            <a class="btn btn-utama" href="#paket">Lihat Paket →</a>
            <a class="btn btn-garis" href="#tema">Intip Contohnya</a>
        </div>
        <?php /* Baris trust (temuan review A2): kanal pembayaran disebut sebelum
                 pengunjung menekan apa pun — keberatan "aman tidak?" dijawab di muka. */ ?>
        <p class="hero-trust"><strong>Mulai Rp 99rb</strong>, sekali bayar · QRIS, VA &amp; e-wallet — gateway berlisensi</p>
    </div>
    <div class="hero-visual">
        <div class="hero-panggung">
            <div class="hero-arch hero-arch-1" data-naik>
                <img src="<?php echo esc_url($harih_aset . '/demo/harih-cincin-buket.jpg'); ?>" alt="" width="600" height="800" fetchpriority="high" decoding="async">
            </div>
            <div class="hero-arch hero-arch-2">
                <img src="<?php echo esc_url($harih_aset . '/demo/harih-gaun-detail.jpg'); ?>" alt="" width="600" height="800" loading="lazy" decoding="async">
            </div>
            <span class="stiker stiker-1">±5 Menit Jadi</span>
            <span class="stiker stiker-2">Mulai 99rb</span>
            <span class="stiker stiker-3">langsung ke WA ✦</span>
        </div>
    </div>
</section>

<?php
/* Marquee: konten diduplikasi 2× agar loop translateX(-50%) mulus. */
$harih_marquee = 'jadi dalam hitungan menit ✦ mulai Rp 99rb sekali bayar ✦ langsung terkirim ke WhatsApp ✦ RSVP &amp; ucapan tamu ✦ amplop digital ✦ nama tamu otomatis di link ✦ ';
$harih_marquee = str_replace('✦', '<i>✦</i>', $harih_marquee);
?>
<div class="marquee" aria-hidden="true">
    <div class="marquee-jalur">
        <span><?php echo $harih_marquee; ?></span>
        <span><?php echo $harih_marquee; ?></span>
    </div>
</div>

<main>
    <section class="cara">
        <h2>Tiga langkah, <em class="aksen-emas">beres</em>.</h2>
        <p class="seksi-sub">Semuanya dari HP, sambil rebahan juga bisa.</p>
        <div class="cara-grid">
            <div class="cara-kartu" data-naik><span class="cara-no">1</span><strong>Pilih paket &amp; bayar</strong><span>QRIS, transfer bank, atau e-wallet — aman via payment gateway resmi.</span></div>
            <div class="cara-kartu" data-naik><span class="cara-no">2</span><strong>Isi data undangan</strong><span>Link form dikirim ke WhatsApp &amp; email. Isinya ±10 menit dari HP.</span></div>
            <div class="cara-kartu" data-naik><span class="cara-no">3</span><strong>Undangan jadi otomatis</strong><span>±5 menit setelah data dikirim, link undangan + QR code sampai ke kamu.</span></div>
        </div>
    </section>

    <?php /* Bukti visual produk (temuan review A1): tema tampil sebagai gambar,
             bukan sekadar tautan teks. Carousel scroll-snap supaya di HP tetap
             satu kartu penuh per geseran. */ ?>
    <section class="tema" id="tema">
        <div class="tema-kepala">
            <h2>Intip dulu <em class="aksen-sage">hasilnya</em></h2>
            <p>Geser untuk lihat semua tema →</p>
        </div>
        <div class="tema-track">
            <?php foreach ($harih_temas as $harih_tid => $harih_label) : ?>
            <a class="tema-kartu" href="<?php echo esc_url(home_url('/u/demo-' . $harih_tid . '/')); ?>" target="_blank" rel="noopener">
                <span class="tema-gambar"><img src="<?php echo esc_url($harih_aset . '/og/og-' . $harih_tid . '.jpg'); ?>" alt="Contoh <?php echo esc_attr(harih_nama_tema($harih_label)); ?>" width="1200" height="630" loading="lazy" decoding="async"></span>
                <span class="tema-baris">
                    <span>
                        <span class="tema-nama"><?php echo esc_html(harih_nama_tema($harih_label)); ?></span>
                        <span class="tema-sifat"><?php echo $harih_tema_sifat[$harih_tid] ?? 'bandingkan dari HP-mu'; ?></span>
                    </span>
                    <span class="tema-pill">Demo ↗</span>
                </span>
            </a>
            <?php endforeach; ?>
            <a class="tema-kartu tema-kartu-akhir" href="#tema">
                <span class="tema-akhir-judul">Tema baru<br>terus nambah</span>
                <span class="tema-akhir-pill">Lihat semua demo</span>
            </a>
        </div>

        <?php /* "Coba nama kalian" (G1.3, 2026-08-07). Melihat NAMA SENDIRI di
                 dalam produk sebelum membayar adalah pemicu keinginan paling
                 langsung yang bisa kita berikan, dan biayanya hampir nol:
                 semuanya sisi klien, hanya berlaku di undangan demo, tidak ada
                 yang disimpan, tidak ada endpoint baru. Polanya sudah terbukti —
                 `?to=Nama` dan pemilih nuansa demo bekerja persis begini.

                 `<form>` sungguhan supaya Enter di HP mengirim; JS-nya cuma
                 merakit URL, tidak ada yang dikirim ke server. */ ?>
        <form class="coba-nama" id="coba-nama" autocomplete="off">
            <p class="coba-nama-judul">Coba pakai <em class="aksen-sage">nama kalian</em></p>
            <div class="coba-nama-baris">
                <label class="coba-nama-field">
                    <span>Nama panggilan kamu</span>
                    <input type="text" id="coba-pria" maxlength="20" placeholder="Raka" required>
                </label>
                <label class="coba-nama-field">
                    <span>Nama panggilan pasangan</span>
                    <input type="text" id="coba-wanita" maxlength="20" placeholder="Sela" required>
                </label>
                <label class="coba-nama-field coba-nama-tgl">
                    <span>Tanggal <span class="coba-opsional">(opsional)</span></span>
                    <input type="date" id="coba-tgl">
                </label>
            </div>
            <div class="coba-nama-kaki">
                <button type="submit" class="btn btn-utama">Lihat undangan kami →</button>
                <span class="coba-nama-catatan">Langsung terbuka — tanpa daftar, tanpa bayar.</span>
            </div>
        </form>
    </section>

    <section class="paket" id="paket">
        <h2>Pilih <em class="aksen-emas">paketmu</em></h2>
        <p class="seksi-sub">Sekali bayar, tanpa biaya tersembunyi.</p>

        <?php /*
          Satu pertanyaan sebelum tabel harga. Tujuannya bukan segmentasi
          canggih — tujuannya berhenti menawarkan paket Hemat kepada resepsi
          gedung 300 tamu: masa aktif H+7 dan tanpa galeri jelas tidak cocok,
          dan pembelinya kecewa. Penyaringan dilakukan di klien; SEMUA kartu
          tetap ada di DOM supaya mesin pencari membaca seluruh tangga harga.
          Klik kedua pada chip yang sama = reset (temuan review A4).
        */ ?>
        <div class="tamu-tanya" id="tamu-tanya">
            <p class="tamu-label" id="tamu-label">Kira-kira tamumu berapa?</p>
            <div class="tamu-opsi" role="group" aria-labelledby="tamu-label">
                <button type="button" class="tamu-btn" data-tamu="kecil">Di bawah 100</button>
                <button type="button" class="tamu-btn" data-tamu="sedang">100–200</button>
                <button type="button" class="tamu-btn" data-tamu="besar">Di atas 200</button>
            </div>
            <p class="tamu-catatan" id="tamu-catatan"></p>
        </div>

        <div class="paket-grid">
            <?php foreach ($harih_paket as $p) : $beli = harih_url_beli($p['sku']); $unggulan = $p['badge'] !== ''; ?>
            <article class="paket-card<?php echo $unggulan ? ' unggulan' : ''; ?>" data-paket="<?php echo esc_attr(strtolower($p['nama'])); ?>">
                <?php if ($unggulan) : ?><p class="paket-badge"><?php echo esc_html($p['badge']); ?></p><?php endif; ?>
                <p class="paket-label"><?php echo esc_html(strtoupper($p['nama'])); ?></p>
                <p class="paket-harga"><?php echo esc_html($p['harga']); ?><span class="rb"> rb</span></p>
                <p class="paket-sub"><?php echo $p['sub']; ?></p>
                <ul class="paket-fitur">
                    <?php foreach ($p['fitur'] as $f) : ?><li><span class="fitur-cek" aria-hidden="true">✓</span><?php echo $f; ?></li><?php endforeach; ?>
                </ul>
                <?php if ($beli) : ?>
                    <a class="btn btn-blok <?php echo $unggulan ? 'btn-terang' : 'btn-garis-sage'; ?>" href="<?php echo esc_url($beli); ?>">Pesan <?php echo esc_html($p['nama']); ?></a>
                <?php else : ?>
                    <span class="btn btn-blok btn-nonaktif" aria-disabled="true">Segera tersedia</span>
                <?php endif; ?>
            </article>
            <?php endforeach; ?>
        </div>
    </section>

    <?php if ($harih_ada_harga) : ?>
    <section class="band-cetak" id="cetak">
        <div class="band-kartu">
            <img class="band-foto" src="<?php echo esc_url($harih_aset . '/demo/harih-cincin-sepatu.jpg'); ?>" alt="" width="1200" height="800" loading="lazy" decoding="async">
            <div class="band-scrim" aria-hidden="true"></div>
            <div class="band-isi">
                <p class="band-badge">Undangan Cetak</p>
                <h2>Buat orang tua &amp; sesepuh: undangan cetak beramplop nama, <em class="aksen-emas-muda">satu desain</em> dengan undangan digitalmu.</h2>
                <p>Detail acara terbaca tanpa HP, QR-nya membuka undangan digital kalian. Dari data yang sama, gratis ongkir ke seluruh Indonesia.</p>
                <a class="btn btn-terang" href="<?php echo esc_url(home_url('/harga/')); ?>">Lihat Paket Cetak</a>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <section class="faq">
        <h2>Sering <em class="aksen-emas">ditanya</em></h2>
        <details><summary>Berapa lama undangan saya jadi?</summary><p>Setelah kamu mengisi form data (±10 menit), undangan dibuat otomatis dan link-nya dikirim ke WhatsApp &amp; email dalam ±5 menit.</p></details>
        <details><summary>Bagaimana cara membagikan ke tamu?</summary><p>Cukup bagikan satu link via WhatsApp. Nama tamu bisa muncul otomatis di halaman pembuka dengan menambah <code>?to=Nama%20Tamu</code> di belakang link — panduannya dikirim bersama undangan.</p></details>
        <details><summary>Bisa revisi setelah jadi?</summary><p>Bisa, lewat CS. Paket Favorit dapat 1× revisi gratis, Premium 3× gratis dan didahulukan. Paket Hemat dan revisi di luar jatah dikenakan Rp 25 ribu per pengajuan. Kalau kesalahannya dari sistem kami, koreksinya selalu gratis. Revisi dikerjakan maksimal 2×24 jam — ajukan paling lambat H-3 sebelum acara.</p></details>
        <details><summary>Pembayarannya bagaimana?</summary><p>QRIS, virtual account bank, dan e-wallet — diproses payment gateway berlisensi resmi. Kamu menerima bukti pembayaran otomatis via email.</p></details>
    </section>

    <?php /* CTA penutup (temuan review A5): pembaca yang sampai bawah tidak
             dibiarkan buntu tanpa ajakan. */ ?>
    <section class="cta-penutup">
        <h2>Hari bahagiamu, <em class="aksen-emas-muda">diundang dengan indah</em>.</h2>
        <p>Mulai Rp 99 ribu, sekali bayar — jadi dalam hitungan menit.</p>
        <a class="btn btn-terang" href="#paket">Pilih Paket Sekarang →</a>
    </section>
</main>

<?php get_template_part('template-parts/toko/kaki', null, null); ?>

<script>
(function () {
    'use strict';
    // "Coba nama kalian" (G1.3): merakit URL demo, tidak mengirim apa pun.
    // Nama TIDAK di-encode manual — URLSearchParams yang mengurusnya, jadi
    // nama ber-& / spasi / emoji aman (pelajaran dari `?to=` di FU.6, yang
    // sempat perlu diperbaiki untuk kasus "Bapak Hendra & Ibu Sari").
    var form = document.getElementById('coba-nama');
    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var pria   = document.getElementById('coba-pria').value.trim();
            var wanita = document.getElementById('coba-wanita').value.trim();
            var tgl    = document.getElementById('coba-tgl').value;
            if (!pria || !wanita) return;

            var q = new URLSearchParams();
            q.set('pria', pria);
            q.set('wanita', wanita);
            if (/^\d{4}-\d{2}-\d{2}$/.test(tgl)) q.set('tgl', tgl);
            q.set('buka', '1');   // gerbang langsung terbuka — tanpa ini pengunjung
                                  // harus menekan "Buka Undangan" dulu dan efek
                                  // "nama kami muncul" hilang di layar pertama
            // Tema pertama di carousel = tema paling laku; dari sana pengunjung
            // bisa berpindah lewat tautan demo yang lain.
            var demo = document.querySelector('.tema-track .tema-kartu[href*="/u/demo-"]');
            if (!demo) return;
            window.open(demo.getAttribute('href') + '?' + q.toString(), '_blank', 'noopener');
        });
    }

    // Filter tamu. Resepsi di atas 200 tamu: paket Hemat disembunyikan — masa
    // aktif H+7 dan tanpa galeri memang tidak cocok untuk skala itu, dan
    // menawarkannya hanya menghasilkan pembeli kecewa. Ini nudge, bukan
    // penegakan: klik kedua pada chip yang sama mengembalikan semua paket.
    var tombol = document.querySelectorAll('.tamu-btn');
    var kartu = document.querySelectorAll('.paket-card');
    var catatan = document.getElementById('tamu-catatan');
    var SEMBUNYI = { kecil: [], sedang: [], besar: ['hemat'] };
    var PESAN = {
        kecil:  'Ketiga paket cocok untuk skala ini.',
        sedang: 'Galeri foto & amplop digital biasanya mulai terasa perlu di skala ini.',
        besar:  'Paket Hemat kami sembunyikan — masa aktif H+7 dan tanpa galeri tidak cocok untuk resepsi sebesar ini. Klik lagi untuk menampilkan semua paket.'
    };
    var pilihan = null;

    function terapkan() {
        tombol.forEach(function (b) { b.classList.toggle('aktif', b.dataset.tamu === pilihan); });
        kartu.forEach(function (k) {
            k.hidden = pilihan ? SEMBUNYI[pilihan].indexOf(k.dataset.paket) !== -1 : false;
        });
        catatan.textContent = pilihan ? PESAN[pilihan] : '';
    }

    tombol.forEach(function (b) {
        b.addEventListener('click', function () {
            pilihan = pilihan === b.dataset.tamu ? null : b.dataset.tamu;
            terapkan();
        });
    });
})();
</script>
<?php wp_footer(); ?>
</body>
</html>
