<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Product;
use App\Models\StockOut;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $totalStockValue = Product::selectRaw('SUM(current_stock * purchase_price) as total')->value('total') ?? 0;
        
        $todaySales = StockOut::whereDate('date', Carbon::today())->sum('total');
        
        $lowStockProducts = Product::whereColumn('current_stock', '<=', 'min_stock')->get();
        $totalProducts = Product::count();
        
        // Data Grafik Penjualan 7 Hari Terakhir
        $chartLabels = [];
        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $chartLabels[] = $date->format('d M');
            $chartData[] = StockOut::whereDate('date', $date)->sum('total');
        }
        
        return view('dashboard', compact('totalStockValue', 'todaySales', 'lowStockProducts', 'chartLabels', 'chartData', 'totalProducts'));
    }
}
