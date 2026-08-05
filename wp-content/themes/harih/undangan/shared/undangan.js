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

    /* ---- Tanggal Hijriah (Intl bawaan browser — kalender islamic-umalqura) ---- */
    (function () {
        var els = document.querySelectorAll('[data-hijriah]');
        if (!els.length) return;
        try {
            var fmt = new Intl.DateTimeFormat('id-ID-u-ca-islamic-umalqura', { day: 'numeric', month: 'long', year: 'numeric' });
            els.forEach(function (el) {
                var t = new Date(el.getAttribute('data-hijriah') + 'T12:00:00+07:00');
                if (isNaN(t)) return;
                var teks = fmt.format(t);
                el.textContent = /H$/.test(teks) ? teks : teks + ' H';
            });
        } catch (e) { /* browser tanpa kalender islamic → elemen tetap kosong & tersembunyi */ }
    })();

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
    var parallaxEls = [];
    document.querySelectorAll('.hero-arch-foto img, [data-parallax]').forEach(function (el) { parallaxEls.push(el); });
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
            // Parallax halus: hero arch + foto kisah ([data-parallax=px]).
            if (!reduced) {
                parallaxEls.forEach(function (el) {
                    var wrap = el.parentElement;
                    var r = wrap.getBoundingClientRect();
                    if (r.bottom < 0 || r.top > window.innerHeight) return;
                    var kuat = parseFloat(el.getAttribute('data-parallax')) || 30;
                    var prog = (window.innerHeight - r.top) / (window.innerHeight + r.height);
                    el.style.translate = '0 ' + ((prog - .5) * kuat).toFixed(1) + 'px';
                });
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
        var calBtn = $('.cd-cal-baris'); // baris berisi tombol Google + .ics
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

    /* ---- Slider galeri: dots tersinkron dengan posisi geser ---- */
    (function () {
        var slider = $('#galeri-slider');
        var dotsWrap = $('#galeri-dots');
        if (!slider || !dotsWrap) return;
        var slides = slider.querySelectorAll('.galeri-slide');
        if (slides.length < 2) { dotsWrap.hidden = true; return; }
        var dots = [];
        slides.forEach(function (_, i) {
            var b = document.createElement('button');
            b.type = 'button';
            b.setAttribute('aria-label', 'Foto ' + (i + 1));
            if (i === 0) b.classList.add('aktif');
            b.addEventListener('click', function () {
                slider.scrollTo({ left: i * slider.clientWidth, behavior: reduced ? 'auto' : 'smooth' });
                setAktif(i);
            });
            dotsWrap.appendChild(b);
            dots.push(b);
        });
        // Sinkron dot via IntersectionObserver pada tiap slide (root = slider):
        // tidak bergantung event scroll — andal untuk swipe jari, klik dot,
        // maupun scroll programatik.
        function setAktif(idx) {
            dots.forEach(function (d, i) { d.classList.toggle('aktif', i === idx); });
        }
        if ('IntersectionObserver' in window) {
            var sio = new IntersectionObserver(function (entries) {
                entries.forEach(function (en) {
                    if (en.isIntersecting) setAktif(Array.prototype.indexOf.call(slides, en.target));
                });
            }, { root: slider, threshold: .6 });
            slides.forEach(function (sl) { sio.observe(sl); });
        }
        // Fallback di luar IO: setelah jari lepas (swipe) atau scroll berhenti,
        // hitung indeks dari scrollLeft. Menjamin dot sinkron di engine mana pun.
        function sinkronDariScroll() {
            setTimeout(function () {
                setAktif(Math.round(slider.scrollLeft / slider.clientWidth));
            }, 380); // tunggu snap menetap
        }
        slider.addEventListener('touchend', sinkronDariScroll, { passive: true });
        slider.addEventListener('pointerup', sinkronDariScroll, { passive: true });
        if ('onscrollend' in slider) slider.addEventListener('scrollend', function () {
            setAktif(Math.round(slider.scrollLeft / slider.clientWidth));
        });
    })();

    /* ---- Lightbox galeri ---- */
    (function () {
        var imgs = Array.prototype.slice.call(document.querySelectorAll('.galeri-slide img, .galeri-grid img'));
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

    /* ---- Facade live streaming: iframe berat dimuat saat DIKLIK ---- */
    (function () {
        var f = document.querySelector('.video-frame-dalam[data-yt]');
        if (!f) return;
        function muat() {
            var id = f.getAttribute('data-yt');
            var ifr = document.createElement('iframe');
            ifr.src = 'https://www.youtube-nocookie.com/embed/' + id + '?autoplay=1';
            ifr.title = 'Live streaming';
            ifr.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture';
            ifr.allowFullscreen = true;
            f.textContent = '';
            f.appendChild(ifr);
            f.style.cursor = 'default';
        }
        f.addEventListener('click', muat, { once: true });
        f.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); muat(); }
        }, { once: true });
    })();

    /* ---- Facade peta: iframe Google dimuat saat DIKLIK, bukan saat load ---- */
    (function () {
        var petas = document.querySelectorAll('.peta-facade[data-peta]');
        if (!petas.length) return;
        petas.forEach(function (f) {
            var muat = function () {
                var q = f.getAttribute('data-peta');
                var ifr = document.createElement('iframe');
                ifr.src = 'https://maps.google.com/maps?q=' + encodeURIComponent(q) + '&z=16&output=embed';
                ifr.title = 'Peta ' + q;
                ifr.loading = 'lazy';
                ifr.referrerPolicy = 'no-referrer-when-downgrade';
                f.textContent = '';
                f.classList.add('peta-aktif');
                f.removeAttribute('role');
                f.removeAttribute('tabindex');
                f.appendChild(ifr);
            };
            f.addEventListener('click', muat, { once: true });
            f.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); muat(); }
            }, { once: true });
        });
    })();

    /* ---- Pill kehadiran RSVP (menggantikan dropdown) ---- */
    (function () {
        function grupPill(hiddenSel, btnSel, attr) {
            var hidden = $(hiddenSel);
            var pills = document.querySelectorAll(btnSel);
            if (!hidden || !pills.length) return;
            pills.forEach(function (b) {
                b.addEventListener('click', function () {
                    hidden.value = b.getAttribute(attr);
                    pills.forEach(function (x) { x.classList.toggle('aktif', x === b); });
                });
            });
        }
        grupPill('#rsvp-hadir', '.hadir-btn[data-hadir]', 'data-hadir');
        grupPill('#rsvp-sesi', '.sesi-btn', 'data-sesi');
    })();

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
        var SESI_LABEL = { akad: 'Akad', resepsi: 'Resepsi', keduanya: 'Akad & Resepsi' };
        var info = [];
        if (u.jumlah > 1) info.push(u.jumlah + ' tamu');
        if (u.hadir === 'hadir' && SESI_LABEL[u.sesi]) info.push(SESI_LABEL[u.sesi]);
        if (info.length) {
            var inf = document.createElement('span');
            inf.className = 'ucapan-info';
            inf.textContent = '· ' + info.join(' · ');
            head.appendChild(inf);
        }
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
                jumlah: parseInt(($('#rsvp-jumlah') || {}).value || '1', 10),
                sesi: ($('#rsvp-sesi') || {}).value || 'keduanya',
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
                    list.prepend(itemUcapan({ nama: data.nama, pesan: data.pesan, hadir: data.hadir, jumlah: data.jumlah, sesi: data.sesi, waktu: '' }));
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
