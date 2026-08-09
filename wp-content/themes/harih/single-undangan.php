<?php
/**
 * Template CPT `undangan` — halaman berdiri sendiri (blueprint §7).
 *
 * Tidak memakai get_header()/get_footer() Astra (undangan = halaman imersif
 * penuh), tapi wp_head()/wp_footer() TETAP dipanggil agar LiteSpeed, robots
 * noindex, OG tags, dan enqueue aset berfungsi. Semua meta dibaca server-side
 * (get_post_meta) — REST baca publik diblokir di mu-plugin.
 *
 * Tema visual = skin CSS: undangan/shared/ (kerangka) + undangan/{tema}/ (kulit).
 */

if (!defined('ABSPATH')) exit;

the_post();

$id = get_the_ID();
$m  = static fn(string $k): string => trim((string) get_post_meta($id, $k, true));

$paket   = harih_paket_aktif($id);
$plus    = $paket !== 'hemat';   // fitur Favorit ke atas (§10)
$premium = $paket === 'premium';

// Nuansa keagamaan: undangan lama (tanpa meta `nuansa`) diturunkan dari
// toggle `salam_islami` supaya tampilannya tidak berubah tanpa diminta.
$nuansa = $m('nuansa');
if ($nuansa === '' || !array_key_exists($nuansa, harih_nuansa_daftar())) {
    $nuansa = $m('salam_islami') === '0' ? 'umum' : 'islam';
}
// Pratinjau silang tema × nuansa pada undangan DEMO saja: `?nuansa=kristen`
// dst. Nuansa dan tema memang dua meta terpisah — ini membuat kemandiriannya
// bisa dilihat sendiri oleh calon pemesan, tanpa perlu membuat 21 demo.
// Dibatasi ke demo supaya undangan pelanggan tidak bisa diubah lewat URL.
if (isset($_GET['nuansa']) && $m('order_id') === 'demo') {
    $pratinjau = sanitize_key(wp_unslash($_GET['nuansa']));
    if (array_key_exists($pratinjau, harih_nuansa_daftar())) $nuansa = $pratinjau;
}
$nuansa_teks = harih_nuansa_teks($nuansa);
// Penimpa per undangan (CS) — kosong = pakai bawaan nuansa.
if ($m('salam_teks')  !== '') $nuansa_teks['salam']  = esc_html($m('salam_teks'));
if ($m('ayat_teks')   !== '') $nuansa_teks['ayat']   = $m('ayat_teks');
if ($m('ayat_sumber') !== '') $nuansa_teks['sumber'] = $m('ayat_sumber');

$galeri = json_decode($m('galeri') ?: '[]', true);
$galeri = is_array($galeri) ? array_values(array_filter(array_map('esc_url', $galeri))) : [];

