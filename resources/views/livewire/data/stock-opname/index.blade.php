<?php

use Livewire\Volt\Component;
use App\Models\StockOpname;
use Livewire\WithPagination;
use Illuminate\Pagination\Paginator;
use Livewire\Attributes\Rule; // Import untuk validasi

new class extends Component
{
    use WithPagination;

    public string $search = '';
    public string $sortBy = 'Kode_Item';
    public string $sortDirection = 'asc';

    // Properti utama sebagai "pengaman" mode Stock Opname
    public bool $isSoActive = false;

    // Menampung semua data input (stok fisik & keterangan)
    // Menggunakan #[Rule] untuk validasi real-time
    #[Rule('array')]
    public array $soData = [];

    #[Rule('nullable|integer|min:0', as: 'Stok Fisik', onUpdate: false)]
    public $stokFisikValue; // Properti untuk validasi individual

    #[Rule('nullable|string', as: 'Keterangan', onUpdate: false)]
    public $keteranganValue; // Properti untuk validasi individual

    // Opsi keterangan yang sudah diringkas (ini bagus!)
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
        // Inisialisasi data saat komponen dimuat
        $this->loadSoData();
    }

    // Mengambil data awal dari database untuk mengisi state
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

    // Aksi untuk tombol "Lakukan Stock Opname" / "Batalkan"
    public function toggleSoMode()
    {
        if ($this->isSoActive) {
            // Jika mode dinonaktifkan (klik "Batalkan")
            $this->loadSoData(); // Kembalikan semua input ke data semula dari DB
            $this->resetValidation(); // Hapus pesan error jika ada
            session()->flash('info', 'Stock opname dibatalkan, data input dikembalikan.');
        } else {
            // Jika mode diaktifkan
            session()->flash('success', 'Mode stock opname aktif. Silakan isi stok fisik.');
        }
        $this->isSoActive = !$this->isSoActive;
    }

    // Fungsi ini akan dipanggil setiap kali `soData` diupdate (real-time)
    public function updatedSoData($value, $key)
    {
        // $key akan berbentuk 'id.fisik' atau 'id.keterangan'
        [$id, $field] = explode('.', $key);

        if ($field === 'fisik') {
            $this->stokFisikValue = $value;
            $this->validateOnly('stokFisikValue'); // Validasi hanya untuk input yang diubah
        }
    }


    // Aksi untuk tombol "Simpan Stock Opname"
    public function saveStockOpname()
    {
        $this->validate([
            'soData.*.fisik' => 'nullable|integer|min:0',
            'soData.*.keterangan' => 'nullable|string|max:255',
        ]);

        $updatedCount = 0;

        foreach ($this->soData as $id => $data) {
            $stokFisik = $data['fisik'];

            // Proses hanya jika ada input angka pada stok fisik
            if (is_numeric($stokFisik) && $stokFisik !== '') {
                $item = StockOpname::find($id);
                if ($item) {
                    $item->Stock_Opname = (int) $stokFisik;
                    $item->Keterangan = $data['keterangan'] ?: null; // Simpan null jika keterangan kosong
                    $item->save();
                    $updatedCount++;
                }
            }
        }

        // Matikan mode SO setelah menyimpan
        $this->isSoActive = false;

        // Muat ulang data untuk sinkronisasi (opsional tapi disarankan)
        $this->loadSoData();

        session()->flash('success', "Stock Opname berhasil disimpan! ($updatedCount item diperbarui)");
        $this->resetPage();
    }

    // Fungsi sorting dan searching (sudah baik, tidak perlu diubah)
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

    // Mengambil data untuk ditampilkan di view
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

        // Inisialisasi data untuk item di halaman saat ini jika belum ada
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
};
?>

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
                    $stokSistem = $item->Stok_Sistem;

                    // Ambil nilai input fisik dari state
                    $stokFisikInput = $this->soData[$id]['fisik'] ?? '';

                    // Hitung selisih HANYA jika input fisik adalah angka
                    $selisih = null;
                    if (is_numeric($stokFisikInput) && $stokFisikInput !== '') {
                    $selisih = $stokSistem - (int)$stokFisikInput;
                    }

                    // Logika warna selisih:
                    // Positif (+) -> Stok Sistem > Fisik (Kurang/Merah)
                    // Negatif (-) -> Stok Sistem < Fisik (Lebih/Hijau)
                        // Nol (0) -> Stok Sistem = Fisik (Sesuai/Biru)
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
                                    {{-- Tampilkan selisih absolut (tanpa minus) jika surplus --}}
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