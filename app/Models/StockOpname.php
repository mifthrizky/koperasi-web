<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use App\Models\Barang;

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
        'Keterangan',
        'Bulan',
        'Tahun',
        'petugas',
    ];

    /**
     * TAMBAHKAN BLOK INI
     * ==============================================================
     * The attributes that should be cast.
     * Ini akan memastikan tipe data selalu konsisten saat diakses.
     *
     * @var array
     */
    protected $casts = [
        'Kode_Item' => 'string',
        'Nama_Item' => 'string',
        'Stok_Masuk' => 'integer',
        'Stok_Keluar' => 'integer',
        'Stok_Retur' => 'integer',
        'Stock_Opname' => 'integer',
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
        $masuk = (int) $this->Stok_Masuk ?? 0;
        $keluar = (int) $this->Stok_Keluar ?? 0;
        $retur = (int) $this->Stok_Retur ?? 0;

        return $masuk - $keluar - $retur;
    }
}
