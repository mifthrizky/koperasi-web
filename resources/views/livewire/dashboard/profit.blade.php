<?php

use Livewire\Volt\Component;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;

new class extends Component
{
    public string $filterMonth = '';
    public string $filterYear = '';

    private const MONTHS_ORDER = [
        'JANUARI',
        'FEBRUARI',
        'MARET',
        'APRIL',
        'MEI',
        'JUNI',
        'JULI',
        'AGUSTUS',
        'SEPTEMBER',
        'OKTOBER',
        'NOVEMBER',
        'DESEMBER'
    ];

    #[On('filter-updated')]
    public function updateFilter($month, $year)
    {
        $this->filterMonth = $month;
        $this->filterYear = $year;
    }

    #[Computed]
    public function currentProfit()
    {
        return $this->calculateProfit($this->filterMonth, $this->filterYear);
    }

    #[Computed]
    public function profitPercentage()
    {
        // Jika tidak ada filter bulan dan tahun, return 0
        if (empty($this->filterMonth) || empty($this->filterYear)) {
            return 0;
        }

        $previousMonth = $this->getPreviousMonth();

        // Jika tidak ada bulan sebelumnya (misal Januari), return 0
        if (!$previousMonth) {
            return 0;
        }

        $previousProfit = $this->calculateProfit($previousMonth, $this->filterYear);
        $currentProfit = $this->currentProfit();

        // Hindari division by zero
        if ($previousProfit == 0) {
            return $currentProfit > 0 ? 100 : 0;
        }

        return (($currentProfit - $previousProfit) / abs($previousProfit)) * 100;
    }

    #[Computed]
    public function percentageColor()
    {
        $percentage = $this->profitPercentage();

        if ($percentage > 0) {
            return 'text-success'; // Merah untuk profit naik (untung)
        } elseif ($percentage < 0) {
            return 'text-danger'; // Biru untuk profit turun (rugi)
        }

        return 'text-secondary'; // Abu-abu untuk tidak ada perubahan (0)
    }

    private function calculateProfit(string $month, string $year): float
    {
        $mongodb = DB::getMongoDB();

        // Build filter
        $filter = [];
        if (!empty($month)) {
            $filter['Bulan'] = strtoupper(trim($month));
        }
        if (!empty($year)) {
            $filter['Tahun'] = (int) $year;
        }

        // Build pipeline - hanya tambahkan $match jika ada filter
        $buildPipeline = function () use ($filter) {
            $pipeline = [];

            // Hanya tambahkan $match jika filter tidak kosong
            if (!empty($filter)) {
                $pipeline[] = ['$match' => $filter];
            }

            $pipeline[] = [
                '$group' => [
                    '_id' => null,
                    'total' => ['$sum' => '$Total_Harga']
                ]
            ];

            return $pipeline;
        };

        // Total Penjualan
        $penjualanResult = $mongodb->selectCollection('penjualans')
            ->aggregate($buildPipeline())
            ->toArray();

        $totalPenjualan = !empty($penjualanResult) ? $penjualanResult[0]->total : 0;

        // Total Pembelian
        $pembelianResult = $mongodb->selectCollection('pembelians')
            ->aggregate($buildPipeline())
            ->toArray();

        $totalPembelian = !empty($pembelianResult) ? $pembelianResult[0]->total : 0;

        // Total Retur (jika ada collection returs)
        try {
            $returResult = $mongodb->selectCollection('returs')
                ->aggregate($buildPipeline())
                ->toArray();

            $totalRetur = !empty($returResult) ? $returResult[0]->total : 0;
        } catch (\Exception $e) {
            // Jika collection returs tidak ada
            $totalRetur = 0;
        }

        // Keuntungan = (Penjualan + Retur) - Pembelian
        return ($totalPenjualan + $totalRetur) - $totalPembelian;
    }

    private function getPreviousMonth(): ?string
    {
        if (empty($this->filterMonth)) {
            return null;
        }

        $currentMonth = strtoupper(trim($this->filterMonth));
        $currentIndex = array_search($currentMonth, self::MONTHS_ORDER);

        // Jika bulan tidak ditemukan atau sudah Januari
        if ($currentIndex === false || $currentIndex === 0) {
            return null;
        }

        return self::MONTHS_ORDER[$currentIndex - 1];
    }

    public function with(): array
    {
        $percentage = $this->profitPercentage();

        return [
            'currentProfit' => $this->currentProfit(),
            'percentage' => round($percentage, 2),
            'percentageColor' => $this->percentageColor(),
            'hasFilter' => !empty($this->filterMonth) && !empty($this->filterYear),
            'filterMonth' => $this->filterMonth,
            'filterYear' => $this->filterYear,
        ];
    }
};
?>

<div class="card text-black h-100 shadow-sm">
    <div class="card-body">
        <!-- Header dengan Judul dan Persentase -->
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="text-muted mb-0">Keuntungan</h6>

            <span class="fw-semibold {{ $percentageColor }}">
                {{ $percentage > 0 ? '+' : '' }}{{ number_format($percentage, 2) }}%
            </span>
        </div>

        <!-- Sub label filter -->
        @if($filterMonth || $filterYear)
        <small class="text-muted d-block mb-3">
            {{ $filterMonth ?: 'Semua' }} {{ $filterYear ?: '' }}
        </small>
        @else
        <small class="text-muted d-block mb-3">Keseluruhan</small>
        @endif

        <!-- Nilai Keuntungan -->
        <h3 class="mb-0 fw-bold {{ $currentProfit < 0 ? 'text-danger' : 'text-dark' }}">
            Rp {{ number_format(abs($currentProfit), 0, ',', '.') }}
        </h3>

        @if($currentProfit < 0)
            <small class="text-danger">
            <i class="bi bi-exclamation-triangle-fill me-1"></i>Rugi
            </small>
            @endif
    </div>
</div>