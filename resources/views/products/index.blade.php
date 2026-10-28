@extends('layouts.app')

@section('title', 'Katalog Produk - KatalogTEFA')

@section('content')
    <div class="section">
        <div class="container">
            <a href="{{ route('home') }}" class="back-link">← Kembali ke Beranda</a>
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
                        <article class="product-card">
                            <a class="product-img-wrap" href="{{ route('products.show', $product) }}" aria-label="Lihat {{ $product->name }}">
                                @if($product->image)
                                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="product-img">
                                @else
                                    <div class="product-img-placeholder">
                                        <span>📦</span>
                                    </div>
                                @endif
                            </a>
                            <div class="product-info">
                                <h3 class="product-name">
                                    <a class="product-detail-link" href="{{ route('products.show', $product) }}">{{ $product->name }}</a>
                                </h3>
                                <p class="product-meta"><strong>Nama usaha:</strong> {{ $product->business_name ?: ($product->user?->name ?? 'Contoh katalog TEFA') }}</p>
                                <p class="product-meta"><strong>Bidang:</strong> {{ $product->category->name }}</p>
                                <a class="product-detail-link" href="{{ route('products.show', $product) }}">Lihat promosi</a>
                                @if($product->location_address)
                                    <div class="product-address">
                                        <strong>Alamat:</strong>
                                        <p>📍 {{ $product->location_address }}</p>
                                    </div>
                                    <a class="btn btn-outline product-map-action" target="_blank" rel="noopener noreferrer"
                                        href="https://www.google.com/maps/dir/?api=1&amp;destination={{ urlencode($product->location_address) }}">
                                        Lihat rute di Google Maps
                                    </a>
                                @endif
                            </div>
                        </article>
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