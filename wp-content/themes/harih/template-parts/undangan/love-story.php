<?php
/** Section 5 — Love story (§7.5, paket ≠ hemat). */
if (!defined('ABSPATH')) exit;
$u = $args;
?>
<section class="section love-story" id="love-story" data-reveal>
    <h2 class="section-title">Kisah Kami</h2>
    <div class="prose"><?php echo nl2br(esc_html($u['love_story'])); ?></div>
</section>
