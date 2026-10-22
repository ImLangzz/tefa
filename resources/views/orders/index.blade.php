@extends('layouts.app')

@section('title', 'Pesanan - KatalogTEFA')

@section('content')
    <div class="section">
        <div class="container">
            <h1 class="page-title">Pesanan Saya</h1>

            <section class="related-section">
                <h2>Pembelian</h2>
                @forelse($purchases as $order)
                    <article class="order-card">
                        <div>
                            <h3><a href="{{ route('products.show', $order->product) }}">{{ $order->product->name }}</a></h3>
                            <p>Penjual: {{ $order->seller->name }}</p>
                            <p>{{ $order->quantity }} × Rp {{ number_format($order->unit_price, 0, ',', '.') }}
                                = Rp {{ number_format($order->total_price, 0, ',', '.') }}</p>
                            <p>Status: <strong>{{ ucfirst($order->status) }}</strong></p>
                        </div>
                        @if($order->status === \App\Models\Order::STATUS_PENDING)
                            <form action="{{ route('orders.cancel', $order) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-outline">Batalkan pesanan</button>
                            </form>
                        @elseif($order->status === \App\Models\Order::STATUS_COMPLETED && !$order->rating)
                            <a href="{{ route('products.show', $order->product) }}" class="btn btn-primary">Beri rating</a>
                        @endif
                        @if($order->conversation)
                            <a href="{{ route('conversations.show', $order->conversation) }}" class="btn btn-outline">Buka Chat</a>
                        @endif
                    </article>
                @empty
                    <p>Belum ada pembelian.</p>
                @endforelse
            </section>

            <section class="related-section">
                <h2>Penjualan</h2>
                @forelse($sales as $order)
                    <article class="order-card">
                        <div>
                            <h3><a href="{{ route('products.show', $order->product) }}">{{ $order->product->name }}</a></h3>
                            <p>Pembeli: {{ $order->buyer->name }}</p>
                            <p>{{ $order->quantity }} × Rp {{ number_format($order->unit_price, 0, ',', '.') }}
                                = Rp {{ number_format($order->total_price, 0, ',', '.') }}</p>
                            <p>Status: <strong>{{ ucfirst($order->status) }}</strong></p>
                        </div>
                        @if($order->status === \App\Models\Order::STATUS_PENDING)
                            <form action="{{ route('orders.complete', $order) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-primary">Konfirmasi selesai</button>
                            </form>
                        @endif
                        @if($order->conversation)
                            <a href="{{ route('conversations.show', $order->conversation) }}" class="btn btn-outline">Buka Chat</a>
                        @endif
                    </article>
                @empty
                    <p>Belum ada pesanan untuk barang yang Anda jual.</p>
                @endforelse
            </section>
        </div>
    </div>
@endsection
