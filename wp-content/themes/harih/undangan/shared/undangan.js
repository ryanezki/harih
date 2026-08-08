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
    var qs = new URLSearchParams(location.search);
    var nama = qs.get('to');
    if (nama) {
        document.querySelectorAll('.guest-name').forEach(function (el) { el.textContent = nama; });

        /* U18 — nama yang SUDAH kita ketahui berhenti diminta lagi.
           `.guest-name` cuma ada satu, di dalam gerbang; begitu gerbang naik
           nama itu hilang dari layar dan `#rsvp-nama` dibiarkan kosong. Tamu
           mengetik ulang, dan mempelai menerima ejaan yang tidak cocok dengan
           daftar undangannya — padahal sisi pembeli membangun tautan personal
           per tamu di tiga titik page-tamu.php. Tetap bisa disunting: ini
           mengisi, bukan mengunci. Aman terhadap cache halaman karena semuanya
           sisi klien, sama seperti pola `?to=` itu sendiri.

           Diset langsung, bukan lewat DOMContentLoaded: skrip ini dimuat di
           footer sehingga form-nya sudah terurai — persis seperti baris
           `.guest-name` di atas yang juga menanyai DOM seketika. */
        var fNama = document.getElementById('rsvp-nama');
        if (fNama && !fNama.value) fNama.value = nama;
    }

    /* ---- Pratinjau "coba nama kalian" (G1.3) — HANYA di undangan demo ----

       Calon pembeli mengetik namanya di beranda dan langsung melihatnya di
       dalam produk. Semuanya sisi klien: tidak ada yang disimpan, tidak ada
       permintaan tambahan, dan cache halaman tetap satu untuk semua orang —
       pola yang sama dengan ?to= di atas.

       Dibatasi ke demo (window.UNDANGAN.demo, diisi server dari meta order_id)
       supaya nama di undangan PELANGGAN tidak bisa diubah lewat URL oleh siapa
       pun yang memegang tautannya. Pembatasan yang sama sudah dipakai pemilih
       nuansa `?nuansa=`.

       textContent, bukan innerHTML — nama datang dari input publik. */
    if (window.UNDANGAN && window.UNDANGAN.demo) {
        (function () {
            var pria   = (qs.get('pria')   || '').trim().slice(0, 20);
            var wanita = (qs.get('wanita') || '').trim().slice(0, 20);
            var tgl    = qs.get('tgl') || '';
            if (!pria && !wanita) return;

            // Nama tampil di dua tempat: panel gerbang & hero. Keduanya memakai
            // struktur "<nama> <span class=amp>&</span> <nama>", jadi yang boleh
            // diganti cuma simpul teksnya — span ampersand dipertahankan supaya
            // gaya italic display-nya tidak hilang.
            document.querySelectorAll('.cover-names').forEach(function (el) {
                var amp = el.querySelector('.amp');
                if (!amp) return;
                var teks = [];
                el.childNodes.forEach(function (n) { if (n.nodeType === 3) teks.push(n); });
                if (teks.length >= 2) {
                    if (pria)   teks[0].textContent = pria + ' ';
                    if (wanita) teks[teks.length - 1].textContent = ' ' + wanita;
                }
            });

            // Tanggal: dirender ulang di klien dengan locale id-ID. Hijriah
            // sengaja TIDAK ikut diubah — blok itu punya perhitungannya sendiri
            // dari data undangan, dan menyesatkan kalau setengah tersinkron.
            if (/^\d{4}-\d{2}-\d{2}$/.test(tgl)) {
                var d = new Date(tgl + 'T12:00:00+07:00');
                if (!isNaN(d)) {
                    var f = new Intl.DateTimeFormat('id-ID', {
                        weekday: 'long', day: 'numeric', month: 'long', year: 'numeric'
                    }).format(d);
                    document.querySelectorAll('.cover-date, .hero-tanggal p').forEach(function (el) {
                        el.textContent = f;
                    });
                }
            }

            // Kapsul penanda: pengunjung harus tahu ini pratinjau, bukan
            // undangannya yang sudah jadi. Berpasangan dengan pemilih nuansa
            // yang sudah ada di kiri-bawah, jadi ditaruh di atas.
            var tanda = document.createElement('p');
            tanda.className = 'pratinjau-nama';
            tanda.textContent = 'pratinjau — nama kalian di tema ini';
            document.body.appendChild(tanda);
        })();
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
            if (musicBtn) {
                musicBtn.hidden = false;
                musicBtn.classList.add('playing');
                musicBtn.classList.remove('paused');
            }
        }).catch(function () {
            if (musicBtn) {
                musicBtn.hidden = false;
                musicBtn.classList.add('paused');
                musicBtn.classList.remove('playing');
            }
        });
    }

    if (musicBtn) {
        musicBtn.addEventListener('click', function () {
            if (!audio) return;
            if (audio.paused) {
                // U11 — lewat playMusic() supaya volume & penanganan penolakan
                // autoplay tetap di satu tempat; sebelumnya cabang ini memanggil
                // audio.play() sendiri dan melewatkan keduanya.
                playMusic();
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

    /* ---- U12: bilah lompat + gerbang tidak diulang dalam satu sesi ----

       Diukur di demo live 360x740: `#acara` — yang memuat ALAMAT, JAM, dan peta —
       ada di 2.977 px dari puncak, yaitu 4,0 layar penuh, pada dokumen setinggi
       9.125 px (12,3 layar). Dan seluruh dokumen hanya memuat sembilan tag <a>,
       satu-satunya ber-href "#" adalah placeholder RSVP-WA yang bawaannya
       hidden: NOL tautan lompat. Tamu yang membuka undangan di depan gedung
       untuk memastikan alamatnya harus menggulir empat layar — setelah menekan
       gerbang lagi, karena tidak ada yang mengingat ia sudah membukanya.

       Jangkarnya memakai id yang SUDAH ADA di markup, jadi urutan section tidak
       berubah sama sekali — ini menambah jalan pintas, bukan menata ulang. */
    var KUNCI_GERBANG = 'harih-gerbang-' + (cfg.slug || cfg.id || '');

    function bangunBilahAksi() {
        if (document.querySelector('.bilah-aksi')) return;
        var titik = [['#acara', 'Acara'], ['#galeri', 'Galeri'], ['#amplop', 'Amplop'], ['#rsvp', 'RSVP']]
            .filter(function (t) { return document.querySelector(t[0]); });
        if (titik.length < 2) return;   // satu tautan bukan navigasi
        var nav = document.createElement('nav');
        nav.className = 'bilah-aksi';
        nav.setAttribute('aria-label', 'Lompat ke bagian undangan');
        titik.forEach(function (t) {
            var a = document.createElement('a');
            a.href = t[0];
            a.textContent = t[1];
            nav.appendChild(a);
        });
        document.body.appendChild(nav);
        document.body.classList.add('ada-bilah');
    }

    function sesudahGerbang() {
        if (musicBtn) { musicBtn.hidden = false; musicBtn.classList.add('paused'); }
        initProgress();
        bangunBilahAksi();
    }

    // Kunjungan berikutnya DALAM SESI YANG SAMA melewati gerbang. Sengaja
    // sessionStorage, bukan localStorage: kesan pertama tetap utuh untuk
    // kunjungan baru, yang dihemat hanya pengulangan di hari yang sama.
    var lewatiGerbang = false;
    try { lewatiGerbang = sessionStorage.getItem(KUNCI_GERBANG) === '1'; } catch (e) { /* mode privat */ }
    if (gate && lewatiGerbang) {
        gate.classList.add('lewati', 'terbuka');
        document.body.classList.remove('is-locked');
        sesudahGerbang();
    }

    if (openBtn) {
        // C1c — penanda "gerbang BISA dibuka", bukan sekadar "JS jalan".
        // Dipasang tepat setelah handler terpasang; arloji penjaga di
        // single-undangan.php membuka paksa bila penanda ini tak pernah datang.
        document.body.classList.add('js-siap');
        openBtn.addEventListener('click', function () {
            if (gate) gate.classList.add('terbuka');
            document.body.classList.remove('is-locked');
            /* U11 — MUSIK TIDAK LAGI DIUNDUH TANPA DITANYA.
               Dulu menekan gerbang memanggil playMusic() seketika, dan tombol
               kontrolnya baru muncul DI DALAM playMusic() — jadi urutannya:
               tamu menekan → audio.play() → unduhan 1,8–2,2 MB mulai → barulah
               kontrolnya terlihat. Terukur dari server: 1.908.919 · 1.781.026 ·
               2.214.866 byte, sementara satu pemuatan undangan tanpa MP3 hanya
               ±796 KB. Biaya itu ditanggung RATUSAN tamu yang tidak memesan apa
               pun, di pasar yang kuotanya jadi keberatan nyata.
               Sekarang: tombolnya ditampilkan dalam keadaan `paused`, dan satu
               ketukan lagi memutarnya. `preload="none"` di markup memastikan
               nol byte berpindah sampai itu terjadi. */
            sesudahGerbang();
            try { sessionStorage.setItem(KUNCI_GERBANG, '1'); } catch (e) { /* mode privat */ }
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
        /* U17 — amplop tertutup memakai `grid-template-rows: 0fr` + opacity 0,
           tanpa `hidden`/`visibility`/`inert`. Isinya tetap di urutan tab: dua
           kontrol — termasuk "Salin Nomor" — menerima fokus keyboard di dalam
           panel setinggi NOL piksel. `inert` menutupnya dalam satu atribut. */
        function segarkanInert(buka) { ampWrap.toggleAttribute('inert', !buka); }
        segarkanInert(ampWrap.classList.contains('terbuka'));
        ampBtn.addEventListener('click', function () {
            var buka = ampWrap.classList.toggle('terbuka');
            ampBtn.setAttribute('aria-expanded', buka ? 'true' : 'false');
            ampBtn.textContent = buka ? 'Tutup Amplop' : 'Buka Amplop Digital';
            segarkanInert(buka);
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
        /* U17 — pemicunya dulu HANYA `click` pada <img>: lightbox tidak bisa
           dibuka sama sekali tanpa mouse. Gambar diberi peran tombol + urutan
           tab, dan Enter/Space membuka persis seperti klik. */
        imgs.forEach(function (img, i) {
            function buka() {
                if (!lb) bangun();
                tampil(i);
            }
            img.setAttribute('role', 'button');
            img.setAttribute('tabindex', '0');
            if (!img.getAttribute('aria-label')) {
                img.setAttribute('aria-label', 'Perbesar foto ' + (i + 1));
            }
            img.addEventListener('click', buka);
            img.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); buka(); }
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
            // U17 — keydown tidak lagi `once`, jadi muat() harus tahan
            // dipanggil dua kali; tanpa ini Enter kedua akan me-restart video.
            if (f.querySelector('iframe')) return;
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
        /* U17 — `{once:true}` DICABUT dari keydown. Opsi itu mencopot listener setiap
           kali ia DIPANGGIL, bukan setiap kali ia berhasil: satu tekan panah-bawah
           untuk menggulir sudah menghabiskan satu-satunya kesempatan, dan sesudahnya
           Enter mati permanen sementara klik mouse tetap bekerja. Listener klik boleh
           tetap `once` karena klik selalu memuat. */
        f.addEventListener('click', muat, { once: true });
        f.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); muat(); }
        });
    })();

    /* ---- Pemilih nuansa (halaman demo saja) ---- */
    (function () {
        var kotak = document.querySelector('.pratinjau-nuansa');
        if (!kotak) return;
        var pilih = $('#pilih-nuansa');
        var url = new URL(window.location.href);

        // Berpindah nuansa memuat ulang halaman; tanpa ini pengunjung harus
        // menekan "Buka Undangan" dan menggulir ulang tiap kali membandingkan.
        if (url.searchParams.get('buka') === '1') {
            var gerbang = $('#gate');
            if (gerbang) gerbang.classList.add('terbuka');
            document.body.classList.remove('is-locked');
            initProgress();
            // Foto hero baru selesai dimuat setelah frame pertama, jadi satu
            // requestAnimationFrame saja mendarat di posisi yang keburu bergeser.
            // Diulang setelah `load` supaya berhenti tepat di blok salam.
            var keSalam = function () {
                var salam = document.getElementById('salam');
                if (!salam) return;
                // `scroll-behavior: smooth` di <html> membuat lompatan ini
                // dianimasikan lalu tertelan animasi berikutnya — hasilnya
                // halaman tetap di puncak. Dimatikan sesaat, lalu dikembalikan.
                var akar = document.documentElement;
                var simpan = akar.style.scrollBehavior;
                akar.style.scrollBehavior = 'auto';
                window.scrollTo(0, salam.getBoundingClientRect().top + window.scrollY - 8);
                akar.style.scrollBehavior = simpan;
            };
            setTimeout(keSalam, 140);
            window.addEventListener('load', function () { setTimeout(keSalam, 80); });
        }

        kotak.hidden = false;
        pilih.addEventListener('change', function () {
            url.searchParams.set('nuansa', pilih.value);
            url.searchParams.set('buka', '1');
            window.location.href = url.toString();
        });
    })();

    /* ---- Facade peta: iframe Google dimuat saat DIKLIK, bukan saat load ---- */
    (function () {
        var petas = document.querySelectorAll('.peta-facade[data-peta]');
        if (!petas.length) return;
        petas.forEach(function (f) {
            var muat = function () {
                // U17 — keydown tidak lagi `once`, jadi muat() harus tahan
                // dipanggil dua kali; tanpa ini Enter kedua memuat ulang peta.
                if (f.querySelector('iframe')) return;
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
            /* U17 — `{once:true}` DICABUT dari keydown. Opsi itu mencopot listener setiap
               kali ia DIPANGGIL, bukan setiap kali ia berhasil: satu tekan panah-bawah
               untuk menggulir sudah menghabiskan satu-satunya kesempatan, dan sesudahnya
               Enter mati permanen sementara klik mouse tetap bekerja. Listener klik boleh
               tetap `once` karena klik selalu memuat. */
            f.addEventListener('click', muat, { once: true });
            f.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); muat(); }
            });
        });
    })();

    /* ---- Pill kehadiran RSVP (menggantikan dropdown) ---- */
    /* U15 — keadaan terpilih diekspos ke pembaca layar. Sebelumnya
       `aria-pressed`/`aria-checked`/`aria-selected` muncul NOL kali di seluruh
       tema: satu-satunya penanda adalah kelas CSS. Karena defaultnya "hadir",
       tamu berhalangan yang memakai pembaca layar bisa mengirim konfirmasi
       HADIR tanpa sadar — lalu mempelai menghitung kursi & katering dari situ.

       U18 — dan blok "Hadir Pada"/"Jumlah Tamu" disembunyikan saat tamu memilih
       Berhalangan; menanyakan sesi mana yang ia hadiri setelah ia menjawab
       tidak hadir adalah pertanyaan yang tidak punya jawaban benar. */
    var rsvpReset;   // dipasang oleh blok pil RSVP di bawah (U18)
    (function () {
        function grupPill(hiddenSel, btnSel, attr, sesudah) {
            var hidden = $(hiddenSel);
            var pills = document.querySelectorAll(btnSel);
            if (!hidden || !pills.length) return null;
            function pasang(b) {
                hidden.value = b.getAttribute(attr);
                pills.forEach(function (x) {
                    var aktif = x === b;
                    x.classList.toggle('aktif', aktif);
                    x.setAttribute('aria-pressed', aktif ? 'true' : 'false');
                });
                if (sesudah) sesudah(hidden.value);
            }
            // Keadaan awal ikut diumumkan — tanpa ini pil yang sudah aktif sejak
            // halaman dimuat tetap terbaca "tidak ditekan".
            pills.forEach(function (b) {
                b.setAttribute('aria-pressed', b.classList.contains('aktif') ? 'true' : 'false');
                b.addEventListener('click', function () { pasang(b); });
            });
            return pasang;
        }

        var barisJumlah = document.querySelector('.rsvp-jumlah-baris');
        var barisSesi   = document.querySelector('.rsvp-sesi-baris');
        function terapkan(nilai) {
            var hadir = nilai !== 'tidak';
            if (barisJumlah) barisJumlah.hidden = !hadir;
            if (barisSesi)   barisSesi.hidden   = !hadir;
        }

        grupPill('#rsvp-hadir', '.hadir-btn[data-hadir]', 'data-hadir', terapkan);
        grupPill('#rsvp-sesi', '.sesi-btn', 'data-sesi');
        terapkan(($('#rsvp-hadir') || {}).value || 'hadir');

        /* U18 — `form.reset()` membersihkan SEBAGIAN saja, dan itu terbukti di
           demo live: nama, pesan, dan jumlah kembali, tapi pil Kehadiran & Sesi
           tetap menyala pilihan orang sebelumnya. Sebabnya `type="hidden"`
           berada dalam value mode "default" — menulis `.value` ikut mengubah
           ATRIBUT-nya, sehingga reset justru memulihkan ke pilihan tadi.
           Karena itu keadaan awal disimpan sendiri, lalu dipulihkan eksplisit. */
        var awal = {};
        ['#rsvp-hadir', '#rsvp-sesi'].forEach(function (sel) {
            var el = $(sel);
            if (el) awal[sel] = el.value;
        });

        rsvpReset = function () {
            ['#rsvp-nama', '#rsvp-pesan'].forEach(function (sel) {
                var el = $(sel);
                if (el) el.value = '';
            });
            var j = $('#rsvp-jumlah');
            if (j) j.value = '1';
            Object.keys(awal).forEach(function (sel) {
                var el = $(sel);
                if (!el) return;
                el.value = awal[sel];
                var grup = sel === '#rsvp-hadir' ? '.hadir-btn[data-hadir]' : '.sesi-btn';
                var attr = sel === '#rsvp-hadir' ? 'data-hadir' : 'data-sesi';
                document.querySelectorAll(grup).forEach(function (b) {
                    var aktif = b.getAttribute(attr) === awal[sel];
                    b.classList.toggle('aktif', aktif);
                    b.setAttribute('aria-pressed', aktif ? 'true' : 'false');
                });
            });
            terapkan(awal['#rsvp-hadir'] || 'hadir');
        };
    })();

    /* ---- Salin rekening ---- */
    document.querySelectorAll('.btn-copy').forEach(function (btn) {
        /* C2 — label asli disimpan SEKALI, di luar handler klik. Sebelumnya
           dibaca tiap klik dari `btn.textContent`, jadi ketukan kedua dalam
           1800 ms menyimpan "Tersalin ✓" sebagai teks asli dan label
           tersangkut permanen. Ini tidak butuh kegagalan apa pun — cukup dua
           ketukan cepat, dan terjadi di semua peramban. */
        btn.dataset.label = btn.textContent;

        btn.addEventListener('click', function () {
            var teks = btn.dataset.copy || '';
            var pulih = function () {
                setTimeout(function () {
                    btn.textContent = btn.dataset.label;
                    btn.classList.remove('copied', 'gagal-salin');
                }, 1800);
            };
            var selesai = function () {
                btn.textContent = 'Tersalin ✓';
                btn.classList.add('copied');
                pulih();
            };
            /* C2 — kegagalan HARUS punya jalur sendiri. Sebelumnya
               `.then(selesai, selesai)`: argumen kedua itu handler PENOLAKAN,
               diisi fungsi sukses. Di WebView WhatsApp — dari mana mayoritas
               tamu Indonesia membuka undangan — `writeText` bisa menolak, dan
               justru di kondisi itu tombolnya berbohong: tamu menempel isi
               clipboard LAMA ke aplikasi m-banking, lalu mengira sudah benar. */
            var gagal = function () {
                btn.textContent = 'Gagal menyalin — tekan lama nomornya';
                btn.classList.add('gagal-salin');
                pulih();
            };
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(teks).then(selesai, gagal);
            } else {
                var ta = document.createElement('textarea');
                ta.value = teks;
                document.body.appendChild(ta);
                ta.select();
                // `execCommand` mengembalikan false saat gagal TANPA melempar,
                // jadi `try/catch` saja tidak cukup — harus periksa nilainya.
                var ok = false;
                try { ok = document.execCommand('copy'); } catch (e) { ok = false; }
                document.body.removeChild(ta);
                (ok ? selesai : gagal)();
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
        // Jumlah tamu hanya bermakna bila yang bersangkutan HADIR — tanpa
        // syarat ini buku tamu publik menampilkan "Berhalangan · 3 tamu".
        // Pemeriksaan yang sama sudah dipakai baris berikutnya untuk sesi.
        if (u.hadir === 'hadir' && u.jumlah > 1) info.push(u.jumlah + ' tamu');
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

    /* U13 — buku tamu dulu memuat SEKALI, 50 teratas, tanpa penghitung, tanpa
       muat-lagi, dan tanpa cabang kosong: ketiga undangan demo menampilkannya
       kosong melompong kepada calon pembeli yang sedang menilai produk. */
    var halUcapan = 0;
    var btnLagi = null;

    function pasangTombolLagi(sisa) {
        if (!btnLagi) {
            btnLagi = document.createElement('button');
            btnLagi.type = 'button';
            btnLagi.className = 'btn btn-ghost ucapan-lagi';
            btnLagi.addEventListener('click', function () { muatUcapan(); });
            list.parentNode.insertBefore(btnLagi, list.nextSibling);
        }
        btnLagi.hidden = sisa <= 0;
        btnLagi.textContent = 'Lihat ' + sisa + ' ucapan lainnya';
    }

    function muatUcapan() {
        if (!list || !cfg.restUrl || !cfg.slug) return;
        var berikut = halUcapan + 1;
        if (btnLagi) btnLagi.disabled = true;
        fetch(cfg.restUrl + '/rsvp/' + encodeURIComponent(cfg.slug) + '?hal=' + berikut)
            .then(function (r) { return r.ok ? r.json() : null; })
            .then(function (data) {
                if (data === null) throw new Error('gagal');
                // Menerima kedua bentuk: objek {items,total} (baru) dan array
                // polos (respons lama yang mungkin masih ter-cache 60 dtk).
                var items = Array.isArray(data) ? data : (data.items || []);
                var total = Array.isArray(data) ? data.length : (data.total || items.length);
                if (berikut === 1) list.textContent = '';
                items.forEach(function (u) { list.appendChild(itemUcapan(u)); });
                halUcapan = berikut;

                if (berikut === 1 && !items.length) {
                    var kosong = document.createElement('p');
                    kosong.className = 'ucapan-kosong';
                    kosong.textContent = 'Jadilah yang pertama mengirim doa & ucapan.';
                    list.appendChild(kosong);
                }
                if (total > list.querySelectorAll('.ucapan-item').length) {
                    pasangTombolLagi(total - list.querySelectorAll('.ucapan-item').length);
                } else if (btnLagi) {
                    btnLagi.hidden = true;
                }
            })
            .catch(function () {
                // Dibedakan dari keadaan kosong: "belum ada ucapan" dan "gagal
                // memuat" adalah dua kabar yang berbeda bagi tamu.
                if (berikut === 1 && list && !list.children.length) {
                    var g = document.createElement('p');
                    g.className = 'ucapan-kosong';
                    g.textContent = 'Ucapan gagal dimuat — coba muat ulang halaman.';
                    list.appendChild(g);
                }
            })
            .finally(function () { if (btnLagi) btnLagi.disabled = false; });
    }
    muatUcapan();

    if (form) {
        /* Konfirmasi ke WhatsApp MEMPELAI (G1.6, 2026-08-07).

           Ini jawaban kami atas ide "RSVP lewat WhatsApp" — tapi arahnya
           dibalik. Yang DITOLAK adalah tamu mengirim RSVP ke nomor bisnis
           hariH: ratusan nomor asing menghubungi satu sesi WhatsApp Web adalah
           pola yang membuat sesi di-ban, dan sesi itu memikul SELURUH kanal
           delivery (9 workflow). Yang dikerjakan: pesan berjalan dari nomor
           TAMU ke nomor PRIBADI MEMPELAI — sesi WAHA kita tidak tersentuh sama
           sekali, jadi risikonya nol. Nilainya juga lebih tepat sasaran: yang
           ingin tahu siapa yang datang memang mempelai, bukan kami.

           Tanpa endpoint baru, tanpa field baru, tanpa perubahan REST. */
        function tampilkanTombolWa(data) {
            var wa = (window.UNDANGAN && window.UNDANGAN.waCp) || '';
            if (!wa) return;                       // undangan tanpa CP: tombol tidak dirender
            var tombol = $('#rsvp-wa');
            if (!tombol) return;

            var kabar = data.hadir === 'hadir' ? 'insya Allah kami hadir'
                      : data.hadir === 'tidak' ? 'mohon maaf kami berhalangan hadir'
                      : 'kami belum bisa memastikan kehadiran';
            var jml   = data.hadir === 'hadir' && data.jumlah > 1 ? ' bersama ' + data.jumlah + ' orang' : '';
            var sesi  = data.hadir === 'hadir'
                      ? (data.sesi === 'akad' ? ' di acara akad'
                       : data.sesi === 'resepsi' ? ' di acara resepsi' : '')
                      : '';
            var teks  = 'Halo, saya ' + data.nama + '. Saya sudah mengisi konfirmasi kehadiran — '
                      + kabar + jml + sesi + '. Selamat menempuh hidup baru 🙏';

            tombol.href = 'https://wa.me/' + wa + '?text=' + encodeURIComponent(teks);
            tombol.hidden = false;
        }

        form.addEventListener('submit', function (ev) {
            ev.preventDefault();
            var btn = $('#rsvp-submit');
            var msg = $('#rsvp-msg');
            var data = {
                slug: cfg.slug,
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
                tampilkanTombolWa(data);
                rsvpReset && rsvpReset();
            }).catch(function (err) {
                msg.classList.add('error');
                msg.textContent = err.message;
            }).finally(function () {
                btn.disabled = false;
            });
        });
    }
})();
