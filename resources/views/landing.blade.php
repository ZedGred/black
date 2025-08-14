@extends('layouts.app') {{-- Pastikan layouts.app menggunakan Metronic --}}

@section('title', 'Black: Read & Discover Hidden Mythic Stories')

@section('content')
    <!--begin::Landing Page Wrapper-->
    <div class="d-flex flex-column w-100 min-h-100 bg-light">

        <!--begin::Navbar-->
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark py-6 px-14" style="min-height: 100px;">
            <div class="container-fluid">
                <a class="navbar-brand text-white d-flex align-items-center gap-3 fw-bold" href="{{ url('/') }}"
                    style="font-family: Helvetica, Arial, sans-serif; font-size: 2.8rem;">
                    <i class="ki-outline ki-book fs-3x text-white"></i>
                    BLACK
                </a>


                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                    <ul class="navbar-nav gap-5">
                        <li class="nav-item">
                            <a class="nav-link text-white fs-4" href="{{ route('home') }}">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white fs-4" href="{{ route('home') }}">Author</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white fs-4" href="{{ route('home') }}">Community</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white fs-4" href="{{ route('login') }}">Sign in</a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('register') }}"
                                class="btn btn-light text-dark fs-4 px-6 py-3 rounded-pill">Get started</a>
                        </li>
                    </ul>
                </div>

            </div>
        </nav>
        <!--end::Navbar-->

        <!-- resources/views/components/hero-section.blade.php -->
        <div class="d-flex align-items-start min-vh-100 bg-light px-15">
            <div class="w-100 text-start pt-20">
                <!-- Tulisan Besar -->
                <h1 class="fw-bold text-dark mb-8"
                    style="font-family: Helvetica, Arial, sans-serif; font-size: 8rem; line-height: 1.1;">
                    Human<br>stories & ideas
                </h1>

                <!-- Tulisan Kecil -->
                <p class="text-muted mb-14" style="font-size: 2.25rem;">
                    A place to read, write, and deepen your understanding
                </p>

                <!-- Tombol Hitam -->
                <a href="{{ route('register') }}" class="btn btn-dark px-10 py-5 fs-3 rounded-pill">
                    Get Started
                </a>
            </div>
        </div>


    </div>
    <!--end::Landing Page Wrapper-->
@endsection
