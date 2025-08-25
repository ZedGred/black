@extends('layouts.app')

@section('title', 'Login')

@push('styles')
    <style>
        input:-webkit-autofill,
        input:-webkit-autofill:hover,
        input:-webkit-autofill:focus,
        input:-webkit-autofill:active {
            -webkit-box-shadow: 0 0 0 30px #1e1e2f inset !important;
            -webkit-text-fill-color: #ffffff !important;
        }
    </style>
@endpush

@section('content')
    <div class="d-flex flex-center flex-column flex-column-fluid min-vh-100" style="background-color: #121212;">
        <div class="card shadow rounded-4 w-100 w-md-450px p-10 p-md-15 bg-dark">
            <!-- Header -->
            <div class="text-center mb-10">
                <h1 class="text-white fw-bold fs-2x mb-3">Welcome Back</h1>
                <p class="text-gray-400 fs-6">Please sign in to your account</p>
            </div>

            <!-- Login Form -->
            <form id="loginForm" class="form w-100" novalidate>
                @csrf

                <!-- Email -->
                <div class="mb-8">
                    <label class="form-label text-gray-300 fs-6 fw-semibold mb-2">Email</label>
                    <input type="email" id="email" name="email"
                        class="form-control form-control-solid bg-transparent text-white border border-gray-600 rounded-3 px-6 py-3"
                        placeholder="you@example.com" required autofocus />
                    <div class="invalid-feedback d-block" id="error-email"></div>
                </div>

                <!-- Password -->
                <div class="mb-8">
                    <label class="form-label text-gray-300 fs-6 fw-semibold mb-2">Password</label>
                    <input type="password" id="password" name="password"
                        class="form-control form-control-solid bg-transparent text-white border border-gray-600 rounded-3 px-6 py-3"
                        placeholder="Your password" required />
                    <div class="invalid-feedback d-block" id="error-password"></div>
                </div>

                <!-- Remember Me -->
                <div class="d-flex justify-content-between align-items-center mb-10">
                    <label class="form-check form-check-sm form-check-custom form-check-solid text-white m-0">
                        <input class="form-check-input" type="checkbox" id="remember" name="remember" />
                        <span class="form-check-label fs-6 fw-semibold">Remember me</span>
                    </label>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn btn-primary btn-lg w-100 py-4 fw-bold fs-6">
                    Sign In
                </button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
        // Setup global axios config
        axios.defaults.withCredentials = true;

        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            // Clear error messages
            ['email', 'password'].forEach(id => {
                document.getElementById('error-' + id).textContent = '';
            });

            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            const remember = document.getElementById('remember').checked;

            try {
                const response = await axios.post('/api/login', {
                    email,
                    password,
                    remember
                });

                // Kalau sukses, server sudah set cookie HttpOnly token
                // Jadi tidak perlu simpan token di localStorage

                window.location.href = '/home';

            } catch (error) {
                if (error.response && error.response.data) {
                    const data = error.response.data;

                    if (data.errors) {
                        if (data.errors.email) {
                            document.getElementById('error-email').textContent = data.errors.email[0];
                        }
                        if (data.errors.password) {
                            document.getElementById('error-password').textContent = data.errors.password[0];
                        }
                    } else if (data.message) {
                        alert(data.message);
                    } else {
                        alert('Login gagal tanpa pesan error spesifik.');
                    }
                } else {
                    alert('Terjadi kesalahan, coba lagi.');
                }
            }
        });
    </script>
@endsection
