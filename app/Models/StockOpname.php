<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class StockOpname extends Model
{
    protected $connection = 'mongodb';
    protected $guarded = ['_id'];
    protected $collection = 'stock_opnames';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

    protected $fillable = [
        'Kode_Item',
        'Nama_Item',
        'Stok_Masuk',
        'Stok_Keluar',
        'Stok_Sistem',
        'Stock_Opname',
        'Keterangan'
    ];

    // Relasi ke Barang
    public function barang()
    {
        return $this->belongsTo(Barang::class, 'Kode_Item', 'Kode_Item');
    }
}
