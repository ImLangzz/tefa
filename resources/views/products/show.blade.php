@extends('layouts.app')

@section('title', $product->name . ' - KatalogTEFA')

@section('content')
    <div class="section">
        <div class="container">
            <a href="{{ route('products.index') }}" class="back-link">← Kembali ke Katalog</a>

            <div class="product-detail-wrap">
                <!-- Gambar -->
                <div class="detail-img-side">
                    @if($product->image)
                        <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="detail-img">
                    @else
                        <div class="detail-img-placeholder">
                            <span>📦</span>
                        </div>
                    @endif
                </div>

                <!-- Info -->
                <div class="detail-info-side">
                    <span class="detail-category">{{ $product->category->name }}</span>
                    <h1 class="detail-name">{{ $product->name }}</h1>
                    <p class="detail-price">{{ $product->price_formatted }}</p>
                    <p class="product-meta">
                        ⭐ {{ $product->ratings->isNotEmpty() ? number_format($product->ratings->avg('stars'), 1).' / 5' : 'Belum ada rating' }}
                        ({{ $product->ratings->count() }} rating)
                    </p>
                    <p class="product-meta">
                        {{ $product->quantity > 0 ? 'Stok tersedia: '.$product->quantity : 'Stok habis / belum diatur' }}
                    </p>
                    <p class="product-meta">Penjual: {{ $product->user?->name ?? 'Contoh katalog TEFA' }}</p>
                    <div class="detail-divider"></div>
                    <h3 class="detail-about-title">Tentang Produk</h3>
                    <p class="detail-desc">{{ $product->description }}</p>
                    <div class="detail-actions">
                        <a href="{{ route('products.index') }}" class="btn btn-outline">← Katalog</a>
                        @auth
                            @if(auth()->user()->isMember() && !auth()->user()->is_blacklisted && $product->user && auth()->id() !== $product->user_id)
                                <form action="{{ route('conversations.start', $product) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-outline">Chat Penjual</button>
                                </form>
                            @endif
                            @if(auth()->user()->isMember() && !auth()->user()->is_blacklisted && $product->user && auth()->id() !== $product->user_id && $product->quantity > 0)
                                <form action="{{ route('orders.store', $product) }}" method="POST" class="purchase-form">
                                    @csrf
                                    <label for="quantity" class="form-label">Jumlah</label>
                                    <input id="quantity" type="number" name="quantity" value="1" min="1"
                                        max="{{ $product->quantity }}" class="form-input" required>
                                    <button type="submit" class="btn btn-primary">Pesan Barang</button>
                                </form>
                            @elseif(auth()->id() === $product->user_id)
                                <span class="form-hint">Ini barang yang Anda jual.</span>
                            @elseif($product->quantity < 1)
                                <span class="form-hint">Stok habis.</span>
                            @elseif(!$product->user)
                                <span class="form-hint">Penjual produk ini sudah tidak tersedia, sehingga barang tidak dapat dipesan melalui chat.</span>
                            @endif
                            @if(auth()->user()->canManageProducts() && auth()->id() !== $product->user_id)
                                <details class="report-form">
                                    <summary>Laporkan produk</summary>
                                    <form action="{{ route('products.report', $product) }}" method="POST">
                                        @csrf
                                        <label for="reason" class="form-label">Alasan laporan</label>
                                        <textarea id="reason" name="reason" class="form-input form-textarea"
                                            minlength="10" maxlength="1000" required></textarea>
                                        <button type="submit" class="btn btn-outline">Kirim laporan</button>
                                    </form>
                                </details>
                            @endif
                            @if(auth()->user()->canManageProducts())
                                <a href="{{ route('products.create') }}" class="btn btn-primary">+ Tambah Produk</a>
                            @endif
                        @endauth
                    </div>
                </div>
            </div>

            @if($rateableOrder)
                <section class="related-section">
                    <h2>Beri rating untuk pembelian Anda</h2>
                    <form action="{{ route('ratings.store', $rateableOrder) }}" method="POST" class="product-form">
                        @csrf
                        <div class="form-group">
                            <label for="stars" class="form-label">Rating</label>
                            <select id="stars" name="stars" class="form-input" required>
                                <option value="">Pilih rating</option>
                                @for($stars = 5; $stars >= 1; $stars--)
                                    <option value="{{ $stars }}">{{ $stars }} bintang</option>
                                @endfor
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="comment" class="form-label">Ulasan (opsional)</label>
                            <textarea id="comment" name="comment" class="form-input form-textarea" maxlength="1000"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Kirim rating</button>
                    </form>
                </section>
            @endif

            @if($product->ratings->isNotEmpty())
                <section class="related-section">
                    <h2>Rating dan ulasan</h2>
                    <div class="review-list">
                        @foreach($product->ratings->sortByDesc('created_at') as $rating)
                            <article class="review-card">
                                <strong>{{ $rating->buyer->name }}</strong>
                                <span>⭐ {{ $rating->stars }} / 5</span>
                                @if($rating->comment)
                                    <p>{{ $rating->comment }}</p>
                                @endif
                                <small>{{ $rating->created_at->format('d/m/Y') }}</small>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            <!-- Produk Terkait -->
            @if($related->isNotEmpty())
                <div class="related-section">
                    <h2>Produk Terkait</h2>
                    <div class="product-grid">
                        @foreach($related as $rel)
                            <a href="{{ route('products.show', $rel) }}" class="product-card">
                                <div class="product-img-wrap">
                                    @if($rel->image)
                                        <img src="{{ asset('storage/' . $rel->image) }}" alt="{{ $rel->name }}" class="product-img">
                                    @else
                                        <div class="product-img-placeholder"><span>📦</span></div>
                                    @endif
                                    <span class="product-badge">{{ $rel->category->name }}</span>
                                </div>
                                <div class="product-info">
                                    <h3 class="product-name">{{ $rel->name }}</h3>
                                    <p class="product-price">{{ $rel->price_formatted }}</p>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection