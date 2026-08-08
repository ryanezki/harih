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

    // B1 (2026-08-07) — dikunci ke SLUG, bukan ID.
    //
    // Rute lama `/rsvp/(?P<id>\d+)` memakai ID post yang BERURUTAN, sementara
    // seluruh model privasi undangan bertumpu pada "slug tidak bisa ditebak".
    // Dibuktikan dengan panen sungguhan sebelum diperbaiki: `for id in 1..N`
    // mengembalikan nama tamu, pesan, kehadiran, jumlah, dan sesi untuk SEMUA
    // undangan pelanggan, tanpa autentikasi dan tanpa pernah tahu satu link pun.
    // Ironis karena file ini berjuang menutup semua jalur enumerasi lain
    // (listing wp/v2, REST search, sitemap, feed, oEmbed).
    //
    // Permintaan lama ke `/rsvp/13` tidak perlu ditangani khusus: `13` cocok
    // dengan pola slug, tidak ada undangan ber-slug itu, jadi jatuh ke 404 —
    // dan `undangan.js` memang sudah memperlakukan non-200 sebagai daftar kosong.
    register_rest_route('undangan/v1', '/rsvp/(?P<slug>[a-z0-9\-]+)', [
        'methods'             => 'GET',
        'permission_callback' => '__return_true',
        'callback'            => 'undangan_rsvp_list',
    ]);
});

/**
 * Undangan dari slug — satu-satunya pintu masuk publik ke CPT `undangan`.
 *
 * Status `publish` DITEGAKKAN DI KUERI, bukan diperiksa belakangan: masa-aktif
 * menonaktifkan undangan kedaluwarsa dengan mengubahnya jadi `draft`, dan tanpa
 * syarat ini undangan yang sudah "dimatikan" tetap menyerahkan seluruh daftar
 * tamunya.
 */
function undangan_dari_slug(string $slug): ?WP_Post {
    $slug = sanitize_title($slug);
    if ($slug === '') return null;
    $found = get_posts([
        'name'           => $slug,
        'post_type'      => 'undangan',
        'post_status'    => 'publish',
        'posts_per_page' => 1,
    ]);
    return $found ? $found[0] : null;
}

