<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Retur extends Model
{
    protected $connection = 'mongodb';


    protected $guarded = ['_id'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'No',
        'Kode_Item',
        'Nama_Item',
        'Jumlah',
        'Satuan',
        'Harga',
        'Potongan',
        'Total_Harga',
        'Bulan',
        'Tahun',
    ];

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'Kode_Item', 'Kode_Item');
    }
}
