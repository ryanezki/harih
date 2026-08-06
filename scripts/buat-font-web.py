#!/usr/bin/env python3
"""
Bangun berkas woff2 self-hosted untuk font halaman UNDANGAN (G1.2).

Kenapa ada: sampai 2026-08-07 halaman undangan masih memuat Google Fonts —
dua permintaan render-blocking ke dua domain pihak ketiga sebelum teks tampil
dalam font yang benar. Halaman toko sudah self-hosted sejak redesain
2026-08-06; undangan tertinggal. Terukur sebelum perubahan (jaringan seluler
disimulasikan, UA iPhone):

    HTML undangan          TTFB 0,175 s · total 0,31 s
    → CSS Google Fonts     TTFB 0,304 s · total 0,34 s · 12,3 KB
      (mendeklarasikan 30 varian woff2 untuk tema-01 saja)
      → woff2 pertama      TTFB 0,274 s · total 0,33 s · 24,6 KB

Rantai itu SERIAL — woff2 baru bisa mulai diunduh setelah CSS-nya tiba, dan
CSS-nya baru diminta setelah HTML diurai. Self-host memangkasnya jadi satu
permintaan ke origin yang koneksinya sudah terbuka, bisa di-preload paralel.

Sumbernya font yang SUDAH ada di repo (dibawa masuk untuk generator kartu OG,
`buat-aset-og.py`) — berkas .ttf JANGAN dihapus, GD masih memakainya.
Dua face italic ditambahkan 2026-08-07 karena `undangan.css` memakai
`font-style: italic` di empat tempat, termasuk ampersand 46px di section
mempelai; tanpa face italik sungguhan browser mensintesis oblique dan itu
terlihat jelas pada serif berukuran besar.

Ketiga keluarga non-Prata adalah **variable font** (sumbu wght), jadi satu
berkas menutup seluruh weight yang dipakai (300/400/500/600) — bukan satu
berkas per weight seperti yang dikirim Google.

Pakai:  python3 scripts/buat-font-web.py
Butuh:  pip install fonttools brotli
"""

import subprocess
import sys
from pathlib import Path

DIR = Path(__file__).resolve().parent.parent / "wp-content/themes/harih/aset/font"

# Dipecah dua persis seperti yang Google lakukan, memakai rentang Google sendiri.
# Alasannya terukur: pada Cormorant, latin-ext menambah 42 KB (61 → 103 KB).
# Dengan `unicode-range` di CSS, browser hanya mengunduh latin-ext bila halaman
# benar-benar memuat karakternya — jadi undangan berhuruf Indonesia biasa
# membayar 61 KB, sementara nama ber-Ā/ł/ș tetap tampil benar. Menggabung
# keduanya jadi satu berkas berarti SETIAP tamu membayar 42 KB untuk glyph yang
# hampir tidak pernah dipakai; membuang latin-ext berarti nama mempelai bisa
# jatuh ke font fallback — dua-duanya buruk, dan pemisahan menghindari keduanya.
SUBSET = {
    "latin": ",".join([
        "U+0000-00FF,U+0131,U+0152-0153,U+02BB-02BC,U+02C6,U+02DA,U+02DC",
        "U+0304,U+0308,U+0329,U+2000-206F,U+20AC,U+2122,U+2191,U+2193",
        "U+2212,U+2215,U+FEFF,U+FFFD",
    ]),
    "latinext": ",".join([
        "U+0100-02BA,U+02BD-02C5,U+02C7-02CC,U+02CE-02D7,U+02DD-02FF",
        "U+1D00-1DBF,U+1E00-1E9F,U+1EF2-1EFF,U+2020,U+20A0-20AB,U+20AD-20C0",
        "U+2113,U+2C60-2C7F,U+A720-A7FF",
    ]),
}

# (sumber .ttf, awalan keluaran, dipakai tema)
FACES = [
    ("CormorantGaramond.ttf",        "cormorant-garamond",        "tema-01"),
    ("CormorantGaramond-Italic.ttf", "cormorant-garamond-italic", "tema-01"),
    ("Karla.ttf",                    "karla",                     "tema-02"),
    ("Karla-Italic.ttf",             "karla-italic",              "tema-02"),
    ("Prata.ttf",                    "prata",                     "tema-03"),
    ("Manrope.ttf",                  "manrope",                   "tema-03"),
]


def main() -> int:
    if not DIR.is_dir():
        print(f"Direktori font tidak ditemukan: {DIR}", file=sys.stderr)
        return 1

    per_tema: dict[str, float] = {}
    gagal = False

    for src_name, prefix, tema in FACES:
        src = DIR / src_name
        if not src.exists():
            print(f"  ✗ {src_name} tidak ada — lewati", file=sys.stderr)
            gagal = True
            continue

        baris = [f"  ✓ {tema}  {src_name:32} {src.stat().st_size / 1024:7.1f} KB →"]
        for subset, unicodes in SUBSET.items():
            out = DIR / f"{prefix}-{subset}.woff2"
            # --layout-features='*' dipertahankan: Cormorant memakai `liga`/`kern`
            # untuk pasangan huruf yang jadi ciri khasnya. Sumbu variable (fvar)
            # ikut terbawa selama tidak memakai --instancer, sehingga satu berkas
            # menutup weight 300–700 sekaligus.
            subprocess.run(
                [
                    sys.executable, "-m", "fontTools.subset", str(src),
                    f"--unicodes={unicodes}",
                    "--layout-features=*",
                    "--flavor=woff2",
                    "--no-hinting",
                    "--desubroutinize",
                    f"--output-file={out}",
                ],
                check=True,
                stdout=subprocess.DEVNULL,
            )
            kb = out.stat().st_size / 1024
            baris.append(f"{out.name} {kb:.1f} KB")
            if subset == "latin":
                per_tema[tema] = per_tema.get(tema, 0) + kb

        print(" ".join(baris))

    print("\n  Yang benar-benar diunduh tamu pada undangan berhuruf latin biasa:")
    for tema, kb in sorted(per_tema.items()):
        print(f"    {tema}: {kb:.0f} KB  (latin-ext hanya menyusul bila namanya memuat karakternya)")
    print("\n  Jangan lupa naikkan HARIH_VERSION di themes/harih/functions.php.")
    return 1 if gagal else 0


if __name__ == "__main__":
    raise SystemExit(main())
