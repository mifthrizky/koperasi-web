<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="layout-menu-fixed" data-base-url="{{url('/')}}" data-framework="laravel">

<head>
  @include('partials.head')
</head>

<body>

  <div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">

      <!-- Layout Content -->
      <x-layouts.menu.vertical :title="$title ?? null"></x-layouts.menu.vertical>
      <!--/ Layout Content -->

      <!-- Layout container -->
      <div class="layout-page">
        <!-- Navbar -->
        <x-layouts.navbar.default :title="$title ?? null"></x-layouts.navbar.default>
        <!--/ Navbar -->

        <!-- Content wrapper -->
        <div class="content-wrapper">
          <!-- Content -->
          <div class="container-xxl flex-grow-1 container-p-y">
            {{ $slot }}
          </div>
          <!-- / Content -->

          <!-- Footer -->
          <x-layouts.footer.default :title="$title ?? null"></x-layouts.footer.default>
          <!--/ Footer -->
          <div class="content-backdrop fade"></div>
          <!-- / Content wrapper -->
        </div>
      </div>
      <!-- / Layout page -->
    </div>
  </div>

  <!-- Include Scripts -->
  @include('partials.scripts')
  <!-- / Include Scripts -->

  <!-- Pop up -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
  
  @livewireScripts
  {{-- tempat semua script tambahan dari komponen Livewire --}}

 

  @stack('scripts')
 <!-- pie chart -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.js"></script>

</script>
</body>

</html>