<?php

use Livewire\Volt\Component;
use App\Models\Barang;
use Livewire\WithPagination;
use Illuminate\Pagination\Paginator;
use Livewire\Attributes\On;

new class extends Component
{
    use WithPagination;

    public string $search = '';
    public string $sortBy = 'created_at';
    public string $sortDirection = 'desc';
    public ?string $barangIdToDelete = null;

    public function confirmDelete(string $id)
    {
        $this->barangIdToDelete = $id;
        $this->dispatch('show-delete-confirmation');
    }

    #[On('deleteConfirmed')]
    public function destroy()
    {
        if (!$this->barangIdToDelete) {
            return;
        }

        Barang::find($this->barangIdToDelete)?->delete();
        $this->barangIdToDelete = null;

        session()->flash('success', 'Data berhasil dihapus.');

        $this->dispatch('$refresh');
    }

    public function boot()
    {
        Paginator::useBootstrap();
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

    public function with(): array
    {
        $barangs = Barang::query()
            ->when($this->search, function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery->where('Nama_Item', 'like', '%' . $this->search . '%');
                    if (is_numeric($this->search)) {
                        $subQuery->orWhere('Kode_Item', (int) $this->search);
                        $subQuery->orWhere('Kode_Item', (string) $this->search);
                    }
                });
            })
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(10);

        return [
            'barangs' => $barangs,
        ];
    }
}; ?>

@section('title', 'Barang')
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
        <span class="text-muted fw-light">Data Koperasi /</span> Data Barang
    </h4>

    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Tabel Data Barang</h5>
                <div>
                    <a href="{{ route('barang.import') }}" class="btn btn-info me-2">
                        <i class="bx bx-upload me-1"></i> Import dari Excel
                    </a>

                    <a href="{{ route('barang.create') }}" class="btn btn-primary" wire:navigate>
                        <i class="bx bx-plus-circle me-1"></i> Tambah Data
                    </a>
                </div>

            </div>

            {{-- Pencarian --}}
            <div class="row">
                <div class="col-md-12">
                    <input
                        wire:model.live.debounce.300ms="search"
                        type="text"
                        class="form-control"
                        placeholder="Cari berdasarkan Nama atau Kode Item...">
                </div>
            </div>
        </div>

        {{-- Tabel Data --}}
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width: 120px;">Kode Item</th>
                        <th style="max-width: 250px;">Nama Item</th>
                        <th style="width: 80px;">Jenis</th>
                        <th style="width: 80px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($barangs as $barang)
                    <tr>
                        <td><strong>{{ $barang->Kode_Item }}</strong></td>
                        <td class="text-truncate" style="max-width: 250px;" title="{{ $barang->Nama_Item }}">
                            {{ $barang->Nama_Item }}
                        </td>
                        <td><span class="badge bg-label-primary">{{ $barang->Jenis }}</span></td>
                        <td>
                            <div class="dropdown">
                                <button class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                    <i class="bx bx-dots-vertical-rounded"></i>
                                </button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="{{ route('barang.edit', $barang) }}" wire:navigate>
                                        <i class="bx bx-edit-alt me-1"></i> Edit
                                    </a>
                                    <a class="dropdown-item" href="javascript:void(0);" wire:click="confirmDelete('{{ $barang->id }}')">
                                        <i class="bx bx-trash me-1"></i> Hapus
                                    </a>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-3">Tidak ada data ditemukan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>


        {{-- Paginasi --}}
        @if ($barangs->hasPages())
        <div class="card-footer d-flex justify-content-center">
            {{ $barangs->links('livewire::bootstrap') }}
        </div>
        @endif
    </div>
</div>

{{-- SweetAlert2 --}}
@script
<script>
    // Listener ini akan dipasang sekali saat halaman dimuat dan lebih stabil
    window.addEventListener('show-delete-confirmation', event => {
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
                // Kirim event kembali ke komponen Livewire yang aktif
                Livewire.dispatch('deleteConfirmed')
            }
        })
    });
</script>
@endscript