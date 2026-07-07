<?php
/**
 * Undangan Core — Custom Post Type & meta (blueprint §5–§6).
 */

if (!defined('ABSPATH')) exit;

/**
 * Daftar tema yang tersedia. Satu-satunya sumber kebenaran `template_id`:
 * dipakai sebagai whitelist saat sanitasi meta DAN saat memuat aset tema di
 * theme (anti path traversal — TASKS T1.12/T2.18). Tambah tema baru di sini.
 */
function undangan_get_temas(): array {
    return [
        'tema-01' => 'Tema 01 — Botanical Elegan',
    ];
}

function undangan_sanitize_template_id($value): string {
    $value = sanitize_key((string) $value);
    return array_key_exists($value, undangan_get_temas()) ? $value : 'tema-01';
}

function undangan_sanitize_paket($value): string {
    $value = sanitize_key((string) $value);
    return in_array($value, ['hemat', 'favorit', 'premium'], true) ? $value : 'hemat';
}

/**
 * `galeri` hanya menerima JSON array berisi URL http(s), maksimal 10 item
 * (batas upload §8); disimpan ulang sebagai JSON rapi.
 */
function undangan_sanitize_galeri($value): string {
    $items = json_decode((string) $value, true);
    if (!is_array($items)) return '[]';

    $urls = [];
    foreach ($items as $item) {
        if (!is_string($item)) continue;
        $url = esc_url_raw($item, ['http', 'https']);
        if ($url === '') continue;
        $urls[] = $url;
        if (count($urls) >= 10) break;
    }
    return (string) wp_json_encode($urls);
}

add_action('init', function () {
    register_post_type('undangan', [
        'labels'              => ['name' => 'Undangan', 'singular_name' => 'Undangan'],
        'public'              => true,
        // show_in_rest dibutuhkan n8n untuk create via REST (autentikasi Application
        // Password). BACA publiknya diblokir di rest.php — jangan dilonggarkan.
        'show_in_rest'        => true,
        'rest_base'           => 'undangan',
        'supports'            => ['title', 'custom-fields'],
        'rewrite'             => ['slug' => 'u', 'with_front' => false],
        'menu_icon'           => 'dashicons-heart',
        'exclude_from_search' => true,
    ]);

    register_post_type('ucapan', [
        'labels'   => ['name' => 'Ucapan (RSVP)', 'singular_name' => 'Ucapan'],
        'public'   => false,
        'show_ui'  => true,
        'supports' => ['title', 'editor'],
    ]);
});

add_action('init', function () {
    // Sanitasi per jenis field (URL disanitasi sebagai URL, bukan teks bebas).
    $fields = [
        'paket'           => 'undangan_sanitize_paket',
        'template_id'     => 'undangan_sanitize_template_id',
        'order_id'        => 'sanitize_text_field',
        'nama_pria'       => 'sanitize_text_field',
        'nama_wanita'     => 'sanitize_text_field',
        'ortu_pria'       => 'sanitize_text_field',
        'ortu_wanita'     => 'sanitize_text_field',
        'tanggal_akad'    => 'sanitize_text_field',
        'waktu_akad'      => 'sanitize_text_field',
        'tanggal_resepsi' => 'sanitize_text_field',
        'waktu_resepsi'   => 'sanitize_text_field',
        'lokasi_nama'     => 'sanitize_text_field',
        'lokasi_alamat'   => 'sanitize_textarea_field',
        'gmaps_url'       => 'esc_url_raw',
        'love_story'      => 'sanitize_textarea_field',
        'galeri'          => 'undangan_sanitize_galeri',
        'musik_url'       => 'esc_url_raw',
        'video_url'       => 'esc_url_raw',
        'rekening'        => 'sanitize_textarea_field',
        'qris_media_url'  => 'esc_url_raw',
        'wa_cp'           => 'sanitize_text_field',
    ];

    foreach ($fields as $field => $sanitize) {
        register_post_meta('undangan', $field, [
            'type'              => 'string',
            'single'            => true,
            'show_in_rest'      => true,
            'sanitize_callback' => $sanitize,
            'auth_callback'     => function () { return current_user_can('edit_posts'); },
        ]);
    }
});
