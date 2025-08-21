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
        document.addEventListener("DOMContentLoaded", () => {
            const token = document.querySelector('input[name=_token]')?.value || '';
            const tbody = document.querySelector('#userArticlesTable tbody');
            const pagination = document.getElementById('pagination');

            // ==============================
            // Load user articles
            // ==============================
            async function loadUserArticles(page = 1) {
                try {
                    const res = await axios.get(`/api/users/{{ $user->name }}/articles?page=${page}`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': token
                        },
                        withCredentials: true
                    });

                    const paginatedData = res.data.data; // JSON dari controller
                    const data = paginatedData.data; // array artikel
                    renderPagination(paginatedData);

                    tbody.innerHTML = '';

                    if (data.length === 0) {
                        tbody.innerHTML =
                            `<tr><td colspan="4" class="text-center">Tidak ada artikel publik</td></tr>`;
                        pagination.innerHTML = '';
                        return;
                    }

                    let rows = '';
                    data.forEach(article => {
                        const createdAt = new Date(article.created_at);
                        const formattedDate = createdAt.toLocaleDateString('en-US', {
                            month: 'short',
                            day: '2-digit'
                        });

                        const userName = article.user?.name || '—';
                        const commentCount = article.comments?.length || 0;
                        const likesCount = article.liked_users_count || 0;

                        rows += `
                    <tr>
                        <td>${article.title} <br><small>${formattedDate}</small></td>
                        <td>${userName}</td>
                        <td>${commentCount} komentar</td>
                        <td>${likesCount} like</td>
                    </tr>
                `;
                    });

                    tbody.innerHTML = rows;
                    renderPagination(res.data.data);

                } catch (err) {
                    console.error(err);
                    tbody.innerHTML =
                        `<tr><td colspan="4" class="text-center text-danger">Gagal memuat data</td></tr>`;
                }
            }

            // ==============================
            // Render pagination
            // ==============================
            function renderPagination(stories) {
                pagination.innerHTML = '';
                stories.links.forEach(link => {
                    const disabled = !link.url ? 'disabled' : '';
                    const active = link.active ? 'active' : '';
                    const page = getPageNumber(link.url);

                    pagination.innerHTML += `
                <li class="page-item ${disabled} ${active}">
                    ${disabled 
                        ? `<span class="page-link">${link.label}</span>` 
                        : `<a class="page-link" href="#" data-page="${page}">${link.label}</a>`}
                </li>
            `;
                });

                // Event click
                document.querySelectorAll('#pagination a').forEach(a => {
                    a.addEventListener('click', e => {
                        e.preventDefault();
                        loadUserArticles(a.dataset.page);
                    });
                });
            }

            function getPageNumber(url) {
                if (!url) return 1;
                const params = new URL(url).searchParams;
                return params.get('page') || 1;
            }

            // ==============================
            // Init
            // ==============================
            loadUserArticles();
        });
    </script>
@endsection
