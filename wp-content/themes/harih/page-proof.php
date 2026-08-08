<?php
/**
 * Template Name: Persetujuan Proof hariH
 *
 * Halaman bertoken tempat pemesan menyetujui proof cetak (TASKS F4.5).
 * Token HMAC BERCAKUP `proof` (B9); tanpa token → 403. Meneruskan link ini
 * TIDAK ikut memberi akses ke halaman bertoken lain — itu inti perubahannya.
 *
 * Halaman ini yang membuat klausul S&K §12.1 punya bukti: waktu persetujuan,
 * hash berkas proof, dan hash snapshot data dicatat di order. Karena itu
 * konsekuensinya ditulis TERANG di atas tombol — bukan disembunyikan di
 * tautan syarat & ketentuan.
 */

if (!defined('ABSPATH')) exit;

// Sama seperti /isi-data/: halaman bertoken tidak boleh di-cache. Tanpa ini
// LiteSpeed menyajikan HTML lama berhari-hari, dan nonce anonim yang hanya sah
// ≤24 jam sudah mati saat pemesan menekan Setujui.
nocache_headers();
do_action('litespeed_control_set_nocache'); // no-op bila LSCWP tidak aktif

$order_id = absint($_GET['order'] ?? 0);
$key      = sanitize_text_field((string) ($_GET['key'] ?? ''));

// B9 — token DICAKUP per halaman: token /proof/ tidak membuka halaman lain.
// Rumusnya terpusat di undangan_token_halaman() (mu-plugins/undangan-core).
$sah = undangan_token_sah($order_id, 'proof', $key);
$order = $sah && function_exists('wc_get_order') ? wc_get_order($order_id) : null;
if (!$order) {
    wp_die('Link persetujuan tidak valid. Hubungi CS bila kamu merasa ini keliru.', 'Link tidak valid', ['response' => 403]);
}

