<?php
/**
 * hariH child theme (parent: Astra).
 * Halaman undangan (CPT `undangan`) dirender standalone — lihat single-undangan.php.
 */

if (!defined('ABSPATH')) exit;

/**
 * Cache-buster SEMUA aset tema (dipakai di tiap wp_enqueue_*).
 * WAJIB dinaikkan setiap kali ada perubahan CSS/JS — tanpa itu browser
 * pengunjung & LiteSpeed tetap menyajikan berkas lama meski file di server
 * sudah baru, dan perbaikan tampilan terlihat "tidak berpengaruh".
 */
const HARIH_VERSION = '2.8.0';

add_action('after_setup_theme', function () {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
});

/* =========================================================================
 * Helper
 * ========================================================================= */

/** template_id tervalidasi whitelist (fallback aman bila mu-plugin belum ada). */
function harih_tema_aktif(int $post_id): string {
    $tema = (string) get_post_meta($post_id, 'template_id', true);
    return function_exists('undangan_sanitize_template_id')
        ? undangan_sanitize_template_id($tema)
        : 'tema-01';
}

function harih_paket_aktif(int $post_id): string {
    $p = (string) get_post_meta($post_id, 'paket', true);
    return in_array($p, ['hemat', 'favorit', 'premium'], true) ? $p : 'hemat';
}

/*
 * Font per tema. Tiap tema punya KONSEP tipografi sendiri, bukan sekadar font
 * berbeda dengan struktur sama:
 *   tema-01 — satu keluarga serif (Cormorant Garamond) untuk display, teks, DAN label
 *   tema-02 — satu keluarga sans (Karla) untuk semuanya; kontemporer
 *   tema-03 — kontras tinggi disengaja: didone Prata + Manrope 300
 *
 * `harih_tema_fonts()` DIHAPUS 2026-08-07 (G1.2): font tidak lagi datang dari
 * Google Fonts, melainkan dari @font-face self-hosted di undangan/{tema}/style.css
 * — jadi tidak ada lagi stylesheet yang perlu di-enqueue terpisah. Alasan &
 * angka pengukurannya ada di komentar tema-01/style.css dan
 * scripts/buat-font-web.py. Preload-nya diatur di blok wp_head di bawah.
 */

/** "2026-09-12" → "Sabtu, 12 September 2026" (butuh locale situs id_ID). */
function harih_format_tanggal(string $tgl): string {
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tgl)) return $tgl;
    $ts = strtotime($tgl . ' 12:00:00');
    return $ts ? wp_date('l, j F Y', $ts) : $tgl;
}

/**
 * Target countdown sebagai ISO ber-offset +07:00 (asumsi acara WIB, T3.7), atau
 * '' bila tanggal resepsi tidak valid.
 *
 * SATU sumber untuk dua konsumen — `window.UNDANGAN.target` (enqueue) DAN
 * atribut `data-target` di template countdown. Sebelumnya nilai ini hanya
 * dihitung di enqueue sementara template membaca `$args['target']` yang tidak
 * pernah ada, sehingga `data-target` selalu kosong dan countdown tidak pernah
 * berjalan di undangan mana pun (ketahuan saat QA P2.3).
 */
function harih_target_countdown(int $post_id): string {
    $tanggal = (string) get_post_meta($post_id, 'tanggal_resepsi', true);
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) return '';

    $waktu = (string) get_post_meta($post_id, 'waktu_resepsi', true);
    $jam   = preg_match('/^(\d{1,2})[:.](\d{2})/', $waktu, $m)
        ? sprintf('%02d:%s', (int) $m[1], $m[2])
        : '00:00';

    return "{$tanggal}T{$jam}:00+07:00";
}

function harih_youtube_id(string $url): string {
    if (preg_match('~(?:youtube\.com/(?:watch\?v=|embed/|shorts/|live/)|youtu\.be/)([A-Za-z0-9_-]{6,20})~', $url, $m)) {
        return $m[1];
    }
    return '';
}

