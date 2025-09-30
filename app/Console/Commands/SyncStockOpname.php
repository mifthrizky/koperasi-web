<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\StockOpname;
use App\Models\Pembelian;
use App\Models\Penjualan;
use App\Models\Retur;

class SyncStockOpname extends Command
{
    protected $signature = 'sync:stockopname';
    protected $description = 'Sinkronisasi data stock opname dari pembelian';

    public function handle()
    {
        $this->info('Mulai sinkronisasi...');

        $penjualans = Penjualan::all();

        $totalPenjualanPerItem = $penjualans->groupBy('Kode_Item')->map(function ($items) {
            return $items->sum('Jumlah');
        });

        $returs = Retur::all();
        $totalReturPerItem = $returs->groupBy('Kode_Item')->map(function ($items) {
            return $items->sum('Jumlah');
        });

        $pembelians = Pembelian::all();

        if ($pembelians->isEmpty()) {
            $this->info('Tidak ada data pembelian. Selesai.');
            return 0;
        }

        $created   = 0;
        $updated   = 0;
        $processed = 0;

        // Group pembelian per kode_item
        $grouped = $pembelians->groupBy(fn($item) => (string) $item->Kode_Item);

        foreach ($grouped as $kode => $items) {
            $processed++;

            $stokMasuk  = $items->sum('Jumlah');
            $stokKeluar = $totalPenjualanPerItem->get($kode, 0);
            $stokRetur = $totalReturPerItem->get($kode, 0);

            // Ambil sample metadata
            $sample = $items->last() ?? $items->first();
            $nama   = $sample->Nama_Item ?? null;

            // Simpan/update
            $attributes = ['Kode_Item' => $kode];
            $values = [
                'Nama_Item'    => $nama,
                'Stok_Masuk'   => $stokMasuk,
                'Stok_Keluar'  => $stokKeluar,
                'Stok_Retur'   => $stokRetur,
                'Stock_Opname' => null,
                'Keterangan'   => null
            ];

            $model = StockOpname::updateOrCreate($attributes, $values);

            if ($model->wasRecentlyCreated) {
                $created++;
            } else {
                $updated++;
            }
        }

        $this->info("Diproses: $processed | Dibuat: $created | Diupdate: $updated");
        $this->info('Sinkronisasi selesai.');
        return 0;
    }
}
