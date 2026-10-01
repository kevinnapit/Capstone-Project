# Steering Pengembangan Sistem Manajemen Usaha Fotokopi

Dokumen ini menjadi acuan bersama selama analisis, desain, implementasi, pengujian, dan penyusunan laporan Capstone Project. Setiap perubahan kebutuhan yang disepakati tim perlu diperbarui di dokumen ini agar implementasi tetap konsisten.

## 1. Ringkasan Proyek

Sistem dikembangkan untuk membantu usaha fotokopi mengelola pesanan, penjualan ATK, layanan fotokopi dan cetak, stok, pembayaran, serta laporan harian. Sistem menggantikan pencatatan kertas yang saat ini menyebabkan stok tidak terlacak, pesanan WhatsApp berpotensi terlewat, perhitungan harga dilakukan manual, dan laporan harian belum rapi.

### Tujuan utama

- Mencatat pesanan yang datang langsung maupun melalui WhatsApp.
- Menghitung harga layanan dan barang secara konsisten.
- Mengurangi stok berdasarkan barang atau bahan yang benar-benar terpakai.
- Menyimpan riwayat pembatalan dan revisi pesanan.
- Menyediakan laporan item, harga jual, stok, dan total penjualan harian.
- Menyediakan audit log untuk aksi penting pengguna.

## 2. Teknologi dan Pendekatan

- Backend dan antarmuka: Laravel 10 dengan Blade.
- Bahasa: PHP 8.1 atau versi kompatibel dengan proyek.
- Database: MySQL.
- Autentikasi: session-based authentication Laravel.
- CSS/JavaScript: Vite; gunakan pendekatan sederhana agar dapat dikerjakan oleh tim tanpa frontend khusus.
- Arsitektur aplikasi: pisahkan modul autentikasi, master data, transaksi, laporan, dan audit.

Untuk versi awal, prioritaskan aplikasi web responsif berbasis Blade. Jangan menambahkan SPA atau framework frontend lain tanpa kebutuhan yang jelas dan persetujuan tim.

## 3. Pengguna dan Hak Akses

### Admin/Pemilik

- Mengelola pengguna dan pegawai.
- Mengelola master barang, layanan, harga, dan stok.
- Melihat seluruh transaksi, laporan, dan audit log.
- Melakukan penyesuaian stok dengan alasan yang wajib dicatat.

### Pegawai

- Membuat dan memperbarui pesanan.
- Memilih barang ATK atau spesifikasi layanan.
- Mengonfirmasi harga dan mencatat pembayaran.
- Membatalkan atau merevisi pesanan dengan alasan.
- Melihat data operasional yang diperlukan.

Gunakan otorisasi Laravel melalui middleware, policy, atau gate. Validasi hak akses tidak boleh hanya bergantung pada tampilan tombol di halaman.

## 4. Modul Sistem

### 4.1 Authentication

- Login dan logout.
- Registrasi pengguna hanya jika diizinkan oleh admin.
- Lupa dan reset kata sandi.
- Manajemen profil pengguna.
- Status pengguna aktif/nonaktif.

### 4.2 Master Data

- Pengguna dan pegawai.
- Barang ATK.
- Kategori barang.
- Satuan barang.
- Layanan fotokopi dan cetak.
- Daftar harga layanan.
- Stok awal dan penyesuaian stok.
- Metode pembayaran.

### 4.3 Transaction

- Pesanan langsung dan WhatsApp.
- Detail pembelian ATK.
- Detail layanan fotokopi/cetak.
- Perhitungan subtotal, diskon jika kelak diperlukan, dan total.
- Konfirmasi harga.
- Pembayaran.
- Revisi dan pembatalan pesanan.
- Pencatatan pemakaian atau kerugian stok.
- Riwayat perubahan status transaksi.

### 4.4 Report

- Daftar item dan harga jual.
- Posisi stok saat ini.
- Riwayat pergerakan stok.
- Penjualan per hari dan rentang tanggal.
- Total penjualan harian.
- Rincian transaksi dan metode pembayaran.
- Pesanan batal/revisi serta nilai kerugian bahan.

## 5. Alur Bisnis Utama

1. Pesanan datang langsung atau melalui WhatsApp.
2. Pegawai mencatat sumber pesanan dan informasi pelanggan yang tersedia.
3. Pegawai memilih jenis transaksi:
   - pembelian ATK; atau
   - jasa fotokopi/cetak.
4. Untuk ATK, pegawai memilih item dan jumlah.
5. Untuk layanan, pegawai mencatat jenis layanan, jenis kertas, mode warna, jumlah halaman/lembar, sisi cetak, dan kuantitas yang relevan.
6. Sistem mengambil harga aktif dan menghitung total secara otomatis.
7. Pegawai mengonfirmasi harga dan pesanan kepada pelanggan.
8. Jika dilanjutkan, pesanan diproses, pembayaran dicatat, lalu pesanan diselesaikan.
9. Jika dibatalkan atau direvisi, status dan alasan wajib dicatat.
10. Jika bahan sudah terpakai pada pesanan batal/revisi, pemakaian tersebut tetap mengurangi stok dan dicatat sebagai pemakaian/kerugian.
11. Semua jalur menyimpan riwayat transaksi dan pergerakan stok.
12. Data masuk ke laporan item, harga, stok, dan penjualan harian.

