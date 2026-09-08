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
                    <th>Status</th>
                    <th width="130">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($products as $product)
                <tr style="{{ !$product->is_active ? 'opacity: 0.45;' : '' }}">
                    <td>
                        {{ $product->name }}
                        @if(!$product->is_active)
                            <span style="font-size:0.75rem; color: var(--danger); margin-left:6px;">(nonaktif)</span>
                        @endif
                    </td>
                    <td>Rp {{ number_format($product->purchase_price, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($product->selling_price, 0, ',', '.') }}</td>
                    <td>
                        @if($product->current_stock <= $product->min_stock)
                            <span class="badge badge-danger">{{ $product->current_stock }} {{ $product->unit }}</span>
                        @else
                            <span class="badge badge-success">{{ $product->current_stock }} {{ $product->unit }}</span>
                        @endif
                    </td>
                    <td>
                        @if($product->is_active)
                            <span class="badge badge-success">Aktif</span>
                        @else
                            <span class="badge badge-danger">Nonaktif</span>
                        @endif
                    </td>
                    <td>
                        {{-- Form tersembunyi, submit via JS --}}
                        <form id="form-toggle-{{ $product->id }}"
                              action="{{ route('stock.toggle-active', $product) }}"
                              method="POST" style="display:none;">
                            @csrf
                        </form>

                        @if($product->is_active)
                            <button type="button"
                                    class="btn btn-danger"
                                    style="font-size:0.8rem; padding: 6px 12px;"
                                    onclick="confirmToggle({{ $product->id }}, '{{ addslashes($product->name) }}', true)">
                                Nonaktifkan
                            </button>
                        @else
                            <button type="button"
                                    class="btn btn-primary"
                                    style="font-size:0.8rem; padding: 6px 12px;"
                                    onclick="confirmToggle({{ $product->id }}, '{{ addslashes($product->name) }}', false)">
                                Aktifkan
                            </button>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection

@section('scripts')
<script>
    function confirmToggle(id, name, isCurrentlyActive) {
        if (isCurrentlyActive) {
            // Konfirmasi nonaktifkan
            Swal.fire({
                icon: 'warning',
                title: 'Nonaktifkan Barang?',
                html: `Barang <strong>"${name}"</strong> tidak akan muncul di dropdown lagi.<br><br>Kamu bisa mengaktifkannya kembali kapan saja.`,
                showCancelButton: true,
                confirmButtonText: 'Ya, Nonaktifkan',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#e74c3c',
                cancelButtonColor: 'var(--border)',
                background: 'var(--bg-panel)',
                color: 'var(--text-main)',
                reverseButtons: true,
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('form-toggle-' + id).submit();
                }
            });
        } else {
            // Aktifkan langsung tanpa konfirmasi
            Swal.fire({
                icon: 'question',
                title: 'Aktifkan Barang?',
                html: `Barang <strong>"${name}"</strong> akan muncul kembali di dropdown.`,
                showCancelButton: true,
                confirmButtonText: 'Ya, Aktifkan',
                cancelButtonText: 'Batal',
                confirmButtonColor: 'var(--primary)',
                cancelButtonColor: 'var(--border)',
                background: 'var(--bg-panel)',
                color: 'var(--text-main)',
                reverseButtons: true,
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('form-toggle-' + id).submit();
                }
            });
        }
    }
</script>
@endsection
