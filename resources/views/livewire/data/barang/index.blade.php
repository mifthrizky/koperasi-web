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
                <a href="{{ route('barang.create') }}" class="btn btn-primary" wire:navigate>
                    <i class="bx bx-plus-circle me-1"></i> Tambah Data
                </a>
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
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Kode Item</th>
                        <th>Nama Item</th>
                        <th>Jenis</th>
                        <th wire:click="sort('Harga_Satuan')" style="cursor: pointer;">
                            Harga Satuan
                            <i class="bx bx-sort-alt-2 text-muted"></i>
                        </th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @forelse ($barangs as $barang)
                    <tr wire:key="{{ $barang->id }}">
                        <td><strong>{{ $barang->Kode_Item }}</strong></td>
                        <td>{{ $barang->Nama_Item }}</td>
                        <td><span class="badge bg-label-primary me-1">{{ $barang->Jenis }}</span></td>
                        <td>Rp {{ number_format($barang->Harga_Satuan, 0, ',', '.') }}</td>
                        <td>
                            <div class="dropdown">
                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
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
                        <td colspan="6" class="text-center py-3">
                            Tidak ada data ditemukan.
                        </td>
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