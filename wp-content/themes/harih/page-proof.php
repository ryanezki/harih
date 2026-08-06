<?php
/**
 * Template Name: Persetujuan Proof hariH
 *
 * Halaman bertoken tempat pemesan menyetujui proof cetak (TASKS F4.5).
 * Token HMAC sama dengan `/isi-data/` & `/upsell/`; tanpa token → 403.
 *
 * Halaman ini yang membuat klausul S&K §12.1 punya bukti: waktu persetujuan,
 * hash berkas proof, dan hash snapshot data dicatat di order. Karena itu
 * konsekuensinya ditulis TERANG di atas tombol — bukan disembunyikan di
 * tautan syarat & ketentuan.
 */

if (!defined('ABSPATH')) exit;

$order_id = absint($_GET['order'] ?? 0);
$key      = sanitize_text_field((string) ($_GET['key'] ?? ''));

$sah = $order_id && $key !== '' && defined('FORM_TOKEN_SECRET') && hash_equals(
    substr(hash_hmac('sha256', (string) $order_id, FORM_TOKEN_SECRET), 0, 16),
    $key
);
$order = $sah && function_exists('wc_get_order') ? wc_get_order($order_id) : null;
if (!$order) {
    wp_die('Link persetujuan tidak valid. Hubungi CS bila kamu merasa ini keliru.', 'Link tidak valid', ['response' => 403]);
}

$pesan = '';
if (($_POST['harih_setuju'] ?? '') === '1' && wp_verify_nonce((string) ($_POST['_harih_nonce'] ?? ''), 'harih_proof_' . $order_id)) {
    $hasil = undangan_catat_persetujuan($order);
    $pesan = $hasil['pesan'];
    $order = wc_get_order($order_id); // muat ulang agar status terbaru terbaca
}

$berkas    = undangan_proof_berkas($order);
$disetujui = (string) $order->get_meta('_proof_disetujui');
$snapshot  = json_decode((string) $order->get_meta('_snapshot'), true) ?: [];

/** Baris ringkas data yang dibekukan — supaya pemesan memeriksa yang benar. */
$harih_label = [
    'nama_pria' => 'Mempelai pria', 'nama_wanita' => 'Mempelai wanita',
    'ortu_pria' => 'Orang tua pria', 'ortu_wanita' => 'Orang tua wanita',
    'tanggal_akad' => 'Tanggal akad', 'waktu_akad' => 'Waktu akad',
    'lokasi_akad_nama' => 'Lokasi akad', 'lokasi_akad_alamat' => 'Alamat akad',
    'tanggal_resepsi' => 'Tanggal resepsi', 'waktu_resepsi' => 'Waktu resepsi',
    'lokasi_nama' => 'Lokasi resepsi', 'lokasi_alamat' => 'Alamat resepsi',
];
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="robots" content="noindex, nofollow">
<meta name="referrer" content="no-referrer">
<?php wp_head(); ?>
</head>
<body class="katalog-body proof-body">

<header class="hero hero-ringkas">
    <p class="brand">hariH</p>
    <?php if ($disetujui) : ?>
        <h1>Proof sudah disetujui</h1>
        <p class="hero-sub">Disetujui pada <?php echo esc_html(wp_date('j F Y, H:i', strtotime($disetujui))); ?> WIB. Pesanan <strong>#<?php echo esc_html($order_id); ?></strong> masuk antrean produksi — nomor resi kami kirimkan setelah paket berangkat.</p>
    <?php else : ?>
        <h1>Periksa proof cetak Anda</h1>
        <p class="hero-sub">Ini tampilan terakhir sebelum masuk mesin. Mohon periksa <strong>ejaan nama, gelar, tanggal, jam, dan alamat</strong> baik-baik — bagian inilah yang paling sering luput.</p>
    <?php endif; ?>
</header>

<main>
    <?php if ($pesan) : ?><p class="proof-notif"><?php echo esc_html($pesan); ?></p><?php endif; ?>

    <section class="proof-berkas">
        <?php if (!$berkas) : ?>
            <p class="satuan-kosong">Berkas proof belum dilampirkan. Tim kami akan mengirimkannya lebih dulu lewat WhatsApp.</p>
        <?php else : ?>
            <?php foreach ($berkas as $i => $url) : ?>
                <?php $gambar = (bool) preg_match('/\.(jpe?g|png|webp)$/i', $url); ?>
                <figure class="proof-item">
                    <?php if ($gambar) : ?>
                        <a href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener">
                            <img src="<?php echo esc_url($url); ?>" alt="Proof cetak halaman <?php echo esc_attr($i + 1); ?>" loading="lazy">
                        </a>
                        <figcaption>Ketuk gambar untuk memperbesar</figcaption>
                    <?php else : ?>
                        <a class="btn btn-garis" href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener">Buka berkas proof <?php echo esc_html($i + 1); ?></a>
                    <?php endif; ?>
                </figure>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>

    <?php if ($snapshot) : ?>
    <section class="proof-data">
        <h2>Data yang akan dicetak</h2>
        <dl class="proof-daftar">
            <?php foreach ($harih_label as $k => $label) : ?>
                <?php if (!empty($snapshot[$k])) : ?>
                    <div><dt><?php echo esc_html($label); ?></dt><dd><?php echo esc_html($snapshot[$k]); ?></dd></div>
                <?php endif; ?>
            <?php endforeach; ?>
        </dl>
        <p class="proof-kunci">Data ini <strong>dibekukan</strong> saat proof dibuat. Perubahan pada undangan digital setelah ini tidak ikut tercetak — beri tahu CS bila ada yang perlu diubah, sebelum Anda menyetujui.</p>
    </section>
    <?php endif; ?>

    <?php if (!$disetujui && $berkas) : ?>
    <section class="proof-setuju">
        <p class="proof-konsekuensi">Dengan menekan tombol di bawah, Anda menyatakan seluruh teks pada proof sudah benar. <strong>Setelah disetujui, kesalahan penulisan yang sudah tampak di proof menjadi tanggung jawab pemesan</strong> dan cetak ulang dikenakan biaya — sesuai <a href="<?php echo esc_url(home_url('/syarat-ketentuan/')); ?>" target="_blank" rel="noopener">Syarat &amp; Ketentuan §12.1</a>. Kesalahan dari pihak kami tetap kami cetak ulang gratis.</p>
        <form method="post">
            <?php wp_nonce_field('harih_proof_' . $order_id, '_harih_nonce'); ?>
            <input type="hidden" name="harih_setuju" value="1">
            <button type="submit" class="btn btn-utama btn-blok">Saya sudah periksa — setujui proof ini</button>
        </form>
        <p class="proof-batal">Ada yang perlu diperbaiki? <a href="https://wa.me/6282251975575?text=<?php echo esc_attr(rawurlencode("Halo hariH, ada yang perlu diperbaiki di proof pesanan #{$order_id}.")); ?>" target="_blank" rel="noopener">Beri tahu kami lewat WhatsApp</a> — jangan setujui dulu.</p>
    </section>
    <?php endif; ?>
</main>

<footer class="kaki">
    <p class="brand">hariH</p>
    <p class="kaki-tag">Pesanan #<?php echo esc_html($order_id); ?></p>
    <nav class="kaki-nav">
        <a href="<?php echo esc_url(home_url('/kontak/')); ?>">Kontak</a>
        <a href="<?php echo esc_url(home_url('/syarat-ketentuan/')); ?>">Syarat &amp; Ketentuan</a>
    </nav>
</footer>

<?php wp_footer(); ?>
</body>
</html>
