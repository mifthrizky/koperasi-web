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
    }

    #[Computed]
    public function topItems()
    {
        $collection = DB::getMongoDB()->selectCollection('penjualans');
        
        // Build filter berdasarkan bulan dan tahun yang dipilih
        $matchFilter = [];
        
        if (!empty($this->filterMonth)) {
            $matchFilter['Bulan'] = strtoupper(trim($this->filterMonth));
        }
        
        if (!empty($this->filterYear)) {
            $matchFilter['Tahun'] = (int) $this->filterYear;
        }

        // Aggregation pipeline untuk menghitung total jumlah per item
        $pipeline = [];
        
        // Tambahkan $match jika ada filter
        if (!empty($matchFilter)) {
            $pipeline[] = ['$match' => $matchFilter];
        }
        
        // Group by Nama_Item dan sum Jumlah
        $pipeline[] = [
            '$group' => [
                '_id' => '$Nama_Item',
                'totalJumlah' => ['$sum' => '$Jumlah'],
                'satuan' => ['$first' => '$Satuan']
            ]
        ];
        
        // Sort descending berdasarkan totalJumlah
        $pipeline[] = [
            '$sort' => ['totalJumlah' => -1]
        ];
        
        // Limit 10 item teratas
        $pipeline[] = ['$limit' => 26];
        
        // Project untuk format hasil
        $pipeline[] = [
            '$project' => [
                '_id' => 0,
                'nama_item' => '$_id',
                'total_jumlah' => '$totalJumlah',
                'satuan' => '$satuan'
            ]
        ];

        $results = $collection->aggregate($pipeline)->toArray();
        
        return $results;
    }


    public function with(): array
    {
        return [
            'topItems' => $this->topItems(),
        ];
    }
};
?>

<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">
            Top Barang Terlaris
            @if($filterMonth || $filterYear)
                <small class="text-muted">
                    ({{ $filterMonth ?: 'Semua Bulan' }} {{ $filterYear ?: 'Semua Tahun' }})
                </small>
            @endif
        </h5>
    </div>
    <div class="card-body">
        @if(count($topItems) > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th style="width: 50px;">Rank</th>
                            <th>Nama Item</th>
                            <th class="text-end">Total Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($topItems as $index => $item)
                            <tr>
                                <td>
                                    <span class="badge 
                                        @if($index === 0) bg-warning
                                        @elseif($index === 1) bg-secondary
                                        @elseif($index === 2) bg-info
                                        @else bg-light text-dark
                                        @endif
                                    ">
                                        {{ $index + 1 }}
                                    </span>
                                </td>
                                <td>{{ $item->nama_item }}</td>
                                <td class="text-end">
                                    <strong>{{ number_format($item->total_jumlah) }} {{ trim($item->satuan) }}</strong>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="alert alert-info mb-0">
                <i class="bi bi-info-circle me-2"></i>
                Tidak ada data untuk ditampilkan
            </div>
        @endif
    </div>
</div>