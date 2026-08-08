<?php
/**
 * Template Name: Upsell Pasca-Bayar hariH
 *
 * Penawaran naik ke paket cetak untuk pembeli yang BARU membayar undangan
 * digital (TASKS F3.10). Halaman bertoken seperti `/isi-data/` — bukan halaman
 * publik: isinya harga khusus yang tidak boleh bisa dibuka sembarang orang.
 *
 * Aturan yang dijalankan di sini (semuanya keputusan terkunci):
 *   · kredit berlaku 14 HARI sejak pembayaran, dengan hitung mundur TAMPIL —
 *     tanpa batas waktu yang terlihat, penawaran ini hanya jadi pengingat
 *     yang bisa ditunda selamanya;
 *   · à la carte DILARANG muncul di sini. Halaman ini menjual naik kelas,
 *     bukan tambal-sulam per item — pilihan satuan justru menggerus nilai
 *     paketnya;
 *   · hanya untuk order digital yang SUDAH dibayar dan BELUM memuat cetak.
 *
 * Sumber harga: produk `UPG-*` bila sudah ada (harga tetap, F1.3). Selama SKU
 * itu belum dibuat, halaman jatuh ke paket `CETAK-*` dengan kredit = nilai
 * yang sudah dibayar pembeli, dan penutupan transaksinya lewat WhatsApp —
 * jadi halaman ini sudah bisa dipakai sebelum harga upgrade diputuskan.
 */

if (!defined('ABSPATH')) exit;

// Halaman bertoken tidak boleh di-cache — sama seperti /isi-data/. URL-nya
// memuat token order, dan isinya khusus satu pemesan.
nocache_headers();
do_action('litespeed_control_set_nocache'); // no-op bila LSCWP tidak aktif

$order_id = absint($_GET['order'] ?? 0);
$key      = sanitize_text_field((string) ($_GET['key'] ?? ''));

// B9 — token DICAKUP per halaman (lihat undangan_token_halaman()).
$sah = undangan_token_sah($order_id, 'upsell', $key);
$order = $sah && function_exists('wc_get_order') ? wc_get_order($order_id) : null;
if (!$order || !$order->is_paid()) {
    wp_die(
        'Link penawaran tidak valid atau sudah tidak berlaku. Hubungi CS bila kamu merasa ini keliru.',
        'Link tidak valid',
        ['response' => 403]
    );
}

// Order yang SUDAH memuat cetak tidak ditawari lagi.
$sudah_cetak = false;
$dibayar_digital = 0.0;
foreach ($order->get_items() as $item) {
    $produk = $item->get_product();
    $jenis  = function_exists('undangan_jenis_produk') ? undangan_jenis_produk($produk) : '';
    if (in_array($jenis, ['hybrid', 'satuan'], true)) $sudah_cetak = true;
    if ($jenis === 'digital') $dibayar_digital += (float) $item->get_total();
}

// Tenggat kredit: 14 hari sejak pembayaran.
$tgl_bayar = $order->get_date_paid() ?: $order->get_date_created();
$tenggat   = $tgl_bayar ? (clone $tgl_bayar)->modify('+14 days') : null;
$kedaluwarsa = !$tenggat || $tenggat->getTimestamp() < time();

/**
 * Paket yang ditawarkan: UPG-* bila ada, jika belum → CETAK-* + kredit.
 *
 * ⚠️ HORMAT SENGAJA TIDAK ADA DI SINI (keputusan owner 2026-08-07).
 *
 * Bukan karena kreditnya kemahalan — kredit rata Rp 300.000 dipertahankan.
 * Alasannya marjin per jam: setup mesin sama saja untuk 50 maupun 150 unit,
 * jadi paket terkecil menanggung setup yang sama dengan pendapatan paling
 * kecil. Diturunkan dari struktur biaya yang tercatat (ongkir 150rb, bahan
 * ±Rp 1.500/unit) dan estimasi waktu 4,5 jam untuk 100 unit:
 *
 *   Hormat  lewat upgrade → ±Rp 222rb/jam  (percetakan reguler: 100–200rb/jam)
 *   Resepsi lewat upgrade → ±Rp 511rb/jam
 *   Grand   lewat upgrade → ±Rp 871rb/jam
 *
 * Hormat-lewat-upgrade cuma ±1,1× pekerjaan reguler — padahal ia datang
 * membawa tenggat pernikahan, Garansi Tepat Waktu, dan risiko refund
 * Rp 890.000. Setelah disesuaikan risiko, order itu lebih buruk daripada tidak
 * mengambilnya sama sekali.
 *
 * Efek sampingnya menguntungkan: Rp 890rb di sebelah Rp 2,6 juta menjadikan
 * Hormat pilihan aman yang otomatis dipilih — jangkar yang bekerja MELAWAN
 * kita. Halaman ini kini punya dua pilihan, bukan tiga.
 *
 * `CETAK-HORMAT` TETAP dijual penuh di katalog & halaman /harga/ — yang dicabut
 * hanya jalur upgrade-nya. ⚠️ Marjin Hormat pada harga penuh (±1,6× reguler)
 * juga tipis dan perlu ditinjau terpisah — lihat TASKS F1.7a.
 */
