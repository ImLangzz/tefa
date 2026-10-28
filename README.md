<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Login dan Peran Pengguna

Pengunjung dapat membuat akun anggota melalui `/register`. Semua anggota memiliki hak
yang sama: satu akun dapat membeli dan menjual. Hanya **admin** yang memiliki akses
moderasi; pendaftaran admin publik tidak tersedia.

Anggota dapat mengunggah promosi usaha dengan nama, kategori, deskripsi, dan alamat,
tanpa perlu mencantumkan harga, stok, koordinat GPS, atau nomor WhatsApp. Katalog TEFA hanya berfungsi
sebagai media promosi dan penunjuk lokasi. Tidak ada fitur pesanan, pembelian, ataupun
chat dalam aplikasi; pengunjung menghubungi penjual secara langsung di luar aplikasi.
Ulasan yang sudah ada tetap ditampilkan sebagai informasi produk.

Setiap promosi dapat mencantumkan alamat tempat usaha. Tautan Google Maps mencari
alamat tersebut; aplikasi tidak mengambil koordinat GPS atau melacak lokasi pembeli.

Anggota dapat melaporkan produk yang diduga melanggar aturan. Admin meninjau laporan di
menu **Moderasi**, menonaktifkan barang, mem-blacklist penjual (semua produknya
disembunyikan), mengabaikan laporan, atau memulihkan akun/produk yang telah ditinjau.
Pemeriksaan barang ilegal dilakukan melalui laporan dan moderasi admin, bukan deteksi
otomatis.

Untuk membuat akun admin tanpa menjalankan ulang seeder katalog, atur `ADMIN_EMAIL`
dan `ADMIN_PASSWORD` di `.env` (opsional `ADMIN_NAME`), lalu jalankan:

```sh
php artisan db:seed --class=AdminSeeder
```

Akun admin akan dibuat atau diperbarui dengan nilai tersebut. Jangan gunakan kata
sandi contoh di lingkungan produksi. Sesi login menggunakan driver pada `SESSION_DRIVER`.

## Deploy ke Vercel

Project Laravel ini menggunakan Dockerfile dan Vercel Container Runtime (saat ini
berstatus beta). Tampilan tetap berasal dari Blade dan asset Vite yang sama. Vercel
menjalankan container secara stateless, jadi database dan gambar yang diunggah harus
disimpan di layanan eksternal.

1. Pastikan perubahan project sudah di-push ke GitHub. Jangan commit `.env`.
2. Di Vercel, pilih **Add New → Project**, lalu import repository GitHub.
   Biarkan Root Directory di root repository. `vercel.json` memilih preset Container;
   jika diminta, pilih **Container** sebagai Framework Preset.
3. Siapkan PostgreSQL eksternal dan bucket penyimpanan S3-compatible, misalnya
   Cloudflare R2. Pastikan Vercel Container Runtime tersedia untuk akun/project Anda.
4. Di **Project → Settings → Environment Variables**, tambahkan:

   | Variable | Isi |
   | --- | --- |
   | `APP_ENV` | `production` |
   | `APP_DEBUG` | `false` |
   | `APP_KEY` | Key production baru dari `php artisan key:generate --show` |
   | `APP_URL` | URL deployment Vercel, misalnya `https://nama-project.vercel.app` |
   | `LOG_CHANNEL` | `stderr` |
   | `DB_CONNECTION` | `pgsql` |
   | `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` | Nilai koneksi dari penyedia PostgreSQL |
   | `SESSION_DRIVER` | `database` |
   | `SESSION_SECURE_COOKIE` | `true` |
   | `CACHE_STORE` | `database` |
   | `QUEUE_CONNECTION` | `sync` |
   | `PUBLIC_FILESYSTEM_DISK` | `s3` |
   | `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`, `AWS_BUCKET` | Kredensial dan nama bucket |
   | `AWS_DEFAULT_REGION`, `AWS_ENDPOINT`, `AWS_URL` | Region, endpoint API, dan URL publik untuk gambar |
   | `AWS_USE_PATH_STYLE_ENDPOINT` | `true` jika diwajibkan penyedia object storage |

   Untuk bucket privat, atur akses publik/CDN dengan benar atau gunakan URL gambar
   yang dapat diakses browser. Jangan menggunakan nilai contoh sebagai kredensial.
5. Deploy project. Build membuat asset frontend, menginstal dependensi Laravel, lalu
   Apache melayani aplikasi melalui folder `public/`.
6. Sebelum aplikasi digunakan, jalankan migrasi ke PostgreSQL production. Di terminal
   pada folder project, pasang dan hubungkan Vercel CLI satu kali:

   ```sh
   composer install
   npm install --global vercel
   vercel login
   vercel link
   ```

   Setelah Vercel meminta konfirmasi project, pilih project yang baru dibuat. Lalu
   jalankan migrasi dan seeder dengan Environment Variables production dari Vercel:

   ```sh
   vercel env run -e production -- php artisan migrate --force
   vercel env run -e production -- php artisan db:seed --class=AdminSeeder
   ```

   `vercel env run` menjalankan perintah dengan variables Vercel tanpa menulis secret
   ke file. Atur `ADMIN_EMAIL`, `ADMIN_PASSWORD`, dan opsional `ADMIN_NAME` di Vercel
   sebelum menjalankan seeder.

   Pada Windows dengan XAMPP, PHP lokal juga perlu ekstensi `pdo_pgsql` untuk
   menjalankan migrasi. Jika `php -m` belum menampilkan `pdo_pgsql`, buka
   `C:\xampp\php\php.ini`, hapus tanda `;` di depan `extension=pdo_pgsql`, simpan,
   lalu buka ulang terminal. Pastikan aktif dengan `php -m | findstr pdo_pgsql`.
7. Deploy ulang setelah mengubah Environment Variables. Buka URL deployment dan tes
   pendaftaran, login, katalog, serta upload gambar.

`database/database.sqlite` dan penyimpanan lokal tidak digunakan sebagai penyimpanan
production karena perubahan file pada container Vercel tidak permanen. Jangan gunakan
APP_KEY atau password admin lokal untuk production; buat nilai production baru.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
