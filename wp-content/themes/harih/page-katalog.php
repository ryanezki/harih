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
            'Revisi data 1× via CS',
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
            'Revisi data 3× + prioritas CS',
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

$harih_demo = home_url('/u/demo-tema-01/');
$harih_ada_reseller = (bool) get_page_by_path('jadi-reseller');
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<?php /* Open Graph katalog (T1.14) — og:image default menyusul bersama aset tema (T1.13) */ ?>
<meta property="og:type" content="website">
<meta property="og:site_name" content="hariH">
<meta property="og:locale" content="id_ID">
<meta property="og:title" content="hariH — Undangan Pernikahan Digital">
<meta property="og:description" content="Undangan digital mulai Rp 99 ribu — jadi otomatis dalam hitungan menit, langsung terkirim ke WhatsApp. RSVP, galeri foto, amplop digital.">
<meta property="og:url" content="<?php echo esc_url(home_url('/')); ?>">
<?php wp_head(); ?>
</head>
<body class="katalog-body">

<header class="hero">
    <p class="brand">hariH</p>
    <h1>Undangan pernikahan digital,<br>jadi dalam hitungan menit.</h1>
    <p class="hero-sub">Pilih paket, bayar, isi data — undangan cantikmu langsung terkirim ke WhatsApp &amp; email. Tanpa antre desainer, mulai <strong>Rp 99 ribu</strong>.</p>
    <div class="hero-cta">
        <a class="btn btn-utama" href="#paket">Lihat Paket</a>
        <a class="btn btn-garis" href="<?php echo esc_url($harih_demo); ?>" target="_blank" rel="noopener">Lihat Contoh Undangan ↗</a>
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
        <div class="paket-grid">
            <?php foreach ($harih_paket as $p) : $beli = harih_url_beli($p['sku']); ?>
            <article class="paket-card<?php echo $p['badge'] ? ' unggulan' : ''; ?>">
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

    <section class="demo">
        <h2>Lihat dulu hasilnya</h2>
        <p>Buka contoh undangan dari HP-mu — persis seperti yang akan diterima tamu.</p>
        <a class="btn btn-garis" href="<?php echo esc_url($harih_demo); ?>" target="_blank" rel="noopener">Demo Tema Botanical Elegan ↗</a>
    </section>

    <section class="faq">
        <h2>Pertanyaan umum</h2>
        <details><summary>Berapa lama undangan saya jadi?</summary><p>Setelah kamu mengisi form data (±10 menit), undangan dibuat otomatis dan link-nya dikirim ke WhatsApp &amp; email dalam ±5 menit.</p></details>
        <details><summary>Bagaimana cara membagikan ke tamu?</summary><p>Cukup bagikan satu link via WhatsApp. Nama tamu bisa muncul otomatis di halaman pembuka dengan menambah <code>?to=Nama%20Tamu</code> di belakang link — panduannya dikirim bersama undangan, plus QR code untuk kartu fisik.</p></details>
        <details><summary>Bisa revisi setelah jadi?</summary><p>Bisa, sesuai paket: Favorit 1× dan Premium 3× dengan prioritas, melalui CS. Paket Hemat dapat menghubungi CS untuk revisi berbayar.</p></details>
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

<?php wp_footer(); ?>
</body>
</html>
