@extends('layouts.app')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
/* Select2 Dark Theme Overrides */
.select2-container--default .select2-selection--single {
    background-color: var(--bg-card) !important;
    border: 1px solid var(--border) !important;
    border-radius: 8px !important;
    height: 42px !important;
    display: flex !important;
    align-items: center !important;
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
    color: var(--text-main) !important;
    line-height: normal !important;
    padding-left: 14px !important;
    font-family: 'Inter', sans-serif;
}
.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 40px !important;
    right: 10px !important;
}
.select2-dropdown {
    background-color: var(--bg-card) !important;
    border: 1px solid var(--border) !important;
    border-radius: 8px !important;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.5) !important;
}
.select2-search--dropdown .select2-search__field {
    background-color: var(--bg-panel) !important;
    border: 1px solid var(--primary) !important;
    color: var(--text-main) !important;
    border-radius: 4px !important;
    padding: 6px 10px !important;
    outline: none !important;
}
.select2-results__option {
    color: var(--text-main) !important;
    padding: 10px 14px !important;
    font-family: 'Inter', sans-serif;
}
.select2-container--default .select2-results__option--highlighted.select2-results__option--selectable {
    background-color: var(--primary) !important;
    color: white !important;
}
.select2-container--default .select2-results__option--selected {
    background-color: var(--bg-panel) !important;
}
</style>
    <h1 class="page-title">Barang Keluar (Penjualan)</h1>

    <div class="card">
        <form action="{{ route('transactions.out.store') }}" method="POST">
            @csrf
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label>Tanggal</label>
                    <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
                </div>
                <div class="form-group">
                    <label>Nama Pelanggan (Opsional)</label>
                    <input type="text" name="customer_name" class="form-control" placeholder="Tulis nama jika ada">
                </div>
            </div>
            
            <div class="form-group">
                <label>Catatan (Opsional)</label>
                <input type="text" name="note" class="form-control" placeholder="Keterangan tambahan">
            </div>

            <hr style="border: 0; border-top: 1px solid var(--border); margin: 30px 0;">
            
            <h3 style="margin-bottom: 15px;">Daftar Barang yang Dijual</h3>
            
            <div class="table-container" style="padding: 0; margin-bottom: 20px;">
                <table id="itemsTable">
                    <thead>
                        <tr>
                            <th>Barang</th>
                            <th width="12%">Qty</th>
                            <th width="25%">Harga Satuan (Rp)</th>
                            <th width="25%">Total Harga (Rp)</th>
                            <th width="8%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="itemsBody">
                        <!-- Rows will be injected here -->
                    </tbody>
                </table>
            </div>

            <!-- Template for a new row -->
            <template id="rowTemplate">
                <tr class="item-row">
                    <td>
                        <select class="form-control product-select" required onchange="calculateRow(this, 'price')">
                            <option value="">-- Ketik / Pilih Barang --</option>
                            @foreach($products as $product)
                                <option value="{{ $product->name }}" data-price="{{ $product->selling_price }}" data-stock="{{ $product->current_stock }}">{{ $product->name }} (Stok: {{ $product->current_stock }})</option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <input type="number" class="form-control qty-input text-right" value="1" min="1" required oninput="calculateRow(this, 'qty')">
                    </td>
                    <td>
                        <input type="text" class="form-control price-display text-right" value="0" required oninput="calculateRow(this, 'price')">
                        <input type="hidden" class="price-val" value="0">
                    </td>
                    <td>
                        <input type="text" class="form-control subtotal-display text-right" value="0" required oninput="calculateRow(this, 'subtotal')">
                    </td>
                    <td>
                        <button type="button" class="btn btn-danger" onclick="removeRow(this)">X</button>
                    </td>
                </tr>
            </template>

            <button type="button" class="btn" style="background-color: var(--border); color: white; margin-bottom: 20px;" onclick="addRow()">+ Tambah Barang Lain</button>
            
            <div style="text-align: right; font-size: 1.5rem; font-weight: bold; margin-bottom: 30px;">
                Total: Rp <span id="grandTotal">0</span>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; font-size: 1.1rem; background-color: var(--success); border-color: var(--success);">Simpan Transaksi Keluar</button>
        </form>
    </div>
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    let rowIndex = 0;
    
    // Initialize first row on load
    document.addEventListener("DOMContentLoaded", function() {
        addRow();
    });
    
    function parseRupiah(text) {
        if (!text) return 0;
        return parseFloat(text.toString().replace(/\./g, '').replace(/,/g, '')) || 0;
    }
    
    function formatRupiah(number) {
        if (isNaN(number)) return "0";
        return new Intl.NumberFormat('id-ID').format(number);
    }
    
    function calculateRow(element, source = 'price') {
        const row = element.closest('.item-row');
        if (!row) return; // sometimes event fires before row is fully ready
        const select = row.querySelector('.product-select');
        const qtyInput = row.querySelector('.qty-input');
        
        const priceDisplay = row.querySelector('.price-display');
        const priceVal = row.querySelector('.price-val');
        const subtotalDisplay = row.querySelector('.subtotal-display');
        
        let qty = parseFloat(qtyInput.value) || 0;
        let maxStock = 0;
        
        // Find selected option to get data attributes
        let selectedOption = select.options[select.selectedIndex];
        
        if (selectedOption && selectedOption.value !== "") {
            maxStock = parseInt(selectedOption.getAttribute('data-stock'));
            // If source is the dropdown changing, update the price
            if (source === 'price' && document.activeElement !== priceDisplay) {
                let p = selectedOption.getAttribute('data-price');
                priceDisplay.value = formatRupiah(p);
                priceVal.value = p;
            }
        }
        
        // Validate stock
        if (maxStock > 0 && qty > maxStock) {
            Swal.fire({
                icon: 'error',
                title: 'Stok Tidak Mencukupi',
                text: 'Maksimal stok yang tersedia: ' + maxStock,
                background: 'var(--bg-panel)',
                color: 'var(--text-main)',
                confirmButtonColor: 'var(--primary)'
            });
            qty = maxStock;
            qtyInput.value = maxStock;
        }
        
        if (source === 'price' || source === 'qty') {
            // Clean the user input formatting
            if (source === 'price') {
                const rawPrice = parseRupiah(priceDisplay.value);
                priceDisplay.value = formatRupiah(rawPrice);
            }
            
            const price = parseRupiah(priceDisplay.value);
            priceVal.value = price;
            
            const subtotal = qty * price;
            subtotalDisplay.value = formatRupiah(subtotal);
            
        } else if (source === 'subtotal') {
            // Clean the user input formatting
            const rawSubtotal = parseRupiah(subtotalDisplay.value);
            subtotalDisplay.value = formatRupiah(rawSubtotal);
            
            const subtotal = rawSubtotal;
            if (qty > 0) {
                const price = Math.round(subtotal / qty);
                priceDisplay.value = formatRupiah(price);
                priceVal.value = price;
            }
        }
        
        calculateGrandTotal();
    }
    
    function calculateGrandTotal() {
        let total = 0;
        document.querySelectorAll('.item-row').forEach(row => {
            const subtotal = parseRupiah(row.querySelector('.subtotal-display').value);
            total += subtotal;
        });
        document.getElementById('grandTotal').innerText = formatRupiah(total);
    }
    
    function addRow() {
        const tbody = document.getElementById('itemsBody');
        const template = document.getElementById('rowTemplate');
        const clone = template.content.cloneNode(true);
        
        // Update names for array submission
        clone.querySelector('.product-select').name = `items[${rowIndex}][product_name]`;
        clone.querySelector('.qty-input').name = `items[${rowIndex}][qty]`;
        clone.querySelector('.price-val').name = `items[${rowIndex}][price]`;
        
        tbody.appendChild(clone);
        
        // Initialize Select2 on the newly added select element
        const newRow = tbody.lastElementChild;
        const selectEl = $(newRow.querySelector('.product-select'));
        selectEl.select2({
            width: '100%',
            dropdownAutoWidth: true
        });
        
        // Re-bind onchange event for Select2 since it hides the original select
        selectEl.on('change', function() {
            calculateRow(this, 'price');
        });
        
        rowIndex++;
    }
    
    function removeRow(btn) {
        const tbody = document.getElementById('itemsBody');
        if (tbody.querySelectorAll('.item-row').length > 1) {
            // Destroy Select2 instance before removing to prevent memory leaks
            const selectEl = $(btn.closest('.item-row')).find('.product-select');
            if (selectEl.hasClass("select2-hidden-accessible")) {
                selectEl.select2('destroy');
            }
            btn.closest('.item-row').remove();
            calculateGrandTotal();
        } else {
            Swal.fire({
                icon: 'warning',
                title: 'Tidak Bisa Dihapus',
                text: 'Minimal harus ada 1 barang dalam transaksi!',
                background: 'var(--bg-panel)',
                color: 'var(--text-main)',
                confirmButtonColor: 'var(--primary)'
            });
        }
    }
</script>
@endsection
