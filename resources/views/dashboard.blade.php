@extends('layouts.app')

@section('title', $title ?? 'Dashboard')

@section('content')
    <div class="d-flex" style="min-height: 80vh; padding: 3rem;">
        {{-- Sidebar kiri --}}
        <div class="text-left" style="max-width: 300px; border-right: 2px solid #ccc; padding-right: 2rem;">
            <div class="symbol symbol-100px symbol-circle mb-4">
                <img src="{{ $user->profile_picture ? asset('storage/' . $user->profile_picture) : asset('assets/media/avatars/blank.png') }}"
                    alt="Profile Picture" />
            </div>
            <h1>Welcome, {{ $user->name }}</h1>
            <p>Email: {{ $user->email }}</p>

            <div class="menu menu-column menu-rounded menu-sub-indention fw-semibold fs-6 mt-4" id="kt_sidebar_menu">
                @foreach ($menus as $menu)
                    <a
                        href="{{ route(
                            $menu['route'],
                            $menu['route'] === 'profile' ? ['username' => str_replace(' ', '_', $user->name)] : []
                        ) }}">
                        <i class="{{ $menu['icon'] }}"></i> {{ $menu['title'] }}
                    </a>
                @endforeach
            </div>
        </div>

        {{-- Konten kanan --}}
        <div style="flex-grow: 1; padding-left: 2rem;">
            @yield('dashboard-content')
        </div>
    </div>
@endsection
