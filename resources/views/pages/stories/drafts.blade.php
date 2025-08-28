@extends('layouts.app')

@section('title', 'Your Story')

@section('dashboard-content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fw-bold">Your Story</h1>
        <a href="{{ route('write') }}" class="btn btn-primary">
            <i class="bi bi-pencil-square"></i> Write
        </a>
    </div>

    <!-- tombol Draft & Published -->
    <div class="mb-4 border-bottom">
    <a href="{{ route('drafts') }}" 
       class="me-3 pb-2 text-decoration-none {{ request()->routeIs('drafts') ? 'fw-bold border-bottom border-3 border-dark' : 'text-muted' }}">
        Draft
    </a>
    <a href="{{ route('published') }}" 
       class="pb-2 text-decoration-none {{ request()->routeIs('published') ? 'fw-bold border-bottom border-3 border-dark' : 'text-muted' }}">
        Published
    </a>
</div>


    <div id="userArticlesContainer" class="row g-3 justify-content-center">
        <div class="col-12 text-center">Loading...</div>
    </div>

    <nav>
        <ul id="pagination" class="pagination justify-content-center mt-4"></ul>
    </nav>

    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const token = document.querySelector('input[name=_token]')?.value || '';
            const container = document.getElementById('userArticlesContainer');
            const pagination = document.getElementById('pagination');

            // ==============================
            // Load user articles
            // ==============================
            async function loadUserArticles(page = 1) {
                try {
                    const username = @json($authUser->name);
                    const res = await axios.get(`/api/users/{{ $authUser->name }}/articles?page=${page}`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': token
                        },
                        withCredentials: true
                    });

                    const paginatedData = res.data.data;
                    const data = paginatedData.data;
                    renderPagination(paginatedData);

                    container.innerHTML = '';

                    if (data.length === 0) {
                        container.innerHTML = `<div class="col-12 text-center">Tidak ada artikel publik</div>`;
                        pagination.innerHTML = '';
                        return;
                    }

                    let cards = '';
                    data.forEach(article => {
                        const createdAt = new Date(article.created_at);
                        const formattedDate = createdAt.toLocaleDateString('en-US', {
                            month: 'short',
                            day: '2-digit'
                        });

                        const excerpt = article.content
                            ? article.content.substring(0, 80) + '...'
                            : '';

                        cards += `
                            <div class="col-md-6 col-lg-4 mx-auto">
                                <div class="card shadow-sm">
                                    <div class="card-body">
                                        <h5 class="card-title mb-1">
                                            <a href="/${article.user?.name}/${article.id}" 
                                               class="text-decoration-none text-dark fw-bold">
                                                ${article.title}
                                            </a>
                                        </h5>
                                        <small class="text-muted d-block mb-2">${formattedDate}</small>

                                        <p class="card-text text-truncate">${excerpt}</p>

                                        <div class="d-flex justify-content-end">
                                            <div class="dropdown">
                                                <a href="#" class="text-muted" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="bi bi-three-dots-vertical"></i>
                                                </a>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li><a class="dropdown-item" href="/${article.user?.name}/${article.id}">View</a></li>
                                                    <li><a class="dropdown-item" href="/articles/${article.id}/edit">Edit</a></li>
                                                    <li><button class="dropdown-item text-danger" onclick="deleteArticle('${article.id}')">Delete</button></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                    });

                    container.innerHTML = cards;

                } catch (err) {
                    console.error(err);
                    container.innerHTML = `<div class="col-12 text-center text-danger">Gagal memuat data</div>`;
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
            // Delete Article
            // ==============================
            async function deleteArticle(id) {
                if (!confirm("Yakin ingin menghapus artikel ini?")) return;
                try {
                    await axios.delete(`/api/articles/${id}`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': token   
                        },
                        withCredentials: true
                    });
                    loadUserArticles();
                } catch (err) {
                    if (err.response) {
                        // error dari server Laravel
                        console.error("Status:", err.response.status);
                        console.error("Data:", err.response.data);
                        alert(
                            `Gagal menghapus artikel: ${err.response.data.error || err.response.data.message}`);
                    } else {
                        // error lain (network dll)
                        console.error(err);
                        alert("Terjadi error jaringan");
                    }
                }
            }

            // ==============================
            // Init
            // ==============================
            loadUserArticles();
            window.deleteArticle = deleteArticle;   
            
        });
        
    </script>
@endsection
