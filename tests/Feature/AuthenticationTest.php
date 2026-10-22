<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_and_registration_pages_are_available_to_guests(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('data-toggle-password="password"', false)
            ->assertSee('Masukkan email dan kata sandi akun KatalogTEFA Anda.');
        $this->get(route('register'))
            ->assertOk()
            ->assertSee('data-register-form', false)
            ->assertSee('data-password-meter', false)
            ->assertSee('data-password-match', false);
    }

    public function test_guests_are_redirected_to_login_before_accessing_the_site(): void
    {
        $this->get(route('home'))->assertRedirect(route('login'));
        $this->get(route('products.index'))->assertRedirect(route('login'));
    }

    public function test_user_can_login_and_logout(): void
    {
        $user = User::factory()->create([
            'email' => 'buyer@example.com',
            'password' => 'password123',
            'role' => User::ROLE_PEMBELI,
        ]);

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password123',
        ])->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($user);

        $this->post(route('logout'))->assertRedirect(route('home'));
        $this->assertGuest();
    }

    public function test_registration_creates_one_account_type_that_can_buy_and_sell(): void
    {
        $this->post(route('register.store'), [
            'name' => 'Seller Baru',
            'email' => 'seller@example.com',
            'role' => User::ROLE_PENJUAL,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('home'));

        $seller = User::where('email', 'seller@example.com')->firstOrFail();
        $this->assertSame(User::ROLE_PEMBELI, $seller->role);
        $this->assertTrue($seller->isMember());
        $this->assertAuthenticatedAs($seller);

        $this->post(route('logout'));

        $this->post(route('register.store'), [
            'name' => 'Admin Palsu',
            'email' => 'admin@example.com',
            'role' => User::ROLE_ADMIN,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('home'));

        $this->assertDatabaseHas('users', [
            'email' => 'admin@example.com',
            'role' => User::ROLE_PEMBELI,
        ]);
    }

    public function test_admin_login_redirects_to_admin_panel_and_admin_cannot_sell(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->post(route('login.store'), [
            'email' => $admin->email,
            'password' => 'password',
        ])->assertRedirect(route('admin.dashboard'));

        $this->get(route('admin.dashboard'))->assertOk()
            ->assertSee('Ringkasan website')
            ->assertSee('Laporan & Moderasi', false)
            ->assertDontSee('Tambah Produk');
        $this->actingAs($admin)->get(route('home'))->assertRedirect(route('admin.dashboard'));

        $this->get(route('products.create'))->assertForbidden();
        $this->post(route('products.store'))->assertForbidden();
    }

    public function test_members_can_add_products(): void
    {
        $this->get(route('products.create'))->assertRedirect(route('login'));

        $buyer = User::factory()->create(['role' => User::ROLE_PEMBELI]);
        $this->actingAs($buyer)->get(route('products.create'))->assertOk();
        $buyerCategory = Category::create([
            'name' => 'Kategori Pembeli',
            'slug' => 'kategori-pembeli',
        ]);
        $this->actingAs($buyer)->post(route('products.store'), [
            'name' => 'Barang dari Anggota',
            'description' => 'Anggota dapat menjual dan membeli.',
            'price' => 5000,
            'quantity' => 2,
            'category_id' => $buyerCategory->id,
        ])->assertRedirect(route('products.index'));
        $this->assertSame(
            $buyer->id,
            Product::where('name', 'Barang dari Anggota')->value('user_id')
        );

        foreach ([User::ROLE_PENJUAL, User::ROLE_PEMBELI] as $role) {
            $user = User::factory()->create(['role' => $role]);

            $this->actingAs($user)->get(route('products.create'))->assertOk();

            $category = Category::create([
                'name' => 'Kategori '.$role.' kedua',
                'slug' => 'kategori-'.$role.'-kedua',
            ]);

            $this->actingAs($user)->post(route('products.store'), [
                'name' => 'Produk '.$role,
                'description' => 'Deskripsi produk untuk pengujian.',
                'price' => 10000,
                'quantity' => 4,
                'category_id' => $category->id,
            ])->assertRedirect(route('products.index'));

            $this->assertSame(
                $user->id,
                Product::where('name', 'Produk '.$role)->value('user_id')
            );
        }
    }

    public function test_member_dashboard_displays_all_products_instead_of_featured_products(): void
    {
        $member = User::factory()->create(['role' => User::ROLE_PEMBELI]);
        $category = Category::create([
            'name' => 'Makanan',
            'slug' => 'makanan-dashboard',
        ]);

        foreach (range(1, 8) as $number) {
            $member->products()->create([
                'category_id' => $category->id,
                'name' => 'Barang katalog '.$number,
                'description' => 'Barang untuk beranda anggota.',
                'price' => 10000,
                'quantity' => 3,
            ]);
        }

        $this->actingAs($member)->get(route('home'))
            ->assertOk()
            ->assertSee('Semua Produk')
            ->assertSee('Barang katalog 1')
            ->assertSee('Barang katalog 8')
            ->assertDontSee('Produk Unggulan');
    }

    public function test_member_dashboard_search_and_category_filters_are_interactive(): void
    {
        $member = User::factory()->create(['role' => User::ROLE_PEMBELI]);
        $food = Category::create(['name' => 'Makanan', 'slug' => 'makanan-filter']);
        $craft = Category::create(['name' => 'Kerajinan', 'slug' => 'kerajinan-filter']);

        $member->products()->create([
            'category_id' => $food->id,
            'name' => 'Brownies Coklat',
            'description' => 'Kue coklat buatan TEFA.',
            'price' => 15000,
            'quantity' => 4,
        ]);
        $member->products()->create([
            'category_id' => $craft->id,
            'name' => 'Tas Rajut',
            'description' => 'Tas buatan tangan.',
            'price' => 50000,
            'quantity' => 2,
        ]);

        $this->actingAs($member)->get(route('home', ['search' => 'Brownies']))
            ->assertOk()
            ->assertSee('Brownies Coklat')
            ->assertDontSee('Tas Rajut');

        $this->actingAs($member)->get(route('home', ['category' => $craft->id]))
            ->assertOk()
            ->assertSee('Tas Rajut')
            ->assertDontSee('Brownies Coklat');
    }

    public function test_multiple_sellers_can_each_have_many_products(): void
    {
        $firstSeller = User::factory()->create(['role' => User::ROLE_PENJUAL]);
        $secondSeller = User::factory()->create(['role' => User::ROLE_PENJUAL]);
        $category = Category::create([
            'name' => 'Makanan',
            'slug' => 'makanan',
        ]);

        foreach ([$firstSeller, $secondSeller] as $seller) {
            $seller->products()->createMany([
                [
                    'category_id' => $category->id,
                    'name' => 'Produk A '.$seller->id,
                    'description' => 'Produk pertama.',
                    'price' => 10000,
                ],
                [
                    'category_id' => $category->id,
                    'name' => 'Produk B '.$seller->id,
                    'description' => 'Produk kedua.',
                    'price' => 20000,
                ],
            ]);
        }

        $this->assertCount(2, $firstSeller->products);
        $this->assertCount(2, $secondSeller->products);
        $this->assertTrue($firstSeller->products->every(
            fn (Product $product) => $product->user->is($firstSeller)
        ));
        $this->assertTrue($secondSeller->products->every(
            fn (Product $product) => $product->user->is($secondSeller)
        ));
    }
}
