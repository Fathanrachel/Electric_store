<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('sku')->label('SKU / Kode Barang')->maxLength(255),
                TextInput::make('name')->label('Nama Barang')->required()->maxLength(255),
                Select::make('category_id')->relationship('category', 'name')->searchable()->preload()->required(),
                TextInput::make('unit')->label('Satuan')->default('pcs')->required(),
                TextInput::make('purchase_price')->label('Harga Beli Terakhir')->numeric()->default(0)->prefix('Rp'),
                TextInput::make('selling_price')->label('Harga Jual')->numeric()->default(0)->prefix('Rp'),
                TextInput::make('min_stock')->label('Batas Stok Minimum')->numeric()->default(5)->required(),
            ]);
    }
}
