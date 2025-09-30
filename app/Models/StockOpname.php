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
        'Stok_Retur',
        'Stock_Opname',
        'Keterangan'
    ];

    // Relasi ke Barang
    public function barang()
    {
        return $this->belongsTo(Barang::class, 'Kode_Item', 'Kode_Item');
    }

    /**
     * ====================================================================
     * ACCESSOR UNTUK STOK SISTEM
     * ====================================================================
     *
     * Accessor ini akan membuat atribut virtual 'Stok_Sistem'.
     * Setiap kali Anda memanggil $item->Stok_Sistem, fungsi ini akan dijalankan.
     */
    public function getStokSistemAttribute(): int
    {
        // Ambil nilai dari atribut model, beri nilai default 0 jika null
        $masuk = $this->Stok_Masuk ?? 0;
        $keluar = $this->Stok_Keluar ?? 0;
        $retur = $this->Stok_Retur ?? 0;

        // Lakukan kalkulasi real-time
        return $masuk - $keluar - $retur;
    }
}
