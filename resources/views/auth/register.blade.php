@extends('layouts.app')

@section('title', 'Register')


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
        <div class="card shadow rounded-4 w-100 w-md-500px p-10 p-md-15 bg-dark">
            <!-- Header -->
            <div class="text-center mb-10">
                <h1 class="text-white fw-bold fs-2x mb-3">Create Your Account</h1>
                <p class="text-gray-400 fs-6">Fill in the details below to get started</p>
            </div>

            <!-- Register Form -->
            <form id="registerForm" class="form w-100" novalidate>
                @csrf
                <!-- Name -->
                <div class="mb-8">
                    <label class="form-label text-gray-300 fs-6 fw-semibold mb-2">Name</label>
                    <input type="text" name="name" id="name"
                        class="form-control form-control-solid bg-transparent text-white border border-gray-600 rounded-3 px-6 py-3"
                        placeholder="Your full name" required autofocus />
                    <div class="invalid-feedback d-block" id="error-name"></div>
                </div>

                <!-- Email -->
                <div class="mb-8">
                    <label class="form-label text-gray-300 fs-6 fw-semibold mb-2">Email</label>
                    <input type="email" name="email" id="email"
                        class="form-control form-control-solid bg-transparent text-white border border-gray-600 rounded-3 px-6 py-3"
                        placeholder="you@example.com" required />
                    <div class="invalid-feedback d-block" id="error-email"></div>
                </div>

                <!-- Password -->
                <div class="mb-8">
                    <label class="form-label text-gray-300 fs-6 fw-semibold mb-2">Password</label>
                    <input type="password" name="password" id="password"
                        class="form-control form-control-solid bg-transparent text-white border border-gray-600 rounded-3 px-6 py-3"
                        placeholder="Create a password" required />
                    <div class="invalid-feedback d-block" id="error-password"></div>
                </div>

                <!-- Password Confirmation -->
                <div class="mb-8">
                    <label class="form-label text-gray-300 fs-6 fw-semibold mb-2">Confirm Password</label>
                    <input type="password" name="password_confirmation" id="password_confirmation"
                        class="form-control form-control-solid bg-transparent text-white border border-gray-600 rounded-3 px-6 py-3"
                        placeholder="Confirm your password" required />
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn btn-primary btn-lg w-100 py-4 fw-bold fs-6">
                    Register
                </button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
        // Setup global axios config
        axios.defaults.withCredentials = true;

        document.getElementById('registerForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            // Clear previous errors    
            ['name', 'email', 'password'].forEach(id => {
                document.getElementById('error-' + id).textContent = '';
            });

            const data = {
                name: document.getElementById('name').value.trim(),
                email: document.getElementById('email').value.trim(),
                password: document.getElementById('password').value,
                password_confirmation: document.getElementById('password_confirmation').value,
            };

            try {
                const res = await axios.post('/api/register/users', data);

                // Kalau sampai sini berarti register sukses
                alert('Registrasi berhasil! Selamat datang.');
                window.location.href = '/dashboard';

            } catch (error) {
                if (error.response && error.response.data) {
                    const json = error.response.data;

                    if (json.errors) {
                        for (const key in json.errors) {
                            if (json.errors.hasOwnProperty(key)) {
                                const errorDiv = document.getElementById('error-' + key);
                                if (errorDiv) errorDiv.textContent = json.errors[key][0];
                            }
                        }
                    } else if (json.message) {
                        alert('Error: ' + json.message);
                    } else {
                        alert('Registrasi gagal tanpa pesan error spesifik.');
                    }
                } else {
                    alert('Terjadi kesalahan: ' + error.message);
                }
            }
        });
    </script>

@endsection
