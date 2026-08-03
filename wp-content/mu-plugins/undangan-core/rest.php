<?php
/**
 * Undangan Core — REST: endpoint RSVP (§6) + privasi REST (TASKS T1.10).
 */

if (!defined('ABSPATH')) exit;

/* =========================================================================
 * RSVP — tulis & baca ucapan
 * ========================================================================= */

add_action('rest_api_init', function () {
    register_rest_route('undangan/v1', '/rsvp', [
        'methods'             => 'POST',
        'permission_callback' => '__return_true',
        'callback'            => 'undangan_rsvp_create',
    ]);

    register_rest_route('undangan/v1', '/rsvp/(?P<id>\d+)', [
        'methods'             => 'GET',
        'permission_callback' => '__return_true',
        'callback'            => 'undangan_rsvp_list',
    ]);
});

function undangan_rsvp_create(WP_REST_Request $r) {
    // Honeypot: bot mengisi field 'website' → pura-pura sukses (§6).
    if (!empty($r['website'])) return ['ok' => true];

    $uid   = absint($r['undangan_id']);
    $nama  = mb_substr(sanitize_text_field((string) ($r['nama'] ?? '')), 0, 100);
    $pesan = mb_substr(sanitize_textarea_field((string) ($r['pesan'] ?? '')), 0, 1500);
    $hadir = in_array($r['hadir'] ?? '', ['hadir', 'tidak', 'ragu'], true) ? $r['hadir'] : 'ragu';

    if (!$uid || get_post_type($uid) !== 'undangan' || $nama === '') {
        return new WP_Error('bad_request', 'Data tidak lengkap.', ['status' => 400]);
    }

    // Rate limit ramah CGNAT (T3.3): kunci per IP+undangan (bukan per IP saja —
    // operator seluler Indonesia berbagi 1 IP publik untuk ribuan pengguna).
    $ip  = $_SERVER['REMOTE_ADDR'] ?? '0';
    $key = 'rsvp_' . md5($ip . '|' . $uid);
    if (get_transient($key)) {
        return new WP_Error('too_fast', 'Terlalu cepat — coba lagi sebentar.', ['status' => 429]);
    }

    $id = wp_insert_post([
        'post_type'    => 'ucapan',
        'post_status'  => 'publish',
        'post_title'   => $nama,
        'post_content' => $pesan,
        'meta_input'   => ['undangan_id' => $uid, 'hadir' => $hadir],
    ]);

    if (!$id || is_wp_error($id)) {
        return new WP_Error('server_error', 'Gagal menyimpan, coba lagi.', ['status' => 500]);
    }

    // Set transient SETELAH sukses — validasi yang gagal tidak mengunci tamu.
    set_transient($key, 1, 15);

    // Segarkan cache daftar ucapan undangan ini (no-op bila LSCWP tidak aktif).
    do_action('litespeed_purge', 'rsvp_' . $uid);

    return ['ok' => true];
}

function undangan_rsvp_list(WP_REST_Request $r) {
    $uid = absint($r['id']);

    $posts = get_posts([
        'post_type'   => 'ucapan',
        'numberposts' => 50,
        'meta_key'    => 'undangan_id',
        'meta_value'  => $uid,
    ]);

    $items = array_map(fn($p) => [
        'nama'  => $p->post_title,
        'pesan' => $p->post_content,
        'hadir' => get_post_meta($p->ID, 'hadir', true),
        'waktu' => get_the_date('d M Y H:i', $p),
    ], $posts);

    // Cache 60 dtk di LiteSpeed (T3.2). Tanpa ini SETIAP pageview undangan
    // memicu 1 hit PHP untuk memuat ucapan — mematahkan strategi cache A2.
    // Staleness 60 dtk dapat diterima untuk buku tamu; kiriman baru mem-purge
    // tag `rsvp_{id}` di atas sehingga tamu pengirim segera melihat ucapannya.
    do_action('litespeed_control_set_cacheable');
    do_action('litespeed_control_set_ttl', 60);
    do_action('litespeed_tag_add', 'rsvp_' . $uid);

    $res = rest_ensure_response($items);
    $res->header('X-LiteSpeed-Cache-Control', 'public, max-age=60'); // fallback level server
    // Cache-Control untuk BROWSER tamu. Wajib eksplisit: setelan Browser Cache
    // LiteSpeed mengirim `public, max-age=604800` ke semua respons, sehingga tanpa
    // baris ini tamu yang membuka ulang undangan tidak melihat ucapan baru selama
    // SEMINGGU (ketahuan saat QA P2.3 — nilainya memang 604800 di produksi).
    $res->header('Cache-Control', 'public, max-age=60');
    return $res;
}

