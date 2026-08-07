#!/usr/bin/env python3
"""Uji happy-path WF-02 end-to-end (A3) — jalur pembeli sah, dari nol sampai undangan terbit.

KENAPA ADA: sampai 2026-08-07 seluruh uji otomatis proyek ini adalah uji NEGATIF
(`cek-live.sh` hanya menguji token salah → 403). Jalur `route:'ok'` — satu-satunya
jalur yang dilewati pembeli yang benar-benar membayar — tidak pernah dieksekusi
sekali pun, dan itulah sebabnya `ReferenceError` pada `adaCetak` bertahan dua hari
sambil smoke test tetap 21/21 hijau.

CARA PAKAI
    python3 scripts/uji-happy-path.py 628xxxxxxxxxx            # jalankan + bersihkan
    python3 scripts/uji-happy-path.py 628xxxxxxxxxx --sisakan  # jangan bersihkan
    python3 scripts/uji-happy-path.py --bersihkan               # hapus sisa uji saja

ARGUMEN nomor WA wajib: pesan delivery BENAR-BENAR dikirim ke nomor itu (3 pesan).
Pakai nomor yang bisa kamu periksa. Email dikirim ke OWNER_EMAIL di vps/.env.

YANG DIUJI
    1. paket `premium`       → undangan tier premium
    2. paket `premium+cetak` → undangan tier premium JUGA (bukan hemat), dan
       `ada_cetak=true` sehingga pesan delivery memakai blok daftar tamu,
       bukan tawaran upsell. Ini regresi yang ditutup A1 — kalau ia kambuh,
       kasus 2 menghasilkan undangan tanpa galeri dengan masa aktif 7 hari.

⚠️  DIHARAPKAN GAGAL: node `Set Order Completed (WC)` akan melempar
    `NodeApiError: ID tidak valid (400)` karena skrip ini hanya membuat baris
    SHEET, bukan order WooCommerce. Kegagalan itu terjadi SETELAH delivery, jadi
    tidak membatalkan apa pun — tapi ia menandai eksekusi sebagai `error` dan
    MEMICU WF-00. Dua alert ke owner per jalannya skrip ini adalah normal.
    (Efek samping yang berguna: itu sekaligus membuktikan ikatan errorWorkflow.)

PRASYARAT: vps/.env + vps/google-sa.json (lokal, gitignored) = cermin server.
"""
import warnings; warnings.filterwarnings('ignore')
import sys, os, io, json, time, hmac, hashlib

try:
    import requests
    from google.oauth2 import service_account
    from googleapiclient.discovery import build
except ImportError as e:
    sys.exit(f'Modul kurang: {e}. Pasang: pip3 install requests google-api-python-client google-auth')

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
TAB = 'orders'
UJI = [(900001, 'premium'), (900002, 'premium+cetak')]
WEBHOOK = 'https://n8n.harih.id/webhook/form-undangan'


def env():
    p = f'{ROOT}/vps/.env'
    if not os.path.exists(p):
        sys.exit(f'{p} tidak ada — lihat vps/.env.example')
    d = dict(l.split('=', 1) for l in open(p) if '=' in l and not l.lstrip().startswith('#'))
    return {k.strip(): v.strip().strip('"').strip("'") for k, v in d.items()}


E = env()
_cred = service_account.Credentials.from_service_account_file(
    f'{ROOT}/vps/google-sa.json', scopes=['https://www.googleapis.com/auth/spreadsheets'])
_api = lambda: build('sheets', 'v4', credentials=_cred, cache_discovery=False).spreadsheets()


def baca():
    v = _api().values().get(spreadsheetId=E['SHEET_ID'], range=f'{TAB}!A:Z').execute().get('values', [])
    if not v:
        return [], []
    hdr = v[0]
    return hdr, [dict(zip(hdr, r + [''] * (len(hdr) - len(r)))) for r in v[1:]]


