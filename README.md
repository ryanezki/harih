# hariH — Platform Undangan Digital

Platform undangan digital otomatis (WordPress/WooCommerce + n8n): pelanggan memilih paket → membayar → mengisi data → undangan terbit & terkirim otomatis via email + WhatsApp.

- **Blueprint teknis:** [docs/blueprint-undangan-digital.md](docs/blueprint-undangan-digital.md)
- **Daftar kerja & progres:** [docs/TASKS.md](docs/TASKS.md)

## Struktur repo

```
wp-content/
├── mu-plugins/
│   ├── undangan-core.php          # loader (mu-plugin: selalu aktif)
│   └── undangan-core/
│       ├── cpt.php                # CPT undangan & ucapan + 21 meta + whitelist tema
│       ├── rest.php               # endpoint RSVP + privasi REST (blok baca publik)
│       ├── woocommerce.php        # checkout ramping, validasi & normalisasi no. WA,
│       │                          #   1 order = 1 paket, ambang webhook failures
│       └── hardening.php          # ukuran gambar, XML-RPC off, anti user-enumeration
└── themes/
    └── harih/                     # child theme (parent: Astra)
        ├── functions.php          # enqueue per-halaman, OG tags, helper
        ├── single-undangan.php    # renderer undangan — standalone, mobile-first
        ├── template-parts/undangan/   # 10 section (§7 blueprint)
        └── undangan/
            ├── shared/            # kerangka CSS + JS (semua tema)
            └── tema-01/           # skin "Botanical Elegan"
```

**Arsitektur tema:** tema visual = *skin* CSS. Menambah tema baru:
1. duplikasi `undangan/tema-01/` → `undangan/tema-02/`, sesuaikan custom properties & ornamen;
2. daftarkan di `undangan_get_temas()` (`mu-plugins/undangan-core/cpt.php`) — ini whitelist `template_id`;
3. tambahkan font di `harih_tema_fonts()` (`themes/harih/functions.php`).

## Prasyarat server

- WordPress 6.x, **PHP ≥ 8.0**
- Parent theme **Astra** (gratis): `wp theme install astra`
- Plugin (T1.1): `woocommerce`, `litespeed-cache`, `fluent-smtp`, `limit-login-attempts-reloaded`, plugin Duitku
- Locale situs **id_ID** & timezone **Asia/Jakarta** (Settings → General) — dipakai format tanggal & waktu ucapan

## Deploy ke Hostinger

Upload isi `wp-content/` ke `wp-content/` situs (rsync via SSH):

```bash
rsync -avz wp-content/mu-plugins/ USER@HOST:~/public_html/wp-content/mu-plugins/
rsync -avz wp-content/themes/harih/ USER@HOST:~/public_html/wp-content/themes/harih/

# di server:
wp theme install astra
wp theme activate harih
wp rewrite flush   # wajib setelah perubahan CPT/rewrite (slug /u/)
```

Alternatif: `git clone` repo di server lalu symlink kedua folder — pilih salah satu dan konsisten.

Setelah deploy pertama, jalankan konfigurasi manual T1 di [docs/TASKS.md](docs/TASKS.md): permalink *Post name*, LiteSpeed drop query string `to`/`utm_*`/`ref`, webhook WooCommerce → n8n, FluentSMTP → Brevo, `DISABLE_WP_CRON` + cron hPanel, dst.

## Pengembangan lokal

Pakai [wp-env](https://developer.wordpress.org/block-editor/reference-guides/packages/packages-env/) (butuh Docker) dengan `.wp-env.json`:

```json
{
    "core": null,
    "themes": ["./wp-content/themes/harih", "https://downloads.wordpress.org/theme/astra.zip"],
    "mappings": { "wp-content/mu-plugins": "./wp-content/mu-plugins" },
    "config": { "FORM_TOKEN_SECRET": "dev-secret-ganti-di-produksi" }
}
```

lalu `npx @wordpress/env start`. Alternatif GUI: LocalWP + symlink dua folder `wp-content` di atas.

Membuat undangan demo lokal: buat post *Undangan* di wp-admin, isi meta (paket, template_id, nama, tanggal…) via panel Custom Fields, buka `/u/{slug}`.

## Keamanan yang sudah ditangani di kode

- Baca publik `wp/v2/undangan` diblokir; meta (rekening, kontak) tidak pernah keluar via REST tanpa auth (T1.10)
- `undangan` keluar dari sitemap, search REST, feed, oEmbed; halaman ber-`noindex` (T1.10)
- `template_id` di-whitelist — bukan path bebas (anti traversal, T1.12)
- Semua output meta di-escape (`esc_html`/`esc_url`); ucapan dirender via `textContent` (anti XSS)
- RSVP: honeypot + rate limit per IP+undangan (ramah CGNAT) + cache 60 dtk di GET (T3.2–T3.3)
- Nomor WA dinormalisasi ke `628…` sejak checkout (T1.17)
- XML-RPC mati, user-enumeration REST/`?author=` ditutup, ambang webhook WooCommerce dinaikkan (T1.11)
