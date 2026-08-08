<?php
/**
 * Template Name: Daftar Tamu hariH
 *
 * Halaman bertoken tempat pemesan menempel daftar nama tamu (TASKS FU.6 +
 * prasyarat amplop bernama).
 *
 * KENAPA SATU DAFTAR INI PENTING: ia melayani tiga hal sekaligus yang selama
 * ini terpisah —
 *   1. **amplop bernama tamu** yang jadi pembeda paket cetak. Premis lamanya
 *      keliru dan sempat menular ke salinan penjualan: percetakan konvensional
 *      justru sering memberi label nama GRATIS. Pembeda kami bukan harganya,
 *      melainkan namanya **dicetak langsung pada amplop** — bukan stiker yang
 *      ditempel — dan itu hanya mungkin karena datanya sudah ada di sini;
 *   2. **link personal `?to=`** yang selama ini harus disalin satu per satu —
 *      300 tamu berarti 300 salin-tempel, pekerjaan paling menjemukan bagi
 *      pemesan;
 *   3. kelak QR check-in per tamu (backlog FU.7).
 * Membangunnya sekali menutup ketiganya.
 *
 * Token HMAC BERCAKUP `tamu` (B9) — token halaman lain tidak membukanya,
 * dan sebaliknya. Rumusnya di undangan_token_halaman().
 */

if (!defined('ABSPATH')) exit;

// Sama seperti /isi-data/: halaman bertoken tidak boleh di-cache. Tanpa ini
// LiteSpeed menyajikan HTML lama berhari-hari, dan nonce anonim yang hanya sah
// ≤24 jam sudah mati saat pemesan menekan Simpan.
nocache_headers();
do_action('litespeed_control_set_nocache'); // no-op bila LSCWP tidak aktif

$order_id = absint($_GET['order'] ?? 0);
$key      = sanitize_text_field((string) ($_GET['key'] ?? ''));

// B9 — token DICAKUP per halaman: token /tamu/ tidak membuka halaman lain.
// Rumusnya terpusat di undangan_token_halaman() (mu-plugins/undangan-core).
$sah = undangan_token_sah($order_id, 'tamu', $key);
$order = $sah && function_exists('wc_get_order') ? wc_get_order($order_id) : null;
if (!$order) {
    wp_die('Link daftar tamu tidak valid. Hubungi CS bila kamu merasa ini keliru.', 'Link tidak valid', ['response' => 403]);
}

$undangan_id = function_exists('undangan_cari_undangan_order') ? undangan_cari_undangan_order($order_id) : 0;

/** Jatah amplop bernama sesuai paket cetak yang dibeli. */
$harih_jatah = 0;
$JATAH = [
    'CETAK-HORMAT' => 50, 'CETAK-RESEPSI' => 100, 'CETAK-GRAND' => 150,
    'UPG-HORMAT'   => 50, 'UPG-RESEPSI'   => 100, 'UPG-GRAND'   => 150,
    'SATUAN-UNDANGAN-LIPAT' => 0, // jumlahnya = kuantitas yang dipesan
];
foreach ($order->get_items() as $item) {
    $p   = $item->get_product();
    $sku = $p ? strtoupper((string) $p->get_sku()) : '';
    if (isset($JATAH[$sku])) {
        $harih_jatah += $sku === 'SATUAN-UNDANGAN-LIPAT' ? (int) $item->get_quantity() : $JATAH[$sku];
    }
}

$pesan   = '';
$galat   = '';
$kembali = ''; // ketikan pemesan, dikembalikan ke textarea bila simpan gagal

/* U5 — batas nama. Angkanya dipakai server DAN tampil di layar sejak awal;
   sebelumnya ia hanya hidup di dalam array_slice() dan tidak pernah sampai ke
   orang yang mengetiknya (di-grep ke seluruh tema + mu-plugin: nol string yang
   pernah dirender). Nilainya juga sudah tayang di Kebijakan Privasi. */
const HARIH_MAKS_TAMU = 600;

/* U6 — status proof dibaca untuk KEPERLUAN TAMPILAN, bukan cuma untuk menulis
   catatan order. Sebelumnya `_proof_disetujui` hanya muncul satu kali di berkas
   ini, di dalam cabang add_order_note() yang tidak pernah dilihat pemesan. */
$harih_proof_disetujui = (string) $order->get_meta('_proof_disetujui');

