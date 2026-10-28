@extends('layouts.app')

@section('title', 'Beranda - KatalogTEFA')

@section('content')
    <div class="section">
        <div class="container">
            <h1 class="page-title">Promosi Usaha TEFA</h1>
            <p class="form-hint">Selamat datang, {{ auth()->user()->name }}. Temukan produk dan lokasi usaha komunitas TEFA, atau promosikan usaha Anda.</p>

            <div class="hero-actions">
                <a href="{{ route('products.create') }}" class="btn btn-primary">Promosikan Usaha</a>
                <a href="{{ route('products.index') }}" class="btn btn-outline">Jelajahi Katalog</a>
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
                <button type="submit" class="btn btn-primary">Cari</button>
                @if(request('search') || (request('category') && request('category') !== 'all'))
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
                        <article class="product-card">
                            <a class="product-img-wrap" href="{{ route('products.show', $product) }}" aria-label="Lihat {{ $product->name }}">
                                @if($product->image)
                                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="product-img">
                                @else
                                    <div class="product-img-placeholder"><span>📦</span></div>
                                @endif
                            </a>
                            <div class="product-info">
                                <h2 class="product-name">
                                    <a class="product-detail-link" href="{{ route('products.show', $product) }}">{{ $product->name }}</a>
                                </h2>
                                <p class="product-meta"><strong>Nama usaha:</strong> {{ $product->business_name ?: ($product->user?->name ?? 'Katalog TEFA') }}</p>
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
                <div class="pagination-wrap">{{ $products->links() }}</div>
            @endif
        </div>
    </div>
@endsection
