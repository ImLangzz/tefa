@extends('layouts.app')

@section('title', 'Daftar - KatalogTEFA')

@section('content')
    <div class="section">
        <div class="container form-container">
            <h1 class="page-title">Daftar Akun</h1>
            <p class="form-hint">Satu akun bisa digunakan untuk membeli sekaligus menjual barang.</p>

            @if($errors->any())
                <div class="alert-error" role="alert">
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('register.store') }}" method="POST" class="product-form" data-register-form>
                @csrf
                <div class="form-group">
                    <label for="name" class="form-label">Nama</label>
                    <input id="name" type="text" name="name" value="{{ old('name') }}"
                        class="form-input" autocomplete="name" required>
                </div>
                <div class="form-group">
                    <label for="email" class="form-label">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}"
                        class="form-input" autocomplete="email" required>
                </div>
                <div class="form-group">
                    <label for="password" class="form-label">Kata Sandi (minimal 8 karakter)</label>
                    <div class="password-field">
                        <input id="password" type="password" name="password"
                            class="form-input" autocomplete="new-password" minlength="8" required>
                        <button type="button" class="password-toggle" data-toggle-password="password"
                            aria-label="Tampilkan kata sandi" aria-pressed="false">Tampilkan</button>
                    </div>
                    <div class="password-strength" aria-live="polite">
                        <div class="password-strength-track"><span data-password-meter></span></div>
                        <span data-password-label>Gunakan minimal 8 karakter.</span>
                    </div>
                </div>
                <div class="form-group">
                    <label for="password_confirmation" class="form-label">Ulangi Kata Sandi</label>
                    <div class="password-field">
                        <input id="password_confirmation" type="password" name="password_confirmation"
                            class="form-input" autocomplete="new-password" required>
                        <button type="button" class="password-toggle" data-toggle-password="password_confirmation"
                            aria-label="Tampilkan ulang kata sandi" aria-pressed="false">Tampilkan</button>
                    </div>
                    <span class="password-match" data-password-match aria-live="polite"></span>
                </div>
                <button type="submit" class="btn btn-primary">Daftar</button>
                <p class="form-hint">Sudah punya akun? <a href="{{ route('login') }}">Masuk di sini</a>.</p>
            </form>
        </div>
    </div>
@endsection
