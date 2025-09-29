<?php

use Livewire\Volt\Component;

new class extends Component
{
    //
}; ?>

<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Data Pengembalian') }}
        </h2>
    </x-slot>

    <div class="card">
        <div class="card-body">
            <h3>Ini data Pengembalian</h3>
        </div>
    </div>
</div>