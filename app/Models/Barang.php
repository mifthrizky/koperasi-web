<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Barang extends Model
{
    protected $connection = 'mongodb';
    protected $guarded = ['_id'];
    protected $collection = 'barangs';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

    protected $fillable = [
        'Kode_Item',
        'Nama_Item',
        'Jenis',
        'Harga_Satuan'
    ];

    // Relasi ke Pembelian
    public function pembelians()
    {
        return $this->hasMany(Penjualan::class, 'Kode_Item', 'Kode_Item');
    }

    // Relasi ke Penjualan
    public function penjualans()
    {
        return $this->hasMany(Penjualan::class, 'Kode_Item', 'Kode_Item');
    }

    // Relasi ke Retur
    public function returs()
    {
        return $this->hasMany(Retur::class, 'Kode_Item', 'Kode_Item');
    }

    // Relasi ke StockOpname
    public function StockOpname()
    {
        return $this->hasMany(Retur::class, 'Kode_Item', 'Kode_Item');
    }
}
