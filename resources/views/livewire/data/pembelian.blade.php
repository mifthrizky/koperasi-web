<?php

use Livewire\Volt\Component;

new class extends Component
{
    //
}; ?>

<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Data Pembelian') }}
        </h2>
    </x-slot>

    <div class="card">
        <div class="card-body">
            <h3>Ini data pembelian</h3>
        </div>
    </div>
</div>