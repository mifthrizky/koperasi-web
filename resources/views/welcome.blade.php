<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="layout-menu-fixed" data-base-url="{{url('/')}}" data-framework="laravel">
  @section('title', __('Welcome'))
  <head>
    @include('partials.head')
  </head>
  <body>
    <div class="container-xxl flex-grow-1 container-p-y">
      <div class="d-flex justify-content-end">
          @if (Route::has('login'))
            @auth
                <a href="{{ url('/dashboard') }}" class="btn btn-primary">Dashboard</a>
            @else
                <a href="{{ route('login') }}" class="btn btn-secondary me-2">Log in</a>

                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="btn btn-primary">Register</a>
                @endif
            @endauth
          @endif
      </div>
      <div class="position-absolute top-50 start-50 translate-middle">
        <div class="card">
          <div class="row g-0">
            <div class="col-md-6 d-flex align-items-center">
              <div class="card-body">
                <h1 class="h4 card-title">Sneat Design Laravel Livewire</h1>
                <p class="card-text mb-5">The Starter Kit integrates Sneat components into Laravel Livewire. Visit our live docs and demo to explore the components.</p>
                <ul class="mb-0">
                  <li class="mb-3">Read the Laravel <a href="https://laravel.com/docs" target="_blank">Documentation</a></li>
                  <li class="mb-3">Read the Sneat Laravel <a href="https://demos.themeselection.com/sneat-bootstrap-html-admin-template/documentation/laravel-introduction.html" target="_blank">Documentation</a></li>
                  <li>Sneat <a href="https://demos.themeselection.com/sneat-bootstrap-html-laravel-admin-template-free/demo/" target="_blank">Components</a></li>
                </ul>
              </div>
            </div>
            <div class="col-md-6">
              <img class="card-img card-img-right" src="{{asset('assets/img/illustrations/laravel-livewire-sneat.png')}}" alt="Card image">
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Include Scripts -->
    @include('partials.scripts')
    <!-- / Include Scripts -->
  </body>
</html>
