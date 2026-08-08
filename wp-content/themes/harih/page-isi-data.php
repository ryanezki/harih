<?php
/**
 * Template Name: Form Isi Data Undangan
 *
 * Dibuka dari link email/WA pasca-bayar: /isi-data/?order=123&key=a1b2…&paket=favorit
 * (blueprint §8). Token diverifikasi server-side SEBELUM output apa pun.
 *
 * - Halaman ini tidak boleh di-cache (T2.7): nocache_headers + action LSCWP di
 *   bawah; setting "Exclude URI /isi-data/" di LiteSpeed tetap dipasang manual.
 * - `paket` di URL hanya HINT TAMPILAN field — enforcement paket sesungguhnya
 *   dilakukan WF-02 dari data order (T2.18). Tanpa hint: tampilkan semua field.
 * - Submit: multipart XHR langsung ke webhook n8n (isi-data.js). Foto dikompresi
 *   client-side sebelum dikirim (T2.8).
 */

if (!defined('ABSPATH')) exit;

nocache_headers();
do_action('litespeed_control_set_nocache'); // no-op bila LSCWP tidak aktif

$order = absint($_GET['order'] ?? 0);
$key   = sanitize_text_field(wp_unslash($_GET['key'] ?? ''));

// B9 — token DICAKUP per halaman. Rumusnya terpusat di
// undangan_token_halaman(); padanannya di n8n ada di WF-01 (pembuat link ini)
// dan WF-02 node `Verifikasi Token` (pemeriksa submit form) — jangan diubah
// sepihak, ketiganya harus bergerak bersama.
$valid = undangan_token_sah($order, 'isi-data', $key);
if (!$valid) {
    wp_die(
        'Link tidak valid atau tidak lengkap. Silakan buka kembali link dari email/WhatsApp Anda, atau hubungi CS hariH.',
        'Link tidak valid',
        ['response' => 403]
    );
}

$paket_hint = sanitize_key($_GET['paket'] ?? '');
if (!in_array($paket_hint, ['hemat', 'favorit', 'premium'], true)) {
    $paket_hint = 'premium';
}
$plus    = $paket_hint !== 'hemat';
$premium = $paket_hint === 'premium';

$temas   = function_exists('undangan_get_temas') ? undangan_get_temas() : ['tema-01' => 'Tema 01'];
$musik   = harih_musik_library();
$webhook = harih_form_webhook_url();