/**
 * Pustaka musik instrumental berlisensi (TASKS P0.6, eks-T1.15) — URL => label.
 *
 * Berkas di-host sendiri di `uploads/musik/` (bukan media library: tidak perlu
 * jadi attachment, ikut ter-backup lewat mirror rsync mingguan, dan tidak bisa
 * terhapus tak sengaja saat membersihkan media). Sudah di-encode ulang ke
 * 128 kbps — aslinya 256 kbps / 4–5 MB, terlalu berat untuk tamu yang membuka
 * undangan dari kuota seluler.
 *
 * Lisensi: Pixabay Content License atas nama owner, bebas komersial tanpa
 * atribusi. Sertifikatnya diarsipkan — lihat `docs/aset-lisensi.md`.
 *
 * Array ini juga menjadi WHITELIST meta `musik_url` (lihat cpt.php) — menambah
 * track cukup di sini, dan URL di luar daftar ini akan ditolak.
 */
function harih_musik_library(): array {
    $dir = 'https://harih.id/wp-content/uploads/musik/';
    return [
        $dir . 'harih-klasik-modern.mp3'  => 'Klasik Modern — orkestra syahdu',
        $dir . 'harih-romantis-hangat.mp3' => 'Romantis — hangat & mengalun',
        $dir . 'harih-piano-romantis.mp3'  => 'Piano Romantis — lembut',
    ];
}

/**
 * Nuansa keagamaan undangan (evaluasi owner 2026-08-05).
 *
 * Customer hariH beragam agamanya; sebelumnya hanya ada toggle Islami on/off
 * sehingga pasangan non-Muslim kehilangan salam pembuka & ayat sama sekali —
 * padahal undangan Kristen, Hindu, Buddha, dan Konghucu punya konvensi
 * salamnya sendiri yang sama kuatnya. Nilai `umum` sengaja disediakan untuk
 * pasangan yang memilih tidak menampilkan unsur agama apa pun.
 *
 * Ketiga teks bisa ditimpa per undangan lewat meta `salam_teks`, `ayat_teks`,
 * `ayat_sumber` — supaya CS bisa menyesuaikan tanpa menyentuh kode.
 */
function harih_nuansa_daftar(): array {
    return [
        'islam'    => 'Islam',
        'kristen'  => 'Kristen',
        'katolik'  => 'Katolik',
        'hindu'    => 'Hindu',
        'buddha'   => 'Buddha',
        'konghucu' => 'Konghucu',
        'umum'     => 'Tanpa unsur agama (umum)',
    ];
}

function harih_nuansa_teks(string $nuansa): array {
    $peta = [
        'islam' => [
            'salam'   => 'Assalamu&rsquo;alaikum Warahmatullahi Wabarakatuh',
            'ayat'    => 'Dan di antara tanda-tanda (kebesaran)-Nya ialah Dia menciptakan pasangan-pasangan untukmu dari jenismu sendiri, agar kamu cenderung dan merasa tenteram kepadanya, dan Dia menjadikan di antaramu rasa kasih dan sayang.',
            'sumber'  => 'QS. Ar-Rum: 21',
            'penutup' => 'Wassalamu&rsquo;alaikum Warahmatullahi Wabarakatuh',
            'hijriah' => true,
        ],
        'kristen' => [
            'salam'   => 'Salam sejahtera bagi kita semua',
            'ayat'    => 'Demikianlah mereka bukan lagi dua, melainkan satu. Karena itu, apa yang telah dipersatukan Allah, tidak boleh diceraikan manusia.',
            'sumber'  => 'Matius 19:6',
            'penutup' => 'Tuhan Yesus memberkati',
            'hijriah' => false,
        ],
        'katolik' => [
            'salam'   => 'Salam sejahtera dalam kasih Kristus',
            'ayat'    => 'Demikianlah mereka bukan lagi dua, melainkan satu. Karena itu, apa yang telah dipersatukan Allah, tidak boleh diceraikan manusia.',
            'sumber'  => 'Matius 19:6',
            'penutup' => 'Berkah Dalem',
            'hijriah' => false,
        ],
        'hindu' => [
            'salam'   => 'Om Swastyastu',
            'ayat'    => 'Semoga pikiran yang baik datang kepada kami dari segala penjuru.',
            'sumber'  => 'Rgveda I.89.1',
            'penutup' => 'Om Shanti, Shanti, Shanti, Om',
            'hijriah' => false,
        ],
        'buddha' => [
            'salam'   => 'Namo Buddhaya',
            'ayat'    => 'Semoga semua makhluk hidup berbahagia.',
            'sumber'  => 'Karaniya Metta Sutta',
            'penutup' => 'Sabbe satta bhavantu sukhitatta',
            'hijriah' => false,
        ],
        'konghucu' => [
            'salam'   => 'Wei De Dong Tian',
            'ayat'    => 'Jalan Suci itu bermula dari hubungan suami dan istri.',
            'sumber'  => 'Zhong Yong XI: 5',
            'penutup' => 'Xian You Yi De',
            'hijriah' => false,
        ],
        'umum' => [
            'salam' => '', 'ayat' => '', 'sumber' => '', 'penutup' => '', 'hijriah' => false,
        ],
    ];
    return $peta[$nuansa] ?? $peta['umum'];
}

