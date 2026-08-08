# Prompt gambar produk — hariH

**Dibuat 2026-08-08 (U22).** Untuk dipakai di GPT Image 2 (akun `ryan.ezki@gmail.com`).

**Cara memasang hasilnya:** simpan ke `wp-content/themes/harih/aset/produk/` dengan **nama berkas persis** seperti di tabel, lalu deploy. Slotnya mengisi dirinya sendiri — tidak perlu menyentuh kode. Format: `.webp` (paling ringan) atau `.jpg`. Ukuran ideal **1600×1200** untuk kartu paket, **1600×1067** untuk dua gambar hero.

> ⚠️ **Semua hasil AI otomatis diberi label "ilustrasi" di halaman.** Itu disengaja dan jangan dicabut: menampilkan render sebagai foto produk di halaman seharga Rp 1,19–5,9 juta adalah klaim palsu, dan itu persis kelas cacat yang baru kita bersihkan (badge "Paling Populer", klaim "hemat Rp 700.000"). Begitu foto sungguhan ada, ganti berkasnya dan hapus argumen `true` terakhir di `harih_foto_produk()`.

---

## Yang dibutuhkan

| Nama berkas | Dipakai di | Rasio |
|---|---|---|
| `amplop-nama` | hero `/harga/` — gambar paling menentukan | 3:2 |
| `undangan-terbuka` | hero `/harga/` | 3:2 |
| `paket-hormat` | kartu paket Hormat | 4:3 |
| `paket-resepsi` | kartu paket Resepsi | 4:3 |
| `paket-grand` | kartu paket Grand | 4:3 |

---

## Konteks yang berlaku untuk SEMUA prompt

Tempel blok ini di depan tiap prompt supaya gayanya konsisten:

> Photorealistic product photography for an Indonesian wedding stationery brand.
> Natural soft daylight from a window, gentle shadows, shallow depth of field.
> Palette: warm cream paper (#faf7f0), deep sage green (#3f5c4f), muted antique
> gold (#b4923f). Calm, elegant, understated — printed-card craftsmanship, not
> glossy commercial advertising. Neutral linen or light oak surface. No text that
> must be readable, no logos, no watermark, no people's faces. Square-on or gentle
> 30-degree angle. Editorial catalogue quality.

---

## 1. `amplop-nama` — gambar paling menentukan

Seluruh salinan penjualan menuntut pembeli **membayangkan** ini: *"nama tiap tamu dicetak langsung pada amplopnya, bukan stiker yang ditempel."* Selama gambarnya tidak ada, itu tetap jadi bayangan.

```
[konteks di atas]

Extreme close-up of a cream-colored A5 wedding envelope lying flat on soft
linen. A guest's name is PRINTED DIRECTLY onto the envelope paper in elegant
dark serif lettering — the ink sits in the paper fibre, clearly not a stick-on
label: no label edge, no white sticker border, no raised sticker shadow.
Shallow depth of field so the name is the sharpest point in frame. A second
and third envelope are softly blurred behind it, suggesting a stack prepared
for many guests. Warm daylight from the left.
```

**Kunci mutu:** kalau hasilnya masih terlihat seperti stiker yang ditempel, ulangi dengan menambahkan *"the name is letterpress-printed into the paper, absolutely no label or sticker"*. Pembeda produk kita justru ada di situ.

## 2. `undangan-terbuka` — QR di halaman dalam

```
[konteks di atas]

An A4 wedding invitation folded to A5, lying open on a linen surface to reveal
the inner spread. On the inner page there is a crisp black QR code, roughly
3 cm square, printed on cream paper with generous white space around it.
Above the QR, elegant serif typography suggests event details (text may be
softly out of focus and unreadable). Thick, substantial paper stock with a
visible fold crease. A matching printed envelope rests beside it, slightly out
of frame. Warm natural daylight, gentle shadow along the fold.
```

## 3. `paket-hormat` — 50 set

```
[konteks di atas]

A neat flat-lay of a small Indonesian wedding invitation set on a linen
surface: a modest stack of about a dozen cream folded invitation cards, and
beside them a stack of matching printed envelopes. Everything shares one
design language — same cream paper, same sage-and-gold accents. Composition
is calm and restrained, plenty of negative space, suggesting a small
carefully-chosen batch rather than bulk. Shot from directly above.
```

## 4. `paket-resepsi` — 100 set + souvenir

```
[konteks di atas]

An overhead flat-lay of a complete Indonesian wedding stationery set arranged
in tidy groups on linen: a stack of cream folded invitation cards, a stack of
matching printed envelopes, a row of small round souvenir labels, a few
thank-you cards, and a scattering of small round wax-look seal stickers in
antique gold. All items share one cream-sage-gold design language, clearly one
coordinated set. Balanced composition, generous negative space between groups.
Shot from directly above, soft daylight.
```

## 5. `paket-grand` — 150 set premium

```
[konteks di atas]

An overhead flat-lay of a premium Indonesian wedding stationery set on linen,
noticeably richer than a basic set: a stack of thick cream folded invitation
cards with subtle gold foil detailing, matching printed envelopes, souvenir
labels, hangtags with fine cotton string, a small set of gift-box labels, and
a couple of PVC committee ID cards on lanyards. One coherent cream-sage-gold
design language across every item. Luxurious but restrained — no glitter, no
heavy ornament. Shot from directly above, soft daylight, deep even shadows.
```

---

## Yang mengalahkan semua ini — dan hanya Anda yang punya

Render AI menutup lubang "halaman kosong". Ia **tidak** menutup keberatan yang sebenarnya di pasar Indonesia: *"ini penipu atau bukan?"*

Yang menjawab itu adalah bukti bahwa produksinya nyata — dan Anda punya percetakan sungguhan. Tiga foto ponsel biasa mengalahkan lima render sempurna:

1. **Bengkel cetaknya** — mesin, tumpukan kertas, ruangan apa adanya. Tidak perlu rapi.
2. **Mesin sedang mencetak atau creasing** — kertas di tengah proses.
3. **Tangan sedang melipat atau memasukkan undangan ke amplop.** Tangan manusia di dalam frame adalah sinyal kepercayaan terkuat yang bisa dipasang halaman mana pun.

Simpan dengan nama `bengkel-1`, `bengkel-2`, `bengkel-3` di folder yang sama; slotnya tinggal saya tambahkan.

Sampel `TEST-173` juga sudah tercetak sungguhan — memotretnya menghasilkan foto produk asli, dan seluruh label "ilustrasi" bisa dicabut.
