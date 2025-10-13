<?php
use Livewire\Volt\Component;
use Illuminate\Support\Facades\DB;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public int $perPage = 10;
    public int $page = 1;
    public string $pageInput = '';
    public array $lowStockItems = [];
    public int $totalPages = 1;

    public function mount()
    {
        $this->updateLowStockList();
    }

    private function updateLowStockList()
    {
        $mongo = DB::getMongoDB();
        $stockCollection = $mongo->selectCollection('stock_opnames');
        $returCollection = $mongo->selectCollection('returs');

        // === Aggregate stok masuk & keluar ===
        $stockPipeline = [
            [
                '$group' => [
                    '_id' => '$Kode_Item',
                    'Nama_Item' => ['$first' => '$Nama_Item'],
                    'stok_masuk' => ['$sum' => '$Stok_Masuk'],
                    'stok_keluar' => ['$sum' => '$Stok_Keluar']
                ]
            ],
            [
                '$project' => [
                    'Kode_Item' => '$_id',
                    'Nama_Item' => 1,
                    'stok_masuk' => 1,
                    'stok_keluar' => 1
                ]
            ]
        ];

        $stockData = $stockCollection->aggregate($stockPipeline)->toArray();

        // === Ambil retur ===
        $returPipeline = [
            [
                '$group' => [
                    '_id' => '$Kode_Item',
                    'stok_retur' => ['$sum' => '$Kuantitas']
                ]
            ]
        ];

        $returData = $returCollection->aggregate($returPipeline)->toArray();
        $returMap = [];
        foreach ($returData as $item) {
            $returMap[$item->_id] = $item->stok_retur;
        }

        // === Hitung stok akhir ===
        $final = [];
        foreach ($stockData as $item) {
            $stokRetur = $returMap[$item->Kode_Item ?? $item->_id] ?? 0;
            $stokAkhir = ($item->stok_masuk ?? 0) - ($item->stok_keluar ?? 0) - $stokRetur;

            $final[] = [
                'Kode_Item' => $item->Kode_Item ?? $item->_id,
                'Nama_Item' => $item->Nama_Item ?? 'Tidak diketahui',
                'stok_akhir' => max($stokAkhir, 0),
            ];
        }

        // Urutkan stok terkecil ke terbesar
        usort($final, fn($a, $b) => $a['stok_akhir'] <=> $b['stok_akhir']);

        // Pagination manual
        $offset = ($this->page - 1) * $this->perPage;
        $pagedData = array_slice($final, $offset, $this->perPage);

        $this->lowStockItems = $pagedData;
        $this->totalPages = ceil(count($final) / $this->perPage);

        // 🔧 Sinkronisasi halaman input setiap kali data diperbarui
        $this->pageInput = (string) $this->page;
    }

    public function previousPage()
    {
        if ($this->page > 1) {
            $this->page--;
            $this->updateLowStockList();
            $this->pageInput = (string) $this->page;
            $this->dispatch('page-updated', page: $this->page); // 🔹 kirim event ke frontend
        }
    }

    public function nextPage()
    {
        if ($this->page < $this->totalPages) {
            $this->page++;
            $this->updateLowStockList();
            $this->pageInput = (string) $this->page;
            $this->dispatch('page-updated', page: $this->page); // 🔹 kirim event ke frontend
        }
    }

    public function goToPage()
    {
        $page = (int) $this->pageInput;
        if ($page >= 1 && $page <= $this->totalPages) {
            $this->page = $page;
            $this->updateLowStockList();
        } else {
            // kembalikan ke halaman valid
            $this->pageInput = (string) $this->page;
        }
    }

    public function with(): array
    {
        return [
            'lowStockItems' => $this->lowStockItems,
            'page' => $this->page,
            'totalPages' => $this->totalPages,
            'pageInput' => $this->pageInput,
        ];
    }
};
?>

<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">
            Barang Hampir Habis
            <small class="text-muted">(Stok Rendah)</small>
        </h5>
    </div>

    <div class="card-body">
        @if(count($lowStockItems) > 0)

            <!-- 🔹 Info Halaman di Atas List -->
            <div class="d-flex justify-content-end align-items-center mb-2">
                <small class="text-muted">
                    Menampilkan halaman <strong>{{ $page }}</strong> dari {{ $totalPages }}
                </small>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th style="width: 50px;">No</th>
                            <th>Nama Item</th>
                            <th>Kode Item</th>
                            <th class="text-end">Stok Akhir</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($lowStockItems as $index => $item)
                            <tr>
                                <td>
                                    <span class="badge bg-light text-dark">
                                        {{ (($page - 1) * 10) + $index + 1 }}
                                    </span>
                                </td>
                                <td>{{ $item['Nama_Item'] }}</td>
                                <td><small class="text-muted">{{ $item['Kode_Item'] }}</small></td>
                                <td class="text-end">
                                    <span class="badge 
                                        {{ $item['stok_akhir'] <= 5 ? 'bg-danger' : ($item['stok_akhir'] <= 15 ? 'bg-warning text-dark' : 'bg-success') }}">
                                        {{ $item['stok_akhir'] }} unit
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- 🔹 Navigasi Pagination -->
            <div class="d-flex justify-content-between align-items-center px-3 py-2 border-top small">
                <button wire:click="previousPage"
                    class="btn btn-sm btn-outline-secondary px-2 py-1"
                    style="font-size: 12px;"
                    @disabled($page <= 1)>
                    ← Sebelumnya
                </button>

                <div class="d-flex align-items-center gap-1">
                    <span>Halaman</span>
                    <input type="number"
                        wire:model.defer="pageInput"
                        wire:keydown.enter="goToPage"
                        min="1" max="{{ $totalPages }}"
                        class="form-control form-control-sm text-center"
                        style="width: 55px; height: 26px; font-size: 12px; padding: 0;"
                        placeholder="{{ $page }}" />
                    <span>dari {{ $totalPages }}</span>
                </div>

                <button wire:click="nextPage"
                    class="btn btn-sm btn-outline-secondary px-2 py-1"
                    style="font-size: 12px;"
                    @disabled($page >= $totalPages)>
                    Selanjutnya →
                </button>
            </div>
        @else
            <div class="alert alert-info mb-0">
                <i class="bi bi-info-circle me-2"></i>
                Tidak ada data stok yang rendah
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('livewire:load', () => {
        Livewire.on('page-updated', ({ page }) => {
            const input = document.querySelector('input[wire\\:model="pageInput"]');
            if (input) input.value = page;
        });
    });
</script>
@endpush
