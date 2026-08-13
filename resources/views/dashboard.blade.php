@extends('layouts.app')

@section('content')
    <h1 class="page-title">Dashboard</h1>

    <div class="dashboard-cards">
        <div class="card">
            <div class="card-title">Total Nilai Stok</div>
            <div class="card-value">Rp {{ number_format($totalStockValue, 0, ',', '.') }}</div>
        </div>
        
        <div class="card">
            <div class="card-title">Penjualan Hari Ini</div>
            <div class="card-value text-success">Rp {{ number_format($todaySales, 0, ',', '.') }}</div>
        </div>
    </div>
    
    <div class="card" style="margin-bottom: 40px;">
        <div class="card-title" style="margin-bottom: 20px;">Grafik Penjualan (7 Hari Terakhir)</div>
        <div style="position: relative; height: 300px; width: 100%;">
            <canvas id="salesChart"></canvas>
        </div>
    </div>

    @if($lowStockProducts->count() > 0)
        <div class="table-container" style="border-color: var(--danger);">
            <h2 style="color: var(--danger); margin-bottom: 15px;">⚠️ Peringatan: Stok Menipis</h2>
            <table>
                <thead>
                    <tr>
                        <th>Nama Barang</th>
                        <th>Stok Saat Ini</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($lowStockProducts as $product)
                    <tr>
                        <td>{{ $product->name }}</td>
                        <td><span class="badge badge-danger">{{ $product->current_stock }} {{ $product->unit }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @elseif($totalProducts == 0)
        <div class="table-container">
            <p style="color: var(--text-muted);">ℹ️ Belum ada data barang di sistem. Lakukan transaksi Barang Masuk untuk menambahkan barang.</p>
        </div>
    @else
        <div class="table-container">
            <p style="color: var(--success);">✅ Semua stok barang dalam batas aman.</p>
        </div>
    @endif
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('salesChart').getContext('2d');
    
    // Create gradient
    let gradient = ctx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, 'rgba(99, 102, 241, 0.5)'); // Indigo
    gradient.addColorStop(1, 'rgba(99, 102, 241, 0.0)');
    
    const salesChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: {!! json_encode($chartLabels) !!},
            datasets: [{
                label: 'Penjualan (Rp)',
                data: {!! json_encode($chartData) !!},
                borderColor: '#6366f1',
                backgroundColor: gradient,
                borderWidth: 3,
                pointBackgroundColor: '#6366f1',
                pointBorderColor: '#fff',
                pointHoverBackgroundColor: '#fff',
                pointHoverBorderColor: '#6366f1',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.y !== null) {
                                label += new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(context.parsed.y);
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(255, 255, 255, 0.05)',
                        drawBorder: false,
                    },
                    ticks: {
                        color: '#94a3b8',
                        callback: function(value, index, values) {
                            if(value >= 1000000) return 'Rp ' + (value/1000000) + ' Jt';
                            if(value >= 1000) return 'Rp ' + (value/1000) + ' Rb';
                            return 'Rp ' + value;
                        }
                    }
                },
                x: {
                    grid: {
                        display: false,
                        drawBorder: false,
                    },
                    ticks: {
                        color: '#94a3b8'
                    }
                }
            }
        }
    });
</script>
@endsection
