@extends('layouts.app')

@section('title', $title ?? 'Dashboard')

@section('content')
    <div class="d-flex" style="min-height: 80vh; padding: 3rem;">
        {{-- Sidebar kiri --}}
        <div class="text-left" style="max-width: 300px; border-right: 2px solid #ccc; padding-right: 2rem;">
            <div class="symbol symbol-100px symbol-circle mb-4">
                <img src="{{ $authUser->profile_picture ? asset('storage/' . $authUser->profile_picture) : asset('assets/media/avatars/blank.png') }}"
                    alt="Profile Picture" />
            </div>
            <h1>Welcome, {{ $authUser->name }}</h1>
            <p>Email: {{ $authUser->email }}</p>

            <div class="menu menu-column menu-rounded menu-sub-indention fw-semibold fs-6 mt-4" id="kt_sidebar_menu">
                @foreach ($menus as $menu)
                    {{-- menu utama --}}
                    <a href="{{ route(
                        $menu['route'],
                        $menu['route'] === 'profile' ? ['username' => str_replace(' ', '_', $authUser->name)] : [],
                    ) }}"
                        class="nav-link">
                        <i class="{{ $menu['icon'] }}"></i> {{ $menu['title'] }}
                    </a>
                @endforeach

                {{-- tombol logout di bawah (pisah dari link menu) --}}
                <ul class="navbar-nav flex-column mt-auto">
                    <li class="nav-item">
                        <a href="#" id="btnLogout" class="nav-link text-danger">
                            <i class="bi bi-box-arrow-right"></i> Sign Out
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        {{-- Konten kanan --}}
        <div style="flex-grow: 1; padding-left: 2rem;">
            @yield('dashboard-content')
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const logoutBtn = document.getElementById('btnLogout');

            if (logoutBtn) {
                logoutBtn.addEventListener("click", async (e) => {
                    e.preventDefault();

                    try {
                        // Panggil API logout
                        await axios.post('/api/logout', {}, {
                            headers: {
                                'Accept': 'application/json',
                                'X-XSRF-TOKEN': getCookie(
                                    'XSRF-TOKEN') // ambil token CSRF dari cookie
                            },
                            withCredentials: true // supaya cookie session ikut dikirim
                        });

                        // Hapus cookie auth (kalau ada token lain simpan manual)
                        document.cookie = "XSRF-TOKEN=; Max-Age=0; path=/;";

                        // Redirect ke halaman login
                        window.location.href = "/login";
                    } catch (error) {
                        console.error(error);
                        alert("Gagal logout, coba lagi.");
                    }
                });
            }

            // Helper ambil cookie by name
            function getCookie(name) {
                const value = `; ${document.cookie}`;
                const parts = value.split(`; ${name}=`);
                if (parts.length === 2) return parts.pop().split(';').shift();
            }
        });
    </script>

@endsection
