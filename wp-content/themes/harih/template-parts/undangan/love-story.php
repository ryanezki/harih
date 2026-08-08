<?php
/**
 * Section — Kisah Kami (acuan: foto full-bleed dengan gradasi atas-bawah yang
 * melebur ke latar + parallax halus, lalu kutipan italic). Foto memakai
 * galeri[1] bila ada (galeri[0] sudah dipakai hero) — tanpa foto, kutipan
 * tetap tampil sendiri.
 */
if (!defined('ABSPATH')) exit;
$u = $args;
$foto = $u['galeri'][1] ?? ($u['galeri'][0] ?? '');
?>
<section class="section love-story" id="love-story">
    <h2 class="section-title" data-reveal>Kisah Kami</h2>
    <?php if ($foto !== '') : ?>
    <div class="kisah-foto" data-reveal data-delay="120">
        <img src="<?php echo esc_url($foto); ?>" alt="" loading="lazy" decoding="async" data-parallax="44">
    </div>
    <?php endif; ?>
    <?php
    /* Timeline bertanggal (FU.3). Aturannya sengaja permisif dan MUNDUR-AMAN:
       baris yang diawali tahun ("2019 — Pertama bertemu", "Mei 2019 · …")
       jadi item timeline; kalau tak satu pun baris berformat itu, teks lama
       tampil apa adanya sebagai prosa. Tidak ada migrasi data, tidak ada
       undangan lama yang berubah tampilannya tanpa diminta. */
    $kisah_item = [];
    $kisah_baris = array_filter(array_map('trim', explode("\n", $u['love_story'])));
    // Satu baris bukan lini masa — butuh minimal dua babak agar terbaca sebagai
    // rangkaian; kalimat tunggal ber-em-dash tetap tampil sebagai prosa.
    if (count($kisah_baris) < 2) $kisah_baris = [];
    foreach ($kisah_baris as $b) {
        // Label bebas selama pendek (≤ 22 karakter) dan diikuti pemisah:
        // "2019 — …", "Maret 2026 — …", "Pertemuan pertama — …". Tidak semua
        // pasangan berpacaran bertahun-tahun; ada yang hitungan bulan, jadi
        // memaksa format tahun membuat fitur ini tidak terpakai (temuan owner).
        if (preg_match('/^(.{1,22}?)\s*[—–]\s*(.+)$/u', $b, $m)) {
            $kisah_item[] = [$m[1], $m[2]];
        } else {
            $kisah_item = [];
            break; // satu baris saja tidak berformat → perlakukan seluruhnya sebagai prosa
        }
    }
    ?>
    <?php if ($kisah_item) : ?>
    <ol class="kisah-timeline">
        <?php foreach ($kisah_item as $i => $k) : ?>
        <li data-reveal data-delay="<?php echo esc_attr(200 + $i * 90); ?>">
            <span class="kt-titik" aria-hidden="true"></span>
            <span class="kt-waktu"><?php echo esc_html($k[0]); ?></span>
            <span class="kt-teks"><?php echo esc_html($k[1]); ?></span>
        </li>
        <?php endforeach; ?>
    </ol>
    <?php else : ?>
    <div class="prose" data-reveal data-delay="200"><?php echo nl2br(esc_html($u['love_story'])); ?></div>
    <?php endif; ?>
</section>
