<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Product;
use App\Models\StockOut;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->input('filter', 'weekly');
        
        $totalStockValue = Product::selectRaw('SUM(current_stock * purchase_price) as total')->value('total') ?? 0;
        
        $chartLabels = [];
        $chartData = [];

        if ($filter == 'monthly') {
            $salesTitle = 'Penjualan Bulan Ini';
            $chartTitle = 'Grafik Penjualan (30 Hari Terakhir)';
            
            $salesAmount = StockOut::whereMonth('date', Carbon::now()->month)
                                   ->whereYear('date', Carbon::now()->year)
                                   ->sum('total');
            
            for ($i = 29; $i >= 0; $i--) {
                $date = Carbon::today()->subDays($i);
                $chartLabels[] = $date->format('d M');
                $chartData[] = StockOut::whereDate('date', $date)->sum('total');
            }
        } else {
            $salesTitle = 'Penjualan Minggu Ini';
            $chartTitle = 'Grafik Penjualan (Minggu Ini)';
            
            $salesAmount = StockOut::whereBetween('date', [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek()
            ])->sum('total');
            
            $startOfWeek = Carbon::now()->startOfWeek();
            $daysIndo = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
            for ($i = 0; $i < 7; $i++) {
                $date = $startOfWeek->copy()->addDays($i);
                $chartLabels[] = $daysIndo[$i];
                $chartData[] = StockOut::whereDate('date', $date)->sum('total');
            }
        }
        
        $lowStockProducts = Product::whereColumn('current_stock', '<=', 'min_stock')->get();
        $totalProducts = Product::count();
        
        return view('dashboard', compact(
            'totalStockValue', 'salesTitle', 'salesAmount', 'chartTitle', 
            'chartLabels', 'chartData', 'lowStockProducts', 'totalProducts', 'filter'
        ));
    }
}
