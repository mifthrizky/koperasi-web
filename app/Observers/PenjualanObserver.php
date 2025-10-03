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
        // Cari data stok berdasarkan Kode_Item dari penjua$penjualan yang baru dibuat
        $stockItem = StockOpname::where('Kode_Item', $penjualan->Kode_Item)->first();

        if ($stockItem) {
            $stockItem->increment('Stok_Keluar', $penjualan->Jumlah);
        }
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
