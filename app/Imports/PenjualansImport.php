<?php

namespace App\Imports;

use App\Models\Penjualan;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class PenjualansImport implements ToCollection, WithHeadingRow, WithChunkReading
{
    private string $month;
    private int $year;


    public function __construct(string $month, int $year)
    {
        $this->month = $month;
        $this->year = $year;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            if (empty($row['kode_item'])) {
                continue;
            }

            Penjualan::create([
                'Kode_Item'   => $row['kode_item'],
                'Nama_Item'   => $row['nama_item'],
                'Jenis'       => $row['jenis'],
                'Merek'       => $row['merek'] ?? null,
                'Jumlah'      => $row['jumlah'],
                'Satuan'      => $row['satuan'],
                'Total_Harga' => $row['total_harga'],
                'Bulan'       => $this->month,
                'Tahun'       => $this->year,
            ]);
        }
    }

    // Proses file per 100 baris untuk efisiensi memori
    public function chunkSize(): int
    {
        return 100;
    }
}
