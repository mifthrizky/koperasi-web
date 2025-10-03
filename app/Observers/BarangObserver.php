<?php

namespace App\Observers;

use App\Models\Barang;
use App\Models\StockOpname;

class BarangObserver
{
    /**
     * Handle the Barang "created" event.
     */
    public function created(Barang $barang): void
    {
        StockOpname::create([
            'Kode_Item'    => $barang->Kode_Item,    // Sesuaikan dengan 'Kode_Item'
            'Nama_Item'    => $barang->Nama_Item,    // Sesuaikan dengan 'Nama_Item'
            'Stok_Masuk'   => 0,                      // Set nilai awal
            'Stok_Keluar'  => 0,                      // Set nilai awal
            'Stok_Retur'   => 0,                      // Set nilai awal
            'Stock_Opname' => null,                      // Set nilai awal untuk stok opname
        ]);
    }

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
