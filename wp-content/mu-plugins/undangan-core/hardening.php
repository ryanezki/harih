<?php
/**
 * Undangan Core — hardening & efisiensi hosting (§6 poin 4, §11).
 */

if (!defined('ABSPATH')) exit;

// Hemat disk & inodes (10 GB / 200rb inodes): jangan generate turunan gambar
// berlebih — sisakan medium & large saja (§6).
add_filter('intermediate_image_sizes_advanced', function ($sizes) {
    unset($sizes['thumbnail'], $sizes['medium_large'], $sizes['1536x1536'], $sizes['2048x2048']);
    return $sizes;
});
add_filter('big_image_size_threshold', fn() => 1600);

// Matikan XML-RPC (T1.4 — versi kode; tidak bergantung setting plugin).
add_filter('xmlrpc_enabled', '__return_false');
// Filter di atas hanya menolak method ber-autentikasi — endpoint tetap balas
// 200 dan bisa disalahgunakan (pingback, probing). Tolak request-nya utuh.
add_action('init', function () {
    if (defined('XMLRPC_REQUEST') && XMLRPC_REQUEST) {
        status_header(403);
        exit('XML-RPC dinonaktifkan.');
    }
}, 0);

// Jangan bocorkan versi WordPress.
add_filter('the_generator', '__return_empty_string');

// Tutup enumerasi user via REST — username bot n8n adalah target brute force.
add_filter('rest_endpoints', function ($endpoints) {
    if (!is_user_logged_in()) {
        unset($endpoints['/wp/v2/users'], $endpoints['/wp/v2/users/(?P<id>[\d]+)']);
    }
    return $endpoints;
});

// Arsip author tidak dipakai toko ini — 404-kan (mencegah ?author=1 redirect
// yang membocorkan username di URL).
add_action('template_redirect', function () {
    if (is_author()) {
        global $wp_query;
        $wp_query->set_404();
        status_header(404);
        nocache_headers();
    }
});
