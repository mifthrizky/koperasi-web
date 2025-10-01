<?php

use Livewire\Volt\Component;
use App\Models\StockOpname;
use Livewire\WithPagination;
use Illuminate\Pagination\Paginator;
use Livewire\Attributes\Rule;

new class extends Component
{
    use WithPagination;

    public string $search = '';
    public string $sortBy = 'Kode_Item';
    public string $sortDirection = 'asc';
    public bool $isSoActive = false;

    #[Rule('array')]
    public array $soData = [];

    #[Rule('nullable|integer|min:0', as: 'Stok Fisik', onUpdate: false)]
    public $stokFisikValue;

    #[Rule('nullable|string', as: 'Keterangan', onUpdate: false)]
    public $keteranganValue;

    public array $keteranganOptions = [
        'Kesalahan Catat',
        'Hilang',
        'Rusak',
        'Lain-lain',
    ];

    public function boot()
    {
        Paginator::useBootstrap();
    }

    public function mount()
    {
        $this->loadSoData();
    }

    private function loadSoData()
    {
        $items = StockOpname::select('id', 'Stock_Opname', 'Keterangan')->get();
        foreach ($items as $item) {
            $this->soData[$item->id] = [
                'fisik'      => $item->Stock_Opname ?? '',
                'keterangan' => $item->Keterangan ?? '',
            ];
        }
    }

    public function toggleSoMode()
    {
        if ($this->isSoActive) {
            $this->loadSoData();
            $this->resetValidation();
            session()->flash('info', 'Stock opname dibatalkan, data input dikembalikan.');
        } else {
            session()->flash('success', 'Mode stock opname aktif. Silakan isi stok fisik.');
        }
        $this->isSoActive = !$this->isSoActive;
    }

    public function updatedSoData($value, $key)
    {
        [$id, $field] = explode('.', $key);

        if ($field === 'fisik') {
            $this->stokFisikValue = $value;
            $this->validateOnly('stokFisikValue');
        }
    }

    public function saveStockOpname()
    {
        $this->validate([
            'soData.*.fisik' => 'nullable|integer|min:0',
            'soData.*.keterangan' => 'nullable|string|max:255',
        ]);

        $updatedCount = 0;

        foreach ($this->soData as $id => $data) {
            $stokFisik = $data['fisik'];

            if (is_numeric($stokFisik) && $stokFisik !== '') {
                $item = StockOpname::find($id);
                if ($item) {
                    $item->Stock_Opname = (int) $stokFisik;
                    $item->Keterangan = $data['keterangan'] ?: null;
                    $item->save();
                    $updatedCount++;
                }
            }
        }

        $this->isSoActive = false;
        $this->loadSoData();
        session()->flash('success', "Stock Opname berhasil disimpan! ($updatedCount item diperbarui)");
        $this->resetPage();
    }

    public function sort($column)
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }
        $this->sortBy = $column;
        $this->resetPage();
    }

    public function updating($property)
    {
        if (in_array($property, ['search'])) {
            $this->resetPage();
        }
    }

    /**
     * Query ini mengambil data seperti biasa. Kalkulasi stok sistem
     * akan ditangani secara otomatis oleh Accessor di Model.
     */
    public function with(): array
    {
        $stockOpnames = StockOpname::query()
            ->when($this->search, function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery->where('Nama_Item', 'like', '%' . $this->search . '%');
                    if (is_numeric($this->search)) {
                        $subQuery->orWhere('Kode_Item', (int) $this->search);
                    }
                });
            })
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(10);

        foreach ($stockOpnames as $item) {
            if (!isset($this->soData[$item->id])) {
                $this->soData[$item->id] = [
                    'fisik' => $item->Stock_Opname ?? '',
                    'keterangan' => $item->Keterangan ?? '',
                ];
            }
        }

        return [
            'stockOpnames' => $stockOpnames,
        ];
    }
}; ?>

