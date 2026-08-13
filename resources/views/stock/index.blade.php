@extends('layouts.app')

@section('content')
    <h1 class="page-title">Kartu Stok</h1>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Nama Barang</th>
                    <th>Harga Beli (Terakhir)</th>
                    <th>Harga Jual (Terakhir)</th>
                    <th>Stok Saat Ini</th>
                </tr>
            </thead>
            <tbody>
                @foreach($products as $product)
                <tr>
                    <td>{{ $product->name }}</td>
                    <td>Rp {{ number_format($product->purchase_price, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($product->selling_price, 0, ',', '.') }}</td>
                    <td>
                        @if($product->current_stock <= $product->min_stock)
                            <span class="badge badge-danger">{{ $product->current_stock }} {{ $product->unit }}</span>
                        @else
                            <span class="badge badge-success">{{ $product->current_stock }} {{ $product->unit }}</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
