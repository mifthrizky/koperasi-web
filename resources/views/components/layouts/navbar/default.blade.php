<nav
  class="layout-navbar container-xxl navbar-detached navbar navbar-expand-xl align-items-center bg-navbar-theme"
  id="layout-navbar">
  <div class="layout-menu-toggle navbar-nav align-items-xl-center me-4 me-xl-0 d-xl-none">
    <a class="nav-item nav-link px-0 me-xl-6" href="javascript:void(0)">
      <i class="icon-base bx bx-menu icon-md"></i>
    </a>
  </div>

  <div class="navbar-nav-right d-flex align-items-center justify-content-end" id="navbar-collapse">
    <div class="navbar-nav align-items-center me-auto">
      <div class="nav-item d-flex align-items-center">
        <img
          src="{{ asset('assets/img/logo/polman.png') }}"
          alt="Logo Kampus"
          class="w-px-30 h-auto me-2 rounded-circle"
          style="object-fit: contain;" />

        <span class="fw-bold d-none d-sm-block me-3 text-dark">
          Politeknik Manufaktur Bandung
        </span>

        <div class="vr me-3 d-none d-sm-block"></div>

        <span class="text-secondary d-none d-sm-block">
          Halaman:
        </span>
        <h5 class="mb-0 fw-bold ms-1 text-primary">
          @yield('title', 'Dashboard')
        </h5>
      </div>
    </div>

    <ul class="navbar-nav flex-row align-items-center ms-md-auto">
      <!-- User -->
      <li class="nav-item navbar-dropdown dropdown-user dropdown">
        <!-- Check if the user is authenticated -->
        @if (Auth::check())
        <a class="nav-link dropdown-toggle hide-arrow p-0" href="javascript:void(0);" data-bs-toggle="dropdown">
          <div class="avatar avatar-online">
            <img src="{{ Auth::user()->profile_photo_url ?? asset('assets/img/avatars/1.png') }}" alt class="w-px-40 h-auto rounded-circle">
          </div>
        </a>
        <ul class="dropdown-menu dropdown-menu-end">
          <li>
            <a class="dropdown-item" href="{{ route('settings.profile') }}" wire:navigate>
              <div class="d-flex">
                <div class="flex-shrink-0 me-3">
                  <div class="avatar avatar-online">
                    <img src="{{ Auth::user()->profile_photo_url ?? asset('assets/img/avatars/1.png') }}" alt class="w-px-40 h-auto rounded-circle" />
                  </div>
                </div>
                <div class="flex-grow-1">
                  <h6 class="mb-0">{{ Auth::user()->name }}</h6>
                  <small class="text-body-secondary">{{ Auth::user()->role ?? 'User' }}</small> <!-- Display user role -->
                </div>
              </div>
            </a>
          </li>
          <li>
            <div class="dropdown-divider my-1"></div>
          </li>
          <li>
            <a class="dropdown-item {{ request()->routeIs('settings.profile') ? 'active' : '' }}" href="{{ route('settings.profile') }}" wire:navigate>
              <i class="icon-base bx bx-user icon-md me-3"></i><span>My Profile</span>
            </a>
          </li>
          <li>
            <a class="dropdown-item {{ request()->routeIs('settings.password') ? 'active' : '' }}" href="{{ route('settings.password') }}" wire:navigate>
              <i class="icon-base bx bx-cog icon-md me-3"></i><span>Settings</span>
            </a>
          </li>
          <li>
            <div class="dropdown-divider my-1"></div>
          </li>
          <li>
            <form method="POST" action="{{ route('logout') }}">
              @csrf
              <button class="dropdown-item" type="submit" class="btn p-0"><i class="icon-base bx bx-power-off icon-md me-3"></i><span>Log Out</span></button>
            </form>
          </li>
        </ul>
        @else
        <!-- If the user is not logged in, show the login option -->
        <a class="nav-link dropdown-toggle hide-arrow p-0" href="javascript:void(0);" data-bs-toggle="dropdown">
          <div class="avatar avatar-online">
            <img src="{{ asset('assets/img/avatars/1.png') }}" alt class="w-px-40 h-auto rounded-circle" />
          </div>
        </a>
        <ul class="dropdown-menu dropdown-menu-end">
          <li><a class="dropdown-item" href="{{ route('login') }}">Log In</a></li>
        </ul>
        @endif
      </li>
      <!--/ User -->
    </ul>
  </div>
</nav>