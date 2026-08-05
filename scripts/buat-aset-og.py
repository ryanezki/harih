#!/usr/bin/env python3
"""Bangun aset og:image 1200x630 berbrand — katalog + 1 per tema (TASKS P0.3).

Preview share WhatsApp adalah etalase produk: reseller membagikan katalog, tamu
membagikan undangan. Kartu dirender dari HTML memakai tipografi & token warna
ASLI tiap tema (disalin dari undangan/{tema}/style.css) — termasuk KONSEP
tipografinya: tema-01 satu keluarga serif, tema-02 satu keluarga sans,
tema-03 kontras didone+sans ringan — lalu di-screenshot
Chrome headless — jadi aset OG selalu konsisten dengan skin yang dijual.

Jalankan dari root repo:
    python3 scripts/buat-aset-og.py

Hasil: wp-content/themes/harih/aset/og/*.jpg
(aset tinggal di dalam tema supaya ikut ter-version-control & ter-deploy rsync) (≤ 300 KB, aturan aset blueprint §7).
Menambah tema baru → tambahkan entri di TEMA lalu jalankan ulang.

Prasyarat: Google Chrome terpasang (dipakai headless), `sips` (bawaan macOS),
dan koneksi internet saat render (font diambil dari Google Fonts).
"""
import base64, pathlib, subprocess, sys, tempfile

ROOT = pathlib.Path(__file__).resolve().parent.parent
FOTO = ROOT / "wp-content" / "themes" / "harih" / "aset" / "demo"
KELUAR = ROOT / "wp-content" / "themes" / "harih" / "aset" / "og"
CHROME = "/Applications/Google Chrome.app/Contents/MacOS/Google Chrome"

# Token disalin dari undangan/{tema}/style.css — jaga tetap sinkron.
TEMA = {
    "katalog": {
        "foto": "harih-cincin-sepatu.jpg",
        "font_display": "Playfair Display", "font_body": "Plus Jakarta Sans",
        "overlay": "linear-gradient(180deg, rgba(28,42,35,.42) 0%, rgba(28,42,35,.78) 100%)",
        "ink": "#f7f4ea", "gold": "#c8a961", "garis": "rgba(244,241,231,.5)",
        "eyebrow": "UNDANGAN PERNIKAHAN DIGITAL",
        "judul": "hariH", "judul_size": "150px",
        "sub": "Jadi dalam hitungan menit, langsung terkirim ke WhatsApp",
        "kaki": "Undangan pernikahan digital &nbsp;·&nbsp; harih.id",
    },
    "tema-01": {
        "foto": "harih-cincin-buket.jpg",
        "font_display": "Cormorant Garamond", "font_body": "Cormorant Garamond",
        # Foto buket sangat terang di area atas — overlay lebih pekat daripada
        # cover tema aslinya supaya eyebrow emas tetap terbaca (uji kontras P0.3).
        "overlay": "linear-gradient(180deg, rgba(28,42,35,.52) 0%, rgba(28,42,35,.80) 100%)",
        "ink": "#f7f4ea", "gold": "#d0ac5c", "garis": "rgba(247,244,234,.45)",
        "eyebrow": "TEMA 01", "judul": "Botanical Elegan", "judul_size": "104px",
        "sub": "Sage &amp; gading, ornamen botani lembut",
        "kaki": "hariH &nbsp;·&nbsp; harih.id",
    },
    "tema-02": {
        "foto": "harih-cincin-sepatu.jpg",
        "font_display": "Karla", "font_body": "Karla",
        "overlay": "linear-gradient(180deg, rgba(90,42,24,.40) 0%, rgba(59,30,18,.78) 100%)",
        "ink": "#faf1e6", "gold": "#d79a68", "garis": "rgba(250,241,230,.45)",
        "eyebrow": "TEMA 02", "judul": "Senja Terakota", "judul_size": "82px",
        "sub": "Terakota &amp; tembaga, bingkai lengkung khas senja",
        "kaki": "hariH &nbsp;·&nbsp; harih.id",
    },
    "tema-03": {
        "foto": "harih-gaun-detail.jpg",
        "font_display": "Prata", "font_body": "Manrope",
        "overlay": "linear-gradient(180deg, rgba(12,18,32,.62) 0%, rgba(12,18,32,.88) 100%)",
        "ink": "#e8e4d8", "gold": "#c9a45c", "garis": "rgba(201,164,92,.55)",
        "eyebrow": "TEMA 03", "judul": "Langit Malam", "judul_size": "92px",
        "sub": "Navy pekat &amp; emas, cover bertabur bintang",
        "kaki": "hariH &nbsp;·&nbsp; harih.id",
    },
}

