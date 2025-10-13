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
        StockOpname::updateOrCreate(
            [
                'Kode_Item' => $retur->Kode_Item,
                'Bulan' => $retur->Bulan,
                'Tahun' => $retur->Tahun,
            ],
            [
                // Jika membuat baru, isi Nama_Item dan beri nilai default 0
                'Nama_Item'   => $barang->Nama_Item ?? 'Nama Tidak Ditemukan',
                'Stok_Masuk'  => \Illuminate\Support\Facades\DB::raw("`Stok_Masuk` + 0"),
                'Stok_Keluar' => \Illuminate\Support\Facades\DB::raw("`Stok_Keluar` + 0"),
                'Stok_Retur'  => \Illuminate\Support\Facades\DB::raw("`Stok_Retur` + {$retur->Jumlah}"),
            ]
        );
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
