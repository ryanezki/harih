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

        <label class="field">
            <span>Kehadiran</span>
            <select name="hadir" id="rsvp-hadir">
                <option value="hadir">Hadir</option>
                <option value="tidak">Berhalangan</option>
                <option value="ragu">Belum Pasti</option>
            </select>
        </label>

        <label class="field">
            <span>Ucapan &amp; doa</span>
            <textarea name="pesan" id="rsvp-pesan" rows="3" maxlength="1500"></textarea>
        </label>

        <button type="submit" class="btn" id="rsvp-submit">Kirim</button>
        <p class="rsvp-msg" id="rsvp-msg" role="status"></p>
    </form>

    <div class="ucapan-list" id="ucapan-list" aria-live="polite"></div>
</section>
