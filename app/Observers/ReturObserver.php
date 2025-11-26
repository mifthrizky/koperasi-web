<?php

namespace App\Observers;

use App\Models\Retur;
use App\Models\StockOpname;

class ReturObserver
{
    /**
     * Handle the Retur "created" event.
     */
    public function created(Retur $retur): void
    {
        $stockItem = StockOpname::firstOrCreate(
            [
                'Kode_Item' => $retur->Kode_Item,
                'Bulan'     => $retur->Bulan,
                'Tahun'     => (int)$retur->Tahun,
            ],
            [
                // FIX: Gunakan $retur->Nama_Item atau default string
                'Nama_Item'   => $retur->Nama_Item ?? 'Nama Tidak Ditemukan',
                'Stok_Masuk'  => 0,
                'Stok_Keluar' => 0,
                'Stok_Retur'  => 0,
            ]
        );

        // Increment Stok Retur
        $stockItem->increment('Stok_Retur', $retur->Jumlah);
    }

    /**
     * Handle the Retur "updated" event.
     */
    public function updated(Retur $retur): void
    {
        //
    }

    /**
     * Handle the Retur "deleted" event.
     */
    public function deleted(Retur $retur): void
    {
        //
    }

    /**
     * Handle the Retur "restored" event.
     */
    public function restored(Retur $retur): void
    {
        //
    }

    /**
     * Handle the Retur "force deleted" event.
     */
    public function forceDeleted(Retur $retur): void
    {
        //
    }
}
