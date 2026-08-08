<?php
/**
 * Footer halaman toko — satu sumber untuk lima halaman publik.
 * Tautan legal muncul kondisional mengikuti halaman yang ada.
 */
if (!defined('ABSPATH')) exit;
$ada_harga = (bool) get_page_by_path('harga');
?>
<footer class="kaki">
    <div class="kaki-atas">
        <div>
            <p class="brand">hariH</p>
            <p class="kaki-tag"><?php echo esc_html($args['tagline'] ?? 'Undangan digital untuk hari bahagiamu.'); ?></p>
        </div>
        <nav class="kaki-nav">
            <a href="<?php echo esc_url(home_url('/kontak/')); ?>">Kontak</a>
            <a href="<?php echo esc_url(home_url('/syarat-ketentuan/')); ?>">Syarat &amp; Ketentuan</a>
            <a href="<?php echo esc_url(home_url('/kebijakan-privasi/')); ?>">Kebijakan Privasi</a>
            <a href="<?php echo esc_url(home_url('/kebijakan-refund/')); ?>">Refund</a>
            <?php if ($ada_harga) : ?><a href="<?php echo esc_url(home_url('/harga/')); ?>">Paket Cetak</a><?php endif; ?>
        </nav>
    </div>
    <?php /* U23 — SINYAL KEPERCAYAAN. Hitungan pada TEKS TAMPAK beranda,
             /harga/, dan /satuan/: "testimoni" 0 · "ulasan" 0 · "portofolio" 0,
             dan nomor CS muncul 3–9x di HTML mentah tapi NOL kali di teks tampak
             — hanya di dalam href wa.me, jadi tidak bisa dibaca, disalin, atau
             dicek pengunjung. Blok Identitas Usaha pun cuma ada di /kontak/.
             Di negara dengan penipuan online tinggi, itu tiga hal yang paling
             murah untuk diperbaiki. Ditaruh di kaki karena ia satu sumber untuk
             delapan halaman. */ ?>
    <p class="kaki-identitas">
        <strong>hariH</strong> · Melayani seluruh Indonesia ·
        WhatsApp <a href="<?php echo esc_url(harih_wa_link('Halo hariH, saya mau bertanya soal undangan.')); ?>" target="_blank" rel="noopener"><?php echo esc_html(harih_wa_tampil()); ?></a> ·
        <a href="mailto:hi@harih.id">hi@harih.id</a><br>
        CS Senin–Sabtu 09.00–18.00 WIB · di luar jam itu dibalas paling lambat pagi berikutnya.
    </p>
    <p class="kaki-cc">© <?php echo esc_html(wp_date('Y')); ?> hariH · harih.id</p>
</footer>
