<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RatingController extends Controller
{
    public function store(Request $request, Order $order): RedirectResponse
    {
        abort_unless($order->buyer_id === $request->user()->id, 403);
        abort_unless($order->status === Order::STATUS_COMPLETED, 403, 'Rating hanya tersedia setelah pesanan selesai.');
        abort_if($order->rating()->exists(), 409, 'Pesanan ini sudah diberi rating.');

        $validated = $request->validate([
            'stars' => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        $order->rating()->create([
            ...$validated,
            'product_id' => $order->product_id,
            'buyer_id' => $order->buyer_id,
        ]);

        return back()->with('success', 'Terima kasih, rating Anda berhasil disimpan.');
    }
}
