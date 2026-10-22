<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductReport;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ModerationController extends Controller
{
    public function dashboard(): View
    {
        return view('admin.dashboard', [
            'pendingReportCount' => ProductReport::where('status', 'pending')->count(),
            'blacklistedUserCount' => User::where('is_blacklisted', true)->count(),
            'inactiveProductCount' => Product::where('is_active', false)->count(),
            'activeProductCount' => Product::where('is_active', true)->count(),
            'recentReports' => ProductReport::with(['product', 'reporter'])
                ->where('status', 'pending')
                ->latest()
                ->take(5)
                ->get(),
        ]);
    }

    public function index(): View
    {
        $reports = ProductReport::with(['product.user', 'product.category', 'reporter'])
            ->where('status', 'pending')
            ->latest()
            ->get();
        $blacklistedUsers = User::where('is_blacklisted', true)->latest()->get();
        $inactiveProducts = Product::with('user')->where('is_active', false)->latest()->get();

        return view('admin.reports', compact('reports', 'blacklistedUsers', 'inactiveProducts'));
    }

    public function products(Request $request): View
    {
        $query = Product::with(['user', 'category'])
            ->withCount('reports')
            ->latest();

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(function ($products) use ($search) {
                $products->where('name', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%')
                    ->orWhereHas('user', fn ($users) => $users->where('name', 'like', '%'.$search.'%'));
            });
        }

        if ($request->input('status') === 'active') {
            $query->where('is_active', true);
        } elseif ($request->input('status') === 'inactive') {
            $query->where('is_active', false);
        }

        return view('admin.products', [
            'products' => $query->paginate(15)->withQueryString(),
        ]);
    }

    public function report(Request $request, Product $product): RedirectResponse
    {
        abort_if($request->user()->is_blacklisted, 403);
        abort_unless($product->user_id !== $request->user()->id, 403, 'Anda tidak dapat melaporkan produk sendiri.');

        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:10', 'max:1000'],
        ]);

        $report = ProductReport::firstOrCreate(
            ['product_id' => $product->id, 'reporter_id' => $request->user()->id],
            ['reason' => $validated['reason']]
        );

        if (! $report->wasRecentlyCreated) {
            return back()->withErrors(['reason' => 'Anda sudah pernah melaporkan produk ini.']);
        }

        return back()->with('success', 'Laporan terkirim dan akan ditinjau oleh admin.');
    }

    public function deactivate(ProductReport $report): RedirectResponse
    {
        $report->product()->update(['is_active' => false]);
        $report->update(['status' => 'reviewed']);

        return back()->with('success', 'Produk dinonaktifkan dan laporannya ditinjau.');
    }

    public function blacklist(ProductReport $report): RedirectResponse
    {
        $seller = User::query()->findOrFail($report->product->user_id);

        abort_if($seller->isAdmin(), 422, 'Akun admin tidak dapat dimasukkan ke blacklist.');

        $seller->update([
            'is_blacklisted' => true,
            'blacklist_reason' => 'Diblokir setelah peninjauan laporan produk #'.$report->product_id.'.',
        ]);
        $seller->products()->update(['is_active' => false]);
        ProductReport::whereIn('product_id', $seller->products()->select('id'))
            ->where('status', 'pending')
            ->update(['status' => 'reviewed']);
        $report->update(['status' => 'reviewed']);

        return back()->with('success', 'Akun penjual diblokir dan seluruh produknya disembunyikan.');
    }

    public function dismiss(ProductReport $report): RedirectResponse
    {
        $report->update(['status' => 'dismissed']);

        return back()->with('success', 'Laporan ditandai selesai tanpa tindakan.');
    }

    public function restore(User $user): RedirectResponse
    {
        abort_if($user->isAdmin(), 422, 'Akun admin tidak dapat dipulihkan melalui blacklist penjual.');

        $user->update([
            'is_blacklisted' => false,
            'blacklist_reason' => null,
        ]);

        return back()->with('success', 'Akun penjual dipulihkan. Produk yang sebelumnya dinonaktifkan perlu ditinjau dan diaktifkan kembali oleh admin.');
    }

    public function activate(Product $product): RedirectResponse
    {
        abort_if($product->user?->is_blacklisted, 422, 'Pulihkan akun penjual sebelum mengaktifkan produknya.');

        $product->update(['is_active' => true]);

        return back()->with('success', 'Produk diaktifkan kembali.');
    }

    public function deleteProduct(Product $product): RedirectResponse
    {
        $image = $product->image;
        $product->delete();

        if ($image) {
            Storage::disk('public')->delete($image);
        }

        return back()->with('success', 'Produk dan data terkaitnya berhasil dihapus dari katalog.');
    }
}
