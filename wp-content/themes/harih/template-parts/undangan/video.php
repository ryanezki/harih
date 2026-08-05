<?php
/** Section 7 — Video / live streaming (§7.7, paket premium). */
if (!defined('ABSPATH')) exit;
$u  = $args;
$yt = harih_youtube_id($u['video_url']);
?>
<section class="section video" id="video" data-reveal>
    <h2 class="section-title">Video</h2>
    <?php if ($yt !== '') : ?>
        <div class="video-frame" data-reveal data-delay="140">
            <div class="video-frame-dalam">
                <iframe src="<?php echo esc_url('https://www.youtube-nocookie.com/embed/' . $yt); ?>"
                    title="Video pernikahan" loading="lazy" allowfullscreen
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"></iframe>
            </div>
        </div>
    <?php else : ?>
        <a class="btn" href="<?php echo esc_url($u['video_url']); ?>" target="_blank" rel="noopener">Tonton Video / Live Streaming</a>
    <?php endif; ?>
</section>