/**
 * Aset og:image default (P0.3) — kartu berbrand 1200×630 per tema, dibangun
 * oleh `scripts/buat-aset-og.py` dan tinggal di dalam tema (ikut rsync, tidak
 * bergantung media library yang bisa terhapus tak sengaja).
 * `$tema` kosong / tidak dikenal → kartu katalog.
 */
function harih_og_default(string $tema = ''): string {
    $file = array_key_exists($tema, function_exists('undangan_get_temas') ? undangan_get_temas() : [])
        ? "og-{$tema}.jpg"
        : 'og-katalog.jpg';
    return get_stylesheet_directory_uri() . '/aset/og/' . $file;
}

/** Foto demo (P0.2/P0.3) — stok berlisensi, ikut dalam tema. Lihat docs/aset-lisensi.md. */
function harih_foto_demo(string $nama): string {
    return get_stylesheet_directory_uri() . '/aset/demo/' . $nama;
}

/** URL webhook n8n penerima form isi data (WF-02). Set via wp-config saat deploy. */
function harih_form_webhook_url(): string {
    if (defined('N8N_FORM_WEBHOOK_URL')) return (string) N8N_FORM_WEBHOOK_URL;
    return (string) get_option('n8n_form_webhook_url', '');
}

/**
 * URL webhook pendaftaran reseller (WF-03). Default: diturunkan dari URL form
 * (ganti path `form-undangan` → `daftar-reseller`) supaya tanpa konfigurasi
 * tambahan; override via konstanta bila path webhook berbeda.
 */
function harih_reseller_webhook_url(): string {
    if (defined('N8N_RESELLER_WEBHOOK_URL')) return (string) N8N_RESELLER_WEBHOOK_URL;
    $form = harih_form_webhook_url();
    return $form !== '' ? str_replace('form-undangan', 'daftar-reseller', $form) : '';
}

/* =========================================================================
 * Aset
 * ========================================================================= */

// Halaman toko: style child di atas Astra.
add_action('wp_enqueue_scripts', function () {
    if (is_singular('undangan') || is_page_template('page-isi-data.php') || is_page_template('page-katalog.php') || is_page_template('page-jadi-reseller.php') || is_page_template('page-harga-hybrid.php') || is_page_template('page-satuan.php') || is_page_template('page-upsell.php') || is_page_template('page-proof.php') || is_page_template('page-tamu.php') || is_page_template('page-rekap.php') || is_page_template('page-teks.php')) return;
    wp_enqueue_style('harih-child', get_stylesheet_uri(), [], HARIH_VERSION);
}, 20);

