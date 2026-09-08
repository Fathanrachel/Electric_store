<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Product;

class StockController extends Controller
{
    public function index()
    {
        // Tampilkan semua produk (aktif & nonaktif) agar bisa dikelola
        $products = Product::with('category')->orderBy('is_active', 'desc')->orderBy('name')->get();
        return view('stock.index', compact('products'));
    }

    public function toggleActive(Product $product)
    {
        $product->update(['is_active' => !$product->is_active]);

        $status = $product->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->route('stock.index')
            ->with('success', "Produk \"{$product->name}\" berhasil {$status}.");
    }
}
