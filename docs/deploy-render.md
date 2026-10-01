# Deploy ke Render

Proyek ini menggunakan satu Docker Web Service berisi Nginx dan PHP-FPM, serta satu Render PostgreSQL. Infrastruktur didefinisikan di `render.yaml`.

## 1. Persiapan repository

Push seluruh perubahan ke repository GitHub/GitLab yang akan dihubungkan ke Render. Pastikan file berikut ikut ter-push:

- `Dockerfile`
- `render.yaml`
- `.dockerignore`
- folder `docker/`

Jangan commit file `.env`.

## 2. Buat Blueprint

1. Masuk ke Render Dashboard.
2. Pilih **New > Blueprint**.
3. Hubungkan repository proyek ini.
4. Render akan membaca `render.yaml` dan membuat Web Service serta PostgreSQL di region Singapore.
5. Isi secret yang diminta:

| Key | Nilai |
| --- | --- |
| `APP_KEY` | Output dari `php artisan key:generate --show` |
| `APP_URL` | URL HTTPS service, misalnya `https://capstone-project-ut.onrender.com` |
| `ADMIN_EMAIL` | Email akun admin pertama |
| `ADMIN_PASSWORD` | Password kuat akun admin pertama |

Jika nama service sudah dipakai, Render dapat meminta nama lain. Sesuaikan `APP_URL` dengan URL final yang diberikan Render.

## 3. Proses deployment

Pada setiap startup container, `docker/start.sh` akan:

1. membuat dan memperbaiki permission folder Laravel;
2. membuat cache konfigurasi, route, dan view;
3. menjalankan `php artisan migrate --force`;
4. menjalankan seeder idempotent untuk role, permission, master data, dan akun admin pertama;
5. menjalankan PHP-FPM dan Nginx melalui Supervisor.

Aset Tailwind, Flowbite, dan JavaScript dibangun saat Docker image dibuat, sehingga Node.js tidak ikut masuk ke image runtime.

## 4. Pemeriksaan setelah deploy

- Buka `https://<nama-service>.onrender.com/up`; hasilnya harus `{"status":"ok"}`.
- Buka URL utama dan login menggunakan `ADMIN_EMAIL` dan `ADMIN_PASSWORD`.
- Periksa menu Role, Master Data, dan Pesanan.
- Pastikan `APP_DEBUG=false` tetap digunakan di production.

## Catatan

- Database menggunakan PostgreSQL melalui `DATABASE_URL` internal Render.
- Session menggunakan encrypted cookie agar tidak hilang ketika filesystem container diganti.
- File yang disimpan ke filesystem container bersifat sementara. Jika nanti ada upload dokumen/gambar, gunakan object storage atau persistent disk.
- `ADMIN_EMAIL` dan `ADMIN_PASSWORD` hanya dipakai untuk membuat admin jika tabel pengguna masih kosong. Mengubah nilainya tidak mengganti password admin yang sudah ada.
