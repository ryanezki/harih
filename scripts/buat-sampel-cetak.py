#!/usr/bin/env python3
"""Bangkitkan berkas SAMPEL siap cetak dari snapshot beku sebuah pesanan.

⚠️  INI BUKAN ENGINE PRODUKSI (F4.3/F4.4).
    Engine sesungguhnya — render SVG→PDF + imposition dengan bleed, gutter, dan
    registration mark — masih ditunda menunggu volume yang membenarkannya, dan
    diperkirakan 2–3 minggu kerja. Skrip ini sekali-pakai, untuk satu tujuan:
    menghasilkan sesuatu yang BISA DICETAK HARI INI supaya pertanyaan yang
    hanya bisa dijawab tangan akhirnya terjawab —

        · apakah mesin creasing sanggup melipatnya
        · berapa menit per unit, dari cetak sampai lipat
        · berapa bobot hasil akhir (untuk ongkir yang kita tanggung)
        · apakah QR-nya terpindai setelah dilipat

    Sumber datanya SNAPSHOT BEKU pesanan, bukan data undangan yang hidup —
    sama seperti yang akan dibaca produksi (TASKS F4.1).

CARA PAKAI
    python3 scripts/buat-sampel-cetak.py <order_id> [-o keluaran.pdf]

KELUARAN — satu PDF 3 halaman:
    1. sisi LUAR   (A4 lanskap): panel kiri = sampul belakang · kanan = sampul depan
    2. sisi DALAM  (A4 lanskap): panel kiri & kanan = isi undangan
    3. nama amplop (A4 lanskap): daftar nama tamu dari snapshot, dua kolom

    Cetak halaman 1 & 2 bolak-balik pada satu lembar A4, lipat vertikal di
    tengah → jadi A5 potret. Tanda lipat berupa tik kecil di tepi atas & bawah.

PRASYARAT: Google Chrome (dipakai headless untuk merender PDF) dan `vps/.env`.
Tidak menyentuh container produksi mana pun.
"""
import argparse, html, json, os, pathlib, subprocess, sys, tempfile, urllib.parse, urllib.request

ROOT = pathlib.Path(__file__).resolve().parent.parent
FONT = ROOT / 'wp-content/themes/harih/aset/font'
CHROME = '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome'
SSH = ['ssh', '-p', '65002', '-o', 'BatchMode=yes', 'u803921702@147.93.80.20']
WP_DIR = 'domains/harih.id/public_html'

BULAN = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
         'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember']


def tanggal_id(iso: str) -> str:
    try:
        th, bl, hr = iso.split('-')
        return f'{int(hr)} {BULAN[int(bl)]} {th}'
    except Exception:
        return iso


def ambil_snapshot(order_id: int) -> dict:
    """Snapshot BEKU dari pesanan — bukan meta undangan yang hidup."""
    out = subprocess.run(
        SSH + [f'cd {WP_DIR} && wp eval \'echo wc_get_order({order_id})->get_meta("_snapshot");\''],
        capture_output=True, text=True)
    raw = out.stdout.strip()
    if not raw:
        sys.exit(f'Pesanan {order_id} belum punya snapshot beku. Bekukan dulu lewat halaman pesanan.')
    return json.loads(raw)


def qr_data_uri(link: str) -> str:
    """QR memakai generator & parameter YANG SAMA dengan WF-02 (ecc=H, qzone=4)
    supaya sampel ini menguji QR yang benar-benar akan diproduksi."""
    import base64
    q = urllib.parse.urlencode({'size': '600x600', 'data': link, 'ecc': 'H', 'qzone': '4'})
    with urllib.request.urlopen(f'https://api.qrserver.com/v1/create-qr-code/?{q}', timeout=30) as r:
        return 'data:image/png;base64,' + base64.b64encode(r.read()).decode()


def font_data_uri(nama: str) -> str:
    import base64
    b = (FONT / nama).read_bytes()
    return 'data:font/ttf;base64,' + base64.b64encode(b).decode()


def e(x) -> str:
    return html.escape(str(x or ''), quote=False)


def baris(teks: str) -> list:
    return [b.strip() for b in str(teks or '').split('\n') if b.strip()]


