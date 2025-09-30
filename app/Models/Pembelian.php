<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Pembelian extends Model
{
    protected $connection = 'mongodb';
    protected $guarded = ['_id'];
    protected $collection = 'pembelians';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

    protected $fillable = [
        'Kode_Item',
        'Nama_Item',
        'Jenis',
        'Jumlah',
        'Satuan',
        'Total_Harga',
        'Bulan',
        'Tahun',
    ];

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
}
