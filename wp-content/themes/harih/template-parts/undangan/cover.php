<?php
/**
 * Section 1 — Gate pembuka + Hero (§7.1, standar kualitas acuan §2.3).
 *
 * GATE: overlay fixed layar penuh — foto ken-burns / gradasi tema, nama tamu
 * dalam panel kaca, tombol berdenyut. Scroll terkunci sampai dibuka; menekan
 * tombol = gesture yang membuat autoplay musik legal di browser. Saat dibuka,
 * gate naik dengan easing dramatis (undangan.js) dan TIDAK kembali.
 *
 * HERO: bagian pertama di aliran halaman — foto arch ber-ken-burns, segel
 * monogram bercincin berputar, nama ber-shimmer, tanggal diapit hairline yang
 * "tumbuh". Nama tamu diisi client-side dari ?to= (keputusan cache A2).
 */
if (!defined('ABSPATH')) exit;
$u = $args;

$inisial = static function (string $nama): string {
    $nama = trim($nama);
    return $nama !== '' ? mb_strtoupper(mb_substr($nama, 0, 1)) : '';
};
$segel = trim($inisial($u['nama_pria']) . '·' . $inisial($u['nama_wanita']), '·');
?>
<div class="gate<?php echo $u['cover_image'] !== '' ? ' has-photo' : ''; ?>" id="gate"<?php
    if ($u['cover_image'] !== '') printf(' style="--cover-image:url(%s)"', esc_url($u['cover_image']));
?>>
    <div class="gate-inner">
        <?php if ($segel !== '') : ?>
        <div class="seal" aria-hidden="true"><span class="seal-ring"></span><span class="seal-teks"><?php echo esc_html($segel); ?></span></div>
        <?php endif; ?>
        <div class="gate-bawah">
            <p class="cover-eyebrow">Undangan Pernikahan</p>
            <h1 class="cover-names"><?php
                echo esc_html($u['nama_pria'] !== '' ? $u['nama_pria'] : 'Mempelai Pria');
                ?> <span class="amp" aria-hidden="true">&amp;</span> <?php
                echo esc_html($u['nama_wanita'] !== '' ? $u['nama_wanita'] : 'Mempelai Wanita');
            ?></h1>
            <?php if ($u['tanggal_resepsi'] !== '') : ?>
                <p class="cover-date"><?php echo esc_html(harih_format_tanggal($u['tanggal_resepsi'])); ?></p>
                <?php if ($u['salam_islami']) : ?><p class="tgl-hijriah" data-hijriah="<?php echo esc_attr($u['tanggal_resepsi']); ?>"></p><?php endif; ?>
            <?php endif; ?>
            <div class="gate-tamu">
                <p class="gate-tamu-label">Kepada Yth.</p>
                <p class="guest-name">Bapak/Ibu/Saudara/i</p>
            </div>
            <button type="button" class="btn btn-open" id="buka-undangan">Buka Undangan</button>
        </div>
    </div>
</div>

<section class="section hero" id="hero">
    <?php if ($u['cover_image'] !== '') : ?>
    <div class="hero-arch" data-reveal>
        <div class="hero-arch-foto"><img src="<?php echo esc_url($u['cover_image']); ?>" alt="" loading="eager" decoding="async"></div>
    </div>
    <?php endif; ?>
    <?php if ($segel !== '') : ?>
    <div class="seal seal-hero<?php echo $u['cover_image'] !== '' ? ' naik' : ''; ?>" data-reveal data-delay="120" aria-hidden="true"><span class="seal-ring"></span><span class="seal-teks"><?php echo esc_html($segel); ?></span></div>
    <?php endif; ?>
    <p class="cover-eyebrow" data-reveal data-delay="200">Undangan Pernikahan</p>
    <h2 class="cover-names hero-names" data-reveal data-delay="280"><?php
        echo esc_html($u['nama_pria'] !== '' ? $u['nama_pria'] : 'Mempelai Pria');
        ?> <span class="amp" aria-hidden="true">&amp;</span> <?php
        echo esc_html($u['nama_wanita'] !== '' ? $u['nama_wanita'] : 'Mempelai Wanita');
    ?></h2>
    <?php if ($u['tanggal_resepsi'] !== '') : ?>
    <div class="hero-tanggal" data-reveal data-delay="360">
        <i data-grow="44"></i>
        <p><?php echo esc_html(harih_format_tanggal($u['tanggal_resepsi'])); ?></p>
        <i data-grow="44"></i>
    </div>
    <?php if ($u['salam_islami']) : ?><p class="tgl-hijriah hero-hijriah" data-reveal data-delay="420" data-hijriah="<?php echo esc_attr($u['tanggal_resepsi']); ?>"></p><?php endif; ?>
    <?php endif; ?>
</section>
