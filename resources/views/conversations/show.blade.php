@extends('layouts.app')

@section('title', 'Chat - KatalogTEFA')

@section('content')
    @php
        $otherUser = auth()->id() === $conversation->buyer_id
            ? $conversation->seller
            : $conversation->buyer;
    @endphp

    <div class="section">
        <div class="container">
            <a href="{{ route('conversations.index') }}" class="back-link">← Kembali ke Chat</a>

            <section class="chat-panel">
                <header class="chat-header">
                    <div>
                        <h1>{{ $otherUser->name }}</h1>
                        <p>Produk: <a href="{{ route('products.show', $conversation->product) }}">{{ $conversation->product->name }}</a></p>
                    </div>
                    <a href="{{ route('products.show', $conversation->product) }}" class="btn btn-outline">Lihat Produk</a>
                </header>

                <div class="chat-messages" aria-live="polite">
                    @forelse($conversation->messages as $message)
                        <article class="chat-message {{ $message->sender_id === auth()->id() ? 'chat-message-own' : '' }}">
                            <strong>{{ $message->sender->name }}</strong>
                            <p>{{ $message->body }}</p>
                            <small>{{ $message->created_at->format('d/m/Y H:i') }}</small>
                        </article>
                    @empty
                        <p class="form-hint">Belum ada pesan. Mulai percakapan di bawah.</p>
                    @endforelse
                </div>

                @if(!auth()->user()->is_blacklisted)
                    <form action="{{ route('conversations.send', $conversation) }}" method="POST" class="chat-compose">
                        @csrf
                        <label for="body" class="form-label">Pesan</label>
                        <textarea id="body" name="body" class="form-input form-textarea" maxlength="2000"
                            placeholder="Tulis pesan..." required>{{ old('body') }}</textarea>
                        @error('body')
                            <p class="error-msg">{{ $message }}</p>
                        @enderror
                        <button type="submit" class="btn btn-primary">Kirim Pesan</button>
                    </form>
                @else
                    <p class="form-hint">Akun Anda diblokir dari aktivitas jual beli dan chat.</p>
                @endif
            </section>
        </div>
    </div>
@endsection
