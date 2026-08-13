<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Product;
use App\Models\Supplier;
use App\Models\StockIn;
use App\Models\StockInItem;
use App\Models\StockOut;
use App\Models\StockOutItem;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    public function stockIn()
    {
        $products = Product::all();
        return view('transactions.in', compact('products'));
    }

    public function storeStockIn(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.product_name' => 'required|string',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {
            $total = 0;
            $transactionNo = 'IN-' . date('YmdHis');
            
            $stockIn = StockIn::create([
                'transaction_no' => $transactionNo,
                'date' => $request->date,
                'note' => $request->note,
                'total' => 0, // will update later
            ]);

            foreach ($request->items as $item) {
                // Find or create product by name
                $product = Product::firstOrCreate(
                    ['name' => $item['product_name']],
                    [
                        'unit' => 'pcs',
                        'purchase_price' => $item['price'],
                        'selling_price' => 0,
                        'min_stock' => 5,
                        'current_stock' => 0,
                    ]
                );

                // Auto-update purchase price based on latest transaction
                if ($item['price'] > 0 && $product->purchase_price != $item['price']) {
                    $product->update(['purchase_price' => $item['price']]);
                }

                $subtotal = $item['qty'] * $item['price'];
                $total += $subtotal;
                
                StockInItem::create([
                    'stock_in_id' => $stockIn->id,
                    'product_id' => $product->id,
                    'qty' => $item['qty'],
                    'price' => $item['price'],
                    'subtotal' => $subtotal,
                ]);
            }
            
            $stockIn->update(['total' => $total]);
        });

        return redirect()->route('dashboard')->with('success', 'Transaksi Barang Masuk berhasil disimpan.');
    }

    public function stockOut()
    {
        $products = Product::where('current_stock', '>', 0)->get();
        return view('transactions.out', compact('products'));
    }

    public function storeStockOut(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.product_name' => 'required|string',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {
            $total = 0;
            $transactionNo = 'OUT-' . date('YmdHis');
            
            $stockOut = StockOut::create([
                'transaction_no' => $transactionNo,
                'date' => $request->date,
                'customer_name' => $request->customer_name,
                'note' => $request->note,
                'total' => 0,
            ]);

            foreach ($request->items as $item) {
                $product = Product::where('name', $item['product_name'])->first();
                if (!$product) {
                    throw new \Exception('Barang tidak ditemukan: ' . $item['product_name']);
                }
                if ($product->current_stock < $item['qty']) {
                    throw new \Exception('Stok tidak cukup untuk barang: ' . $product->name);
                }

                // Auto-update selling price based on latest transaction
                if ($item['price'] > 0 && $product->selling_price != $item['price']) {
                    $product->update(['selling_price' => $item['price']]);
                }

                $subtotal = $item['qty'] * $item['price'];
                $total += $subtotal;
                
                StockOutItem::create([
                    'stock_out_id' => $stockOut->id,
                    'product_id' => $product->id,
                    'qty' => $item['qty'],
                    'price' => $item['price'],
                    'subtotal' => $subtotal,
                ]);
            }
            
            $stockOut->update(['total' => $total]);
        });

        return redirect()->route('dashboard')->with('success', 'Transaksi Barang Keluar berhasil disimpan.');
    }
}
