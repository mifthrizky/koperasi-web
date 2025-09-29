<?php

namespace App\Console\Commands;

use App\Models\Retur; // Ganti ke model Retur
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class ImportReturData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:retur';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import data retur dari file JSON ke MongoDB';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memulai proses impor data retur...');

        try {
            // Hapus data lama agar tidak duplikat
            Retur::truncate();

            // Arahkan ke file retur_2025.json
            $filePath = storage_path('app/data/retur_2025.json');

            if (!File::exists($filePath)) {
                $this->error('File data retur_2025.json tidak ditemukan!');
                return 1;
            }

            $jsonContent = File::get($filePath);
            $lines = explode("\n", $jsonContent);

            $progressBar = $this->output->createProgressBar(count($lines));
            $progressBar->start();

            foreach ($lines as $line) {
                if (trim($line) === '') {
                    $progressBar->advance();
                    continue;
                }

                $data = json_decode($line, true);

                if (is_array($data)) {
                    // **BAGIAN PENTING: Mapping Kolom**
                    // Kita buat array baru untuk memastikan data sesuai dengan model
                    $mappedData = [
                        'No' => (int) $data['No'],
                        'Kode_Item' => $data['Kode_Item'],
                        'Nama_Item' => $data['Nama_Item'],
                        'Jumlah' => (int) $data['Jml'], // Ambil dari 'Jml', simpan sebagai 'Jumlah'
                        'Satuan' => $data['Satuan'],
                        'Harga' => (int) $data['Harga'],
                        'Pot._%' => (int) $data['Pot. %'],
                        'Total_Harga' => (int) $data['Total_Harga'],
                        'Bulan' => $data['Bulan'],
                        'Tahun' => (int) $data['Tahun'],
                    ];

                    // Gunakan Model Retur dengan data yang sudah di-mapping
                    Retur::create($mappedData);
                }

                $progressBar->advance();
            }

            $progressBar->finish();
            $this->info("\nImpor data retur berhasil diselesaikan!");
            return 0;
        } catch (\Exception $e) {
            Log::error('Gagal impor data retur: ' . $e->getMessage());
            $this->error("\nTerjadi kesalahan: " . $e->getMessage());
            return 1;
        }
    }
}
