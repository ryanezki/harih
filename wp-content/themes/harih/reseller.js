/**
 * hariH — Form pendaftaran reseller (page-jadi-reseller.php).
 * Kontrak WF-03: POST FormData (nama, wa, bank, norek, website[honeypot])
 * ke webhook n8n. Respons JSON {ok, message} — message ditampilkan apa adanya.
 * Tanpa header custom → request "simple", tidak memicu preflight CORS.
 */
(function () {
    'use strict';

    var cfg = window.RESELLER || {};
    var form = document.getElementById('form-reseller');
    if (!form || form.hasAttribute('data-nonaktif')) return;

    var btn = document.getElementById('btn-daftar');
    var msg = document.getElementById('kirim-msg');
    var mengirim = false;

    function tampil(teks, error) {
        msg.textContent = teks;
        msg.classList.toggle('error', !!error);
    }

    form.addEventListener('submit', function (ev) {
        ev.preventDefault();
        if (mengirim) return;
        if (!form.reportValidity()) return;

        // Validasi ringan nomor WA — validasi sesungguhnya di WF-03 (T2.14)
        var wa = (form.wa.value || '').replace(/\D/g, '');
        if (wa.length < 9 || wa.length > 15) {
            tampil('Nomor WhatsApp tidak valid — contoh: 08123456789.', true);
            form.wa.focus();
            return;
        }

        mengirim = true;
        btn.disabled = true;
        tampil('Mengirim…');

        fetch(cfg.webhook, { method: 'POST', body: new FormData(form) })
            .then(function (res) {
                return res.json().catch(function () { return {}; }).then(function (body) {
                    return { status: res.status, body: body };
                });
            })
            .then(function (r) {
                if (r.status >= 200 && r.status < 300) {
                    form.hidden = true;
                    var sukses = document.getElementById('panel-sukses');
                    if (sukses) {
                        if (r.body && r.body.message) {
                            var p = document.getElementById('pesan-sukses');
                            if (p) p.textContent = r.body.message;
                        }
                        sukses.hidden = false;
                        sukses.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                } else {
                    mengirim = false;
                    btn.disabled = false;
                    tampil((r.body && r.body.message) || 'Gagal mengirim (kode ' + r.status + '). Coba lagi.', true);
                }
            })
            .catch(function () {
                mengirim = false;
                btn.disabled = false;
                tampil('Koneksi terputus — periksa jaringan lalu coba lagi.', true);
            });
    });
})();
