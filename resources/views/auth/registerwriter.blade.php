@extends('dashboard')

@section('title', 'Register Writer')

@section('dashboard-content')
    <div class="container mt-5" style="max-width: 500px;">
        <h1 class="text-center mb-4">Register as Writer</h1>

        <form id="registerWriterForm">
            @csrf

            {{-- Email --}}
            <div class="mb-3">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}">
                <div class="text-danger small" id="error-email"></div>
            </div>

            {{-- Password --}}
            <div class="mb-3">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" class="form-control">
                <div class="text-danger small" id="error-password"></div>
            </div>

            <button type="submit" class="btn btn-primary w-100">Register</button>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
        axios.defaults.withCredentials = true;

        document.getElementById('registerWriterForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            // Reset error messages
            ['email', 'password'].forEach(id => {
                const el = document.getElementById('error-' + id);
                if (el) el.textContent = '';
            });

            const payload = {
                email: document.getElementById('email').value,
                password: document.getElementById('password').value
            };

            try {
                const response = await axios.post("{{ url('/api/register/writer') }}", payload, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('input[name=_token]').value
                    }
                });

                // Debug alert
                alert(
                    'Response data:\n' + JSON.stringify(response.data, null, 2) +
                    '\n\nCurrent cookies:\n' + document.cookie
                );

                if (response.data.status === 'success') {
                    alert(response.data.message);
                    window.location.href = '/dashboard'; // Redirect ke dashboard
                } else if (response.data.errors) {
                    Object.keys(response.data.errors).forEach(key => {
                        const el = document.getElementById('error-' + key);
                        if (el) el.textContent = response.data.errors[key][0];
                    });
                } else {
                    alert(response.data.message || 'Something went wrong');
                }

            } catch (error) {
                let msg = 'Something went wrong';
                if (error.response && error.response.data) {
                    msg = JSON.stringify(error.response.data, null, 2);
                }
                alert(msg);
                console.error(error);
            }
        });
    </script>

@endsection
