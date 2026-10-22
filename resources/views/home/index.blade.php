@extends('layouts.app')

@section('title', 'Beranda - KatalogTEFA')

@section('content')
    <div class="section">
        <div class="container">
            <h1 class="page-title">Semua Produk</h1>
            <p class="form-hint">Selamat datang, {{ auth()->user()->name }}. Jelajahi produk komunitas TEFA atau jual produk Anda.</p>

            <div class="hero-actions">
                <a href="{{ route('products.create') }}" class="btn btn-primary">+ Jual Produk</a>
                <a href="{{ route('orders.index') }}" class="btn btn-outline">Pesanan Saya</a>
            </div>

            <form action="{{ route('home') }}" method="GET" class="filter-bar">
                <input type="search" name="search" value="{{ request('search') }}"
                    placeholder="Cari nama produk atau penjual..." class="filter-input">
                <select name="category" class="filter-select">
                    <option value="all">Semua Kategori</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
                <select name="sort" class="filter-select">
                    <option value="latest" {{ request('sort', 'latest') === 'latest' ? 'selected' : '' }}>Terbaru</option>
                    <option value="price_low" {{ request('sort') === 'price_low' ? 'selected' : '' }}>Harga terendah</option>
                    <option value="price_high" {{ request('sort') === 'price_high' ? 'selected' : '' }}>Harga tertinggi</option>
                    <option value="rating" {{ request('sort') === 'rating' ? 'selected' : '' }}>Rating tertinggi</option>
                </select>
                <button type="submit" class="btn btn-primary">Cari</button>
                @if(request('search') || (request('category') && request('category') !== 'all') || request('sort'))
                    <a href="{{ route('home') }}" class="btn btn-outline">Reset</a>
                @endif
            </form>

            @if($products->isEmpty())
                <div class="empty-state">
                    <span class="empty-icon">📦</span>
                    @if(request('search') || (request('category') && request('category') !== 'all'))
                        <p>Produk tidak ditemukan. Coba ubah pencarian atau kategori.</p>
                    @else
                        <p>Belum ada produk di katalog.</p>
                        <a href="{{ route('products.create') }}">Tambahkan produk pertama.</a>
                    @endif
                </div>
            @else
                <p class="result-count">Menampilkan {{ $products->total() }} dari {{ $productCount }} produk</p>
                <div class="product-grid">
                    @foreach($products as $product)
                        <a href="{{ route('products.show', $product) }}" class="product-card">
                            <div class="product-img-wrap">
                                @if($product->image)
                                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="product-img">
                                @else
                                    <div class="product-img-placeholder"><span>📦</span></div>
                                @endif
                                <span class="product-badge">{{ $product->category->name }}</span>
                            </div>
                            <div class="product-info">
                                <h2 class="product-name">{{ $product->name }}</h2>
                                <p class="product-desc">{{ Str::limit($product->description, 60) }}</p>
                                <p class="product-price">{{ $product->price_formatted }}</p>
                                <p class="product-meta">
                                    ⭐ {{ $product->ratings_avg_stars ? number_format($product->ratings_avg_stars, 1) : 'Belum ada rating' }}
                                    · Stok {{ $product->quantity }}
                                </p>
                                <p class="product-meta">Oleh {{ $product->user?->name ?? 'Katalog TEFA' }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
                <div class="pagination-wrap">{{ $products->links() }}</div>
            @endif
        </div>
    </div>
@endsection