def token(oid, halaman='isi-data'):
    """Rumus identik undangan_token_halaman() di PHP, WF-01, dan node
    `Verifikasi Token` WF-02.

    Bahan HMAC BERCAKUP sejak B9 (2026-08-07): `"{order_id}|{halaman}"`. Tanpa
    sufiks halaman, submit form dibalas 403 — dan gejalanya mudah disalahartikan
    sebagai pipeline yang rusak, padahal cuma rumus token yang tertinggal.
    """
    bahan = f'{oid}|{halaman}'.encode()
    return hmac.new(E['FORM_TOKEN_SECRET'].encode(), bahan, hashlib.sha256).hexdigest()[:16]


def sisip(oid, paket, wa):
    hdr, _ = baca()
    row = {'order_id': str(oid), 'tgl_order': time.strftime('%Y-%m-%d %H:%M:%S'),
           'nama': 'Rangga Adiputra', 'email': E['OWNER_EMAIL'], 'wa': wa, 'paket': paket,
           'kupon': '', 'total': '2900000' if 'cetak' in paket else '299000',
           'token': token(oid), 'status': 'MENUNGGU_DATA', 'link_undangan': '',
           'tgl_acara': '', 'wa_status': '', 'exec_id': 'A3-UJI'}
    _api().values().append(spreadsheetId=E['SHEET_ID'], range=f'{TAB}!A:Z', valueInputOption='RAW',
                           insertDataOption='INSERT_ROWS',
                           body={'values': [[row.get(h, '') for h in hdr]]}).execute()
    return row


def bersihkan():
    meta = _api().get(spreadsheetId=E['SHEET_ID']).execute()
    sid = next(s['properties']['sheetId'] for s in meta['sheets'] if s['properties']['title'] == TAB)
    _, rows = baca()
    target = {str(o) for o, _ in UJI}
    idx = [i for i, r in enumerate(rows) if r.get('order_id') in target]
    reqs = [{'deleteDimension': {'range': {'sheetId': sid, 'dimension': 'ROWS',
             'startIndex': i + 1, 'endIndex': i + 2}}} for i in sorted(idx, reverse=True)]
    if reqs:
        _api().batchUpdate(spreadsheetId=E['SHEET_ID'], body={'requests': reqs}).execute()
    print(f'  {len(reqs)} baris sheet uji dihapus')
    print('  ⚠️  Undangan & media di WordPress TIDAK ikut terhapus otomatis. Jalankan di Hostinger:')
    print("     wp post list --post_type=undangan --fields=ID,post_name   # cari rangga-sekar-*")
    print("     wp post list --post_type=attachment --fields=ID,post_title | grep undangan-9000")
    print('     wp post delete <ID media…> <ID undangan…> --force   # --force agar kartu OG ikut bersih')


def png(w, h, warna):
    import zlib, struct
    raw = b''.join(b'\x00' + bytes(warna) * w for _ in range(h))
    def bab(t, d):
        return struct.pack('>I', len(d)) + t + d + struct.pack('>I', zlib.crc32(t + d) & 0xffffffff)
    return (b'\x89PNG\r\n\x1a\n'
            + bab(b'IHDR', struct.pack('>IIBBBBB', w, h, 8, 2, 0, 0, 0))
            + bab(b'IDAT', zlib.compress(raw)) + bab(b'IEND', b''))


