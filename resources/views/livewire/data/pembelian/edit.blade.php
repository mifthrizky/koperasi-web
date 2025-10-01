<?php

use Livewire\Volt\Component;
use App\Models\Pembelian;
use App\Models\StockOpname; // Import StockOpname
use Livewire\Attributes\Rule;

new class extends Component
{
    public Pembelian $pembelian;

    #[Rule('required|numeric')]
    public string $kode_item = '';
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

    public function mount(Pembelian $pembelian)
    {
        $this->pembelian   = $pembelian;
        $this->kode_item   = $pembelian->Kode_Item;
        $this->nama_item   = $pembelian->Nama_Item;
        $this->jenis       = $pembelian->Jenis;
        $this->jumlah      = $pembelian->Jumlah;
        $this->satuan      = $pembelian->Satuan;
        $this->total_harga = $pembelian->Total_Harga;
        $this->bulan       = $pembelian->Bulan;
    }

    public function update()
    {
        $validated = $this->validate();

        // Karena Kode Item readonly, validasi unique tidak terlalu krusial di sini
        $isUnique = Pembelian::where('Kode_Item', $this->kode_item)
            ->where('_id', '!=', $this->pembelian->id)
            ->doesntExist();

        if (!$isUnique) {
            $this->addError('kode_item', 'Kode item sudah digunakan.');
            return;
        }

        // --- BAGIAN KUNCI YANG DIPERBAIKI ---
        $jumlahLama = $this->pembelian->Jumlah;
        $jumlahBaru = (int) $this->jumlah;
        $selisih = $jumlahBaru - $jumlahLama;

        if ($selisih !== 0) {
            $stockItem = StockOpname::where('Kode_Item', (int)$this->kode_item)->first();
            if ($stockItem) {
                // Terapkan selisih ke Stok_Masuk
                $stockItem->increment('Stok_Masuk', $selisih);
            }
        }

        $this->pembelian->update($validated);
        // --- AKHIR BAGIAN PERBAIKAN ---

        session()->flash('success', 'Data pembelian berhasil diperbarui dan stok telah disesuaikan.');
        return $this->redirectRoute('pembelian.index', navigate: true);
    }
};
?>

@section('title', 'Pembelian')
<div>
    {{-- Kode View tidak perlu diubah --}}
    <h4 class="py-3 mb-4">
        <span class="text-muted fw-light">Data Pembelian /</span> Edit Data
    </h4>
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Form Edit Data Pembelian</h5>
        </div>
        <div class="card-body">
            <form wire:submit="update">
                <div class="mb-3">
                    <label for="kode_item" class="form-label">Kode Item</label>
                    <input type="number" class="form-control @error('kode_item') is-invalid @enderror"
                        id="kode_item" wire:model="kode_item" readonly>
                    @error('kode_item') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label for="nama_item" class="form-label">Nama Item</label>
                    <input type="text" class="form-control @error('nama_item') is-invalid @enderror"
                        id="nama_item" wire:model="nama_item" readonly>
                    @error('nama_item') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label for="jenis" class="form-label">Jenis</label>
                    <input type="text" class="form-control @error('jenis') is-invalid @enderror"
                        id="jenis" wire:model="jenis" readonly>
                    @error('jenis') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
                        <input type="text" class="form-control @error('satuan') is-invalid @enderror"
                            id="satuan" wire:model="satuan">
                        @error('satuan') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
                <div class="d-flex justify-content-end mt-4">
                    <a href="{{ route('pembelian.index') }}" class="btn btn-secondary me-2" wire:navigate>Batal</a>
                    <button type="submit" class="btn btn-primary">
                        <span wire:loading.remove>Update</span>
                        <span wire:loading>Memperbarui...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>