// Halaman undangan, form isi data & katalog: buang SEMUA aset tema/plugin lain
// (Astra, WooCommerce, dst.) lalu muat hanya aset kita — halaman harus ringan
// & bebas bentrok CSS. Konvensi: semua handle milik kita berawalan `undangan-`.
add_action('wp_enqueue_scripts', function () {
    $is_undangan = is_singular('undangan');
    $is_isidata  = is_page_template('page-isi-data.php');
    $is_katalog  = is_page_template('page-katalog.php');
    $is_reseller = is_page_template('page-jadi-reseller.php');
    $is_hybrid   = is_page_template('page-harga-hybrid.php') || is_page_template('page-satuan.php') || is_page_template('page-upsell.php') || is_page_template('page-proof.php') || is_page_template('page-tamu.php') || is_page_template('page-rekap.php') || is_page_template('page-teks.php');
    if (!$is_undangan && !$is_isidata && !$is_katalog && !$is_reseller && !$is_hybrid) return;

    global $wp_styles, $wp_scripts;
    foreach ((array) $wp_styles->queue as $handle) {
        if (strpos($handle, 'undangan-') !== 0) wp_dequeue_style($handle);
    }
    foreach ((array) $wp_scripts->queue as $handle) {
        if (strpos($handle, 'undangan-') !== 0) wp_dequeue_script($handle);
    }

    if ($is_katalog || $is_reseller || $is_hybrid) {
        // Font halaman toko SELF-HOSTED sejak redesain 2026-08-06 (@font-face di
        // katalog.css) — tidak lagi memanggil Google Fonts. Preload woff2 yang
        // dipakai di layar pertama supaya judul tidak berkedip ganti font.
        wp_enqueue_style('undangan-katalog', get_stylesheet_directory_uri() . '/katalog.css', [], HARIH_VERSION);
        if ($is_reseller) {
            wp_enqueue_script('undangan-reseller-js', get_stylesheet_directory_uri() . '/reseller.js', [], HARIH_VERSION, true);
        }
        return;
    }

    if ($is_isidata) {
        // Font self-hosted lewat @font-face di isi-data.css — tidak lagi CDN.
        wp_enqueue_style('undangan-isidata', get_stylesheet_directory_uri() . '/undangan/shared/isi-data.css', [], HARIH_VERSION);
        wp_enqueue_script('undangan-isidata-js', get_stylesheet_directory_uri() . '/undangan/shared/isi-data.js', [], HARIH_VERSION, true);
        return;
    }

    $id   = get_queried_object_id();
    $tema = harih_tema_aktif($id);
    $dir  = get_stylesheet_directory_uri();

    // Font self-hosted lewat @font-face di undangan/{tema}/style.css — tidak ada
    // lagi stylesheet Google Fonts yang di-enqueue (G1.2, 2026-08-07).
    wp_enqueue_style('undangan-shared', "{$dir}/undangan/shared/undangan.css", [], HARIH_VERSION);
    wp_enqueue_style('undangan-tema', "{$dir}/undangan/{$tema}/style.css", ['undangan-shared'], HARIH_VERSION);
    wp_enqueue_script('undangan-js', "{$dir}/undangan/shared/undangan.js", [], HARIH_VERSION, true);

    // Normalisasi sama dengan yang dipakai section penutup, supaya nomor yang
    // sama tidak dirapikan dua cara berbeda.
    $harih_wa_mentah = (string) get_post_meta($id, 'wa_cp', true);
    $harih_wa_cp     = $harih_wa_mentah === '' ? ''
        : (function_exists('undangan_normalize_phone')
            ? undangan_normalize_phone($harih_wa_mentah)
            : preg_replace('/\D+/', '', $harih_wa_mentah));

    $cfg = [
        'id'      => $id,
        'restUrl' => esc_url_raw(rest_url('undangan/v1')),
        'target'  => harih_target_countdown($id),
        'musik'   => esc_url_raw((string) get_post_meta($id, 'musik_url', true)),
        // Penanda undangan DEMO. Dipakai undangan.js untuk mengizinkan pratinjau
        // nama lewat query (G1.3) — undangan pelanggan harus mengabaikannya
        // sepenuhnya, sama seperti pemilih nuansa `?nuansa=` yang juga khusus demo.
        'demo'    => get_post_meta($id, 'order_id', true) === 'demo',
        // Nomor CP mempelai untuk tombol konfirmasi WhatsApp pasca-RSVP (G1.6).
        // Bukan data baru yang dibuka: nomor ini SUDAH tampil publik di section
        // penutup undangan yang sama. Yang tidak pernah keluar tetap `wa_rsvp`
        // (nomor TAMU) — itu memang sengaja tidak ada di API publik.
        'waCp'    => $harih_wa_cp,
    ];
    wp_add_inline_script('undangan-js', 'window.UNDANGAN = ' . wp_json_encode($cfg) . ';', 'before');
}, 999);

/**
 * Delapan halaman yang memakai katalog.css. Daftarnya sebelumnya ditulis ulang
 * di beberapa tempat dan mulai berbeda satu sama lain — persis penyakit yang
 * membuat nav & footer diangkat jadi komponen bersama.
 */
function harih_halaman_toko(): bool {
    static $tpl = [
        'page-katalog.php', 'page-harga-hybrid.php', 'page-satuan.php', 'page-upsell.php',
        'page-proof.php', 'page-tamu.php', 'page-rekap.php', 'page-jadi-reseller.php',
        'page-teks.php',
    ];
    foreach ($tpl as $t) {
        if (is_page_template($t)) return true;
    }
    return false;
}