def bangun_html(s: dict, qr: str) -> str:
    pria, wanita = e(s.get('nama_pria')), e(s.get('nama_wanita'))
    p1 = pria.split(' ')[0]
    w1 = wanita.split(' ')[0]
    tamu = baris(s.get('daftar_tamu'))
    turut = baris(s.get('turut_mengundang'))
    # Ditanam sebagai data URI, bukan file:// — Chrome headless tidak selalu
    # memuat font lokal, dan kegagalannya SENYAP: hasilnya jatuh ke Georgia
    # tanpa satu pun peringatan, dan baru ketahuan saat berkasnya sudah dicetak.
    ff = font_data_uri('CormorantGaramond.ttf')
    ffi = font_data_uri('CormorantGaramond-Italic.ttf')

    def acara(judul, tgl, waktu, nama, alamat):
        if not (tgl or nama):
            return ''
        return f'''<div class="acara">
            <p class="acara-judul">{e(judul)}</p>
            <p class="acara-tgl">{e(tanggal_id(tgl))}</p>
            <p class="acara-jam">{e(waktu)}</p>
            <p class="acara-tempat">{e(nama)}</p>
            <p class="acara-alamat">{e(alamat)}</p>
        </div>'''

    return f'''<!doctype html><meta charset="utf-8">
<style>
@font-face {{ font-family:'Cormorant'; src:url('{ff}') format('truetype'); font-weight:400; }}
@font-face {{ font-family:'Cormorant'; src:url('{ffi}') format('truetype'); font-style:italic; }}
/* WAJIB: tanpa `size`, Chrome memakai US Letter (216mm) dan panel 297mm
   terpotong diam-diam. Semua halaman dibuat A4 LANSKAP — @page bernama untuk
   mencampur orientasi dukungannya tidak seragam antar versi Chrome. */
@page {{ size:A4 landscape; margin:0; }}
* {{ box-sizing:border-box; margin:0; padding:0; }}
body {{ font-family:'Cormorant',Georgia,serif; color:#2f2b23; -webkit-print-color-adjust:exact; print-color-adjust:exact; }}

/* A4 LANSKAP 297x210mm, dua panel A5 potret 148.5mm masing-masing */
.lembar {{ width:297mm; height:210mm; display:flex; page-break-after:always; position:relative; background:#faf7f0; }}
.panel {{ width:148.5mm; height:210mm; padding:18mm 14mm; display:flex; flex-direction:column; }}
/* Tik lipat di tepi atas & bawah — sengaja hanya 4mm dan di tepi, bukan garis
   penuh: garis penuh akan ikut tercetak di produk jadi. */
.tik {{ position:absolute; left:148.5mm; width:0; border-left:.3pt solid #999; }}
.tik-atas {{ top:0; height:4mm; }} .tik-bawah {{ bottom:0; height:4mm; }}

.tengah {{ justify-content:center; align-items:center; text-align:center; }}
.eyebrow {{ font-size:8pt; letter-spacing:.42em; text-transform:uppercase; color:#6b6758; }}
.nama-besar {{ font-size:31pt; line-height:1.18; margin:9mm 0; }}
.amp {{ font-style:italic; font-size:19pt; color:#a3835f; display:block; margin:2mm 0; }}
.tgl-sampul {{ font-size:12pt; letter-spacing:.16em; color:#6b6758; }}
.garis {{ width:26mm; height:.4pt; background:#a3835f; margin:7mm auto; }}

.salam {{ font-size:11pt; text-align:center; line-height:1.75; }}
.ayat {{ font-style:italic; font-size:9.5pt; line-height:1.8; text-align:center; color:#6b6758; margin:5mm 0; }}
.ortu {{ text-align:center; margin-top:6mm; }}
.ortu p {{ font-size:9.5pt; line-height:1.6; color:#6b6758; }}
.ortu .nm {{ font-size:16pt; color:#2f2b23; margin:2mm 0 1mm; }}

.acara {{ text-align:center; margin-bottom:8mm; }}
.acara-judul {{ font-size:8pt; letter-spacing:.34em; text-transform:uppercase; color:#a3835f; margin-bottom:2.5mm; }}
.acara-tgl {{ font-size:15pt; }}
.acara-jam {{ font-size:10pt; letter-spacing:.08em; color:#6b6758; margin-bottom:1.5mm; }}
.acara-tempat {{ font-size:11.5pt; }}
.acara-alamat {{ font-size:9pt; color:#6b6758; line-height:1.5; }}

.qr-blok {{ margin-top:auto; text-align:center; }}
.qr-blok img {{ width:31mm; height:31mm; display:block; margin:0 auto 2.5mm; }}
.qr-blok p {{ font-size:7.5pt; letter-spacing:.1em; color:#6b6758; line-height:1.5; }}

.turut p {{ font-size:9.5pt; line-height:1.7; }}
.penutup {{ font-size:10pt; line-height:1.8; text-align:center; }}

/* Halaman nama amplop — A4 potret */
.amplop {{ width:297mm; height:210mm; padding:16mm 18mm; background:#fff; }}
.amplop-grid {{ column-count:2; column-gap:14mm; }}
.amplop h2 {{ font-size:11pt; letter-spacing:.3em; text-transform:uppercase; color:#6b6758; margin-bottom:3mm; }}
.amplop .ket {{ font-size:8.5pt; color:#6b6758; margin-bottom:9mm; line-height:1.6; }}
.nama-amplop {{ font-size:15pt; padding:5mm 0 2.5mm; border-bottom:.3pt dashed #ccc; break-inside:avoid; }}
.nama-amplop span {{ font-size:7.5pt; letter-spacing:.2em; color:#a3835f; display:block; margin-bottom:1.5mm; text-transform:uppercase; }}
</style>

<!-- ===== Halaman 1 — sisi LUAR: kiri belakang, kanan depan ===== -->
<div class="lembar">
  <div class="tik tik-atas"></div><div class="tik tik-bawah"></div>
  <div class="panel tengah">
    <div class="turut" style="text-align:center">
      {'<p class="eyebrow" style="margin-bottom:5mm">Turut Mengundang</p>' if turut else ''}
      {''.join(f'<p>{e(t)}</p>' for t in turut)}
    </div>
    <div class="garis"></div>
    <p class="penutup">Merupakan suatu kehormatan dan kebahagiaan bagi kami<br>apabila Bapak/Ibu/Saudara/i berkenan hadir<br>untuk memberikan doa restu.</p>
    {f'<p class="eyebrow" style="margin-top:8mm">Dress code · {e(s.get("dresscode"))}</p>' if s.get('dresscode') else ''}
  </div>
  <div class="panel tengah">
    <p class="eyebrow">The Wedding Of</p>
    <h1 class="nama-besar">{p1}<span class="amp">&amp;</span>{w1}</h1>
    <div class="garis"></div>
    <p class="tgl-sampul">{e(tanggal_id(s.get('tanggal_resepsi')))}</p>
  </div>
</div>

<!-- ===== Halaman 2 — sisi DALAM ===== -->
<div class="lembar">
  <div class="tik tik-atas"></div><div class="tik tik-bawah"></div>
  <div class="panel" style="justify-content:center">
    <p class="salam">Assalamu&rsquo;alaikum Warahmatullahi Wabarakatuh</p>
    <p class="ayat">&ldquo;Dan di antara tanda-tanda kebesaran-Nya ialah Dia menciptakan pasangan-pasangan untukmu dari jenismu sendiri, agar kamu cenderung dan merasa tenteram kepadanya.&rdquo;<br>&mdash; QS. Ar-Rum: 21</p>
    <div class="garis"></div>
    <div class="ortu">
      <p class="nm">{pria}</p>
      <p>{e(s.get('anak_ke_pria'))}<br>{e(s.get('ortu_pria'))}</p>
    </div>
    <div class="ortu">
      <p class="nm">{wanita}</p>
      <p>{e(s.get('anak_ke_wanita'))}<br>{e(s.get('ortu_wanita'))}</p>
    </div>
  </div>
  <div class="panel">
    {acara('Akad Nikah', s.get('tanggal_akad'), s.get('waktu_akad'), s.get('lokasi_akad_nama'), s.get('lokasi_akad_alamat'))}
    {acara('Resepsi', s.get('tanggal_resepsi'), s.get('waktu_resepsi'), s.get('lokasi_nama'), s.get('lokasi_alamat'))}
    <div class="qr-blok">
      <img src="{qr}" alt="">
      <p>Pindai untuk peta lokasi,<br>konfirmasi kehadiran, dan galeri</p>
    </div>
  </div>
</div>

<!-- ===== Halaman 3 — nama amplop ===== -->
<div class="amplop">
  <h2>Nama Amplop &mdash; {len(tamu)} tamu</h2>
  <p class="ket">Dari daftar tamu yang dibekukan pada pesanan ini. Cetak langsung
  pada amplopnya (bukan stiker). Posisi &amp; ukuran perlu disesuaikan ke ukuran
  amplop yang dipakai &mdash; halaman ini untuk menguji keterbacaan dan umpan mesin.</p>
  <div class="amplop-grid">
  {''.join(f'<div class="nama-amplop"><span>Kepada Yth.</span>{e(t)}</div>' for t in tamu)}
  </div>
</div>
'''


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument('order_id', type=int)
    ap.add_argument('-o', '--output', default=None)
    args = ap.parse_args()

    if not os.path.exists(CHROME):
        sys.exit(f'Google Chrome tidak ditemukan di {CHROME} — dipakai untuk merender PDF.')

    print(f'  mengambil snapshot beku pesanan {args.order_id}…')
    snap = ambil_snapshot(args.order_id)
    link = snap.get('permalink') or ''
    print(f'  QR untuk {link}')
    qr = qr_data_uri(link)

    keluaran = pathlib.Path(args.output or f'sampel-cetak-{args.order_id}.pdf').resolve()
    with tempfile.TemporaryDirectory() as tmp:
        src = pathlib.Path(tmp) / 'sampel.html'
        src.write_text(bangun_html(snap, qr), encoding='utf-8')
        subprocess.run([CHROME, '--headless', '--disable-gpu', '--no-pdf-header-footer',
                        '--allow-file-access-from-files',
                        f'--print-to-pdf={keluaran}', src.as_uri()],
                       capture_output=True, timeout=120)

    if not keluaran.exists() or keluaran.stat().st_size < 5000:
        sys.exit('Render gagal — PDF kosong atau terlalu kecil.')
    print(f'  → {keluaran}  ({keluaran.stat().st_size:,} byte)')
    print()
    print('  Cetak halaman 1 & 2 BOLAK-BALIK pada satu lembar A4 (lanskap, skala 100%,')
    print('  JANGAN "fit to page"). Lipat vertikal di tengah mengikuti tik di tepi.')
    print('  Halaman 3 untuk menguji nama amplop.')


if __name__ == '__main__':
    main()
