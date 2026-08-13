<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockOut extends Model
{
    use HasFactory;

    protected $fillable = ['transaction_no', 'date', 'customer_name', 'total', 'note'];

    public function items()
    {
        return $this->hasMany(StockOutItem::class);
    }
}
