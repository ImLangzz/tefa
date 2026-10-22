@extends('layouts.app')

@section('title', 'Masuk - KatalogTEFA')

@section('content')
    <div class="section">
        <div class="container form-container">
            <h1 class="page-title">Masuk</h1>
            <p class="form-hint">Masukkan email dan kata sandi akun KatalogTEFA Anda.</p>

            @if($errors->any())
                <div class="alert-error" role="alert">
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('login.store') }}" method="POST" class="product-form">
                @csrf
                <div class="form-group">
                    <label for="email" class="form-label">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}"
                        class="form-input" autocomplete="email" required autofocus>
                </div>
                <div class="form-group">
                    <label for="password" class="form-label">Kata Sandi</label>
                    <div class="password-field">
                        <input id="password" type="password" name="password"
                            class="form-input" autocomplete="current-password" required>
                        <button type="button" class="password-toggle" data-toggle-password="password"
                            aria-label="Tampilkan kata sandi" aria-pressed="false">Tampilkan</button>
                    </div>
                </div>
                <div class="form-group">
                    <label><input type="checkbox" name="remember" value="1"> Ingat saya</label>
                </div>
                <button type="submit" class="btn btn-primary">Masuk</button>
                <p class="form-hint">Belum punya akun? <a href="{{ route('register') }}">Daftar di sini</a>.</p>
            </form>
        </div>
    </div>
@endsection
