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
                    <p class="product-meta">
                        ⭐ {{ $product->ratings->isNotEmpty() ? number_format($product->ratings->avg('stars'), 1).' / 5' : 'Belum ada rating' }}
                        ({{ $product->ratings->count() }} rating)
                    </p>
                    <p class="product-meta">Usaha: {{ $product->business_name ?: ($product->user?->name ?? 'Contoh katalog TEFA') }}</p>
                    <p class="product-meta">Penjual: {{ $product->user?->name ?? 'Pemilik tidak diketahui' }}</p>
                    <div class="detail-divider"></div>
                    <h3 class="detail-about-title">Tentang Produk</h3>
                    <p class="detail-desc">{{ $product->description }}</p>
                    @if($product->location_address)
                        <section class="product-location-detail">
                            <h3 class="detail-about-title">Lokasi Tempat Jualan</h3>
                            <p class="product-address">📍 {{ $product->location_address }}</p>
                            <a class="btn btn-outline product-map-action" target="_blank" rel="noopener noreferrer"
                                href="https://www.google.com/maps/dir/?api=1&amp;destination={{ urlencode($product->location_address) }}">
                                Lihat rute di Google Maps
                            </a>
                        </section>
                    @endif
                    <div class="detail-actions">
                        <a href="{{ route('products.index') }}" class="btn btn-outline">← Katalog</a>
                        @auth
                            @if(auth()->id() === $product->user_id)
                                <span class="form-hint">Ini adalah promosi usaha Anda.</span>
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
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection