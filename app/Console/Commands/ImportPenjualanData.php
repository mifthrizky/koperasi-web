<?php

namespace App\Console\Commands;

use App\Models\Penjualan; // <-- Ganti ke model Penjualan
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class ImportPenjualanData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:penjualan'; // <-- Ganti nama signature

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import data penjualan dari file JSON ke MongoDB'; // <-- Ganti deskripsi

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memulai proses impor data penjualan...');

        try {
            // Hapus data lama agar tidak duplikat
            Penjualan::truncate();

            // Arahkan ke file penjualan.json
            $filePath = storage_path('app/data/penjualan_2025.json');

            if (!File::exists($filePath)) {
                $this->error('File data penjualan.json tidak ditemukan!');
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
                    // Ubah tipe data jika perlu (opsional, tapi baik dilakukan)
                    $data['Jumlah'] = (int) $data['Jumlah'];
                    $data['Total_Harga'] = (int) $data['Total_Harga'];

                    // Gunakan Model Penjualan
                    Penjualan::create($data);
                }

                $progressBar->advance();
            }

            $progressBar->finish();
            $this->info("\nImpor data penjualan berhasil diselesaikan!");
            return 0;
        } catch (\Exception $e) {
            Log::error('Gagal impor data penjualan: ' . $e->getMessage());
            $this->error("\nTerjadi kesalahan: " . $e->getMessage());
            return 1;
        }
    }
}
