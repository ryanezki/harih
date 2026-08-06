<?php
/**
 * Nav halaman toko — dipakai bersama katalog, harga, satuan, teks, reseller.
 * Dijadikan satu berkas supaya lima halaman tidak pelan-pelan berbeda sendiri
 * (sebelumnya tiap halaman menulis header-nya masing-masing dan hasilnya
 * memang sudah mulai melenceng).
 *
 * $args['beranda'] = true bila sedang DI beranda (tautan pakai anchor lokal).
 */
if (!defined('ABSPATH')) exit;
$di_beranda = !empty($args['beranda']);
$basis      = $di_beranda ? '' : home_url('/');
$ada_harga  = (bool) get_page_by_path('harga');
?>
<header class="nav">
    <?php if ($di_beranda) : ?>
        <p class="brand">hariH</p>
    <?php else : ?>
        <a class="brand-tautan" href="<?php echo esc_url(home_url('/')); ?>"><p class="brand">hariH</p></a>
    <?php endif; ?>
    <nav class="nav-tautan">
        <a href="<?php echo esc_url($basis . '#tema'); ?>">Tema</a>
        <a href="<?php echo esc_url($basis . '#paket'); ?>">Paket</a>
        <?php if ($ada_harga) : ?>
            <a href="<?php echo esc_url($di_beranda ? '#cetak' : home_url('/harga/')); ?>">Cetak</a>
        <?php endif; ?>
        <a class="nav-cta" href="<?php echo esc_url($basis . '#paket'); ?>">Buat Undangan</a>
        <?php /* Mode gelap (G1.1). Ikon dipilih CSS lewat atribut `data-tema` di
                 <html>, jadi JS tidak perlu menyentuh isi tombol sama sekali. */ ?>
        <button type="button" class="tema-toggle" id="tema-toggle"
                aria-label="Ganti mode terang / gelap" title="Ganti mode terang / gelap">
            <span class="ikon-terang" aria-hidden="true">☾</span>
            <span class="ikon-gelap" aria-hidden="true">☀</span>
        </button>
    </nav>
</header>
<script data-no-optimize="1">
(function () {
    var b = document.getElementById('tema-toggle');
    if (!b) return;
    b.addEventListener('click', function () {
        // Sumber kebenaran = yang benar-benar dirender, bukan tebakan dari
        // atribut: pada kunjungan pertama atribut memang belum ada dan temanya
        // datang dari setelan sistem.
        var gelap = getComputedStyle(document.documentElement).colorScheme === 'dark';
        var baru  = gelap ? 'terang' : 'gelap';
        document.documentElement.dataset.tema = baru;
        try { localStorage.setItem('harihTema', baru); } catch (e) {}
    });
})();
</script>
