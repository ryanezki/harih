<?php
/** Section 2 — Countdown ke resepsi (§7.2) + state pasca-acara (T3.7). */
if (!defined('ABSPATH')) exit;
$u = $args;
if ($u['target'] === '') return;
?>
<section class="section countdown" id="countdown" data-reveal>
    <h2 class="section-title">Menghitung Hari</h2>
    <div class="countdown-grid" id="countdown-grid" data-target="<?php echo esc_attr($u['target']); ?>">
        <div class="cd-tile"><span class="cd-num" data-cd="hari">0</span><span class="cd-label">Hari</span></div>
        <div class="cd-tile"><span class="cd-num" data-cd="jam">0</span><span class="cd-label">Jam</span></div>
        <div class="cd-tile"><span class="cd-num" data-cd="menit">0</span><span class="cd-label">Menit</span></div>
        <div class="cd-tile"><span class="cd-num" data-cd="detik">0</span><span class="cd-label">Detik</span></div>
    </div>
    <p class="countdown-done" id="countdown-done" hidden>Acara telah berlangsung — terima kasih atas doa &amp; restu Anda.</p>
</section>
