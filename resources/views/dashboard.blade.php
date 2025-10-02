@section('title', __('Dashboard'))
<x-layouts.app :title="__('Dashboard')">
    <livewire:dashboard.month-filter />
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="overflow-hidden rounded border mb-4" style="min-height: 300px;">
                 <livewire:dashboard.pie-chart-jenis/>
            </div>

            <div class="overflow-hidden rounded border mb-4" style="min-height: 300px;">
                  <livewire:dashboard.stack-bar-stok/>
                    
            </div>

            <div class="overflow-hidden rounded border mb-4" style="min-height: 300px;">
                  <livewire:dashboard.salesVSbuy/>
            </div>
            <div class="overflow-hidden rounded border mb-4">
                  <livewire:dashboard.returs-chart/>
            </div>
        </div>
        <div class="col-lg-4 d-flex flex-column gap-4">
            <div class="overflow-hidden rounded border" style="aspect-ratio: 16/6;">
                <livewire:dashboard.profit/>
            </div>

            <div class="overflow-hidden rounded border flex-grow-1" style="min-height: 300px;">
                <livewire:dashboard.top-selling-items />
            </div>
        </div>
    </div>

</x-layouts.app>