<?php

use Livewire\Volt\Component;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;

new class extends Component
{

    public function chartData()
    {
        $mongoDB = DB::getMongoDB();

        // koleksi utama yang di-aggregate
        $collection = $mongoDB->selectCollection('stock_opnames');

        // Aggregation pipeline 
        $pipeline = [];

        // Mencari Jenis dengan lookup yang sudah dikonversi
        $pipeline[] = [
            '$lookup' => [
                'from' => 'barangs',
                'let' => ['kode_item_string' => '$Kode_Item'],
                'pipeline' => [
                    [
                        '$match' => [
                            '$expr' => [
                                '$eq' => [
                                    ['$toString' => '$Kode_Item'],
                                    '$$kode_item_string'
                                ]
                            ]
                        ]
                    ]
                ],
                'as' => 'info_item'
            ]
        ];

        $pipeline[] = [
            '$addFields' => [
                // Ambil field Jenis dari record pertama hasil lookup (karena Jenis harusnya sama)
                'Jenis' => [
                    '$arrayElemAt' => ['$info_item.Jenis', 0]
                ]
            ]
        ];

        // Filtering: Hapus dokumen yang Kode_Item-nya tidak ditemukan Jenis-nya di koleksi 'barangs'
        $pipeline[] = [
            '$match' => [
                'Jenis' => ['$exists' => true, '$ne' => null]
            ]
        ];

        // Group by Jenis dan sum Jumlah
        $pipeline[] = [
            '$group' => [
                '_id' => '$Jenis',
                'stokMasuk' => ['$sum' => '$Stok_Masuk'],
                'stokKeluar' => ['$sum' => '$Stok_Keluar']
            ]
        ];

        // menambah netstock untuk sort
        $pipeline[] = [
            '$addFields' => [
                'NetStock' => ['$subtract' => ['$stokMasuk', '$stokKeluar']]
            ]
        ];

        // Sort descending berdasarkan totalJumlah
        $pipeline[] = [
            '$sort' => ['NetStock' => -1]
        ];

        // Project untuk format hasil
        $pipeline[] = [
            '$project' => [
                '_id' => 0,
                'jenis' => '$_id',
                'stok_masuk' => '$stokMasuk',
                'stok_keluar' => ['$multiply' => ['$stokKeluar', -1]],
            ]
        ];

        $results = $collection->aggregate($pipeline)->toArray();

        return array_map(function ($item) {
            return (array) $item;
        }, $results);
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
            Grafik Pergerakan Stok Item
        </h5>
    </div>
    <div class="card-body" style="height: fit-content;">
        @if($hasData)
        <div wire:ignore.self>
            <div style="overflow-x: auto; padding-bottom: 10px;">
                <div style="min-width: 1000px; height: 400px;">
                    <canvas id="jenisStackedBar"></canvas>
                </div>
            </div>
        </div>
        @else
        <div class="alert alert-info mb-0">
            <i class="bi bi-info-circle me-2"></i>
            Tidak ada data untuk ditampilkan
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        let jenisStackedBar = null;
        const initialChartData = @json($chartData);

        function renderChart(chartData) {
            const ctx = document.getElementById('jenisStackedBar');

            if (!ctx || !chartData || chartData.length === 0) {
                if (jenisStackedBar) {
                    jenisStackedBar.destroy(); // Hancurkan chart jika tidak ada data
                    jenisStackedBar = null;
                }
                return;
            }

            const labels = chartData.map(item => item.jenis);
            const stokMasuk = chartData.map(item => item.stok_masuk);
            const stokKeluar = chartData.map(item => item.stok_keluar);

            // (Baru ditambahkan) Hancurkan chart lama sebelum membuat yang baru jika ada perubahan besar
            if (jenisStackedBar) {
                jenisStackedBar.destroy();
                jenisStackedBar = null;
            }

            // Buat atau update chart
            jenisStackedBar = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                            label: 'Stok Masuk', // Dataset Pertama
                            data: stokMasuk,
                            backgroundColor: 'rgba(75, 192, 192, 0.8)', // Warna Hijau/Biru
                            borderColor: 'rgba(75, 192, 192, 1)',
                            borderWidth: 1,
                        },
                        {
                            label: 'Stok Keluar', // Dataset Kedua
                            data: stokKeluar,
                            backgroundColor: 'rgba(255, 99, 132, 0.8)', // Warna Merah
                            borderColor: 'rgba(255, 99, 132, 1)',
                            borderWidth: 1,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false, // Penting untuk kontrol height
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.dataset.label || '';
                                    const value = Math.abs(context.parsed.y) || 0;
                                    return `${label}: ${value.toLocaleString()}`;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            // Tidak perlu stacked:true di sini karena kita membagi data berdasarkan nilai positif/negatif
                        },
                        y: {
                            stacked: false, // JANGAN set ke true jika Anda ingin membagi Masuk/Keluar di sekitar garis nol
                            beginAtZero: false, // Mulai dari nol dinonaktifkan agar bar bisa ke negatif
                            // Opsional: Tambahkan Gridline di garis nol untuk penekanan visual
                            grid: {
                                zeroLineColor: '#000000', // Garis hitam di nol
                                zeroLineWidth: 2,
                            }
                        }
                    }
                }
            });
            console.log('centered Bar Chart created');
        }

        // Render chart pertama kali
        renderChart(initialChartData);
    });
</script>
@endpush