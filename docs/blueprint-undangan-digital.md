# Blueprint Teknis: Platform Undangan Digital Otomatis

**Versi:** 1.0 (MVP) · **Target eksekusi:** 4 minggu · **Stack:** WordPress/WooCommerce (Hostinger Single) + n8n (VPS terpisah)

**Tujuan produk:** pelanggan memilih template → membayar → mengisi data → undangan digital jadi dan terkirim otomatis via email + WhatsApp dalam hitungan menit, 24 jam nonstop, tanpa campur tangan manusia.

---

## 1. Keputusan Arsitektur (final — jangan diperdebatkan ulang saat coding)

| # | Keputusan | Alasan |
|---|-----------|--------|
| A1 | Undangan = **Custom Post Type** `undangan` di WordPress, URL `domain.com/u/{slug}` | Lebih cepat dibangun & di-maintain daripada file statis. LiteSpeed Cache membuat performa setara halaman statis. |
| A2 | Nama tamu dipersonalisasi via URL param `?to=Nama`, **dirender client-side (JS)** | Supaya semua tamu memakai 1 cache halaman yang sama. LiteSpeed diset drop query string `to`. |
| A3 | Alur order: **bayar dulu → dapat link form isi data (bertoken) → n8n generate** | Standar industri undangan digital; mencegah pengisian data tanpa bayar. |
| A4 | Form isi data = **page template custom** yang POST multipart langsung ke webhook n8n | Kontrol penuh, gratis (tanpa plugin form berbayar), upload foto ditangani n8n. |
| A5 | Tracking reseller via **kupon WooCommerce unik per reseller** (prefix `RES-`) | Cara paling akurat & sederhana; customer dapat diskon, reseller dapat komisi. |
| A6 | WhatsApp via **WAHA (Docker, self-hosted di VPS n8n, gratis)**; fallback Fonnte | Biaya Rp 0. Pakai nomor WA khusus bisnis (bukan nomor pribadi). |
| A7 | Email transaksional via **Brevo** (gratis 300 email/hari), bukan mailbox Hostinger | Deliverability lebih baik, ada API untuk n8n. |
| A8 | Payment gateway: **Tripay atau Duitku** (mendukung pendaftaran perorangan, QRIS/VA/e-wallet). Midtrans jika sudah ada badan usaha | QRIS wajib untuk pasar Indonesia. |
| A9 | Data operasional (log order, komisi) di **Google Sheets** dulu | Nol setup, owner bisa lihat langsung. Migrasi ke Postgres di VPS jika sudah >500 order/bulan. |
| A10 | RSVP/ucapan = CPT `ucapan` (non-public) + custom REST endpoint dengan honeypot & rate limit | Tanpa plugin tambahan, data terlihat rapi di wp-admin. |

---

## 2. Arsitektur Sistem

```
                         ┌──────────────────────────────────────┐
                         │  HOSTINGER (Single Web Hosting)      │
  Customer ──checkout──▶ │  WordPress + WooCommerce             │
  Tamu ──buka undangan─▶ │  • Toko & katalog template           │
                         │  • CPT undangan  (/u/{slug})         │
                         │  • Form isi data (bertoken)          │
                         │  • REST API + Application Password   │
                         └───────┬──────────────────▲───────────┘
                    webhook      │                  │  REST (create post,
                    order.updated│                  │  upload media, kupon)
                                 ▼                  │
                         ┌──────────────────────────┴───────────┐
                         │  VPS (sudah dimiliki)                │
                         │  n8n  = otak otomasi                 │
                         │  WAHA = WhatsApp gateway (Docker)    │
                         └───────┬──────────────────────────────┘
                                 │
                 ┌───────────────┼───────────────┬──────────────┐
                 ▼               ▼               ▼              ▼
              Brevo          Google Sheets   qrserver.com   Tripay/Duitku
              (email)        (log & komisi)  (QR code)      (pembayaran)
```

---

## 3. Prasyarat & Kredensial (siapkan sebelum coding)

