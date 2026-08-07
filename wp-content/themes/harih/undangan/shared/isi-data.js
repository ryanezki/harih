/**
 * hariH — Form Isi Data (page-isi-data.php).
 * Konfigurasi dari PHP: window.ISI_DATA = { webhook, maxFoto, maxSizeMB }.
 *
 * Kompresi gambar client-side (T2.8): re-encode via canvas sebelum upload.
 * Menyelesaikan sekaligus: HEIC iPhone (iOS auto-transcode ke JPEG untuk
 * accept jpeg/png/webp), payload >16 MB, orientasi EXIF (browser modern
 * menerapkan orientasi saat drawImage), upload lambat di seluler — dan
 * BONUS privasi: metadata EXIF (termasuk lokasi GPS) ikut terbuang.
 *
 * Submit (T2.10): XHR multipart langsung ke webhook n8n dengan progress bar.
 * Field file TIDAK diberi atribut name — file mentah tidak pernah ikut
 * FormData(form); yang dikirim hanya hasil kompresi (foto_0…foto_9, qris).
 */
(function () {
    'use strict';

    var cfg = window.ISI_DATA || {};
    var $ = function (sel) { return document.querySelector(sel); };

    var form = $('#isi-data-form');
    if (!form) return;

    var state = { foto: [], qris: null, uploading: false };

    /* ================= Kompresi gambar ================= */

    function loadImage(url) {
        return new Promise(function (resolve, reject) {
            var img = new Image();
            img.onload = function () { resolve(img); };
            img.onerror = reject;
            img.src = url;
        });
    }

    function toBlob(canvas, type, quality) {
        return new Promise(function (resolve, reject) {
            canvas.toBlob(function (b) { b ? resolve(b) : reject(new Error('encode gagal')); }, type, quality);
        });
    }

    /* ================= Ambang resolusi foto (F4.2) =================

       Kenapa ada: foto pertama galeri BUKAN cuma tampil di undangan — ia juga
       jadi kartu og:image 1200×630 yang muncul tiap kali pemesan menyebarkan
       undangannya (FU.1). Foto kecil berarti undangan buram DAN preview
       WhatsApp buram, di pengganda klik paling langsung yang kita punya.

       Penyebab paling sering di Indonesia bukan kamera jelek — melainkan foto
       yang DITERIMA LEWAT WHATSAPP lalu diteruskan lagi: WhatsApp mengecilkan
       gambar tiap kali. Karena itu pesannya menyebut sebabnya, bukan sekadar
       "foto terlalu kecil" — pemesan perlu tahu harus minta apa ke fotografer.

       Ditolak di titik unggah (bukan di tahap proof) sesuai F4.2: makin telat
       ketahuan, makin mahal. Dua ambang, bukan satu — menolak semua yang tidak
       ideal akan menjebak pemesan yang memang hanya punya foto seadanya. */
    var MIN_SISI_TOLAK = 640;   // di bawah ini tidak layak cetak maupun layar
    var MIN_SISI_IDEAL = 900;   // di bawah ini masih dipakai, tapi diberi tahu

    function pesanResolusi(nama, w, h) {
        return 'Foto "' + nama + '" terlalu kecil (' + w + '×' + h + ' piksel). '
             + 'Biasanya ini foto yang diterima lewat WhatsApp — WhatsApp mengecilkan gambar. '
             + 'Minta file aslinya ke fotografer (lewat Google Drive/email), atau pilih dari galeri HP yang memotretnya.';
    }

    /**
     * Re-encode gambar: skala maksimal maxDim px, format keluaran `type`.
     * JPEG untuk foto; PNG untuk QRIS (kode QR rusak oleh artefak JPEG).
     */
    async function kompres(file, maxDim, type, namaBaru, minSisiPendek) {
        var url = URL.createObjectURL(file);
        try {
            var img = await loadImage(url);
            var w = img.naturalWidth, h = img.naturalHeight;
            if (!w || !h) throw new Error('gambar tidak terbaca');

            // Ambang resolusi (F4.2). Hanya untuk FOTO — QRIS sengaja tidak
            // dibatasi karena kode QR yang sah memang bisa kecil.
            if (minSisiPendek && Math.min(w, h) < minSisiPendek) {
                var e = new Error('resolusi rendah');
                e.kode = 'RESOLUSI';
                e.lebar = w;
                e.tinggi = h;
                throw e;
            }

            var scale = Math.min(1, maxDim / Math.max(w, h));
            var canvas = document.createElement('canvas');
            canvas.width = Math.round(w * scale);
            canvas.height = Math.round(h * scale);
            var ctx = canvas.getContext('2d');
            ctx.drawImage(img, 0, 0, canvas.width, canvas.height);

            var blob = await toBlob(canvas, type, 0.82);
            if (type === 'image/jpeg' && blob.size > 900 * 1024) {
                blob = await toBlob(canvas, type, 0.65); // foto sangat detail → turunkan kualitas
            }
            var ext = type === 'image/png' ? '.png' : '.jpg';
            var keluar = new File([blob], namaBaru + ext, { type: type });
            // Ukuran ASLI (sebelum diskalakan) — dipakai pemanggil untuk
            // memutuskan apakah perlu peringatan lunak.
            keluar.asliLebar = w;
            keluar.asliTinggi = h;
            return keluar;
        } finally {
            URL.revokeObjectURL(url);
        }
    }

    /* ================= Galeri foto ================= */

    var inputFoto = $('#input-foto');
    var fotoGrid = $('#foto-grid');
    var pesanFoto = $('#pesan-foto');

    function renderFoto() {
        if (!fotoGrid) return;
        fotoGrid.textContent = '';
        state.foto.forEach(function (item, i) {
            fotoGrid.appendChild(buatThumb(item, function () {
                URL.revokeObjectURL(item.url);
                state.foto.splice(i, 1);
                renderFoto();
            }));
        });
    }

    function buatThumb(item, onHapus) {
        var wrap = document.createElement('div');
        wrap.className = 'foto-item';
        var img = document.createElement('img');
        img.src = item.url;
        img.alt = '';
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'foto-hapus';
        btn.setAttribute('aria-label', 'Hapus foto');
        btn.textContent = '×';
        btn.addEventListener('click', onHapus);
        wrap.appendChild(img);
        wrap.appendChild(btn);
        return wrap;
    }

    if (inputFoto) {
        inputFoto.addEventListener('change', async function () {
            var files = Array.from(inputFoto.files || []);
            if (pesanFoto) pesanFoto.textContent = '';

            for (var i = 0; i < files.length; i++) {
                var f = files[i];
                if (state.foto.length >= cfg.maxFoto) {
                    if (pesanFoto) pesanFoto.textContent = 'Maksimal ' + cfg.maxFoto + ' foto.';
                    break;
                }
                if (!/^image\//.test(f.type)) {
                    if (pesanFoto) pesanFoto.textContent = 'File bukan gambar: ' + f.name;
                    continue;
                }
                try {
                    if (pesanFoto) pesanFoto.textContent = 'Memproses foto ' + (i + 1) + '/' + files.length + '…';
                    var hasil = await kompres(f, 1600, 'image/jpeg', 'foto-' + Date.now() + '-' + i, MIN_SISI_TOLAK);
                    if (hasil.size > cfg.maxSizeMB * 1024 * 1024) {
                        if (pesanFoto) pesanFoto.textContent = 'Foto terlalu besar setelah dikompresi: ' + f.name;
                        continue;
                    }
                    state.foto.push({ file: hasil, url: URL.createObjectURL(hasil) });
                    renderFoto();
                    if (pesanFoto) {
                        pesanFoto.classList.remove('pesan-lunak');
                        var pendek = Math.min(hasil.asliLebar || 0, hasil.asliTinggi || 0);
                        if (pendek && pendek < MIN_SISI_IDEAL) {
                            pesanFoto.classList.add('pesan-lunak');
                            pesanFoto.textContent = 'Foto "' + f.name + '" resolusinya pas-pasan ('
                                + hasil.asliLebar + '×' + hasil.asliTinggi + ') — tetap kami pakai, '
                                + 'tapi hasilnya akan terlihat lebih lembut. File asli dari fotografer akan jauh lebih tajam.';
                        } else {
                            pesanFoto.textContent = '';
                        }
                    }
                } catch (e) {
                    if (pesanFoto) pesanFoto.textContent = e && e.kode === 'RESOLUSI'
                        ? pesanResolusi(f.name, e.lebar, e.tinggi)
                        : 'Gagal memproses ' + f.name + ' — coba foto lain.';
                    if (pesanFoto) pesanFoto.classList.remove('pesan-lunak');
                }
            }
            inputFoto.value = '';
        });
    }

    /* ================= QRIS ================= */

    var inputQris = $('#input-qris');
    var qrisGrid = $('#qris-grid');
    var pesanQris = $('#pesan-qris');

    function renderQris() {
        if (!qrisGrid) return;
        qrisGrid.textContent = '';
        if (state.qris) {
            qrisGrid.appendChild(buatThumb(state.qris, function () {
                URL.revokeObjectURL(state.qris.url);
                state.qris = null;
                renderQris();
            }));
        }
    }

    if (inputQris) {
        inputQris.addEventListener('change', async function () {
            var f = inputQris.files && inputQris.files[0];
            if (pesanQris) pesanQris.textContent = '';
            if (!f) return;
            if (!/^image\//.test(f.type)) {
                if (pesanQris) pesanQris.textContent = 'File bukan gambar.';
                return;
            }
            try {
                // PNG: kode QR harus tetap tajam agar bisa dipindai
                var hasil = await kompres(f, 1200, 'image/png', 'qris-' + Date.now());
                if (state.qris) URL.revokeObjectURL(state.qris.url);
                state.qris = { file: hasil, url: URL.createObjectURL(hasil) };
                renderQris();
            } catch (e) {
                if (pesanQris) pesanQris.textContent = 'Gagal memproses gambar QRIS.';
            }
            inputQris.value = '';
        });
    }

    /* ================= Counter love story ================= */

    var ls = $('#love-story');
    var lsCount = $('#ls-count');
    if (ls && lsCount) {
        ls.addEventListener('input', function () { lsCount.textContent = ls.value.length; });
    }

    /* ================= Submit ================= */

    var btnKirim = $('#btn-kirim');
    var progress = $('#progress');
    var progressBar = $('#progress-bar');
    var kirimMsg = $('#kirim-msg');
    var fields = $('#form-fields');

    function tampilPesan(teks, error) {
        kirimMsg.textContent = teks;
        kirimMsg.classList.toggle('error', !!error);
    }

    window.addEventListener('beforeunload', function (e) {
        if (state.uploading) { e.preventDefault(); e.returnValue = ''; }
    });

    /* ---- Contoh isi Kisah Kami (evaluasi owner: sediakan template) ---- */
    (function () {
        var CONTOH = {
            linimasa: 'Awal 2025 \u2014 Pertama bertemu lewat teman bersama\n' +
                      'Juni 2025 \u2014 Perjalanan pertama berdua\n' +
                      'Desember 2025 \u2014 Lamaran di hadapan kedua keluarga\n' +
                      '2026 \u2014 Hari yang kami tunggu',
            paragraf: 'Kami dipertemukan oleh teman yang sama pada awal tahun ini. ' +
                      'Tidak butuh waktu lama untuk yakin, dan dengan restu kedua keluarga, ' +
                      'kami memutuskan melanjutkannya ke jenjang pernikahan.'
        };
        var ta = document.getElementById('love-story');
        document.querySelectorAll('[data-isi-kisah]').forEach(function (b) {
            b.addEventListener('click', function () {
                if (!ta) return;
                var isi = CONTOH[b.getAttribute('data-isi-kisah')];
                if (ta.value.trim() !== '' && !confirm('Ganti isi Kisah Kami dengan contoh ini?')) return;
                ta.value = isi;
                ta.dispatchEvent(new Event('input', { bubbles: true })); // penghitung karakter ikut segar
                ta.focus();
            });
        });
    })();

    form.addEventListener('submit', function (ev) {
        ev.preventDefault();
        if (state.uploading) return;

        if (!form.reportValidity()) return; // validasi native (required, url, dsb.)
        if (!cfg.webhook) {
            tampilPesan('Formulir belum terhubung ke sistem — hubungi CS.', true);
            return;
        }

        // Rakit payload SEBELUM fieldset di-disable (fieldset disabled = field
        // tidak ikut FormData).
        var fd = new FormData(form);
        state.foto.forEach(function (item, i) { fd.append('foto_' + i, item.file); });
        fd.append('jumlah_foto', String(state.foto.length));
        if (state.qris) fd.append('qris', state.qris.file);

        state.uploading = true;
        fields.disabled = true;
        btnKirim.disabled = true;
        progress.hidden = false;
        progressBar.style.width = '0%';
        tampilPesan('Mengunggah… jangan tutup halaman ini.');

        var xhr = new XMLHttpRequest();
        xhr.open('POST', cfg.webhook);
        xhr.timeout = 180000;
        // Jangan set Content-Type manual — browser mengisi boundary multipart,
        // dan tanpa header custom request tetap "simple" (tanpa preflight CORS).

        xhr.upload.onprogress = function (e) {
            if (e.lengthComputable) {
                var pct = Math.round((e.loaded / e.total) * 100);
                progressBar.style.width = pct + '%';
                tampilPesan(pct < 100 ? 'Mengunggah… ' + pct + '%' : 'Memproses… sebentar lagi.');
            }
        };

        function gagal(teks) {
            state.uploading = false;
            fields.disabled = false;
            btnKirim.disabled = false;
            progress.hidden = true;
            tampilPesan(teks, true);
        }

        xhr.onload = function () {
            if (xhr.status >= 200 && xhr.status < 300) {
                state.uploading = false;
                progressBar.style.width = '100%';
                form.hidden = true;
                var sukses = $('#panel-sukses');
                if (sukses) sukses.hidden = false;
                window.scrollTo({ top: 0, behavior: 'smooth' });
            } else {
                var pesan = 'Gagal mengirim (kode ' + xhr.status + '). Coba lagi — isian Anda tidak hilang.';
                try {
                    var body = JSON.parse(xhr.responseText);
                    if (body && body.message) pesan = body.message;
                } catch (e) { /* respons bukan JSON */ }
                gagal(pesan);
            }
        };
        xhr.onerror = function () {
            gagal('Koneksi terputus — periksa jaringan lalu tekan Kirim lagi. Isian Anda tidak hilang.');
        };
        xhr.ontimeout = function () {
            gagal('Waktu unggah habis — coba jaringan yang lebih stabil lalu kirim lagi.');
        };

        xhr.send(fd);
    });
})();
