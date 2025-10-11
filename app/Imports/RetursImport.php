<?php

namespace App\Imports;

use App\Models\Retur;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class RetursImport implements ToCollection, WithHeadingRow, WithChunkReading
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
            if (empty($row['kd_item'])) {
                continue; // Lewati baris jika kode_item kosong
            }

            Retur::create([
                'Kode_Item' => $row['kd_item'],
                'Nama_Item' => $row['nama_item'],
                'Jumlah'    => $row['jml'],
                'Satuan'    => $row['satuan'],
                'Harga'     => $row['harga'],
                'Potongan'  => $row['pot'],     // "Pot. %" → jadi 'pot'
                'Total_Harga' => $row['total'],
                'Bulan'     => $this->month,
                'Tahun'     => $this->year,
            ]);
        }
    }

    // Proses file per 100 baris untuk efisiensi memori
    public function chunkSize(): int
    {
        return 100;
    }
}
