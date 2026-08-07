<?php
/**
 * Template Name: Harga Hybrid hariH
 *
 * Halaman harga digital + cetak (TASKS F1.5) — STATIS, tanpa produk
 * WooCommerce: semua CTA cetak mengarah ke WhatsApp. Lima order pertama
 * ditutup manual (invoice WA + transfer), sesuai urutan uang F1.
 *
 * DITERBITKAN 2026-08-05 atas keputusan owner (alat cetak dipastikan tersedia),
 * mendahului gerbang F1.1/F1.2 di rencana. Konsekuensinya dipindahkan ke alur
 * pemesanan, bukan diabaikan: halaman ini TIDAK punya tombol bayar. Semua CTA
 * mengarah ke WhatsApp, dan slot produksi + tanggal dikonfirmasi manual
 * sebelum ada uang berpindah — itu yang menjaga ketiga garansi (yang sudah
 * jadi klausul S&K §12) tetap bisa ditepati sementara F1.1 (uji pindai QR) &
 * F1.2 (percetakan terpilih) diselesaikan.
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
        'sub'   => 'Untuk orang tua, sesepuh, dan atasan — yang pantas menerima undangan di tangan, bukan sekadar link.',
        'fitur' => [
            'Undangan digital lengkap (setara paket Premium)',
            '50 undangan cetak lipat dua — detail acara terbaca tanpa HP',
            '50 amplop bernama tamu — dicetak langsung pada amplopnya, bukan stiker yang ditempel',
            'QR menuju undangan digital, dicetak besar di halaman dalam',
            'Pratinjau (proof) disetujui dulu, baru dicetak',
            'Gratis ongkir ke seluruh Indonesia',
        ],
        'spek'  => 'A4 dilipat jadi A5 · desain seragam dengan undangan digital',
        'badge' => '',
    ],
    [
        'nama'  => 'Resepsi',
        'harga' => '2.900.000',
        'sub'   => 'Resepsi gedung: undangan di layar semua tamu, undangan cetak untuk yang dihormati, souvenir bernama Anda.',
        'fitur' => [
            'Undangan digital custom',
            '100 undangan cetak lipat dua + 100 amplop bernama tamu',
            '200 label souvenir dengan nama & tanggal Anda',
            '100 kartu terima kasih',
            '100 stiker segel amplop',
            'Pratinjau (proof) disetujui dulu, baru dicetak',
            'Gratis ongkir ke seluruh Indonesia',
        ],
        'spek'  => 'A4 dilipat jadi A5 · desain seragam dengan undangan digital',
        'badge' => 'Paling Populer',
        'hemat' => 'Dibeli satuan: Rp 3.600.000 — Anda hemat Rp 700.000',
    ],
    [
        'nama'  => 'Grand',
        'harga' => '5.900.000',
        'sub'   => 'Resepsi besar dengan panitia: dari undangan cetak premium sampai ID panitia, semua satu desain.',
        'fitur' => [
            'Undangan digital custom',
            '150 undangan cetak lipat premium + 150 amplop bernama tamu',
            '300 label souvenir + 300 kupon souvenir',
            '150 hangtag + tali',
            'Set label seserahan',
            '15 ID card panitia',
            'Pratinjau (proof) disetujui dulu, baru dicetak',
            'Gratis ongkir ke seluruh Indonesia',
        ],
        'spek'  => 'Kertas lebih tebal · finishing khusus · amplop premium',
        'badge' => '',
    ],
];

$harih_satuan = [
    ['Undangan cetak lipat + amplop bernama', 'Rp 15.000/pcs', 'min. 50'],
    ['Kartu undangan ber-QR (bagi luas)',     'Rp 9.500/pcs',  'min. 100'],
    ['Kartu ber-QR holographic',              'Rp 14.000/pcs', 'min. 100'],
    ['Label souvenir',                        'Rp 2.000/pcs',  'min. 200'],
    ['Hangtag + tali',                        'Rp 3.500/pcs',  'min. 100'],
    ['Kartu terima kasih',                    'Rp 3.500/pcs',  'min. 100'],
    ['Stiker segel undangan',                 'Rp 1.500/pcs',  'min. 100'],
    ['Set label seserahan (12 pcs)',          'Rp 249.000',    'per set'],
    ['ID card PVC panitia',                   'Rp 25.000/pcs', 'min. 10'],
];

?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="description" content="Undangan digital + undangan cetak lipat dengan amplop bernama tamu, satu desain. Untuk yang paling dihormati. Garansi tepat waktu — tiba H-14 atau uang kembali.">
<meta property="og:type" content="website">
<meta property="og:site_name" content="hariH">
<meta property="og:locale" content="id_ID">
<meta property="og:title" content="hariH — Undangan Digital + Undangan Cetak Bernama">
<meta property="og:description" content="Satu desain, dua wujud: undangan digital untuk semua tamu, undangan cetak beramplop nama untuk yang paling Anda hormati. Garansi tiba H-14 atau uang kembali 100%.">
<meta property="og:url" content="<?php echo esc_url(get_permalink()); ?>">
<meta property="og:image" content="<?php echo esc_url(harih_og_default()); ?>">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta name="twitter:card" content="summary_large_image">
<?php wp_head(); ?>
</head>
<body class="katalog-body harga-hybrid-body">

<?php get_template_part('template-parts/toko/nav', null, ['beranda' => false]); ?>

<header class="hero">
    <h1>Nama tiap tamu tercetak di amplopnya &mdash;<br>tiba H-14, atau uang kembali.</h1>
    <p class="hero-sub">Undangan digital untuk semua tamu — dan <strong>undangan cetak lipat beserta amplop bernama</strong> untuk orang tua, sesepuh, dan atasan. Satu kali isi data, keduanya jadi, dengan desain yang sama.</p>
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
                <p>QR di undangan cetak diuji pindai sebelum dikirim. Kalau ada yang gagal terbaca, <strong>seluruh batch kami ganti.</strong> Detail acara tetap tercetak lengkap — tamu tanpa HP pun bisa membacanya.</p>
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
                <p class="paket-harga-full">mulai <strong>Rp <?php echo esc_html(function_exists('harih_harga_mulai') ? harih_harga_mulai() : 99); ?> rb</strong></p>
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
        <p class="paket-catatan">Harga sudah termasuk desain dari data undangan Anda, proof sebelum cetak, dan <strong>gratis ongkir ke seluruh Indonesia</strong>.</p>
        <?php
        /* Sisa slot ditampilkan APA ADANYA (F4.6). Angka 8/bulan berasal dari
           kapasitas nyata: ±4,5 jam mesin per order × 8 = ±36 jam/bulan.
           Menampilkan sisa yang jujur lebih kuat daripada "kuota terbatas"
           yang tidak bisa diperiksa siapa pun — dan mencegah kami menerima
           pesanan yang tidak bisa dipenuhi tepat waktu. */
        $harih_sisa = function_exists('undangan_sisa_slot') ? undangan_sisa_slot() : null;
        ?>
        <?php
        $harih_kuota  = defined('UNDANGAN_KUOTA_BULAN') ? UNDANGAN_KUOTA_BULAN : 8;
        $harih_ambang = defined('UNDANGAN_SLOT_TAMPIL') ? UNDANGAN_SLOT_TAMPIL : 4;
        // Tampilkan HANYA saat benar-benar menipis, atau saat sudah penuh.
        // Sisa penuh (8 dari 8) diam — lihat alasannya di UNDANGAN_SLOT_TAMPIL.
        $harih_tampil = $harih_sisa !== null && ($harih_sisa === 0 || $harih_sisa <= $harih_ambang);
        ?>
        <?php if ($harih_tampil) : ?>
        <p class="paket-slot-sisa">
            <?php if ($harih_sisa > 0) : ?>
                <strong>Tinggal <?php echo esc_html($harih_sisa); ?> dari <?php echo esc_html($harih_kuota); ?> slot produksi <?php echo esc_html(wp_date('F')); ?></strong> yang tersisa.
            <?php else : ?>
                <strong>Slot produksi <?php echo esc_html(wp_date('F')); ?> sudah penuh.</strong> Hubungi kami untuk jadwal bulan berikutnya.
            <?php endif; ?>
        </p>
        <?php endif; ?>
        <p class="paket-catatan paket-slot"><strong>Pemesanan cetak dikonfirmasi lebih dulu lewat WhatsApp.</strong> Kapasitas produksi per bulan terbatas dan pesanan diterima paling lambat H-21 sebelum acara — kami pastikan slot &amp; tanggal Anda aman sebelum ada pembayaran. Itulah yang membuat Garansi Tepat Waktu bisa kami tanda tangani.</p>
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
        <p class="satuan-cta">
            <a class="btn btn-garis" href="<?php echo esc_url(home_url('/satuan/')); ?>">Buka Katalog Satuan</a>
            <span class="satuan-cta-min">minimum Rp 1.000.000 per transaksi</span>
        </p>
    </section>

    <section class="faq">
        <h2>Pertanyaan umum</h2>
        <details><summary>Kenapa harus pesan paling lambat H-21?</summary><p>Karena kartu Anda kami jamin tiba H-14 — buffer 7 hari itulah yang membuat garansinya bisa kami tepati, termasuk waktu proof, produksi, dan pengiriman ke luar pulau.</p></details>
        <details><summary>Kenapa undangan cetak lipat, bukan kartu QR kecil?</summary><p>Karena yang menerima undangan cetak biasanya orang tua dan sesepuh — kartu yang isinya hanya kode QR menuntut mereka membuka HP dulu. Undangan lipat memuat nama, tanggal, jam, dan alamat lengkap yang bisa langsung dibaca, dengan QR besar di halaman dalam bagi yang ingin membuka versi digitalnya. Kartu QR kecil tetap tersedia sebagai item satuan untuk yang ingin membagikannya secara luas.</p></details>
        <details><summary>Bagaimana kalau ada salah ketik?</summary><p>Sebelum produksi Anda menerima proof untuk disetujui. Salah cetak dari pihak kami (hasil beda dari proof): cetak ulang gratis dan dikirim ekspres. Kesalahan yang sudah tampak di proof yang disetujui menjadi tanggung jawab pemesan — karena itu periksa proof baik-baik, kami bantu checklist-nya.</p></details>
        <details><summary>Ongkirnya benar gratis ke mana saja?</summary><p>Ya, ke seluruh Indonesia, satu alamat per pesanan, dikirim dengan kurir ber-SLA dan nomor resi yang kami kirimkan ke Anda.</p></details>
        <details><summary>Barang tiba rusak, bagaimana?</summary><p>Laporkan maksimal 2×24 jam setelah paket diterima dengan foto/video — barang rusak dalam pengiriman diganti baru tanpa biaya. Lengkapnya di <a href="<?php echo esc_url(home_url('/kebijakan-refund/')); ?>">Kebijakan Refund</a>.</p></details>
        <details><summary>Saya sudah beli undangan digital hariH — bisa tambah cetak?</summary><p>Bisa. Chat CS dengan nomor pesanan Anda; data undangan yang sudah ada langsung dipakai untuk desain kartunya.</p></details>
    </section>
</main>

<?php get_template_part('template-parts/toko/kaki', null, ['tagline' => 'Satu desain, dua wujud — digital & cetak.']); ?>

<?php wp_footer(); ?>
</body>
</html>
