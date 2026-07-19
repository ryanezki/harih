<?php
/**
 * Template Name: Jadi Reseller hariH
 *
 * Landing pendaftaran reseller (TASKS T3.9) — dibuat oleh scripts/buat-toko.sh.
 * Form mengikuti KONTRAK WF-03 (n8n/workflows/README.md): POST `nama`, `wa`,
 * `bank`, `norek` + honeypot `website` (harus kosong) ke webhook
 * /webhook/daftar-reseller. Respons JSON {ok, message} ditampilkan apa adanya
 * (200 sukses/duplikat · 422 data kurang/bukan WA · 429 rate limit).
 *
 * Alur setelah submit: baris PENDING di sheet → owner approve via link WA/email
 * → kupon dibuat → welcome kit dikirim ke WA pendaftar (maks 1×24 jam).
 */

if (!defined('ABSPATH')) exit;

$webhook = harih_reseller_webhook_url();
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta property="og:type" content="website">
<meta property="og:site_name" content="hariH">
<meta property="og:locale" content="id_ID">
<meta property="og:title" content="Jadi Reseller hariH — Komisi 30% Tiap Order">
<meta property="og:description" content="Bagikan undangan digital mulai Rp 99 ribu ke jaringanmu. Pembeli hemat 10%, kamu dapat komisi 30%. Payout tiap Senin, cukup dari HP.">
<meta property="og:url" content="<?php echo esc_url(get_permalink()); ?>">
<?php wp_head(); ?>
</head>
<body class="katalog-body">

<header class="hero">
    <p class="brand"><a href="<?php echo esc_url(home_url('/')); ?>">hariH</a></p>
    <h1>Jadi reseller,<br>dapat komisi 30% tiap order.</h1>
    <p class="hero-sub">Bagikan undangan digital mulai <strong>Rp 99 ribu</strong> ke kenalan &amp; media sosialmu. Pembeli hemat 10% pakai kodemu — kamu terima komisi 30%. Semua dari HP.</p>
    <div class="hero-cta">
        <a class="btn btn-utama" href="#daftar">Daftar Sekarang</a>
        <a class="btn btn-garis" href="<?php echo esc_url(home_url('/')); ?>">Lihat Katalog ↗</a>
    </div>
</header>

<main>
    <section class="cara">
        <h2>Cara kerjanya</h2>
        <ol class="cara-grid">
            <li><span class="cara-no">1</span><strong>Daftar &amp; verifikasi</strong><span>Isi form di bawah. Kami verifikasi maksimal 1×24 jam, lalu kode kupon pribadimu dikirim via WhatsApp.</span></li>
            <li><span class="cara-no">2</span><strong>Bagikan kodemu</strong><span>Promosikan katalog + kode kuponmu — caption siap pakai kami sediakan. Pembeli hemat 10%.</span></li>
            <li><span class="cara-no">3</span><strong>Terima komisi</strong><span>Komisi 30% dari tiap order tercatat otomatis. Rekap + payout ke rekeningmu tiap Senin pagi.</span></li>
        </ol>
    </section>

    <section class="paket" id="daftar">
        <h2>Daftar jadi reseller</h2>

        <div class="panel-sukses" id="panel-sukses" hidden>
            <div class="cek" aria-hidden="true">✓</div>
            <h3>Pendaftaran diterima!</h3>
            <p id="pesan-sukses">Kami verifikasi maksimal 1×24 jam — kode kupon &amp; welcome kit dikirim ke WhatsApp kamu setelah disetujui.</p>
        </div>

        <form class="form-kartu" id="form-reseller" novalidate<?php if ($webhook === '') echo ' data-nonaktif'; ?>>
            <?php if ($webhook === '') : ?>
                <div class="banner-warning">Form belum terhubung ke sistem. Hubungi kami via halaman <a href="<?php echo esc_url(home_url('/kontak/')); ?>">Kontak</a>.</div>
            <?php endif; ?>

            <?php /* Honeypot (kontrak WF-03): manusia tidak melihat & tidak mengisi ini */ ?>
            <div class="hp-field" aria-hidden="true">
                <label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
            </div>

            <label class="field"><span>Nama lengkap *</span>
                <input type="text" name="nama" maxlength="100" required placeholder="mis. Dina Rahma"></label>
            <label class="field"><span>Nomor WhatsApp aktif *</span>
                <input type="tel" name="wa" maxlength="20" inputmode="tel" required placeholder="08xxxxxxxxxx">
                <small>Kode kupon, welcome kit, dan rekap komisi dikirim ke nomor ini.</small></label>
            <div class="row-2">
                <label class="field"><span>Bank / e-wallet *</span>
                    <input type="text" name="bank" maxlength="50" required placeholder="mis. BCA / DANA"></label>
                <label class="field"><span>Nomor rekening *</span>
                    <input type="text" name="norek" maxlength="40" inputmode="numeric" required placeholder="untuk payout komisi"></label>
            </div>

            <button type="submit" class="btn btn-utama btn-blok" id="btn-daftar" <?php disabled($webhook === ''); ?>>Daftar Jadi Reseller</button>
            <p class="kirim-msg" id="kirim-msg" role="status"></p>
        </form>

        <div class="aturan">
            <h3>Ketentuan singkat</h3>
            <ul class="paket-fitur">
                <li>Promosikan ke kenalan &amp; media sosialmu sendiri — bukan spam ke grup acak.</li>
                <li>Kode kupon tidak berlaku untuk pembelian sendiri.</li>
                <li>Komisi 30% dihitung dari nilai order setelah diskon; payout tiap Senin.</li>
                <li>hariH dapat menonaktifkan kode yang disalahgunakan.</li>
            </ul>
        </div>
    </section>
</main>

<footer class="kaki">
    <p class="brand">hariH</p>
    <nav class="kaki-nav">
        <a href="<?php echo esc_url(home_url('/')); ?>">Katalog</a>
        <a href="<?php echo esc_url(home_url('/kontak/')); ?>">Kontak</a>
        <a href="<?php echo esc_url(home_url('/syarat-ketentuan/')); ?>">Syarat &amp; Ketentuan</a>
    </nav>
    <p class="kaki-cc">© <?php echo esc_html(wp_date('Y')); ?> hariH · harih.id</p>
</footer>

<script>
window.RESELLER = <?php echo wp_json_encode(['webhook' => $webhook]); ?>;
</script>
<?php wp_footer(); ?>
</body>
</html>
