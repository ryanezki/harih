<?php
/**
 * Template Name: Katalog hariH
 *
 * Halaman katalog/landing (TASKS T1.20) — dipasang sebagai front page oleh
 * scripts/buat-toko.sh. Dirender standalone (tanpa CSS Astra/WooCommerce,
 * lihat dequeue di functions.php) supaya ringan & konsisten dengan brand
 * tema-01. Versi minimum untuk review merchant Duitku; polish di T3.8.
 *
 * Tombol beli → /checkout/?add-to-cart=<id> — mu-plugin mengosongkan cart
 * sebelum add (T1.17), jadi 1 klik = 1 paket, langsung ke checkout.
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
            'Cover, countdown, detail akad & resepsi + Google Maps',
            'Musik latar instrumental',
            'RSVP + ucapan tamu',
            'Nama tamu otomatis di link',
            '3 tema dasar',
            'Revisi via CS: Rp 25rb per pengajuan',
            'Masa aktif sampai H+7',
        ],
    ],
    [
        'sku'    => 'HARIH-FAVORIT',
        'nama'   => 'Favorit',
        'harga'  => '179',
        'sub'    => 'Paling laris — lengkap untuk hari bahagiamu.',
        'badge'  => 'Terpopuler',
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
        'sub'    => 'Terlengkap, termasuk video & prioritas.',
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
 * Demo per tema (P0.2) — daftarnya diturunkan dari `undangan_get_temas()`
 * supaya tema baru otomatis ikut tertaut di sini (slug demo: `demo-{tema}`,
 * dibuat oleh scripts/buat-demo.sh).
 */
$harih_temas = function_exists('undangan_get_temas')
    ? undangan_get_temas()
    : ['tema-01' => 'Tema 01 — Botanical Elegan'];

/** "Tema 01 — Botanical Elegan" → "Botanical Elegan" (label tombol demo). */
function harih_nama_tema(string $label): string {
    $bagian = preg_split('/\s*—\s*/u', $label, 2);
    return trim($bagian[1] ?? $label);
}

$harih_ada_reseller = (bool) get_page_by_path('jadi-reseller');
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<?php /* Deskripsi SERP (P2.4) — ±155 karakter, memuat kata kunci + harga + kanal */ ?>
<meta name="description" content="Undangan pernikahan digital yang jadi otomatis dalam hitungan menit dan langsung terkirim ke WhatsApp. Satu tema selaras — RSVP, galeri foto, amplop digital.">
<?php /* Open Graph katalog (T1.14 + P0.3) — reseller membagikan link ini di WA */ ?>
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

<header class="hero">
    <p class="brand">hariH</p>
    <h1>Undangan pernikahan digital,<br>jadi dalam hitungan menit.</h1>
    <p class="hero-sub">Pilih paket, bayar, isi data — undangan cantikmu langsung terkirim ke WhatsApp &amp; email. <strong>Tanpa antre desainer, tanpa menunggu berhari-hari.</strong></p>
    <div class="hero-cta">
        <a class="btn btn-utama" href="#paket">Lihat Paket</a>
        <a class="btn btn-garis" href="#demo">Lihat Contoh Undangan</a>
    </div>
</header>

