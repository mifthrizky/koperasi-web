<?php

use Livewire\Volt\Component;
use App\Models\StockOpname;
use Livewire\WithPagination;
use Illuminate\Pagination\Paginator;
use Livewire\Attributes\Computed;


new class extends Component
{
    use WithPagination;

    public string $search = '';
    public string $sortBy = 'Kode_Item';
    public string $sortDirection = 'asc';
    public string $selectedMonth = '';
    public bool $isSoActive = false;

    public array $stokFisik = [];
    public array $keterangan = [];

    public array $keteranganOptions = [
        'Salah Catat',
        'Hilang',
        'Rusak',
        'Lain-lain',
    ];

    public function boot()
    {
        Paginator::useBootstrap();
    }

    #[Computed]
    public function months()
    {
        // Daftar master urutan bulan
        $urutanBulan = ['JANUARI', 'FEBRUARI', 'MARET', 'APRIL', 'MEI', 'JUNI', 'JULI', 'AGUSTUS', 'SEPTEMBER', 'OKTOBER', 'NOVEMBER', 'DESEMBER'];
        // Ambil nomor bulan sekarang (misal: Oktober = 10)
        $bulanSekarang = now()->month;
        // "Potong" array master dari awal sebanyak nomor bulan sekarang
        return array_slice($urutanBulan, 0, $bulanSekarang);
    }

    private function getStockOpnameQuery()
    {
        return StockOpname::query()
            ->when($this->selectedMonth, fn($query) => $query->where('Bulan', $this->selectedMonth))
            ->when($this->search, function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery->where('Nama_Item', 'like', '%' . $this->search . '%');
                    if (is_numeric($this->search)) {
                        $subQuery->orWhere('Kode_Item', (int) $this->search);
                    }
                });
            })
            ->orderBy($this->sortBy, $this->sortDirection);
    }

    public function toggleSoMode()
    {
        if ($this->isSoActive) {
            $this->stokFisik = [];
            $this->keterangan = [];
            $this->resetValidation();
            session()->flash('info', 'Stock opname dibatalkan.');
        } else {
            $stockOpnamesOnPage = $this->getStockOpnameQuery()->paginate(10);
            $tempStokFisik = [];
            $tempKeterangan = [];
            foreach ($stockOpnamesOnPage as $item) {
                $id = (string) $item->id;
                $tempStokFisik[$id] = $item->Stock_Opname;
                $tempKeterangan[$id] = $item->Keterangan;
            }
            $this->stokFisik = $tempStokFisik;
            $this->keterangan = $tempKeterangan;
            session()->flash('success', 'Mode stock opname aktif. Silakan edit stok fisik.');
        }
        $this->isSoActive = !$this->isSoActive;
    }

    public function saveStockOpname()
    {
        if (!$this->isSoActive) {
            session()->flash('error', 'Mode stock opname tidak aktif.');
            return;
        }
        $updatedCount = 0;
        $errorCount = 0;
        try {
            $petugasName = Auth::user()->name;

            foreach ($this->stokFisik as $id => $fisikValue) {
                if ($fisikValue === '' || $fisikValue === null) continue;

                if (!is_numeric($fisikValue) || $fisikValue < 0) {
                    $errorCount++;
                    $this->addError("stokFisik.{$id}", "Stok harus angka positif.");
                    continue;
                }
                $item = StockOpname::find($id);
                if (!$item) continue;

                $stokFisikInt = (int)$fisikValue;
                $keteranganValue = $this->keterangan[$id] ?? null;

                if ($item->Stock_Opname != $stokFisikInt || $item->Keterangan != $keteranganValue) {
                    $item->Stock_Opname = $stokFisikInt;
                    $item->Keterangan = $keteranganValue;
                    $item->petugas = $petugasName;
                    $item->save();
                    $updatedCount++;
                }
            }
            if ($errorCount > 0) {
                session()->flash('error', "Terdapat {$errorCount} kesalahan input. Periksa kembali data Anda.");
                return;
            }
            $this->isSoActive = false;
            $this->stokFisik = [];
            $this->keterangan = [];
            $this->resetValidation();
            if ($updatedCount > 0) {
                session()->flash('success', "Stock Opname berhasil disimpan! ({$updatedCount} item diperbarui)");
            } else {
                session()->flash('info', "Tidak ada perubahan data yang disimpan.");
            }
            $this->dispatch('$refresh');
        } catch (\Exception $e) {
            \Log::error('Kesalahan Simpan Stock Opname: ' . $e->getMessage());
            session()->flash('error', 'Terjadi kesalahan saat menyimpan data.');
        }
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
        if (in_array($property, ['search', 'selectedMonth'])) {
            $this->resetPage();
        }
    }

    public function with(): array
    {
        $stockOpnames = $this->getStockOpnameQuery()->paginate(10);
        return [
            'stockOpnames' => $stockOpnames,
            'months' => $this->months(),
        ];
    }
};
?>

<div>
    @section('title', 'Stock Opname')
    {{-- Notifikasi --}}
    @if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @elseif (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
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
            <div class="d-flex justify-content-between align-items-center row py-3 gap-3 gap-md-0">
                <div class="col-md-3">
                    <select wire:model.live="selectedMonth" class="form-select">
                        <option value="">Semua Bulan</option>
                        @foreach($months as $month)
                        <option value="{{ $month }}">{{ ucfirst(strtolower($month)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-5">
                    <input wire:model.live.debounce.300ms="search" type="text" class="form-control" placeholder="Cari berdasarkan Nama atau Kode Item...">
                </div>
                <div class="col-md-4 text-md-end">
                    @if ($isSoActive)
                    <button type="button" x-data="{ isSaving: false }" @click="isSaving = true; $wire.call('saveStockOpname')" :disabled="isSaving" wire:target="saveStockOpname" class="btn btn-success me-2">
                        <span wire:loading wire:target="saveStockOpname" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        <span wire:loading.remove wire:target="saveStockOpname"><i class='bx bx-save me-1'></i></span>
                        Simpan
                    </button>
                    <button type="button" wire:click.prevent="toggleSoMode" wire:loading.attr="disabled" class="btn btn-outline-secondary">Batalkan</button>
                    @else
                    <a href="{{ route('stock-opname.import') }}" class="btn btn-info me-2" wire:navigate>
                        <i class="bx bx-upload me-1"></i> Impor dari Excel
                    </a>
                    <button type="button" wire:click.prevent="toggleSoMode" class="btn btn-primary">
                        <i class='bx bx-edit-alt me-1'></i> Lakukan SO
                    </button>
                    @endif
                </div>
            </div>
        </div>

        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th style="cursor: pointer;" wire:click="sort('Kode_Item')">Kode Item <i class="bx bx-sort-alt-2"></i></th>
                        <th style="cursor: pointer;" wire:click="sort('Nama_Item')">Nama Item <i class="bx bx-sort-alt-2"></i></th>
                        <th>Stok Masuk</th>
                        <th>Stok Keluar</th>
                        <th>Stok Retur</th>
                        <th>Stok Sistem</th>
                        <th style="width: 150px;">Stok Fisik</th>
                        <th>Selisih</th>
                        <th style="width: 180px;">Keterangan</th>
                        <th>Petugas</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($stockOpnames as $item)
                    @php
                    $id = (string) $item->id;
                    $stokSistem = $item->Stok_Sistem;
                    $nilaiFisikUntukHitung = $isSoActive ? ($this->stokFisik[$id] ?? null) : $item->Stock_Opname;
                    $selisih = null;
                    if (is_numeric($nilaiFisikUntukHitung)) {
                    $selisih = $stokSistem - (int) $nilaiFisikUntukHitung;
                    }
                    $selisihBgClass = 'bg-label-secondary';
                    if (is_numeric($selisih)) {
                    if ($selisih > 0) $selisihBgClass = 'bg-label-danger';
                    elseif ($selisih < 0) $selisihBgClass='bg-label-success' ;
                        else $selisihBgClass='bg-label-primary' ;
                        }
                        @endphp
                        <tr wire:key="item-{{ $id }}">
                        <td><strong>{{ $item->Kode_Item }}</strong></td>
                        <td>{{ $item->Nama_Item }}</td>
                        <td><span class="fw-semibold">{{ $item->Stok_Masuk }}</span></td>
                        <td><span class="fw-semibold">{{ $item->Stok_Keluar }}</span></td>
                        <td><span class="fw-semibold">{{ $item->Stok_Retur}}</span></td>
                        <td><span class="fw-semibold">{{ $stokSistem }}</span></td>
                        <td>
                            @if ($isSoActive)
                            <input wire:model.blur="stokFisik.{{ $id }}" type="number" min="0" step="1" class="form-control form-control-sm @error('stokFisik.' . $id) is-invalid @enderror" placeholder="Isi stok...">
                            @error('stokFisik.' . $id)
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @else
                            {{ $item->Stock_Opname ?? 'N/A' }}
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $selisihBgClass }}">
                                {{ is_numeric($selisih) ? abs($selisih) : 'N/A' }}
                            </span>
                        </td>
                        <td>
                            @if ($isSoActive)
                            <select wire:model="keterangan.{{ $id }}" class="form-select form-select-sm" @disabled(!is_numeric($nilaiFisikUntukHitung) || $selisih==0)>
                                <option value="">Pilih...</option>
                                @foreach ($keteranganOptions as $option)
                                <option value="{{ $option }}">{{ $option }}</option>
                                @endforeach
                            </select>
                            @else
                            {{ $item->Keterangan ?? '-' }}
                            @endif
                        </td>
                        <td>{{ $item->petugas ?? '-' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center">Tidak ada data ditemukan.</td>
                        </tr>
                        @endforelse
                </tbody>
            </table>
        </div>

        @if ($stockOpnames->hasPages())
        <div class="card-footer d-flex justify-content-center">
            {{ $stockOpnames->links() }}
        </div>
        @endif
    </div>
</div>