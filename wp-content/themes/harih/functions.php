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
const HARIH_VERSION = '0.3.0';

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

/**
 * Google Fonts per tema. Pertimbangkan self-host / fitur "Localize Resources"
 * LiteSpeed setelah launch (performa + privasi).
 */
function harih_tema_fonts(string $tema): string {
    $map = [
        'tema-01' => 'https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;0,600;1,400&family=Plus+Jakarta+Sans:wght@400;500;600&display=swap',
        'tema-02' => 'https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,400&family=Karla:wght@400;500;600&display=swap',
        'tema-03' => 'https://fonts.googleapis.com/css2?family=Prata&family=Manrope:wght@400;500;600&display=swap',
    ];
    return $map[$tema] ?? '';
}

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
 * Pustaka musik instrumental berlisensi (TASKS T1.15) — URL file => label.
 * Host file di uploads sendiri; arsipkan bukti lisensinya. Form isi data
 * otomatis menampilkan dropdown begitu array ini terisi.
 */
function harih_musik_library(): array {
    return [
        // 'https://harih.id/wp-content/uploads/musik/contoh.mp3' => 'Contoh — piano lembut',
    ];
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
    if (is_singular('undangan') || is_page_template('page-isi-data.php') || is_page_template('page-katalog.php') || is_page_template('page-jadi-reseller.php')) return;
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
    if (!$is_undangan && !$is_isidata && !$is_katalog && !$is_reseller) return;

    global $wp_styles, $wp_scripts;
    foreach ((array) $wp_styles->queue as $handle) {
        if (strpos($handle, 'undangan-') !== 0) wp_dequeue_style($handle);
    }
    foreach ((array) $wp_scripts->queue as $handle) {
        if (strpos($handle, 'undangan-') !== 0) wp_dequeue_script($handle);
    }

    if ($is_katalog || $is_reseller) {
        wp_enqueue_style('undangan-fonts', harih_tema_fonts('tema-01'), [], null);
        wp_enqueue_style('undangan-katalog', get_stylesheet_directory_uri() . '/katalog.css', [], HARIH_VERSION);
        if ($is_reseller) {
            wp_enqueue_script('undangan-reseller-js', get_stylesheet_directory_uri() . '/reseller.js', [], HARIH_VERSION, true);
        }
        return;
    }

    if ($is_isidata) {
        wp_enqueue_style('undangan-fonts', harih_tema_fonts('tema-01'), [], null);
        wp_enqueue_style('undangan-isidata', get_stylesheet_directory_uri() . '/undangan/shared/isi-data.css', [], HARIH_VERSION);
        wp_enqueue_script('undangan-isidata-js', get_stylesheet_directory_uri() . '/undangan/shared/isi-data.js', [], HARIH_VERSION, true);
        return;
    }

    $id   = get_queried_object_id();
    $tema = harih_tema_aktif($id);
    $dir  = get_stylesheet_directory_uri();

    if ($font = harih_tema_fonts($tema)) {
        wp_enqueue_style('undangan-fonts', $font, [], null);
    }
    wp_enqueue_style('undangan-shared', "{$dir}/undangan/shared/undangan.css", [], HARIH_VERSION);
    wp_enqueue_style('undangan-tema', "{$dir}/undangan/{$tema}/style.css", ['undangan-shared'], HARIH_VERSION);
    wp_enqueue_script('undangan-js', "{$dir}/undangan/shared/undangan.js", [], HARIH_VERSION, true);

    $cfg = [
        'id'      => $id,
        'restUrl' => esc_url_raw(rest_url('undangan/v1')),
        'target'  => harih_target_countdown($id),
        'musik'   => esc_url_raw((string) get_post_meta($id, 'musik_url', true)),
    ];
    wp_add_inline_script('undangan-js', 'window.UNDANGAN = ' . wp_json_encode($cfg) . ';', 'before');
}, 999);

// Preconnect Google Fonts hanya di halaman standalone kita.
add_filter('wp_resource_hints', function ($urls, $relation) {
    if ($relation === 'preconnect' && (is_singular('undangan') || is_page_template('page-isi-data.php') || is_page_template('page-katalog.php') || is_page_template('page-jadi-reseller.php'))) {
        $urls[] = 'https://fonts.googleapis.com';
        $urls[] = ['href' => 'https://fonts.gstatic.com', 'crossorigin'];
    }
    return $urls;
}, 10, 2);

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
    $galeri = json_decode((string) get_post_meta($id, 'galeri', true) ?: '[]', true);
    $img    = (is_array($galeri) && !empty($galeri[0]) && is_string($galeri[0]))
        ? $galeri[0]
        : harih_og_default(harih_tema_aktif($id));

    echo "\n" . '<meta property="og:type" content="website">' . "\n";
    echo '<meta property="og:site_name" content="hariH">' . "\n";
    echo '<meta property="og:locale" content="id_ID">' . "\n";
    echo '<meta property="og:title" content="' . esc_attr($title) . '">' . "\n";
    echo '<meta property="og:description" content="' . esc_attr($desc) . '">' . "\n";
    echo '<meta property="og:url" content="' . esc_url(get_permalink($id)) . '">' . "\n";
    echo '<meta property="og:image" content="' . esc_url($img) . '">' . "\n";
    echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
}, 5);

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
    if ($p = get_page_by_path('isi-data')) {
        $ids[] = (int) $p->ID;
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
        $parts['title'] = 'Undangan Digital Otomatis, Mulai Rp 99 Ribu';
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
