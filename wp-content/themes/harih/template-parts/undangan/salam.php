<?php
/**
 * Section 2 — Salam pembuka (urutan acuan: SEBELUM countdown).
 * Konvensi undangan akad Indonesia (evaluasi 2026-08-06): salam Islami +
 * QS. Ar-Rum: 21. Meta `salam_islami` = '0' menyembunyikan blok Islami
 * (toggle CS via wp-admin untuk pasangan non-Muslim) — teks sukacita umum
 * tetap tampil.
 */
if (!defined('ABSPATH')) exit;
$u = $args;
?>
<section class="section salam" id="salam">
    <span data-reveal aria-hidden="true"><span class="acara-diamond"></span></span>
    <?php if ($u['salam_islami']) : ?>
    <p class="salam-assalam" data-reveal data-delay="100">Assalamu&rsquo;alaikum Warahmatullahi Wabarakatuh</p>
    <blockquote class="salam-ayat" data-reveal data-delay="180">
        <p>&ldquo;Dan di antara tanda-tanda (kebesaran)-Nya ialah Dia menciptakan pasangan-pasangan untukmu dari jenismu sendiri, agar kamu cenderung dan merasa tenteram kepadanya, dan Dia menjadikan di antaramu rasa kasih dan sayang.&rdquo;</p>
        <cite>QS. Ar-Rum: 21</cite>
    </blockquote>
    <?php endif; ?>
    <p class="salam-teks" data-reveal data-delay="<?php echo $u['salam_islami'] ? 260 : 120; ?>">Dengan memohon rahmat Tuhan Yang Maha Esa, dengan penuh sukacita kami mengundang Bapak/Ibu/Saudara/i pada pernikahan kami:</p>
</section>
