<?php
/**
 * Template Name: Katalog Satuan hariH
 *
 * Katalog à la carte (TASKS F3.11) — item cetak yang dibeli terpisah dari paket.
 *
 * Daftar produk dibaca LANGSUNG dari WooCommerce (SKU `SATUAN-*`), bukan
 * ditulis ulang di sini: harga di halaman ini tidak akan pernah berbeda dari
 * harga yang ditagihkan checkout. Minimum per produk juga dibaca dari meta
 * `_min_qty` yang sama yang ditegakkan mu-plugin.
 *
 * Aturan bisnis yang harus terlihat SEBELUM pembeli mengisi keranjang:
 *   · minimum Rp 1.000.000 per transaksi (keputusan terkunci)
 *   · paket selalu lebih hemat per unit — halaman ini pembandingnya, bukan
 *     jalur utama. Karena itu jalur ke /harga/ ditaruh di atas dan di bawah.
 */

if (!defined('ABSPATH')) exit;

$harih_satuan = [];
if (function_exists('wc_get_products')) {
    $harih_satuan = wc_get_products([
        'status'  => 'publish',
        'limit'   => 40,
        'orderby' => 'price',
        'order'   => 'DESC',
    ]);
    $harih_satuan = array_values(array_filter($harih_satuan, static fn($p) =>
        str_starts_with(strtoupper((string) $p->get_sku()), 'SATUAN-')));
}

$harih_min_transaksi = defined('UNDANGAN_MIN_TRANSAKSI_SATUAN') ? UNDANGAN_MIN_TRANSAKSI_SATUAN : 1000000;
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="description" content="Kartu undangan ber-QR, label souvenir, hangtag, dan kartu terima kasih — dibeli satuan. Minimum Rp 1 juta per transaksi, gratis ongkir se-Indonesia.">
<meta name="robots" content="noindex, follow">
<?php wp_head(); ?>
</head>
<body class="katalog-body satuan-body">

<?php get_template_part('template-parts/toko/nav', null, ['beranda' => false]); ?>

<header class="hero hero-ringkas">
    <h1>Beli satuan</h1>
    <p class="hero-sub">Sudah punya undangan digital, tinggal butuh cetakannya? Semua item bisa dipesan terpisah. <strong>Paket tetap lebih hemat per unit</strong> — halaman ini pembandingnya.</p>
    <div class="hero-cta">
        <a class="btn btn-utama" href="<?php echo esc_url(home_url('/harga/')); ?>">Lihat Paket (lebih hemat)</a>
    </div>
</header>

<main>
    <section class="satuan-aturan">
        <p><strong>Minimum <?php echo esc_html(strip_tags(wc_price($harih_min_transaksi))); ?> per transaksi.</strong> Tiap item juga punya minimum jumlahnya sendiri — tercantum di kartunya. Alasannya sederhana: menyiapkan cetakan 20 pcs memakan waktu yang hampir sama dengan 200 pcs.</p>
        <p>Gratis ongkir ke seluruh Indonesia · proof disetujui dulu sebelum dicetak · pesanan diterima paling lambat H-21 sebelum acara.</p>
    </section>

    <section class="paket" id="item">
        <?php if (!$harih_satuan) : ?>
            <p class="satuan-kosong">Katalog satuan sedang disiapkan. <a href="<?php echo esc_url(home_url('/harga/')); ?>">Lihat paket</a> lebih dulu, ya.</p>
        <?php else : ?>
        <div class="satuan-grid">
            <?php foreach ($harih_satuan as $p) :
                $min  = max(1, (int) $p->get_meta('_min_qty'));
                // Sama seperti katalog: selama gerbang sandbox, arahkan ke WhatsApp.
                $beli = harih_bayar_online_siap()
                    ? add_query_arg(['add-to-cart' => $p->get_id(), 'quantity' => $min], wc_get_cart_url())
                    : harih_wa_pesan($p->get_name() . ' (min. ' . $min . ' pcs)');
                $subtotal_min = (float) $p->get_price() * $min;
            ?>
            <article class="satuan-kartu">
                <h3><?php echo esc_html($p->get_name()); ?></h3>
                <p class="satuan-harga"><?php echo wp_kses_post(wc_price($p->get_price())); ?><span>/pcs</span></p>
                <p class="satuan-min">Minimum <?php echo esc_html($min); ?> pcs · <?php echo esc_html(strip_tags(wc_price($subtotal_min))); ?></p>
                <?php if ($p->get_short_description()) : ?>
                    <p class="satuan-ket"><?php echo esc_html(wp_strip_all_tags($p->get_short_description())); ?></p>
                <?php endif; ?>
                <a class="btn btn-garis btn-blok" href="<?php echo esc_url($beli); ?>">Tambah <?php echo esc_html($min); ?> pcs</a>
            </article>
            <?php endforeach; ?>
        </div>
        <p class="paket-catatan">Keranjang menolak checkout selama total item satuan belum mencapai <?php echo esc_html(strip_tags(wc_price($harih_min_transaksi))); ?> — tambahkan item lain, atau ambil paket yang sudah termasuk undangan digital.</p>
        <?php endif; ?>
    </section>

    <section class="jembatan-cetak">
        <p class="jembatan-label">Perbandingan</p>
        <h2>Isi Paket Resepsi kalau dibeli satuan: Rp 3.600.000</h2>
        <p>Harga paketnya Rp 2.900.000 — selisih <strong>Rp 700.000</strong>, dan undangan digital customnya sudah termasuk.</p>
        <a class="btn btn-utama" href="<?php echo esc_url(home_url('/harga/')); ?>">Lihat Paket</a>
    </section>
</main>

<?php get_template_part('template-parts/toko/kaki', null, null); ?>

<?php wp_footer(); ?>
</body>
</html>