/* =========================================================================
 * Privasi REST (T1.10) — meta undangan berisi rekening, nomor WA, alamat &
 * jadwal acara. `auth_callback` register_post_meta hanya membatasi TULIS;
 * tanpa blok di bawah, GET /wp-json/wp/v2/undangan tanpa login membocorkan
 * semuanya secara massal.
 * ========================================================================= */

// 1) Blokir BACA publik wp/v2/undangan (listing & single). Frontend membaca
//    meta server-side via get_post_meta; hanya n8n (terautentikasi) yang
//    butuh endpoint ini untuk create/update.
add_filter('rest_pre_dispatch', function ($result, $server, $request) {
    if ($result !== null) return $result;
    if (!preg_match('#^/wp/v2/undangan(?:/|$)#', $request->get_route())) return $result;
    if (!in_array($request->get_method(), ['GET', 'HEAD'], true)) return $result;
    if (current_user_can('edit_posts')) return $result;
    return new WP_Error('rest_forbidden', 'Akses dibatasi.', ['status' => rest_authorization_required_code()]);
}, 10, 3);

// 2) Sabuk pengaman kedua: bila response undangan lolos lewat jalur lain
//    (_embed dsb.), buang meta-nya untuk pengguna tak berwenang.
add_filter('rest_prepare_undangan', function ($response, $post) {
    if (!current_user_can('edit_posts')) {
        $data         = $response->get_data();
        $data['meta'] = new stdClass();
        $response->set_data($data);
    }
    return $response;
}, 10, 2);

// 3) Keluarkan undangan dari endpoint search REST (membocorkan judul + URL
//    semua undangan → enumerasi).
add_filter('rest_post_search_query', function ($args) {
    if (current_user_can('edit_posts')) return $args;
    $types             = array_diff((array) ($args['post_type'] ?? []), ['undangan']);
    $args['post_type'] = $types ?: ['post'];
    return $args;
});

// 4) Keluarkan dari wp-sitemap.xml — slug acak percuma kalau semua URL
//    terdaftar rapi di sitemap.
add_filter('wp_sitemaps_post_types', function ($types) {
    unset($types['undangan']);
    return $types;
});

// 5) noindex halaman undangan (tetap bisa dibuka siapa pun yang punya link;
//    katalog & landing tetap indexable).
add_filter('wp_robots', function ($robots) {
    if (is_singular('undangan')) {
        $robots['noindex']  = true;
        $robots['nofollow'] = true;
    }
    return $robots;
});

// 6) Matikan feed & oEmbed untuk undangan (jalur enumerasi lain).
add_action('template_redirect', function () {
    if (!is_feed()) return;
    $pt     = get_query_var('post_type');
    $adalah = is_array($pt) ? in_array('undangan', $pt, true) : $pt === 'undangan';
    if ($adalah || is_singular('undangan')) {
        status_header(404);
        nocache_headers();
        exit;
    }
});

add_filter('oembed_response_data', function ($data, $post) {
    return ($post instanceof WP_Post && $post->post_type === 'undangan') ? false : $data;
}, 10, 2);
