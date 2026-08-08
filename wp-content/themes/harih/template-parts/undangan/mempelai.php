<?php
/**
 * Section 3 — Profil mempelai (standar acuan §3: lingkaran inisial ala avatar
 * terlihat seperti aplikasi chat — diganti panel ARCH bergradasi dengan
 * inisial serif italic besar, menggemakan motif arch galeri & hero).
 */
if (!defined('ABSPATH')) exit;
$u = $args;

$inisial = static fn(string $nama): string => $nama !== '' ? mb_strtoupper(mb_substr($nama, 0, 1)) : '♥';
?>
<section class="section mempelai" id="mempelai">
    <h2 class="section-title" data-reveal>Kedua Mempelai</h2>

    <?php
    /* "Putra kedua dari Bapak…" — konvensi kuat undangan Indonesia.
       Ikon Instagram inline SVG: nol request, warna ikut emas tema. */
    $ig_svg = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.2" cy="6.8" r="1" fill="currentColor" stroke="none"/></svg>';
    $kartu_mempelai = [
        ['nama' => $u['nama_pria'],   'ortu' => $u['ortu_pria'],   'urutan' => $u['anak_ke_pria'],   'ig' => $u['ig_pria'],   'sebutan' => 'Putra'],
        ['nama' => $u['nama_wanita'], 'ortu' => $u['ortu_wanita'], 'urutan' => $u['anak_ke_wanita'], 'ig' => $u['ig_wanita'], 'sebutan' => 'Putri'],
    ];
    ?>
    <?php foreach ($kartu_mempelai as $i => $mp) : ?>
    <?php if ($i === 1) : ?><div class="mempelai-amp" aria-hidden="true" data-reveal>&amp;</div><?php endif; ?>
    <div class="mempelai-card" data-reveal data-delay="120">
        <div class="arch-inisial" aria-hidden="true"><?php echo esc_html($inisial($mp['nama'])); ?></div>
        <h3 class="mempelai-nama"><?php echo esc_html($mp['nama']); ?></h3>
        <?php if ($mp['ortu'] !== '') : ?>
            <p class="mempelai-ortu"><?php
                echo esc_html($mp['sebutan'] . ($mp['urutan'] !== '' ? ' ' . $mp['urutan'] : '') . ' dari ' . $mp['ortu']);
            ?></p>
        <?php endif; ?>
        <?php if ($mp['ig'] !== '') : ?>
            <a class="mempelai-ig" href="<?php echo esc_url('https://instagram.com/' . $mp['ig']); ?>" target="_blank" rel="noopener" aria-label="Instagram <?php echo esc_attr($mp['nama']); ?>"><?php echo $ig_svg; // svg statis aman ?><span>@<?php echo esc_html($mp['ig']); ?></span></a>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</section>
