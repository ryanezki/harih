/**
 * hariH — perilaku bersama halaman undangan (semua tema).
 * Konfigurasi dari PHP (functions.php): window.UNDANGAN = { id, restUrl, target, musik }.
 * Tanpa dependensi. Standar kualitas: gate pembuka sinematik, reveal ber-stagger,
 * countdown hidup, amplop tertutup, lightbox galeri, progress bar, partikel —
 * semuanya hormat pada prefers-reduced-motion.
 */
(function () {
    'use strict';

    var cfg = window.UNDANGAN || {};
    var $ = function (sel) { return document.querySelector(sel); };
    var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    /* ---- Nama tamu dari ?to= — client-side supaya semua tamu memakai satu
       cache halaman yang sama (keputusan A2 blueprint) ---- */
    var nama = new URLSearchParams(location.search).get('to');
    if (nama) {
        document.querySelectorAll('.guest-name').forEach(function (el) { el.textContent = nama; });
    }

    /* ---- Musik ---- */
    var audio = $('#undangan-audio');
    var musicBtn = $('#music-toggle');

    function playMusic() {
        if (!audio) return;
        audio.volume = .6;
        audio.play().then(function () {
            if (musicBtn) { musicBtn.hidden = false; musicBtn.classList.add('playing'); }
        }).catch(function () {
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

    /* ---- Gate pembuka (standar acuan §2.3): menekan tombol = gesture yang
       membuat autoplay musik legal; gate naik dan tidak kembali ---- */
    var gate = $('#gate');
    var openBtn = $('#buka-undangan');
    if (openBtn) {
        openBtn.addEventListener('click', function () {
            if (gate) gate.classList.add('terbuka');
            document.body.classList.remove('is-locked');
            playMusic();
            initProgress();
        });
    }

    /* ---- Progress scroll 2px emas (acuan §2.6) ---- */
    var progressInner = null;
    function initProgress() {
        if (progressInner) return;
        var bar = document.createElement('div');
        bar.className = 'scroll-progress';
        bar.setAttribute('aria-hidden', 'true');
        progressInner = document.createElement('i');
        bar.appendChild(progressInner);
        document.body.appendChild(bar);
        onScroll();
    }
    var heroFoto = document.querySelector('.hero-arch-foto img');
    var rafPending = false;
    function onScroll() {
        if (rafPending) return;
        rafPending = true;
        requestAnimationFrame(function () {
            rafPending = false;
            if (progressInner) {
                var max = document.documentElement.scrollHeight - window.innerHeight;
                progressInner.style.transform = 'scaleX(' + (max > 0 ? Math.min(1, window.scrollY / max) : 0) + ')';
            }
            // Parallax sangat halus pada foto hero — ken-burns tetap jalan di
            // dalam wrapper; translate diberikan pada wrapper-nya.
            if (!reduced && heroFoto) {
                var wrap = heroFoto.parentElement;
                var r = wrap.getBoundingClientRect();
                if (r.bottom > 0 && r.top < window.innerHeight) {
                    var prog = (window.innerHeight - r.top) / (window.innerHeight + r.height);
                    heroFoto.style.translate = '0 ' + ((prog - .5) * 30).toFixed(1) + 'px';
                }
            }
        });
    }
    window.addEventListener('scroll', onScroll, { passive: true });

    /* ---- Partikel emas melayang — hanya tema ber---particles:1 (tema
       sinematik); tema terang tetap tenang ---- */
    (function () {
        if (reduced) return;
        var on = getComputedStyle(document.documentElement).getPropertyValue('--particles').trim();
        if (on !== '1') return;
        var wrap = document.createElement('div');
        wrap.className = 'partikel';
        wrap.setAttribute('aria-hidden', 'true');
        var kiri = [8, 17, 28, 41, 55, 67, 79, 90];
        for (var i = 0; i < kiri.length; i++) {
            var sp = document.createElement('span');
            var uk = 2 + (i % 3);
            sp.style.left = kiri[i] + '%';
            sp.style.width = uk + 'px';
            sp.style.height = uk + 'px';
            sp.style.setProperty('--durasi', (21 + (i * 7) % 14) + 's');
            sp.style.setProperty('--tunda', '-' + ((i * 5) % 24) + 's');
            wrap.appendChild(sp);
        }
        document.body.appendChild(wrap);
    })();

    /* ---- Reveal ber-stagger + hairline tumbuh (acuan §2.1, §2.6) ---- */
    function tumbuhkan(el) {
        el.querySelectorAll('[data-grow]').forEach(function (g) {
            g.style.width = g.getAttribute('data-grow') + 'px';
        });
        if (el.hasAttribute('data-grow')) el.style.width = el.getAttribute('data-grow') + 'px';
    }
    var revealEls = document.querySelectorAll('[data-reveal]');
    if (reduced || !('IntersectionObserver' in window)) {
        revealEls.forEach(function (el) { el.classList.add('in-view'); tumbuhkan(el); });
    } else {
        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (e) {
                if (!e.isIntersecting) return;
                e.target.style.transitionDelay = (e.target.getAttribute('data-delay') || '0') + 'ms';
                e.target.classList.add('in-view');
                tumbuhkan(e.target);
                io.unobserve(e.target);
            });
        }, { threshold: 0.12, rootMargin: '0px 0px -6% 0px' });
        revealEls.forEach(function (el) { io.observe(el); });
    }

    /* ---- Countdown hidup (acuan §2.4): tiap pergantian angka dianimasikan ---- */
    var grid = $('#countdown-grid');
    if (grid && grid.dataset.target) {
        var targetMs = Date.parse(grid.dataset.target);
        var doneEl = $('#countdown-done');
        var calBtn = $('.cd-cal');
        var num = {
            hari: grid.querySelector('[data-cd="hari"]'),
            jam: grid.querySelector('[data-cd="jam"]'),
            menit: grid.querySelector('[data-cd="menit"]'),
            detik: grid.querySelector('[data-cd="detik"]')
        };
        var pad = function (v) { return String(v).padStart(2, '0'); };
        var setNum = function (el, str) {
            if (!el || el.textContent === str) return;
            el.textContent = str;
            if (!reduced && el.animate) {
                el.animate(
                    [{ opacity: .15, transform: 'translateY(-7px)' }, { opacity: 1, transform: 'none' }],
                    { duration: 450, easing: 'cubic-bezier(.2,.6,.2,1)' }
                );
            }
        };
        var timer = null;
        var tick = function () {
            if (isNaN(targetMs)) { grid.hidden = true; if (timer) clearInterval(timer); return; }
            var diff = targetMs - Date.now();
            if (diff <= 0) {
                grid.hidden = true;
                if (calBtn) calBtn.hidden = true;
                if (doneEl) doneEl.hidden = false;
                if (timer) clearInterval(timer);
                return;
            }
            var s = Math.floor(diff / 1000);
            setNum(num.hari, String(Math.floor(s / 86400)));
            setNum(num.jam, pad(Math.floor((s % 86400) / 3600)));
            setNum(num.menit, pad(Math.floor((s % 3600) / 60)));
            setNum(num.detik, pad(s % 60));
        };
        timer = setInterval(tick, 1000);
        tick();
    }

    /* ---- Amplop tertutup default (acuan §3): grid-rows 0fr→1fr ---- */
    var ampBtn = $('#amplop-toggle');
    var ampWrap = $('#amplop-wrap');
    if (ampBtn && ampWrap) {
        ampBtn.addEventListener('click', function () {
            var buka = ampWrap.classList.toggle('terbuka');
            ampBtn.setAttribute('aria-expanded', buka ? 'true' : 'false');
            ampBtn.textContent = buka ? 'Tutup Amplop' : 'Buka Amplop Digital';
        });
    }

    /* ---- Lightbox galeri ---- */
    (function () {
        var imgs = Array.prototype.slice.call(document.querySelectorAll('.galeri-grid img'));
        if (!imgs.length) return;
        var idx = 0, lb = null, lbImg = null, lbNum = null;
        function bangun() {
            lb = document.createElement('div');
            lb.className = 'lightbox';
            lb.hidden = true;
            lbImg = document.createElement('img');
            lbImg.alt = 'Foto diperbesar';
            var nav = document.createElement('div');
            nav.className = 'lightbox-nav';
            var prev = document.createElement('button');
            prev.type = 'button'; prev.textContent = '‹'; prev.setAttribute('aria-label', 'Sebelumnya');
            var next = document.createElement('button');
            next.type = 'button'; next.textContent = '›'; next.setAttribute('aria-label', 'Berikutnya');
            lbNum = document.createElement('span');
            nav.appendChild(prev); nav.appendChild(lbNum); nav.appendChild(next);
            var hint = document.createElement('p');
            hint.className = 'lightbox-hint';
            hint.textContent = 'Ketuk di mana saja untuk menutup';
            lb.appendChild(lbImg); lb.appendChild(nav); lb.appendChild(hint);
            lb.addEventListener('click', function () { lb.hidden = true; });
            prev.addEventListener('click', function (e) { e.stopPropagation(); tampil(idx - 1); });
            next.addEventListener('click', function (e) { e.stopPropagation(); tampil(idx + 1); });
            document.addEventListener('keydown', function (e) {
                if (lb.hidden) return;
                if (e.key === 'Escape') lb.hidden = true;
                if (e.key === 'ArrowLeft') tampil(idx - 1);
                if (e.key === 'ArrowRight') tampil(idx + 1);
            });
            document.body.appendChild(lb);
        }
        function tampil(i) {
            idx = (i + imgs.length) % imgs.length;
            lbImg.src = imgs[idx].src;
            lbNum.textContent = (idx + 1) + ' / ' + imgs.length;
            lb.hidden = false;
        }
        imgs.forEach(function (img, i) {
            img.addEventListener('click', function () {
                if (!lb) bangun();
                tampil(i);
            });
        });
    })();

    /* ---- Tilt 3D kartu rekening & QRIS mengikuti sentuhan (acuan §2.6) ---- */
    if (!reduced) {
        document.querySelectorAll('.rekening-item, .qris-kartu').forEach(function (kartu) {
            kartu.addEventListener('pointermove', function (e) {
                var r = kartu.getBoundingClientRect();
                var rx = ((e.clientY - r.top) / r.height - .5) * -7;
                var ry = ((e.clientX - r.left) / r.width - .5) * 9;
                kartu.style.transition = 'transform .18s ease';
                kartu.style.transform = 'perspective(700px) rotateX(' + rx.toFixed(2) + 'deg) rotateY(' + ry.toFixed(2) + 'deg)';
            });
            kartu.addEventListener('pointerleave', function () {
                kartu.style.transition = 'transform .7s cubic-bezier(.2,.6,.2,1)';
                kartu.style.transform = 'none';
            });
        });
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
