# Panduan Kontribusi e-ARSIP

Dokumen ini adalah acuan kerja tim e-ARSIP. Tujuannya agar perubahan kode mudah direview, tidak saling menimpa, dan `main` selalu stabil.

## 1. Prinsip Utama

1. `main` adalah branch stabil. **Jangan push langsung ke `main`.**
2. Satu branch = satu tiket/fitur.
3. Semua perubahan masuk melalui Pull Request (PR).
4. PR harus lulus CI dan mendapat minimal satu approval.
5. Perubahan kecil lebih mudah direview daripada PR besar.
6. Jangan commit `.env`, database SQLite, `vendor`, `node_modules`, atau berkas build.
7. Jangan mengedit migrasi yang sudah pernah dijalankan.

## 2. Setup Awal untuk Anggota Baru

Setelah menerima undangan collaborator:

```bash
git clone https://github.com/HansNura/arsip.git
cd arsip

composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed

npm install --include=dev
npm run build

php artisan test
php artisan serve
```

Buka `http://localhost:8000`.

**Windows (PowerShell), ganti perintah `touch`:**

```powershell
New-Item database/database.sqlite -ItemType File -Force
```

Akun demo lokal:

| Peran | Email | Kata sandi |
| --- | --- | --- |
| Admin | `admin@arsip.test` | `password` |
| Staf Arsip | `staf@arsip.test` | `password` |

Jika `npm run build` gagal karena `vite: command not found`, jalankan:

```bash
npm install --include=dev
```

## 3. Alur Kerja Harian

### Mulai dari main terbaru

```bash
git switch main
git pull origin main
```

### Buat branch fitur

```bash
git switch -c feature/arsip-004-crud-angkatan
```

Contoh penamaan:

- `feature/arsip-004-crud-angkatan`
- `feature/arsip-005-data-peserta`
- `fix/login-redirect`
- `chore/update-readme`
- `test/verifikasi-publik`

### Kerjakan perubahan

```bash
php artisan test
composer ci
npm run build
```

### Commit

```bash
git add -A
git commit -m "feat(angkatan): tambah CRUD program dan batch"
```

Gunakan pesan commit yang menjelaskan **apa** dan **mengapa**, bukan sekadar "update".

### Push

```bash
git push -u origin feature/arsip-004-crud-angkatan
```

### Buat Pull Request

Buka tautan yang muncul di terminal, atau:

```bash
gh pr create --fill
```

Isi PR:
- ringkasan perubahan,
- tiket terkait (`Closes #12`),
- cara menguji,
- screenshot jika ada perubahan UI.

### Review

- Minta review dari rekan tim.
- CI wajib hijau: **Lint & Test** dan **Build assets**.
- Selesaikan semua komentar.
- Setelah disetujui, gunakan **Squash and merge**.
- Hapus branch setelah merge.

## 4. Konvensi Commit

Gunakan pola Conventional Commits:

```text
feat: fitur baru
fix: perbaikan bug
refactor: perubahan struktur tanpa mengubah perilaku
test: menambah atau memperbaiki test
docs: dokumentasi
chore: tooling, dependency, konfigurasi
style: format kode
```

Contoh:

```text
feat(peserta): tambah pencarian berdasarkan NIK dan tahun
fix(auth): tolak login akun nonaktif
test(verifikasi): tambah kasus sertifikat dibatalkan
docs(erd): perbarui relasi certificate_reissues
```

## 5. Aturan Migrasi dan Database

- Migrasi yang sudah pernah dijalankan **tidak boleh diedit**.
- Perubahan skema harus berupa migrasi baru.
- Setelah `git pull`, jalankan:
  ```bash
  php artisan migrate
  ```
- Jika mengubah skema, perbarui juga:
  - `docs/erd.md`
  - `docs/erd.dbml`
- Koordinasikan perubahan tabel dengan rekan tim sebelum merge.
- Untuk reset lokal:
  ```bash
  php artisan migrate:fresh --seed
  ```
  Jangan jalankan perintah ini di production.

## 6. Sinkronisasi dan Konflik

Sebelum membuka PR, pastikan branch Anda berisi `main` terbaru:

```bash
git switch main
git pull origin main
git switch feature/nama-fitur
git merge main
```

Jika ada konflik:

1. Buka file yang bertanda konflik.
2. Selesaikan konflik secara manual.
3. Jalankan test.
4. `git add -A`
5. `git commit`
6. `git push`

Jangan menyelesaikan konflik dengan menghapus perubahan rekan tim secara sembarangan.

## 7. Review dan Merge

- Reviewer memeriksa: kebenaran logika, keamanan, dampak migrasi, test, dan UI.
- Penulis PR tidak boleh approve PR sendiri.
- Gunakan **Squash and merge** agar riwayat `main` rapi.
- Setelah merge, hapus branch fitur.
- Jika perlu memperbaiki setelah review, push commit tambahan ke branch yang sama.

## 8. Keamanan dan Data

- Jangan pernah commit `.env` atau kredensial.
- Jangan menampilkan NIK, alamat, email, atau kontak pada halaman publik.
- Gunakan data dummy untuk test dan screenshot.
- Laporkan potensi celah keamanan secara privat, bukan di issue publik.
- Password harus selalu di-hash; jangan menyimpan password mentah.

## 9. CI dan Kualitas Kode

Pipeline GitHub Actions menjalankan:

- `Lint & Test`: Laravel Pint dan PHPUnit.
- `Build assets`: `npm ci` dan `npm run build`.

Sebelum push, jalankan lokal:

```bash
composer ci
npm run build
```

Perintah lain:

```bash
composer lint       # memperbaiki format otomatis
composer test       # menjalankan test
```

## 10. Troubleshooting

| Masalah | Solusi |
| --- | --- |
| `Vite manifest not found` | `npm install --include=dev && npm run build` |
| `vite: command not found` | `NODE_ENV=production` membuat devDependencies terlewat; gunakan `--include=dev` |
| `no such table` | `php artisan migrate` |
| `419 Page Expired` | Refresh halaman atau ulangi submit formulir |
| `403 Forbidden` | Cek peran akun dan middleware `role` |
| `Your branch is behind` | `git pull origin main` lalu merge ke branch fitur |
| Konflik merge | Selesaikan manual, test, lalu commit |
| `Permission denied (publickey)` | Pastikan sudah menerima undangan dan SSH key terdaftar |
| Test gagal | Jalankan `composer ci`, baca pesan error, perbaiki sebelum push |

## 11. Peran Tim

| Anggota | Fokus utama |
| --- | --- |
| Helgi Nur Allamsyah | Core, autentikasi, sertifikasi, deployment |
| Salman Alfaridzi | Data peserta, pencarian, pelaporan, retensi, verifikasi publik |

Semua anggota tetap boleh mengerjakan tiket lain selama dikoordinasikan.

## 12. Referensi Internal

- `README.md` — cara menjalankan aplikasi.
- `docs/erd.md` — ERD dan aturan data.
- `docs/laporan-adpl.md` — laporan analisa dan desain.
- `Rencana Kerja Tim Projek.xlsx` — backlog dan roadmap.
- `docs/tutor-salman.md` — tutorial langkah demi langkah untuk anggota baru.
