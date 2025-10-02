<?php
use Livewire\Volt\Component;
use Illuminate\Support\Facades\DB;

new class extends Component
{
    /**
     * Fungsi untuk menghitung Total Nilai Retur dari koleksi 'returs'.
     */
    public function totalReturValue()
    {
        $mongoDB = DB::getMongoDB();
        $collection = $mongoDB->selectCollection('returs');

        // Pipeline untuk menjumlahkan semua Total_Harga
        $pipeline = [
            [
                // Group semua dokumen menjadi satu untuk mendapatkan total keseluruhan
                '$group' => [
                    '_id' => null, // null untuk menjumlahkan semua dokumen
                    'TotalHargaRetur' => ['$sum' => '$Total_Harga']
                ]
            ],
            [
                // Project untuk memformat hasil
                '$project' => [
                    '_id' => 0,
                    'total_nilai' => '$TotalHargaRetur'
                ]
            ]
        ];

        $results = $collection->aggregate($pipeline)->toArray();

        // Ambil nilai total dan pastikan dikembalikan sebagai integer, default 0
        $total = $results[0]['total_nilai'] ?? 0;

        return (int)$total;
    }

    public function with()
    {
        $total = $this->totalReturValue();

        return [
            'totalReturValue' => $total,
            // Format total sebagai string Rupiah untuk ditampilkan di view
            'formattedValue' => 'Rp ' . number_format($total, 0, ',', '.'),
        ];
    }
}
?>

<div class="card">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-8">
                {{-- Nilai total yang sudah diformat Rupiah --}}
                <h3 class="text-muted">Total Nilai Retur</p></h3>
                <h3 class="mb-0 text-danger">{{ $formattedValue }}</h3>
            </div>
            <div class="col-4 text-end">
                {{-- Icon representasi Retur/Pengembalian --}}
                <i class="bi bi-arrow-return-left text-danger" style="font-size: 2.5rem;"></i>
            </div>
        </div>
        
        <small class="text-muted mt-2 d-block">
            Memantau total nilai barang yang dikembalikan (Tingkat Pengembalian).
        </small>
        
        {{-- Contoh opsional: Menambahkan Progress Bar sebagai visualisasi "Gauge" sederhana --}}
        @if ($totalReturValue > 0)
            <p class="text-muted mt-2 mb-1 small">Nilai Retur (batas retur 10.000.000)</p>
            <div class="progress" style="height: 5px;">
                <div 
                    class="progress-bar bg-danger" 
                    role="progressbar" 
                    {{-- Asumsi Max Value (Target) adalah 50 Juta Rupiah (Bisa disesuaikan) --}}
                    style="width: {{ min(100, ($totalReturValue / 10000000) * 100) }}%" 
                    aria-valuenow="{{ $totalReturValue }}" 
                    aria-valuemin="0" 
                    aria-valuemax="50000000">
                </div>
            </div>
        @endif
    </div>
</div>