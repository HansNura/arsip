# Tutor Onboarding Salman

Panduan ini dibuat untuk Salman Alfaridzi agar bisa mulai berkontribusi ke e-ARSIP tanpa harus menghafal semua perintah Git. Ikuti sesi secara berurutan.

> Aturan utama: **jangan push langsung ke `main`**. Semua pekerjaan lewat branch dan Pull Request.

## 0. Konsep Git dalam 60 Detik

| Istilah | Arti sederhana |
| --- | --- |
| Repository | Folder proyek yang riwayat perubahannya dicatat Git. |
| `main` | Branch utama yang harus selalu stabil. |
| Branch | Salinan kerja untuk satu fitur, agar tidak mengganggu `main`. |
| Commit | Catatan satu kelompok perubahan. |
| Push | Mengirim commit dari komputer ke GitHub. |
| Pull | Mengambil perubahan terbaru dari GitHub. |
| Pull Request (PR) | Permintaan menggabungkan branch fitur ke `main`, sekaligus tempat review. |
| Merge | Menggabungkan branch fitur ke `main`. |
| Conflict | Dua perubahan menyentuh baris yang sama dan harus diselesaikan manual. |

Alur singkat:

```text
main -> buat branch fitur -> kerja -> commit -> push -> PR -> review -> merge -> hapus branch
```

## Sesi 1: Persiapan dan Menjalankan Aplikasi

### 1. Terima undangan

Buka email atau notifikasi GitHub, lalu terima undangan collaborator untuk:

```text
https://github.com/HansNura/arsip
```

### 2. Pastikan tools terpasang

```bash
git --version
php -v
composer --version
node -v
npm -v
```

Versi minimum: PHP 8.2, Composer 2, Node.js 22.

### 3. Atur identitas Git

```bash
git config --global user.name "Salman Alfaridzi"
git config --global user.email "email-github-salman@example.com"
```

Gunakan email yang sama dengan akun GitHub agar commit dikenali.

### 4. Clone dan setup

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
```

Windows PowerShell:

```powershell
New-Item database/database.sqlite -ItemType File -Force
```

### 5. Jalankan aplikasi

```bash
php artisan serve
```

Buka `http://localhost:8000`. Login dengan:

- Admin: `admin@arsip.test` / `password`
- Staf: `staf@arsip.test` / `password`

### 6. Pastikan semuanya sehat

```bash
composer ci
```

Jika semua test lulus, environment Salman sudah siap.

## Sesi 2: Branch Pertama dan Pull Request

### 1. Ambil main terbaru

```bash
git switch main
git pull origin main
```

### 2. Buat branch

Contoh untuk tiket ARSIP-004:

```bash
git switch -c feature/arsip-004-crud-angkatan
```

### 3. Kerjakan perubahan kecil

Contoh latihan pertama yang aman:

> Tambahkan test yang memastikan password kurang dari 8 karakter ditolak saat admin membuat pengguna baru.

Test dapat ditambahkan di `tests/Feature/AdminUserManagementTest.php`.

Jalankan:

```bash
php artisan test
composer ci
```

### 4. Commit

```bash
git add -A
git commit -m "test(pengguna): tolak password kurang dari 8 karakter"
```

### 5. Push branch

```bash
git push -u origin feature/arsip-004-crud-angkatan
```

### 6. Buka Pull Request

Buka tautan yang muncul, atau:

```bash
gh pr create --fill
```

Isi PR:
- ringkasan perubahan,
- tiket terkait,
- cara menguji,
- screenshot jika ada perubahan tampilan.

Tunggu CI hijau dan minta review dari Helgi.

## Sesi 3: Review, Merge, dan Sinkronisasi

### Jika diminta memperbaiki

Lakukan perubahan pada branch yang sama, lalu:

```bash
git add -A
git commit -m "fix: perbaiki validasi sesuai review"
git push
```

PR akan otomatis diperbarui. Tidak perlu membuat PR baru.

### Setelah disetujui

- Gunakan **Squash and merge**.
- Hapus branch fitur di GitHub.
- Kembali ke `main` lokal:

```bash
git switch main
git pull origin main
git branch -d feature/arsip-004-crud-angkatan
```

### Sebelum mulai tiket berikutnya

Selalu ulangi dari `main` terbaru. Jangan menumpuk beberapa fitur dalam satu branch.

## Rutinitas Harian

```bash
# pagi: ambil perubahan terbaru
git switch main
git pull origin main

# mulai kerja
git switch -c feature/arsip-xxx-nama-fitur

# sebelum commit
composer ci
npm run build

# commit dan push
git add -A
git commit -m "feat(modul): deskripsi singkat"
git push -u origin feature/arsip-xxx-nama-fitur
```

## Cheat Sheet Git

| Kebutuhan | Perintah |
| --- | --- |
| Lihat status | `git status` |
| Lihat branch | `git branch` |
| Pindah branch | `git switch nama-branch` |
| Buat branch | `git switch -c nama-branch` |
| Ambil perubahan | `git pull origin main` |
| Lihat riwayat | `git log --oneline --graph --decorate` |
| Batalkan perubahan file | `git restore nama-file` |
| Simpan perubahan sementara | `git stash` |
| Ambil kembali stash | `git stash pop` |
| Hapus branch lokal | `git branch -d nama-branch` |
| Lihat remote | `git remote -v` |

## Kesalahan Umum dan Solusinya

| Situasi | Solusi |
| --- | --- |
| Lupa membuat branch, sudah mengubah `main` | Buat branch sekarang: `git switch -c feature/nama`, lalu commit di branch itu. |
| Ingin membuang semua perubahan yang belum di-commit | `git restore .` (hati-hati, perubahan hilang). |
| Konflik saat merge | Buka file, selesaikan bagian `<<<<<<<`, `=======`, `>>>>>>>`, lalu `git add` dan commit. |
| `Your branch is behind` | `git pull origin main`, lalu merge ke branch fitur. |
| `Vite manifest not found` | `npm install --include=dev && npm run build`. |
| `no such table` | `php artisan migrate`. |
| `403 Forbidden` | Cek peran akun; halaman admin hanya untuk Admin. |
| `419 Page Expired` | Refresh halaman, lalu submit ulang. |
| Tidak sengaja commit `.env` | Jangan push; hapus dari staging dan minta bantuan segera. |

## Aturan Penting

1. Satu branch = satu tiket.
2. Jangan mengedit migrasi yang sudah pernah dijalankan.
3. Jangan commit `.env`, database, `vendor`, `node_modules`, atau berkas build.
4. Jangan menampilkan NIK, alamat, email, atau kontak di halaman publik.
5. Selalu jalankan `composer ci` sebelum push.
6. PR kecil lebih cepat direview.
7. Jika ragu, bertanya lebih baik daripada menebak.

## Checklist Salman

- [ ] Undangan collaborator sudah diterima.
- [ ] Repository berhasil di-clone.
- [ ] Aplikasi berjalan di `http://localhost:8000`.
- [ ] Login admin dan staf berhasil.
- [ ] `composer ci` lulus.
- [ ] Sudah membuat satu branch latihan.
- [ ] Sudah membuat satu Pull Request.
- [ ] Sudah melakukan satu review PR milik Helgi.

## Referensi

- `CONTRIBUTING.md` — aturan kontribusi lengkap.
- `README.md` — cara menjalankan aplikasi.
- `docs/erd.md` — struktur database.
- `Rencana Kerja Tim Projek.xlsx` — daftar tiket dan sprint.
