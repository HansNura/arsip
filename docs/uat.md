# UAT & Checklist Keamanan — e-ARSIP

Dokumen ini dipakai untuk ARSIP-020 (pengujian integrasi dan pengecekan celah keamanan). Jalankan di lingkungan staging/lokal dengan data dummy.

## 1. Persiapan

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate:fresh --seed
npm install --include=dev
npm run build
php artisan serve
```

Akun uji:

| Peran | Email | Kata sandi |
| --- | --- | --- |
| Admin | `admin@arsip.test` | `password` |
| Staf Arsip | `staf@arsip.test` | `password` |

Gunakan data dummy; jangan memakai NIK/alamat/email asli.

## 2. Skenario UAT

Tandai `[x]` bila hasil sesuai.

### A. Autentikasi & RBAC

- [ ] Staf dapat login dan membuka dashboard.
- [ ] Akun nonaktif tidak dapat login.
- [ ] Staf tidak dapat membuka `/admin/users`, `/admin/retention`, dan aksi approve/reject reissue (403).
- [ ] Admin dapat membuka seluruh halaman admin.
- [ ] Logout mengakhiri sesi dan tercatat di `audit_logs`.

### B. Data peserta & pelatihan

- [ ] CRUD program dan angkatan berjalan.
- [ ] Pendaftaran peserta ke angkatan berhasil; NIK duplikat ditolak.
- [ ] Presensi per pertemuan dan nilai akhir tersimpan.
- [ ] Pencarian peserta berdasarkan NIK, nama, tahun, dan kelas berfungsi.

### C. Sertifikasi

- [ ] Sertifikat hanya dapat diterbitkan untuk peserta lulus.
- [ ] Sertifikat aktif ganda untuk satu enrollment ditolak.
- [ ] PDF tersimpan di disk privat dan dapat diunduh setelah login.
- [ ] QR pada PDF mengarah ke `/verify/s/{token}` dan menampilkan status valid.
- [ ] Tanda tangan berupa gambar tampil di PDF (bukan PKI).

### D. Verifikasi publik

- [ ] `/verify?nomor=...` menampilkan status valid/dibatalkan/tidak ditemukan.
- [ ] `/verify/s/{token}` menampilkan status yang sama.
- [ ] `GET /api/verify?nomor=...` mengembalikan JSON `{hasil, sertifikat}`.
- [ ] Halaman dan API tidak menampilkan NIK, alamat, email, atau no. telepon.
- [ ] Submit verifikasi ke-21 dalam satu menit mengembalikan `429`.
- [ ] API ke-21 dalam satu menit mengembalikan `429`.

### E. Penerbitan ulang (reissue)

- [ ] Staf dapat mengajukan reissue untuk sertifikat aktif.
- [ ] Pengajuan untuk sertifikat non-aktif atau permohonan terbuka ganda ditolak.
- [ ] Admin dapat menyetujui/menolak; staf tidak bisa approve (403).
- [ ] Admin tidak dapat menyetujui permohonan yang ia ajukan sendiri.
- [ ] Setelah disetujui, staf dapat memproses penerbitan pengganti.
- [ ] Sertifikat lama berstatus `diganti`, pengganti berstatus `aktif`, dan nomor baru berbeda.
- [ ] PDF pengganti dapat diunduh; `replaced_by_id` dan `new_certificate_id` terisi.
- [ ] Audit `CREATE_REISSUE`, `APPROVE_REISSUE`/`REJECT_REISSUE`, `REISSUE`, `REPLACE_CERTIFICATE` tercatat.

### F. Laporan & ekspor

- [ ] Rekap kelulusan tampil per program/periode dan filter tanggal bekerja.
- [ ] Ekspor XLSX dapat dibuka di spreadsheet.
- [ ] Ekspor PDF dapat dibuka dan terbaca.
- [ ] Setiap ekspor tercatat sebagai `EXPORT` di `audit_logs`.

### G. Retensi

- [ ] Admin dapat membuat/mengubah kebijakan retensi; hanya satu kebijakan aktif per entity.
- [ ] `php artisan retention:archive-alumni` memproses alumni melewati masa retensi.
- [ ] Aksi `arsipkan`, `cold_storage`, dan `hapus_lembut` sesuai kebijakan.
- [ ] Alumni berstatus `hapus_lembut` dapat dipulihkan admin.
- [ ] Setiap eksekusi kebijakan tercatat sebagai `RETENTION_ARCHIVE`.

## 3. Checklist Keamanan

- [ ] Tidak ada `.env`, database, `vendor`, `node_modules`, atau PDF hasil generate di git.
- [ ] Semua password di-hash; tidak ada password mentah di database/log.
- [ ] CSRF aktif pada seluruh form web.
- [ ] Rate limiting login (5/menit), submit verifikasi/API (20/menit), halaman/QR (60/menit).
- [ ] PDF sertifikat hanya dapat diunduh lewat route terautentikasi.
- [ ] Halaman/API publik hanya mengembalikan kolom aman dari `v_public_certificate`.
- [ ] Security headers aktif: `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy`, `Permissions-Policy`, `X-Permitted-Cross-Domain-Policies`.
- [ ] HSTS aktif saat request HTTPS.
- [ ] `audit_logs` append-only (update/delete ditolak trigger).
- [ ] `composer audit --locked --no-dev` bersih.
- [ ] `APP_ENV=production`, `APP_DEBUG=false`, dan `APP_URL` memakai HTTPS.
- [ ] `SESSION_SECURE_COOKIE=true` dan cookie `HttpOnly` + `SameSite=Lax`.
- [ ] Seeder demo tidak dijalankan di production; admin dibuat lewat `php artisan user:create-admin`.
- [ ] Trusted proxies dikonfigurasi bila aplikasi berada di belakang reverse proxy agar HSTS/HTTPS terdeteksi benar.
- [ ] Pemisahan tugas reissue berjalan (pengaju ≠ penyetuju).
- [ ] Tidak ada pesan error/stack trace yang menampilkan data sensitif di production (`APP_DEBUG=false`).

## 4. Catatan Batasan

- Tanda tangan PDF bersifat visual, bukan tanda tangan kriptografis/PKI.
- Cold storage baru berupa penanda `storage_tier` + `archived_at`; pemindahan berkas fisik belum termasuk.
- Deployment production, SSL, dan backup otomatis masuk ARSIP-021 (belum dikerjakan).

## 5. Sign-off

| Peran | Nama | Tanggal | Hasil |
| --- | --- | --- | --- |
| Penguji staf |  |  |  |
| Penguji admin |  |  |  |
| Reviewer keamanan |  |  |  |