<main>
    <section class="cara">
        <h2>Cara pesan</h2>
        <ol class="cara-grid">
            <li><span class="cara-no">1</span><strong>Pilih paket &amp; bayar</strong><span>QRIS, transfer bank, atau e-wallet — aman via payment gateway resmi.</span></li>
            <li><span class="cara-no">2</span><strong>Isi data undangan</strong><span>Link form dikirim ke WhatsApp &amp; email. Isinya ±10 menit dari HP.</span></li>
            <li><span class="cara-no">3</span><strong>Undangan jadi otomatis</strong><span>±5 menit setelah data dikirim, link undangan + QR code sampai ke kamu.</span></li>
        </ol>
    </section>

    <section class="paket" id="paket">
        <h2>Pilih paketmu</h2>

        <?php /*
          Satu pertanyaan sebelum tabel harga. Tujuannya bukan segmentasi
          canggih — tujuannya berhenti menawarkan paket Hemat kepada resepsi
          gedung 300 tamu. Itu salah-jual yang merugikan dua pihak: masa aktif
          H+7 dan tanpa galeri jelas tidak cocok, dan pembelinya kecewa.
          Penyaringan dilakukan di klien; SEMUA kartu tetap ada di DOM supaya
          mesin pencari tetap membaca seluruh tangga harga.
        */ ?>
        <div class="tamu-tanya" id="tamu-tanya">
            <p class="tamu-label" id="tamu-label">Perkiraan jumlah tamu?</p>
            <div class="tamu-opsi" role="group" aria-labelledby="tamu-label">
                <button type="button" class="tamu-btn" data-tamu="kecil">Di bawah 100</button>
                <button type="button" class="tamu-btn" data-tamu="sedang">100–200</button>
                <button type="button" class="tamu-btn" data-tamu="besar">Di atas 200</button>
            </div>
            <p class="tamu-catatan" id="tamu-catatan" hidden></p>
        </div>

        <div class="paket-grid">
            <?php foreach ($harih_paket as $p) : $beli = harih_url_beli($p['sku']); ?>
            <article class="paket-card<?php echo $p['badge'] ? ' unggulan' : ''; ?>" data-paket="<?php echo esc_attr(strtolower($p['nama'])); ?>">
                <?php if ($p['badge']) : ?><p class="paket-badge"><?php echo esc_html($p['badge']); ?></p><?php endif; ?>
                <h3><?php echo esc_html($p['nama']); ?></h3>
                <p class="paket-harga"><span class="rp">Rp</span><?php echo esc_html($p['harga']); ?><span class="rb">rb</span></p>
                <p class="paket-sub"><?php echo esc_html($p['sub']); ?></p>
                <ul class="paket-fitur">
                    <?php foreach ($p['fitur'] as $f) : ?><li><?php echo esc_html($f); ?></li><?php endforeach; ?>
                </ul>
                <?php if ($beli) : ?>
                    <a class="btn btn-utama btn-blok" href="<?php echo esc_url($beli); ?>">Pesan Paket <?php echo esc_html($p['nama']); ?></a>
                <?php else : ?>
                    <span class="btn btn-blok btn-nonaktif" aria-disabled="true">Segera tersedia</span>
                <?php endif; ?>
            </article>
            <?php endforeach; ?>
        </div>
        <p class="paket-catatan">Harga sekali bayar — tanpa biaya tersembunyi. Undangan tetap jadi <strong>instan &amp; otomatis</strong> di semua paket.</p>
    </section>

    <section class="demo" id="demo">
        <h2>Lihat dulu hasilnya</h2>
        <p>Buka contoh undangan dari HP-mu — persis seperti yang akan diterima tamu. Isi ketiganya sengaja dibuat sama, supaya kamu bisa membandingkan tampilan tiap tema.</p>
        <div class="demo-tema">
            <?php foreach ($harih_temas as $harih_tid => $harih_label) : ?>
                <a class="btn btn-garis" href="<?php echo esc_url(home_url('/u/demo-' . $harih_tid . '/')); ?>" target="_blank" rel="noopener"><?php echo esc_html(harih_nama_tema($harih_label)); ?> ↗</a>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="faq">
        <h2>Pertanyaan umum</h2>
        <details><summary>Berapa lama undangan saya jadi?</summary><p>Setelah kamu mengisi form data (±10 menit), undangan dibuat otomatis dan link-nya dikirim ke WhatsApp &amp; email dalam ±5 menit.</p></details>
        <details><summary>Bagaimana cara membagikan ke tamu?</summary><p>Cukup bagikan satu link via WhatsApp. Nama tamu bisa muncul otomatis di halaman pembuka dengan menambah <code>?to=Nama%20Tamu</code> di belakang link — panduannya dikirim bersama undangan.</p></details>
        <details><summary>Bisa revisi setelah jadi?</summary><p>Bisa, lewat CS. Paket Favorit dapat 1× revisi gratis, Premium 3× gratis dan didahulukan. Paket Hemat dan revisi di luar jatah dikenakan Rp 25 ribu per pengajuan. Kalau kesalahannya dari sistem kami, koreksinya selalu gratis. Revisi dikerjakan maksimal 2×24 jam — ajukan paling lambat H-3 sebelum acara.</p></details>
        <details><summary>Pembayarannya bagaimana?</summary><p>QRIS, virtual account bank, dan e-wallet — diproses payment gateway berlisensi resmi. Kamu menerima bukti pembayaran otomatis via email.</p></details>
    </section>
</main>

<footer class="kaki">
    <p class="brand">hariH</p>
    <p class="kaki-tag">Undangan digital untuk hari bahagiamu.</p>
    <nav class="kaki-nav">
        <a href="<?php echo esc_url(home_url('/kontak/')); ?>">Kontak</a>
        <a href="<?php echo esc_url(home_url('/syarat-ketentuan/')); ?>">Syarat &amp; Ketentuan</a>
        <a href="<?php echo esc_url(home_url('/kebijakan-privasi/')); ?>">Kebijakan Privasi</a>
        <a href="<?php echo esc_url(home_url('/kebijakan-refund/')); ?>">Kebijakan Refund</a>
        <?php if ($harih_ada_reseller) : ?><a href="<?php echo esc_url(home_url('/jadi-reseller/')); ?>">Jadi Reseller</a><?php endif; ?>
    </nav>
    <p class="kaki-cc">© <?php echo esc_html(wp_date('Y')); ?> hariH · harih.id</p>
</footer>

<script>
(function () {
    'use strict';
    // Resepsi di atas 200 tamu: paket Hemat disembunyikan. Masa aktif H+7 dan
    // tanpa galeri memang tidak cocok untuk skala itu — menawarkannya hanya
    // menghasilkan pembeli kecewa. Ini nudge, bukan penegakan: pengunjung tetap
    // bisa memilih ulang atau memuat ulang halaman.
    var tombol = document.querySelectorAll('.tamu-btn');
    var kartu = document.querySelectorAll('.paket-card');
    var catatan = document.getElementById('tamu-catatan');
    var SEMBUNYI = { kecil: [], sedang: [], besar: ['hemat'] };
    var PESAN = {
        kecil:  'Ketiga paket cocok untuk skala ini.',
        sedang: 'Galeri foto & amplop digital biasanya mulai terasa perlu di skala ini.',
        besar:  'Paket Hemat kami sembunyikan — masa aktif H+7 dan tanpa galeri tidak cocok untuk resepsi sebesar ini.'
    };

    tombol.forEach(function (b) {
        b.addEventListener('click', function () {
            var pilihan = b.dataset.tamu;
            tombol.forEach(function (x) { x.classList.toggle('aktif', x === b); });
            kartu.forEach(function (k) {
                k.hidden = SEMBUNYI[pilihan].indexOf(k.dataset.paket) !== -1;
            });
            catatan.textContent = PESAN[pilihan];
            catatan.hidden = false;
        });
    });
})();
</script>
<?php wp_footer(); ?>
</body>
</html>