| Variabel (env di n8n) | Sumber | Keterangan |
|---|---|---|
| `WP_URL` | — | `https://domain.com` |
| `WP_APP_USER` / `WP_APP_PASSWORD` | WP-CLI (lihat §4) | Auth Basic untuk REST API |
| `WC_CK` / `WC_CS` | WooCommerce → Settings → Advanced → REST API | Scope Read/Write (kupon, order) |
| `WC_WEBHOOK_SECRET` | Saat membuat webhook WooCommerce | Verifikasi HMAC |
| `FORM_TOKEN_SECRET` | Generate acak 32 char | **Nilai sama** didefinisikan di `wp-config.php` |
| `BREVO_API_KEY` | brevo.com (akun gratis) | Verifikasi domain pengirim (SPF/DKIM) |
| `WAHA_URL` | VPS sendiri | mis. `http://localhost:3000` dari n8n |
| `SHEET_ID` + Google Service Account | Google Cloud Console | Share sheet ke email service account |
| Kredensial Tripay/Duitku | Daftar merchant | Mulai mode sandbox |

Kebutuhan lain dari owner: nama brand + domain (gratis dari paket), nomor WA khusus bisnis, desain 3 tema pertama (desainer atau beli template HTML berlisensi lalu diadaptasi), rekening payout reseller.

---

## 4. Setup Infrastruktur (SSH ke Hostinger, jalankan WP-CLI)

```bash
# 1. Plugin inti (semua gratis)
wp plugin install woocommerce litespeed-cache fluent-smtp --activate
wp plugin install limit-login-attempts-reloaded --activate

# 2. Payment gateway — pilih salah satu (cek nama slug terbaru di wp.org):
wp plugin install tripay-payment-gateway --activate
# atau plugin resmi Duitku / Midtrans sesuai akun merchant yang dipakai

# 3. Buat user bot untuk n8n + Application Password
wp user create n8nbot bot@domain.com --role=administrator
wp user application-password create n8nbot n8n --porcelain
# → simpan output sebagai WP_APP_PASSWORD

# 4. Keamanan dasar
wp option update blog_public 1
wp config set FORM_TOKEN_SECRET "ISI_32_KARAKTER_ACAK" --type=constant
```

Konfigurasi manual sekali jalan:
1. **WooCommerce → Settings → Advanced → Webhooks**: buat webhook `Order updated`, Delivery URL = `https://n8n-anda.com/webhook/wc-order`, Secret = `WC_WEBHOOK_SECRET`.
2. **LiteSpeed Cache → Cache → Drop Query String**: tambahkan `to`, `utm_source`, `utm_medium`, `utm_campaign`, `ref`.
3. **Permalink**: Post name. **XML-RPC**: matikan (LiteSpeed → Toolbox atau filter).
4. **FluentSMTP**: sambungkan Brevo (untuk email bawaan WooCommerce — invoice dsb.).
5. Di VPS, jalankan WAHA: `docker run -d --name waha --restart=always -p 3000:3000 -v waha:/app/.sessions devlikeapro/waha` lalu scan QR sesi `default` dengan nomor WA bisnis.

---

## 5. Data Model

**CPT `undangan`** (public, `show_in_rest`) — meta (semua string; `galeri` berisi JSON array URL):

`paket` (hemat|favorit|premium) · `template_id` (tema-01…) · `order_id` · `nama_pria` · `nama_wanita` · `ortu_pria` · `ortu_wanita` · `tanggal_akad` · `waktu_akad` · `tanggal_resepsi` · `waktu_resepsi` · `lokasi_nama` · `lokasi_alamat` · `gmaps_url` · `love_story` · `galeri` · `musik_url` · `video_url` · `rekening` · `qris_media_url` · `wa_cp`

**CPT `ucapan`** (non-public, tampil di wp-admin) — title=nama tamu, content=pesan, meta: `undangan_id`, `hadir` (hadir|tidak|ragu).

**Google Sheets** (3 tab):
- `orders`: order_id · tgl_order · nama · email · wa · paket · kupon · total · token · status (`MENUNGGU_DATA`/`SUDAH_JADI`) · link_undangan · tgl_acara
- `resellers`: kode · nama · wa · bank · norek · tgl_daftar
- `komisi`: tgl · order_id · kode · total_order · komisi · status (`UNPAID`/`PAID`)

---

## 6. Kode Inti WordPress

Buat file `wp-content/mu-plugins/undangan-core.php` (mu-plugin = selalu aktif, tidak bisa dimatikan tak sengaja):