## 6. Aturan Bisnis

### Pesanan dan status

Status minimal yang disarankan:

- `draft`: pesanan masih disusun.
- `confirmed`: harga dan pesanan sudah dikonfirmasi.
- `processing`: sedang dikerjakan.
- `completed`: selesai dan diserahkan.
- `cancelled`: dibatalkan.
- `revised`: mengalami revisi; detail perubahan harus memiliki riwayat.

Perubahan status harus tervalidasi. Contohnya, pesanan `completed` tidak boleh kembali menjadi `draft` tanpa prosedur koreksi dan audit log.

Keputusan MVP yang sudah diterapkan:

- Satu pesanan dapat memuat campuran produk ATK dan layanan.
- Nomor pesanan memakai format `ORD-YYYYMMDD-XXXX` dengan urutan harian.
- Kuantitas tagihan layanan satu sisi/cetak dihitung dari `pages × copies`.
- Kuantitas tagihan layanan timbal balik dihitung dari `ceil(pages ÷ 2) × copies`.
- Harga dan nama produk/layanan disimpan sebagai snapshot ketika draft dibuat.
- Pembayaran parsial didukung, tetapi pesanan hanya dapat diselesaikan setelah lunas.
- Stok produk dan kertas layanan dikurangi saat pesanan berubah menjadi `completed`.
- Pembatalan dapat mencatat lembar kertas yang telanjur digunakan sebagai pergerakan stok `waste`.

### Harga

- Harga transaksi harus disalin sebagai snapshot ke detail transaksi.
- Perubahan harga master tidak boleh mengubah transaksi lama.
- Nilai uang disimpan sebagai `DECIMAL`, bukan `FLOAT` atau `DOUBLE`.
- Perhitungan final dilakukan di server, bukan hanya melalui JavaScript di browser.

### Stok

- Semua perubahan stok harus mempunyai sumber, jumlah, waktu, pengguna, dan catatan.
- Jangan hanya menyimpan angka stok terakhir tanpa riwayat pergerakan.
- Penjualan ATK mengurangi stok barang ketika transaksi diselesaikan atau pada titik bisnis yang disepakati.
- Pesanan batal/revisi tetap mengurangi bahan jika bahan sudah digunakan.
- Koreksi stok harus dicatat sebagai penyesuaian, bukan mengubah angka secara diam-diam.
- Stok tidak boleh negatif kecuali tim secara eksplisit memutuskan dan mendokumentasikan kebijakan tersebut.

### Pembayaran

- Pembayaran berkaitan dengan satu pesanan.
- Simpan jumlah bayar, metode, waktu, penerima, dan catatan.
- Status pembayaran minimal: `unpaid`, `partial`, dan `paid` jika pembayaran parsial digunakan.
- Total pembayaran tidak boleh dianggap sebagai total penjualan sebelum memenuhi aturan pengakuan transaksi yang dipilih tim.

## 7. Daftar Harga Awal

Harga berikut menjadi data awal dan sebaiknya dimasukkan melalui seeder. Semua nilai dalam rupiah.

### Fotokopi

| Jenis kertas | 1 sisi | Timbal balik |
|---|---:|---:|
| A4 | 200 | 400 |
| A3 | 400 | 800 |
| B5 | 175 | 350 |
| B4 | 350 | 700 |
| F4 | 200 | 400 |

### Cetak

| Jenis kertas | Hitam putih | Warna sedang | Full warna |
|---|---:|---:|---:|
| A4 | 300 | 500 | 1.000 |
| F4 | 350 | 600 | 1.200 |

Harga master harus memiliki masa berlaku atau status aktif agar perubahan harga di masa mendatang tidak merusak histori transaksi.

## 8. Entitas Data Minimum

Struktur final ditentukan pada ERD dan migration, tetapi sistem minimal perlu mempertimbangkan:

- `users`
- `roles` atau mekanisme role yang setara
- `employees`
- `customers` (opsional pada tahap awal; pesanan tetap boleh tanpa akun pelanggan)
- `product_categories`
- `units`
- `products`
- `services`
- `service_prices`
- `orders`
- `order_items`
- `payments`
- `stock_movements`
- `order_status_histories`
- `audit_logs`

Detail transaksi dapat memuat barang dan layanan dalam satu tabel dengan tipe item, atau dipisah menjadi tabel khusus. Pilihan harus menjaga integritas referensi, snapshot nama/harga, dan kemudahan laporan.

## 9. Audit Log

Audit log minimal mencatat:

