<?php
/** Section — Turut Mengundang (konvensi Indonesia): satu nama per baris. */
if (!defined('ABSPATH')) exit;
$u = $args;
$nama = array_values(array_filter(array_map('trim', explode("\n", $u['turut_mengundang']))));
if (!$nama) return;
?>
<section class="section turut" id="turut">
    <h2 class="section-title" data-reveal>Turut Mengundang</h2>
    <ul class="turut-daftar" data-reveal data-delay="120">
        <?php foreach ($nama as $n) : ?><li><?php echo esc_html($n); ?></li><?php endforeach; ?>
    </ul>
</section>