HTML = """<!doctype html><html><head><meta charset="utf-8">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family={font_q}&display=swap" rel="stylesheet">
<style>
  * {{ margin: 0; padding: 0; box-sizing: border-box; }}
  html, body {{ width: 1200px; height: 630px; overflow: hidden; }}
  .kartu {{
    position: relative; width: 1200px; height: 630px;
    background-image: {overlay}, url("data:image/jpeg;base64,{foto_b64}");
    background-size: cover, cover; background-position: center, center;
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    text-align: center; color: {ink}; font-family: '{font_body}', sans-serif;
  }}
  /* Bingkai tipis — gema ornamen bingkai di cover tema */
  .kartu::after {{
    content: ''; position: absolute; inset: 26px;
    border: 1px solid {garis}; pointer-events: none;
  }}
  /* Bayangan teks di SEMUA teks kecil: foto latar bisa terang di titik mana pun,
     dan teks emas tipis paling dulu hilang kontrasnya. */
  .eyebrow {{
    font-size: 20px; letter-spacing: .42em; text-transform: uppercase;
    color: {gold}; margin-bottom: 26px; padding-left: .42em;
    text-shadow: 0 1px 10px rgba(0,0,0,.55);
  }}
  .judul {{
    font-family: '{font_display}', Georgia, serif; font-size: {judul_size};
    font-weight: 400; line-height: 1.02; letter-spacing: -.01em;
    text-shadow: 0 2px 24px rgba(0,0,0,.28);
  }}
  .rule {{ display: flex; align-items: center; gap: 14px; margin: 30px 0 24px; }}
  .rule i {{ display: block; width: 92px; height: 1px; background: {gold}; opacity: .85; }}
  .rule b {{ font-size: 17px; color: {gold}; line-height: 1; }}
  .sub {{ font-size: 25px; opacity: .95; font-weight: 400; text-shadow: 0 1px 12px rgba(0,0,0,.5); }}
  .kaki {{
    position: absolute; bottom: 56px; left: 0; right: 0;
    font-size: 18px; letter-spacing: .16em; text-transform: uppercase;
    color: {gold}; opacity: .95; text-shadow: 0 1px 10px rgba(0,0,0,.55);
  }}
</style></head><body>
<div class="kartu">
  <p class="eyebrow">{eyebrow}</p>
  <h1 class="judul">{judul}</h1>
  <div class="rule"><i></i><b>&#10022;</b><i></i></div>
  <p class="sub">{sub}</p>
  <p class="kaki">{kaki}</p>
</div></body></html>"""


def render(nama: str, cfg: dict) -> pathlib.Path:
    foto = FOTO / cfg["foto"]
    if not foto.exists():
        sys.exit(f"Foto tidak ada: {foto}")
    b64 = base64.b64encode(foto.read_bytes()).decode()

    fonts = {cfg["font_display"], cfg["font_body"]}
    font_q = "&family=".join(sorted(f.replace(" ", "+") for f in fonts))

    html = HTML.format(foto_b64=b64, font_q=font_q, **{k: v for k, v in cfg.items() if k != "foto"})

    KELUAR.mkdir(parents=True, exist_ok=True)
    with tempfile.TemporaryDirectory() as tmp:
        src = pathlib.Path(tmp) / "kartu.html"
        src.write_text(html)
        png = pathlib.Path(tmp) / "kartu.png"
        subprocess.run([
            CHROME, "--headless", "--disable-gpu", "--hide-scrollbars",
            "--force-device-scale-factor=1", "--window-size=1200,630",
            "--virtual-time-budget=8000",  # tunggu Google Fonts termuat
            f"--screenshot={png}", f"file://{src}",
        ], capture_output=True, check=True)
        if not png.exists():
            sys.exit(f"Chrome tidak menghasilkan screenshot untuk {nama}")

        jpg = KELUAR / f"og-{nama}.jpg"
        subprocess.run(["sips", "-s", "format", "jpeg", "-s", "formatOptions", "80",
                        str(png), "--out", str(jpg)], capture_output=True, check=True)
    return jpg


if __name__ == "__main__":
    for nama, cfg in TEMA.items():
        jpg = render(nama, cfg)
        kb = jpg.stat().st_size // 1024
        tanda = "✓" if kb <= 300 else "⚠ > 300 KB"
        print(f"{jpg.relative_to(ROOT)}  {kb} KB  {tanda}")
