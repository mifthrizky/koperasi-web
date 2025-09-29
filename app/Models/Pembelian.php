<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Pembelian extends Model
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
        'Jumlah',
        'Satuan',
        'Total_Harga',
        'Bulan',
        'Tahun',
    ];
}
