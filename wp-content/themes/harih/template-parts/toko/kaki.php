<?php
/**
 * Footer halaman toko — satu sumber untuk lima halaman publik.
 * Tautan legal & reseller muncul kondisional mengikuti halaman yang ada.
 */
if (!defined('ABSPATH')) exit;
$ada_harga    = (bool) get_page_by_path('harga');
$ada_reseller = harih_reseller_aktif();
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
            <?php if ($ada_reseller) : ?><a href="<?php echo esc_url(home_url('/jadi-reseller/')); ?>">Jadi Reseller</a><?php endif; ?>
        </nav>
    </div>
    <p class="kaki-cc">© <?php echo esc_html(wp_date('Y')); ?> hariH · harih.id</p>
</footer>
