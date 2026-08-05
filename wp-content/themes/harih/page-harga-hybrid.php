<?php
/**
 * Template Name: Harga Hybrid hariH
 *
 * Halaman harga digital + cetak (TASKS F1.5) — STATIS, tanpa produk
 * WooCommerce: semua CTA cetak mengarah ke WhatsApp. Lima order pertama
 * ditutup manual (invoice WA + transfer), sesuai urutan uang F1.
 *
 * ⚠️ GERBANG PUBLIKASI (jangan dilewati): file ini boleh ada di server,
 * tapi HALAMAN WordPress-nya baru dibuat setelah:
 *   1. F1.1 lolos — QR tercetak+laminasi doff terbukti terpindai di ruangan
 *      remang (dasar Garansi QR Terbaca), dan
 *   2. F1.2 selesai — ada percetakan subkontrak terpilih dengan harga tertulis.
 * Menerbitkan janji kartu fisik sebelum barangnya bisa dibuat mengulang
 * kesalahan musik (tercantum berbulan-bulan sebelum ada). Cara publish:
 *   wp post create --post_type=page --post_title='Harga Cetak & Digital' \
 *     --post_name=harga --post_status=publish \
 *     --meta_input='{"_wp_page_template":"page-harga-hybrid.php"}'
 *
 * Copy mengikuti Rencana Bisnis §5.9: jual HASIL, bukan gramatur — spesifikasi
 * teknis tampil kecil sebagai bukti. Tiga garansi tampil DI HALAMAN INI
 * (§11.2: garansi tersembunyi di FAQ = penyebab #1 closing rate rendah).
 * Grand = jangkar harga; penghematan paket vs satuan ditulis eksplisit.
 */

if (!defined('ABSPATH')) exit;

// CTA = chat CS dengan teks terisi (nomor sama dengan halaman Kontak).
$harih_cta = static fn(string $paket): string =>
    'https://wa.me/6282251975575?text=' . rawurlencode("Halo hariH, saya tertarik {$paket}. Boleh info ketersediaan slot bulan ini?");

$harih_paket_cetak = [
    [
        'nama'  => 'Hormat',
        'harga' => '1.190.000',
        'sub'   => 'Untuk orang tua, sesepuh, dan atasan — yang pantas menerima kartu di tangan, bukan sekadar link.',
        'fitur' => [
            'Undangan digital lengkap (setara paket Premium)',
            '150 kartu undangan fisik ber-QR — tamu memindai, undangan digital terbuka',
            '100 stiker segel amplop',
            'Pratinjau (proof) disetujui dulu, baru dicetak',
            'Gratis ongkir ke seluruh Indonesia',
        ],
        'spek'  => 'Art carton 260gsm · laminasi doff · uji pindai per batch',
        'badge' => '',
    ],
    [
        'nama'  => 'Resepsi',
        'harga' => '2.900.000',
        'sub'   => 'Resepsi gedung: undangan di layar tamu, kartu di meja penerima tamu, souvenir bernama Anda.',
        'fitur' => [
            'Undangan digital custom',
            '150 kartu undangan fisik ber-QR',
            '200 label souvenir dengan nama & tanggal Anda',
            '100 kartu terima kasih',
            '100 stiker segel amplop',
            'Pratinjau (proof) disetujui dulu, baru dicetak',
            'Gratis ongkir ke seluruh Indonesia',
        ],
        'spek'  => 'Art carton 260gsm · laminasi doff · uji pindai per batch',
        'badge' => 'Paling Populer',
        'hemat' => 'Dibeli satuan: Rp 3.525.000 — Anda hemat Rp 625.000',
    ],
    [
        'nama'  => 'Grand',
        'harga' => '5.900.000',
        'sub'   => 'Resepsi besar dengan panitia: dari kartu akses tamu sampai ID panitia, semua satu desain.',
        'fitur' => [
            'Undangan digital custom',
            '200 kartu undangan fisik ber-QR holographic',
            '300 label souvenir + 300 kupon souvenir',
            '150 hangtag + tali',
            'Set label seserahan',
            '15 ID card panitia',
            'Pratinjau (proof) disetujui dulu, baru dicetak',
            'Gratis ongkir ke seluruh Indonesia',
        ],
        'spek'  => 'Holographic foil · art carton 260gsm · uji pindai per batch',
        'badge' => '',
    ],
];

