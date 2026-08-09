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
    <?php
    /* R4 — kaki bercabang. Untuk order mitra, nama yang tampil adalah nama
       MITRA: itu nilai tambah yang dijual ke WO & percetakan, dan alasan
       mereka memakainya lagi. Untuk order publik, tetap hariH persis seperti
       sebelumnya — ajakan "jadi mitra" belum dipasang karena halaman /mitra/
       baru lahir di M8, dan disiplin C3/U21 melarang menaut ke alur yang
       belum ada. Tautan mitra dipasang nofollow: ini tautan pihak ketiga yang
       muncul di ratusan halaman undangan sekaligus. */
    $brand = $u['mitra'] ?? ['nama' => 'hariH', 'url' => home_url('/'), 'mitra' => false];
    ?>
    <p class="credit">Undangan digital oleh
        <?php if ($brand['url'] !== '') : ?>
            <a href="<?php echo esc_url($brand['url']); ?>"<?php echo !empty($brand['mitra']) ? ' target="_blank" rel="noopener nofollow"' : ''; ?>><?php echo esc_html($brand['nama']); ?></a>
        <?php else : ?>
            <?php echo esc_html($brand['nama']); ?>
        <?php endif; ?>
    </p>
</section>