function undangan_rsvp_create(WP_REST_Request $r) {
    // Honeypot: bot mengisi field 'website' → pura-pura sukses (§6).
    if (!empty($r['website'])) return ['ok' => true];

    // B1 — slug, bukan `undangan_id`. Selain menutup enumerasi, ini menutup
    // jalur spam: dengan ID berurutan siapa pun bisa membanjiri buku tamu SEMUA
    // undangan lewat satu loop. Status `publish` ikut ditegakkan lewat
    // `undangan_dari_slug()` — sebelumnya hanya tipe post yang diperiksa,
    // sehingga undangan yang sudah kedaluwarsa (draft) masih menerima RSVP.
    $undangan = undangan_dari_slug((string) ($r['slug'] ?? ''));
    $uid   = $undangan ? $undangan->ID : 0;
    $nama  = mb_substr(sanitize_text_field((string) ($r['nama'] ?? '')), 0, 100);
    $pesan = mb_substr(sanitize_textarea_field((string) ($r['pesan'] ?? '')), 0, 1500);
    $hadir = in_array($r['hadir'] ?? '', ['hadir', 'tidak', 'ragu'], true) ? $r['hadir'] : 'ragu';
    // Kelengkapan RSVP (evaluasi 2026-08-06): jumlah tamu, sesi, nomor WA.
    // WA disimpan untuk mempelai TAPI TIDAK PERNAH dikembalikan endpoint publik.
    $jumlah = min(9, max(1, absint($r['jumlah'] ?? 1)));
    $sesi   = in_array($r['sesi'] ?? '', ['akad', 'resepsi', 'keduanya'], true) ? $r['sesi'] : 'keduanya';
    $wa     = preg_replace('/\D+/', '', (string) ($r['wa'] ?? ''));
    $wa     = $wa !== '' ? mb_substr($wa, 0, 16) : '';

    if (!$uid || $nama === '') {
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
        'meta_input'   => ['undangan_id' => $uid, 'hadir' => $hadir, 'jumlah' => $jumlah, 'sesi' => $sesi, 'wa_rsvp' => $wa],
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
    $undangan = undangan_dari_slug((string) $r['slug']);
    if (!$undangan) {
        // 404, bukan daftar kosong: daftar kosong memberi tahu penebak bahwa
        // slug-nya benar dan undangannya sekadar belum punya ucapan.
        return new WP_Error('not_found', 'Undangan tidak ditemukan.', ['status' => 404]);
    }
    $uid = $undangan->ID;

    /* U13 — `numberposts => 50` tanpa parameter halaman berarti ucapan ke-51 dan
       seterusnya TIDAK PERNAH bisa dilihat tamu mana pun. Dan karena get_posts()
       bawaannya `date DESC`, yang tampil adalah 50 TERBARU — jadi yang hilang
       justru pengirim paling awal, orang-orang terdekat yang membalas lebih
       dulu. Halaman /rekap/ memakai batas 500, jadi mempelai aman dan tamu
       tidak. Sekarang ber-offset, dengan `total` dikembalikan supaya klien bisa
       menawarkan "muat lagi" alih-alih diam. */
    $per   = 30;
    $hal   = max(1, (int) ($r['hal'] ?? 1));
    $query = new WP_Query([
        'post_type'      => 'ucapan',
        'post_status'    => 'publish',
        'posts_per_page' => $per,
        'paged'          => $hal,
        'meta_key'       => 'undangan_id',
        'meta_value'     => $uid,
        'no_found_rows'  => false,
    ]);
    $posts = $query->posts;
    $total = (int) $query->found_posts;

    // wp_insert_post meng-escape '&' dkk. di title/content (wptexturize/kses).
    // Frontend me-render via textContent (XSS-safe), jadi entity harus di-decode
    // DI SINI — tanpa ini pesan tamu tampil literal "jumlah &amp; sesi".
    $items = array_map(fn($p) => [
        'nama'   => wp_specialchars_decode($p->post_title, ENT_QUOTES),
        'pesan'  => wp_specialchars_decode($p->post_content, ENT_QUOTES),
        'hadir'  => get_post_meta($p->ID, 'hadir', true),
        'jumlah' => (int) get_post_meta($p->ID, 'jumlah', true),
        'sesi'   => get_post_meta($p->ID, 'sesi', true),
        // 'wa_rsvp' SENGAJA tidak diikutkan — data kontak tamu bukan konsumsi publik
        'waktu'  => get_the_date('d M Y H:i', $p),
    ], $posts);

    // Cache 60 dtk di LiteSpeed (T3.2). Tanpa ini SETIAP pageview undangan
    // memicu 1 hit PHP untuk memuat ucapan — mematahkan strategi cache A2.
    // Staleness 60 dtk dapat diterima untuk buku tamu; kiriman baru mem-purge
    // tag `rsvp_{id}` di atas sehingga tamu pengirim segera melihat ucapannya.
    do_action('litespeed_control_set_cacheable');
    do_action('litespeed_control_set_ttl', 60);
    do_action('litespeed_tag_add', 'rsvp_' . $uid);

    /* Bentuk respons berubah dari ARRAY POLOS jadi objek ber-`total` supaya
       klien bisa tahu masih ada berapa. Aman: `undangan.js` di-deploy bersama
       lewat HARIH_VERSION (URL aset ikut berganti), dan ia tetap menerima kedua
       bentuk sebagai jaring pengaman untuk respons yang masih ter-cache. */
    $res = rest_ensure_response([
        'items' => $items,
        'total' => $total,
        'hal'   => $hal,
        'per'   => $per,
    ]);
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

/* =========================================================================
 * Rekap RSVP harian (G1, 2026-08-07) — sumber data untuk WF-05.
 *
 * Dipanggil sekali sehari oleh n8n, BUKAN oleh publik: `edit_posts` + Basic Auth
 * Application Password yang sudah dipakai WF-02. Perhitungannya dilakukan di
 * sini karena datanya memang tinggal di sini (CPT `ucapan`); menariknya mentah
 * ke n8n berarti memindahkan ratusan baris hanya untuk dijumlahkan.
 *
 * Dikembalikan berkunci `order_id` supaya WF-05 bisa menjodohkannya langsung
 * dengan baris sheet — yang sudah memuat nomor WA PEMBELI. Sengaja bukan
 * `wa_cp`: pembeli adalah kontak yang memang sudah pernah kami hubungi (dia
 * yang membeli dan menerima pesan delivery), sementara CP undangan bisa jadi
 * orang lain yang tidak pernah menghubungi kami sama sekali.
 *
 * Hanya undangan yang PUNYA RSVP BARU 24 jam terakhir yang dikembalikan —
 * tanpa itu WF-05 akan mengirim "0 RSVP baru" tiap hari sampai acaranya lewat,
 * dan pesan yang tidak membawa kabar apa-apa adalah gangguan.
 * ========================================================================= */

add_action('rest_api_init', function () {
    register_rest_route('undangan/v1', '/rekap-harian', [
        'methods'             => 'GET',
        'permission_callback' => function () { return current_user_can('edit_posts'); },
        'callback'            => 'undangan_rekap_harian',
    ]);
});

/**
 * ⚠️ SELALU mengembalikan objek `{"data": {...}}`, tidak pernah array telanjang.
 *
 * Bukan gaya-gayaan. Node HTTP n8n MEMECAH respons array jadi satu item per
 * elemen — jadi `[]` menghasilkan NOL item, dan node berikutnya tidak dijalankan
 * sama sekali. Karena node ini duduk di tengah rantai WF-05, hari tanpa RSVP
 * baru (yaitu hampir setiap hari) akan diam-diam mematikan SELURUH reminder
 * harian: nudge belum-isi-data, pengingat H-3, dan peringatan masa aktif.
 *
 * Pembungkus `data` membuat responsnya selalu satu objek → selalu satu item.
 * Sabuk pengaman kedua ada di sisi n8n (`alwaysOutputData: true`).
 */
function undangan_rekap_harian(): array {
    $sejak = gmdate('Y-m-d H:i:s', time() - DAY_IN_SECONDS);

    // 1) Undangan mana yang dapat RSVP baru? Satu kueri, hanya ID + relasi.
    $baru = get_posts([
        'post_type'      => 'ucapan',
        'posts_per_page' => 500,
        'date_query'     => [['after' => $sejak, 'column' => 'post_date_gmt']],
        'fields'         => 'ids',
    ]);
    if (!$baru) return ['data' => new stdClass()];

    $hitung_baru = [];
    foreach ($baru as $uid) {
        $target = (int) get_post_meta($uid, 'undangan_id', true);
        if ($target) $hitung_baru[$target] = ($hitung_baru[$target] ?? 0) + 1;
    }

    // 2) Total keseluruhan per undangan — angka yang benar-benar dipakai
    //    mempelai adalah JUMLAH TAMU YANG DIBAWA, bukan jumlah pengisi form.
    //    Pemisahan akad/resepsi mengikuti halaman /rekap/ (FU.5): yang memilih
    //    "keduanya" dihitung di dua-duanya, persis angka untuk katering & kursi.
    $hasil = [];
    foreach ($hitung_baru as $undangan_id => $jml_baru) {
        $order_id = trim((string) get_post_meta($undangan_id, 'order_id', true));
        if ($order_id === '' || $order_id === 'demo') continue;

        $semua = get_posts([
            'post_type'      => 'ucapan',
            'posts_per_page' => 2000,
            'meta_key'       => 'undangan_id',
            'meta_value'     => $undangan_id,
            'fields'         => 'ids',
        ]);

        $akad = $resepsi = $responden = 0;
        foreach ($semua as $u) {
            if (get_post_meta($u, 'hadir', true) !== 'hadir') continue;
            $responden++;
            $jumlah = max(1, (int) get_post_meta($u, 'jumlah', true));
            $sesi   = (string) get_post_meta($u, 'sesi', true);
            if ($sesi === 'akad' || $sesi === 'keduanya' || $sesi === '') $akad    += $jumlah;
            if ($sesi === 'resepsi' || $sesi === 'keduanya' || $sesi === '') $resepsi += $jumlah;
        }

        $hasil[$order_id] = [
            'baru'          => $jml_baru,
            'hadir_akad'    => $akad,
            'hadir_resepsi' => $resepsi,
            'responden'     => $responden,
        ];
    }

    // (object) — bukan array. Kunci berupa order_id memang menghasilkan objek
    // JSON saat isinya ada, tapi PHP menyerialkan array kosong jadi `[]`.
    // Pemaksaan ini membuat bentuknya sama di kedua kasus.
    return ['data' => (object) $hasil];
}
