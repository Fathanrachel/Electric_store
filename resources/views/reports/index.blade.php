@extends('layouts.app')

@section('content')
    <h1 class="page-title">Laporan</h1>

    <div class="card" style="margin-bottom: 30px;">
        <form action="{{ route('reports.index') }}" method="GET" style="display: flex; gap: 20px; align-items: flex-end;">
            <div class="form-group" style="margin: 0; flex: 1;">
                <label>Dari Tanggal</label>
                <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
            </div>
            <div class="form-group" style="margin: 0; flex: 1;">
                <label>Sampai Tanggal</label>
                <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
            </div>
            <div>
                <button type="submit" class="btn btn-primary">Filter Laporan</button>
            </div>
        </form>
    </div>

    <div class="dashboard-cards">
        <div class="card">
            <div class="card-title">Total Pembelian (Barang Masuk)</div>
            <div class="card-value text-danger">Rp {{ number_format($totalIn, 0, ',', '.') }}</div>
        </div>
        
        <div class="card">
            <div class="card-title">Total Penjualan (Barang Keluar)</div>
            <div class="card-value text-success">Rp {{ number_format($totalOut, 0, ',', '.') }}</div>
        </div>
        
        <div class="card">
            <div class="card-title">Estimasi Laba Kotor (Bulan Ini)</div>
            @php $laba = $totalOut - $totalIn; @endphp
            <div class="card-value {{ $laba >= 0 ? 'text-success' : 'text-danger' }}">
                Rp {{ number_format($laba, 0, ',', '.') }}
            </div>
        </div>
    </div>

    <div style="display: flex; flex-direction: column; gap: 30px;">
        <div class="table-container">
            <h3 style="margin-bottom: 15px;">Riwayat Barang Masuk</h3>
            <table>
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>No Transaksi</th>
                        <th>Barang (Qty)</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($stockIns as $in)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($in->date)->format('d M Y') }}</td>
                        <td>{{ $in->transaction_no }}</td>
                        <td>
                            @foreach($in->items as $item)
                                <div style="font-size: 0.85rem; color: var(--text-muted);">
                                    • {{ $item->product->name }} ({{ $item->qty }})
                                </div>
                            @endforeach
                        </td>
                        <td style="text-align: right;">Rp {{ number_format($in->total, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="table-container">
            <h3 style="margin-bottom: 15px;">Riwayat Barang Keluar</h3>
            <table>
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>No Transaksi</th>
                        <th>Pelanggan</th>
                        <th>Barang (Qty)</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($stockOuts as $out)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($out->date)->format('d M Y') }}</td>
                        <td>{{ $out->transaction_no }}</td>
                        <td>{{ $out->customer_name ?? '-' }}</td>
                        <td>
                            @foreach($out->items as $item)
                                <div style="font-size: 0.85rem; color: var(--text-muted);">
                                    • {{ $item->product->name }} ({{ $item->qty }})
                                </div>
                            @endforeach
                        </td>
                        <td style="text-align: right;">Rp {{ number_format($out->total, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

@endsection
