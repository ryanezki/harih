<?php
/** Section 10 — Penutup + share WA (§7.10) + credit hariH (akuisisi organik). */
if (!defined('ABSPATH')) exit;
$u = $args;

$share_url = 'https://wa.me/?text=' . rawurlencode($u['judul'] . ' — ' . $u['permalink']);

$cp = '';
if ($u['wa_cp'] !== '') {
    $cp = function_exists('undangan_normalize_phone') ? undangan_normalize_phone($u['wa_cp']) : preg_replace('/\D+/', '', $u['wa_cp']);
}
?>
<section class="section penutup" id="penutup">
    <div class="ornament" aria-hidden="true"></div>
    <p class="penutup-teks">Merupakan suatu kehormatan &amp; kebahagiaan bagi kami apabila Bapak/Ibu/Saudara/i berkenan hadir dan memberikan doa restu.</p>
    <p class="penutup-salam">Hormat kami,</p>
    <p class="penutup-nama"><?php echo esc_html(trim($u['nama_pria'] . ' & ' . $u['nama_wanita'], ' &')); ?></p>

    <?php if ($u['nuansa_teks']['penutup'] !== '') : ?>
        <p class="penutup-wassalam"><?php echo wp_kses($u['nuansa_teks']['penutup'], []); ?></p>
    <?php endif; ?>
    <p class="penutup-maaf">Mohon maaf apabila terdapat kesalahan penulisan nama dan gelar.</p>

    <a class="btn btn-ghost" href="<?php echo esc_url($share_url); ?>" target="_blank" rel="noopener">Bagikan via WhatsApp</a>
    <?php if ($cp !== '') : ?>
        <p class="penutup-cp"><a href="<?php echo esc_url('https://wa.me/' . $cp); ?>" target="_blank" rel="noopener">Hubungi kami</a></p>
    <?php endif; ?>

    <?php
    $judul_musik = function_exists('harih_musik_library') ? (harih_musik_library()[$u['musik_url']] ?? '') : '';
    ?>
    <?php if ($judul_musik !== '') : ?>
        <p class="penutup-musik">&#9834;&nbsp;<?php echo esc_html($judul_musik); ?></p>
    <?php endif; ?>
    <p class="credit">Undangan digital oleh <a href="<?php echo esc_url(home_url('/?utm_source=undangan&utm_medium=footer')); ?>">hariH</a></p>
</section>
