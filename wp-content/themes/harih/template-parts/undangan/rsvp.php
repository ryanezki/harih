<?php
/** Section 9 — RSVP + daftar ucapan (§7.9). Submit & muat daftar via undangan.js → REST undangan/v1. */
if (!defined('ABSPATH')) exit;
$u = $args;
?>
<section class="section rsvp" id="rsvp" data-reveal>
    <h2 class="section-title">Konfirmasi &amp; Ucapan</h2>

    <form id="rsvp-form" autocomplete="off">
        <?php /* Honeypot: manusia tidak melihat/mengisi field ini (§6). */ ?>
        <input type="text" name="website" class="hp-field" tabindex="-1" autocomplete="off" aria-hidden="true">

        <label class="field">
            <span>Nama</span>
            <input type="text" name="nama" id="rsvp-nama" maxlength="100" required>
        </label>

        <div class="field">
            <span id="rsvp-hadir-label">Kehadiran</span>
            <?php /* Tiga pill sejajar (acuan) — nilai disimpan di input hidden
                     ber-id sama dengan select lama, jadi kontrak undangan.js
                     tidak berubah. */ ?>
            <input type="hidden" name="hadir" id="rsvp-hadir" value="hadir">
            <div class="hadir-opsi" role="group" aria-labelledby="rsvp-hadir-label">
                <button type="button" class="hadir-btn aktif" data-hadir="hadir">Hadir</button>
                <button type="button" class="hadir-btn" data-hadir="tidak">Berhalangan</button>
                <button type="button" class="hadir-btn" data-hadir="ragu">Belum Pasti</button>
            </div>
        </div>

        <?php /* U16 — `<label>`, bukan `<div>` + `<span>` lepas. Sebelumnya
                 nama aksesibel select ini KOSONG, sementara dua field di
                 sekitarnya sudah memakai `<label class="field">` — jadi ini
                 kelalaian, bukan pilihan. Nol perubahan CSS: `.field span` dan
                 `.field select` menargetkan kelas, bukan nama elemen. */ ?>
        <label class="field rsvp-jumlah-baris">
            <span>Jumlah Tamu</span>
            <select name="jumlah" id="rsvp-jumlah">
                <?php for ($n = 1; $n <= 6; $n++) : ?><option value="<?php echo $n; ?>"><?php echo $n; ?> orang</option><?php endfor; ?>
            </select>
        </label>

        <?php /* U18 — "Hadir Pada" hanya bermakna bila memang ADA dua acara.
                 Pada undangan resepsi-saja (tanggal akad tidak wajib di
                 page-isi-data.php) acara.php merender satu kartu sementara blok
                 ini tetap menawarkan Akad/Resepsi/Keduanya dengan "Keduanya"
                 sudah terpilih — nilai yang terkirim ke mempelai jadi omong
                 kosong. undangan.js menyembunyikannya lagi saat tamu memilih
                 Berhalangan. */ ?>
        <?php if (trim((string) ($u['tanggal_akad'] ?? '')) !== '' && trim((string) ($u['tanggal_resepsi'] ?? '')) !== '') : ?>
        <div class="field rsvp-sesi-baris">
            <span id="rsvp-sesi-label">Hadir Pada</span>
            <input type="hidden" name="sesi" id="rsvp-sesi" value="keduanya">
            <div class="hadir-opsi" role="group" aria-labelledby="rsvp-sesi-label">
                <button type="button" class="hadir-btn sesi-btn" data-sesi="akad">Akad</button>
                <button type="button" class="hadir-btn sesi-btn" data-sesi="resepsi">Resepsi</button>
                <button type="button" class="hadir-btn sesi-btn aktif" data-sesi="keduanya">Keduanya</button>
            </div>
        </div>
        <?php endif; ?>

        <label class="field">
            <span>Ucapan &amp; doa</span>
            <textarea name="pesan" id="rsvp-pesan" rows="3" maxlength="1500"></textarea>
        </label>

        <button type="submit" class="btn" id="rsvp-submit">Kirim</button>
        <p class="rsvp-msg" id="rsvp-msg" role="status"></p>

        <?php /* Konfirmasi ke WhatsApp MEMPELAI (G1.6) — muncul setelah RSVP
                 terkirim, isinya sudah terisi dari jawaban tamu. Pesannya
                 berjalan dari nomor TAMU ke nomor mempelai; nomor bisnis hariH
                 tidak menerima apa pun, jadi sesi WAHA tidak berisiko.
                 Hanya dirender bila undangan punya nomor CP. */ ?>
        <?php if (trim($u['wa_cp']) !== '') : ?>
        <a class="btn btn-ghost rsvp-wa" id="rsvp-wa" href="#" target="_blank" rel="noopener" hidden>
            Beri tahu mempelai lewat WhatsApp
        </a>
        <?php endif; ?>
    </form>

    <div class="ucapan-list" id="ucapan-list" aria-live="polite"></div>
</section>
