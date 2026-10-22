<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Conversation;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketplaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_order_through_chat_and_rate_after_seller_confirms(): void
    {
        $seller = User::factory()->create(['role' => User::ROLE_PENJUAL]);
        $buyer = User::factory()->create(['role' => User::ROLE_PEMBELI]);
        $outsider = User::factory()->create(['role' => User::ROLE_PEMBELI]);
        $product = $this->createProduct($seller, [
            'name' => 'Tas TEFA',
            'quantity' => 5,
        ]);

        $response = $this->actingAs($buyer)->post(route('orders.store', $product), [
            'quantity' => 2,
        ]);

        $conversation = Conversation::firstOrFail();
        $response->assertRedirect(route('conversations.show', $conversation));

        $order = Order::firstOrFail();
        $this->assertSame(Order::STATUS_PENDING, $order->status);
        $this->assertSame($buyer->id, $order->buyer_id);
        $this->assertSame($seller->id, $order->seller_id);
        $this->assertSame(20000.0, (float) $order->total_price);
        $this->assertSame($conversation->id, $order->conversation_id);
        $this->assertSame(5, $product->fresh()->quantity);
        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'sender_id' => $buyer->id,
            'body' => "Saya ingin memesan Tas TEFA sebanyak 2. Nomor pesanan: #{$order->id}.",
        ]);

        $this->actingAs($seller)->post(route('conversations.send', $conversation), [
            'body' => 'Pesanan akan segera diproses.',
        ])->assertRedirect(route('conversations.show', $conversation));
        $this->actingAs($buyer)->get(route('conversations.show', $conversation))
            ->assertOk()
            ->assertSee('Pesanan akan segera diproses.');
        $this->actingAs($outsider)->get(route('conversations.show', $conversation))
            ->assertForbidden();

        $this->actingAs($buyer)->post(route('ratings.store', $order), [
            'stars' => 5,
            'comment' => 'Barang bagus.',
        ])->assertForbidden();

        $this->actingAs($seller)->post(route('orders.complete', $order))
            ->assertSessionHas('success');

        $this->assertSame(Order::STATUS_COMPLETED, $order->fresh()->status);
        $this->assertSame(3, $product->fresh()->quantity);

        $this->actingAs($buyer)->post(route('ratings.store', $order), [
            'stars' => 5,
            'comment' => 'Barang bagus.',
        ])->assertRedirect();

        $this->assertDatabaseHas('ratings', [
            'order_id' => $order->id,
            'product_id' => $product->id,
            'buyer_id' => $buyer->id,
            'stars' => 5,
        ]);

        $this->actingAs($buyer)->post(route('ratings.store', $order), [
            'stars' => 4,
        ])->assertStatus(409);
    }

    public function test_product_page_shows_chat_and_order_actions_for_available_seller_products(): void
    {
        $seller = User::factory()->create(['role' => User::ROLE_PENJUAL]);
        $buyer = User::factory()->create(['role' => User::ROLE_PEMBELI]);
        $product = $this->createProduct($seller, ['name' => 'Produk untuk dipesan']);

        $this->actingAs($buyer)->get(route('products.show', $product))
            ->assertOk()
            ->assertSee('Chat Penjual')
            ->assertSee('Pesan Barang');
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

        $this->actingAs($buyer)->post(route('orders.store', $product), [
            'quantity' => 1,
        ]);
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
        $this->assertDatabaseMissing('orders', ['product_id' => $product->id]);
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
            'price' => 10000,
            'quantity' => 5,
        ], $attributes));
    }
}
