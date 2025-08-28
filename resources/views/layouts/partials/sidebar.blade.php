<div class="text-left" style="max-width: 300px; border-right: 2px solid #ccc; padding-right: 2rem;">
    @if (auth()->check() && isset($authUser))
        <div class="symbol symbol-100px symbol-circle mb-4">
            <img src="{{ $authUser->profile_picture ? asset('storage/' . $authUser->profile_picture) : asset('assets/media/avatars/blank.png') }}"
                alt="Profile Picture" />
        </div>
        <h1>Welcome, {{ $authUser->name }}</h1>
        <p>Email: {{ $authUser->email }}</p>

        <div class="menu menu-column menu-rounded menu-sub-indention fw-semibold fs-6 mt-4" id="kt_sidebar_menu">
            @foreach (config('menu') as $menu)
                @canany($menu['permissions'])
                    <a href="{{ route($menu['route'], $menu['route'] === 'profile' ? ['username' => Str::slug($authUser->name)] : []) }}"
                        class="nav-link">
                        <i class="{{ $menu['icon'] }}"></i> {{ $menu['title'] }}
                    </a>
                @endcanany
            @endforeach

            {{-- tombol logout --}}
            <ul class="navbar-nav flex-column mt-auto">
                <li class="nav-item">
                    <a href="#" id="btnLogout" class="nav-link text-danger">
                        <i class="bi bi-box-arrow-right"></i> Sign Out
                    </a>
                </li>
            </ul>
        </div>
    @else
    <div class="menu menu-column menu-rounded menu-sub-indention fw-semibold fs-6 mt-4" id="kt_sidebar_menu">
            {{-- tombol logout --}}
            <ul class="navbar-nav flex-column mt-auto">
                <li class="nav-item">
                    <a href="{{ route('home') }}" class="nav-link text-danger">
                        <i class="fas fa-home"></i> Home
                        
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('login') }}" class="nav-link text-danger">
                        <i class="bi bi-person-plus"></i> Sign up
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('login') }}" class="nav-link text-danger">
                        <i class="bi bi-box-arrow-right"></i> Sign in
                    </a>
                </li>

            </ul>
        </div>
    @endif
</div>
