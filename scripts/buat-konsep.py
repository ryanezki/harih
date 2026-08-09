#!/usr/bin/env python3
"""Bangun peta konsep model mitra hariH: satu definisi scene -> .excalidraw + .svg."""
import json, random, html

random.seed(20260809)
E, S = [], []          # elemen excalidraw, potongan svg

INK, GRAY = "#1e1e1e", "#868e96"
PAL = {
    "biru":   ("#1971c2", "#a5d8ff"),
    "hijau":  ("#2f9e44", "#b2f2bb"),
    "merah":  ("#e03131", "#ffc9c9"),
    "kuning": ("#f08c00", "#ffec99"),
    "ungu":   ("#6741d9", "#d0bfff"),
    "abu":    ("#495057", "#e9ecef"),
    "putih":  ("#1e1e1e", "transparent"),
}
FONT = '"Segoe UI", "Helvetica Neue", Arial, sans-serif'


def _base(t, x, y, w, h, stroke=INK, bg="transparent", **kw):
    d = dict(id=f"e{len(E)}", type=t, x=x, y=y, width=w, height=h, angle=0,
             strokeColor=stroke, backgroundColor=bg, fillStyle="solid", strokeWidth=1,
             strokeStyle="solid", roughness=1, opacity=100, groupIds=[], frameId=None,
             roundness={"type": 3} if t in ("rectangle", "diamond") else None,
             seed=random.randint(1, 2**31), version=1, versionNonce=random.randint(1, 2**31),
             isDeleted=False, boundElements=None, updated=1, link=None, locked=False)
    d.update(kw)
    E.append(d)
    return d


def teks(x, y, s, size=16, warna=INK, bold=False, anchor="start", w=None):
    lebar = w if w else len(s) * size * 0.55
    _base("text", x, y, lebar, size * 1.25, stroke=warna, text=s, fontSize=size,
          fontFamily=1, textAlign="left", verticalAlign="top", containerId=None,
          originalText=s, autoResize=True, lineHeight=1.25)
    ax = {"start": "start", "middle": "middle", "end": "end"}[anchor]
    tebal = ' font-weight="700"' if bold else ''
    S.append('<text x="%s" y="%s" font-family=\'%s\' font-size="%s" fill="%s" text-anchor="%s"%s>%s</text>'
             % (x, y + size, FONT, size, warna, ax, tebal, html.escape(s)))


def kotak(x, y, w, h, judul, isi=None, warna="putih", size=16, dash=False):
    st, bg = PAL[warna]
    _base("rectangle", x, y, w, h, stroke=st, bg=bg,
          strokeStyle="dashed" if dash else "solid", strokeWidth=2 if not dash else 1)
    dd = ' stroke-dasharray="7 5"' if dash else ''
    S.append('<rect x="%s" y="%s" width="%s" height="%s" rx="10" fill="%s" stroke="%s" stroke-width="%s"%s/>'
             % (x, y, w, h, bg, st, 1 if dash else 2, dd))
    cx = x + w / 2
    ty = y + 14
    if judul:
        teks(cx, ty, judul, size=size, warna=st, bold=True, anchor="middle", w=w - 20)
    if isi:
        for i, baris in enumerate(isi):
            teks(cx, ty + size * 1.6 + i * 20, baris, size=13, warna=INK, anchor="middle", w=w - 20)


def panah(x1, y1, x2, y2, label=None, warna=INK, dash=False):
    _base("arrow", x1, y1, x2 - x1, y2 - y1, stroke=warna,
          strokeStyle="dashed" if dash else "solid", strokeWidth=2,
          points=[[0, 0], [x2 - x1, y2 - y1]], lastCommittedPoint=None,
          startBinding=None, endBinding=None, startArrowhead=None,
          endArrowhead="arrow", elbowed=False)
    dd = ' stroke-dasharray="7 5"' if dash else ''
    S.append('<line x1="%s" y1="%s" x2="%s" y2="%s" stroke="%s" stroke-width="2" marker-end="url(#p)"%s/>'
             % (x1, y1, x2, y2, warna, dd))
    if label:
        teks((x1 + x2) / 2, (y1 + y2) / 2 - 20, label, size=12, warna=warna, anchor="middle")


def judul(y, t, sub=None):
    teks(50, y, t, size=26, bold=True)
    if sub:
        teks(50, y + 34, sub, size=14, warna=GRAY)


