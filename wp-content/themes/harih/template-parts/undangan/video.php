<?php
/**
 * Section — Live Streaming. Facade: thumbnail YouTube + tombol putar;
 * iframe (berat) baru dimuat saat DIKLIK (undangan.js) — evaluasi 2026-08-06:
 * "embed YouTube berat, ganti thumbnail + lazy load".
 */
if (!defined('ABSPATH')) exit;
$u  = $args;
$yt = harih_youtube_id($u['video_url']);
?>
<section class="section video" id="video">
    <h2 class="section-title" data-reveal>Live Streaming</h2>
    <p class="section-intro" data-reveal data-delay="100">Bagi Bapak/Ibu/Saudara/i yang berhalangan hadir, prosesi dapat diikuti secara langsung:</p>
    <?php if ($yt !== '') : ?>
        <div class="video-frame" data-reveal data-delay="180">
            <div class="video-frame-dalam" data-yt="<?php echo esc_attr($yt); ?>" role="button" tabindex="0" aria-label="Putar live streaming">
                <img src="<?php echo esc_url('https://i.ytimg.com/vi/' . $yt . '/hqdefault.jpg'); ?>" alt="" loading="lazy" decoding="async">
                <span class="video-play" aria-hidden="true"></span>
            </div>
        </div>
    <?php else : ?>
        <a class="btn btn-ghost" data-reveal data-delay="180" href="<?php echo esc_url($u['video_url']); ?>" target="_blank" rel="noopener">Buka Live Streaming</a>
    <?php endif; ?>
</section>
