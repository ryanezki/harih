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
        'tema-02' => 'Tema 02 — Senja Terakota',
        'tema-03' => 'Tema 03 — Langit Malam',
    ];
}

function undangan_sanitize_template_id($value): string {
    $value = sanitize_key((string) $value);
    return array_key_exists($value, undangan_get_temas()) ? $value : 'tema-01';
}

/**
 * Daftar mitra white-label (R4, inti M2). Satu-satunya sumber kebenaran
 * `mitra_id` — persis peran `undangan_get_temas()` untuk `template_id`.
 *
 * Disimpan di option `harih_mitra_brand`, bentuknya `{slug: {nama, url, wa}}`.
 * Nilainya dibersihkan SETIAP KALI dibaca, bukan hanya saat ditulis: option
 * bisa diubah dari wp-admin, WP-CLI, atau impor database, dan yang berbahaya
 * di sini adalah `url` — ia jadi tautan keluar di kaki undangan yang dibuka
 * ratusan tamu. Entri tanpa `nama` dibuang: kaki tanpa nama bukan white-label,
 * cuma tautan misterius.
 *
 * Sengaja slug, bukan user ID: role `harih_mitra` baru lahir di M1, sementara
 * R4 harus jalan sekarang untuk dipakai menawar. Saat M1/M7 membuat user
 * mitra sungguhan, `user_nicename`-nya bisa langsung jadi slug di sini —
 * bentuk metanya tidak perlu berubah.
 */
function undangan_get_mitra(): array {
    $bersih = [];
    foreach ((array) get_option('harih_mitra_brand', []) as $slug => $m) {
        $slug = sanitize_key((string) $slug);
        if ($slug === '' || !is_array($m)) continue;

        $nama = sanitize_text_field((string) ($m['nama'] ?? ''));
        if ($nama === '') continue;

        $url = esc_url_raw((string) ($m['url'] ?? ''), ['http', 'https']);
        $wa  = preg_replace('/\D+/', '', (string) ($m['wa'] ?? ''));

        $bersih[$slug] = ['nama' => $nama, 'url' => $url, 'wa' => (string) $wa];
    }
    return $bersih;
}

/** `mitra_id` di luar daftar → '' (jatuh ke brand hariH), bukan disimpan apa adanya. */
function undangan_sanitize_mitra_id($value): string {
    $value = sanitize_key((string) $value);
    return array_key_exists($value, undangan_get_mitra()) ? $value : '';
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

/**
 * `musik_url` hanya boleh berisi track dari pustaka kita sendiri (P0.6).
 * Nilainya datang dari form pelanggan dan berakhir sebagai `<audio src>` di
 * halaman undangan — tanpa whitelist, siapa pun yang memegang token form bisa
 * menyuntikkan URL audio eksternal ke halaman yang dibagikan ke ratusan tamu.
 * Pustakanya hidup di tema (`harih_musik_library()`), yang sudah termuat saat
 * sanitasi berjalan pada request REST dari WF-02.
 */
function undangan_sanitize_musik_url($value): string {
    $url = esc_url_raw((string) $value, ['http', 'https']);
    if ($url === '') return '';
    // Tema belum termuat (konteks CLI tertentu): jangan buang data yang sudah ada —
    // jalur yang perlu dijaga (REST WF-02) selalu punya tema termuat.
    if (!function_exists('harih_musik_library')) return $url;
    return array_key_exists($url, harih_musik_library()) ? $url : '';
}

/**
 * `galeri_tata` — tata letak galeri: `slider` (bawaan, geser satu per satu) atau
 * `kolase` (grid mozaik, semua foto terlihat sekaligus). Nilai di luar keduanya
 * jatuh ke `slider`, sehingga undangan lama tidak berubah tampilan tanpa diminta.
 */
function undangan_sanitize_galeri_tata($value): string {
    $v = sanitize_key((string) $value);
    return in_array($v, ['slider', 'kolase'], true) ? $v : 'slider';
}

/**
 * `koordinat` / `koordinat_akad` — "lat,lng" ternormalisasi, atau '' bila tidak
 * masuk akal. Dipakai peta tersemat & tombol Waze; nilai sampah di sini berarti
 * TAMU DIARAHKAN KE TEMPAT YANG SALAH, jadi rentangnya divalidasi, bukan cuma
 * bentuknya.
 */
function undangan_sanitize_koordinat($value): string {
    $v = trim((string) $value);
    if ($v === '') return '';
    if (!preg_match('/^(-?\d{1,3}(?:\.\d+)?)\s*,\s*(-?\d{1,3}(?:\.\d+)?)$/', $v, $m)) return '';

    $lat = (float) $m[1];
    $lng = (float) $m[2];
    if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) return '';
    // 0,0 = "Null Island" — hampir selalu hasil parsing gagal, bukan lokasi nyata.
    if ($lat === 0.0 && $lng === 0.0) return '';

    return $m[1] . ',' . $m[2];
}

/**
 * Ambil "lat,lng" dari URL Google Maps yang SUDAH panjang. Tidak melakukan
 * permintaan jaringan apa pun.
 */