@section('title', 'Stock Opname')
<div>
    {{-- Notifikasi --}}
    @if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @elseif (session('info'))
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        {{ session('info') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <h4 class="py-3 mb-4">
        <span class="text-muted fw-light">Data Koperasi /</span> Stock Opname
    </h4>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Tabel Stock Opname</h5>

            {{-- Search Bar & Tombol Aksi Utama --}}
            <div class="d-flex justify-content-between align-items-center row py-3 gap-3 gap-md-0">
                <div class="col-md-8">
                    <input
                        wire:model.live.debounce.300ms="search"
                        type="text"
                        class="form-control"
                        placeholder="Cari berdasarkan Nama atau Kode Item...">
                </div>
                <div class="col-md-4 text-md-end">
                    @if($isSoActive)
                    {{-- Tombol Simpan & Batal muncul saat mode SO aktif --}}
                    <button wire:click="saveStockOpname" wire:loading.attr="disabled" class="btn btn-success me-2">
                        <span wire:loading wire:target="saveStockOpname" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        <span wire:loading.remove wire:target="saveStockOpname"><i class='bx bx-save me-1'></i></span>
                        Simpan
                    </button>
                    <button wire:click="toggleSoMode" class="btn btn-outline-secondary">Batalkan</button>
                    @else
                    {{-- Tombol Pengaman Utama --}}
                    <button wire:click="toggleSoMode" class="btn btn-primary">
                        <i class='bx bx-edit-alt me-1'></i> Lakukan Stock Opname
                    </button>
                    @endif
                </div>
            </div>
        </div>

        {{-- Tabel Data --}}
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th style="cursor: pointer;" wire:click="sort('Kode_Item')">Kode Item <i class="bx bx-sort-alt-2"></i></th>
                        <th style="cursor: pointer;" wire:click="sort('Nama_Item')">Nama Item <i class="bx bx-sort-alt-2"></i></th>
                        <th>Stok Sistem</th>
                        <th style="width: 150px;">Stok Fisik</th>
                        <th>Selisih</th>
                        <th style="width: 180px;">Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($stockOpnames as $item)
                    @php
                    $id = $item->id;
                    // INI BAGIAN AJAIBNYA!
                    // Kode ini tidak berubah, tapi sekarang memanggil Accessor di model
                    // untuk menghitung (Stok_Masuk - Stok_Keluar - Stok_Retur)
                    $stokSistem = $item->Stok_Sistem;

                    $stokFisikInput = $this->soData[$id]['fisik'] ?? '';

                    $selisih = null;
                    if (is_numeric($stokFisikInput) && $stokFisikInput !== '') {
                    $selisih = $stokSistem - (int)$stokFisikInput;
                    }

                    $selisihBgClass = 'bg-label-secondary'; // Default
                    if (is_numeric($selisih)) {
                    if ($selisih > 0) $selisihBgClass = 'bg-label-danger';
                    elseif ($selisih < 0) $selisihBgClass='bg-label-success' ;
                        else $selisihBgClass='bg-label-primary' ;
                        }
                        @endphp
                        <tr wire:key="{{ $id }}">
                        <td><strong>{{ $item->Kode_Item }}</strong></td>
                        <td>{{ $item->Nama_Item }}</td>
                        <td><span class="fw-semibold">{{ $stokSistem }}</span></td>

                        {{-- Kolom Input Stok Fisik --}}
                        <td>
                            @if($isSoActive)
                            <input
                                wire:model.live.debounce.500ms="soData.{{ $id }}.fisik"
                                type="number"
                                min="0"
                                class="form-control form-control-sm @error('soData.'.$id.'.fisik') is-invalid @enderror"
                                placeholder="0">
                            @else
                            {{ $item->Stock_Opname ?? 'N/A' }}
                            @endif
                        </td>

                        {{-- Kolom Selisih (Real-time) --}}
                        <td>
                            <span class="badge {{ $selisihBgClass }}">
                                {{ is_numeric($selisih) ? abs($selisih) : 'N/A' }}
                            </span>
                        </td>

                        {{-- Kolom Keterangan (Dropdown) --}}
                        <td>
                            @if($isSoActive)
                            <select
                                wire:model="soData.{{ $id }}.keterangan"
                                class="form-select form-select-sm"
                                @disabled(!is_numeric($stokFisikInput) || $selisih==0)>
                                <option value="">Pilih...</option>
                                @foreach($keteranganOptions as $option)
                                <option value="{{ $option }}">{{ $option }}</option>
                                @endforeach
                            </select>
                            @else
                            {{ $item->Keterangan ?? '-' }}
                            @endif
                        </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center">Tidak ada data ditemukan.</td>
                        </tr>
                        @endforelse
                </tbody>
            </table>
        </div>

        {{-- Paginasi --}}
        @if ($stockOpnames->hasPages())
        <div class="card-footer d-flex justify-content-center">
            {{ $stockOpnames->links() }}
        </div>
        @endif
    </div>
</div>