@extends('layouts.app')

@section('title', 'Chat - KatalogTEFA')

@section('content')
    <div class="section">
        <div class="container">
            <h1 class="page-title">Chat</h1>
            <p class="form-hint">Percakapan Anda dengan penjual dan pembeli.</p>

            <div class="chat-list">
                @forelse($conversations as $conversation)
                    @php
                        $otherUser = auth()->id() === $conversation->buyer_id
                            ? $conversation->seller
                            : $conversation->buyer;
                    @endphp
                    <a href="{{ route('conversations.show', $conversation) }}" class="chat-list-item">
                        <div>
                            <strong>{{ $otherUser->name }}</strong>
                            <p>{{ $conversation->product->name }}</p>
                            <p class="chat-preview">{{ $conversation->latestMessage?->body ?? 'Belum ada pesan.' }}</p>
                        </div>
                        <span>{{ $conversation->updated_at->format('d/m/Y H:i') }}</span>
                    </a>
                @empty
                    <div class="empty-state">
                        <p>Belum ada percakapan. Buka produk lalu pilih Chat Penjual untuk memulai.</p>
                        <a href="{{ route('products.index') }}">Lihat katalog</a>
                    </div>
                @endforelse
            </div>

            @if($conversations->hasPages())
                <div class="pagination-wrap">{{ $conversations->links() }}</div>
            @endif
        </div>
    </div>
@endsection