if (($_POST['harih_simpan_tamu'] ?? '') === '1') {
    $isi = sanitize_textarea_field(wp_unslash((string) ($_POST['daftar_tamu'] ?? '')));

    // Ketiga syarat DIPISAH, bukan digabung jadi satu `if`. Sebelumnya gabungan
    // itu membuat setiap kegagalan berakhir SENYAP: pemesan menempel 300 nama,
    // menekan Simpan, halaman memuat ulang seolah tidak terjadi apa-apa, dan
    // seluruh ketikannya hilang. Nonce mati bukan kasus tepi di sini — nonce
    // anonim hanya sah ≤24 jam sementara halaman ini sempat disajikan dari
    // cache berhari-hari (lihat nocache_headers di atas).
    if (!wp_verify_nonce((string) ($_POST['_harih_nonce'] ?? ''), 'harih_tamu_' . $order_id)) {
        $galat   = 'Sesi halaman ini sudah kedaluwarsa — muat ulang halaman lalu tekan Simpan sekali lagi. Daftar yang kamu ketik masih ada di bawah, tidak hilang.';
        $kembali = $isi;
    } elseif (!$undangan_id) {
        $galat   = 'Undangan untuk pesanan ini belum terbit, jadi daftar tamu belum bisa disimpan. Isi data undanganmu dulu — atau hubungi CS bila kamu merasa ini keliru.';
        $kembali = $isi;
    } else {
        /* U5 — PEMBERSIHAN & PEMERIKSAAN, bukan pemotongan senyap.
           Yang lama: array_slice(…, 0, 600) memotong lalu count() menghitung
           SESUDAHNYA, sehingga 650 nama tersimpan 600 dan dilaporkan
           "Tersimpan — 600 nama tamu.". Komentar kodenya sendiri sudah menyebut
           salah-tempel multi-kolom sebagai sebab, tapi tidak pernah
           menanganinya. */
        $mentah = array_values(array_filter(array_map('trim', explode("\n", $isi)), static function ($s) {
            return $s !== '';
        }));

        // Tempelan dari Excel/Sheets membawa kolom lain dipisah TAB (atau titik
        // koma). Koma sengaja TIDAK dipakai sebagai pemisah — nama Indonesia
        // sering memuatnya secara sah ("Ahmad Fauzi, S.T.") dan memenggalnya
        // justru merusak data yang benar.
        $ada_kolom = false;
        $bersih = [];
        foreach ($mentah as $b) {
            if (strpbrk($b, "\t;") !== false) {
                $ada_kolom = true;
                // preg_split, bukan strtok: strtok menyimpan STATE global antar
                // pemanggilan dan mudah salah pakai di dalam loop.
                $b = trim(preg_split('/[\t;]/', $b, 2)[0]);
            }
            if ($b !== '') $bersih[] = $b;
        }

        // Duplikat: dibandingkan tanpa peduli huruf besar/kecil dan spasi ganda,
        // yang pertama dipertahankan supaya urutan pemesan tidak berubah.
        $terlihat = [];
        $unik = [];
        $ganda = [];
        foreach ($bersih as $b) {
            $kunci = mb_strtolower(preg_replace('/\s+/u', ' ', $b));
            if (isset($terlihat[$kunci])) { $ganda[] = $b; continue; }
            $terlihat[$kunci] = true;
            $unik[] = $b;
        }

        if (count($unik) > HARIH_MAKS_TAMU) {
            // Kelebihan TIDAK dibuang diam-diam: seluruh ketikan dikembalikan
            // dan tidak ada yang disimpan, memakai pola $kembali yang sudah
            // dipakai cabang nonce di atas. Menyimpan separuh diam-diam adalah
            // kerusakan yang tidak bisa dilihat pemesan sampai amplop tercetak.
            $galat = sprintf(
                'Daftar berisi %d nama — batas kami %d. Tidak ada yang disimpan supaya tidak ada nama yang hilang diam-diam. Kurangi %d nama lalu tekan Simpan lagi; ketikanmu masih utuh di bawah.',
                count($unik),
                HARIH_MAKS_TAMU,
                count($unik) - HARIH_MAKS_TAMU
            );
            $kembali = $isi;
        } else {
            $sebelum = (string) get_post_meta($undangan_id, 'daftar_tamu', true);
            $sesudah = implode("\n", $unik);
            update_post_meta($undangan_id, 'daftar_tamu', $sesudah);

            $pesan = sprintf('Tersimpan — %d nama tamu.', count($unik));
            if ($ganda) {
                $contoh = array_slice(array_unique($ganda), 0, 3);
                $pesan .= sprintf(
                    ' %d nama ganda digabung (%s%s).',
                    count($ganda),
                    implode(', ', $contoh),
                    count(array_unique($ganda)) > 3 ? ', …' : ''
                );
            }
            if ($ada_kolom) {
                $pesan .= ' Beberapa baris berisi lebih dari satu kolom — kami pakai kolom pertamanya saja.';
            }

            /* C6 — daftar tamu kini ikut dibekukan ke snapshot proof. Perubahan
               SETELAH proof disetujui sengaja TIDAK dilarang: pemesan yang baru
               menemukan satu nama salah ketik akan terjebak, dan CS-nya cuma satu
               orang. Yang dilakukan: mencatatnya di order supaya sebelum masuk
               mesin ketahuan bahwa yang dicetak mungkin bukan yang disetujui. */
            if ($sebelum !== $sesudah && $harih_proof_disetujui) {
                $order->add_order_note(sprintf(
                    'Daftar tamu DIUBAH pemesan setelah proof disetujui — sekarang %d nama (sebelumnya %d). Snapshot yang disetujui tidak ikut berubah; pastikan versi mana yang dicetak sebelum produksi.',
                    count($unik),
                    count(array_filter(array_map('trim', explode("\n", $sebelum))))
                ));
                $order->save();

                /* U6 — dan pemesan IKUT DIBERI TAHU. Sebelumnya peringatan ini
                   hanya masuk catatan order yang cuma terlihat admin, sementara
                   layar pemesan tetap berbunyi "Tersimpan" polos. */
                $pesan .= ' ⚠️ Proof pesanan ini sudah kamu setujui, jadi perubahan ini TIDAK otomatis ikut tercetak — beri tahu kami lewat WhatsApp supaya kami cek sebelum masuk mesin.';
            }
        }
    }
}

