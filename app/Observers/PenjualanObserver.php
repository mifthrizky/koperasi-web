<?php

namespace App\Observers;

use App\Models\Penjualan;
use App\Models\StockOpname;

class PenjualanObserver
{
    /**
     * Handle the Penjualan "created" event.
     */
    public function created(Penjualan $penjualan): void
    {
        // Ganti updateOrCreate dengan firstOrCreate
        $stockItem = StockOpname::firstOrCreate(
            [
                'Kode_Item' => $penjualan->Kode_Item,
                'Bulan'     => $penjualan->Bulan,
                'Tahun'     => (int)$penjualan->Tahun,
            ],
            // Nilai default untuk data baru (semua 0 dulu)
            [
                // FIX: Gunakan $penjualan->Nama_Item, bukan $barang->Nama_Item (karena variabel $barang tidak ada)
                'Nama_Item'   => $penjualan->Nama_Item ?? 'Nama Tidak Ditemukan',
                'Stok_Masuk'  => 0,
                'Stok_Keluar' => 0,
                'Stok_Retur'  => 0,
            ]
        );

        // Lakukan increment terpisah agar aman di MongoDB
        $stockItem->increment('Stok_Keluar', $penjualan->Jumlah);
    }

    /**
     * Handle the Penjualan "updated" event.
     */
    public function updated(Penjualan $penjualan): void
    {
        //
    }

    /**
     * Handle the Penjualan "deleted" event.
     */
    public function deleted(Penjualan $penjualan): void
    {
        //
    }

    /**
     * Handle the Penjualan "restored" event.
     */
    public function restored(Penjualan $penjualan): void
    {
        //
    }

    /**
     * Handle the Penjualan "force deleted" event.
     */
    public function forceDeleted(Penjualan $penjualan): void
    {
        //
    }
}
