<?php
/**
 * Section 8 — Amplop digital (§7.8, paket ≠ hemat).
 * `rekening` = teks bebas per baris, mis. "BCA 1234567890 a.n. Fulan".
 * Tombol Salin menyalin deretan angka terpanjang di baris (nomor rekening) —
 * lebih berguna daripada menyalin satu baris penuh ke aplikasi m-banking.
 */
if (!defined('ABSPATH')) exit;
$u = $args;

$baris = array_values(array_filter(array_map('trim', explode("\n", $u['rekening']))));
?>
<section class="section amplop" id="amplop" data-reveal>
    <h2 class="section-title">Amplop Digital</h2>
    <p class="section-intro">Doa restu Anda adalah hadiah terbaik bagi kami. Namun bila ingin berbagi tanda kasih, dapat melalui:</p>

    <?php foreach ($baris as $line) :
        $salin = $line;
        if (preg_match('/\d(?:[\d\s.-]*\d)?/', $line, $m)) {
            $digits = preg_replace('/\D+/', '', $m[0]);
            if (strlen($digits) >= 6) $salin = $digits;
        }
    ?>
    <div class="rekening-item">
        <span class="rekening-teks"><?php echo esc_html($line); ?></span>
        <button type="button" class="btn-copy" data-copy="<?php echo esc_attr($salin); ?>">Salin</button>
    </div>
    <?php endforeach; ?>

    <?php if ($u['qris_media_url'] !== '') : ?>
    <figure class="qris">
        <img src="<?php echo esc_url($u['qris_media_url']); ?>" alt="Kode QRIS" loading="lazy" decoding="async">
        <figcaption>Scan QRIS dari aplikasi pembayaran apa pun</figcaption>
    </figure>
    <?php endif; ?>
</section>