$daftar = $undangan_id ? (string) get_post_meta($undangan_id, 'daftar_tamu', true) : '';
$nama   = array_values(array_filter(array_map('trim', explode("\n", $daftar))));
$link   = $undangan_id ? get_permalink($undangan_id) : '';
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="robots" content="noindex, nofollow">
<meta name="referrer" content="no-referrer">
<?php wp_head(); ?>
</head>
<body class="katalog-body tamu-body">

<header class="hero hero-ringkas">
    <p class="brand">hariH</p>
    <h1>Daftar tamu</h1>
    <p class="hero-sub">Tempel nama tamu di sini — satu nama per baris. Dari daftar yang sama kami mencetak <strong>nama di amplop</strong>, dan kamu langsung mendapat <strong>link undangan personal</strong> untuk tiap tamu.</p>
</header>

<main>
    <?php /* Di ATAS penjaga $undangan_id: salah satu cabang galat justru menyala
             ketika undangan belum ada, dan pesannya tidak boleh ikut tersembunyi. */ ?>
    <?php if ($galat) : ?><p class="proof-notif proof-galat"><?php echo esc_html($galat); ?></p><?php endif; ?>

    <?php if (!$undangan_id) : ?>
        <p class="satuan-kosong">Undangan kamu belum dibuat. Isi dulu data undangan lewat link yang kami kirim, lalu buka halaman ini lagi.</p>
    <?php else : ?>

    <?php if ($pesan) : ?><p class="proof-notif"><?php echo esc_html($pesan); ?></p><?php endif; ?>

    <?php /* U6 — spanduk tetap, bukan hanya pesan sesaat setelah menyimpan.
             Pemesan yang membuka halaman ini seminggu setelah menyetujui proof
             harus tahu taruhannya SEBELUM ia mengetik, bukan sesudah. */ ?>
    <?php if ($harih_proof_disetujui) : ?>
    <p class="proof-notif proof-galat">
        Proof pesanan ini sudah kamu setujui pada <strong><?php echo esc_html(wp_date('j F Y', strtotime($harih_proof_disetujui))); ?></strong>.
        Perubahan daftar di sini <strong>tidak otomatis ikut tercetak</strong> —
        <a href="<?php echo esc_url(harih_wa_link("Halo hariH, saya mengubah daftar tamu pesanan #{$order_id} setelah proof disetujui.")); ?>" target="_blank" rel="noopener">beri tahu kami lewat WhatsApp</a>
        supaya kami cek sebelum masuk mesin.
    </p>
    <?php endif; ?>

    <section class="tamu-form">
        <form method="post">
            <?php wp_nonce_field('harih_tamu_' . $order_id, '_harih_nonce'); ?>
            <input type="hidden" name="harih_simpan_tamu" value="1">
            <label class="field">
                <span>Nama tamu <em class="opsional">(satu nama per baris)</em></span>
                <textarea name="daftar_tamu" id="daftar-tamu" rows="10" placeholder="Bapak Hendra &amp; Ibu Sari&#10;Keluarga Besar Wicaksono&#10;Damar Prasetyo"><?php echo esc_textarea($kembali !== '' ? $kembali : $daftar); ?></textarea>
            </label>
            <p class="tamu-hitung">
                <?php /* U5 — batasnya tampil sejak awal, bukan baru diketahui
                         setelah nama ke-601 hilang. */ ?>
                <span id="tamu-jumlah"><?php echo esc_html(count($nama)); ?></span> / <?php echo esc_html(HARIH_MAKS_TAMU); ?> nama
                <span id="tamu-belum-simpan" class="tamu-belum" hidden>· <strong>belum disimpan</strong></span>
                <?php if ($harih_jatah) : ?>
                    · jatah amplop bernama paketmu: <strong><?php echo esc_html($harih_jatah); ?></strong>
                    <span id="tamu-lebih" class="tamu-lebih"<?php echo count($nama) > $harih_jatah ? '' : ' hidden'; ?>>— kelebihan dicetak sebagai amplop polos, atau tambah lewat item satuan</span>
                <?php endif; ?>
                <span id="tamu-lewat-batas" class="tamu-galat" hidden>— melewati batas <?php echo esc_html(HARIH_MAKS_TAMU); ?>; kurangi dulu, kalau tidak Simpan akan ditolak</span>
            </p>
            <button type="submit" class="btn btn-utama btn-blok">Simpan daftar tamu</button>
        </form>
    </section>

    <?php if ($nama) : ?>
    <section class="tamu-link">
        <h2>Link personal tiap tamu</h2>
        <p>Nama tamu muncul otomatis di halaman pembuka undangan. Salin semuanya sekaligus, lalu tempel ke aplikasi pengirim pesanmu — tidak perlu menyalin satu per satu.</p>
        <div class="tamu-aksi">
            <button type="button" class="btn btn-garis" id="salin-semua">Salin semua link</button>
            <button type="button" class="btn btn-garis" id="unduh-csv">Unduh CSV</button>
            <a class="btn btn-garis" href="<?php echo esc_url(add_query_arg(['order' => $order_id, 'key' => undangan_token_halaman($order_id, 'rekap')], home_url('/rekap/'))); ?>">Rekap kehadiran</a>
        </div>
        <?php /* U6 — kedua tombol ekspor membaca TEXTAREA (daftar hidup),
                 sementara amplop dicetak dari data TERSIMPAN. Selama keduanya
                 berbeda, ekspor dihentikan dengan sebab yang jelas alih-alih
                 diam-diam menghasilkan berkas yang tidak sama dengan cetakan. */ ?>
        <p class="tamu-galat" id="tamu-peringatan-ekspor" hidden>Daftar di layar belum disimpan, jadi belum sama dengan yang akan dicetak. Tekan <strong>Simpan daftar tamu</strong> dulu.</p>
        <ul class="tamu-daftar" id="tamu-daftar" data-link="<?php echo esc_attr($link); ?>">
            <?php foreach (array_slice($nama, 0, 300) as $n) : ?>
            <li>
                <span class="tamu-nama"><?php echo esc_html($n); ?></span>
                <code class="tamu-url"><?php echo esc_html(add_query_arg('to', rawurlencode($n), $link)); ?></code>
            </li>
            <?php endforeach; ?>
        </ul>
        <?php if (count($nama) > 300) : ?>
            <p class="tamu-catatan">Menampilkan 300 pertama dari <?php echo esc_html(count($nama)); ?> nama — tombol salin &amp; CSV tetap memuat seluruhnya.</p>
        <?php endif; ?>
    </section>
    <?php endif; ?>

    <?php endif; ?>
