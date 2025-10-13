<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Penjualan extends Model
{
    protected $connection = 'mongodb';

    protected $guarded = ['_id'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'Kode_Item',
        'Nama_Item',
        'Jenis',
        'Merek',
        'Jumlah',
        'Satuan',
        'Total_Harga',
        'Bulan',
        'Tahun',
    ];

    // Relasi ke Pembelian
    public function barang()
    {
        return $this->belongsTo(Barang::class, 'Kode_Item', 'Kode_Item');
    }
}
