<?php

use Livewire\Volt\Component;
use App\Models\Barang;
use Livewire\Attributes\Rule;

new class extends Component
{
    public Barang $barang;

    #[Rule('required|numeric')]
    public ?int $kode_item = null;

    #[Rule('required|string|max:255')]
    public string $nama_item = '';

    #[Rule('required|string')]
    public string $jenis = '';

    #[Rule('required|numeric|min:0')]
    public ?int $harga_satuan = null;

    public function mount(Barang $barang)
    {
        $this->barang       = $barang;
        $this->kode_item    = $barang->Kode_Item;
        $this->nama_item    = $barang->Nama_Item;
        $this->jenis        = $barang->Jenis;
        $this->harga_satuan = $barang->Harga_Satuan;
    }

    public function update()
    {
        $validated = $this->validate();

        $isUnique = Barang::where('Kode_Item', $this->kode_item)
            ->where('_id', '!=', $this->barang->id)
            ->doesntExist();

        if (!$isUnique) {
            $this->addError('kode_item', 'Kode item sudah digunakan.');
            return;
        }

        $this->barang->update([
            'Kode_Item'    => $this->kode_item,
            'Nama_Item'    => $this->nama_item,
            'Jenis'        => $this->jenis,
            'Harga_Satuan' => $this->harga_satuan,
        ]);

        session()->flash('success', 'Data barang berhasil diperbarui.');
        return $this->redirectRoute('barang.index', navigate: true);
    }
}; ?>


<div>
    <h4 class="py-3 mb-4">
        <span class="text-muted fw-light">Data Barang /</span> Edit Data
    </h4>

    {{-- Formnya sama persis dengan halaman create, hanya wire:submit-nya diubah --}}
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Form Edit Data Barang</h5>
        </div>
        <div class="card-body">
            <form wire:submit="update">
                {{-- (Salin semua elemen <div class="mb-3"> ... </div> dari form create ke sini) --}}
                {{-- Atau salin seluruh isi <form> dari create.blade.php dan ubah button submit --}}
                <div class="mb-3">
                    <label for="kode_item" class="form-label">Kode Item</label>
                    <input type="number" class="form-control @error('kode_item') is-invalid @enderror" id="kode_item" wire:model="kode_item" placeholder="Contoh: 101">
                    @error('kode_item') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label for="nama_item" class="form-label">Nama Item</label>
                    <input type="text" class="form-control @error('nama_item') is-invalid @enderror" id="nama_item" wire:model="nama_item" placeholder="Contoh: Gula Pasir">
                    @error('nama_item') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label for="jenis" class="form-label">Jenis</label>
                    <input type="text" class="form-control @error('jenis') is-invalid @enderror" id="jenis" wire:model="jenis" placeholder="Contoh: Sembako">
                    @error('jenis') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label for="harga_satuan" class="form-label">Harga Satuan (Rp)</label>
                    <input type="number" class="form-control @error('harga_satuan') is-invalid @enderror"
                        id="harga_satuan" wire:model="harga_satuan" placeholder="Contoh: 750000">
                    @error('harga_satuan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <a href="{{ route('barang.index') }}" class="btn btn-secondary me-2" wire:navigate>Batal</a>
                    <button type="submit" class="btn btn-primary">
                        <span wire:loading.remove>Update</span>
                        <span wire:loading>Memperbarui...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>