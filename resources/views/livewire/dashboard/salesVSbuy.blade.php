<?php
use Livewire\Volt\Component;
use Illuminate\Support\Facades\DB;

new class extends Component
{
    /**
     * Helper untuk mengkonversi nama bulan menjadi angka untuk sorting kronologis.
     */
    private function getMonthNumber(string $monthName): int
    {
        $months = [
            'JANUARI' => 1, 'FEBRUARI' => 2, 'MARET' => 3, 'APRIL' => 4,
            'MEI' => 5, 'JUNI' => 6, 'JULI' => 7, 'AGUSTUS' => 8,
            'SEPTEMBER' => 9, 'OKTOBER' => 10, 'NOVEMBER' => 11, 'DESEMBER' => 12,
        ];
        // Pastikan nama bulan dicocokkan dalam format kapital (sesuai data Anda)
        return $months[strtoupper(trim($monthName))] ?? 0;
    }

    public function chartData()
    {
        $mongoDB = DB::getMongoDB();
        $mergedData = [];

        // --- 1. Aggregate Penjualan (Revenue) ---
        $penjualansCollection = $mongoDB->selectCollection('penjualans');
        $pipelineSales = [
            // Group by Bulan dan Tahun, sum Total_Harga
            ['$group' => [
                '_id' => ['Bulan' => '$Bulan', 'Tahun' => '$Tahun'],
                'Total' => ['$sum' => '$Total_Harga']
            ]],
            // Projecting fields
            ['$project' => [
                '_id' => 0, 'Bulan' => '$_id.Bulan', 'Tahun' => '$_id.Tahun', 'Total' => '$Total'
            ]]
        ];
        $salesResults = $penjualansCollection->aggregate($pipelineSales)->toArray();
        
        // --- 2. Aggregate Pembelian (Cost) ---
        $pembeliansCollection = $mongoDB->selectCollection('pembelians');
        $pipelinePurchases = [
            // Group by Bulan dan Tahun, sum Total_Harga
            ['$group' => [
                '_id' => ['Bulan' => '$Bulan', 'Tahun' => '$Tahun'],
                'Total' => ['$sum' => '$Total_Harga']
            ]],
            // Projecting fields
            ['$project' => [
                '_id' => 0, 'Bulan' => '$_id.Bulan', 'Tahun' => '$_id.Tahun', 'Total' => '$Total'
            ]]
        ];
        $purchaseResults = $pembeliansCollection->aggregate($pipelinePurchases)->toArray();

        // --- 3. Merge and Standardize Data ---
        // Proses Penjualan
        foreach ($salesResults as $item) {
            // Buat kunci periode untuk sorting: YYYY-MM
            $periodKey = $item['Tahun'] . '-' . str_pad($this->getMonthNumber($item['Bulan']), 2, '0', STR_PAD_LEFT);
            $mergedData[$periodKey]['Periode'] = strtoupper($item['Bulan']) . '-' . $item['Tahun'];
            $mergedData[$periodKey]['Penjualan'] = $item['Total'];
            $mergedData[$periodKey]['Pembelian'] = $mergedData[$periodKey]['Pembelian'] ?? 0; // Inisialisasi
        }

        // Proses Pembelian
        foreach ($purchaseResults as $item) {
            $periodKey = $item['Tahun'] . '-' . str_pad($this->getMonthNumber($item['Bulan']), 2, '0', STR_PAD_LEFT);
            $mergedData[$periodKey]['Periode'] = strtoupper($item['Bulan']) . '-' . $item['Tahun'];
            $mergedData[$periodKey]['Pembelian'] = $item['Total'];
            $mergedData[$periodKey]['Penjualan'] = $mergedData[$periodKey]['Penjualan'] ?? 0; // Inisialisasi
        }

        // --- 4. Sort Chronologically (berdasarkan periodKey) ---
        ksort($mergedData);

        // --- 5. Final Formatting ---
        return array_values($mergedData);
    }

    public function with()
    {
        $data = $this->chartData();

        return [
            'chartData' => $data,
            'hasData' => count($data) > 0,
        ];
    }
}
?>

<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">
            Tren Penjualan dan Pembelian Bulanan/Tahunan
        </h5>
    </div>
    <div class="card-body" style="height: fit-content;">
        @if($hasData)
            <div wire:ignore.self>
                {{-- Pembungkus untuk scroll horizontal --}}
                <div style="overflow-x: auto; padding-bottom: 10px;">
                    {{-- Container dengan lebar minimum agar memicu scroll --}}
                    <div style="min-width: 800px; height: 400px;">
                        <canvas id="trendLineChart"></canvas>
                    </div>
                </div>
            </div>
        @else
            <div class="alert alert-info mb-0">
                <i class="bi bi-info-circle me-2"></i>
                Tidak ada data Penjualan atau Pembelian untuk ditampilkan.
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let trendLineChart = null;
    const initialChartData = @json($chartData); 

    function renderChart(chartData) {
        const ctx = document.getElementById('trendLineChart');
        
        if (!ctx || !chartData || chartData.length === 0) {
            if (trendLineChart) {
                trendLineChart.destroy(); 
                trendLineChart = null;
            }
            return;
        }

        const labels = chartData.map(item => item.Periode);
        const penjualanData = chartData.map(item => item.Penjualan);
        const pembelianData = chartData.map(item => item.Pembelian);

        if (trendLineChart) {
            trendLineChart.destroy();
            trendLineChart = null;
        }

        trendLineChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Penjualan', 
                        data: penjualanData,
                        borderColor: 'rgba(75, 192, 192, 1)', // Biru/Hijau
                        backgroundColor: 'rgba(75, 192, 192, 0.5)',
                        borderWidth: 2,
                        fill: false, // Hanya garis
                        tension: 0.3 // Garis agak melengkung (smooth)
                    },
                    {
                        label: 'Pembelian', 
                        data: pembelianData,
                        borderColor: 'rgba(255, 99, 132, 1)', // Merah
                        backgroundColor: 'rgba(255, 99, 132, 0.5)',
                        borderWidth: 2,
                        fill: false,
                        tension: 0.3
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false, // Penting untuk scrollable chart
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.dataset.label || '';
                                const value = context.parsed.y || 0;
                                // Format nilai ke format mata uang Rupiah
                                return `${label}: Rp ${value.toLocaleString('id-ID')}`; 
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Periode (Bulan-Tahun)'
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Total Harga/Biaya (Rp)'
                        },
                        beginAtZero: true // Skala dimulai dari nol
                    }
                }
            }
        });
        console.log('Trend Line Chart created');
    }

    renderChart(initialChartData);
});
</script>
@endpush