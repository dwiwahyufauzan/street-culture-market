# Implementasi Plan 11 — Design System & Frontend Layout Refinement

**Tanggal**: 18 September 2026  
**Status**: ✅ Selesai

---

## Ringkasan

Plan 11 memfokuskan pada **Design System refinement** terinspirasi Canalize.asia — minimalis streetwear dengan bold typography, sharp contrast, dan editorial layout. Sebagian besar fondasi sudah ada dari plan-plan sebelumnya; plan ini memperkuat design tokens, menambah utility classes baru, dan membuat komponen visual baru.

---

## Perubahan yang Dilakukan

### 1. `resources/css/app.css` — Design Tokens Diperluas

Ditambahkan:
- **Font import** Italian variant Inter untuk variasi italic editorial
- **`::selection`** styling — hitam/putih konsisten dengan brand
- **`:focus-visible`** ring — keyboard accessible
- **Scrollbar** disempurnakan: 4px width, hover state
- **Kelas baru**:
  - `.btn-ghost` — aksi sekunder tanpa border
  - `.input-field-dark` — input di background gelap (footer newsletter)
  - `.badge-dark`, `.badge-light`, `.badge-sale` — badge variants
  - `.label-overline` — teks overline kecil uppercase tracking-wide
  - `.heading-editorial` — heading besar editorial 6xl
  - `.heading-section` — heading section standar 4xl
  - `.link-underline` — animated underline dari kiri ke kanan on hover
  - `.divider-scm` — divider konsisten
  - `.marquee-track` + `@keyframes marquee` — animasi ticker horizontal
  - `.page-hero` — wrapper standard hero halaman
  - `.card-hover-overlay` — overlay hover card standard
  - `.sticky-top-bar` — navbar sticky dengan backdrop blur

### 2. `resources/views/components/section-marquee.blade.php` — [BARU]

Komponen ticker teks horizontal bergerak infinite loop:
- Background hitam penuh
- Teks bergerak dari kanan ke kiri (30s loop)
- Konten: brand name, taglines, lokasi, shipping info
- Animasi berhenti saat di-hover

### 3. `resources/views/components/section-editorial-grid.blade.php` — [BARU]

Section editorial layout asimetris 7-5 kolom:
- **Kiri (7 col)**: Hero product besar dengan overlay typography
- **Kanan (5 col)**: 2 produk stacked
- Semua panel memiliki overlay gradient gelap + info produk
- Fallback placeholder monochromatic jika tidak ada gambar

### 4. `resources/views/home/index.blade.php` — Diperbarui

Urutan section baru:
1. Hero Banner
2. **Marquee Ticker** ← baru
3. New Arrivals
4. Collections
5. **Editorial Grid** ← baru  
6. Featured Products
7. Brand Story

### 5. `app/Http/Controllers/HomeController.php` — Diperbarui

Ditambahkan query `$editorialProducts`:
- 3 produk aktif secara random
- Di-pass ke view sebagai `editorialProducts`

---

## File yang Diubah

| File | Status |
|------|--------|
| `resources/css/app.css` | Modified |
| `resources/views/components/section-marquee.blade.php` | Created |
| `resources/views/components/section-editorial-grid.blade.php` | Created |
| `resources/views/home/index.blade.php` | Modified |
| `app/Http/Controllers/HomeController.php` | Modified |

---

## Build & Verification

```bash
npm run build       # ✅ Berhasil — 117.80 kB CSS, 54.33 kB JS
vendor/bin/pint     # ✅ HomeController.php fixed (fully_qualified_strict_types)
php artisan view:clear  # ✅ View cache cleared
php artisan config:cache  # ✅ Config cached
```

---

## Design System Token Reference

| Token | Value |
|-------|-------|
| `scm-black` | `#0a0a0a` |
| `scm-white` | `#fafafa` |
| `scm-gray-*` | 50–900 monochromatic scale |
| Font | Inter (300–900, italic) |
| Animation | `fade-in`, `slide-up`, `fly-to-cart`, `marquee` |
| Letter-spacing | `widest-2: 0.25em`, `widest-3: 0.35em` |
| Transition | `400: 400ms` |

---

## Komponen Design System

| Kelas | Fungsi |
|-------|--------|
| `.btn-primary` | CTA utama hitam |
| `.btn-outline` | Tombol border hitam, hover fill |
| `.btn-ghost` | Tombol tanpa border |
| `.input-field` | Input light background |
| `.input-field-dark` | Input dark background |
| `.badge-dark/light/sale` | Badge tag |
| `.label-overline` | Overline teks kecil |
| `.heading-editorial` | Editorial hero heading |
| `.heading-section` | Section heading |
| `.link-underline` | Link dengan animated underline |
| `.section-padding` | Padding section standard |
| `.container-scm` | Max-width container 2xl |
| `.page-hero` | Hero halaman dalam |
| `.marquee-track` | Ticker marquee infinit |
| `.card-hover-overlay` | Product card hover overlay |
| `.sticky-top-bar` | Sticky bar dengan blur |
