@section('title', __('Dashboard'))
<x-layouts.app :title="__('Dashboard')">
  <div class="row g-4">
    {{-- Biarkan 3 kolom atas jika masih dibutuhkan --}}
    <div class="col-lg-4">
      <div class="overflow-hidden rounded border" style="aspect-ratio: 16/6;">
        <x-placeholder-pattern class="h-100 w-100" style="stroke: color-mix(in oklab, oklch(.21 .034 264.665) 20%, transparent);" />
      </div>
    </div>
    <div class="col-lg-4">
      <div class="overflow-hidden rounded border" style="aspect-ratio: 16/6;">
        <x-placeholder-pattern class="h-100 w-100" style="stroke: color-mix(in oklab, oklch(.21 .034 264.665) 20%, transparent);" />
      </div>
    </div>
    <div class="col-lg-4">
      <div class="overflow-hidden rounded border" style="aspect-ratio: 16/6;">
        <x-placeholder-pattern class="h-100 w-100" style="stroke: color-mix(in oklab, oklch(.21 .034 264.665) 20%, transparent);" />
      </div>
    </div>

    {{-- GANTI BAGIAN INI --}}
    <div class="col-lg-12">
      {{-- Hapus placeholder lama dan panggil komponen Livewire --}}
      <livewire:dashboard.top-selling-items />
    </div>
    {{-- SELESAI --}}

  </div>
</x-layouts.app>