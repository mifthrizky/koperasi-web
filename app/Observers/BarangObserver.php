<?php

namespace App\Observers;

use App\Models\Barang;
use App\Models\StockOpname;

class BarangObserver
{
    /**
     * Handle the Barang "created" event.
     */
    public function created(Barang $barang): void {}

    /**
     * Handle the Barang "updated" event.
     */
    public function updated(Barang $barang): void
    {
        //
    }

    /**
     * Handle the Barang "deleted" event.
     */
    public function deleted(Barang $barang): void
    {
        // Ini boleh dibiarkan (Hapus semua riwayat stok jika barang induk dihapus)
        StockOpname::where('Kode_Item', $barang->Kode_Item)->delete();
    }

    /**
     * Handle the Barang "restored" event.
     */
    public function restored(Barang $barang): void
    {
        //
    }

    /**
     * Handle the Barang "force deleted" event.
     */
    public function forceDeleted(Barang $barang): void
    {
        //
    }
}
