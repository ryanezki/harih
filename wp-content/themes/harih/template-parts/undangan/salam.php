<?php
/**
 * Section 2 — Salam pembuka (urutan acuan: SEBELUM countdown).
 *
 * Isi salam & ayat mengikuti `nuansa` undangan (islam/kristen/katolik/hindu/
 * buddha/konghucu/umum) — lihat harih_nuansa_teks() di functions.php. Nuansa
 * `umum` menampilkan kalimat sukacita saja, tanpa unsur agama sama sekali.
 */
if (!defined('ABSPATH')) exit;
$u = $args;
$n = $u['nuansa_teks'];
$religius = $n['salam'] !== '' || $n['ayat'] !== '';
?>
<section class="section salam" id="salam">
    <span data-reveal aria-hidden="true"><span class="acara-diamond"></span></span>
    <?php if ($n['salam'] !== '') : ?>
    <p class="salam-assalam" data-reveal data-delay="100"><?php echo wp_kses($n['salam'], []); ?></p>
    <?php endif; ?>
    <?php if ($n['ayat'] !== '') : ?>
    <blockquote class="salam-ayat" data-reveal data-delay="180">
        <p>&ldquo;<?php echo esc_html($n['ayat']); ?>&rdquo;</p>
        <?php if ($n['sumber'] !== '') : ?><cite><?php echo esc_html($n['sumber']); ?></cite><?php endif; ?>
    </blockquote>
    <?php endif; ?>
    <p class="salam-teks" data-reveal data-delay="<?php echo $religius ? 260 : 120; ?>"><?php
        echo $religius
            ? 'Dengan memohon rahmat Tuhan Yang Maha Esa, dengan penuh sukacita kami mengundang Bapak/Ibu/Saudara/i pada pernikahan kami:'
            : 'Dengan penuh sukacita, kami mengundang Bapak/Ibu/Saudara/i untuk hadir pada pernikahan kami:';
    ?></p>
</section>
