<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    // Daftar katalog produk
    public function index(Request $request)
    {
        $categories = Category::all();
        $query = Product::with(['category', 'user'])
            ->withAvg('ratings', 'stars')
            ->where('is_active', true)
            ->where(function ($products) {
                $products->whereNull('user_id')
                    ->orWhereHas('user', fn ($users) => $users->where('is_blacklisted', false));
            });

        // Filter pencarian
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($products) use ($search) {
                $products->where('name', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%');
            });
        }

        // Filter kategori
        if ($request->filled('category') && $request->category !== 'all') {
            $query->where('category_id', $request->category);
        }

        $products = $query->latest()->paginate(12)->withQueryString();

        return view('products.index', compact('products', 'categories'));
    }

    // Detail produk
    public function show(Product $product)
    {
        abort_unless(
            $product->is_active && (! $product->user || ! $product->user->is_blacklisted),
            404
        );

        $product->load(['category', 'user', 'ratings.buyer']);
        $related = Product::with('category')
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('is_active', true)
            ->where(function ($products) {
                $products->whereNull('user_id')
                    ->orWhereHas('user', fn ($users) => $users->where('is_blacklisted', false));
            })
            ->take(4)
            ->get();

        return view('products.show', compact('product', 'related'));
    }

    // Form tambah produk
    public function create()
    {
        abort_if(request()->user()->is_blacklisted, 403, 'Akun Anda diblokir dari aktivitas jual beli.');

        $categories = Category::all();

        return view('products.create', compact('categories'));
    }

    // Simpan produk baru
    public function store(Request $request)
    {
        abort_if($request->user()->is_blacklisted, 403, 'Akun Anda diblokir dari aktivitas jual beli.');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:5000',
            'business_name' => 'required|string|max:150',
            'location_address' => 'required|string|max:500',
            'category_id' => 'required|exists:categories,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ], [
            'name.required' => 'Nama produk wajib diisi.',
            'description.required' => 'Tentang produk wajib diisi.',
            'business_name.required' => 'Nama usaha wajib diisi.',
            'location_address.required' => 'Alamat tempat jualan wajib diisi.',
            'category_id.required' => 'Kategori wajib dipilih.',
            'image.image' => 'File harus berupa gambar.',
            'image.max' => 'Ukuran gambar maksimal 5MB.',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        }
        unset($validated['image']);

        $request->user()->products()->create([
            ...$validated,
            'image' => $imagePath,
        ]);

        return redirect()->route('products.index')
            ->with('success', 'Produk berhasil ditambahkan!');
    }
}