```php
<?php
/** Plugin Name: Undangan Core */
if (!defined('ABSPATH')) exit;

/* ---- 1. Custom Post Types ---- */
add_action('init', function () {
    register_post_type('undangan', [
        'labels' => ['name' => 'Undangan', 'singular_name' => 'Undangan'],
        'public' => true, 'show_in_rest' => true, 'rest_base' => 'undangan',
        'supports' => ['title', 'custom-fields'],
        'rewrite' => ['slug' => 'u', 'with_front' => false],
        'menu_icon' => 'dashicons-heart', 'exclude_from_search' => true,
    ]);
    register_post_type('ucapan', [
        'labels' => ['name' => 'Ucapan (RSVP)', 'singular_name' => 'Ucapan'],
        'public' => false, 'show_ui' => true, 'supports' => ['title', 'editor'],
    ]);
});

/* ---- 2. Meta fields (terbuka untuk REST, hanya user login yang bisa tulis) ---- */
add_action('init', function () {
    $fields = ['paket','template_id','order_id','nama_pria','nama_wanita',
        'ortu_pria','ortu_wanita','tanggal_akad','waktu_akad','tanggal_resepsi',
        'waktu_resepsi','lokasi_nama','lokasi_alamat','gmaps_url','love_story',
        'galeri','musik_url','video_url','rekening','qris_media_url','wa_cp'];
    foreach ($fields as $f) {
        register_post_meta('undangan', $f, [
            'type' => 'string', 'single' => true, 'show_in_rest' => true,
            'sanitize_callback' => 'sanitize_textarea_field',
            'auth_callback' => function () { return current_user_can('edit_posts'); },
        ]);
    }
});

/* ---- 3. Endpoint RSVP: tulis & baca ---- */
add_action('rest_api_init', function () {
    register_rest_route('undangan/v1', '/rsvp', [
        'methods' => 'POST', 'permission_callback' => '__return_true',
        'callback' => function (WP_REST_Request $r) {
            if (!empty($r['website'])) return ['ok' => true]; // honeypot untuk bot

            $ip = $_SERVER['REMOTE_ADDR'] ?? '0';                // rate limit:
            if (get_transient('rsvp_' . md5($ip)))               // 1 kiriman /
                return new WP_Error('too_fast', 'Coba lagi sebentar.', ['status' => 429]); // 30 dtk / IP
            set_transient('rsvp_' . md5($ip), 1, 30);

            $uid   = absint($r['undangan_id']);
            $nama  = sanitize_text_field($r['nama'] ?? '');
            $pesan = sanitize_textarea_field($r['pesan'] ?? '');
            $hadir = in_array($r['hadir'] ?? '', ['hadir','tidak','ragu'], true) ? $r['hadir'] : 'ragu';
            if (!$uid || get_post_type($uid) !== 'undangan' || $nama === '')
                return new WP_Error('bad_request', 'Data tidak lengkap.', ['status' => 400]);

            $id = wp_insert_post(['post_type' => 'ucapan', 'post_status' => 'publish',
                'post_title' => $nama, 'post_content' => $pesan,
                'meta_input' => ['undangan_id' => $uid, 'hadir' => $hadir]]);
            return ['ok' => (bool) $id];
        },
    ]);
    register_rest_route('undangan/v1', '/rsvp/(?P<id>\d+)', [
        'methods' => 'GET', 'permission_callback' => '__return_true',
        'callback' => function (WP_REST_Request $r) {
            $posts = get_posts(['post_type' => 'ucapan', 'numberposts' => 50,
                'meta_key' => 'undangan_id', 'meta_value' => absint($r['id'])]);
            return array_map(fn($p) => [
                'nama' => $p->post_title, 'pesan' => $p->post_content,
                'hadir' => get_post_meta($p->ID, 'hadir', true),
                'waktu' => get_the_date('d M Y H:i', $p),
            ], $posts);
        },
    ]);
});

/* ---- 4. Hemat disk & inodes: jangan generate ukuran gambar berlebih ---- */
add_filter('intermediate_image_sizes_advanced', function ($sizes) {
    unset($sizes['thumbnail'], $sizes['medium_large'], $sizes['1536x1536'], $sizes['2048x2048']);
    return $sizes; // sisakan medium & large saja
});
add_filter('big_image_size_threshold', fn() => 1600);
```

