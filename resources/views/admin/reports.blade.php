@extends('layouts.admin')

@section('title', 'Moderasi Produk - KatalogTEFA')

@section('content')
    <div class="section">
        <div class="container">
            <a href="{{ route('admin.dashboard') }}" class="back-link">← Kembali ke Ringkasan Admin</a>
            <h1 class="page-title">Laporan Produk</h1>
            @forelse($reports as $report)
                <article class="order-card">
                    <div>
                        <h2>{{ $report->product->name }}</h2>
                        <p>Penjual: {{ $report->product->user?->name ?? 'Pemilik tidak diketahui' }}</p>
                        <p>Pelapor: {{ $report->reporter->name }}</p>
                        <p>Alasan: {{ $report->reason }}</p>
                        <p>Tentang produk: {{ $report->product->description }}</p>
                        <p>Kategori: {{ $report->product->category->name ?? 'Tidak ada kategori' }}</p>
                    </div>
                    <div class="moderation-actions">
                        <form action="{{ route('admin.reports.deactivate', $report) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-outline">Nonaktifkan barang</button>
                        </form>
                        @if($report->product->user && !$report->product->user->isAdmin())
                            <form action="{{ route('admin.reports.blacklist', $report) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-danger">Blacklist penjual</button>
                            </form>
                        @endif
                        <form action="{{ route('admin.reports.dismiss', $report) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-outline">Abaikan laporan</button>
                        </form>
                    </div>
                </article>
            @empty
                <div class="empty-state"><p>Tidak ada laporan yang menunggu peninjauan.</p></div>
            @endforelse

            <section class="related-section">
                <h2>Akun dalam blacklist</h2>
                @forelse($blacklistedUsers as $user)
                    <article class="order-card">
                        <div>
                            <strong>{{ $user->name }}</strong>
                            <p>{{ $user->email }}</p>
                            <p>Alasan: {{ $user->blacklist_reason }}</p>
                        </div>
                        <form action="{{ route('admin.blacklist.restore', $user) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-outline">Pulihkan akun</button>
                        </form>
                    </article>
                @empty
                    <p>Tidak ada akun yang diblokir.</p>
                @endforelse
            </section>

            <section class="related-section">
                <h2>Produk nonaktif</h2>
                @forelse($inactiveProducts as $product)
                    <article class="order-card">
                        <div>
                            <strong>{{ $product->name }}</strong>
                            <p>Penjual: {{ $product->user?->name ?? 'Pemilik tidak diketahui' }}</p>
                        </div>
                        @if(!$product->user?->is_blacklisted)
                            <form action="{{ route('admin.products.activate', $product) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-primary">Aktifkan kembali</button>
                            </form>
                        @else
                            <span>Penjual masih dalam blacklist</span>
                        @endif
                    </article>
                @empty
                    <p>Tidak ada produk nonaktif.</p>
                @endforelse
            </section>
        </div>
    </div>
@endsection