// Satu sumber kebenaran untuk batas foto — dipakai teks di halaman DAN
// window.ISI_DATA. Sebelumnya angka 10 ditulis terpisah di keduanya, pola yang
// sudah pernah menggigit di C5 (harga di enam tempat).
$harih_max_foto = 10;
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="referrer" content="no-referrer"><?php /* T2.9 — token di URL tidak boleh bocor via header Referer */ ?>
<?php wp_head(); ?>
</head>
<body class="isidata-body">
<div class="wrap">

    <header class="isidata-header">
        <p class="brand">hariH</p>
        <h1>Lengkapi Data Undangan</h1>
        <p class="sub">Pesanan <strong>#<?php echo esc_html($order); ?></strong> · paket <strong><?php echo esc_html(ucfirst($paket_hint)); ?></strong></p>
        <p class="sub-kecil">Isi santai saja — setelah dikirim, undangan Anda terbit otomatis dalam hitungan menit.</p>
    </header>

    <?php if ($webhook === '') : ?>
        <div class="banner-warning">Formulir belum terhubung ke sistem (konfigurasi <code>N8N_FORM_WEBHOOK_URL</code> belum dipasang). Hubungi tim teknis.</div>
    <?php endif; ?>

    <div class="panel-sukses" id="panel-sukses" hidden>
        <div class="cek" aria-hidden="true">✓</div>
        <h2>Data diterima!</h2>
        <p>Undangan Anda sedang dibuat. Link undangan akan dikirim ke <strong>WhatsApp &amp; email</strong> Anda dalam ±5 menit. Halaman ini boleh ditutup.</p>
    </div>

    <form id="isi-data-form" novalidate>
        <input type="hidden" name="order" value="<?php echo esc_attr($order); ?>">
        <input type="hidden" name="key" value="<?php echo esc_attr($key); ?>">
        <input type="hidden" name="paket" value="<?php echo esc_attr($paket_hint); ?>">

        <fieldset id="form-fields">

            <section class="kartu">
                <h2>1. Pilih Tema</h2>
                <div class="tema-grid">
                    <?php $pertama = true; foreach ($temas as $tid => $label) : ?>
                    <label class="tema-card">
                        <input type="radio" name="template_id" value="<?php echo esc_attr($tid); ?>" <?php checked($pertama); ?> required>
                        <span class="tema-nama"><?php echo esc_html($label); ?></span>
                        <a class="tema-demo" href="<?php echo esc_url(home_url('/u/demo-' . $tid . '/')); ?>" target="_blank" rel="noopener">Lihat contoh ↗</a>
                    </label>
                    <?php $pertama = false; endforeach; ?>
                </div>
            </section>

            <section class="kartu">
                <h2>2. Mempelai</h2>
                <label class="field"><span>Nama mempelai pria *</span>
                    <input type="text" name="nama_pria" maxlength="100" required placeholder="mis. Raka Pratama"></label>
                <label class="field"><span>Nuansa undangan</span>
                    <select name="nuansa">
                        <?php foreach (harih_nuansa_daftar() as $harih_nk => $harih_nl) : ?>
                        <option value="<?php echo esc_attr($harih_nk); ?>"<?php selected($harih_nk, 'islam'); ?>><?php echo esc_html($harih_nl); ?></option>
                        <?php endforeach; ?>
                    </select></label>
                <p class="kartu-note">Menentukan salam pembuka, ayat/kutipan, salam penutup, dan tampil-tidaknya tanggal Hijriah. Pilih <strong>Tanpa unsur agama</strong> bila ingin undangan netral.</p>
                <label class="field"><span>Orang tua mempelai pria</span>
                    <input type="text" name="ortu_pria" maxlength="150" placeholder="mis. Bapak Hendra & Ibu Sari"></label>
                <div class="row-2">
                    <label class="field"><span>Anak ke- <em class="opsional">(mis. kedua)</em></span>
                        <input type="text" name="anak_ke_pria" maxlength="20" placeholder="kedua"></label>
                    <label class="field"><span>Instagram <em class="opsional">(tanpa @)</em></span>
                        <input type="text" name="ig_pria" maxlength="40" placeholder="username"></label>
                </div>
                <label class="field"><span>Nama mempelai wanita *</span>
                    <input type="text" name="nama_wanita" maxlength="100" required placeholder="mis. Sela Ananda"></label>
                <label class="field"><span>Orang tua mempelai wanita</span>
                    <input type="text" name="ortu_wanita" maxlength="150" placeholder="mis. Bapak Budi & Ibu Rina"></label>
                <div class="row-2">
                    <label class="field"><span>Anak ke- <em class="opsional">(mis. pertama)</em></span>
                        <input type="text" name="anak_ke_wanita" maxlength="20" placeholder="pertama"></label>
                    <label class="field"><span>Instagram <em class="opsional">(tanpa @)</em></span>
                        <input type="text" name="ig_wanita" maxlength="40" placeholder="username"></label>
                </div>
                <label class="field"><span>Turut mengundang <em class="opsional">(satu nama per baris)</em></span>
                    <textarea name="turut_mengundang" rows="3" maxlength="1200" placeholder="Keluarga Besar …&#10;Bapak/Ibu …"></textarea></label>
            </section>

            <section class="kartu">
                <h2>3. Acara</h2>
                <p class="kartu-note">Waktu ditampilkan dalam WIB.</p>
                <div class="row-2">
                    <label class="field"><span>Tanggal akad</span><input type="date" name="tanggal_akad"></label>
                    <label class="field"><span>Waktu akad</span><input type="time" name="waktu_akad"></label>
                </div>
                <label class="field"><span>Nama lokasi akad <em class="opsional">(kosongkan bila sama dengan resepsi)</em></span>
                    <input type="text" name="lokasi_akad_nama" maxlength="150" placeholder="mis. Masjid Agung"></label>
                <label class="field"><span>Alamat lokasi akad</span>
                    <textarea name="lokasi_akad_alamat" rows="2" maxlength="300" placeholder="alamat lokasi akad (bila beda)"></textarea></label>
                <label class="field"><span>Link Google Maps akad</span>
                    <input type="url" name="gmaps_akad_url" maxlength="300" inputmode="url" placeholder="https://maps.app.goo.gl/…"></label>
                <label class="field"><span>Koordinat lokasi akad <em class="opsional">(opsional)</em></span>
                    <input type="text" name="koordinat_akad" maxlength="60" inputmode="decimal" placeholder="-6.914744,107.609810"></label>
                <label class="field"><span>Catatan lokasi akad <em class="opsional">(parkir, pintu masuk)</em></span>
                    <textarea name="catatan_lokasi_akad" rows="2" maxlength="400" placeholder="Parkir di halaman masjid, masuk lewat pintu timur"></textarea></label>
                <div class="row-2">
                    <label class="field"><span>Dress code <em class="opsional">(opsional)</em></span>
                        <input type="text" name="dresscode" maxlength="120" placeholder="mis. Sage & Krem"></label>
                    <label class="field"><span>Warna dress code <em class="opsional">(hex, koma)</em></span>
                        <input type="text" name="dresscode_warna" maxlength="60" placeholder="#3f5c4f, #f4f1e7"></label>
                </div>
                <label class="field"><span>Susunan acara <em class="opsional">(per baris: 18.00 Pembukaan)</em></span>
                    <textarea name="rundown" rows="3" maxlength="1200" placeholder="18.00 Pembukaan&#10;19.00 Resepsi"></textarea></label>
                <div class="row-2">
                    <label class="field"><span>Tanggal resepsi *</span><input type="date" name="tanggal_resepsi" required></label>
                    <label class="field"><span>Waktu resepsi *</span><input type="time" name="waktu_resepsi" required></label>
                </div>
                <label class="field"><span>Nama lokasi *</span>
                    <input type="text" name="lokasi_nama" maxlength="150" required placeholder="mis. Graha Kencana"></label>
                <label class="field"><span>Alamat lokasi *</span>
                    <textarea name="lokasi_alamat" rows="2" maxlength="300" required placeholder="mis. Jl. Melati No. 12, Bandung"></textarea></label>
                <label class="field"><span>Link Google Maps</span>
                    <input type="url" name="gmaps_url" maxlength="300" inputmode="url" placeholder="https://maps.app.goo.gl/…"></label>
                <label class="field"><span>Koordinat lokasi <em class="opsional">(opsional — paling akurat)</em></span>
                    <input type="text" name="koordinat" maxlength="60" inputmode="decimal" placeholder="-6.914744,107.609810">
                    <em class="petunjuk">Di Google Maps: tekan lama titik lokasi → koordinat muncul di bawah → salin ke sini. Kalau dikosongkan, kami coba ambil sendiri dari link Maps di atas.</em></label>
                <label class="field"><span>Catatan lokasi resepsi <em class="opsional">(parkir, valet, pintu masuk)</em></span>
                    <textarea name="catatan_lokasi" rows="2" maxlength="400" placeholder="Valet tersedia di lobi utama · parkir basement P2"></textarea></label>
            </section>

            <?php if ($plus) : ?>
            <section class="kartu">
                <h2>4. Kisah Cinta <span class="paket-badge">Favorit+</span></h2>
                <label class="field"><span>Ceritakan singkat kisah kalian (opsional)</span>
                    <textarea name="love_story" id="love-story" rows="5" maxlength="2000" placeholder="2019 — Pertama bertemu di sebuah kedai kopi&#10;2023 — Lamaran di tempat yang sama&#10;2026 — Hari yang kami tunggu"></textarea></label>
                <p class="kartu-note">Dua format, pilih yang cocok — <strong>lini masa</strong> (dua baris atau lebih, tiap baris <em>Label — cerita</em>) atau <strong>paragraf</strong> biasa. Label boleh tahun, bulan, atau kata bebas: cocok untuk yang kenal bertahun-tahun maupun beberapa bulan.</p>
                <div class="kisah-contoh">
                    <button type="button" class="btn-mini" data-isi-kisah="linimasa">Contoh lini masa</button>
                    <button type="button" class="btn-mini" data-isi-kisah="paragraf">Contoh paragraf</button>
                </div>
                <p class="counter"><span id="ls-count">0</span>/2000</p>
            </section>

            <section class="kartu">
                <h2>5. Galeri Foto <span class="paket-badge">Favorit+</span></h2>
                <p class="kartu-note">Maksimal <?php echo (int) $harih_max_foto; ?> foto. Foto pertama menjadi sampul undangan <strong>dan gambar preview saat undangan dibagikan di WhatsApp</strong> — pilih yang terbaik. Foto dikompresi otomatis di perangkat Anda sebelum dikirim, jadi aman untuk kuota.</p>
                <p class="kartu-note"><strong>Pakai file asli dari fotografer.</strong> Foto yang diterima atau diteruskan lewat WhatsApp sudah dikecilkan sistem WhatsApp — hasilnya buram saat dipakai sebagai sampul.</p>
                <div class="foto-grid" id="foto-grid"></div>
                <label class="btn-file" for="input-foto">＋ Pilih foto</label>
                <input type="file" id="input-foto" accept="image/jpeg,image/png,image/webp" multiple hidden>
                <?php /* U1 — tiga elemen dengan tugas berbeda, dulu ditumpuk di satu <p>:
                         `#pesan-foto` sementara (progres kompresi, TANPA live region — ia
                         berubah tiap foto dan akan dibacakan berkali-kali) · `#foto-hitung`
                         permanen · `#tolak-foto` bertahan sampai pemilihan berikutnya. */ ?>
                <p class="pesan-file" id="pesan-foto"></p>
                <p class="foto-hitung" id="foto-hitung">0 dari <?php echo (int) $harih_max_foto; ?> foto terpilih</p>
                <ul class="pesan-tolak" id="tolak-foto" role="status" hidden></ul>
                <div class="field">
                    <span>Tata letak galeri</span>
                    <div class="pilih-tata">
                        <label class="tata-opsi">
                            <input type="radio" name="galeri_tata" value="slider" checked>
                            <span class="tata-ikon" aria-hidden="true"><i class="tt-besar"></i></span>
                            <span class="tata-nama">Geser<em>satu foto penuh per geseran</em></span>
                        </label>
                        <label class="tata-opsi">
                            <input type="radio" name="galeri_tata" value="kolase">
                            <span class="tata-ikon" aria-hidden="true"><i class="tt-a"></i><i></i><i></i><i></i><i></i></span>
                            <span class="tata-nama">Kolase<em>semua foto terlihat sekaligus</em></span>
                        </label>
                    </div>
                </div>
            </section>
            <?php endif; ?>

            <section class="kartu">
                <h2><?php echo $plus ? '6' : '4'; ?>. Musik Latar</h2>
                <?php if ($musik) : ?>
                <label class="field"><span>Pilih musik instrumental</span>
                    <select name="musik_url">
                        <option value="">Tanpa musik</option>
                        <?php foreach ($musik as $url => $label) : ?>
                            <option value="<?php echo esc_attr($url); ?>"><?php echo esc_html($label); ?></option>
                        <?php endforeach; ?>
                    </select></label>
                <?php else : ?>
                <p class="kartu-note">Pilihan musik akan ditambahkan tim kami saat proses pembuatan — atau sampaikan preferensi Anda ke CS setelah undangan jadi.</p>
                <?php endif; ?>
            </section>

            <?php if ($plus) : ?>
            <section class="kartu">
                <h2>7. Amplop Digital <span class="paket-badge">Favorit+</span></h2>
                <label class="field"><span>Rekening (satu per baris, opsional)</span>
                    <textarea name="rekening" rows="3" maxlength="300" placeholder="BCA 1234567890 a.n. Nama&#10;Mandiri 9876543210 a.n. Nama"></textarea></label>
                <label class="field"><span>Tautan dompet digital <em class="opsional">(opsional, satu per baris)</em></span>
                    <textarea name="dompet" rows="3" maxlength="500" placeholder="GoPay|https://gopay.link/…&#10;DANA|https://link.dana.id/…"></textarea>
                    <em class="petunjuk">Format: <strong>Nama|tautan</strong>. Hanya tautan resmi GoPay, DANA, OVO, ShopeePay, dan LinkAja yang diterima — tautan lain kami abaikan demi keamanan tamu Anda.</em></label>
                <label class="field"><span>Alamat kirim kado/hampers <em class="opsional">(opsional)</em></span>
                    <textarea name="alamat_kado" rows="2" maxlength="400" placeholder="Nama penerima, alamat lengkap, no. HP"></textarea></label>
                <div class="field">
                    <span>Gambar QRIS (opsional)</span>
                    <div class="foto-grid" id="qris-grid"></div>
                    <label class="btn-file" for="input-qris">＋ Pilih gambar QRIS</label>
                    <input type="file" id="input-qris" accept="image/jpeg,image/png,image/webp" hidden>
                    <p class="pesan-file" id="pesan-qris" role="status"></p>
                </div>
            </section>
            <?php endif; ?>

            <?php if ($premium) : ?>
            <section class="kartu">
                <h2>8. Video / Live Streaming <span class="paket-badge">Premium</span></h2>
                <label class="field"><span>Link video atau live streaming (opsional — bisa menyusul via CS)</span>
                    <input type="url" name="video_url" maxlength="300" inputmode="url" placeholder="https://youtube.com/…"></label>
            </section>
            <?php endif; ?>

            <section class="kartu">
                <h2><?php echo $premium ? '9' : ($plus ? '8' : '5'); ?>. Narahubung</h2>
                <label class="field"><span>Nomor WhatsApp narahubung di undangan (opsional)</span>
                    <input type="tel" name="wa_cp" maxlength="20" inputmode="tel" placeholder="08xxxxxxxxxx"></label>
            </section>

        </fieldset>

        <div class="kirim-area">
            <button type="submit" class="btn" id="btn-kirim" <?php disabled($webhook === ''); ?>>Kirim &amp; Buat Undangan</button>
            <div class="progress" id="progress" hidden>
                <div class="progress-bar" id="progress-bar"></div>
            </div>
            <p class="kirim-msg" id="kirim-msg" role="status"></p>
            <p class="bantuan">Kesulitan mengisi? <a href="<?php echo esc_url(home_url('/kontak/')); ?>" target="_blank" rel="noopener">Hubungi CS</a></p>
        </div>
    </form>

</div>

<script>
window.ISI_DATA = <?php echo wp_json_encode([
    'webhook'   => $webhook,
    'maxFoto'   => $harih_max_foto,
    'maxSizeMB' => 2,
]); ?>;
</script>
<?php wp_footer(); ?>
</body>
</html>