# ───────────────────────────── A. Aliran uang ─────────────────────────────
judul(40, "hariH — model mitra (grosir)", "Terkunci 9 Agustus 2026 · B1–B18 · angka dari pengukuran produksi 8 Agustus")

judul(120, "A · Siapa membayar siapa")
kotak(60, 180, 250, 110, "MITRA", ["WO · MUA · fotografer", "Percetakan Jabodetabek", "20–200 acara/tahun"], "biru")
kotak(470, 180, 250, 110, "hariH", ["produksi + sistem", "8 slot cetak/bulan"], "ungu")
kotak(60, 340, 250, 95, "MEMPELAI", ["klien MITRA,", "bukan klien hariH"], "abu")
kotak(880, 180, 250, 110, "TAMU", ["300–800 per acara", "membuka undangan"], "abu")

panah(320, 220, 460, 220, "bayar GROSIR", "#2f9e44")
panah(190, 335, 190, 300, None, "#2f9e44")
teks(205, 305, "bayar harga MITRA", size=12, warna="#2f9e44")
panah(730, 235, 870, 235, "undangan", GRAY)
kotak(1160, 180, 300, 110, "Yang TIDAK terjadi", ["hariH tidak pernah membayar mitra", "hariH tidak tahu mitra jual berapa"], "merah", size=14)
teks(470, 320, "Arah uang SELALU mitra → hariH  (B2)", size=14, warna="#2f9e44", bold=True)
teks(470, 344, "Itu yang menghapus seluruh kategori data rekening mitra.", size=12, warna=GRAY)

# ───────────────────────────── B. Produk & harga ─────────────────────────────
judul(480, "B · Yang dijual, dan selisih untuk mitra")
kotak(60, 545, 430, 150, "CETAK — sumber uang utama", [
    "Resepsi 100 pcs   eceran 2,9jt → grosir 1.650.000",
    "Grand   150 pcs   eceran 5,9jt → grosir 3.400.000",
    "spread mitra 43% & 42%  ·  ambil di tempat",
    "kirim? +Rp 150rb flat, disebut di muka", ], "hijau")
kotak(540, 545, 330, 150, "DIGITAL", [
    "Rp 99rb – 299rb", "marginal cost ≈ 0", "nasional, tidak terikat", "kuota cetak"], "biru")
kotak(920, 545, 330, 150, "SLOT TERKUNCI", [
    "retainer bulanan", "ditawarkan di ORDER KE-2 (B15)", "saldo hangus akhir bulan",
    "angkanya BELUM dikunci"], "kuning", dash=True)
kotak(1300, 545, 240, 150, "DICABUT", [
    "Paket Hormat", "1,19jt / 50 pcs", "biaya peluang", "Rp 2,37jt per slot (B9)"], "merah")

# ───────────────────────────── C. Alur order ─────────────────────────────
judul(730, "C · Alur satu order — hari ini, manual penuh, nol kode baru")
langkah = [
    ("1  Mitra WA owner", "\"Resepsi untuk klien saya\""),
    ("2  Owner buat order", "WooCommerce → processing"),
    ("3  WF-01 menyala", "baris sheet + link form"),
    ("4  Mempelai isi data", "form bertoken"),
    ("5  WF-02 generate", "undangan tayang"),
    ("6  Proof → produksi", "mitra AMBIL di tempat"),
]
for i, (a, b) in enumerate(langkah):
    x = 60 + i * 250
    kotak(x, 795, 215, 80, a, [b], "abu", size=13)
    if i < len(langkah) - 1:
        panah(x + 215, 835, x + 250, 835)
teks(60, 895, "Satu-satunya yang belum otomatis: mitra_id masih diisi tangan  →  M2", size=14, warna="#f08c00", bold=True)
teks(60, 918, "Setelah M1/M3/M7: mitra login ke portal, lihat harga & sisa slotnya sendiri, buat order sendiri.", size=13, warna=GRAY)