/**
 * Mode gelap halaman toko (G1.1) — pemasang atribut anti-kedip.
 *
 * WAJIB inline & sinkron di <head> SEBELUM CSS terpasang. Kalau keputusan tema
 * diambil setelah halaman tampil, pengunjung yang memilih gelap melihat kilatan
 * putih penuh layar dulu setiap kali membuka halaman.
 *
 * Aman terhadap cache LiteSpeed: HTML yang di-cache identik untuk semua orang —
 * yang berbeda cuma isi localStorage di sisi klien. `data-no-optimize` menahan
 * LiteSpeed men-defer/menggabung script ini; kalau ter-defer, kedipnya kembali.
 */
add_action('wp_head', function () {
    // `/isi-data/` ikut meski bukan halaman toko: pemesan berpindah ke sana tepat
    // setelah membayar, dan pilihan temanya harus terbawa.
    if (!harih_halaman_toko() && !is_page_template('page-isi-data.php')) return;
    ?>
<script data-no-optimize="1" data-cfasync="false">
(function () {
    try {
        var t = localStorage.getItem('harihTema');
        if (t === 'gelap' || t === 'terang') document.documentElement.dataset.tema = t;
    } catch (e) { /* localStorage diblokir (mode privat) → ikuti setelan sistem */ }
})();
</script>
    <?php
}, 0);

