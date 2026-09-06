# PhotoGram — Aplikasi Berbagi Foto

Aplikasi web sederhana untuk upload, edit, dan berbagi foto, lengkap dengan
**dashboard insight** untuk melihat statistik like, komentar, dan views.

Dibangun dengan: **PHP (native, PDO)**, **MySQL**, **HTML**, **JavaScript (vanilla)**,
dan **Tailwind CSS** (via CDN).

## Fitur

- Registrasi & login (password di-hash dengan `password_hash`)
- Feed foto (like, komentar real-time via AJAX)
- Upload foto dengan pilihan filter (grayscale, sepia, invert, cerah, kontras)
- Edit foto: crop & rotate (Cropper.js) + ganti filter + edit caption
- Hapus foto
- Halaman profil dengan tampilan grid foto
- **Dashboard Insight**:
  - Total foto, like, komentar, views, dan engagement rate
  - Grafik views 14 hari terakhir (line chart)
  - Perbandingan like vs komentar untuk 5 foto teratas (bar chart)
  - Tabel detail statistik semua foto
  - Tracking views otomatis saat foto tampil di layar (Intersection Observer)

## Instalasi (di XAMPP / Laragon)

1. Ekstrak folder `photogram` ke dalam folder `htdocs` (XAMPP) atau `www` (Laragon).
2. Buka **phpMyAdmin**, lalu impor file `database/schema.sql`
   (ini akan otomatis membuat database `photogram_db` beserta tabelnya,
   dan 1 user demo: username `demo`, password `password123`).
3. Buka `config/db.php`, sesuaikan `DB_USER`, `DB_PASS` jika perlu
   (default XAMPP: user `root`, password kosong).
4. Sesuaikan konstanta `BASE_URL` di `config/db.php` sesuai nama folder project Anda,
   contoh: `define('BASE_URL', '/photogram/');`
5. Pastikan folder `uploads/` memiliki izin tulis (writable):
   ```
   chmod -R 755 uploads/
   ```
6. Pastikan ekstensi PHP **GD** aktif (untuk fitur filter & crop foto).
   Cek di `php.ini`: hapus tanda `;` pada baris `extension=gd`.
7. Akses melalui browser: `http://localhost/photogram/`

## Struktur Folder

```
photogram/
├── ajax/               # Endpoint AJAX (like, comment, track_view)
├── config/db.php       # Koneksi database
├── css/style.css       # CSS tambahan
├── database/schema.sql # Struktur database
├── includes/           # header, footer, functions helper
├── js/main.js          # Interaksi like/komentar/tracking
├── uploads/            # Folder penyimpanan foto (writable)
├── index.php           # Feed utama
├── login.php / register.php / logout.php
├── upload.php          # Upload foto baru
├── edit.php            # Edit foto (crop, rotate, filter, caption)
├── delete.php          # Hapus foto
├── profile.php         # Halaman profil
└── dashboard.php       # Dashboard insight/analytics
```

## Catatan Keamanan & Pengembangan Lanjutan

- Ini adalah versi dasar/MVP — cocok untuk belajar atau prototipe.
- Untuk produksi, sebaiknya tambahkan: CSRF token pada form, rate limiting,
  validasi file upload lebih ketat (cek isi file, bukan hanya MIME),
  dan resize otomatis gambar besar agar hemat storage.
- Desain tampilan terinspirasi dari gaya umum aplikasi berbagi foto,
  bukan menyalin aset atau logo dari aplikasi manapun.
