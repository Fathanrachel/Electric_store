<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Product;

class StockController extends Controller
{
    public function index()
    {
        $products = Product::with('category')->get();
        return view('stock.index', compact('products'));
    }
}
