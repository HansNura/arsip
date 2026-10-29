# e-ARSIP

Sistem arsip sertifikat pelatihan: pengelolaan peserta dan angkatan, penerbitan sertifikat bernomor unik, verifikasi publik, retensi data, serta jejak audit.

Fondasi aplikasi sudah siap untuk pengembangan modul berikutnya.

## Stack

- Laravel 12 (PHP 8.2+)
- SQLite (default) dengan dukungan partial index, view, dan trigger
- Blade + Tailwind CSS 4 + Vite 7
- Autentikasi session Laravel dengan RBAC `admin` dan `staf_arsip`
- PHPUnit 11

## Menjalankan di Lokal

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
npm install --include=dev
npm run build
php artisan serve
```

Buka `http://localhost:8000`.

> Catatan: jika `NODE_ENV=production` aktif di shell, npm akan melewati `devDependencies`.
> Gunakan `npm install --include=dev` agar Vite dan Tailwind ikut terpasang.

### Akun Demo

Dibuat oleh `UserSeeder`:

| Peran | Email | Kata sandi |
| --- | --- | --- |
| Admin | `admin@arsip.test` | `password` |
| Staf Arsip | `staf@arsip.test` | `password` |

Di lingkungan `local`, `DemoDataSeeder` juga membuat satu sertifikat contoh:
`PEL/2026/0001` (Budi Santoso).

Ubah kata sandi akun demo sebelum dipakai di lingkungan nyata.

### Perintah Berguna

```bash
php artisan migrate:fresh --seed   # reset database + data demo
php artisan test                   # menjalankan seluruh test
composer ci                        # Pint check + seluruh test
composer lint                      # perbaiki format kode otomatis
php artisan route:list             # daftar route
npm run dev                        # Vite dev server
composer run dev                   # server + queue + log + Vite
```

## Linter & CI

- **Laravel Pint** dipakai sebagai linter/formatter PHP.
- **GitHub Actions** ada di `.github/workflows/ci.yml`:
  - Job `quality`: install Composer, `pint --test`, lalu `php artisan test`.
  - Job `frontend`: `npm ci --include=dev` dan `npm run build`.
- Jalankan `composer ci` sebelum push agar pipeline lokal dan GitHub konsisten.

## Halaman

| URL | Akses | Keterangan |
| --- | --- | --- |
| `/` | Publik | Landing page |
| `/verify` | Publik | Verifikasi nomor sertifikat |
| `/verify/s/{token}` | Publik | Verifikasi lewat QR code (token acak) |
| `/api/verify?nomor=...` | Publik | API JSON verifikasi sertifikat |
| `/login` | Publik | Login staf |
| `/dashboard` | Login | Ringkasan data |
| `/profile` | Login | Informasi akun dan ubah kata sandi |
| `/admin/programs` | Login | CRUD program pelatihan |
| `/admin/batches` | Login | CRUD angkatan dan pengelolaan pendaftaran, absensi, serta nilai |
| `/admin/participants` | Login | Pencarian peserta dengan filter NIK, nama, tahun, dan kelas |
| `/admin/certificates` | Login | Terbitkan, cari, dan unduh sertifikat peserta yang lulus |
| `/admin/users` | Admin | Daftar, tambah, edit, aktif/nonaktif pengguna |

## API Verifikasi Publik

Endpoint JSON untuk integrasi eksternal, tanpa login:

```bash
curl "http://localhost:8000/api/verify?nomor=PEL%2F2026%2F0001"
```

Contoh respons:

```json
{
  "hasil": "valid",
  "sertifikat": {
    "nomor_sertifikat": "PEL/2026/0001",
    "status": "aktif",
    "nama": "Budi Santoso",
    "program": "Pelatihan Kepemimpinan Dasar",
    "kode_batch": "2026-01",
    "tahun": 2026,
    "tanggal_terbit": "2026-02-10"
  }
}
```

`hasil` bernilai `valid`, `dibatalkan`, atau `tidak_ditemukan`. Saat tidak ditemukan, `sertifikat` bernilai `null`. Endpoint dibatasi 20 permintaan per menit per IP dan hanya mengembalikan kolom aman.

