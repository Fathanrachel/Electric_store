@extends('layouts.app')

@section('content')
    <h1 class="page-title">Barang Masuk (Pembelian)</h1>

    <div class="card">
        <form action="{{ route('transactions.in.store') }}" method="POST">
            @csrf
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group" style="grid-column: span 2;">
                    <label>Tanggal</label>
                    <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
                </div>
            </div>
            
            <div class="form-group">
                <label>Catatan (Opsional)</label>
                <input type="text" name="note" class="form-control" placeholder="No Invoice / Keterangan">
            </div>

            <hr style="border: 0; border-top: 1px solid var(--border); margin: 30px 0;">
            
            <h3 style="margin-bottom: 15px;">Daftar Barang</h3>
            
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
                        <tr class="item-row">
                            <td>
                                <input type="text" name="items[0][product_name]" class="form-control product-input" list="productList" required placeholder="Ketik nama barang..." oninput="calculateRow(this)">
                                <datalist id="productList">
                                    @foreach($products as $product)
                                        <option value="{{ $product->name }}" data-price="{{ $product->purchase_price }}">
                                    @endforeach
                                </datalist>
                            </td>
                            <td>
                                <input type="number" name="items[0][qty]" class="form-control qty-input text-right" value="1" min="1" required oninput="calculateRow(this, 'qty')">
                            </td>
                            <td>
                                <input type="text" class="form-control price-display text-right" value="0" required oninput="calculateRow(this, 'price')">
                                <input type="hidden" name="items[0][price]" class="price-val" value="0">
                            </td>
                            <td>
                                <input type="text" class="form-control subtotal-display text-right" value="0" required oninput="calculateRow(this, 'subtotal')">
                            </td>
                            <td>
                                <button type="button" class="btn btn-danger" onclick="removeRow(this)">X</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <button type="button" class="btn" style="background-color: var(--border); color: white; margin-bottom: 20px;" onclick="addRow()">+ Tambah Barang Lain</button>
            
            <div style="text-align: right; font-size: 1.5rem; font-weight: bold; margin-bottom: 30px;">
                Total: Rp <span id="grandTotal">0</span>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; font-size: 1.1rem;">Simpan Transaksi Masuk</button>
        </form>
    </div>
@endsection

@section('scripts')
<script>
    let rowIndex = 1;
    
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
        const input = row.querySelector('.product-input');
        const qtyInput = row.querySelector('.qty-input');
        
        const priceDisplay = row.querySelector('.price-display');
        const priceVal = row.querySelector('.price-val');
        const subtotalDisplay = row.querySelector('.subtotal-display');
        
        let qty = parseFloat(qtyInput.value) || 0;
        
        // Auto fill price on input if match
        if(element.classList.contains('product-input')) {
            const list = document.getElementById('productList');
            const options = list.options;
            for (let i = 0; i < options.length; i++) {
                if (options[i].value === input.value) {
                    let p = options[i].getAttribute('data-price');
                    priceDisplay.value = formatRupiah(p);
                    priceVal.value = p;
                    source = 'price';
                    break;
                }
            }
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
        const firstRow = tbody.querySelector('.item-row');
        const newRow = firstRow.cloneNode(true);
        
        // Update names to have correct index
        newRow.querySelector('.product-input').name = `items[${rowIndex}][product_name]`;
        newRow.querySelector('.product-input').value = "";
        
        newRow.querySelector('.qty-input').name = `items[${rowIndex}][qty]`;
        newRow.querySelector('.qty-input').value = "1";
        
        newRow.querySelector('.price-val').name = `items[${rowIndex}][price]`;
        newRow.querySelector('.price-val').value = "0";
        
        newRow.querySelector('.price-display').value = "0";
        newRow.querySelector('.subtotal-display').value = "0";
        
        tbody.appendChild(newRow);
        rowIndex++;
    }
    
    function removeRow(btn) {
        const tbody = document.getElementById('itemsBody');
        if (tbody.querySelectorAll('.item-row').length > 1) {
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
