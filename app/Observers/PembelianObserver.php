<?php

namespace App\Observers;

use App\Models\Pembelian;
use App\Models\StockOpname;

class PembelianObserver
{
    /**
     * Handle the Pembelian "created" event.
     */
    public function created(Pembelian $pembelian): void
    {
        // Gunakan firstOrCreate untuk menangani item baru yang belum ada stoknya.
        $stockItem = StockOpname::firstOrCreate(
            ['Kode_Item' => $pembelian->Kode_Item],
            ['Nama_Item' => $pembelian->Nama_Item]
        );

        // Tambahkan stok masuk dari jumlah pembelian.
        $stockItem->increment('Stok_Masuk', $pembelian->Jumlah);
    }

    /**
     * Handle the Pembelian "updated" event.
     */
    public function updated(Pembelian $pembelian): void
    {
        //
    }

    /**
     * Handle the Pembelian "deleted" event.
     */
    public function deleted(Pembelian $pembelian): void
    {
        //
    }

    /**
     * Handle the Pembelian "restored" event.
     */
    public function restored(Pembelian $pembelian): void
    {
        //
    }

    /**
     * Handle the Pembelian "force deleted" event.
     */
    public function forceDeleted(Pembelian $pembelian): void
    {
        //
    }
}