---

## 7. Frontend Halaman Undangan

File `single-undangan.php` di theme (child theme). WordPress otomatis memakainya untuk CPT `undangan`. Tema visual dipilih berdasarkan meta `template_id` → assets di `theme/undangan/{template_id}/style.css`.

**Urutan section (mobile-first — 95%+ tamu membuka dari WA di HP):**
1. **Cover fullscreen**: foto/ilustrasi, "Kepada Yth. `<span class="guest-name">`", tombol *Buka Undangan* (tap → scroll + play musik; autoplay audio diblokir browser tanpa interaksi).
2. Countdown ke tanggal resepsi.
3. Profil mempelai + orang tua.
4. Detail akad & resepsi + tombol Google Maps (`gmaps_url`).
5. Love story — hanya jika `paket ≠ hemat`.
6. Galeri foto (JSON `galeri`) — hanya jika `paket ≠ hemat`.
7. Video/live streaming embed — hanya jika `paket = premium`.
8. **Amplop digital**: nomor rekening + tombol *Salin* (`navigator.clipboard`), gambar QRIS (`qris_media_url`) — jika `paket ≠ hemat`.
9. **RSVP + daftar ucapan** (JS fetch ke endpoint §6).
10. Footer + tombol share WA.

**Snippet wajib — nama tamu client-side (kunci strategi cache A2):**
```html
<script>
  const nama = new URLSearchParams(location.search).get('to');
  document.querySelectorAll('.guest-name').forEach(el => {
    el.textContent = nama || 'Bapak/Ibu/Saudara/i';
  });
</script>
```

**Snippet RSVP:**
```js
fetch('/wp-json/undangan/v1/rsvp', {
  method: 'POST', headers: {'Content-Type': 'application/json'},
  body: JSON.stringify({ undangan_id: UNDANGAN_ID, nama, pesan, hadir, website: '' })
}).then(r => r.json()).then(() => muatUlangDaftarUcapan());
```

**Aturan aset:** semua gambar WebP ≤ 300 KB; musik = library instrumental bebas royalti yang kita sediakan (jangan sediakan lagu populer berhak cipta — risiko hukum; jika customer meng-upload lagu sendiri, tanggung jawab ada di customer dan dicantumkan di ToS).

---

## 8. Halaman Form Isi Data (page template `page-isi-data.php`)

Dibuka dari link email/WA: `https://domain.com/isi-data/?order=123&key=a1b2c3...`

**Verifikasi token server-side (di atas template):**
```php
$order = absint($_GET['order'] ?? 0);
$key   = sanitize_text_field($_GET['key'] ?? '');
$valid = $order && hash_equals(
    substr(hash_hmac('sha256', (string) $order, FORM_TOKEN_SECRET), 0, 16), $key
);
if (!$valid) { status_header(403); exit('Link tidak valid. Hubungi CS.'); }
```

**Field form:** template pilihan (radio bergambar) · nama & ortu kedua mempelai · tanggal/waktu akad & resepsi · nama & alamat lokasi · link Google Maps · love story (textarea, opsional) · upload foto (max 10 file, masing-masing ≤ 2 MB — validasi JS sebelum submit) · pilihan musik (dropdown library) · rekening & nama pemilik · upload gambar QRIS (opsional) · nomor WA CP.

Field yang muncul mengikuti paket (dibaca dari data order yang di-embed saat render — n8n menaruh nama paket di link, atau fetch ringan ke endpoint status).

**Submit:** JS `FormData` → `POST https://n8n-anda.com/webhook/form-undangan` (multipart, sertakan `order` & `key`). Tampilkan progress bar + halaman sukses: *"Undangan sedang dibuat, cek WhatsApp Anda dalam ±5 menit."*

---

## 9. Spesifikasi Workflow n8n (node-by-node)

> Semua webhook n8n memakai path acak panjang. Semua secret di environment variable n8n, bukan hardcode.