function undangan_koordinat_dari_url(string $url): string {
    // Diurutkan dari yang paling menunjuk TITIK TEMPAT ke yang paling menunjuk
    // titik tengah peta:
    //   1. `!3d..!4d..`  — koordinat pin tempatnya sendiri (paling tepat)
    //   2. `@lat,lng,17z` — titik tengah peta saat URL disalin dari peramban
    //   3. `?q=` / `query=` / `ll=` / `daddr=` — koordinat yang ditulis eksplisit
    //      di query. Bentuk inilah yang muncul setelah short link diselesaikan
    //      (`maps.app.goo.gl` → `google.com/maps?q=lat,lng`) — sempat terlewat
    //      dan baru ketahuan saat menguji jalur jaringannya sungguhan.
    // Semua pola menuntut DUA angka berdesimal, jadi `?q=Jalan Melati, Bandung`
    // tidak pernah salah tertangkap.
    $pola = [
        '~!3d(-?\d{1,3}\.\d+)!4d(-?\d{1,3}\.\d+)~',
        '~[@/](-?\d{1,3}\.\d+),(-?\d{1,3}\.\d+)~',
        '~[?&](?:q|query|ll|sll|daddr|center|destination)=(-?\d{1,3}\.\d+),\s*(-?\d{1,3}\.\d+)~i',
    ];
    foreach ($pola as $p) {
        if (preg_match($p, $url, $m)) {
            $koord = undangan_sanitize_koordinat($m[1] . ',' . $m[2]);
            if ($koord !== '') return $koord;
        }
    }
    return '';
}

/**
 * Isi `koordinat` otomatis dari `gmaps_url` — sekali, saat undangan dibuat.
 *
 * KENAPA DI SINI, bukan di WF-02: menyelesaikan short link butuh satu permintaan
 * HTTP mengikuti redirect. Menaruhnya di workflow berarti membedah struktur node
 * yang bercabang (risiko tinggi, dan tiap perubahan menuntut ritual
 * import→publish→restart). Menaruhnya di render halaman berarti tamu pertama
 * menunggu permintaan ke Google. Di sini ia berjalan tepat sekali, di dalam
 * permintaan REST yang dibuat WF-02 — yang webhook-nya sudah dibalas 200 lebih
 * dulu, jadi pemesan tidak menunggu.
 *
 * Tidak pernah fatal: gagal apa pun → `koordinat` tetap kosong dan peta kembali
 * memakai kueri nama+alamat seperti sebelumnya. Pemesan juga tetap bisa mengisi
 * koordinatnya sendiri di form, dan nilai yang sudah ada TIDAK PERNAH ditimpa.
 */
function undangan_isi_koordinat(int $post_id, string $meta_url, string $meta_koord): void {
    if (get_post_type($post_id) !== 'undangan') return;
    if (trim((string) get_post_meta($post_id, $meta_koord, true)) !== '') return;   // sudah ada → hormati

    $url = trim((string) get_post_meta($post_id, $meta_url, true));
    if ($url === '') return;

    // URL panjang sudah memuat koordinatnya — tanpa permintaan jaringan.
    $koord = undangan_koordinat_dari_url($url);

    if ($koord === '') {
        $host = strtolower((string) wp_parse_url($url, PHP_URL_HOST));
        // Hanya pemendek Google yang diikuti. Mengikuti redirect URL sembarangan
        // yang datang dari form = SSRF; daftar ini yang menahannya.
        if (!in_array($host, ['maps.app.goo.gl', 'goo.gl', 'maps.google.com', 'www.google.com'], true)) return;

        $res = wp_remote_get($url, [
            'timeout'     => 5,
            'redirection' => 5,
            'user-agent'  => 'Mozilla/5.0 (compatible; hariH/1.0)',
        ]);
        if (is_wp_error($res)) return;

        // WP menyimpan riwayat redirect; URL terakhir yang dipakai ada di
        // `http_response`. Bila tidak tersedia, coba tarik dari body.
        $akhir = '';
        $obj   = $res['http_response'] ?? null;
        if ($obj && method_exists($obj, 'get_response_object')) {
            $akhir = (string) ($obj->get_response_object()->url ?? '');
        }
        $koord = $akhir !== '' ? undangan_koordinat_dari_url($akhir) : '';
        if ($koord === '') $koord = undangan_koordinat_dari_url((string) wp_remote_retrieve_body($res));
    }

    if ($koord !== '') update_post_meta($post_id, $meta_koord, $koord);
}

add_action('added_post_meta', function ($mid, $post_id, $key) {
    if ($key === 'gmaps_url')      undangan_isi_koordinat((int) $post_id, 'gmaps_url', 'koordinat');
    if ($key === 'gmaps_akad_url') undangan_isi_koordinat((int) $post_id, 'gmaps_akad_url', 'koordinat_akad');
}, 10, 3);

/**
 * Host dompet digital yang boleh jadi tautan di amplop.
 *
 * WHITELIST, bukan blacklist — disiplin yang sama dengan `musik_url`. Nilai ini
 * datang dari form pemesan dan berakhir sebagai `<a href>` di halaman yang
 * disebar ke ratusan tamu: tanpa penyaringan, satu tautan salah mengubah
 * undangan jadi open redirect di halaman yang justru dipercaya orang.
 * Menambah penyedia cukup di sini.
 */