# ───────────────────────────── D. White-label ─────────────────────────────
judul(970, "D · White-label = nama mitra muncul di tempat yang dilihat orang — BUKAN website terpisah")
baris = [
    ("Undangan CETAK + amplop", "mempelai, tamu, mitra", "PUTIH 100% — nol tanda hariH", "hijau"),
    ("Kaki undangan digital", "tamu yang scroll", "nama mitra — SELESAI (R4)", "hijau"),
    ("Kartu OG / pratinjau WA", "SETIAP tamu penerima link", "masih 'hariH' — celah terbesar", "merah"),
    ("URL harih.id/u/...", "siapa pun yang baca address bar", "tidak dibangun — mahal, nilainya kecil", "abu"),
    ("Portal mitra", "hanya mitra", "belum ada → M3", "kuning"),
]
teks(60, 1030, "PERMUKAAN", size=13, warna=GRAY, bold=True)
teks(430, 1030, "DILIHAT SIAPA", size=13, warna=GRAY, bold=True)
teks(830, 1030, "STATUS", size=13, warna=GRAY, bold=True)
for i, (p, s_, st, w) in enumerate(baris):
    y = 1055 + i * 46
    kotak(60, y, 1400, 38, "", None, w)
    teks(78, y + 10, p, size=14, bold=True)
    teks(448, y + 11, s_, size=13, warna=GRAY)
    teks(848, y + 11, st, size=13, warna=PAL[w][0], bold=True)
teks(60, 1300, "Produk yang paling mahal sudah putih sepenuhnya. Yang tersisa cuma kartu OG — satu tempat di og.php.", size=14, warna="#e03131", bold=True)

# ───────────────────────────── E. Kuota ─────────────────────────────
judul(1360, "E · Kuota 8 slot cetak / bulan")
for i in range(8):
    x = 60 + i * 70
    kotak(x, 1420, 58, 58, "", None, "kuning" if i < 6 else "abu", dash=i >= 6)
teks(60, 1495, "Mitra dapat jatah lebih dulu; publik kebagian sisa (B7). Ledger alokasi = M4.", size=13, warna=GRAY)
kotak(700, 1405, 480, 90, "8 bukan batas kapasitas", [
    "8 × 1,7 jam tangan ≈ 14 jam — ruang nyata jauh lebih besar.",
    "Ditahan di 8 karena belum pernah satu pesanan pun dikirim tepat waktu (B11).",
    "JANGAN dijual sebagai kelangkaan."], "merah", size=14)

# ───────────────────────────── F. Fase & gerbang ─────────────────────────────
judul(1560, "F · Fase & gerbang uang")
kotak(60, 1620, 300, 100, "F0 — JUAL", ["nol kode · harga terkunci", "pesan pembuka siap kirim"], "hijau")
kotak(470, 1620, 300, 100, "F1 — infrastruktur", ["M1 M2 M3 M6 M7 M8", "5–8 hari kerja"], "biru")
kotak(880, 1620, 300, 100, "F2 — kapasitas", ["M4 ledger slot", "M5 rujukan"], "biru")
kotak(1290, 1620, 250, 100, "F3 — skala", ["luar Jabodetabek", "occasion baru"], "abu", dash=True)
for x, t in ((370, "3 mitra BAYAR"), (780, "8 slot terisi"), (1190, "20 mitra")):
    _base("diamond", x, 1630, 90, 80, stroke="#e03131", bg="#ffc9c9")
    S.append(f'<polygon points="{x+45},1630 {x+90},1670 {x+45},1710 {x},1670" fill="#ffc9c9" stroke="#e03131" stroke-width="2"/>')
    teks(x + 45, 1718, t, size=12, warna="#e03131", bold=True, anchor="middle", w=140)
kotak(60, 1760, 1480, 60, "Urutan DIBALIK atas keputusan owner: web dibangun lebih dulu, telepon menyusul.",
      ["Akibatnya M8 lahir dari tebakan, bukan dari keberatan nyata F0.5 — tulis struktural dulu, revisi setelah 10 percakapan pertama."],
      "kuning", size=15)

# ───────────────────────────── tulis ─────────────────────────────
doc = {"type": "excalidraw", "version": 2, "source": "hariH", "elements": E,
       "appState": {"gridSize": None, "viewBackgroundColor": "#ffffff"}, "files": {}}
open("docs/konsep-mitra.excalidraw", "w").write(json.dumps(doc, indent=1))

svg = (f'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1560 1860" width="100%">'
       f'<defs><marker id="p" markerWidth="10" markerHeight="7" refX="9" refY="3.5" orient="auto">'
       f'<polygon points="0 0, 10 3.5, 0 7" fill="{INK}"/></marker></defs>'
       f'<rect width="1560" height="1860" fill="#ffffff"/>' + "".join(S) + "</svg>")
open("docs/konsep-mitra.svg", "w").write(svg)
print(f"elemen excalidraw: {len(E)}  ·  potongan svg: {len(S)}")