$pesan = '';
$galat = '';
if (($_POST['harih_setuju'] ?? '') === '1') {
    // Nonce DIPISAH dari aksi. Sebelumnya keduanya digabung, sehingga nonce mati
    // membuat tombol "Setujui" jadi tombol mati tanpa satu kalimat pun: pemesan
    // menekannya, halaman memuat ulang tak berubah, dan ia menganggap sudah
    // menyetujui — sementara Antrean Cetak selamanya menampilkan "Menunggu
    // persetujuan pelanggan" pada order yang tenggat Garansi Tepat Waktu-nya
    // berjalan. Nonce anonim hanya sah ≤24 jam; halaman ini bisa dibuka
    // berhari-hari setelah linknya dikirim, jadi ini kasus NORMAL.
    if (!wp_verify_nonce((string) ($_POST['_harih_nonce'] ?? ''), 'harih_proof_' . $order_id)) {
        $galat = 'Sesi halaman ini sudah kedaluwarsa, jadi persetujuanmu BELUM tercatat. Muat ulang halaman lalu tekan Setujui sekali lagi.';
    } else {
        $hasil = undangan_catat_persetujuan($order);
        $pesan = $hasil['pesan'];
        $order = wc_get_order($order_id); // muat ulang agar status terbaru terbaca
    }
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
    <?php if ($galat) : ?><p class="proof-notif proof-galat"><?php echo esc_html($galat); ?></p><?php endif; ?>
    <?php if ($pesan) : ?><p class="proof-notif"><?php echo esc_html($pesan); ?></p><?php endif; ?>

    <section class="proof-berkas">
        <?php if (!$berkas) : ?>
            <p class="satuan-kosong">Berkas proof belum dilampirkan. Tim kami akan mengirimkannya lebih dulu lewat WhatsApp.</p>
        <?php else : ?>
            <?php foreach ($berkas as $i => $url) : ?>
                <?php $gambar = (bool) preg_match('/\.(jpe?g|png|webp)$/i', $url); ?>
                <figure class="proof-item">
                    <?php if ($gambar) : ?>
                        <?php /* U7 — berkas PERTAMA dimuat lebih dulu, bukan `lazy`.
                                 Gambar ini satu-satunya alasan halaman ini ada dan
                                 elemen isi pertama di bawah header; `lazy`
                                 menurunkan prioritas pengambilannya justru di
                                 tempat ia paling dibutuhkan. Sisanya tetap lazy. */ ?>
                        <a href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener">
                            <img src="<?php echo esc_url($url); ?>" alt="Proof cetak halaman <?php echo esc_attr($i + 1); ?>"
                                 <?php echo $i === 0 ? 'loading="eager" fetchpriority="high"' : 'loading="lazy"'; ?>>
                        </a>
                        <figcaption>
                            Ketuk gambar untuk memperbesar ·
                            <a href="<?php echo esc_url($url); ?>" download>Unduh berkas</a>
                        </figcaption>
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
            <?php
            /* C6 — daftar tamu ikut dibekukan, tapi ditampilkan sebagai JUMLAH,
               bukan 600 baris nama: tabel ini untuk memeriksa sekilas, dan
               menumpahkan seluruh daftar ke sini justru mengubur data lain yang
               harus diperiksa. Yang perlu diyakinkan pemesan adalah bahwa nama
               tamunya memang ikut terkunci — bukan membaca ulang semuanya. */
            $harih_tamu = array_filter(array_map('trim', explode("\n", (string) ($snapshot['daftar_tamu'] ?? ''))));
            ?>
            <?php if ($harih_tamu) : ?>
                <div>
                    <dt>Daftar tamu (amplop)</dt>
                    <dd><?php printf('%d nama — ikut dibekukan', count($harih_tamu)); ?></dd>
                </div>
            <?php endif; ?>
        </dl>
        <p class="proof-kunci">Data ini <strong>dibekukan</strong> saat proof dibuat. Perubahan pada undangan digital setelah ini tidak ikut tercetak — beri tahu CS bila ada yang perlu diubah, sebelum Anda menyetujui.</p>
    </section>
    <?php endif; ?>

    <?php /* U7 — tombol setujui menuntut SNAPSHOT, bukan cuma berkas.
             Syarat lama `if (!$disetujui && $berkas)` tidak menguji $snapshot,
             sementara seluruh section pemeriksaan data bersyarat `if ($snapshot)`.
             Bila CS menempel URL proof tapi lupa mencentang "Bekukan snapshot"
             (kotak terpisah di proof.php), halaman yang dilihat pemesan menyusut
             jadi: gambar + paragraf tanggung jawab hukum + tombol setujui —
             tanpa tabel data yang justru ia setujui. `_proof_hash` lalu mengunci
             berkas saja, nol data. */ ?>
    <?php if (!$disetujui && $berkas && !$snapshot) : ?>
    <section class="proof-setuju">
        <p class="proof-notif proof-galat">Data cetakmu belum kami kunci, jadi tombol persetujuan belum kami tampilkan — menyetujui sekarang berarti menyetujui gambar tanpa data yang menyertainya. Tim kami sedang menyiapkannya; tombolnya muncul setelah itu.</p>
    </section>
    <?php endif; ?>

    <?php if (!$disetujui && $berkas && $snapshot) : ?>
    <section class="proof-setuju">
        <p class="proof-konsekuensi">Dengan menekan tombol di bawah, Anda menyatakan seluruh teks pada proof sudah benar. <strong>Setelah disetujui, kesalahan penulisan yang sudah tampak di proof menjadi tanggung jawab pemesan</strong> dan cetak ulang dikenakan biaya — sesuai <a href="<?php echo esc_url(home_url('/syarat-ketentuan/')); ?>" target="_blank" rel="noopener">Syarat &amp; Ketentuan §12.1</a>. Kesalahan dari pihak kami tetap kami cetak ulang gratis.</p>
        <form method="post">
            <?php wp_nonce_field('harih_proof_' . $order_id, '_harih_nonce'); ?>
            <input type="hidden" name="harih_setuju" value="1">
            <button type="submit" class="btn btn-utama btn-blok">Saya sudah periksa — setujui proof ini</button>
        </form>
    </section>
    <?php endif; ?>

    <?php /* U7 — jalur pemulihan hidup di KEDUA keadaan. Sebelumnya ia berada
             di dalam blok `!$disetujui`, jadi begitu proof disetujui satu-satunya
             cara memberi tahu "ada yang salah" ikut lenyap — tepat di jendela
             waktu ia paling dibutuhkan, selagi pesanan belum masuk mesin. */ ?>
    <?php if ($berkas) : ?>
    <p class="proof-batal">
        <?php if ($disetujui) : ?>
            Baru menemukan kesalahan? <a href="<?php echo esc_url(harih_wa_link("Halo hariH, saya menemukan kesalahan pada proof pesanan #{$order_id} yang sudah saya setujui.")); ?>" target="_blank" rel="noopener">Hubungi kami SEKARANG lewat WhatsApp</a> — selama belum masuk mesin, masih bisa kami tahan.
        <?php else : ?>
            Ada yang perlu diperbaiki? <a href="<?php echo esc_url(harih_wa_link("Halo hariH, ada yang perlu diperbaiki di proof pesanan #{$order_id}.")); ?>" target="_blank" rel="noopener">Beri tahu kami lewat WhatsApp</a> — jangan setujui dulu.
        <?php endif; ?>
    </p>
    <?php endif; ?>
</main>

<?php
/* U8 — halaman ini dulu JALAN BUNTU: footernya cuma Kontak + S&K, jadi
   pemesan yang sedang memeriksa proof tidak punya jalan ke daftar nama yang
   justru akan dicetak di amplopnya. Linknya tiba terpencar lewat WhatsApp
   berhari-hari jarak, jadi menemukannya kembali bukan hal sepele.
   Pola add_query_arg + undangan_token_halaman() sama persis dengan
   page-tamu.php → /rekap/. */
$harih_uid   = function_exists('undangan_cari_undangan_order') ? undangan_cari_undangan_order($order_id) : 0;
$harih_ntamu = $harih_uid
    ? count(array_filter(array_map('trim', explode("\n", (string) get_post_meta($harih_uid, 'daftar_tamu', true)))))
    : 0;
$harih_link_tamu = add_query_arg(
    ['order' => $order_id, 'key' => undangan_token_halaman($order_id, 'tamu')],
    home_url('/tamu/')
);
?>
<footer class="kaki">
    <p class="brand">hariH</p>
    <p class="kaki-tag">Pesanan #<?php echo esc_html($order_id); ?></p>
    <nav class="kaki-nav">
        <a href="<?php echo esc_url($harih_link_tamu); ?>"><?php
            echo $harih_ntamu
                ? esc_html(sprintf('Lihat %d nama yang akan dicetak di amplop', $harih_ntamu))
                : 'Daftar tamu untuk amplop';
        ?></a>
        <a href="<?php echo esc_url(home_url('/kontak/')); ?>">Kontak</a>
        <a href="<?php echo esc_url(home_url('/syarat-ketentuan/')); ?>">Syarat &amp; Ketentuan</a>
    </nav>
</footer>

<?php wp_footer(); ?>
</body>
</html>
