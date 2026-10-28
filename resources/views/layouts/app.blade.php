<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'KatalogTEFA')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>

<body>

    <!-- Navbar -->
    <nav class="navbar">
        <div class="container nav-container">
            <a href="{{ route('home') }}" class="nav-brand">
                <span class="brand-icon">🏫</span>
                <span>Katalog<strong>TEFA</strong></span>
            </a>
            <ul class="nav-links">
                <li><a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">Beranda</a>
                </li>
                <li><a href="{{ route('products.index') }}"
                        class="{{ request()->routeIs('products.index') ? 'active' : '' }}">Katalog</a></li>
                @auth
                    @if(auth()->user()->isMember())
                        <li>
                            <a href="{{ route('products.create') }}"
                                class="btn-nav {{ request()->routeIs('products.create') ? 'active' : '' }}">
                                Promosikan Usaha
                            </a>
                        </li>
                    @endif
                    @if(auth()->user()->isAdmin())
                        <li><a href="{{ route('admin.reports.index') }}">Moderasi</a></li>
                    @endif
                    <li><span>Hai, {{ auth()->user()->name }}</span></li>
                    <li>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button class="btn-nav" type="submit">Keluar</button>
                        </form>
                    </li>
                @else
                    <li><a href="{{ route('login') }}">Masuk</a></li>
                    <li><a href="{{ route('register') }}">Daftar</a></li>
                @endauth
            </ul>
            <button class="hamburger" id="hamburger">&#9776;</button>
        </div>
        <div class="mobile-menu" id="mobileMenu">
            <a href="{{ route('home') }}">Beranda</a>
            <a href="{{ route('products.index') }}">Katalog</a>
            @auth
                @if(auth()->user()->isMember())
                    <a href="{{ route('products.create') }}">Promosikan Usaha</a>
                @endif
                @if(auth()->user()->isAdmin())
                    <a href="{{ route('admin.reports.index') }}">Moderasi</a>
                @endif
                <span>Hai, {{ auth()->user()->name }}</span>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit">Keluar</button>
                </form>
            @else
                <a href="{{ route('login') }}">Masuk</a>
                <a href="{{ route('register') }}">Daftar</a>
            @endauth
        </div>
    </nav>

    <!-- Flash Message -->
    @if(session('success'))
        <div class="flash-success">
            <span>✅ {{ session('success') }}</span>
            <button onclick="this.parentElement.remove()">✕</button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert-error">{{ session('error') }}</div>
    @endif

    <!-- Content -->
    <main>
        @yield('content')
    </main>

    <script>
        const ham = document.getElementById('hamburger');
        const menu = document.getElementById('mobileMenu');
        ham.addEventListener('click', () => menu.classList.toggle('open'));

        document.querySelectorAll('[data-toggle-password]').forEach((toggle) => {
            toggle.addEventListener('click', () => {
                const input = document.getElementById(toggle.dataset.togglePassword);
                const visible = input.type === 'password';
                input.type = visible ? 'text' : 'password';
                toggle.textContent = visible ? 'Sembunyikan' : 'Tampilkan';
                toggle.setAttribute('aria-pressed', String(visible));
                toggle.setAttribute('aria-label', `${visible ? 'Sembunyikan' : 'Tampilkan'} kata sandi`);
            });
        });

        const registerForm = document.querySelector('[data-register-form]');
        if (registerForm) {
            const password = registerForm.querySelector('#password');
            const confirmation = registerForm.querySelector('#password_confirmation');
            const meter = registerForm.querySelector('[data-password-meter]');
            const strengthLabel = registerForm.querySelector('[data-password-label]');
            const matchLabel = registerForm.querySelector('[data-password-match]');

            const updatePasswordFeedback = () => {
                const value = password.value;
                const checks = [
                    value.length >= 8,
                    /[a-z]/.test(value) && /[A-Z]/.test(value),
                    /\d/.test(value),
                    /[^A-Za-z0-9]/.test(value),
                ];
                const score = value ? checks.filter(Boolean).length : 0;
                meter.style.width = `${score * 25}%`;
                meter.dataset.strength = score < 2 ? 'weak' : score < 4 ? 'medium' : 'strong';
                strengthLabel.textContent = !value
                    ? 'Gunakan minimal 8 karakter.'
                    : score < 2 ? 'Kata sandi mudah ditebak.'
                        : score < 4 ? 'Kekuatan kata sandi cukup.'
                            : 'Kata sandi kuat.';

                const matches = confirmation.value && confirmation.value === value;
                confirmation.setCustomValidity(confirmation.value && !matches ? 'Kata sandi belum sama.' : '');
                matchLabel.textContent = !confirmation.value ? '' : matches ? '✓ Kata sandi cocok.' : 'Kata sandi belum cocok.';
                matchLabel.dataset.matches = matches ? 'true' : 'false';
            };

            password.addEventListener('input', updatePasswordFeedback);
            confirmation.addEventListener('input', updatePasswordFeedback);
        }

    </script>

</body>

</html>