$harih_satuan = [
    ['Kartu undangan fisik ber-QR',        'Rp 9.500/pcs',  'min. 100'],
    ['Kartu ber-QR holographic',           'Rp 14.000/pcs', 'min. 100'],
    ['Label souvenir',                     'Rp 2.000/pcs',  'min. 200'],
    ['Hangtag + tali',                     'Rp 3.500/pcs',  'min. 100'],
    ['Kartu terima kasih',                 'Rp 3.500/pcs',  'min. 100'],
    ['Stiker segel undangan',              'Rp 1.500/pcs',  'min. 100'],
    ['Set label seserahan (12 pcs)',       'Rp 249.000',    'per set'],
    ['ID card PVC panitia',                'Rp 25.000/pcs', 'min. 10'],
];
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="description" content="Undangan digital + kartu undangan fisik ber-QR dalam satu desain. Kartu untuk yang paling dihormati, link untuk semua tamu. Garansi tepat waktu — tiba H-14 atau uang kembali.">
<meta property="og:type" content="website">
<meta property="og:site_name" content="hariH">
<meta property="og:locale" content="id_ID">
<meta property="og:title" content="hariH — Undangan Digital + Kartu Fisik Ber-QR">
<meta property="og:description" content="Satu desain, dua wujud: undangan digital untuk semua tamu, kartu fisik untuk yang paling Anda hormati. Garansi tiba H-14 atau uang kembali 100%.">
<meta property="og:url" content="<?php echo esc_url(get_permalink()); ?>">
<meta property="og:image" content="<?php echo esc_url(harih_og_default()); ?>">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta name="twitter:card" content="summary_large_image">
<?php wp_head(); ?>
</head>
<body class="katalog-body harga-hybrid-body">

<header class="hero">
    <p class="brand">hariH</p>
    <h1>Satu desain, dua wujud:<br>di layar semua tamu, di tangan yang terhormat.</h1>
    <p class="hero-sub">Undangan digital untuk 300 tamu Anda — dan <strong>kartu fisik ber-QR</strong> untuk orang tua, sesepuh, dan atasan yang pantas menerima lebih dari sekadar link. Satu kali isi data, keduanya jadi.</p>
    <div class="hero-cta">
        <a class="btn btn-utama" href="#paket">Lihat Paket</a>
        <a class="btn btn-garis" href="<?php echo esc_url($harih_cta('paket cetak + digital hariH')); ?>" target="_blank" rel="noopener">Tanya via WhatsApp</a>
    </div>
</header>

