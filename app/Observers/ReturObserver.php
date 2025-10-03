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
        // Cari data stok berdasarkan Kode_Item dari penjua$retur yang baru dibuat
        $stockItem = StockOpname::where('Kode_Item', $retur->Kode_Item)->first();

        if ($stockItem) {
            $stockItem->increment('Stok_Retur', $retur->Jumlah);
        }
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
