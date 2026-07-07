/**
 * hariH — perilaku bersama halaman undangan (semua tema).
 * Konfigurasi dari PHP (functions.php): window.UNDANGAN = { id, restUrl, target, musik }.
 * Tanpa dependensi — halaman undangan tidak memuat jQuery/aset lain.
 */
(function () {
    'use strict';

    var cfg = window.UNDANGAN || {};
    var $ = function (sel) { return document.querySelector(sel); };

    /* ---- Nama tamu dari ?to= — dirender client-side supaya semua tamu
       memakai 1 cache halaman yang sama (keputusan A2 blueprint) ---- */
    var nama = new URLSearchParams(location.search).get('to');
    if (nama) {
        document.querySelectorAll('.guest-name').forEach(function (el) { el.textContent = nama; });
    }

    /* ---- Musik ---- */
    var audio = $('#undangan-audio');
    var musicBtn = $('#music-toggle');

    function playMusic() {
        if (!audio) return;
        audio.play().then(function () {
            if (musicBtn) { musicBtn.hidden = false; musicBtn.classList.add('playing'); }
        }).catch(function () {
            // Autoplay ditolak browser — tampilkan tombol dalam keadaan jeda.
            if (musicBtn) { musicBtn.hidden = false; musicBtn.classList.add('paused'); }
        });
    }

    if (musicBtn) {
        musicBtn.addEventListener('click', function () {
            if (!audio) return;
            if (audio.paused) {
                audio.play();
                musicBtn.classList.add('playing');
                musicBtn.classList.remove('paused');
            } else {
                audio.pause();
                musicBtn.classList.remove('playing');
                musicBtn.classList.add('paused');
            }
        });
    }

    /* ---- Buka undangan: lepas kunci scroll + putar musik (autoplay audio
       diblokir browser tanpa gestur — tap tombol ini adalah gesturnya, §7.1) ---- */
    var openBtn = $('#buka-undangan');
    if (openBtn) {
        openBtn.addEventListener('click', function () {
            document.body.classList.remove('is-locked');
            playMusic();
            var next = $('#countdown') || $('#mempelai');
            if (next) next.scrollIntoView({ behavior: 'smooth' });
        });
    }

    /* ---- Countdown + state pasca-acara (target ISO ber-offset +07:00 dari server) ---- */
    var grid = $('#countdown-grid');
    if (grid && grid.dataset.target) {
        var targetMs = Date.parse(grid.dataset.target);
        var doneEl = $('#countdown-done');
        var num = {
            hari: grid.querySelector('[data-cd="hari"]'),
            jam: grid.querySelector('[data-cd="jam"]'),
            menit: grid.querySelector('[data-cd="menit"]'),
            detik: grid.querySelector('[data-cd="detik"]')
        };
        var timer = null;

        var tick = function () {
            if (isNaN(targetMs)) { grid.hidden = true; if (timer) clearInterval(timer); return; }
            var diff = targetMs - Date.now();
            if (diff <= 0) {
                grid.hidden = true;
                if (doneEl) doneEl.hidden = false;
                if (timer) clearInterval(timer);
                return;
            }
            var s = Math.floor(diff / 1000);
            num.hari.textContent = Math.floor(s / 86400);
            num.jam.textContent = Math.floor((s % 86400) / 3600);
            num.menit.textContent = Math.floor((s % 3600) / 60);
            num.detik.textContent = s % 60;
        };
        timer = setInterval(tick, 1000);
        tick();
    }

    /* ---- Reveal saat scroll ---- */
    if ('IntersectionObserver' in window) {
        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (e) {
                if (e.isIntersecting) {
                    e.target.classList.add('in-view');
                    io.unobserve(e.target);
                }
            });
        }, { threshold: 0.12 });
        document.querySelectorAll('[data-reveal]').forEach(function (el) { io.observe(el); });
    } else {
        document.querySelectorAll('[data-reveal]').forEach(function (el) { el.classList.add('in-view'); });
    }

    /* ---- Salin rekening ---- */
    document.querySelectorAll('.btn-copy').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var teks = btn.dataset.copy || '';
            var selesai = function () {
                var asli = btn.textContent;
                btn.textContent = 'Tersalin ✓';
                btn.classList.add('copied');
                setTimeout(function () {
                    btn.textContent = asli;
                    btn.classList.remove('copied');
                }, 1800);
            };
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(teks).then(selesai, selesai);
            } else {
                var ta = document.createElement('textarea');
                ta.value = teks;
                document.body.appendChild(ta);
                ta.select();
                try { document.execCommand('copy'); selesai(); } catch (e) { /* abaikan */ }
                document.body.removeChild(ta);
            }
        });
    });

    /* ---- RSVP ---- */
    var form = $('#rsvp-form');
    var list = $('#ucapan-list');
    var HADIR_LABEL = { hadir: 'Hadir', tidak: 'Berhalangan', ragu: 'Belum Pasti' };

    // Semua teks dari user/API dirender via textContent — aman XSS.
    function itemUcapan(u) {
        var wrap = document.createElement('article');
        wrap.className = 'ucapan-item';

        var head = document.createElement('div');
        head.className = 'ucapan-head';
        var nm = document.createElement('span');
        nm.className = 'ucapan-nama';
        nm.textContent = u.nama || 'Tamu';
        var jenis = HADIR_LABEL[u.hadir] ? u.hadir : 'ragu';
        var badge = document.createElement('span');
        badge.className = 'badge badge-' + jenis;
        badge.textContent = HADIR_LABEL[jenis];
        head.appendChild(nm);
        head.appendChild(badge);
        wrap.appendChild(head);

        if (u.pesan) {
            var p = document.createElement('p');
            p.className = 'ucapan-pesan';
            p.textContent = u.pesan;
            wrap.appendChild(p);
        }
        if (u.waktu) {
            var w = document.createElement('span');
            w.className = 'ucapan-waktu';
            w.textContent = u.waktu;
            wrap.appendChild(w);
        }
        return wrap;
    }

    function muatUcapan() {
        if (!list || !cfg.restUrl || !cfg.id) return;
        fetch(cfg.restUrl + '/rsvp/' + cfg.id)
            .then(function (r) { return r.ok ? r.json() : []; })
            .then(function (items) {
                list.textContent = '';
                (items || []).forEach(function (u) { list.appendChild(itemUcapan(u)); });
            })
            .catch(function () { /* daftar ucapan bukan konten kritis */ });
    }
    muatUcapan();

    if (form) {
        form.addEventListener('submit', function (ev) {
            ev.preventDefault();
            var btn = $('#rsvp-submit');
            var msg = $('#rsvp-msg');
            var data = {
                undangan_id: cfg.id,
                nama: ($('#rsvp-nama').value || '').trim(),
                hadir: $('#rsvp-hadir').value,
                pesan: ($('#rsvp-pesan').value || '').trim(),
                website: form.website.value // honeypot — manusia selalu kosong
            };
            if (!data.nama) return;

            btn.disabled = true;
            msg.classList.remove('error');
            msg.textContent = 'Mengirim…';

            fetch(cfg.restUrl + '/rsvp', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            }).then(function (r) {
                if (r.status === 429) throw new Error('Terlalu cepat — tunggu sebentar lalu coba lagi.');
                if (!r.ok) throw new Error('Gagal mengirim, coba lagi.');
                return r.json();
            }).then(function () {
                msg.textContent = 'Terima kasih, ucapan Anda terkirim ✓';
                // Daftar di server ter-cache 60 dtk — tampilkan kiriman sendiri langsung.
                if (list) {
                    list.prepend(itemUcapan({ nama: data.nama, pesan: data.pesan, hadir: data.hadir, waktu: '' }));
                }
                form.reset();
            }).catch(function (err) {
                msg.classList.add('error');
                msg.textContent = err.message;
            }).finally(function () {
                btn.disabled = false;
            });
        });
    }
})();