$harih_tawaran = [];
foreach ([['RESEPSI', 'Resepsi'], ['GRAND', 'Grand']] as [$kode, $label]) {
    $upg   = function_exists('wc_get_product_id_by_sku') ? wc_get_product_id_by_sku('UPG-' . $kode) : 0;
    $penuh = function_exists('wc_get_product_id_by_sku') ? wc_get_product_id_by_sku('CETAK-' . $kode) : 0;
    if (!$penuh) continue;

    $p_penuh = wc_get_product($penuh);
    $p_upg   = $upg ? wc_get_product($upg) : null;

    $harih_tawaran[] = [
        'label'       => $label,
        'harga_penuh' => (float) $p_penuh->get_price(),
        'harga_bayar' => $p_upg ? (float) $p_upg->get_price() : max(0, (float) $p_penuh->get_price() - $dibayar_digital),
        'kredit'      => $p_upg ? max(0, (float) $p_penuh->get_price() - (float) $p_upg->get_price()) : $dibayar_digital,
        'ringkas'     => wp_strip_all_tags($p_penuh->get_short_description()),
        // D1 — bawa ID order ASAL. Tanpa ini, pembelian upgrade melahirkan order
        // baru yang tidak punya hubungan apa pun dengan undangan yang sudah
        // terbit, dan seluruh produksi (snapshot, proof, daftar tamu, antrean)
        // mencarinya lewat order lama lalu tidak menemukan apa-apa.
        'beli'        => $p_upg ? add_query_arg(
            ['add-to-cart' => $upg, 'upgrade_dari' => $order_id],
            wc_get_checkout_url()
        ) : '',
    ];
}

$wa_cs = static fn(string $paket, int $oid): string =>
    'https://wa.me/6282251975575?text=' . rawurlencode("Halo hariH, saya mau naik ke Paket {$paket} untuk pesanan #{$oid}.");
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="robots" content="noindex, nofollow">
<meta name="referrer" content="no-referrer"><?php /* token pesanan ada di URL — jangan bocor lewat Referer */ ?>
<?php wp_head(); ?>
</head>
<body class="katalog-body upsell-body">

<header class="hero hero-ringkas">
    <p class="brand">hariH</p>
    <?php if ($sudah_cetak) : ?>
        <h1>Pesananmu sudah termasuk cetak</h1>
        <p class="hero-sub">Tidak ada yang perlu ditambahkan. Tim kami akan menghubungi WhatsApp-mu untuk proof &amp; jadwal produksi.</p>
    <?php elseif ($kedaluwarsa) : ?>
        <h1>Masa berlaku kredit sudah lewat</h1>
        <p class="hero-sub">Kredit upgrade berlaku 14 hari sejak pembayaran. Kamu tetap bisa memesan paket cetak dengan harga normal — atau tanyakan dulu ke CS.</p>
        <div class="hero-cta">
            <a class="btn btn-utama" href="<?php echo esc_url(home_url('/harga/')); ?>">Lihat Paket Cetak</a>
        </div>
    <?php else : ?>
        <h1>Kartu fisiknya, untuk yang paling kamu hormati</h1>
        <?php /* Bingkainya KREDIT, bukan diskon (keputusan owner 2026-08-07).
                 Rupiahnya identik, tapi "potongan" mengundang orang membanding
                 -bandingkan harga, sementara "sudah dibayar, tinggal cetaknya"
                 menutup lingkaran yang memang sudah ia mulai. Besaran kreditnya
                 tetap tampil — di kartu tiap paket, tempat ia jadi alasan, bukan
                 di sini tempat ia jadi tawar-menawar. */ ?>
        <p class="hero-sub">Paket digitalmu sudah dibayar. <strong>Tinggal cetaknya.</strong>
            Data undanganmu dipakai ulang — tidak ada pengisian dobel, tidak mulai dari nol.</p>
        <div class="upsell-hitung" data-tenggat="<?php echo esc_attr($tenggat->format(DATE_ATOM)); ?>">
            <p class="upsell-hitung-label">Kredit berlaku sampai <?php echo esc_html(wp_date('j F Y', $tenggat->getTimestamp())); ?></p>
            <p class="upsell-hitung-angka" id="upsell-mundur">—</p>
            <?php /* U8 — konsekuensinya disebutkan SEBELUM terlambat. Kalimat ini
                     sudah ada di cabang kedaluwarsa, yaitu di tempat ia tidak lagi
                     berguna; hitung mundur tanpa penjelasan membaca seperti ancaman
                     kehilangan paketnya, bukan kehilangan kreditnya. */ ?>
            <p class="upsell-hitung-catatan">Setelah itu paketnya tetap bisa dipesan — hanya dengan harga normal, tanpa kredit.</p>
        </div>
    <?php endif; ?>
