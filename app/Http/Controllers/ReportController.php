<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\StockIn;
use App\Models\StockOut;
use App\Models\Product;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));

        $stockIns = StockIn::with('items.product')->whereBetween('date', [$startDate, $endDate])->get();
        $stockOuts = StockOut::with('items.product')->whereBetween('date', [$startDate, $endDate])->get();
        
        $totalIn = $stockIns->sum('total');
        $totalOut = $stockOuts->sum('total');

        return view('reports.index', compact('stockIns', 'stockOuts', 'totalIn', 'totalOut', 'startDate', 'endDate'));
    }
}
