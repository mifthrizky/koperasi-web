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
        'Satuan',
        'Harga_Satuan',
        'Bulan',
        'Tahun',
    ];
}