</main>

<footer class="kaki">
    <p class="brand">hariH</p>
    <p class="kaki-tag">Pesanan #<?php echo esc_html($order_id); ?></p>
    <nav class="kaki-nav">
        <a href="<?php echo esc_url(home_url('/kontak/')); ?>">Kontak</a>
    </nav>
</footer>

<script>
(function () {
    'use strict';
    var ta = document.getElementById('daftar-tamu');
    var jumlah = document.getElementById('tamu-jumlah');
    var lebih = document.getElementById('tamu-lebih');
    var lewat = document.getElementById('tamu-lewat-batas');
    var belumSimpan = document.getElementById('tamu-belum-simpan');
    var peringatanEkspor = document.getElementById('tamu-peringatan-ekspor');
    var jatah = <?php echo (int) $harih_jatah; ?>;
    var maks = <?php echo (int) HARIH_MAKS_TAMU; ?>;

    /* U6 — `kotor` menandai bahwa isi textarea sudah menyimpang dari yang
       TERSIMPAN. Selama itu, tiga hal di halaman ini bisa saling bertentangan:
       penghitung & ekspor membaca textarea, sementara daftar di bawah dan
       AMPLOP YANG DICETAK berasal dari data tersimpan. Pola `kotor` +
       beforeunload diambil dari isi-data.js yang sudah memakainya sejak C2. */
    var kotor = false;

    function tandaiKotor(nilai) {
        kotor = nilai;
        if (belumSimpan) belumSimpan.hidden = !nilai;
        if (peringatanEkspor && !nilai) peringatanEkspor.hidden = true;
    }

    function baris() {
        return (ta.value || '').split('\n').map(function (s) { return s.trim(); }).filter(Boolean);
    }
    if (ta && jumlah) {
        ta.addEventListener('input', function () {
            var n = baris().length;
            jumlah.textContent = n;
            if (lebih) lebih.hidden = !(jatah && n > jatah);
            // U5 — peringatan MUNCUL sebelum Simpan ditekan, bukan sesudah
            // ketikannya ditolak.
            if (lewat) lewat.hidden = n <= maks;
            tandaiKotor(true);
        });
    }

    // Menyimpan berarti halaman dimuat ulang; penanda tidak boleh menahan itu.
    var formTamu = ta && ta.form;
    if (formTamu) formTamu.addEventListener('submit', function () { tandaiKotor(false); });

    window.addEventListener('beforeunload', function (e) {
        if (kotor) { e.preventDefault(); e.returnValue = ''; }
    });

    /** Ekspor hanya sah bila layar == tersimpan. */
    function bolehEkspor() {
        if (!kotor) return true;
        if (peringatanEkspor) {
            peringatanEkspor.hidden = false;
            peringatanEkspor.scrollIntoView({ block: 'center', behavior: 'smooth' });
        }
        return false;
    }

    var daftar = document.getElementById('tamu-daftar');
    if (!daftar) return;
    var dasar = daftar.getAttribute('data-link');
    function semuaLink() {
        return baris().map(function (n) { return n + '\t' + dasar + '?to=' + encodeURIComponent(n); });
    }

    var btnSalin = document.getElementById('salin-semua');
    if (btnSalin) btnSalin.addEventListener('click', function () {
        if (!bolehEkspor()) return;
        var teks = semuaLink().join('\n');
        var selesai = function () {
            var asli = btnSalin.textContent;
            btnSalin.textContent = 'Tersalin ✓';
            setTimeout(function () { btnSalin.textContent = asli; }, 1800);
        };
        if (navigator.clipboard) navigator.clipboard.writeText(teks).then(selesai, selesai);
        else {
            var t = document.createElement('textarea');
            t.value = teks; document.body.appendChild(t); t.select();
            try { document.execCommand('copy'); selesai(); } catch (e) {}
            t.remove();
        }
    });

    var btnCsv = document.getElementById('unduh-csv');
    if (btnCsv) btnCsv.addEventListener('click', function () {
        if (!bolehEkspor()) return;
        // CSV dipakai dua arah: mencetak nama di amplop, dan mengirim link
        // personal massal. Baris header ikut supaya bisa langsung dibuka Excel.
        var isi = 'nama,link\n' + baris().map(function (n) {
            return '"' + n.replace(/"/g, '""') + '","' + dasar + '?to=' + encodeURIComponent(n) + '"';
        }).join('\n');
        var url = URL.createObjectURL(new Blob(["﻿" + isi], { type: 'text/csv;charset=utf-8' }));
        var a = document.createElement('a');
        a.href = url; a.download = 'daftar-tamu.csv';
        document.body.appendChild(a); a.click(); a.remove();
        setTimeout(function () { URL.revokeObjectURL(url); }, 1000);
    });
})();
</script>
<?php wp_footer(); ?>
</body>
</html>
