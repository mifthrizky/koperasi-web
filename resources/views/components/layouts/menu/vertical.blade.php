<!-- Menu -->
<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
  <div class="app-brand demo">
    <a href="{{ url('/') }}" class="app-brand-link"><x-app-logo /></a>
  </div>

  <div class="menu-inner-shadow"></div>

  <ul class="menu-inner py-1">
    <li class="menu-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
      <a class="menu-link" href="{{ route('dashboard') }}" wire:navigate>
        <i class="menu-icon tf-icons bx bx-home-circle"></i>
        <div class="text-truncate">{{ __('Dashboard') }}</div>
      </a>
    </li>

    {{-- Menu Header untuk Data Koperasi --}}
    <li class="menu-header small text-uppercase">
      <span class="menu-header-text">Data Barang</span>
    </li>

    <li class="menu-item {{ request()->routeIs('data.pembelian') ? 'active' : '' }}">
      <a class="menu-link" href="{{ route('barang.index') }}" wire:navigate>
        <i class="menu-icon tf-icons bx bx-box"></i>
        <div class="text-truncate">Barang</div>
      </a>
    </li>


    {{-- Menu Header untuk Data Koperasi --}}
    <li class="menu-header small text-uppercase">
      <span class="menu-header-text">Data Koperasi</span>
    </li>

    <li class="menu-item {{ request()->routeIs('data.pembelian') ? 'active' : '' }}">
      <a class="menu-link" href="{{ route('pembelian.index') }}" wire:navigate>
        <i class="menu-icon tf-icons bx bx-cart-alt"></i>
        <div class="text-truncate">Data Pembelian</div>
      </a>
    </li>

    <li class="menu-item {{ request()->routeIs('data.penjualan') ? 'active' : '' }}">
      <a class="menu-link" href="{{ route('penjualan.index') }}" wire:navigate>
        <i class="menu-icon tf-icons bx bx-line-chart"></i>
        <div class="text-truncate">Data Penjualan</div>
      </a>
    </li>

    <li class="menu-item {{ request()->routeIs('data.pengembalian') ? 'active' : '' }}">
      <a class="menu-link" href="{{ route('pengembalian.index') }}" wire:navigate>
        <i class="menu-icon tf-icons bx bx-archive-out"></i>
        <div class="text-truncate">Data Pengembalian</div>
      </a>
    </li>

    <li class="menu-item {{ request()->routeIs('data.stock-opname') ? 'active' : '' }}">
      <a class="menu-link" href="{{ route('stock-opname.index') }}" wire:navigate>
        <i class="menu-icon tf-icons bx bx-package"></i>
        <div class="text-truncate">Data Stock Opname</div>
      </a>
    </li>


    {{-- Menu Header untuk Pengaturan --}}
    <li class="menu-header small text-uppercase">
      <span class="menu-header-text">Pengaturan</span>
    </li>

    <li class="menu-item {{ request()->is('settings*') ? 'active open' : '' }}">
      <a href="javascript:void(0);" class="menu-link menu-toggle">
        <i class="menu-icon tf-icons bx bx-cog"></i>
        <div class="text-truncate">{{ __('Settings') }}</div>
      </a>
      <ul class="menu-sub">
        <li class="menu-item {{ request()->routeIs('settings.profile') ? 'active' : '' }}">
          <a class="menu-link" href="{{ route('settings.profile') }}" wire:navigate>{{ __('Profile') }}</a>
        </li>
        <li class="menu-item {{ request()->routeIs('settings.password') ? 'active' : '' }}">
          <a class="menu-link" href="{{ route('settings.password') }}" wire:navigate>{{ __('Password') }}</a>
        </li>
      </ul>
    </li>
  </ul>
</aside>
<!-- / Menu -->

<script>
  // Toggle the 'open' class when the menu-toggle is clicked
  document.querySelectorAll('.menu-toggle').forEach(function(menuToggle) {
    menuToggle.addEventListener('click', function() {
      const menuItem = menuToggle.closest('.menu-item');
      // Toggle the 'open' class on the clicked menu-item
      menuItem.classList.toggle('open');
    });
  });
</script>