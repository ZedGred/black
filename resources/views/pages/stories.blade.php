@extends('dashboard')

@section('title', 'Your Story')

@section('dashboard-content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="fw-bold">Your Story</h1>
    <a href="{{ route('write') }}" class="btn btn-primary">
        <i class="bi bi-pencil-square"></i> Write
    </a>
</div>

<table id="userArticlesTable" class="table table-striped">
    <thead>
        <tr>
            <th>Judul</th>
            <th>Tanggal</th>
            <th>Komentar</th>
            <th>Likes</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td colspan="4" class="text-center">Loading...</td>
        </tr>
    </tbody>
</table>

<nav>
    <ul id="pagination" class="pagination justify-content-center"></ul>
</nav>

<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    loadUserArticles();

    async function loadUserArticles(page = 1) {
        try {
            const response = await axios.get(`/api/users/{{ $user->name }}/articles?page=${page}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name=_token]')?.value || ''
                },
                withCredentials: true // ini penting kalau pakai cookies/session
            });

            const data = response.data;
            const tbody = document.querySelector('#userArticlesTable tbody');
            tbody.innerHTML = '';

            const articles = data.stories?.data || [];

            if (articles.length === 0) {
                tbody.innerHTML = `<tr><td colspan="4" class="text-center">Tidak ada artikel publik</td></tr>`;
                document.getElementById('pagination').innerHTML = '';
                return;
            }

            articles.forEach(article => {
                let createdAt = new Date(article.created_at);
                let options = { month: 'short', day: '2-digit' };
                let formattedDate = createdAt.toLocaleDateString('en-US', options);

                tbody.innerHTML += `
                    <tr>
                        <td>${article.title}</td>
                        <td>${formattedDate}</td>
                        <td>0 komentar</td>
                        <td>0 like</td>
                    </tr>
                `;
            });

            renderPagination(data.stories);
        } catch (error) {
            console.error(error);
            document.querySelector('#userArticlesTable tbody').innerHTML = `
                <tr><td colspan="4" class="text-center text-danger">Gagal memuat data</td></tr>
            `;
        }
    }

    function renderPagination(stories) {
        const pagination = document.getElementById('pagination');
        pagination.innerHTML = '';

        stories.links.forEach(link => {
            if (!link.url) {
                pagination.innerHTML += `
                    <li class="page-item disabled">
                        <span class="page-link">${link.label}</span>
                    </li>
                `;
            } else {
                pagination.innerHTML += `
                    <li class="page-item ${link.active ? 'active' : ''}">
                        <a class="page-link" href="#" data-page="${getPageNumber(link.url)}">${link.label}</a>
                    </li>
                `;
            }
        });

        document.querySelectorAll('#pagination a').forEach(a => {
            a.addEventListener('click', function(e) {
                e.preventDefault();
                loadUserArticles(this.dataset.page);
            });
        });
    }

    function getPageNumber(url) {
        const params = new URL(url).searchParams;
        return params.get('page') || 1;
    }
});
</script>
@endsection