</header>

<?php if (!$sudah_cetak && !$kedaluwarsa) : ?>
<main>
    <section class="paket" id="paket">
        <div class="paket-grid paket-grid-hybrid">
            <?php foreach ($harih_tawaran as $t) : ?>
            <article class="paket-card<?php echo $t['label'] === 'Resepsi' ? ' unggulan' : ''; ?>">
                <?php if ($t['label'] === 'Resepsi') : ?><p class="paket-badge">Paling Populer</p><?php endif; ?>
                <h3><?php echo esc_html($t['label']); ?></h3>
                <p class="paket-harga-full">Rp <strong><?php echo esc_html(number_format($t['harga_bayar'], 0, ',', '.')); ?></strong></p>
                <p class="upsell-coret">Harga normal <?php echo esc_html(strip_tags(wc_price($t['harga_penuh']))); ?> · kredit <?php echo esc_html(strip_tags(wc_price($t['kredit']))); ?></p>
                <p class="paket-sub"><?php echo esc_html($t['ringkas']); ?></p>
                <?php if ($t['beli']) : ?>
                    <a class="btn btn-utama btn-blok" href="<?php echo esc_url($t['beli']); ?>">Naik ke Paket <?php echo esc_html($t['label']); ?></a>
                <?php else : ?>
                    <a class="btn btn-utama btn-blok" href="<?php echo esc_url($wa_cs($t['label'], $order_id)); ?>" target="_blank" rel="noopener">Naik lewat WhatsApp</a>
                <?php endif; ?>
            </article>
            <?php endforeach; ?>
        </div>
        <p class="paket-catatan">Gratis ongkir se-Indonesia · proof disetujui dulu sebelum dicetak · pesanan cetak diterima paling lambat H-21 sebelum acara.</p>
    </section>
</main>
<?php endif; ?>

<footer class="kaki">
    <p class="brand">hariH</p>
    <p class="kaki-tag">Pesanan #<?php echo esc_html($order_id); ?></p>
    <nav class="kaki-nav">
        <?php /* U8 — halaman ini juga jalan buntu sebelumnya. Rekap kehadiran
                 adalah halaman yang paling sering dibutuhkan pemesan di jendela
                 waktu yang sama (RSVP sedang masuk selagi ia menimbang cetak). */ ?>
        <a href="<?php echo esc_url(add_query_arg(['order' => $order_id, 'key' => undangan_token_halaman($order_id, 'rekap')], home_url('/rekap/'))); ?>">Rekap kehadiran</a>
        <a href="<?php echo esc_url(home_url('/kontak/')); ?>">Kontak</a>
        <a href="<?php echo esc_url(home_url('/syarat-ketentuan/')); ?>">Syarat &amp; Ketentuan</a>
        <a href="<?php echo esc_url(home_url('/kebijakan-refund/')); ?>">Kebijakan Refund</a>
    </nav>
</footer>

<script>
(function () {
    'use strict';
    // Hitung mundur kredit. Tanpa angka yang bergerak, "berlaku 14 hari" hanya
    // jadi kalimat yang bisa ditunda tanpa batas.
    var kotak = document.querySelector('.upsell-hitung');
    var keluar = document.getElementById('upsell-mundur');
    if (!kotak || !keluar) return;
    var tenggat = Date.parse(kotak.getAttribute('data-tenggat'));
    if (isNaN(tenggat)) return;

    function tik() {
        var sisa = tenggat - Date.now();
        if (sisa <= 0) { keluar.textContent = 'Masa berlaku kredit habis'; return; }
        var d = Math.floor(sisa / 86400000);
        var j = Math.floor((sisa % 86400000) / 3600000);
        var m = Math.floor((sisa % 3600000) / 60000);
        keluar.textContent = d + ' hari ' + j + ' jam ' + m + ' menit lagi';
        setTimeout(tik, 60000);
    }
    tik();
})();
</script>
<?php wp_footer(); ?>
</body>
</html>
