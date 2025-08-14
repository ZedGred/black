@extends('dashboard')

@section('title', 'Register Writer')

@section('dashboard-content')
<div class="d-flex flex-center flex-column min-vh-100 bg-light">
    <div class="card shadow rounded -4 w-100 w-md-450px p-10">
        <div class="text-center mb-8">
            <h1 class="fw-bold">Register as Writer</h1>
        </div>

        <form id="registerWriterForm">
            @csrf

            {{-- Name --}}
            <div class="mb-3">
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-control" value="{{ old('name') }}">
                <div class="invalid-feedback" id="error-name"></div>
            </div>

            {{-- Username --}}
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" value="{{ old('username') }}">
                <div class="invalid-feedback" id="error-username"></div>
            </div>

            {{-- Email --}}
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email') }}">
                <div class="invalid-feedback" id="error-email"></div>
            </div>

            {{-- Password --}}
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control">
                <div class="invalid-feedback" id="error-password"></div>
            </div>

            {{-- Confirm Password --}}
            <div class="mb-3">
                <label class="form-label">Confirm Password</label>
                <input type="password" name="password_confirmation" class="form-control">
            </div>

            <button type="submit" class="btn btn-primary w-100">Register</button>
        </form>

        <div class="text-center mt-5">
            <a href="{{ route('login') }}" class="link-primary">Already have an account? Login</a>
        </div>
    </div>
</div>

<script>
document.getElementById('registerWriterForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    // Reset error feedback
    ['name','username','email','password'].forEach(id => {
        document.getElementById('error-'+id).textContent = '';
    });

    const form = e.target;
    const formData = new FormData(form);
    const payload = Object.fromEntries(formData.entries());

    try {
        const response = await fetch("{{ url('/api/register/writer') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('input[name=_token]').value
            },
            body: JSON.stringify(payload)
        });

        const data = await response.json();

        if (response.ok && data.success) {
            // Sukses register
            alert(data.message);
            window.location.href = "{{ route('login') }}";
        } else if (data.errors) {
            // Tampilkan error validasi
            Object.keys(data.errors).forEach(key => {
                const errorEl = document.getElementById('error-'+key);
                if(errorEl){
                    errorEl.textContent = data.errors[key][0];
                    errorEl.previousElementSibling.classList.add('is-invalid');
                }
            });
        } else {
            alert(data.message || 'Something went wrong');
        }
    } catch (err) {
        console.error(err);
        alert('Something went wrong');
    }
});
</script>
@endsection
