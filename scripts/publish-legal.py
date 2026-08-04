#!/usr/bin/env python3
"""Publikasikan halaman legal dari docs/konten-legal/*.md ke WordPress.

Sumber kebenaran isi halaman legal adalah berkas markdown di repo — script ini
yang mengubahnya jadi HTML dan menimpa halaman di situs, jadi versi tayang dan
versi repo tidak pernah berbeda.

Kenapa tidak memakai konverter bawaan: publikasi sebelumnya membiarkan tabel
markdown lolos apa adanya, sehingga tabel masa aktif di halaman Syarat &
Ketentuan tayang sebagai `| Paket | Masa aktif |` mentah — padahal itu justru
tabel yang menyatakan apa yang dijual. Konverter di bawah menangani tabel.

Jalankan dari root repo:
    python3 scripts/publish-legal.py            # semua halaman
    python3 scripts/publish-legal.py syarat-ketentuan
"""
import html, pathlib, re, subprocess, sys

ROOT = pathlib.Path(__file__).resolve().parent.parent
SUMBER = ROOT / "docs" / "konten-legal"
SSH = ["ssh", "-p", "65002", "-o", "BatchMode=yes", "u803921702@147.93.80.20"]
WP_DIR = "domains/harih.id/public_html"

HALAMAN = ["syarat-ketentuan", "kebijakan-privasi", "kebijakan-refund", "kontak"]


def inline(t: str) -> str:
    """Markdown inline → HTML. Escape dulu, baru pasang tag — supaya `&` di teks
    tidak merusak markup dan markup kita tidak ikut ter-escape."""
    t = html.escape(t, quote=False)
    t = re.sub(r"`([^`]+)`", r"<code>\1</code>", t)
    t = re.sub(r"\[([^\]]+)\]\(([^)]+)\)", r'<a href="\2">\1</a>', t)
    t = re.sub(r"\*\*([^*]+)\*\*", r"<strong>\1</strong>", t)
    t = re.sub(r"(?<!\*)\*([^*]+)\*(?!\*)", r"<em>\1</em>", t)
    return t


def ke_html(md: str) -> str:
    baris = md.split("\n")
    keluar, i = [], 0
    lewati_h1 = True

    while i < len(baris):
        b = baris[i]
        s = b.strip()

        if s == "":
            i += 1
            continue

        # H1 pertama = judul halaman, sudah jadi judul post → jangan dicetak dua kali
        if s.startswith("# ") and lewati_h1:
            lewati_h1 = False
            i += 1
            continue

        if s == "---":
            keluar.append("<hr>")
            i += 1
            continue

        m = re.match(r"^(#{2,4})\s+(.*)$", s)
        if m:
            n = len(m.group(1))
            keluar.append(f"<h{n}>{inline(m.group(2))}</h{n}>")
            i += 1
            continue

        # Tabel: baris pipe diikuti baris pemisah |---|---|
        if s.startswith("|") and i + 1 < len(baris) and re.match(r"^\|[\s:|-]+\|$", baris[i + 1].strip()):
            def sel(row):
                return [c.strip() for c in row.strip().strip("|").split("|")]

            kepala = sel(s)
            i += 2
            isi = []
            while i < len(baris) and baris[i].strip().startswith("|"):
                isi.append(sel(baris[i]))
                i += 1
            t = ["<table>", "<thead><tr>"]
            t += [f"<th>{inline(c)}</th>" for c in kepala]
            t.append("</tr></thead><tbody>")
            for r in isi:
                t.append("<tr>" + "".join(f"<td>{inline(c)}</td>" for c in r) + "</tr>")
            t.append("</tbody></table>")
            keluar.append("".join(t))
            continue

        if s.startswith("> "):
            kutipan = []
            while i < len(baris) and baris[i].strip().startswith(">"):
                kutipan.append(baris[i].strip().lstrip(">").strip())
                i += 1
            keluar.append("<blockquote><p>" + inline(" ".join(kutipan)) + "</p></blockquote>")
            continue

        if re.match(r"^[-*]\s+", s):
            item = []
            while i < len(baris) and re.match(r"^[-*]\s+", baris[i].strip()):
                item.append(inline(re.sub(r"^[-*]\s+", "", baris[i].strip())))
                i += 1
            keluar.append("<ul>" + "".join(f"<li>{x}</li>" for x in item) + "</ul>")
            continue

        if re.match(r"^\d+\.\s+", s):
            item = []
            while i < len(baris) and re.match(r"^\d+\.\s+", baris[i].strip()):
                item.append(inline(re.sub(r"^\d+\.\s+", "", baris[i].strip())))
                i += 1
            keluar.append("<ol>" + "".join(f"<li>{x}</li>" for x in item) + "</ol>")
            continue

        # Paragraf: kumpulkan sampai baris kosong / awal blok lain
        par = []
        while i < len(baris):
            t = baris[i].strip()
            if t == "" or t.startswith(("#", ">", "|", "---")) or re.match(r"^([-*]|\d+\.)\s+", t):
                break
            par.append(t)
            i += 1
        if par:
            keluar.append("<p>" + inline(" ".join(par)) + "</p>")

    return "\n".join(keluar)


def publish(slug: str) -> None:
    berkas = SUMBER / f"{slug}.md"
    if not berkas.exists():
        sys.exit(f"Tidak ada: {berkas}")

    isi = ke_html(berkas.read_text())

    pid = subprocess.run(
        SSH + [f"cd {WP_DIR} && wp post list --post_type=page --name={slug} --field=ID --post_status=any"],
        capture_output=True, text=True, check=True).stdout.strip().split("\n")[0]
    if not pid.isdigit():
        sys.exit(f"Halaman /{slug}/ tidak ditemukan di situs")

    subprocess.run(
        SSH + [f"cd {WP_DIR} && wp post update {pid} - --post_status=publish"],
        input=isi, text=True, check=True, capture_output=True)

    tabel = isi.count("<table>")
    print(f"  /{slug}/ (ID {pid}) diperbarui — {len(isi):,} char, {tabel} tabel")


if __name__ == "__main__":
    daftar = sys.argv[1:] or HALAMAN
    print("== Publikasi halaman legal dari docs/konten-legal/ ==")
    for slug in daftar:
        publish(slug)
    subprocess.run(SSH + [f"cd {WP_DIR} && wp litespeed-purge all"], capture_output=True)
    print("Cache dibersihkan.")
