<?php

namespace App\Imports;

use App\Models\Pembelian;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class PembeliansImport implements ToCollection, WithHeadingRow, WithChunkReading
{
    /**
     * @param Collection $rows
     */
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // Gunakan Pembelian::create() karena setiap baris adalah transaksi pembelian baru.
            // Observer yang sudah dibuat akan otomatis berjalan setelah ini.
            Pembelian::create([
                'Kode_Item'   => $row['kode_item'],
                'Nama_Item'   => $row['nama_item'],
                'Jenis'       => $row['jenis'],
                'Jumlah'      => $row['jumlah'],
                'Satuan'      => $row['satuan'],
                'Total_Harga' => $row['total_harga'],
                'Bulan'       => $row['bulan'],
                'Tahun'       => $row['tahun']
            ]);
        }
    }

    // Proses file per 100 baris untuk efisiensi memori
    public function chunkSize(): int
    {
        return 100;
    }
}