### WF-01 — Order Intake (`POST /webhook/wc-order`)
1. **Webhook** (aktifkan opsi *Raw Body* — dibutuhkan untuk verifikasi HMAC).
2. **Code — verifikasi signature** (tolak jika tidak cocok):
```js
const crypto = require('crypto');
const sig = $json.headers['x-wc-webhook-signature'];
const raw = Buffer.from($binary.data.data, 'base64'); // akses raw body; sesuaikan dgn versi n8n
const expect = crypto.createHmac('sha256', $env.WC_WEBHOOK_SECRET).update(raw).digest('base64');
if (sig !== expect) throw new Error('Invalid signature');
return [{ json: JSON.parse(raw.toString()) }];
```
3. **IF** `status == "processing"` (artinya sudah dibayar). Selain itu → stop.
4. **Google Sheets — lookup** `order_id` di tab `orders`. **IF ditemukan → stop** (idempotency; webhook WooCommerce bisa terkirim berulang).
5. **Code — generate token**:
```js
const crypto = require('crypto');
const token = crypto.createHmac('sha256', $env.FORM_TOKEN_SECRET)
  .update(String($json.id)).digest('hex').slice(0, 16);
```
6. **Sheets — append** baris order, status `MENUNGGU_DATA`.
7. **IF kupon diawali `RES-`** → **Sheets append** ke tab `komisi`: komisi = 30% × total setelah diskon, status `UNPAID`.
8. **Brevo — kirim email**: terima kasih + link form `…/isi-data/?order={id}&key={token}`.
9. **WAHA — kirim WA** (`POST {WAHA_URL}/api/sendText`, `chatId: "62xxxx@c.us"`): pesan yang sama.

### WF-02 — Generate Undangan (`POST /webhook/form-undangan`, multipart)
1. **Webhook** menerima field + file binary.
2. **Code — verifikasi token** (rumus sama dengan WF-01 langkah 5; tolak jika beda).
3. **Sheets — lookup order**: harus berstatus `MENUNGGU_DATA`. Jika `SUDAH_JADI` → balas link undangan yang sudah ada (idempoten, aman jika user submit dua kali).
4. **Loop per file foto** → **HTTP Request** `POST {WP_URL}/wp-json/wp/v2/media` (Auth Basic `WP_APP_USER:WP_APP_PASSWORD`, header `Content-Disposition: attachment; filename="x.jpg"`, body binary) → kumpulkan `source_url` jadi array. Lakukan sama untuk gambar QRIS.
5. **Code — susun payload**: `slug = slugify(nama_pria + '-' + nama_wanita) + '-' + 4 char acak`; `galeri = JSON.stringify(arrayUrl)`.
6. **HTTP Request** `POST {WP_URL}/wp-json/wp/v2/undangan`:
```json
{ "title": "Rina & Bima", "slug": "rina-bima-x7k2", "status": "publish",
  "meta": { "paket": "favorit", "template_id": "tema-02", "order_id": "123",
            "nama_pria": "...", "galeri": "[\"https://...\"]", "...": "..." } }
```
7. **HTTP Request** ambil QR code: `https://api.qrserver.com/v1/create-qr-code/?size=600x600&data={link_undangan}` (binary).
8. **Brevo** — email delivery: link undangan, lampiran QR, panduan cara share (`link?to=Nama%20Tamu`).
9. **WAHA** — WA delivery dengan isi sama.
10. **Sheets — update** baris: `link_undangan`, `tgl_acara`, status `SUDAH_JADI`.
11. **HTTP Request** `PUT {WP_URL}/wp-json/wc/v3/orders/{id}` (Auth `WC_CK:WC_CS`) → `{"status":"completed"}`.

### WF-03 — Onboarding Reseller (`POST /webhook/daftar-reseller`)
Form landing "Jadi Reseller" (nama, WA, bank, no. rekening) → generate kode `RES-XXXX` → **HTTP** `POST /wp-json/wc/v3/coupons` `{"code":"RES-XXXX","discount_type":"percent","amount":"10","individual_use":true}` → **Sheets** append tab `resellers` → **WAHA** kirim welcome kit (kode + link katalog + contoh copywriting promosi).

### WF-04 — Rekap Komisi (Cron: Senin 09:00 WIB)
Baca tab `komisi` status `UNPAID` → group by kode reseller → **WAHA** kirim rekap ke tiap reseller + total ke owner. Payout: owner transfer manual, lalu ubah status → `PAID` di sheet (v1 cukup manual).

