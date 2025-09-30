<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Pembelian;
use App\Models\Barang;

class SyncBarang extends Command
{
    protected $signature = 'sync:barang';
    protected $description = 'Sinkronisasi data barang dari pembelian';

    public function handle()
    {
        $this->info('Mulai sinkronisasi...');

        $pembelians = Pembelian::all();

        if ($pembelians->isEmpty()) {
            $this->info('Tidak ada data pembelian. Selesai.');
            return 0;
        }

        $created   = 0;
        $updated   = 0;
        $processed = 0;

        // Group pembelian per Kode_Item
        $grouped = $pembelians->groupBy(fn($item) => (string) $item->Kode_Item);

        foreach ($grouped as $kode => $items) {
            $processed++;

            $totalPembQty = $items->sum('Jumlah');
            $totalPembHarga = $items->sum(fn($i) => isset($i->Total_Harga) ? (float) $i->Total_Harga : 0);

            // Ambil sample metadata
            $sample = $items->last() ?? $items->first();
            $nama   = $sample->Nama_Item ?? null;
            $jenis  = $sample->Jenis ?? null;
            $satuan = $sample->Satuan ?? null;

            // Hitung harga satuan
            $hargaSatuan = $totalPembQty > 0
                ? ($totalPembHarga / $totalPembQty)
                : 0;

            // Simpan/update ke MongoDB
            $attributes = ['Kode_Item' => $kode];
            $values = [
                'Nama_Item'    => $nama,
                'Jenis'        => $jenis,
                'Harga_Satuan' => $hargaSatuan,
            ];

            $model = Barang::updateOrCreate($attributes, $values);

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
