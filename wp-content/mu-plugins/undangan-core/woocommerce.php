<?php
/**
 * Undangan Core — aturan WooCommerce (TASKS T1.11, T1.16–T1.17).
 * Hook Woo tidak akan pernah dipanggil bila WooCommerce nonaktif — aman.
 */

if (!defined('ABSPATH')) exit;

/**
 * Normalisasi nomor HP Indonesia → format internasional tanpa '+' (628…).
 * Dipakai di checkout dan tema (link wa.me); rumus yang sama dipakai ulang
 * di Code node n8n (T2.14) — jaga tetap sinkron.
 */
function undangan_normalize_phone(string $raw): string {
    $d = preg_replace('/\D+/', '', $raw);
    if (str_starts_with($d, '620')) $d = '62' . substr($d, 3); // ketikan "62 0812…"
    if (str_starts_with($d, '0')) {
        $d = '62' . substr($d, 1);
    } elseif (str_starts_with($d, '8')) {
        $d = '62' . $d;
    }
    return $d;
}

function undangan_is_valid_wa(string $normalized): bool {
    return (bool) preg_match('/^628\d{7,12}$/', $normalized);
}

// T1.11 — WooCommerce menonaktifkan webhook secara permanen & senyap setelah
// N delivery gagal (default 5). n8n restart beberapa menit tidak boleh
// mematikan pipeline order; jaring pengaman lain: WF rekonsiliasi (T3.12).
add_filter('woocommerce_max_webhook_delivery_failures', fn() => 25);

// T1.16 — paket undangan dijual satuan (qty 2 = bayar 2× tapi hanya dapat 1
// token/undangan). SEJAK F3.4 aturan ini DIPERLONGGAR di `cetak.php` untuk SKU
// `SATUAN-*` (à la carte memang dijual per pcs) — jangan menghapus filter ini,
// pengecualiannya dipasang di sana dengan prioritas lebih tinggi.
add_filter('woocommerce_is_sold_individually', '__return_true');

// T1.17 — 1 order = 1 paket. SEJAK F3.5 pengosongan cart dipindah ke
// `cetak.php` supaya selektif: paket saling menggantikan, item satuan
// menumpuk. Tanpa itu cetak + digital tidak mungkin satu keranjang.

// T1.17 — checkout ramping untuk produk virtual: nama + email + WhatsApp saja.
// Checkout default meminta alamat lengkap dan menjatuhkan konversi mobile.
// CATATAN: bila plugin Duitku ternyata membutuhkan field tertentu, kembalikan
// field itu di sini (verifikasi saat uji sandbox T1.18).
// SEJAK F3.6: `cetak.php` MENGEMBALIKAN field alamat (prioritas 20, jalan
// sesudah filter ini) bila keranjang memuat barang fisik. Checkout ramping
// tetap jadi jalur default produk digital.
add_filter('woocommerce_checkout_fields', function ($fields) {
    unset(
        $fields['billing']['billing_company'],
        $fields['billing']['billing_address_1'],
        $fields['billing']['billing_address_2'],
        $fields['billing']['billing_city'],
        $fields['billing']['billing_state'],
        $fields['billing']['billing_postcode'],
        $fields['billing']['billing_country'],
        $fields['order']['order_comments']
    );

    if (isset($fields['billing']['billing_last_name'])) {
        $fields['billing']['billing_last_name']['required'] = false;
    }

    // Field terpenting di seluruh checkout — undangan jadi dikirim ke nomor ini.
    $fields['billing']['billing_phone'] = array_merge($fields['billing']['billing_phone'] ?? [], [
        'label'       => 'Nomor WhatsApp',
        'placeholder' => '08xxxxxxxxxx',
        'required'    => true,
        'priority'    => 25,
        'description' => 'Link pengisian data & undangan jadi dikirim ke nomor WhatsApp ini.',
    ]);

    return $fields;
});

add_filter('default_checkout_billing_country', fn() => 'ID');

// T1.17 — validasi nomor WA saat checkout.
add_action('woocommerce_after_checkout_validation', function ($data, $errors) {
    $normalized = undangan_normalize_phone((string) ($data['billing_phone'] ?? ''));
    if (!undangan_is_valid_wa($normalized)) {
        $errors->add('billing_phone', 'Nomor WhatsApp tidak valid — gunakan format 08xxxxxxxxxx.');
    }
}, 10, 2);

// Simpan nomor dalam bentuk ternormalisasi (628…) supaya n8n/WAHA menerima
// format yang konsisten (chatId salah format = pesan hilang tanpa error).
add_action('woocommerce_checkout_create_order', function ($order) {
    $order->set_billing_phone(undangan_normalize_phone((string) $order->get_billing_phone()));
});
