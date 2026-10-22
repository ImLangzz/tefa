<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ConversationController extends Controller
{
    public function index(Request $request): View
    {
        $userId = $request->user()->id;

        $conversations = Conversation::query()
            ->with(['product', 'buyer', 'seller', 'latestMessage'])
            ->where(fn ($query) => $query->where('buyer_id', $userId)->orWhere('seller_id', $userId))
            ->latest('updated_at')
            ->paginate(20);

        return view('conversations.index', compact('conversations'));
    }

    public function start(Request $request, Product $product): RedirectResponse
    {
        abort_if($request->user()->is_blacklisted, 403, 'Akun Anda diblokir dari aktivitas jual beli.');
        abort_unless(
            $product->is_active && $product->user && ! $product->user->is_blacklisted,
            404
        );
        abort_if($product->user_id === $request->user()->id, 403, 'Anda tidak dapat mengirim pesan kepada diri sendiri.');

        $conversation = Conversation::firstOrCreate([
            'product_id' => $product->id,
            'buyer_id' => $request->user()->id,
            'seller_id' => $product->user_id,
        ]);

        return redirect()->route('conversations.show', $conversation);
    }

    public function show(Request $request, Conversation $conversation): View
    {
        $this->authorizeParticipant($request, $conversation);

        $conversation->load([
            'product',
            'buyer',
            'seller',
            'messages.sender',
        ]);

        return view('conversations.show', compact('conversation'));
    }

    public function send(Request $request, Conversation $conversation): RedirectResponse
    {
        $this->authorizeParticipant($request, $conversation);
        abort_if($request->user()->is_blacklisted, 403, 'Akun Anda diblokir dari aktivitas jual beli.');

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $conversation->messages()->create([
            'sender_id' => $request->user()->id,
            'body' => $validated['body'],
        ]);

        $conversation->touch();

        return redirect()->route('conversations.show', $conversation);
    }

    private function authorizeParticipant(Request $request, Conversation $conversation): void
    {
        abort_unless(
            in_array($request->user()->id, [$conversation->buyer_id, $conversation->seller_id], true),
            403
        );
    }
}
