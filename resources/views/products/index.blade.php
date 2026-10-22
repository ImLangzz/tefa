@extends('layouts.app')

@section('title', 'Katalog Produk - KatalogTEFA')

@section('content')
    <div class="section">
        <div class="container">
            <h1 class="page-title">Katalog Produk</h1>

            <!-- Filter & Search -->
            <form action="{{ route('products.index') }}" method="GET" class="filter-bar">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari produk..."
                    class="filter-input">
                <select name="category" class="filter-select">
                    <option value="all">Semua Kategori</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-primary">Cari</button>
                @if(request('search') || (request('category') && request('category') !== 'all'))
                    <a href="{{ route('products.index') }}" class="btn btn-outline">Reset</a>
                @endif
            </form>

            <!-- Product Grid -->
            @if($products->isEmpty())
                <div class="empty-state">
                    <span class="empty-icon">🔍</span>
                    <p>Produk tidak ditemukan.</p>
                    @if(request('search') || request('category'))
                        <a href="{{ route('products.index') }}" class="btn btn-outline" style="margin-top:12px">Tampilkan Semua</a>
                    @elseif(auth()->check() && auth()->user()->isMember())
                        <a href="{{ route('products.create') }}" class="btn btn-primary" style="margin-top:12px">+ Tambah Produk</a>
                    @endif
                </div>
            @else
                <p class="result-count">Menampilkan {{ $products->total() }} produk</p>
                <div class="product-grid">
                    @foreach($products as $product)
                        <a href="{{ route('products.show', $product) }}" class="product-card">
                            <div class="product-img-wrap">
                                @if($product->image)
                                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="product-img">
                                @else
                                    <div class="product-img-placeholder">
                                        <span>📦</span>
                                    </div>
                                @endif
                                <span class="product-badge">{{ $product->category->name }}</span>
                            </div>
                            <div class="product-info">
                                <h3 class="product-name">{{ $product->name }}</h3>
                                <p class="product-desc">{{ Str::limit($product->description, 60) }}</p>
                                <p class="product-price">{{ $product->price_formatted }}</p>
                                <p class="product-meta">
                                    ⭐ {{ $product->ratings_avg_stars ? number_format($product->ratings_avg_stars, 1) : 'Belum ada rating' }}
                                    · {{ $product->quantity > 0 ? 'Stok '.$product->quantity : 'Stok habis / belum diatur' }}
                                </p>
                                <p class="product-meta">Oleh {{ $product->user?->name ?? 'Contoh katalog TEFA' }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="pagination-wrap">
                    {{ $products->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection