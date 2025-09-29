<?php

use Livewire\Volt\Component;
use App\Models\Pembelian;
use Livewire\WithPagination;
use Illuminate\Pagination\Paginator;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;

new class extends Component
{
    use WithPagination;

    public string $search = '';
    public string $sortBy = 'created_at';
    public string $sortDirection = 'desc';
    public string $selectedMonth = '';
    public ?string $pembelianIdToDelete = null;

    /**
     * Menampilkan konfirmasi sebelum menghapus.
     */
    public function confirmDelete(string $id)
    {
        $this->pembelianIdToDelete = $id;
        $this->dispatch('show-delete-confirmation');
    }

    /**
     * Menghapus data setelah dikonfirmasi.
     */
    #[On('deleteConfirmed')]
    public function destroy()
    {
        if (!$this->pembelianIdToDelete) {
            return;
        }

        Pembelian::find($this->pembelianIdToDelete)?->delete();
        $this->pembelianIdToDelete = null;

        session()->flash('success', 'Data berhasil dihapus.');
    }

    /**
     * Menghitung daftar bulan unik untuk filter.
     */
    #[Computed]
    public function months()
    {
        $bulanDariDB = DB::getMongoDB()
            ->selectCollection('pembelians')
            ->distinct('Bulan');

        $bulanDariDB = array_map(fn($b) => strtoupper(trim($b)), $bulanDariDB);

        $urutanBulan = [
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

        return array_values(array_intersect($urutanBulan, $bulanDariDB));
    }

    /**
     * Mengatur paginasi saat komponen boot.
     */
    public function boot()
    {
        Paginator::useBootstrap();
    }

    /**
     * Mengatur kolom untuk sorting.
     */
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

    /**
     * Mereset halaman saat ada perubahan filter atau pencarian.
     */
    public function updating($property)
    {
        if (in_array($property, ['search', 'selectedMonth'])) {
            $this->resetPage();
        }
    }

    /**
     * Mengambil data untuk di-render di view.
     */
    public function with(): array
    {
        $pembelians = Pembelian::query()
            ->when($this->selectedMonth, function ($query) {
                $query->where('Bulan', $this->selectedMonth);
            })
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

        return [
            'pembelians' => $pembelians,
            'months' => $this->months(),
        ];
    }
}; ?>

<div>
    {{-- Notifikasi Sukses --}}
    @if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    {{-- Judul Halaman --}}
    <h4 class="py-3 mb-4">
        <span class="text-muted fw-light">Data Koperasi /</span> Data Pembelian
    </h4>

    <div class="card">
        <div class="card-header">
            {{-- Header Card: Judul dan Tombol Tambah --}}
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Tabel Data Pembelian</h5>
                <a href="{{ route('pembelian.create') }}" class="btn btn-primary" wire:navigate>
                    <i class="bx bx-plus-circle me-1"></i> Tambah Data
                </a>
            </div>

            {{-- Baris Filter dan Pencarian --}}
            <div class="row">
                <div class="col-md-4">
                    <select wire:model.live="selectedMonth" class="form-select">
                        <option value="">Semua Bulan</option>
                        @foreach($months as $month)
                        <option value="{{ $month }}">
                            {{ ucfirst(strtolower($month)) }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-8">
                    <input
                        wire:model.live.debounce.300ms="search"
                        type="text"
                        class="form-control"
                        placeholder="Cari berdasarkan Nama atau Kode Item...">
                </div>
            </div>
        </div>

        {{-- Tabel Data --}}
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Kode Item</th>
                        <th>Nama Item</th>
                        <th>Jenis</th>
                        <th>Jumlah</th>
                        <th wire:click="sort('Total_Harga')" style="cursor: pointer;">
                            Total Harga
                            <i class="bx bx-sort-alt-2 text-muted"></i>
                        </th>
                        <th>Bulan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @forelse ($pembelians as $pembelian)
                    <tr wire:key="{{ $pembelian->id }}">
                        <td><strong>{{ $pembelian->Kode_Item }}</strong></td>
                        <td>{{ $pembelian->Nama_Item }}</td>
                        <td><span class="badge bg-label-primary me-1">{{ $pembelian->Jenis }}</span></td>
                        <td>{{ $pembelian->Jumlah }} {{ $pembelian->Satuan }}</td>
                        <td>Rp {{ number_format($pembelian->Total_Harga, 0, ',', '.') }}</td>
                        <td>{{ $pembelian->Bulan }}</td>
                        <td>
                            <div class="dropdown">
                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                    <i class="bx bx-dots-vertical-rounded"></i>
                                </button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="{{ route('pembelian.edit', $pembelian) }}" wire:navigate>
                                        <i class="bx bx-edit-alt me-1"></i> Edit
                                    </a>
                                    <a class="dropdown-item" href="javascript:void(0);" wire:click="confirmDelete('{{ $pembelian->id }}')">
                                        <i class="bx bx-trash me-1"></i> Hapus
                                    </a>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-3">
                            Tidak ada data ditemukan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Footer Card: Paginasi --}}
        @if ($pembelians->hasPages())
        <div class="card-footer d-flex justify-content-center">
            {{ $pembelians->links('livewire::bootstrap') }}
        </div>
        @endif
    </div>
</div>

{{-- Script untuk SweetAlert2 --}}
@script
<script>
    document.addEventListener('livewire:initialized', () => {
        Livewire.on('show-delete-confirmation', () => {
            Swal.fire({
                title: 'Anda yakin?',
                text: "Data yang dihapus tidak dapat dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    Livewire.dispatch('deleteConfirmed')
                }
            })
        });
    });
</script>
@endscript