$undangan = [
    'id'              => $id,
    'judul'           => get_the_title(),
    'permalink'       => get_permalink($id),
    'paket'           => $paket,
    'plus'            => $plus,
    'premium'         => $premium,
    'nama_pria'       => $m('nama_pria'),
    'nama_wanita'     => $m('nama_wanita'),
    'ortu_pria'       => $m('ortu_pria'),
    'ortu_wanita'     => $m('ortu_wanita'),
    'tanggal_akad'    => $m('tanggal_akad'),
    'waktu_akad'      => $m('waktu_akad'),
    'tanggal_resepsi' => $m('tanggal_resepsi'),
    'waktu_resepsi'   => $m('waktu_resepsi'),
    // Dipakai template countdown sebagai `data-target` (dibaca undangan.js).
    // Sumbernya sama dengan window.UNDANGAN.target — lihat harih_target_countdown().
    'target'          => harih_target_countdown($id),
    'lokasi_nama'     => $m('lokasi_nama'),
    'lokasi_alamat'   => $m('lokasi_alamat'),
    'gmaps_url'       => $m('gmaps_url'),
    'koordinat'       => $m('koordinat'),
    'lokasi_akad_nama'   => $m('lokasi_akad_nama'),
    'lokasi_akad_alamat' => $m('lokasi_akad_alamat'),
    'gmaps_akad_url'     => $m('gmaps_akad_url'),
    'koordinat_akad'     => $m('koordinat_akad'),
    'love_story'      => $m('love_story'),
    'galeri'          => $galeri,
    'galeri_tata'     => $m('galeri_tata') !== '' ? $m('galeri_tata') : 'slider',
    'cover_image'     => $galeri[0] ?? '',
    'musik_url'       => $m('musik_url'),
    'video_url'       => $m('video_url'),
    'rekening'        => $m('rekening'),
    'qris_media_url'  => $m('qris_media_url'),
    'dompet'          => $m('dompet'),
    'wa_cp'           => $m('wa_cp'),
    'anak_ke_pria'    => $m('anak_ke_pria'),
    'anak_ke_wanita'  => $m('anak_ke_wanita'),
    'ig_pria'         => $m('ig_pria'),
    'ig_wanita'       => $m('ig_wanita'),
    'dresscode'       => $m('dresscode'),
    'dresscode_warna' => $m('dresscode_warna'),
    'turut_mengundang'=> $m('turut_mengundang'),
    'alamat_kado'     => $m('alamat_kado'),
    'rundown'         => $m('rundown'),
    'catatan_lokasi'      => $m('catatan_lokasi'),
    'catatan_lokasi_akad' => $m('catatan_lokasi_akad'),
    'nuansa'          => $nuansa,
    'nuansa_teks'     => $nuansa_teks,
    // R4 — brand kaki undangan. Selalu terisi: mitra bila `mitra_id` sah,
    // hariH bila tidak. Divalidasi ulang di dalam helper, bukan di sini.
    'mitra'           => function_exists('harih_mitra_brand') ? harih_mitra_brand($id) : ['nama' => 'hariH', 'url' => home_url('/'), 'mitra' => false],
    // Dipertahankan HANYA untuk kompatibilitas: undangan lama tidak punya meta
    // `nuansa`, jadi nilainya diturunkan dari toggle Islami yang lama.
    'salam_islami'    => $nuansa === 'islam',
];
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<?php /* iOS Safari mengubah deret angka panjang jadi tautan telepon biru —
         nomor rekening & nomor HP di alamat kado jadi hampir tak terbaca di
         atas latar gelap (temuan owner 2026-08-05). Tautan WA tetap berfungsi
         karena ditulis sebagai <a> eksplisit. */ ?>
<meta name="format-detection" content="telephone=no">
<?php wp_head(); ?>
</head>
<body class="undangan-body">
<?php /* C1c — `is-locked` & `js-gate` TIDAK lagi ditulis di markup.

   Sebelumnya keduanya melekat sejak awal dan hanya JS yang bisa mencabutnya:
   satu kegagalan JS (4G putus di tengah unduhan, galat runtime, pemblokir
   skrip) = overlay layar penuh dengan tombol mati DAN halaman tak bisa
   digulir. Nol persen isi undangan terbaca — pada satu-satunya produk yang
   dilihat calon pembeli sebelum membeli.

   Script sinkron di bawah menyalakan gerbang (tanpa kedip, sama seperti
   kelas statis dulu), lalu ARLOJI PENJAGA mencabutnya bila `undangan.js`
   tidak pernah sempat memasang handler tombol.

   Kenapa memakai arloji padahal rencana awal melarangnya: keberatannya adalah
   "halaman menggulir di belakang overlay fixed = tampak hang" — dan itu benar
   bila yang dilepas HANYA kunci scroll. Di sini `js-gate` ikut dicabut,
   sehingga `.gate` kembali jadi blok biasa dalam alir dokumen dan undangan
   terbaca utuh dengan menggulir. Tanpa arloji, mustahil punya dua-duanya:
   tampil terkunci seketika (tanpa kedip) DAN pulih sendiri saat JS tak datang.
   <noscript> tidak menolong: ia hanya menangkap "skrip dimatikan", bukan
   skrip yang gagal diunduh atau melempar galat — dan justru itu kasusnya. */ ?>
