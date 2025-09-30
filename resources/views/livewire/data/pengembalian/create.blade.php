<?php

use Livewire\Volt\Component;
use App\Models\Retur;
use App\Models\Barang;
use App\Models\StockOpname;
use Livewire\Attributes\Rule;

new class extends Component
{
    #[Rule('required|numeric')]
    public string $kode_item = '';

    // Stok yang bisa diretur (stok sistem saat ini)
    public ?int $stok_tersedia = null;

    #[Rule('required|string|max:255')]
    public string $nama_item = '';

    #[Rule('required|numeric|min:1')]
    public string $jumlah = '';

    #[Rule('required|string')]
    public string $satuan = '';

    #[Rule('required|string')]
    public string $bulan = '';

    #[Rule('required|numeric|min:2000|max:2099')]
    public string $tahun = '';

    /**
     * Dijalankan setiap kali Kode Item diperbarui (real-time).
     * Otomatis mengisi data barang dan stok yang tersedia.
     */
    public function updatedKodeItem($value)
    {
        $this->reset(['nama_item', 'satuan', 'stok_tersedia', 'jumlah']);

        // --- FIX: Gunakan filled() agar string "0" dianggap sebagai input valid ---
        if (filled($value)) {
            $barang = Barang::where('Kode_Item', (int)$value)->first();
            $stock = StockOpname::where('Kode_Item', (int)$value)->first();

            if ($barang) {
                $this->nama_item = $barang->Nama_Item;
                $this->satuan = $barang->Satuan ?? '';

                // Stok yang bisa diretur adalah stok sistem saat ini
                $this->stok_tersedia = $stock ? $stock->Stok_Sistem : 0;
            }
        }
    }

    /**
     * Simpan data retur baru DAN update stok.
     */
    public function save()
    {
        $this->validate();

        // Validasi agar jumlah retur tidak melebihi stok yang ada
        if ($this->stok_tersedia !== null && (int)$this->jumlah > $this->stok_tersedia) {
            $this->addError('jumlah', 'Jumlah retur melebihi stok yang tersedia (' . $this->stok_tersedia . ').');
            return;
        }

        // 1. Catat transaksi retur
        Retur::create([
            'Kode_Item'   => (int) $this->kode_item,
            'Nama_Item'   => $this->nama_item,
            'Jumlah'      => (int) $this->jumlah,
            'Satuan'      => strtoupper($this->satuan),
            'Bulan'       => strtoupper($this->bulan),
            'Tahun'       => (int) $this->tahun,
        ]);

        // 2. Cari item di StockOpname
        $stockItem = StockOpname::where('Kode_Item', (int)$this->kode_item)->first();

        // 3. Tambahkan jumlah yang diretur ke 'Stok_Retur'
        if ($stockItem) {
            $stockItem->increment('Stok_Retur', (int)$this->jumlah);
        }

        session()->flash('success', 'Data pengembalian berhasil ditambahkan dan stok telah diperbarui.');
        return $this->redirectRoute('pengembalian.index', navigate: true);
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
    @endif
    @if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        Terjadi kesalahan validasi. Silakan periksa input Anda.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <h4 class="py-3 mb-4">
        <span class="text-muted fw-light">Data Koperasi /</span> Tambah Data Pengembalian
    </h4>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Form Tambah Data Pengembalian</h5>
        </div>
        <div class="card-body">
            <form wire:submit="save">
                <div class="mb-3">
                    <label for="kode_item" class="form-label">Kode Item</label>
                    <input type="number"
                        class="form-control @error('kode_item') is-invalid @enderror"
                        id="kode_item"
                        wire:model.live.debounce.300ms="kode_item"
                        placeholder="Ketik Kode Item untuk mencari otomatis...">
                    @error('kode_item')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror

                    @if ($stok_tersedia !== null)
                    <p class="mt-2 text-success fw-bold">Stok Sistem Tersedia: {{ number_format($stok_tersedia, 0, ',', '.') }}</p>
                    @elseif (filled($kode_item) && empty($nama_item))
                    <p class="mt-2 text-danger">Barang dengan kode ini tidak ditemukan.</p>
                    @endif
                </div>

                <div class="mb-3">
                    <label for="nama_item" class="form-label">Nama Item</label>
                    <input type="text" class="form-control" id="nama_item" wire:model="nama_item" readonly>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="jumlah" class="form-label">Jumlah</label>
                        <input type="number" class="form-control @error('jumlah') is-invalid @enderror"
                            id="jumlah" wire:model="jumlah" placeholder="Contoh: 5">
                        @error('jumlah') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="satuan" class="form-label">Satuan</label>
                        <input type="text" class="form-control" id="satuan" wire:model="satuan" placeholder="Contoh: PCS, BOX, LUSIN">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="bulan" class="form-label">Bulan</label>
                    <input type="text" class="form-control @error('bulan') is-invalid @enderror"
                        id="bulan" wire:model="bulan" placeholder="Contoh: JANUARI">
                    @error('bulan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label for="tahun" class="form-label">Tahun</label>
                    <input type="number" class="form-control @error('tahun') is-invalid @enderror"
                        id="tahun" wire:model="tahun" placeholder="Contoh: 2025">
                    @error('tahun') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <a href="{{ route('pengembalian.index') }}" class="btn btn-secondary me-2" wire:navigate>Batal</a>
                    <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                        <span wire:loading.remove>Simpan</span>
                        <span wire:loading>Menyimpan...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>