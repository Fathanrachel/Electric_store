<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockInItem extends Model
{
    use HasFactory;

    protected $fillable = ['stock_in_id', 'product_id', 'qty', 'price', 'subtotal'];

    public function stockIn()
    {
        return $this->belongsTo(StockIn::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    protected static function booted()
    {
        static::created(function ($item) {
            $product = $item->product;
            $product->current_stock += $item->qty;
            $product->purchase_price = $item->price; // Update harga beli ke yg terbaru
            $product->save();
        });
        
        static::deleted(function ($item) {
            $product = $item->product;
            $product->current_stock -= $item->qty;
            $product->save();
        });
    }
}
