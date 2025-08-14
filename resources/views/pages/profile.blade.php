{{-- resources/views/profile/show.blade.php --}}
@extends('dashboard')

@section('title',$username->name)

@section('dashboard-content')
<div class="container mt-5">
    <div class="row">
        {{-- Kiri --}}
        <div class="col-md-8">
            {{-- Nama besar --}}
            <h1 class="fw-bold" style="font-size: 3rem;">{{ $username->name }}</h1>

            {{-- Navbar sederhana --}}
            <nav class="mt-3">
                <ul class="nav nav-tabs">
                    <li class="nav-item">
                        <a class="nav-link active" href="#">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">About</a>
                    </li>
                </ul>
            </nav>

            {{-- Konten Home/About bisa pakai yield kalau mau extend --}}
            <div class="mt-4">
                @yield('profile-content')
            </div>
        </div>

        {{-- Kanan --}}
        <div class="col-md-4 text-center">
            {{-- Foto profil --}}
            <div class="mb-3">
                <img src="{{ $username->profile_picture ? asset('storage/' . $username->profile_picture) : asset('assets/media/avatars/blank.png') }}"
                     alt="{{ $username->name }}"
                     class="rounded-circle"
                     style="width: 150px; height: 150px; object-fit: cover;">
            </div>

            {{-- Nama --}}
            <h4>{{ $username->name }}</h4>

            {{-- Tombol Edit Profile --}}
            @if(auth()->id() === $username->id)
                <a href="{{ route('profile', $username->name) }}" class="btn btn-outline-primary btn-sm mt-2">
                    Edit Profile
                </a>
            @endif
        </div>
    </div>
</div>
@endsection
