<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Pembelian;
use App\Models\Penjualan;
use App\Models\Retur;
use App\Models\StockOpname;
use Illuminate\Support\Collection;

class SyncStockOpname extends Command
{
    protected $signature = 'sync:stockopname';
    protected $description = 'Sinkronisasi data stock opname dari pembelian, penjualan, dan retur per bulan.';

    public function handle()
    {
        $this->info('Mulai sinkronisasi bulanan...');

        // Gabungkan semua transaksi menjadi satu collection
        $pembelians = Pembelian::all(['Kode_Item', 'Nama_Item', 'Jumlah', 'Bulan', 'Tahun']);
        $penjualans = Penjualan::all(['Kode_Item', 'Jumlah', 'Bulan', 'Tahun']);
        $returs = Retur::all(['Kode_Item', 'Jumlah', 'Bulan', 'Tahun']);

        // Kelompokkan setiap jenis transaksi berdasarkan kunci unik (Bulan-Tahun-KodeItem)
        $groupedPembelian = $this->groupTransactions($pembelians);
        $groupedPenjualan = $this->groupTransactions($penjualans);
        $groupedRetur = $this->groupTransactions($returs);

        // Dapatkan semua kunci unik dari semua transaksi
        $allKeys = $groupedPembelian->keys()
            ->merge($groupedPenjualan->keys())
            ->merge($groupedRetur->keys())
            ->unique();

        if ($allKeys->isEmpty()) {
            $this->info('Tidak ada data transaksi. Selesai.');
            return 0;
        }

        $created = 0;
        $updated = 0;

        foreach ($allKeys as $key) {
            [$bulan, $tahun, $kodeItem] = explode('-', $key, 3);

            // Dapatkan total untuk setiap jenis transaksi, default 0 jika tidak ada
            $stokMasuk = $groupedPembelian->get($key, 0);
            $stokKeluar = $groupedPenjualan->get($key, 0);
            $stokRetur = $groupedRetur->get($key, 0);

            // Cari nama item dari data pembelian (asumsi paling lengkap)
            $samplePembelian = $pembelians->firstWhere(
                fn($p) =>
                $p->Kode_Item == $kodeItem && $p->Bulan == $bulan && $p->Tahun == $tahun
            );
            $namaItem = $samplePembelian->Nama_Item ?? 'Nama Tidak Ditemukan';

            // Atribut untuk mencari record
            $attributes = [
                'Kode_Item' => $kodeItem,
                'Bulan'     => $bulan,
                'Tahun'     => (int)$tahun,
            ];

            // Nilai untuk di-update atau di-create
            $values = [
                'Nama_Item'   => $namaItem,
                'Stok_Masuk'  => $stokMasuk,
                'Stok_Keluar' => $stokKeluar,
                'Stok_Retur'  => $stokRetur,
            ];

            $model = StockOpname::updateOrCreate($attributes, $values);

            if ($model->wasRecentlyCreated) {
                $created++;
            } elseif ($model->wasChanged()) {
                $updated++;
            }
        }

        $this->info("Sinkronisasi selesai. Dibuat: $created | Diupdate: $updated");
        return 0;
    }

    /**
     * Mengelompokkan transaksi berdasarkan Bulan, Tahun, dan Kode Item.
     */
    private function groupTransactions(Collection $transactions): Collection
    {
        return $transactions->groupBy(function ($item) {
            // Membuat kunci unik: "JANUARI-2025-12345"
            return strtoupper($item->Bulan) . '-' . $item->Tahun . '-' . $item->Kode_Item;
        })->map(function ($items) {
            // Menjumlahkan 'Jumlah' untuk setiap grup
            return $items->sum('Jumlah');
        });
    }
}
