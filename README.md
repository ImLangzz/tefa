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

Anggota dapat mengunggah produk dengan nama, kategori, harga, jumlah stok, deskripsi,
tanpa perlu mencantumkan nomor WhatsApp. Anggota dapat memulai chat langsung dengan
penjual dari halaman produk. Tombol **Pesan Barang** membuat pesanan tertunda sekaligus
membuka percakapan dengan penjual; pesan dan balasan tersedia di menu **Chat**.
Penjual mengonfirmasi pesanan selesai melalui menu **Pesanan**; stok dikurangi saat
konfirmasi dan pembeli baru dapat memberi satu rating untuk pesanan yang selesai.

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

Proyek memakai runtime Container Vercel melalui `Dockerfile.vercel`. Runtime Container
Vercel masih berstatus beta. Deployment produksi memerlukan database MySQL atau
PostgreSQL eksternal; jangan memakai SQLite di filesystem deployment. Atur variabel
`DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, dan `DB_PASSWORD`
di Vercel, lalu jalankan migrasi produksi satu kali dengan `php artisan migrate --force`.

Tambahkan `APP_KEY` (buat lokal dengan `php artisan key:generate --show`), `APP_URL`,
dan `APP_DEBUG=false` sebagai Environment Variables di Vercel. Jangan commit atau
mengunggah `.env`. Biarkan sesi dan cache memakai database, sebagaimana nilai default
di `Dockerfile.vercel`.

Filesystem container tidak boleh dianggap penyimpanan permanen. Sebelum mengaktifkan
unggah gambar produk, konfigurasi object storage eksternal yang kompatibel dengan S3;
disk publik lokal saat ini hanya cocok untuk pengembangan lokal. Data admin juga harus
dibuat di database produksi setelah migrasi selesai, bukan disalin dari database lokal.

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
