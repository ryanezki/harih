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

    // `kotor` = pemesan sudah mengetik/memilih sesuatu. Dipakai beforeunload:
    // sebelumnya peringatan hanya muncul saat SEDANG mengunggah, padahal
    // kehilangan yang paling menyakitkan justru terjadi sebelum itu — form ini
    // ±10 menit pengisian, dan menutup tab membuang semuanya tanpa sepatah kata.
    //
    // `memproses` (U1) = kompresi canvas sedang berjalan. Tanpa flag ini tombol
    // Kirim tetap hidup selama `await kompres()`, dan menekannya merakit payload
    // dari `state.foto` yang baru terisi sebagian — 10 foto dipilih, 2 terkirim,
    // pemesan melihat panel "Data diterima!". Sisanya selesai dikompresi di
    // latar, di balik form yang sudah disembunyikan.
    var state = { foto: [], qris: null, uploading: false, kotor: false, memproses: false };

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
    var tolakFoto = $('#tolak-foto');
    var fotoHitung = $('#foto-hitung');

    function renderFoto() {
        if (fotoHitung) {
            fotoHitung.textContent = state.foto.length + ' dari ' + cfg.maxFoto + ' foto terpilih';
        }
        if (!fotoGrid) return;
        fotoGrid.textContent = '';
        state.foto.forEach(function (item, i) {
            fotoGrid.appendChild(buatThumb(item, function () {
                URL.revokeObjectURL(item.url);
                state.foto.splice(i, 1);
                state.kotor = true;
                renderFoto();
            }, {
                // U9 — foto pertama menentukan sampul undangan DAN kartu preview
                // WhatsApp. Copy-nya sudah menyatakan taruhannya sejak lama, tapi
                // tidak pernah ada cara memilihnya maupun tanda mana yang terpilih.
                sampul: i === 0,
                onSampul: i === 0 ? null : function () {
                    state.foto.unshift(state.foto.splice(i, 1)[0]);
                    state.kotor = true;
                    renderFoto();
                }
            }));
        });
    }

    /**
     * Daftar foto yang DITOLAK — dirender sekali setelah seluruh loop selesai,
     * bukan ditulis ke `#pesan-foto` di tengah jalan (U1).
     *
     * Cacat yang ini tutup: `#pesan-foto` adalah satu elemen yang ditimpa di
     * AWAL tiap iterasi oleh teks "Memproses foto n/N…", lalu dikosongkan bila
     * foto terakhir kebetulan lolos. Foto ke-3 dan ke-6 yang ditolak karena
     * resolusi menghilang tanpa jejak: pemesan melihat 8 thumbnail dan pesan
     * kosong, tanpa pernah tahu dua fotonya tidak ikut.
     */
    function renderTolak(daftar) {
        if (!tolakFoto) return;
        tolakFoto.textContent = '';
        tolakFoto.hidden = daftar.length === 0;
        daftar.forEach(function (item) {
            var li = document.createElement('li');
            // Peringatan LUNAK dibedakan warnanya: fotonya TETAP dipakai, dan
            // merah untuk sesuatu yang berhasil diunggah hanya membuat pemesan
            // mengira ada yang gagal (alasan yang sama sudah dipakai `.pesan-lunak`).
            li.className = item.lunak ? 'tolak-lunak' : 'tolak-keras';
            li.textContent = item.teks;
            tolakFoto.appendChild(li);
        });
    }

    /**
     * Kunci tombol Kirim selama kompresi berjalan (U1). Tombolnya dicari lambat
     * supaya fungsi ini tidak bergantung pada urutan deklarasi `var` di berkas.
     */
    function kunciKirim(terkunci, label) {
        var b = document.getElementById('btn-kirim');
        if (!b) return;
        if (!b.dataset.labelAsli) b.dataset.labelAsli = b.textContent;
        b.disabled = terkunci;
        b.textContent = terkunci ? label : b.dataset.labelAsli;
    }

    function buatThumb(item, onHapus, opsi) {
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

        // QRIS memanggil tanpa `opsi` — tidak ada sampul di sana.
        if (opsi && opsi.sampul) {
            wrap.classList.add('sampul');
            var tanda = document.createElement('span');
            tanda.className = 'foto-badge';
            tanda.textContent = 'Sampul';
            wrap.appendChild(tanda);
        } else if (opsi && opsi.onSampul) {
            var pilih = document.createElement('button');
            pilih.type = 'button';
            pilih.className = 'btn-sampul';
            pilih.title = 'Jadikan sampul';
            pilih.setAttribute('aria-label', 'Jadikan foto ini sampul undangan');
            pilih.textContent = '☆';
            pilih.addEventListener('click', opsi.onSampul);
            wrap.appendChild(pilih);
        }
        return wrap;
    }

    if (inputFoto) {
        inputFoto.addEventListener('change', async function () {
            var files = Array.from(inputFoto.files || []);
            if (!files.length) return;

            // Catatan dikumpulkan dulu, dirender SETELAH loop — lihat renderTolak().
            var catatan = [];
            var lewat = 0;

            state.memproses = true;
            kunciKirim(true, 'Menyiapkan foto…');
            if (pesanFoto) pesanFoto.classList.remove('pesan-lunak');
            renderTolak([]);

            try {
                for (var i = 0; i < files.length; i++) {
                    var f = files[i];
                    if (state.foto.length >= cfg.maxFoto) { lewat = files.length - i; break; }
                    if (!/^image\//.test(f.type)) {
                        catatan.push({ teks: 'File bukan gambar, dilewati: ' + f.name });
                        continue;
                    }
                    try {
                        if (pesanFoto) pesanFoto.textContent = 'Memproses foto ' + (i + 1) + '/' + files.length + '…';
                        var hasil = await kompres(f, 1600, 'image/jpeg', 'foto-' + Date.now() + '-' + i, MIN_SISI_TOLAK);
                        if (hasil.size > cfg.maxSizeMB * 1024 * 1024) {
                            catatan.push({ teks: 'Foto "' + f.name + '" terlalu besar setelah dikompresi — tidak ikut dikirim.' });
                            continue;
                        }
                        state.foto.push({ file: hasil, url: URL.createObjectURL(hasil) });
                        renderFoto();
                        var pendek = Math.min(hasil.asliLebar || 0, hasil.asliTinggi || 0);
                        if (pendek && pendek < MIN_SISI_IDEAL) {
                            catatan.push({
                                lunak: true,
                                teks: 'Foto "' + f.name + '" resolusinya pas-pasan (' + hasil.asliLebar + '×' + hasil.asliTinggi
                                    + ') — tetap kami pakai, tapi hasilnya akan terlihat lebih lembut. '
                                    + 'File asli dari fotografer akan jauh lebih tajam.'
                            });
                        }
                    } catch (e) {
                        catatan.push({
                            teks: e && e.kode === 'RESOLUSI'
                                ? pesanResolusi(f.name, e.lebar, e.tinggi)
                                : 'Gagal memproses "' + f.name + '" — tidak ikut dikirim. Coba foto lain.'
                        });
                    }
                }

                if (lewat > 0) {
                    catatan.push({
                        teks: 'Batas ' + cfg.maxFoto + ' foto tercapai — ' + lewat + ' file terakhir tidak ikut. '
                            + 'Hapus salah satu foto di atas bila ingin menggantinya.'
                    });
                }
            } finally {
                state.memproses = false;
                kunciKirim(false);
                if (pesanFoto) pesanFoto.textContent = '';
                renderTolak(catatan);
                inputFoto.value = '';
            }
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
            // Dikunci sama seperti galeri (U1): QRIS juga lewat kompresi canvas,
            // jadi jendela "Kirim ditekan sebelum selesai" berlaku di sini juga.
            state.memproses = true;
            kunciKirim(true, 'Menyiapkan QRIS…');
            try {
                // PNG: kode QR harus tetap tajam agar bisa dipindai
                var hasil = await kompres(f, 1200, 'image/png', 'qris-' + Date.now());
                if (state.qris) URL.revokeObjectURL(state.qris.url);
                state.qris = { file: hasil, url: URL.createObjectURL(hasil) };
                renderQris();
            } catch (e) {
                if (pesanQris) pesanQris.textContent = 'Gagal memproses gambar QRIS.';
            } finally {
                state.memproses = false;
                kunciKirim(false);
                inputQris.value = '';
            }
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
    var progressPct = $('#progress-pct');
    var kirimMsg = $('#kirim-msg');
    var fields = $('#form-fields');

    function tampilPesan(teks, error) {
        kirimMsg.textContent = teks;
        kirimMsg.classList.toggle('error', !!error);
    }

    // Tandai kotor pada interaksi apa pun dengan form. `capture` supaya ikut
    // menangkap elemen yang menghentikan perambatan event.
    ['input', 'change'].forEach(function (ev) {
        form.addEventListener(ev, function () { state.kotor = true; }, true);
    });

    window.addEventListener('beforeunload', function (e) {
        if (state.uploading || state.kotor) { e.preventDefault(); e.returnValue = ''; }
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

    /* ================= Layar periksa sebelum kirim (U3) =================

       Tiga puluh enam field, satu aksi yang tidak bisa dibatalkan, dan pembeli
       paket DIGITAL tidak pernah melihat layar periksa apa pun antara Kirim dan
       undangan tayang — `/proof/` khusus produk cetak. Ringkasan ini menampilkan
       kembali tanggal dalam bentuk manusia ("Sabtu, 12 Desember 2026"), yang
       justru bentuk paling cepat menyingkap salah ketik tahun. */

    var panelKonfirmasi = $('#panel-konfirmasi');
    var konfirmasiIsi = $('#konfirmasi-isi');
    var btnKonfirmasiYa = $('#konfirmasi-ya');
    var btnKonfirmasiBatal = $('#konfirmasi-batal');

    function nilai(nama) {
        var e = form.elements[nama];
        return e ? String(e.value || '').trim() : '';
    }

    function tanggalManusia(iso, jam) {
        if (!iso) return '';
        var d = new Date(iso + 'T' + (jam || '00:00'));
        if (isNaN(d.getTime())) return iso;
        var teks = new Intl.DateTimeFormat('id-ID', {
            weekday: 'long', day: 'numeric', month: 'long', year: 'numeric'
        }).format(d);
        return jam ? teks + ' · ' + jam + ' WIB' : teks;
    }

    function barisKonfirmasi() {
        var baris = [];
        var pasangan = [nilai('nama_pria'), nilai('nama_wanita')].filter(Boolean).join(' & ');
        if (pasangan) baris.push(['Mempelai', pasangan]);

        var akad = tanggalManusia(nilai('tanggal_akad'), nilai('waktu_akad'));
        if (akad) baris.push(['Akad', akad]);
        baris.push(['Resepsi', tanggalManusia(nilai('tanggal_resepsi'), nilai('waktu_resepsi')) || '—']);

        var lokasi = [nilai('lokasi_nama'), nilai('lokasi_alamat')].filter(Boolean).join(' — ');
        if (lokasi) baris.push(['Lokasi', lokasi]);

        var tema = form.querySelector('input[name="template_id"]:checked');
        if (tema) {
            var kartu = tema.closest('.tema-card');
            var nama = kartu && kartu.querySelector('.tema-nama');
            // `|| tema.value` juga menangkap label yang ada tapi kosong — baris
            // ringkasan tanpa isi lebih membingungkan daripada slug tema.
            baris.push(['Tema', (nama && nama.textContent.trim()) || tema.value]);
        }
        // Galeri hanya ada di paket Favorit ke atas.
        if (inputFoto) {
            baris.push(['Foto', state.foto.length + ' foto' + (state.qris ? ' · QRIS terlampir' : '')]);
        }
        return baris;
    }

    function tampilKonfirmasi() {
        if (!panelKonfirmasi || !konfirmasiIsi) { kirimSekarang(); return; }
        konfirmasiIsi.textContent = '';
        barisKonfirmasi().forEach(function (b) {
            var dt = document.createElement('dt');
            var dd = document.createElement('dd');
            dt.textContent = b[0];
            dd.textContent = b[1];
            konfirmasiIsi.appendChild(dt);
            konfirmasiIsi.appendChild(dd);
        });
        panelKonfirmasi.hidden = false;
        if (btnKirim) btnKirim.hidden = true;
        panelKonfirmasi.scrollIntoView({ block: 'center', behavior: 'smooth' });
        if (btnKonfirmasiYa) btnKonfirmasiYa.focus();
    }

    function tutupKonfirmasi(kembalikanFokus) {
        if (panelKonfirmasi) panelKonfirmasi.hidden = true;
        if (btnKirim) {
            btnKirim.hidden = false;
            if (kembalikanFokus) btnKirim.focus();
        }
    }

    if (btnKonfirmasiYa) {
        btnKonfirmasiYa.addEventListener('click', function () { tutupKonfirmasi(false); kirimSekarang(); });
    }
    if (btnKonfirmasiBatal) {
        btnKonfirmasiBatal.addEventListener('click', function () { tutupKonfirmasi(true); });
    }

    form.addEventListener('submit', function (ev) {
        ev.preventDefault();
        if (state.uploading) return;

        // Jaring kedua U1. Tombolnya sudah di-disable selama kompresi, tapi
        // submit bisa datang dari Enter di dalam field, dan `disabled` pada
        // tombol tidak menghalangi itu.
        if (state.memproses) {
            tampilPesan('Foto masih disiapkan — tunggu sebentar lalu tekan Kirim lagi.', true);
            return;
        }

        if (!form.reportValidity()) return; // validasi native (required, url, min tanggal)
        if (!cfg.webhook) {
            tampilPesan('Formulir belum terhubung ke sistem — hubungi CS.', true);
            return;
        }

        tampilKonfirmasi();
    });

    function kirimSekarang() {
        // Rakit payload SEBELUM fieldset di-disable (fieldset disabled = field
        // tidak ikut FormData).
        var fd = new FormData(form);
        state.foto.forEach(function (item, i) { fd.append('foto_' + i, item.file); });
        fd.append('jumlah_foto', String(state.foto.length));
        if (state.qris) fd.append('qris', state.qris.file);

        // Dideklarasikan DI SINI, sebelum dipakai: `var` di dekat onprogress
        // akan dihoist tanpa nilainya, lalu inisialisasinya menimpa penugasan
        // di bawah ini saat baris itu tercapai.
        var pctTerakhir = 0;
        var faseTerakhir = '';

        state.uploading = true;
        fields.disabled = true;
        btnKirim.disabled = true;
        progress.hidden = false;
        progressBar.style.width = '0%';
        progress.setAttribute('aria-valuenow', '0');
        if (progressPct) progressPct.textContent = '0%';
        faseTerakhir = 'Mengunggah… jangan tutup halaman ini.';
        tampilPesan(faseTerakhir);

        var xhr = new XMLHttpRequest();
        xhr.open('POST', cfg.webhook);
        xhr.timeout = 180000;
        // Jangan set Content-Type manual — browser mengisi boundary multipart,
        // dan tanpa header custom request tetap "simple" (tanpa preflight CORS).

        /* U9 — persen BERHENTI ditulis ke `#kirim-msg`.
           Elemen itu `role="status"`, yaitu live region: menulis angka baru tiap
           event progres berarti puluhan sampai ratusan pengumuman untuk satu
           unggahan. Angkanya pindah ke bar (aria-valuenow + label ber-aria-hidden
           untuk mata), dan `#kirim-msg` hanya menerima PERUBAHAN FASE — dua kali,
           bukan ratusan. */
        function fase(teks) {
            if (teks === faseTerakhir) return;
            faseTerakhir = teks;
            tampilPesan(teks);
        }

        xhr.upload.onprogress = function (e) {
            if (!e.lengthComputable) return;
            var pct = Math.round((e.loaded / e.total) * 100);
            pctTerakhir = pct;
            progressBar.style.width = pct + '%';
            if (progress) progress.setAttribute('aria-valuenow', String(pct));
            if (progressPct) progressPct.textContent = pct + '%';
            fase(pct < 100 ? 'Mengunggah… jangan tutup halaman ini.' : 'Memproses… sebentar lagi.');
        };

        function gagal(teks) {
            state.uploading = false;
            fields.disabled = false;
            btnKirim.disabled = false;
            progress.hidden = true;
            if (progressPct) progressPct.textContent = '';
            faseTerakhir = ''; // percobaan berikutnya harus mengumumkan fasenya lagi
            tampilPesan(teks, true);
        }

        xhr.onload = function () {
            if (xhr.status >= 200 && xhr.status < 300) {
                state.uploading = false;
                state.kotor = false; // terkirim — jangan lagi menahan pemesan pergi
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
        /* U9 — dua situasi yang sangat berbeda dulu diucapkan dengan kalimat yang
           sama. `xhr.timeout` berlaku untuk SELURUH round trip, termasuk waktu
           WF-02 memproses payload setelah bar mencapai 100%. Bila timeout jatuh
           di fase itu, datanya kemungkinan besar SUDAH masuk — dan menyuruh
           "kirim lagi" menghasilkan undangan kedua. */
        xhr.ontimeout = function () {
            if (pctTerakhir >= 100) {
                gagal('Unggahanmu sudah sampai ke server, tapi prosesnya belum sempat dilaporkan kembali. '
                    + 'JANGAN kirim ulang — hubungi CS dan sebutkan pesanan #' + nilai('order') + '. '
                    + 'Kami cek apakah undanganmu sudah terbuat.');
            } else {
                gagal('Waktu unggah habis — coba jaringan yang lebih stabil lalu kirim lagi.');
            }
        };

        xhr.send(fd);
    }
})();