function undangan_dompet_host(): array {
    return [
        'gopay.co.id', 'gojek.link', 'gopay.link', 'gojek.com',
        'link.dana.id', 'm.dana.id', 'dana.id',
        'ovo.id', 'link.ovo.id',
        'shopee.co.id', 's.shopee.co.id', 'shp.ee',
        'linkaja.id', 'link.linkaja.id',
    ];
}

/**
 * `dompet` — satu per baris, `Nama|URL` (label boleh dikosongkan: `URL` saja,
 * labelnya diturunkan dari host). Maksimal 5 baris; baris yang host-nya di luar
 * whitelist DIBUANG DIAM-DIAM, sama seperti perlakuan `musik_url`.
 */
function undangan_sanitize_dompet($value): string {
    $keluar = [];
    $host_ok = undangan_dompet_host();

    foreach (array_filter(array_map('trim', explode("\n", (string) $value))) as $baris) {
        $bagian = explode('|', $baris, 2);
        $label  = count($bagian) === 2 ? sanitize_text_field(trim($bagian[0])) : '';
        $url    = esc_url_raw(trim(end($bagian)), ['https']);   // https saja
        if ($url === '') continue;

        $host = strtolower((string) wp_parse_url($url, PHP_URL_HOST));
        $host = preg_replace('/^www\./', '', $host);
        if (!in_array($host, $host_ok, true)) continue;

        if ($label === '') $label = ucfirst(explode('.', $host)[0]);
        $keluar[] = mb_substr($label, 0, 24) . '|' . $url;
        if (count($keluar) >= 5) break;
    }

    return implode("\n", $keluar);
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
        // Lokasi AKAD terpisah — di Indonesia akad & resepsi lazim beda tempat.
        // Kosong = kartu akad tampil tanpa blok lokasi (tidak menebak-nebak).
        'lokasi_akad_nama'   => 'sanitize_text_field',
        'lokasi_akad_alamat' => 'sanitize_textarea_field',
        'gmaps_akad_url'     => 'esc_url_raw',
        'love_story'      => 'sanitize_textarea_field',
        'galeri'          => 'undangan_sanitize_galeri',
        'musik_url'       => 'undangan_sanitize_musik_url',
        'video_url'       => 'esc_url_raw',
        'rekening'        => 'sanitize_textarea_field',
        'qris_media_url'  => 'esc_url_raw',
        'wa_cp'           => 'sanitize_text_field',
        // Konvensi undangan Indonesia (evaluasi 2026-08-06):
        'anak_ke_pria'    => 'sanitize_text_field',   // "kedua" → "Putra kedua dari…"
        'anak_ke_wanita'  => 'sanitize_text_field',
        'ig_pria'         => 'sanitize_user',          // username Instagram tanpa @
        'ig_wanita'       => 'sanitize_user',
        'dresscode'       => 'sanitize_text_field',
        'dresscode_warna' => 'sanitize_text_field',    // hex dipisah koma; divalidasi saat render
        'turut_mengundang'=> 'sanitize_textarea_field',// satu nama per baris
        'alamat_kado'     => 'sanitize_textarea_field',// alamat kirim hampers
        'rundown'         => 'sanitize_textarea_field',// "18.00 Pembukaan" per baris
        'catatan_lokasi'      => 'sanitize_textarea_field', // parkir/valet/pintu masuk — resepsi
    'catatan_lokasi_akad' => 'sanitize_textarea_field', // idem — venue akad
    'daftar_tamu'     => 'sanitize_textarea_field', // satu nama per baris — amplop bernama + link personal
    'nuansa'          => 'sanitize_key',            // islam|kristen|katolik|hindu|buddha|konghucu|umum
    'salam_teks'      => 'sanitize_text_field',     // penimpa opsional (CS)
    'ayat_teks'       => 'sanitize_textarea_field', // idem
    'ayat_sumber'     => 'sanitize_text_field',     // idem
    'salam_islami'    => 'sanitize_text_field',    // '' / '1' (default tampil) · '0' = sembunyikan (toggle CS)
    // --- G1 (2026-08-07). Keempatnya diperkenalkan SEKALIGUS supaya WF-02
    //     cukup disentuh satu kali; ritual import→publish→restart itu mahal. ---
    'galeri_tata'     => 'undangan_sanitize_galeri_tata', // slider (bawaan) | kolase
    'koordinat'       => 'undangan_sanitize_koordinat',   // "lat,lng" venue resepsi
    'koordinat_akad'  => 'undangan_sanitize_koordinat',   // idem — venue akad
    'dompet'          => 'undangan_sanitize_dompet',      // "Nama|URL" per baris, host di-whitelist
    // --- R4 (2026-08-09), inti M2 dimajukan. 45 → 46 meta. ---
        'mitra_id'        => 'undangan_sanitize_mitra_id',    // slug di undangan_get_mitra(); '' = brand hariH
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
