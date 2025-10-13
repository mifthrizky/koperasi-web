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
        StockOpname::updateOrCreate(
            [
                'Kode_Item' => $penjualan->Kode_Item,
                'Bulan'     => $penjualan->Bulan,
                'Tahun'     => (int)$penjualan->Tahun,
            ],
            // Nilai yang akan di-update atau di-create
            [
                'Nama_Item'   => $barang->Nama_Item ?? 'Nama Tidak Ditemukan',
                'Stok_Masuk'  => \Illuminate\Support\Facades\DB::raw("`Stok_Masuk` + 0"),
                'Stok_Keluar' => \Illuminate\Support\Facades\DB::raw("`Stok_Keluar` + {$penjualan->Jumlah}"),
                'Stok_Retur'  => \Illuminate\Support\Facades\DB::raw("`Stok_Retur` + 0"),
            ]
        );
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
