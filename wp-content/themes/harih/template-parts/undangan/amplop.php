<?php
/**
 * Section 8 — Amplop digital, TERTUTUP secara default (standar acuan §3).
 * Hanya pengantar + tombol; rekening & QRIS mengembang saat tamu sendiri yang
 * membukanya — tidak ada kesan ditodong tanda kasih. Animasi buka/tutup:
 * grid-template-rows 0fr→1fr (undangan.css) — tanpa mengukur tinggi di JS.
 * QRIS dibingkai panel putih ganda + corner ticks: QR telanjang dari
 * qrserver adalah penanda template gratis.
 */
if (!defined('ABSPATH')) exit;
$u = $args;

$baris = array_values(array_filter(array_map('trim', explode("\n", $u['rekening']))));
?>
<section class="section amplop" id="amplop">
    <h2 class="label-atas" data-reveal>Amplop Digital</h2>
    <p class="section-intro amplop-intro" data-reveal data-delay="100">Doa restu Anda adalah hadiah terbaik bagi kami. Namun bila ingin berbagi tanda kasih, dapat melalui:</p>
    <button type="button" class="btn btn-ghost" id="amplop-toggle" aria-expanded="false" aria-controls="amplop-wrap" data-reveal data-delay="180">Buka Amplop Digital</button>

    <div class="amplop-wrap" id="amplop-wrap">
        <div class="amplop-isi">
            <?php foreach ($baris as $line) :
                /* Urai "BANK 1234567890 a.n. Nama" → kartu bank bertumpuk ala
                   acuan. Baris yang tak terurai jatuh ke tampilan satu baris. */
                $bank = $nomor = $anama = '';
                if (preg_match('/^([^0-9]*?)\s*([0-9][0-9 .\-]{4,})\s*(?:a\.?\s?n\.?\s*(.+))?$/iu', $line, $m)) {
                    $bank  = trim($m[1]);
                    $nomor = preg_replace('/\D+/', '', $m[2]);
                    $anama = trim($m[3] ?? '');
                }
                $salin = $nomor !== '' && strlen($nomor) >= 6 ? $nomor : $line;
                // tampilkan nomor berkelompok 4 digit supaya terbaca sebagai kartu
                $tampil = $nomor !== '' ? trim(chunk_split($nomor, 4, ' ')) : '';
            ?>
            <div class="rekening-item<?php echo $nomor !== '' ? ' kartu-bank' : ''; ?>">
                <?php if ($nomor !== '') : ?>
                    <?php if ($bank !== '') : ?><p class="bank-label"><?php echo esc_html($bank); ?></p><?php endif; ?>
                    <p class="bank-nomor"><?php echo esc_html($tampil); ?></p>
                    <?php if ($anama !== '') : ?><p class="bank-nama">a.n. <?php echo esc_html($anama); ?></p><?php endif; ?>
                    <button type="button" class="btn-copy" data-copy="<?php echo esc_attr($salin); ?>">Salin Nomor</button>
                <?php else : ?>
                    <span class="rekening-teks"><?php echo esc_html($line); ?></span>
                    <button type="button" class="btn-copy" data-copy="<?php echo esc_attr($salin); ?>">Salin</button>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>

            <?php
            /* Dompet digital (G1.8, 2026-08-07) — jawaban atas ide "gifting
               digital via payment gateway", tanpa escrow-nya. Uang mengalir
               LANGSUNG dari tamu ke mempelai; kita cuma menyediakan tombolnya.
               Nol fee platform, nol dana singgah, nol urusan izin PJP.

               Host sudah di-whitelist saat sanitasi meta (`cpt.php`) — field ini
               datang dari form pemesan dan berakhir sebagai <a href> di halaman
               yang disebar ke ratusan tamu; tanpa itu ia jadi open redirect di
               halaman yang justru dipercaya orang. Format tersimpan: "Nama|URL". */
            $dompet = [];
            foreach (array_filter(array_map('trim', explode("\n", (string) ($u['dompet'] ?? '')))) as $baris) {
                $bagian = explode('|', $baris, 2);
                if (count($bagian) === 2 && $bagian[1] !== '') $dompet[] = $bagian;
            }
            ?>
            <?php if ($dompet) : ?>
            <div class="dompet-kartu">
                <p class="qris-label">Dompet Digital</p>
                <p class="qris-sub">Langsung ke aplikasi, tanpa salin nomor:</p>
                <div class="dompet-tombol">
                    <?php foreach ($dompet as $d) : ?>
                    <a class="btn btn-ghost" href="<?php echo esc_url($d[1]); ?>" target="_blank" rel="noopener"><?php echo esc_html($d[0]); ?></a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($u['qris_media_url'] !== '') : ?>
            <div class="qris-kartu">
                <i class="tick t1"></i><i class="tick t2"></i><i class="tick t3"></i><i class="tick t4"></i>
                <p class="qris-label">QRIS</p>
                <p class="qris-sub">Tanda kasih tanpa tunai</p>
                <?php /* U16 — `<figcaption>` DI DALAM `<figure>`. HTML hanya
                         mengizinkannya sebagai anak pertama atau terakhir; di
                         luar itu parser memperlakukannya sebagai elemen generik
                         dan keterkaitannya dengan gambar QRIS putus. Nol
                         perubahan visual — `.qris-cap` hanya mengatur margin,
                         font, dan warna. */ ?>
                <figure class="qris">
                    <span class="qris-diamond" aria-hidden="true"></span>
                    <span class="qris-panel"><img src="<?php echo esc_url($u['qris_media_url']); ?>" alt="Kode QRIS" loading="lazy" decoding="async"></span>
                    <figcaption class="qris-cap">Scan QRIS dari aplikasi pembayaran apa pun</figcaption>
                </figure>
            </div>
            <?php endif; ?>

            <?php if (trim($u['alamat_kado']) !== '') : ?>
            <div class="kado-kartu">
                <p class="qris-label">Kirim Hadiah</p>
                <p class="qris-sub">Bagi yang ingin mengirim kado atau hampers:</p>
                <p class="kado-alamat"><?php echo nl2br(esc_html($u['alamat_kado'])); ?></p>
                <button type="button" class="btn-copy" data-copy="<?php echo esc_attr(preg_replace('/\s+/', ' ', $u['alamat_kado'])); ?>">Salin Alamat</button>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>
