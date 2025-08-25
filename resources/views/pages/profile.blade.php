@extends(auth()->check() ? 'dashboard' : 'layouts.app')

@section('title', $profileUser->name)

@section(auth()->check() ? 'dashboard-content' : 'content')
<div class="container mt-5">
    <div class="row">
        {{-- Kiri --}}
        <div class="col-md-8">
            <h1 class="fw-bold" style="font-size: 3rem;">{{ $profileUser->name }}</h1>

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

            <div class="mt-4">
                {{-- konten tambahan --}}
                <p>Ini halaman profil publik dari <b>{{ $profileUser->name }}</b>.</p>
            </div>
        </div>

        {{-- Kanan --}}
        <div class="col-md-4 text-center">
            {{-- Foto profil --}}
            <div class="mb-3">
                <img src="{{ $profileUser->profile_picture ? asset('storage/' . $profileUser->profile_picture) : asset('assets/media/avatars/blank.png') }}"
                     alt="{{ $profileUser->name }}"
                     class="rounded-circle"
                     style="width: 150px; height: 150px; object-fit: cover;">
            </div>

            <h4>{{ $profileUser->name }}</h4>

            {{-- Tombol Edit Profile hanya untuk owner --}}
            @if(auth()->id() === $profileUser->id)
                <a href="{{ route('home', $profileUser->name) }}" class="btn btn-outline-primary btn-sm mt-2">
                    Edit Profile
                </a>
            @endif
        </div>
    </div>
</div>
@endsection

