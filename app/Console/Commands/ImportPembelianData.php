<?php

namespace App\Console\Commands;

use App\Models\Pembelian;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class ImportPembelianData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:pembelian'; // Nama perintah

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import data pembelian dari file JSON ke MongoDB';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memulai proses impor data pembelian...');

        try {
            // Hapus data lama agar tidak duplikat setiap kali import
            Pembelian::truncate();

            $filePath = storage_path('app/data/pembelian_2025.json');

            if (!File::exists($filePath)) {
                $this->error('File data pembelian.json tidak ditemukan!');
                return 1; // Keluar dengan status error
            }

            $jsonContent = File::get($filePath);

            // File format JSONL (JSON per baris), jadi kita proses per baris
            $lines = explode("\n", $jsonContent);

            $progressBar = $this->output->createProgressBar(count($lines));
            $progressBar->start();

            foreach ($lines as $line) {
                if (trim($line) === '') {
                    $progressBar->advance();
                    continue;
                }

                $data = json_decode($line, true);

                // Pastikan data tidak null setelah decode
                if (is_array($data)) {
                    // Ubah tipe data 'Jumlah' dan 'Total_Harga' ke numerik
                    $data['Jumlah'] = (int) $data['Jumlah'];
                    $data['Total_Harga'] = (int) $data['Total_Harga'];

                    Pembelian::create($data);
                }

                $progressBar->advance();
            }

            $progressBar->finish();
            $this->info("\nImpor data pembelian berhasil diselesaikan!");
            return 0; // Sukses

        } catch (\Exception $e) {
            Log::error('Gagal impor data pembelian: ' . $e->getMessage());
            $this->error("\nTerjadi kesalahan: " . $e->getMessage());
            return 1; // Gagal
        }
    }
}
