<?php

use Livewire\Volt\Component;
use App\Models\Retur; // Mengubah dari Pengembalian ke Retur
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
    public ?string $returIdToDelete = null; // Mengubah nama properti

    /**
     * Menampilkan konfirmasi sebelum menghapus.
     */
    public function confirmDelete(string $id)
    {
        $this->returIdToDelete = $id;
        $this->dispatch('show-delete-confirmation');
    }

    /**
     * Menghapus data setelah dikonfirmasi.
     */
    #[On('deleteConfirmed')]
    public function destroy()
    {
        if (!$this->returIdToDelete) {
            return;
        }

        // Menggunakan model Retur
        Retur::find($this->returIdToDelete)?->delete();
        $this->returIdToDelete = null;

        session()->flash('success', 'Data retur berhasil dihapus.'); // Mengubah pesan sukses
    }

    /**
     * Menghitung daftar bulan unik untuk filter.
     */
    #[Computed]
    public function months()
    {
        // Mengubah nama koleksi ke 'returs'
        $bulanDariDB = DB::getMongoDB()
            ->selectCollection('returs')
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
        // Mengubah query ke model Retur
        $returs = Retur::query()
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
            'returs' => $returs, // Mengubah nama variabel
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
        <span class="text-muted fw-light">Data Koperasi /</span> Data Retur
    </h4>

    <div class="card">
        <div class="card-header">
            {{-- Header Card: Judul dan Tombol Tambah --}}
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Tabel Data Retur</h5>
                <a href="{{ route('pengembalian.create') }}" class="btn btn-primary" wire:navigate>
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
                        <th wire:click="sort('Total_Harga_Pengembalian')" style="cursor: pointer;">
                            Total Harga Retur
                            <i class="bx bx-sort-alt-2 text-muted"></i>
                        </th>
                        <th>Alasan</th>
                        <th>Bulan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @forelse ($returs as $retur) {{-- Mengubah nama variabel --}}
                    <tr wire:key="{{ $retur->id }}">
                        <td><strong>{{ $retur->Kode_Item }}</strong></td>
                        <td>{{ $retur->Nama_Item }}</td>
                        <td><span class="badge bg-label-primary me-1">{{ $retur->Jenis }}</span></td>
                        <td>{{ $retur->Jumlah }} {{ $retur->Satuan }}</td>
                        {{-- Mengubah kolom Total Harga --}}
                        <td>Rp {{ number_format($retur->Total_Harga_Pengembalian, 0, ',', '.') }}</td>
                        {{-- Menambahkan kolom Alasan --}}
                        <td>{{ $retur->Alasan }}</td>
                        <td>{{ $retur->Bulan }}</td>
                        <td>
                            <div class="dropdown">
                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                    <i class="bx bx-dots-vertical-rounded"></i>
                                </button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="{{ route('pengembalian.edit', $retur) }}" wire:navigate> {{-- Mengubah rute edit --}}
                                        <i class="bx bx-edit-alt me-1"></i> Edit
                                    </a>
                                    <a class="dropdown-item" href="javascript:void(0);" wire:click="confirmDelete('{{ $retur->id }}')">
                                        <i class="bx bx-trash me-1"></i> Hapus
                                    </a>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-3">
                            Tidak ada data retur ditemukan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Footer Card: Paginasi --}}
        @if ($returs->hasPages())
        <div class="card-footer d-flex justify-content-center">
            {{ $returs->links('livewire::bootstrap') }}
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
                text: "Data retur yang dihapus tidak dapat dikembalikan!",
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