// Preload font halaman toko (self-hosted) — dua berkas yang pasti dipakai di
// layar pertama: display untuk judul, body variabel untuk sisanya.
add_action('wp_head', function () {
    if (!harih_halaman_toko()) return;
    $dir = get_stylesheet_directory_uri() . '/aset/font/';
    foreach (['DMSerifDisplay-regular.woff2', 'Figtree-latin.woff2'] as $f) {
        printf('<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n", esc_url($dir . $f));
    }
}, 1);

/**
 * Preload font UNDANGAN (self-hosted sejak 2026-08-07, G1.2).
 *
 * Menggantikan preconnect ke fonts.googleapis.com/gstatic.com yang ada di sini
 * sebelumnya. Preconnect hanya menghemat jabat tangan; yang mahal adalah
 * rantai SERIAL-nya — HTML harus diurai dulu sebelum CSS Google diminta
 * (terukur 0,34 dtk), dan woff2 baru boleh mulai setelah CSS itu tiba (0,33
 * dtk lagi). Sekarang berkasnya satu origin dengan HTML, jadi bisa
 * dipreload paralel sejak byte pertama.
 *
 * Hanya varian LATIN yang di-preload: latin-ext dibiarkan diambil normal lewat
 * `unicode-range` (jarang terpakai — lihat komentar @font-face di tema-01).
 * Yang di-preload cuma face yang pasti tampil di layar pertama; italic tidak,
 * karena pemakaian pertamanya (ampersand .mempelai-amp) ada di bawah lipatan.
 */
add_action('wp_head', function () {
    if (!is_singular('undangan')) return;

    $per_tema = [
        'tema-01' => ['cormorant-garamond-latin.woff2'],
        'tema-02' => ['karla-latin.woff2'],
        'tema-03' => ['prata-latin.woff2', 'manrope-latin.woff2'],
    ];
    $tema = harih_tema_aktif(get_queried_object_id());
    $dir  = get_stylesheet_directory_uri() . '/aset/font/';

    foreach ($per_tema[$tema] ?? [] as $f) {
        printf('<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n", esc_url($dir . $f));
    }
}, 1);

/* =========================================================================
 * Open Graph (T1.14) — preview share WhatsApp adalah etalase produk.
 * Cache-safe: sama untuk semua tamu (query `to` di-drop LiteSpeed).
 * ========================================================================= */

add_action('wp_head', function () {
    if (!is_singular('undangan')) return;

    $id = get_queried_object_id();
    $np = trim((string) get_post_meta($id, 'nama_pria', true));
    $nw = trim((string) get_post_meta($id, 'nama_wanita', true));

    $title = ($np !== '' && $nw !== '') ? "Undangan Pernikahan {$np} & {$nw}" : get_the_title($id);

    $potongan = ['Kami mengundang Bapak/Ibu/Saudara/i untuk hadir & memberikan doa restu.'];
    if ($tgl = (string) get_post_meta($id, 'tanggal_resepsi', true)) $potongan[] = harih_format_tanggal($tgl);
    if ($lok = trim((string) get_post_meta($id, 'lokasi_nama', true))) $potongan[] = $lok;
    $desc = implode(' · ', $potongan);

    // og:image = foto pertama galeri; bila kosong → kartu default per tema
    // (P0.3). Ini bukan kasus pinggiran: WF-02 sengaja mengabaikan galeri untuk
    // paket Hemat, jadi SELURUH undangan Hemat masuk jalur fallback ini —
    // tanpa itu paket volume kita di-share ke WhatsApp tanpa gambar sama sekali.
    // Kartu komposit 1200×630 per undangan (FU.1): foto pasangan + nama + tanggal.
    // Foto galeri MENTAH tidak boleh dipakai langsung — hampir selalu potret,
    // dan WhatsApp memotongnya jadi pita tengah tanpa konteks apa pun.
    $img = function_exists('undangan_og_kartu') ? undangan_og_kartu($id) : '';
    if ($img === '') $img = harih_og_default(harih_tema_aktif($id));

    echo "\n" . '<meta property="og:type" content="website">' . "\n";
    echo '<meta property="og:site_name" content="hariH">' . "\n";
    echo '<meta property="og:locale" content="id_ID">' . "\n";
    echo '<meta property="og:title" content="' . esc_attr($title) . '">' . "\n";
    echo '<meta property="og:description" content="' . esc_attr($desc) . '">' . "\n";
    echo '<meta property="og:url" content="' . esc_url(get_permalink($id)) . '">' . "\n";
    echo '<meta property="og:image" content="' . esc_url($img) . '">' . "\n";
    // Dimensi WAJIB dinyatakan & harus BENAR: crawler memakainya untuk memesan
    // ruang preview. Semua jalur di atas kini menghasilkan 1200×630.
    echo '<meta property="og:image:width" content="1200">' . "\n";
    echo '<meta property="og:image:height" content="630">' . "\n";
    echo '<meta property="og:image:alt" content="' . esc_attr($title) . '">' . "\n";
    echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
}, 5);

/* =========================================================================
 * Analytics (P2.5)
 * ========================================================================= */

/** Measurement ID GA4. Kosongkan untuk mematikan pelacakan sepenuhnya. */
const HARIH_GA4_ID = 'G-WZ2K77HHY8';

/**
 * GA4 sengaja TIDAK dimuat di dua tempat:
 *
 * 1. `/isi-data/` — URL halaman ini memuat **token order**, sebuah bearer
 *    credential. GA4 mengirim URL lengkap sebagai `page_location`, jadi
 *    memasangnya di sana berarti menyerahkan token pelanggan ke Google —
 *    sekaligus membatalkan `Referrer-Policy: no-referrer` yang dipasang khusus
 *    untuk mencegah kebocoran token itu (T2.9).
 *
 * 2. Halaman undangan `/u/*` — pengunjungnya adalah **tamu customer**, bukan
 *    customer kita; mereka tidak punya hubungan apa pun dengan hariH dan tidak
 *    sepatutnya ikut dilacak. Halaman itu juga yang paling sensitif performanya
 *    (dibuka dari kuota seluler) dan sepenuhnya mengandalkan cache LiteSpeed.
 *
 * Funnel yang memang perlu diukur — katalog → halaman produk → checkout —
 * seluruhnya tetap terlacak.
 */
add_action('wp_head', function () {
    if (HARIH_GA4_ID === '') return;
    if (is_singular('undangan') || is_page_template('page-isi-data.php')) return;

    $id = esc_js(HARIH_GA4_ID);
    echo "\n<!-- GA4 (P2.5) -->\n";
    echo '<script async src="https://www.googletagmanager.com/gtag/js?id=' . esc_attr(HARIH_GA4_ID) . '"></script>' . "\n";
    echo "<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}"
       . "gtag('js',new Date());gtag('config','{$id}');</script>\n";
}, 2);

/* =========================================================================
 * SEO dasar (P2.4)
 * ========================================================================= */

/**
 * Halaman utilitas: tidak punya konten untuk dicari, jadi dikeluarkan dari
 * sitemap DAN diberi noindex. `/isi-data/` bahkan membalas 403 tanpa token —
 * mendaftarkannya di sitemap hanya melahirkan error di Search Console;
 * `/shop/` duplikat katalog front page dan bersaing untuk kata kunci yang sama
 * (halaman produk tetap boleh terindeks — itu bisa punya nilai cari sendiri).
 */
function harih_halaman_utilitas(): array {
    $ids = [];
    // `/upsell/` bertoken (403 tanpa token) dan `/satuan/` adalah pembanding
    // harga, bukan halaman yang ingin kita menangkan di pencarian.
    foreach (['isi-data', 'upsell', 'satuan', 'proof', 'tamu', 'rekap'] as $slug) {
        if ($p = get_page_by_path($slug)) $ids[] = (int) $p->ID;
    }
    if (function_exists('wc_get_page_id')) {
        foreach (['cart', 'checkout', 'myaccount', 'shop'] as $key) {
            $ids[] = (int) wc_get_page_id($key);
        }
    }
    return array_values(array_unique(array_filter($ids, fn($id) => $id > 0)));
}

add_filter('wp_sitemaps_posts_query_args', function ($args, $post_type) {
    if ($post_type !== 'page') return $args;
    if ($ids = harih_halaman_utilitas()) {
        $args['post__not_in'] = array_merge((array) ($args['post__not_in'] ?? []), $ids);
    }
    return $args;
}, 10, 2);

/**
 * Judul dokumen halaman standalone. Sebelumnya judul front page literal
 * "harih.id" — tanpa satu pun kata yang dicari calon customer di Google,
 * dan itu juga judul yang muncul saat link disalin ke luar WhatsApp.
 */
add_filter('document_title_parts', function ($parts) {
    if (is_page_template('page-katalog.php')) {
        $parts['title'] = 'Undangan Digital Otomatis, Langsung ke WhatsApp';
        $parts['site']  = 'hariH';
        unset($parts['tagline'], $parts['page']);
    } elseif (is_page_template('page-satuan.php')) {
        $parts['title'] = 'Beli Satuan — Kartu QR, Label & Souvenir';
        $parts['site']  = 'hariH';
        unset($parts['tagline'], $parts['page']);
    } elseif (is_page_template('page-rekap.php')) {
        $parts['title'] = 'Rekap Konfirmasi Kehadiran';
        $parts['site']  = 'hariH';
        unset($parts['tagline'], $parts['page']);
    } elseif (is_page_template('page-tamu.php')) {
        $parts['title'] = 'Daftar Tamu & Link Personal';
        $parts['site']  = 'hariH';
        unset($parts['tagline'], $parts['page']);
    } elseif (is_page_template('page-proof.php')) {
        $parts['title'] = 'Persetujuan Proof Cetak';
        $parts['site']  = 'hariH';
        unset($parts['tagline'], $parts['page']);
    } elseif (is_page_template('page-upsell.php')) {
        $parts['title'] = 'Penawaran Naik Paket Cetak';
        $parts['site']  = 'hariH';
        unset($parts['tagline'], $parts['page']);
    } elseif (is_page_template('page-harga-hybrid.php')) {
        $parts['title'] = 'Undangan Digital + Kartu Fisik Ber-QR';
        $parts['site']  = 'hariH';
        unset($parts['tagline'], $parts['page']);
    } elseif (is_page_template('page-jadi-reseller.php')) {
        $parts['title'] = 'Jadi Reseller — Komisi 30% Tiap Order';
        $parts['site']  = 'hariH';
        unset($parts['tagline'], $parts['page']);
    }
    return $parts;
});

// Admin bar mengganggu layout fullscreen undangan & form.
add_filter('show_admin_bar', function ($show) {
    return (is_singular('undangan') || is_page_template('page-isi-data.php')) ? false : $show;
});

// Form isi data: jangan diindeks — URL berisi token order (T2.9 pelengkap).
// Halaman utilitas lain (cart/checkout/akun/shop) ikut di-noindex — P2.4.
add_filter('wp_robots', function ($robots) {
    if (is_page_template('page-isi-data.php')) {
        $robots['noindex']  = true;
        $robots['nofollow'] = true;
        return $robots;
    }
    // is_shop() dipakai terpisah: halaman shop Woo adalah archive post-type,
    // is_page() tidak mengenalinya.
    if ((function_exists('is_shop') && is_shop()) || is_page(harih_halaman_utilitas())) {
        $robots['noindex'] = true;
    }
    return $robots;
});
