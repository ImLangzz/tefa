<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        if (auth()->user()->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        $query = Product::with(['category', 'user'])
            ->withAvg('ratings', 'stars')
            ->where('is_active', true)
            ->where(function ($products) {
                $products->whereNull('user_id')
                    ->orWhereHas('user', fn ($users) => $users->where('is_blacklisted', false));
            });

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(function ($products) use ($search) {
                $products->where('name', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%')
                    ->orWhereHas('user', fn ($users) => $users->where('name', 'like', '%'.$search.'%'));
            });
        }

        if ($request->filled('category') && $request->category !== 'all') {
            $query->where('category_id', $request->category);
        }

        match ($request->input('sort')) {
            'rating' => $query->orderByDesc('ratings_avg_stars'),
            default => $query->latest(),
        };

        $products = $query->paginate(12)->withQueryString();

        return view('home.index', [
            'products' => $products,
            'productCount' => Product::where('is_active', true)->count(),
            'categoryCount' => Category::count(),
            'categories' => Category::orderBy('name')->get(),
        ]);
    }
}
