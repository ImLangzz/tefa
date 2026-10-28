@extends('layouts.admin')

@section('title', 'Semua Produk - Panel Admin')
@section('heading', 'Moderasi katalog')

@section('content')
    <section class="admin-panel-card">
        <a href="{{ route('admin.dashboard') }}" class="back-link">← Kembali ke Ringkasan Admin</a>
        <div class="admin-section-heading">
            <div>
                <span class="admin-eyebrow">KONTROL KATALOG</span>
                <h2>Semua Produk</h2>
                <p class="admin-muted">Tinjau produk aktif maupun nonaktif. Produk yang dihapus tidak dapat dipulihkan.</p>
            </div>
            <span class="admin-count-badge">{{ $products->total() }} produk</span>
        </div>

        <form action="{{ route('admin.products.index') }}" method="GET" class="admin-product-filters">
            <input type="search" name="search" value="{{ request('search') }}"
                placeholder="Cari nama produk, deskripsi, atau penjual..." class="form-input">
            <select name="status" class="form-input">
                <option value="">Semua status</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
            </select>
            <button type="submit" class="btn btn-primary">Terapkan</button>
            @if(request('search') || request('status'))
                <a href="{{ route('admin.products.index') }}" class="btn btn-outline">Reset</a>
            @endif
        </form>

        @forelse($products as $product)
            <article class="admin-product-row">
                <div class="admin-product-thumb">
                    @if($product->image)
                        <img src="{{ asset('storage/'.$product->image) }}" alt="">
                    @else
                        <span>📦</span>
                    @endif
                </div>
                <div class="admin-product-info">
                    <div class="admin-product-title">
                        <h3>{{ $product->name }}</h3>
                        <span class="admin-status {{ $product->is_active ? 'is-active' : 'is-inactive' }}">
                            {{ $product->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </div>
                    <p>{{ Str::limit($product->description, 140) }}</p>
                    <div class="admin-product-meta">
                        <span>{{ $product->business_name ?: ($product->user?->name ?? 'Pemilik tidak diketahui') }}</span>
                        @if($product->location_address)
                            <span>📍 {{ $product->location_address }}</span>
                        @endif
                        <span>{{ $product->category?->name ?? 'Tanpa kategori' }}</span>
                        <span>{{ $product->reports_count }} laporan</span>
                    </div>
                </div>
                <form action="{{ route('admin.products.destroy', $product) }}" method="POST"
                    onsubmit="return confirm('Hapus produk {{ addslashes($product->name) }} secara permanen? Pesanan, rating, dan laporan terkait juga akan terhapus.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Hapus produk</button>
                </form>
            </article>
        @empty
            <div class="admin-empty-state">
                <span>⌕</span>
                <p>Tidak ada produk yang cocok dengan pencarian ini.</p>
            </div>
        @endforelse

        @if($products->hasPages())
            <div class="pagination-wrap">{{ $products->links() }}</div>
        @endif
    </section>
@endsection
