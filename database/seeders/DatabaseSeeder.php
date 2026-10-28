<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Buat Kategori
        $categories = [
            ['name' => 'Makanan', 'slug' => 'makanan'],
            ['name' => 'Minuman', 'slug' => 'minuman'],
            ['name' => 'Kerajinan Tangan', 'slug' => 'kerajinan-tangan'],
            ['name' => 'Jasa', 'slug' => 'jasa'],
        ];

        foreach ($categories as $cat) {
            Category::create($cat);
        }

        // Buat Produk
        $products = [
            // Makanan
            [
                'category_id' => 1,
                'name' => 'Kue Brownies Coklat',
                'description' => 'Brownies coklat lezat buatan siswa TEFA, menggunakan bahan-bahan pilihan berkualitas tinggi. Tekstur lembut di dalam dan sedikit renyah di luar.',
                'image' => null,
            ],
            [
                'category_id' => 1,
                'name' => 'Onde-Onde Isi Kacang',
                'description' => 'Onde-onde tradisional dengan isi kacang hijau yang manis. Dibuat fresh setiap hari oleh tim TEFA Boga.',
                'image' => null,
            ],
            [
                'category_id' => 1,
                'name' => 'Nasi Kotak Spesial',
                'description' => 'Nasi kotak lengkap dengan lauk pauk pilihan, cocok untuk acara dan meeting. Bisa dipesan minimal 10 kotak.',
                'image' => null,
            ],
            [
                'category_id' => 1,
                'name' => 'Risoles Mayo',
                'description' => 'Risoles isi ayam dan mayonaise yang gurih dan renyah. Camilan favorit dari dapur TEFA.',
                'image' => null,
            ],
            // Minuman
            [
                'category_id' => 2,
                'name' => 'Thai Tea Original',
                'description' => 'Thai tea dengan rasa original yang khas, perpaduan teh kuat dengan susu yang creamy. Disajikan dingin segar.',
                'image' => null,
            ],
            [
                'category_id' => 2,
                'name' => 'Es Teh Tarik',
                'description' => 'Teh tarik susu yang kaya rasa, dibuat dengan teknik tarik tradisional untuk menghasilkan busa yang sempurna.',
                'image' => null,
            ],
            [
                'category_id' => 2,
                'name' => 'Jus Mangga Segar',
                'description' => 'Jus mangga segar tanpa pengawet, dibuat dari buah mangga pilihan yang manis dan harum.',
                'image' => null,
            ],
            // Kerajinan Tangan
            [
                'category_id' => 3,
                'name' => 'Tas Rajut Multifungsi',
                'description' => 'Tas rajut handmade buatan siswa jurusan Tata Busana. Tersedia dalam berbagai warna, cocok untuk harian maupun acara santai.',
                'image' => null,
            ],
            [
                'category_id' => 3,
                'name' => 'Tempat Tisu Batik',
                'description' => 'Tempat tisu cantik bermotif batik, dibuat dari bahan kayu lapis yang dicat dengan motif batik Jawa.',
                'image' => null,
            ],
            [
                'category_id' => 3,
                'name' => 'Cermin Hias Kayu',
                'description' => 'Cermin dengan bingkai kayu yang dihias ukiran tangan siswa TEFA. Unik dan bernilai seni tinggi.',
                'image' => null,
            ],
            // Jasa
            [
                'category_id' => 4,
                'name' => 'Servis Komputer & Laptop',
                'description' => 'Jasa servis komputer dan laptop oleh siswa jurusan TKJ, meliputi install ulang OS, pembersihan virus, dan penggantian komponen.',
                'image' => null,
            ],
            [
                'category_id' => 4,
                'name' => 'Desain Grafis & Cetak',
                'description' => 'Jasa desain grafis untuk undangan, banner, poster, brosur dan lainnya. Dikerjakan oleh siswa jurusan Multimedia yang berpengalaman.',
                'image' => null,
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }

        if (env('ADMIN_EMAIL') && env('ADMIN_PASSWORD')) {
            $this->call(AdminSeeder::class);
        }
    }
}
