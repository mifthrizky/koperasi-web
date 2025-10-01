<?php

use Livewire\Volt\Component;
use App\Models\Retur;
use App\Models\StockOpname;
use App\Models\Barang;
use Livewire\Attributes\Rule;

new class extends Component
{
    public Retur $retur;

    #[Rule('required|numeric')]
    public string $kode_item = '';
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

    public ?int $stok_tersedia = null;

    /**
     * Mount: Mengisi form dengan data yang benar saat halaman dibuka.
     */
    public function mount(Retur $retur)
    {
        $this->retur = $retur;

        // 1. Isi data transaksi dari objek $retur
        $this->kode_item = (string)$retur->Kode_Item;
        $this->jumlah    = (string)$retur->Jumlah;
        $this->bulan     = $retur->Bulan;
        $this->tahun     = (string)$retur->Tahun;

        // --- BAGIAN KUNCI YANG DIPERBAIKI ---
        // 2. Cari detail item (nama, jenis, satuan) dari collection 'barangs' sebagai sumber utama
        $barang = Barang::where('Kode_Item', (int)$this->kode_item)->first();

        if ($barang) {
            $this->nama_item = $barang->Nama_Item;
            $this->satuan    = $barang->Satuan ?? ''; // Ambil dari barang, beri default jika null
        } else {
            // Fallback: Jika item tidak ada di master barang, gunakan data dari retur
            $this->nama_item = $retur->Nama_Item;
            $this->satuan    = $retur->Satuan ?? '';
        }

        // 3. Cari data stok untuk validasi
        $stock = StockOpname::where('Kode_Item', (int)$this->kode_item)->first();

        if ($stock) {
            $this->stok_tersedia = $stock->Stok_Sistem + $retur->Jumlah;
        } else {
            $this->stok_tersedia = $retur->Jumlah;
        }
        // --- AKHIR BAGIAN PERBAIKAN ---
    }

    /**
     * Menyimpan perubahan data ke database dan menyesuaikan stok.
     */
    public function update()
    {
        $this->validate();

        if ($this->stok_tersedia !== null && (int)$this->jumlah > $this->stok_tersedia) {
            $this->addError('jumlah', 'Jumlah retur melebihi stok yang tersedia (' . $this->stok_tersedia . ').');
            return;
        }

        $jumlahLama = $this->retur->Jumlah;
        $jumlahBaru = (int) $this->jumlah;
        $selisih = $jumlahBaru - $jumlahLama;

        if ($selisih !== 0) {
            $stockItem = StockOpname::where('Kode_Item', (int)$this->kode_item)->first();
            if ($stockItem) {
                $stockItem->increment('Stok_Retur', $selisih);
            }
        }

        $this->retur->update([
            'Jumlah' => $jumlahBaru,
            'Bulan'  => strtoupper($this->bulan),
            'Tahun'  => (int)$this->tahun,
        ]);

        session()->flash('success', 'Data pengembalian berhasil diperbarui dan stok telah disesuaikan.');
        return $this->redirectRoute('pengembalian.index', navigate: true);
    }

    public function cancel()
    {
        return $this->redirectRoute('pengembalian.index', navigate: true);
    }
};
?>

@section('title', 'Pengembalian')
<div>
    {{-- Tampilan Blade tidak ada perubahan, hanya logic di atas --}}
    <h4 class="py-3 mb-4">
        <span class="text-muted fw-light">Data Koperasi /</span> Edit Data Pengembalian
    </h4>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Form Edit Data Pengembalian</h5>
        </div>
        <div class="card-body">
            <form wire:submit="update">
                <div class="mb-3">
                    <label for="kode_item" class="form-label">Kode Item</label>
                    <input type="number" class="form-control" id="kode_item" wire:model="kode_item" readonly>

                    @if ($stok_tersedia !== null)
                    <p class="mt-2 text-success fw-bold">Stok Sistem Tersedia (Maks Retur): {{ number_format($stok_tersedia, 0, ',', '.') }}</p>
                    @endif
                </div>

                <div class="mb-3">
                    <label for="nama_item" class="form-label">Nama Item</label>
                    <input type="text" class="form-control" id="nama_item" wire:model="nama_item" readonly>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="jumlah" class="form-label">Jumlah Diretur</label>
                        <input type="number" class="form-control @error('jumlah') is-invalid @enderror" id="jumlah" wire:model="jumlah">
                        @error('jumlah') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="satuan" class="form-label">Satuan</label>
                        <input type="text" class="form-control" id="satuan" wire:model="satuan" readonly>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="bulan" class="form-label">Bulan</label>
                    <input type="text" class="form-control @error('bulan') is-invalid @enderror" id="bulan" wire:model="bulan" placeholder="Contoh: JANUARI">
                    @error('bulan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label for="tahun" class="form-label">Tahun</label>
                    <input type="number" class="form-control @error('tahun') is-invalid @enderror" id="tahun" wire:model="tahun" placeholder="Contoh: 2025">
                    @error('tahun') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <button type="button" wire:click="cancel" class="btn btn-secondary me-2">Batal</button>
                    <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                        <span wire:loading.remove>Update</span>
                        <span wire:loading>Memperbarui...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>