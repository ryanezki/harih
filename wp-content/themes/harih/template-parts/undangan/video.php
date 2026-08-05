<?php
/**
 * Section — Live Streaming (acuan: bukan "Video" — fungsinya menyiarkan
 * prosesi bagi yang berhalangan hadir; embed YouTube live memakai URL embed
 * yang sama). Bingkai hairline, iframe lazy.
 */
if (!defined('ABSPATH')) exit;
$u  = $args;
$yt = harih_youtube_id($u['video_url']);
?>
<section class="section video" id="video">
    <p class="section-title" data-reveal>Live Streaming</p>
    <p class="section-intro" data-reveal data-delay="100">Bagi Bapak/Ibu/Saudara/i yang berhalangan hadir, prosesi dapat diikuti secara langsung:</p>
    <?php if ($yt !== '') : ?>
        <div class="video-frame" data-reveal data-delay="180">
            <div class="video-frame-dalam">
                <iframe src="<?php echo esc_url('https://www.youtube-nocookie.com/embed/' . $yt); ?>"
                    title="Live streaming" loading="lazy" allowfullscreen
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"></iframe>
            </div>
        </div>
    <?php else : ?>
        <a class="btn btn-ghost" data-reveal data-delay="180" href="<?php echo esc_url($u['video_url']); ?>" target="_blank" rel="noopener">Buka Live Streaming</a>
    <?php endif; ?>
</section>