- pengguna yang melakukan aksi;
- jenis aksi, misalnya create, update, delete, login, perubahan status, pembayaran, dan penyesuaian stok;
- entitas dan ID data yang terpengaruh;
- nilai sebelum dan sesudah untuk perubahan penting;
- alamat IP dan user agent jika relevan;
- waktu kejadian.

Audit log bersifat append-only bagi pengguna aplikasi. Jangan menyediakan fitur edit atau hapus audit log melalui antarmuka operasional biasa.

## 10. Pedoman Implementasi Laravel

- Gunakan migration untuk setiap perubahan skema database.
- Gunakan foreign key, index, unique constraint, dan tipe data yang tepat.
- Gunakan Form Request untuk validasi input yang kompleks.
- Letakkan aturan bisnis penting di service/action class atau model yang teruji; hindari controller yang terlalu besar.
- Gunakan database transaction untuk operasi yang mengubah pesanan, pembayaran, dan stok secara bersamaan.
- Gunakan enum PHP atau konstanta terpusat untuk status; hindari string status tersebar di banyak file.
- Gunakan Eloquent relationship dan eager loading untuk mencegah masalah N+1.
- Gunakan soft delete hanya jika memang diperlukan dan jelaskan pengaruhnya terhadap laporan.
- Jangan menyimpan kata sandi, credential, atau data rahasia di repository.
- Perbarui `.env.example` jika menambahkan konfigurasi baru.

## 11. Validasi dan Keamanan

- Semua input harus divalidasi di server.
- Lindungi form dengan CSRF protection Laravel.
- Gunakan password hashing bawaan Laravel.
- Terapkan rate limiting pada login dan endpoint sensitif.
- Cegah mass assignment dengan `$fillable` atau `$guarded` yang tepat.
- Gunakan parameter binding/Eloquent; jangan merangkai SQL dari input pengguna.
- Batasi upload berdasarkan tipe, ukuran, dan lokasi penyimpanan jika fitur lampiran ditambahkan.
- Tampilkan pesan kesalahan yang membantu pengguna tanpa membocorkan informasi internal.

## 12. Standar Kualitas dan Pengujian

Minimal buat feature test untuk:

- autentikasi dan otorisasi role;
- pembuatan pesanan ATK;
- pembuatan pesanan fotokopi/cetak;
- perhitungan harga setiap varian layanan;
- transaksi selesai yang mengurangi stok;
- pembatalan/revisi dengan bahan terpakai;
- pembatalan tanpa bahan terpakai;
- pembayaran dan laporan penjualan harian;
- penolakan input tidak valid dan akses tanpa izin.

Sebelum perubahan digabungkan:

```bash
php artisan test
./vendor/bin/pint --test
npm run build
```

## 13. Tahapan Pengembangan

### Fase 1 — Fondasi

- Konfigurasi MySQL dan environment.
- Authentication dan role.
- Migration, model, factory, dan seeder master data.

### Fase 2 — Operasional Inti

- Master barang, layanan, dan harga.
- Pembuatan pesanan.
- Kalkulasi harga.
- Pembayaran dan status transaksi.

### Fase 3 — Stok dan Audit

- Pergerakan stok.
- Pemakaian bahan pada pembatalan/revisi.
- Audit log dan histori status.

### Fase 4 — Laporan

- Stok dan pergerakan stok.
- Penjualan harian dan rentang tanggal.
- Ekspor/print laporan jika diperlukan.

### Fase 5 — Penyempurnaan

- Pengujian alur menyeluruh.
- Perbaikan UI responsif.
- Validasi bersama pemilik usaha.
- Dokumentasi dan persiapan presentasi.

## 14. Definition of Done

Sebuah fitur dianggap selesai jika:

- kebutuhan dan aturan bisnisnya sudah jelas;
- migration dan relasi data tersedia bila diperlukan;
- otorisasi dan validasi server diterapkan;
- operasi kritis aman secara transaksional;
- audit log tersedia untuk aksi penting;
- pengujian relevan lulus;
- tampilan dapat digunakan pada desktop dan ponsel;
- tidak merusak laporan atau histori transaksi lama;
- dokumentasi diperbarui jika perilaku sistem berubah.

## 15. Keputusan yang Masih Perlu Disepakati

- Bahan apa saja yang akan dilacak untuk layanan fotokopi/cetak: kertas saja atau termasuk tinta/toner.
- Waktu pasti pengurangan stok: saat mulai diproses atau saat transaksi selesai.
- Apakah pembayaran parsial dan piutang diperlukan.
- Apakah pelanggan perlu disimpan sebagai master data.
- Apakah integrasi WhatsApp hanya berupa pencatatan sumber pesanan atau integrasi pesan otomatis.
- Format struk.
- Kebijakan retur, refund, diskon, dan pajak jika kelak diperlukan.

Hindari mengasumsikan jawaban atas poin-poin tersebut di dalam kode. Catat keputusan tim terlebih dahulu, lalu perbarui requirement, ERD, migration, dan pengujian secara konsisten.