<script data-no-optimize="1" data-cfasync="false">
(function () {
    var b = document.body;
    b.classList.add('is-locked', 'js-gate');
    setTimeout(function () {
        if (!b.classList.contains('js-siap')) b.classList.remove('is-locked', 'js-gate');
    }, 8000);
})();
</script>
<main class="undangan" id="top">
<?php
get_template_part('template-parts/undangan/cover', null, $undangan);
// Countdown tepat setelah halaman pembuka, SEBELUM salam (keputusan owner
// 2026-08-05). Kalimat salam yang berakhir "…pada pernikahan kami:" jadi
// langsung bersambung ke nama mempelai di bawahnya — itu inti masalahnya;
// menaruh hitung mundur di sini menyelesaikannya sekaligus memberi kait di
// layar kedua, sebelum pembaca sempat memutuskan berhenti menggulir.
get_template_part('template-parts/undangan/countdown', null, $undangan);
get_template_part('template-parts/undangan/salam', null, $undangan);
get_template_part('template-parts/undangan/mempelai', null, $undangan);
if (trim($undangan['turut_mengundang']) !== '') {
    get_template_part('template-parts/undangan/turut', null, $undangan);
}
get_template_part('template-parts/undangan/acara', null, $undangan);

if ($plus && $undangan['love_story'] !== '') {
    get_template_part('template-parts/undangan/love-story', null, $undangan);
}
if ($plus && $galeri) {
    get_template_part('template-parts/undangan/galeri', null, $undangan);
}
if ($premium && $undangan['video_url'] !== '') {
    get_template_part('template-parts/undangan/video', null, $undangan);
}
// `dompet` & `alamat_kado` ikut jadi syarat sejak 2026-08-07 (G1.8). Tanpa itu
// pasangan yang HANYA mengisi tautan dompet digital (atau hanya alamat kado)
// kehilangan seluruh section amplop — ketahuan saat menguji undangan uji yang
// tidak punya rekening maupun QRIS.
if ($plus && ($undangan['rekening'] !== '' || $undangan['qris_media_url'] !== ''
    || $undangan['dompet'] !== '' || trim($undangan['alamat_kado']) !== '')) {
    get_template_part('template-parts/undangan/amplop', null, $undangan);
}

get_template_part('template-parts/undangan/rsvp', null, $undangan);
get_template_part('template-parts/undangan/penutup', null, $undangan);
?>
</main>
<?php if ($m('order_id') === 'demo') : ?>
<?php /* Pemilih nuansa untuk halaman demo (keputusan owner 2026-08-05): calon
         pemesan bisa mencoba sendiri tiap nuansa pada tema mana pun, tanpa
         kami perlu membuat 21 undangan demo. Tidak pernah dirender di
         undangan pelanggan. */ ?>
<div class="pratinjau-nuansa" hidden>
    <label for="pilih-nuansa">Nuansa</label>
    <select id="pilih-nuansa">
        <?php foreach (harih_nuansa_daftar() as $harih_nk => $harih_nl) : ?>
        <option value="<?php echo esc_attr($harih_nk); ?>"<?php selected($harih_nk, $nuansa); ?>><?php echo esc_html($harih_nl); ?></option>
        <?php endforeach; ?>
    </select>
    <span class="pratinjau-cap">pratinjau demo</span>
</div>
<?php endif; ?>
<?php if ($undangan['musik_url'] !== '') : ?>
<audio id="undangan-audio" src="<?php echo esc_url($undangan['musik_url']); ?>" loop preload="none"></audio>
<?php $harih_musik_judul = harih_musik_library()[$undangan['musik_url']] ?? ''; ?>
<button type="button" id="music-toggle" class="music-toggle" aria-label="Putar / jeda musik"<?php echo $harih_musik_judul !== '' ? ' title="' . esc_attr($harih_musik_judul) . '"' : ''; ?> hidden><span aria-hidden="true">♪</span></button>
<?php endif; ?>
<?php wp_footer(); ?>
</body>
</html>
