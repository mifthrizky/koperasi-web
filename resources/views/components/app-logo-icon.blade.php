@php
$width = str_replace('px', '', $width ?? '60px'); // Hapus unit 'px' jika ada
$height = str_replace('px', '', $height ?? 'auto');
@endphp

<img
  src="{{ asset('assets/img/logo/kopeg.png') }}"
  alt="Logo Koperasi Polman"
  width="{{ $width }}"
  height="{{ $height }}"
  style="margin-right: 5px;" />