## Keamanan yang Sudah Aktif

- Login dibatasi 5 percobaan per menit; submit verifikasi dan API dibatasi 20 permintaan per menit; halaman verifikasi dan QR dibatasi 60 permintaan per menit.
- Akun nonaktif tidak dapat login, dan sesi yang sedang berjalan langsung diputus pada request berikutnya.
- Password di-hash dengan cast `hashed` Laravel; perubahan password memverifikasi password saat ini.
- RBAC melalui middleware `role`; admin tidak dapat menonaktifkan atau menurunkan peran akun sendiri.
- Email dinormalisasi ke lowercase dan unik secara case-insensitive.
- Verifikasi publik hanya membaca view `v_public_certificate` (tanpa NIK, alamat, atau email).
- QR code memakai `qr_token` acak, bukan nomor sertifikat; halaman verifikasi tetap hanya menampilkan kolom aman.
- PDF sertifikat disimpan di disk privat dan hanya dapat diunduh lewat route terautentikasi.
- Tanda tangan pada PDF berupa **gambar** (bukan tanda tangan kriptografis/PKI); keaslian dijaga `file_hash` SHA-256 dan token verifikasi.
- Log verifikasi disimpan di `verification_logs`.
- Audit login/logout disimpan di `audit_logs`; tabel ini append-only lewat trigger SQLite.
- Partial unique index memastikan satu sertifikat `aktif` per enrollment.

## Struktur Penting

```text
app/
  Enums/                 Status domain (role, batch, sertifikat, dll.)
  Http/Controllers/      Auth, dashboard, verifikasi, admin
  Http/Middleware/       EnsureUserHasRole
  Models/                Model Eloquent + relasi
  Services/AuditLogger   Pencatat audit terpusat
database/
  migrations/            Skema sesuai docs/erd.md
  seeders/               UserSeeder, DemoDataSeeder
docs/
  erd.md                 ERD, kamus data, aturan bisnis
  erd.dbml               ERD untuk dbdiagram.io
  laporan-adpl.md        Draft laporan ADPL sesuai format kampus
resources/views/         Blade: layout, home, auth, dashboard, verify, admin
tests/                   Auth, RBAC, verifikasi, constraint database
```

## Catatan SQLite

- Foreign key diaktifkan Laravel secara default untuk SQLite.
- Untuk aplikasi dengan trafik tulis nyata, aktifkan WAL dan busy timeout:

```sql
PRAGMA journal_mode = WAL;
PRAGMA busy_timeout = 5000;
```

- Backup gunakan `sqlite3 database/database.sqlite ".backup 'backup.sqlite'"` atau Litestream; jangan menyalin file saat WAL aktif.
- SQLite hanya mendukung satu penulis. Operasi batch (mis. generate sertifikat) harus singkat dan memakai transaksi.

## Status Pengembangan

Lihat `Rencana Kerja Tim Projek.xlsx` dan `docs/erd.md`.

- [x] ARSIP-001: scaffold Laravel, konfigurasi environment, health check, asset pipeline, CI/linter
- [x] ARSIP-002: skema database + migrasi + model + constraint
- [x] ARSIP-003: login/logout, RBAC admin/staf, manajemen pengguna, perubahan password, audit auth
- [x] ARSIP-011: endpoint API publik verifikasi sertifikat (JSON)
- [x] ARSIP-012: halaman publik verifikasi nomor dan QR
- [x] ARSIP-013: rate limiting verifikasi publik
- [x] CRUD program dan angkatan, pendaftaran peserta, absensi per pertemuan, nilai akhir, dan pencarian multi-filter
- [x] ARSIP-008: generator nomor sertifikat unik per program dan tahun
- [ ] CRUD peserta dan sertifikat
- [x] ARSIP-009: penerbitan sertifikat, penyimpanan PDF privat, dan unduhan terautentikasi
- [x] ARSIP-010: QR verifikasi berbasis token dan gambar tanda tangan sertifikat
- [ ] Pelaporan, retensi otomatis, reissue, dan deployment production
```
