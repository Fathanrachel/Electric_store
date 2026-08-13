<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockOutItem extends Model
{
    use HasFactory;

    protected $fillable = ['stock_out_id', 'product_id', 'qty', 'price', 'subtotal'];

    public function stockOut()
    {
        return $this->belongsTo(StockOut::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    protected static function booted()
    {
        static::created(function ($item) {
            $product = $item->product;
            $product->current_stock -= $item->qty;
            $product->save();
        });
        
        static::deleted(function ($item) {
            $product = $item->product;
            $product->current_stock += $item->qty;
            $product->save();
        });
    }
}