<main>
    <section class="garansi">
        <h2>Tiga garansi, tertulis di <a href="<?php echo esc_url(home_url('/syarat-ketentuan/')); ?>">Syarat &amp; Ketentuan</a> — bukan sekadar slogan</h2>
        <div class="garansi-grid">
            <article class="garansi-card">
                <h3>Garansi QR Terbaca</h3>
                <p>Setiap batch diuji pindai sebelum dikirim. Kalau ada QR yang gagal terbaca, <strong>seluruh batch kami ganti.</strong></p>
            </article>
            <article class="garansi-card">
                <h3>Garansi Tepat Waktu</h3>
                <p>Kartu Anda tiba paling lambat <strong>H-14 sebelum acara</strong>. Kalau lewat, <strong>uang kembali 100%</strong> — dan kartunya tetap kami kirim.</p>
            </article>
            <article class="garansi-card">
                <h3>Garansi Cetak Benar</h3>
                <p>Salah cetak dari pihak kami: <strong>cetak ulang gratis, kirim ekspres,</strong> tanpa pertanyaan.</p>
            </article>
        </div>
    </section>

    <section class="paket" id="paket">
        <h2>Pilih paketmu</h2>
        <div class="paket-grid paket-grid-hybrid">

            <article class="paket-card paket-digital">
                <h3>Digital</h3>
                <p class="paket-harga-full">mulai <strong>Rp 99 rb</strong></p>
                <p class="paket-sub">Undangan digital saja — jadi otomatis dalam hitungan menit.</p>
                <ul class="paket-fitur">
                    <li>3 pilihan paket: Hemat, Favorit, Premium</li>
                    <li>RSVP, galeri, amplop digital, musik</li>
                    <li>Langsung terkirim ke WhatsApp</li>
                </ul>
                <a class="btn btn-garis btn-blok" href="<?php echo esc_url(home_url('/')); ?>">Lihat Paket Digital</a>
            </article>

            <?php foreach ($harih_paket_cetak as $p) : ?>
            <article class="paket-card<?php echo $p['badge'] ? ' unggulan' : ''; ?>">
                <?php if ($p['badge']) : ?><p class="paket-badge"><?php echo esc_html($p['badge']); ?></p><?php endif; ?>
                <h3><?php echo esc_html($p['nama']); ?></h3>
                <p class="paket-harga-full">Rp <strong><?php echo esc_html($p['harga']); ?></strong></p>
                <p class="paket-sub"><?php echo esc_html($p['sub']); ?></p>
                <?php if (!empty($p['hemat'])) : ?><p class="paket-hemat"><?php echo esc_html($p['hemat']); ?></p><?php endif; ?>
                <ul class="paket-fitur">
                    <?php foreach ($p['fitur'] as $f) : ?><li><?php echo esc_html($f); ?></li><?php endforeach; ?>
                </ul>
                <p class="paket-spek"><?php echo esc_html($p['spek']); ?></p>
                <a class="btn btn-utama btn-blok" href="<?php echo esc_url($harih_cta('Paket ' . $p['nama'] . ' (Rp ' . $p['harga'] . ')')); ?>" target="_blank" rel="noopener">Pesan via WhatsApp</a>
            </article>
            <?php endforeach; ?>

        </div>
        <p class="paket-catatan">Harga sudah termasuk desain dari data undangan Anda, proof sebelum cetak, dan <strong>gratis ongkir ke seluruh Indonesia</strong>. Kapasitas produksi terbatas per bulan — tanyakan slot bulan Anda.</p>
    </section>

    <section class="proses">
        <h2>Prosesnya sederhana</h2>
        <ol class="cara-grid">
            <li><span class="cara-no">1</span><strong>Chat WhatsApp</strong><span>Sebut tanggal acara &amp; paket. Kami konfirmasi slot produksi bulan itu.</span></li>
            <li><span class="cara-no">2</span><strong>Isi data sekali</strong><span>Data yang sama menjadi undangan digital dan desain kartu — tidak ada pengisian dobel.</span></li>
            <li><span class="cara-no">3</span><strong>Setujui proof</strong><span>Kami kirim pratinjau. Produksi jalan hanya setelah Anda setuju.</span></li>
            <li><span class="cara-no">4</span><strong>Terima paling lambat H-14</strong><span>Dikirim dengan resi, gratis ongkir. Lewat H-14? Uang kembali 100%.</span></li>
        </ol>
        <p class="proses-catatan">Pesanan cetak diterima paling lambat <strong>H-21 sebelum acara</strong> — batas ini yang membuat Garansi Tepat Waktu bisa kami tanda tangani.</p>
    </section>

    <section class="satuan">
        <h2>Butuh satuan saja?</h2>
        <p>Semua item bisa dibeli terpisah — minimum order per item, dan <strong>minimum Rp 1.000.000 per transaksi</strong>. Paket selalu lebih hemat per unit; tabel ini pembandingnya.</p>
        <div class="satuan-tabel-wrap">
            <table class="satuan-tabel">
                <thead><tr><th>Item</th><th>Harga</th><th>Minimum</th></tr></thead>
                <tbody>
                <?php foreach ($harih_satuan as [$item, $harga, $min]) : ?>
                    <tr><td><?php echo esc_html($item); ?></td><td><?php echo esc_html($harga); ?></td><td><?php echo esc_html($min); ?></td></tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <p class="satuan-cta"><a class="btn btn-garis" href="<?php echo esc_url($harih_cta('pembelian item satuan')); ?>" target="_blank" rel="noopener">Tanya Item Satuan via WhatsApp</a></p>
    </section>

    <section class="faq">
        <h2>Pertanyaan umum</h2>
        <details><summary>Kenapa harus pesan paling lambat H-21?</summary><p>Karena kartu Anda kami jamin tiba H-14 — buffer 7 hari itulah yang membuat garansinya bisa kami tepati, termasuk waktu proof, produksi, dan pengiriman ke luar pulau.</p></details>
        <details><summary>Bagaimana kalau ada salah ketik?</summary><p>Sebelum produksi Anda menerima proof untuk disetujui. Salah cetak dari pihak kami (hasil beda dari proof): cetak ulang gratis dan dikirim ekspres. Kesalahan yang sudah tampak di proof yang disetujui menjadi tanggung jawab pemesan — karena itu periksa proof baik-baik, kami bantu checklist-nya.</p></details>
        <details><summary>Ongkirnya benar gratis ke mana saja?</summary><p>Ya, ke seluruh Indonesia, satu alamat per pesanan, dikirim dengan kurir ber-SLA dan nomor resi yang kami kirimkan ke Anda.</p></details>
        <details><summary>Barang tiba rusak, bagaimana?</summary><p>Laporkan maksimal 2×24 jam setelah paket diterima dengan foto/video — barang rusak dalam pengiriman diganti baru tanpa biaya. Lengkapnya di <a href="<?php echo esc_url(home_url('/kebijakan-refund/')); ?>">Kebijakan Refund</a>.</p></details>
        <details><summary>Saya sudah beli undangan digital hariH — bisa tambah cetak?</summary><p>Bisa. Chat CS dengan nomor pesanan Anda; data undangan yang sudah ada langsung dipakai untuk desain kartunya.</p></details>
    </section>
</main>

<footer class="kaki">
    <p class="brand">hariH</p>
    <p class="kaki-tag">Satu desain, dua wujud — digital &amp; cetak.</p>
    <nav class="kaki-nav">
        <a href="<?php echo esc_url(home_url('/kontak/')); ?>">Kontak</a>
        <a href="<?php echo esc_url(home_url('/syarat-ketentuan/')); ?>">Syarat &amp; Ketentuan</a>
        <a href="<?php echo esc_url(home_url('/kebijakan-privasi/')); ?>">Kebijakan Privasi</a>
        <a href="<?php echo esc_url(home_url('/kebijakan-refund/')); ?>">Kebijakan Refund</a>
    </nav>
    <p class="kaki-cc">© <?php echo esc_html(wp_date('Y')); ?> hariH · harih.id</p>
</footer>

<?php wp_footer(); ?>
</body>
</html>
