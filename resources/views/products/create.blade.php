@extends('layouts.app')

@section('title', 'Tambah Produk - KatalogTEFA')

@section('content')
    <div class="section">
        <div class="container form-container">
            <a href="{{ route('products.index') }}" class="back-link">← Kembali ke Katalog</a>
            <h1 class="page-title">Tambah Produk Baru</h1>

            @if($errors->any())
                <div class="alert-error">
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data" class="product-form">
                @csrf

                <!-- Nama Produk -->
                <div class="form-group">
                    <label for="name" class="form-label">Nama Produk <span class="required">*</span></label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}"
                        placeholder="Contoh: Kue Brownies Coklat"
                        class="form-input {{ $errors->has('name') ? 'input-error' : '' }}" required>
                    @error('name')
                        <p class="error-msg">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Kategori -->
                <div class="form-group">
                    <label for="category_id" class="form-label">Kategori <span class="required">*</span></label>
                    <select id="category_id" name="category_id"
                        class="form-input {{ $errors->has('category_id') ? 'input-error' : '' }}" required>
                        <option value="">-- Pilih Kategori --</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <p class="error-msg">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Tentang Produk -->
                <div class="form-group">
                    <label for="description" class="form-label">Tentang Produk <span class="required">*</span></label>
                    <textarea id="description" name="description" rows="5"
                        placeholder="Jelaskan produk ini secara detail: bahan, keunggulan, cara pemakaian, dll."
                        class="form-input form-textarea {{ $errors->has('description') ? 'input-error' : '' }}"
                        required>{{ old('description') }}</textarea>
                    @error('description')
                        <p class="error-msg">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Harga -->
                <div class="form-group">
                    <label for="price" class="form-label">Harga (Rp) <span class="required">*</span></label>
                    <div class="input-prefix-wrap">
                        <span class="input-prefix">Rp</span>
                        <input type="number" id="price" name="price" value="{{ old('price') }}" placeholder="Contoh: 25000"
                            min="0" class="form-input input-with-prefix {{ $errors->has('price') ? 'input-error' : '' }}"
                            required>
                    </div>
                    @error('price')
                        <p class="error-msg">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="quantity" class="form-label">Jumlah Stok <span class="required">*</span></label>
                    <input type="number" id="quantity" name="quantity" value="{{ old('quantity', 1) }}"
                        min="1" class="form-input" required>
                    @error('quantity')
                        <p class="error-msg">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Gambar Produk -->
                <div class="form-group">
                    <label class="form-label">Gambar Produk</label>
                    <p class="form-hint">Ambil dari galeri atau langsung dari kamera (JPG, PNG, WEBP — maks. 5MB)</p>

                    <!-- Area Upload -->
                    <div class="image-upload-area" id="uploadArea" onclick="document.getElementById('image').click()">
                        <div class="upload-placeholder" id="uploadPlaceholder">
                            <span class="upload-icon">📷</span>
                            <p class="upload-text">Ketuk untuk memilih foto</p>
                            <p class="upload-hint">Galeri atau Kamera</p>
                        </div>
                        <img id="imagePreview" class="image-preview hidden" alt="Preview gambar">
                    </div>

                    <!-- Input file: capture memungkinkan pilih galeri ATAU kamera di mobile -->
                    <input type="file" id="image" name="image" accept="image/*" class="hidden"
                        onchange="previewImage(event)">

                    <!-- Tombol Khusus Mobile -->
                    <div class="upload-btn-group">
                        <button type="button" class="btn-upload" onclick="openGallery()">
                            🖼️ Pilih dari Galeri
                        </button>
                        <button type="button" class="btn-upload" onclick="openCamera()">
                            📸 Ambil Foto dari Kamera
                        </button>
                    </div>

                    @error('image')
                        <p class="error-msg">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Tombol Submit -->
                <div class="form-actions">
                    <a href="{{ route('products.index') }}" class="btn btn-outline">Batal</a>
                    <button type="submit" class="btn btn-primary">💾 Simpan Produk</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Preview gambar setelah dipilih
        function previewImage(event) {
            const file = event.target.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = function (e) {
                const preview = document.getElementById('imagePreview');
                const placeholder = document.getElementById('uploadPlaceholder');
                preview.src = e.target.result;
                preview.classList.remove('hidden');
                placeholder.classList.add('hidden');
            };
            reader.readAsDataURL(file);
        }

        // Buka galeri
        function openGallery() {
            const input = document.getElementById('image');
            input.removeAttribute('capture');
            input.click();
        }

        // Buka kamera langsung
        function openCamera() {
            const input = document.getElementById('image');
            input.setAttribute('capture', 'environment');
            input.click();
        }
    </script>
@endsection