### WF-05 — Reminder & Post-Event (Cron: harian 08:00 WIB)
Baca tab `orders`: `tgl_acara = hari ini + 3` → WA ke customer ("cek kembali data undangan, hubungi CS jika ada revisi"); `tgl_acara = kemarin` → WA ucapan selamat + minta testimoni + kode referral diskon untuk kerabat.

### WF-06 — Backup Mingguan (Cron: Minggu 02:00 WIB)
**SSH node** ke Hostinger: `wp db export - | gzip > backup-$(date +%F).sql.gz` + `tar -czf uploads.tar.gz wp-content/uploads` → unduh/simpan di VPS, retensi 4 minggu.

---

## 10. Produk & Paket Harga (WooCommerce: 1 produk per paket, tipe *simple*)

| Fitur | **Hemat** Rp 99rb | **Favorit** Rp 179rb | **Premium** Rp 299rb |
|---|---|---|---|
| Cover + countdown + detail acara + Maps + musik | ✔ | ✔ | ✔ |
| RSVP + ucapan tamu + nama tamu di link | ✔ | ✔ | ✔ |
| Pilihan tema | 3 tema dasar | Semua tema | Semua + tema premium |
| Galeri foto (max 10) + love story | — | ✔ | ✔ |
| Amplop digital (rekening + QRIS) | — | ✔ | ✔ |
| Video / live streaming embed | — | — | ✔ |
| Revisi manual oleh CS | — | 1× | 3× + prioritas |
| Masa aktif | H+7 | H+30 | 1 tahun |

Add-on (fase 2): **WA blast ke daftar tamu** Rp 50rb/200 tamu (n8n loop, delay acak 20–45 detik antar pesan, hanya ke daftar yang diberikan customer) · buku tamu QR check-in Rp 100rb.

Semua undangan tetap jadi **instan otomatis** — pembeda paket adalah fitur, masa aktif, dan layanan revisi, bukan kecepatan.

---

## 11. Keamanan, Performa, Batasan Hosting

- **Idempotency wajib** di WF-01 & WF-02 (webhook bisa terkirim >1×; user bisa double-submit). Kunci: cek status di sheet sebelum eksekusi.
- **HMAC** di webhook WooCommerce; **token HMAC** di form; path webhook n8n acak; semua via HTTPS (SSL gratis Hostinger).
- **RSVP**: honeypot + rate limit transient (sudah di kode §6). Jangan tampilkan email/no. HP tamu di publik.
- **Cache**: LiteSpeed full-page untuk `/u/*`; drop query `to` (§4). Dengan ini 25 PHP worker sanggup ribuan pengunjung/hari karena mayoritas hit dilayani cache, bukan PHP.
- **Disk/inodes** (10 GB / 200rb inodes): pembatasan upload (10 foto × ≤2 MB), filter ukuran gambar (§6 poin 4), backup disimpan di VPS bukan di hosting. Estimasi ±6 MB/undangan → aman untuk ±1.000 undangan aktif; arsipkan undangan kedaluwarsa (WF tambahan v2: set ke draft + hapus media H+90 untuk paket Hemat/Favorit).
- **WA**: nomor khusus, warm-up 1–2 minggu (kirim manual dulu), delay antar pesan otomatis, jangan blast ke nomor yang tak dikenal → meminimalkan risiko banned. Fallback: Fonnte (berbayar murah) tinggal ganti 1 node.

---

## 12. Rencana Sprint (1 programmer full-stack WP, 4 minggu)

**Minggu 1 — Fondasi.** Setup §4, hardening, mu-plugin §6, page checkout WooCommerce + payment sandbox, tema undangan #1 selesai (desain + `single-undangan.php`), halaman katalog. *DoD: checkout sandbox berhasil; halaman `/u/demo` tampil sempurna di HP.*

**Minggu 2 — Mesin otomasi.** WAHA jalan, Brevo terverifikasi, form isi data §8, WF-01 & WF-02 lengkap. *DoD: 1 order sandbox mengalir end-to-end → undangan terbit + email & WA terkirim tanpa sentuhan manusia.*

**Minggu 3 — Fitur & reseller.** RSVP live di halaman undangan, amplop digital, tema #2 & #3, landing reseller, WF-03 & WF-04. *DoD: order memakai kupon `RES-*` otomatis tercatat komisinya dan rekap WA terkirim.*

