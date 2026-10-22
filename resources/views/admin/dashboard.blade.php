@extends('layouts.admin')

@section('title', 'Panel Admin - KatalogTEFA')
@section('heading', 'Ringkasan website')

@section('content')
    <div class="admin-welcome">
        <div>
            <h2>Selamat datang, {{ auth()->user()->name }}</h2>
            <p>Kelola keamanan dan laporan katalog dari panel ini. Akun admin tidak digunakan untuk berjualan.</p>
        </div>
        <a href="{{ route('admin.reports.index') }}" class="btn btn-primary">
            Tinjau laporan ({{ $pendingReportCount }})
        </a>
    </div>

    <section class="admin-stats">
        <article class="admin-stat-card">
            <span>Menunggu ditinjau</span>
            <strong>{{ $pendingReportCount }}</strong>
            <a href="{{ route('admin.reports.index') }}">Lihat laporan →</a>
        </article>
        <article class="admin-stat-card">
            <span>Produk aktif</span>
            <strong>{{ $activeProductCount }}</strong>
            <a href="{{ route('admin.products.index', ['status' => 'active']) }}">Kelola katalog →</a>
        </article>
        <article class="admin-stat-card">
            <span>Produk nonaktif</span>
            <strong>{{ $inactiveProductCount }}</strong>
            <a href="{{ route('admin.products.index', ['status' => 'inactive']) }}">Tinjau produk →</a>
        </article>
        <article class="admin-stat-card">
            <span>Akun blacklist</span>
            <strong>{{ $blacklistedUserCount }}</strong>
            <a href="{{ route('admin.reports.index') }}">Kelola akun →</a>
        </article>
    </section>

    <section class="admin-panel-card">
        <div class="admin-section-heading">
            <div>
                <span class="admin-eyebrow">MODERASI</span>
                <h2>Laporan terbaru</h2>
            </div>
            <a href="{{ route('admin.reports.index') }}" class="link-more">Semua laporan →</a>
        </div>

        @forelse($recentReports as $report)
            <article class="admin-report-row">
                <div>
                    <strong>{{ $report->product->name }}</strong>
                    <p>{{ $report->reason }}</p>
                    <small>Dilaporkan oleh {{ $report->reporter->name }} · {{ $report->created_at->format('d/m/Y H:i') }}</small>
                </div>
                <a href="{{ route('admin.reports.index') }}" class="btn btn-outline">Tinjau</a>
            </article>
        @empty
            <div class="admin-empty-state">
                <span>✓</span>
                <p>Tidak ada laporan yang menunggu. Website dalam keadaan terkendali.</p>
            </div>
        @endforelse
    </section>
@endsection