def kirim(row, n_foto=3):
    """Nama field diambil dari body.* yang dibaca WF-02 + name= di page-isi-data.php.
    Foto sebagai foto_1..foto_N (isi-data.js memakai append('foto_' + i)) — BUKAN foto[]."""
    tgl = time.strftime('%Y-%m-%d', time.localtime(time.time() + 60 * 86400))
    data = {
        'order': row['order_id'], 'key': row['token'],
        'nama_pria': 'Rangga Adiputra', 'nama_wanita': 'Sekar Ayu Pramesti',
        'ortu_pria': 'Bpk. Hendra Adiputra & Ibu Sri Wahyuni',
        'ortu_wanita': 'Bpk. Bagas Pramesti & Ibu Ratna Dewi',
        'anak_ke_pria': 'Putra pertama dari', 'anak_ke_wanita': 'Putri kedua dari',
        'tanggal_resepsi': tgl, 'waktu_resepsi': '11.00 - 14.00 WIB',
        'lokasi_nama': 'Gedung Graha Wredatama',
        'lokasi_alamat': 'Jl. Diponegoro No. 12, Semarang',
        'tanggal_akad': tgl, 'waktu_akad': '08.00 WIB',
        'lokasi_akad_nama': 'Masjid Agung Jawa Tengah',
        'lokasi_akad_alamat': 'Jl. Gajah Raya, Semarang',
        'template_id': 'tema-01', 'nuansa': 'islam',
        'love_story': '2019 — Pertama bertemu\n2024 — Lamaran\n2026 — Menikah',
        'rekening': 'BCA 1234567890 a.n. Sekar Ayu Pramesti',
        'alamat_kado': 'Jl. Diponegoro No. 12, Semarang',
        'dresscode': 'Earth tone', 'dresscode_warna': '#a3835f,#6b6758',
        'turut_mengundang': 'Keluarga Besar Adiputra\nKeluarga Besar Pramesti',
        'galeri_tata': 'kolase', 'jumlah_foto': str(n_foto),
    }
    warna = [(210, 180, 140), (180, 160, 200), (160, 200, 180)]
    files = [(f'foto_{i}', (f'a3-{i}.png', io.BytesIO(png(1200, 1600, warna[(i - 1) % 3])), 'image/png'))
             for i in range(1, n_foto + 1)]
    t0 = time.time()
    r = requests.post(WEBHOOK, data=data, files=files, timeout=90)
    return r, time.time() - t0


def tunggu(oid, batas=90):
    for _ in range(batas // 5):
        time.sleep(5)
        _, rows = baca()
        row = next((x for x in rows if x['order_id'] == str(oid)), None)
        if not row:
            return None
        if row['status'] in ('SUDAH_JADI', 'GAGAL') or row['link_undangan']:
            return row
    return row


def main():
    args = [a for a in sys.argv[1:]]
    if '--bersihkan' in args:
        bersihkan(); return 0
    wa = next((a for a in args if a.isdigit()), None)
    if not wa or not wa.startswith('62'):
        sys.exit(__doc__)
    sisakan = '--sisakan' in args

    gagal = 0
    for oid, paket in UJI:
        print(f'\n=== order {oid} · paket={paket} ===')
        row = sisip(oid, paket, wa)
        r, dt = kirim(row)
        print(f'  POST → HTTP {r.status_code} ({dt:.1f} dtk)')
        if r.status_code != 200:
            print(f'  GAGAL: {r.text[:200]}'); gagal += 1; continue
        hasil = tunggu(oid)
        if not hasil or hasil['status'] != 'SUDAH_JADI':
            print(f'  GAGAL: status akhir {hasil and hasil["status"]}'); gagal += 1; continue
        print(f'  status={hasil["status"]} wa_status={hasil["wa_status"]}')
        print(f'  {hasil["link_undangan"]}')
        h = requests.get(hasil['link_undangan'], params={'v': oid}, timeout=30).text
        # Nama berkas memuat komponen acak 8 hex sejak B8 — jangan cocokkan
        # `undangan-{oid}-foto-N` lagi, itu akan selalu nol dan melaporkan
        # "tier salah" padahal galerinya baik-baik saja.
        n = len(set(__import__('re').findall(r'undangan-%s-[0-9a-f]{8}-foto-\d+' % oid, h)))
        ok = n == 3
        print(f'  galeri: {n}/3 foto ter-render → tier {"premium ✓" if ok else "SALAH (jatuh ke hemat?)"}')
        gagal += 0 if ok else 1

    print('\n' + ('=' * 52))
    print('LULUS — jalur pembeli sah utuh' if not gagal else f'{gagal} KEGAGALAN')
    print('Periksa HP: pesan order 900002 harus memuat link DAFTAR TAMU,')
    print('order 900001 harus memuat tawaran UPSELL. Beda blok = ada_cetak benar.')
    if not sisakan:
        print('\nmembersihkan…'); bersihkan()
    return 1 if gagal else 0


if __name__ == '__main__':
    sys.exit(main())
