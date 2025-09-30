<?php

use Livewire\Volt\Component;
use App\Models\Penjualan;
use App\Models\Barang;
use App\Models\StockOpname;
use Livewire\Attributes\Rule;

new class extends Component
{
    #[Rule('required|numeric')]
    public string $kode_item = '';

    public ?int $stok_tersedia = null;

    #[Rule('required|string|max:255')]
    public string $nama_item = '';

    #[Rule('required|string')]
    public string $jenis = '';

    #[Rule('required|numeric|min:1')]
    public string $jumlah = '';

    #[Rule('required|string')]
    public string $satuan = '';

    #[Rule('required|numeric|min:0')]
    public string $total_harga = '';

    #[Rule('required|string')]
    public string $bulan = '';

    #[Rule('required|numeric|min:2000|max:2099')]
    public string $tahun = '';

    public function updatedKodeItem($value)
    {
        $this->reset(['nama_item', 'jenis', 'satuan', 'stok_tersedia', 'jumlah']);

        if (!empty($value)) {
            $barang = Barang::where('Kode_Item', (int)$value)->first();
            $stock = StockOpname::where('Kode_Item', (int)$value)->first();

            if ($barang) {
                $this->nama_item = $barang->Nama_Item;
                $this->jenis = $barang->Jenis;
                $this->stok_tersedia = $stock ? $stock->Stok_Sistem : 0;
            }
        }
    }

    /**
     * Simpan data penjualan baru DAN update stok.
     */
    public function save()
    {

        // Validasi agar jumlah jual tidak melebihi stok
        if ($this->stok_tersedia !== null && (int)$this->jumlah > $this->stok_tersedia) {
            $this->addError('jumlah', 'Jumlah penjualan melebihi stok yang tersedia (' . $this->stok_tersedia . ').');
            return;
        }

        // 1. Catat transaksi penjualan
        Penjualan::create([
            'Kode_Item'   => (int) $this->kode_item,
            'Nama_Item'   => $this->nama_item,
            'Jenis'       => $this->jenis,
            'Jumlah'      => (int) $this->jumlah,
            'Satuan'      => strtoupper($this->satuan),
            'Total_Harga' => (int) $this->total_harga,
            'Bulan'       => strtoupper($this->bulan),
            'Tahun'       => (int) $this->tahun,
        ]);

        // 2. Cari item yang sesuai di collection stock_opnames
        $stockItem = StockOpname::where('Kode_Item', (int)$this->kode_item)->first();

        // 3. Jika item ditemukan, tambahkan jumlah keluar menggunakan increment
        if ($stockItem) {
            $stockItem->increment('Stok_Keluar', (int)$this->jumlah);
        } else {
            // Opsional: Jika item belum ada di stock_opnames, Anda bisa membuatnya di sini
            // StockOpname::create([...]);
        }

        session()->flash('success', 'Data penjualan berhasil ditambahkan dan stok telah diperbarui.');
        return $this->redirectRoute('penjualan.index', navigate: true);
    }
};
?>

<div>
    {{-- Notifikasi Tambahan untuk Error --}}
    @if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    {{-- Sisa kode view Anda tidak perlu diubah sama sekali --}}
    <h4 class="py-3 mb-4">
        <span class="text-muted fw-light">Data Penjualan /</span> Tambah Data
    </h4>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Form Tambah Data Penjualan</h5>
        </div>
        <div class="card-body">
            <form wire:submit="save">
                {{-- KODE VIEW ANDA DI SINI (TIDAK ADA PERUBAHAN) --}}
                <div class="mb-3">
                    <label for="kode_item" class="form-label">Kode Item</label>
                    <input type="number"
                        class="form-control @error('kode_item') is-invalid @enderror"
                        id="kode_item"
                        wire:model.live="kode_item"
                        placeholder="Ketik Kode Item untuk mencari otomatis...">
                    @error('kode_item')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror

                    @if ($stok_tersedia !== null)
                    <p class="mt-2 text-success fw-bold">Stok Tersedia: {{ number_format($stok_tersedia, 0, ',', '.') }}</p>
                    @elseif (!empty($kode_item) && empty($nama_item))
                    <p class="mt-2 text-danger">Barang dengan kode ini tidak ditemukan.</p>
                    @endif
                </div>

                <div class="mb-3">
                    <label for="nama_item" class="form-label">Nama Item</label>
                    <input type="text" class="form-control" id="nama_item" wire:model="nama_item" readonly>
                </div>

                <div class="mb-3">
                    <label for="jenis" class="form-label">Jenis</label>
                    <input type="text" class="form-control" id="jenis" wire:model="jenis" readonly>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="jumlah" class="form-label">Jumlah</label>
                        <input type="number" class="form-control @error('jumlah') is-invalid @enderror"
                            id="jumlah" wire:model="jumlah" placeholder="Contoh: 50">
                        @error('jumlah') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="satuan" class="form-label">Satuan</label>
                        <input type="text" class="form-control" id="satuan" wire:model="satuan" placeholder="Contoh: PCS, BOX, LUSIN">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="total_harga" class="form-label">Total Harga (Rp)</label>
                    <input type="number" class="form-control @error('total_harga') is-invalid @enderror"
                        id="total_harga" wire:model="total_harga" placeholder="Contoh: 750000">
                    @error('total_harga') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
                    <a href="{{ route('penjualan.index') }}" class="btn btn-secondary me-2" wire:navigate>Batal</a>
                    <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                        <span wire:loading.remove>Simpan</span>
                        <span wire:loading>Menyimpan...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>