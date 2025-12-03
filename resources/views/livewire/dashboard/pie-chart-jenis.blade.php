<?php

use Livewire\Volt\Component;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;

new class extends Component
{
    public string $filterMonth = '';
    public string $filterYear = '';

    // Listen ke event dari komponen filter
    #[On('filter-updated')]
    public function updateFilter($month, $year)
    {
        $this->filterMonth = $month;
        $this->filterYear = $year;

        // Dispatch event untuk update chart dengan data baru
        $this->dispatch('pie-chart-data-updated', chartData: $this->chartData());
    }

    #[Computed]
    public function chartData()
    {
        $collection = DB::getMongoDB()->selectCollection('penjualans');

        // Build Filter berdasarkan bulan dan tahun yang dipilih
        $matchFilter = [];

        if (!empty($this->filterMonth)) {
            $matchFilter['Bulan'] = strtoupper(trim($this->filterMonth));
        }

        if (!empty($this->filterYear)) {
            $matchFilter['Tahun'] = (int) $this->filterYear;
        }

        // Aggregation pipeline untuk menghitung total jumlah per jenis
        $pipeline = [];

        // Tambahkan $match jika ada filter
        if (!empty($matchFilter)) {
            $pipeline[] = ['$match' => $matchFilter];
        }

        // Group by Jenis dan sum Jumlah
        $pipeline[] = [
            '$group' => [
                '_id' => '$Jenis',
                'totalJumlah' => ['$sum' => '$Jumlah']
            ]
        ];

        // Sort descending berdasarkan totalJumlah
        $pipeline[] = [
            '$sort' => ['totalJumlah' => -1]
        ];

        // Project untuk format hasil
        $pipeline[] = [
            '$project' => [
                '_id' => 0,
                'jenis' => '$_id',
                'total_jumlah' => '$totalJumlah'
            ]
        ];

        $results = $collection->aggregate($pipeline)->toArray();

        return $results;
    }

    public function with(): array
    {
        $data = $this->chartData();

        return [
            'chartData' => $data,
            'hasData' => count($data) > 0,
            'filterMonth' => $this->filterMonth,
            'filterYear' => $this->filterYear,
        ];
    }
};
?>

<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">
            Grafik Jumlah Terjual per Jenis
            @if($filterMonth || $filterYear)
            <small class="text-muted">
                ({{ $filterMonth ?: 'Semua Bulan' }} {{ $filterYear ?: 'Semua Tahun' }})
            </small>
            @endif
        </h5>
    </div>
    <div class="card-body" style="height: fit-content;">
        @if($hasData)
        <div wire:ignore.self>
            <canvas id="jenisPieChart" style="max-height: 400px;"></canvas>
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
        let jenisPieChart = null;

        function renderChart(chartData) {
            const ctx = document.getElementById('jenisPieChart');

            if (!ctx || !chartData || chartData.length === 0) {
                return;
            }

            const labels = chartData.map(item => item.jenis);
            const values = chartData.map(item => item.total_jumlah);

            const colors = [
                '#FF6384', '#36A2EB', '#FFCE56',
                '#4BC0C0', '#9966FF', '#FF9F40',
                '#8E44AD', '#2ECC71', '#E67E22',
                '#1ABC9C', '#C0392B', '#7D3C98'
            ];

            // Jika chart sudah ada, update datanya
            if (jenisPieChart) {
                jenisPieChart.data.labels = labels;
                jenisPieChart.data.datasets[0].data = values;
                jenisPieChart.data.datasets[0].backgroundColor = colors.slice(0, labels.length);
                jenisPieChart.update();
                console.log('Chart updated with new data');
            } else {
                // Create chart pertama kali
                jenisPieChart = new Chart(ctx, {
                    type: 'pie',
                    data: {
                        labels: labels,
                        datasets: [{
                            data: values,
                            backgroundColor: colors.slice(0, labels.length),
                            borderWidth: 2,
                            borderColor: '#fff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: {
                                position: 'right',
                                labels: {
                                    padding: 15,
                                    font: {
                                        size: 12
                                    }
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const label = context.label || '';
                                        const value = context.parsed || 0;
                                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        const percentage = ((value / total) * 100).toFixed(1);
                                        return `${label}: ${value.toLocaleString()} (${percentage}%)`;
                                    }
                                }
                            }
                        }
                    }
                });
                console.log('Chart created');
            }
        }

        // Render chart pertama kali
        renderChart(@json($chartData));

        // Listen event dari Livewire
        Livewire.on('pie-chart-data-updated', (event) => {
            console.log('Received chart update event:', event);
            renderChart(event.chartData);
        });
    });
</script>
@endpush