**Minggu 4 — Hardening & launch.** WF-05 & WF-06, uji idempotency (kirim webhook 2×, submit form 2×), uji cache & beban, checklist §13 hijau semua, payment mode production, rekrut 3 reseller pertama, soft launch. *DoD final: order riil Rp 10.000 (produk uji tersembunyi) dari HP → undangan diterima di WA < 15 menit.*

---

## 13. Checklist QA End-to-End

- [ ] Checkout berhasil via QRIS, VA, dan e-wallet (sandbox lalu production nominal kecil)
- [ ] Webhook order terkirim ke n8n; signature invalid → ditolak
- [ ] Webhook yang sama dikirim 2× → hanya 1 baris di sheet, 1 email, 1 WA
- [ ] Link form dengan token salah → 403
- [ ] Submit form 2× → tidak membuat undangan ganda, membalas link yang sama
- [ ] Upload 10 foto 2 MB → semua masuk galeri; file ke-11 / >2 MB ditolak di client
- [ ] Undangan tampil benar di Chrome & Safari mobile; musik play setelah tap
- [ ] `?to=Budi%20Santoso` menampilkan nama; halaman tetap kena cache (cek header `x-litespeed-cache: hit`)
- [ ] RSVP masuk ke wp-admin & tampil di halaman; spam honeypot tidak tersimpan; submit beruntun kena 429
- [ ] Tombol salin rekening & gambar QRIS berfungsi
- [ ] Fitur sesuai paket: Hemat tidak menampilkan galeri/amplop; Premium menampilkan video
- [ ] Order dengan kupon `RES-` → komisi tercatat benar (30% dari total setelah diskon)
- [ ] Rekap Senin & reminder H-3 / H+1 terkirim (uji dengan tanggal dimundurkan)
- [ ] Backup mingguan menghasilkan file valid di VPS (uji restore 1×)
- [ ] Email masuk inbox (bukan spam) — SPF/DKIM Brevo hijau

---

## 14. Biaya Operasional Tambahan

| Item | Biaya |
|---|---|
| Hosting Hostinger + VPS n8n | Sudah dimiliki |
| WAHA, Brevo (≤300 email/hari), qrserver, Google Sheets | Rp 0 |
| Payment gateway | Fee per transaksi saja (QRIS ±0,7% + fee kanal) |
| Nomor WA khusus (kartu prabayar) | ± Rp 25rb/bulan |
| **Total biaya tetap tambahan** | **≈ Rp 25rb/bulan** |

Break-even praktis: **1 order pertama tiap bulan sudah menutup seluruh biaya variabel.**

---

## 15. Non-Goals v1 (jangan dibangun sekarang) & Roadmap v2

**Non-goals v1:** dashboard self-service customer untuk edit undangan (revisi lewat CS/wp-admin dulu) · escrow amplop digital dengan payment gateway (kompleks secara legal — v1 cukup transfer langsung ke rekening mempelai) · custom domain per undangan · multi-bahasa · tema builder drag-and-drop.

**Roadmap v2 (setelah 100 order pertama):** occasion baru dengan duplikasi tema (khitanan, aqiqah, wisuda — musiman Mei–September, e-card Lebaran) · buku tamu QR check-in · amplop digital ter-escrow via QRIS dengan fee platform 2–5% (perubahan model bisnis paling bernilai) · migrasi log dari Sheets ke Postgres di VPS · dashboard reseller.

---

## 16. Pertanyaan Terbuka (butuh jawaban owner sebelum Minggu 1)

1. Nama brand + domain yang diklaim dari jatah gratis Hostinger? *(blocking — dipakai di semua konfigurasi)*
2. Akun payment gateway: daftar sebagai perorangan (Tripay/Duitku) atau badan usaha (Midtrans)? *(blocking untuk Minggu 1)*
3. Sumber desain 3 tema pertama: desainer freelance atau beli template HTML berlisensi untuk diadaptasi? *(blocking untuk Minggu 1)*
4. Konfirmasi harga paket & besaran komisi reseller (default blueprint: 30%; diskon kupon 10%). *(non-blocking, bisa diubah kapan saja)*
5. Nomor WA bisnis khusus sudah tersedia? *(dibutuhkan Minggu 2)*
