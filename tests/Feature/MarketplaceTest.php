<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketplaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_page_shows_promotion_and_location_without_chat_or_order_actions(): void
    {
        $seller = User::factory()->create(['role' => User::ROLE_PENJUAL]);
        $buyer = User::factory()->create(['role' => User::ROLE_PEMBELI]);
        $product = $this->createProduct($seller, ['name' => 'Produk untuk dipromosikan']);

        $this->actingAs($buyer)->get(route('products.show', $product))
            ->assertOk()
            ->assertDontSee('Hubungi Penjual')
            ->assertDontSee('Chat')
            ->assertDontSee('Pesan Barang')
            ->assertDontSee('Pesanan Saya')
            ->assertSee('Usaha TEFA')
            ->assertSee('Lokasi Tempat Jualan')
            ->assertSee('https://www.google.com/maps/dir/?api=1&amp;destination=Jalan+Mawar+5%2C+Bandung', false)
            ->assertSee('📍 Jalan Mawar 5, Bandung')
            ->assertSee('Lihat rute di Google Maps')
            ->assertDontSee('Harga')
            ->assertDontSee('Stok')
            ->assertDontSee('Latitude')
            ->assertDontSee('Longitude')
            ->assertDontSee('6.914744');
    }

    public function test_catalog_address_links_directly_to_google_maps_directions(): void
    {
        $member = User::factory()->create(['role' => User::ROLE_PEMBELI]);
        $product = $this->createProduct($member);

        $this->actingAs($member)->get(route('home'))
            ->assertOk()
            ->assertSee('https://www.google.com/maps/dir/?api=1&amp;destination=Jalan+Mawar+5%2C+Bandung', false)
            ->assertSee('📍 Jalan Mawar 5, Bandung')
            ->assertSee('Lihat rute di Google Maps')
            ->assertSeeInOrder(['Produk TEFA', 'Nama usaha:', 'Usaha TEFA', 'Bidang:', 'Kerajinan', 'Lihat promosi', 'Alamat:']);

        $this->actingAs($member)->get(route('products.index'))
            ->assertOk()
            ->assertSee('https://www.google.com/maps/dir/?api=1&amp;destination=Jalan+Mawar+5%2C+Bandung', false)
            ->assertSee('📍 Jalan Mawar 5, Bandung')
            ->assertSee('Lihat rute di Google Maps')
            ->assertSeeInOrder(['Produk TEFA', 'Nama usaha:', 'Usaha TEFA', 'Bidang:', 'Kerajinan', 'Lihat promosi', 'Alamat:']);
    }

    public function test_product_form_and_schema_only_include_promotion_details(): void
    {
        $seller = User::factory()->create(['role' => User::ROLE_PENJUAL]);

        $this->actingAs($seller)->get(route('products.create'))
            ->assertOk()
            ->assertSee('Nama Usaha')
            ->assertSee('Alamat lengkap')
            ->assertDontSee('Harga')
            ->assertDontSee('Stok')
            ->assertDontSee('Latitude')
            ->assertDontSee('Longitude')
            ->assertDontSee('GPS');

        $this->assertFalse(\Illuminate\Support\Facades\Schema::hasColumns('products', [
            'price', 'quantity', 'latitude', 'longitude',
        ]));
    }

    public function test_catalog_has_no_chat_or_order_routes_and_no_footer(): void
    {
        $member = User::factory()->create(['role' => User::ROLE_PEMBELI]);

        $this->actingAs($member)->get(route('home'))
            ->assertOk()
            ->assertSee('Promosi Usaha TEFA')
            ->assertDontSee('Pesanan Saya')
            ->assertDontSee('Chat')
            ->assertDontSee('Teaching Factory')
            ->assertDontSee('Produk & Jasa Unggulan Siswa SMK');

        $this->assertFalse(
            app('router')->getRoutes()->hasNamedRoute('orders.index')
        );
        $this->assertFalse(
            app('router')->getRoutes()->hasNamedRoute('orders.store')
        );
        $this->assertFalse(
            app('router')->getRoutes()->hasNamedRoute('ratings.store')
        );
        $this->assertFalse(
            app('router')->getRoutes()->hasNamedRoute('conversations.index')
        );
        $this->assertFalse(
            app('router')->getRoutes()->hasNamedRoute('conversations.start')
        );
    }

    public function test_admin_can_blacklist_seller_after_product_report(): void
    {
        $seller = User::factory()->create(['role' => User::ROLE_PENJUAL]);
        $reporter = User::factory()->create(['role' => User::ROLE_PEMBELI]);
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $product = $this->createProduct($seller, ['name' => 'Barang terlarang']);

        $this->actingAs($reporter)->post(route('products.report', $product), [
            'reason' => 'Produk ini melanggar aturan katalog TEFA.',
        ])->assertSessionHas('success');

        $report = ProductReport::firstOrFail();

        $this->actingAs($admin)->post(route('admin.reports.blacklist', $report))
            ->assertSessionHas('success');

        $this->actingAs($admin)->get(route('admin.reports.index'))
            ->assertOk()
            ->assertSee('Laporan Produk')
            ->assertSee('Akun dalam blacklist');

        $this->assertTrue($seller->fresh()->is_blacklisted);
        $this->assertFalse($product->fresh()->is_active);
        $this->assertSame('reviewed', $report->fresh()->status);

        $blockedSeller = $seller->fresh();
        $this->actingAs($blockedSeller)->get(route('products.index'))->assertDontSee('Barang terlarang');
        $this->actingAs($blockedSeller)->get(route('products.create'))->assertForbidden();
    }

    public function test_admin_can_review_and_permanently_delete_products_but_members_cannot(): void
    {
        $seller = User::factory()->create(['role' => User::ROLE_PENJUAL]);
        $buyer = User::factory()->create(['role' => User::ROLE_PEMBELI]);
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $product = $this->createProduct($seller, ['name' => 'Produk mencurigakan']);

        $this->actingAs($buyer)->post(route('products.report', $product), [
            'reason' => 'Produk ini harus diperiksa oleh admin.',
        ]);

        $this->actingAs($buyer)->get(route('admin.products.index'))->assertForbidden();
        $this->actingAs($admin)->get(route('admin.products.index'))
            ->assertOk()
            ->assertSee('Produk mencurigakan')
            ->assertSee('Semua Produk');

        $this->actingAs($admin)->delete(route('admin.products.destroy', $product))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
        $this->assertDatabaseMissing('product_reports', ['product_id' => $product->id]);
    }

    private function createProduct(User $seller, array $attributes = []): Product
    {
        $category = Category::create([
            'name' => 'Kerajinan',
            'slug' => 'kerajinan',
        ]);

        return $seller->products()->create(array_merge([
            'category_id' => $category->id,
            'name' => 'Produk TEFA',
            'description' => 'Produk buatan TEFA.',
            'business_name' => 'Usaha TEFA',
            'location_address' => 'Jalan Mawar 5, Bandung',
        ], $attributes));
    }
}
