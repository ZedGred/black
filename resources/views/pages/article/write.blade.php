@extends('layouts.app')

@section('title', 'Write Article')

@section('dashboard-content')
<div class="container mt-5" style="max-width: 600px;">
    <h1 class="text-center mb-4">Write New Article</h1>

    <form id="writeArticleForm">
        @csrf

        {{-- Title --}}
        <div class="mb-3">
            <label for="title">Title</label>
            <input 
                type="text" 
                name="title" 
                id="title" 
                class="form-control" 
                value="{{ old('title') }}"
                required
            >
            <div class="text-danger small" id="error-title"></div>
        </div>

        {{-- Content --}}
        <div class="mb-3">
            <label for="content">Content</label>
            <textarea 
                name="content" 
                id="content" 
                rows="5" 
                class="form-control"
                required
            ></textarea>
            <div class="text-danger small" id="error-content"></div>
        </div>

        <button type="submit" class="btn btn-primary w-100">Publish</button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
    axios.defaults.withCredentials = true; // Penting untuk HTTP-only cookies

    document.getElementById('writeArticleForm').addEventListener('submit', async function(e) {
        e.preventDefault();

        // Reset error messages
        ['title', 'content'].forEach(id => {
            const el = document.getElementById('error-' + id);
            if (el) el.textContent = '';
        });

        const payload = {
            title: document.getElementById('title').value,
            content: document.getElementById('content').value
        };

        try {
            const response = await axios.post("{{ url('/api/articles') }}", payload, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name=_token]').value
                }
            });

            if (response.data.status === 'success') {
                alert('Article published successfully');
                window.location.href = '/home';
            } else if (response.data.errors) {
                Object.keys(response.data.errors).forEach(key => {
                    const el = document.getElementById('error-' + key);
                    if (el) el.textContent = response.data.errors[key][0];
                });
            } 
        } catch (error) {
            let msg = 'Something went wrong';
            if (error.response && error.response.data) {
                msg = JSON.stringify(error.response.data, null, 2);
            }
            console.error(error);
        }
    });
</script>
@endsection
