# Panduan Pindah ke Mac Baru

Repo ini **tidak punya remote di GitHub** — satu-satunya salinan ada di Mac lama. Jadi perpindahan dilakukan dengan menyalin folder secara langsung. Panduan ini mencakup semua yang perlu dibawa, termasuk hal-hal di luar folder project.

## 1. Yang harus disalin dari Mac lama

| Apa | Lokasi di Mac lama | Kenapa |
|---|---|---|
| Folder project | `~/Projects/harih` | Kode, riwayat git, dan `vps/.env` (rahasia deployment, tidak ada backup di git) |
| Memory Claude Code | `~/.claude/projects/-Users-ryanezki-Projects-harih/` | Catatan lintas-sesi: peta infra, checkpoint, preferensi |
| Kunci SSH | `~/.ssh/id_ed25519` dan `id_ed25519.pub` | Akses ke Hostinger dan VPS |
| Konfigurasi git | `~/.gitconfig` | Nama & email untuk commit |

> ⚠️ `vps/.env` dan `id_ed25519` berisi rahasia. Pindahkan lewat jalur aman: **AirDrop, kabel langsung (Migration Assistant), atau disk terenkripsi**. Jangan lewat email atau cloud storage tanpa enkripsi.

Cara praktis mengemas semuanya jadi satu arsip (jalankan di Mac lama):

```bash
cd ~
tar czf pindah-harih.tar.gz \
  Projects/harih \
  .claude/projects/-Users-ryanezki-Projects-harih \
  .ssh/id_ed25519 .ssh/id_ed25519.pub \
  .gitconfig
```

Lalu kirim `pindah-harih.tar.gz` ke Mac baru via AirDrop.

## 2. Persiapan di Mac baru

1. **Pastikan username Mac baru = `ryanezki`.** Path project harus persis `/Users/ryanezki/Projects/harih` supaya memory Claude Code otomatis terbaca (nama folder memory diturunkan dari path). Kalau username terpaksa beda, lihat catatan di bagian 5.
2. **Install Command Line Tools** (berisi git):
   ```bash
   xcode-select --install
   ```
3. **Install Claude Code** — ikuti https://claude.com/claude-code, lalu login:
   ```bash
   claude
   ```

## 3. Ekstrak & pasang

Jalankan di Mac baru (arsip ada di `~/Downloads`):

```bash
cd ~
tar xzf ~/Downloads/pindah-harih.tar.gz
```

Perbaiki izin kunci SSH (SSH menolak kunci yang izinnya terlalu terbuka):

```bash
chmod 700 ~/.ssh
chmod 600 ~/.ssh/id_ed25519
chmod 644 ~/.ssh/id_ed25519.pub
```

Setelah selesai dan terverifikasi, hapus arsipnya (berisi rahasia):

```bash
rm ~/Downloads/pindah-harih.tar.gz ~/pindah-harih.tar.gz 2>/dev/null
```

## 4. Verifikasi

Jalankan satu per satu, semuanya harus lolos:

```bash
# Git & riwayat utuh
cd ~/Projects/harih && git log --oneline -3

# Rahasia deployment ikut terbawa
ls -la vps/.env

# SSH ke Hostinger (WordPress)
ssh -p 65002 u803921702@147.93.80.20 'echo OK-hostinger'

# SSH ke VPS (n8n + WAHA)
ssh root@31.97.50.197 'echo OK-vps'

# Identitas git
git config user.name && git config user.email
```

Terakhir, buka Claude Code di folder project — sesi baru harus otomatis memuat memory (peta infra hariH):

```bash
cd ~/Projects/harih && claude
```

Saat pertama kali SSH ke tiap server akan muncul pertanyaan fingerprint host baru — jawab `yes` (wajar, karena `known_hosts` tidak ikut dibawa).

## 5. Kalau username Mac baru berbeda

Memory Claude Code dikaitkan ke path project. Kalau path baru misalnya `/Users/budi/Projects/harih`, rename folder memory-nya menyesuaikan (garis miring diganti tanda hubung):

```bash
mv ~/.claude/projects/-Users-ryanezki-Projects-harih \
   ~/.claude/projects/-Users-budi-Projects-harih
```

## 6. Saran: backup ke GitHub private

Selama repo hanya ada di satu Mac, kerusakan disk = kehilangan semuanya. Buat repo **private** di GitHub sebagai backup — `vps/.env` tetap aman karena sudah masuk `.gitignore`:

```bash
gh repo create harih --private --source ~/Projects/harih --push
```

(Butuh `gh` CLI: `brew install gh` lalu `gh auth login`.)
