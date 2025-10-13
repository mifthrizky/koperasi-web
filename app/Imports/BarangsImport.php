<?php

namespace App\Imports;

use App\Models\Barang;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class BarangsImport implements ToCollection, WithHeadingRow, WithChunkReading
{
    /**
     * @param Collection $rows
     */
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // Gunakan Eloquent::updateOrCreate() yang menjamin event akan berjalan
            Barang::updateOrCreate(
                [
                    'Kode_Item' => $row['kode_item'] // Kolom unik untuk dicari
                ],
                [
                    'Nama_Item'    => $row['nama_item'], // Data untuk diupdate atau dibuat
                    'Jenis'        => $row['jenis'],
                ]
            );
        }
    }

    // Proses file per 100 baris untuk efisiensi memori
    public function chunkSize(): int
    {
        return 100;
    }
}
