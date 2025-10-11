<?php

namespace App\Imports;

use App\Models\StockOpname;
use App\Models\Barang;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class StockOpnamesImport implements ToCollection, WithHeadingRow, WithChunkReading
{
    private string $month;
    private int $year;
    private string $petugas;

    public function __construct(string $month, int $year, string $petugas)
    {
        $this->month = $month;
        $this->year = $year;
        $this->petugas = $petugas;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // Trim untuk menghapus spasi ekstra dari nilai kolom
            $kodeItem = trim($row['kode_item'] ?? null);
            $qtySo = trim($row['qty_so'] ?? null);

            if (empty($kodeItem)) {
                continue; // Lewati baris jika kode_item kosong
            }

            // Ambil Nama_Item dari model Barang untuk pengisian data baru
            $barang = Barang::where('Kode_Item', $kodeItem)->first();

            // Gunakan updateOrCreate untuk membuat record jika belum ada
            StockOpname::updateOrCreate(
                [
                    'Kode_Item' => $kodeItem,
                    'Bulan'     => $this->month,
                    'Tahun'     => $this->year,
                ],
                [
                    'Nama_Item'    => $barang->Nama_Item ?? 'Nama Tidak Ditemukan',
                    'Stock_Opname' => is_numeric($qtySo) ? (int)$qtySo : 0, // Pastikan hanya angka yang masuk
                    'petugas'      => $this->petugas,
                ]
            );
        }
    }

    public function chunkSize(): int
    {
        return 100;
    }
}
