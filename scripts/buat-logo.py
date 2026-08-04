#!/usr/bin/env python3
"""Bangun logo hariH dalam beberapa varian (TASKS P0.1 — unggahan merchant Duitku).

Konsep: nama "hariH" adalah permainan kata *hari H*. Huruf H terakhir diberi
warna emas supaya maknanya terbaca, dan wordmark-nya sendiri jadi logo — tanpa
ikon cincin/hati generik yang justru menghapus ciri khasnya.

Tipografi & warna diturunkan dari tema-01 "Botanical Elegan" (undangan/tema-01/
style.css) supaya logo, katalog, kartu og:image, dan halaman undangan terasa
satu keluarga.

Catatan ukuran: ikon merchant di kanal pembayaran sering dipotong lingkaran.
Wordmark sengaja ditahan di dalam ~68% area tengah supaya aman dipotong bulat.

Jalankan dari root repo:
    python3 scripts/buat-logo.py

Prasyarat: Google Chrome (dipakai headless), `sips` (bawaan macOS), koneksi
internet saat render (Playfair Display diambil dari Google Fonts, SIL OFL).
"""
import pathlib, subprocess, sys, tempfile

ROOT = pathlib.Path(__file__).resolve().parent.parent
KELUAR = ROOT / "wp-content" / "themes" / "harih" / "aset" / "logo"
CHROME = "/Applications/Google Chrome.app/Contents/MacOS/Google Chrome"

SAGE, GADING, EMAS, TINTA = "#3f5c4f", "#f7f4ea", "#c9a24d", "#34332c"

HTML = """<!doctype html><html><head><meta charset="utf-8">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500&display=swap" rel="stylesheet">
<style>
  * {{ margin: 0; padding: 0; }}
  html, body {{ width: {w}px; height: {h}px; }}
  .kanvas {{
    width: {w}px; height: {h}px; background: {bg};
    display: flex; align-items: center; justify-content: center;
  }}
  .wordmark {{
    font-family: 'Playfair Display', Georgia, serif;
    font-weight: 500; font-size: {ukuran}px; line-height: 1;
    color: {ink}; letter-spacing: -.012em; white-space: nowrap;
  }}
  .wordmark i {{ font-style: normal; color: {aksen}; }}
</style></head><body>
<div class="kanvas"><span class="wordmark">hari<i>H</i></span></div>
</body></html>"""

# nama, lebar, tinggi, background ('transparent' → PNG tembus pandang),
# warna "hari", warna "H", ukuran font
VARIAN = [
    # Utama untuk Duitku: bujur sangkar, latar sage — paling terbaca di kanal pembayaran
    ("harih-kotak-1000",      1000, 1000, SAGE,          GADING, EMAS, 250),
    ("harih-kotak-500",        500,  500, SAGE,          GADING, EMAS, 125),
    # Cadangan bila diminta latar terang
    ("harih-kotak-putih-1000", 1000, 1000, "#ffffff",    SAGE,   EMAS, 250),
    # Wordmark horizontal, latar tembus pandang — untuk dokumen & situs
    ("harih-wordmark",        1200,  400, "transparent", SAGE,   EMAS, 210),
    ("harih-wordmark-putih",  1200,  400, "transparent", GADING, EMAS, 210),
]


def render(nama, w, h, bg, ink, aksen, ukuran) -> pathlib.Path:
    html = HTML.format(w=w, h=h, bg=("none" if bg == "transparent" else bg),
                       ink=ink, aksen=aksen, ukuran=ukuran)
    KELUAR.mkdir(parents=True, exist_ok=True)
    keluar = KELUAR / f"logo-{nama}.png"

    with tempfile.TemporaryDirectory() as tmp:
        src = pathlib.Path(tmp) / "logo.html"
        src.write_text(html)
        perintah = [
            CHROME, "--headless", "--disable-gpu", "--hide-scrollbars",
            "--force-device-scale-factor=1", f"--window-size={w},{h}",
            "--virtual-time-budget=8000",
            f"--screenshot={keluar}", f"file://{src}",
        ]
        if bg == "transparent":
            perintah.insert(-2, "--default-background-color=00000000")
        subprocess.run(perintah, capture_output=True, check=True)

    if not keluar.exists():
        sys.exit(f"Chrome gagal merender {nama}")
    return keluar


if __name__ == "__main__":
    for v in VARIAN:
        f = render(*v)
        print(f"{f.relative_to(ROOT)}  {f.stat().st_size // 1024} KB  {v[1]}×{v[2]}")
