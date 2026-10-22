<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        return view('orders.index', [
            'purchases' => $user->purchases()->with(['product', 'seller', 'conversation'])->latest()->get(),
            'sales' => $user->sales()->with(['product', 'buyer', 'conversation'])->latest()->get(),
        ]);
    }

    public function store(Request $request, Product $product): RedirectResponse
    {
        abort_if($request->user()->is_blacklisted, 403, 'Akun Anda diblokir dari aktivitas jual beli.');
        abort_unless(
            $product->is_active && $product->user && ! $product->user->is_blacklisted,
            404
        );
        abort_if($product->user_id === $request->user()->id, 403, 'Anda tidak dapat membeli produk sendiri.');

        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        abort_unless($product->user, 422, 'Penjual produk ini sudah tidak tersedia.');

        $conversation = DB::transaction(function () use ($request, $product, $validated) {
            $lockedProduct = Product::query()->lockForUpdate()->findOrFail($product->id);
            abort_if($lockedProduct->quantity < $validated['quantity'], 422, 'Stok produk tidak mencukupi.');

            $conversation = Conversation::firstOrCreate([
                'product_id' => $lockedProduct->id,
                'buyer_id' => $request->user()->id,
                'seller_id' => $lockedProduct->user_id,
            ]);

            $order = Order::create([
                'buyer_id' => $request->user()->id,
                'seller_id' => $lockedProduct->user_id,
                'product_id' => $lockedProduct->id,
                'conversation_id' => $conversation->id,
                'quantity' => $validated['quantity'],
                'unit_price' => $lockedProduct->price,
                'total_price' => $lockedProduct->price * $validated['quantity'],
                'status' => Order::STATUS_PENDING,
            ]);

            Message::create([
                'conversation_id' => $conversation->id,
                'sender_id' => $request->user()->id,
                'body' => sprintf(
                    'Saya ingin memesan %s sebanyak %d. Nomor pesanan: #%d.',
                    $lockedProduct->name,
                    $order->quantity,
                    $order->id
                ),
            ]);
            $conversation->touch();

            return $conversation;
        });

        return redirect()->route('conversations.show', $conversation)
            ->with('success', 'Pesanan dibuat. Silakan lanjutkan pembicaraan dengan penjual.');
    }

    public function complete(Request $request, Order $order): RedirectResponse
    {
        abort_if($request->user()->is_blacklisted, 403, 'Akun Anda diblokir dari aktivitas jual beli.');
        abort_unless($order->seller_id === $request->user()->id, 403);

        DB::transaction(function () use ($order) {
            $order = Order::query()->lockForUpdate()->findOrFail($order->id);
            abort_unless($order->status === Order::STATUS_PENDING, 422, 'Pesanan ini sudah diproses.');

            $product = Product::query()->lockForUpdate()->findOrFail($order->product_id);

            abort_if($product->quantity < $order->quantity, 422, 'Stok tidak mencukupi untuk menyelesaikan pesanan.');

            $product->decrement('quantity', $order->quantity);
            $order->update(['status' => Order::STATUS_COMPLETED]);
        });

        return back()->with('success', 'Pesanan ditandai selesai. Pembeli sekarang dapat memberi rating.');
    }

    public function cancel(Request $request, Order $order): RedirectResponse
    {
        abort_unless($order->buyer_id === $request->user()->id, 403);
        abort_unless($order->status === Order::STATUS_PENDING, 422, 'Hanya pesanan yang menunggu yang dapat dibatalkan.');

        $order->update(['status' => Order::STATUS_CANCELLED]);

        return back()->with('success', 'Pesanan dibatalkan.');
    }
}
