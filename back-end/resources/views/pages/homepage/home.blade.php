@extends('layouts.app')

@section('title', 'Artikel')

@section('dashboard-content')
    @php
        $defaultProfile = asset('assets/media/avatars/blank.png');
    @endphp

    <div class="d-flex justify-content-center">
        <div class="w-100" style="max-width: 800px;">
            <!-- Tab Navigation - Moved outside articlesContainer -->
            <nav class="mt-3">
                <div class="mb-4 border-bottom">
                    <a href="{{ route('home') }}"
                        class="me-3 pb-2 text-decoration-none {{ request()->routeIs('home') ? 'fw-bold border-bottom border-3 border-dark' : 'text-muted' }}">
                        For You
                    </a>
                </div>
            </nav>

            <!-- Articles Container -->
            <div id="articlesContainer" class="w-100" data-default-profile="{{ $defaultProfile }}">
                <div class="text-center" id="loadingMessage">
                    <p>Loading...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    <nav>
        <ul id="pagination" class="pagination justify-content-center mt-4"></ul>
    </nav>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const container = document.getElementById('articlesContainer');
            const defaultProfile = container.dataset.defaultProfile;

            async function loadArticles(page = 1) {
                const loadingMessage = document.getElementById('loadingMessage');
                if (loadingMessage) loadingMessage.style.display = 'block';

                try {
                    const response = await axios.get(`/api/articles?page=${page}`, {
                        headers: {
                            'Accept': 'application/json'
                        }
                    });

                    const paginatedData = response.data.data;
                    const data = paginatedData.data || [];
                    
                    // Clear container but keep structure
                    container.innerHTML = '';

                    if (data.length === 0) {
                        container.innerHTML = '<div class="text-center">Tidak ada artikel publik</div>';
                        document.getElementById('pagination').innerHTML = '';
                        return;
                    }

                    data.forEach(article => {
                        const createdAt = new Date(article.created_at);
                        const formattedDate = createdAt.toLocaleDateString('en-US', {
                            month: 'short',
                            day: '2-digit'
                        });
                        const userName = article.user?.name || '—';

                        // Profile picture handling
                        const profilePicture = article.user?.profile_picture || defaultProfile;

                        // Like and comment counts
                        const likesCount = article.liked_users_count || 0;
                        const commentsCount = article.comments_count || 0;

                        const card = document.createElement('div');
                        card.className = 'card mb-3';
                        card.style.cursor = 'pointer';
                        card.style.maxWidth = '100%';
                        card.style.width = '100%';
                        card.innerHTML = `
                            <div class="card-body" style="padding: 1.5rem;">
                                <!-- Author Profile Section -->
                                <div class="d-flex align-items-center mb-3">
                                    <div class="symbol symbol-35px me-2">
                                        <img src="${profilePicture}" alt="${userName}" class="rounded-circle" />
                                    </div>
                                    <a href="{{ url('/') }}/${userName}" class="text-decoration-none text-primary fw-semibold me-2">
                                        ${userName}
                                    </a>
                                    <small class="text-muted">${formattedDate}</small>
                                </div>

                                <!-- Article Title -->
                                <h5 class="card-title mb-3" style="line-height: 1.4; font-size: 1.25rem; word-wrap: break-word;">${article.title}</h5>

                                <!-- Article Content -->
                                <div class="card-text mb-4" style="line-height: 1.6; color: #6c757d; font-size: 0.95rem; word-wrap: break-word;">${(article.excerpt || article.content.substring(0, 150))}...</div>

                                <!-- Like and Comment Section -->
                                <div class="d-flex align-items-center justify-content-start gap-3 mt-auto">
                                    <div class="d-flex align-items-center text-muted">
                                        <i class="bi bi-hand-thumbs-up me-1 fs-5"></i>
                                        <span class="fw-semibold fs-6">${likesCount}</span>
                                    </div>
                                    <div class="d-flex align-items-center text-muted">
                                        <i class="bi bi-chat-left-text me-1 fs-5"></i>
                                        <span class="fw-semibold fs-6">${commentsCount}</span>
                                    </div>
                                </div>
                            </div>
                        `;

                        // Add click event to navigate to article detail
                        card.addEventListener('click', (e) => {
                            // Don't navigate if user clicked on author link
                            if (e.target.closest('a[href*="{{ url('/') }}"]')) {
                                return;
                            }
                            window.location.href = `/articles/${article.id}`;
                        });

                        container.appendChild(card);
                    });

                    renderPagination(paginatedData, loadArticles);

                } catch (error) {
                    console.error(error);
                    container.innerHTML = '<div class="text-center text-danger">Gagal memuat data</div>';
                } finally {
                    const loadingMessage = document.getElementById('loadingMessage');
                    if (loadingMessage) loadingMessage.style.display = 'none';
                }
            }

            function renderPagination(paginatedData, callback) {
                const pagination = document.getElementById('pagination');
                pagination.innerHTML = '';
                paginatedData.links.forEach(link => {
                    const isDisabled = !link.url;
                    const isActive = link.active ? 'active' : '';
                    const page = getPageNumber(link.url);
                    const li = document.createElement('li');
                    li.className = `page-item ${isDisabled ? 'disabled' : ''} ${isActive}`;
                    if (isDisabled) {
                        li.innerHTML = `<span class="page-link">${link.label}</span>`;
                    } else {
                        const a = document.createElement('a');
                        a.className = 'page-link';
                        a.href = '#';
                        a.dataset.page = page;
                        a.innerText = link.label;
                        a.addEventListener('click', function(e) {
                            e.preventDefault();
                            callback(this.dataset.page);
                        });
                        li.appendChild(a);
                    }
                    pagination.appendChild(li);
                });
            }

            function getPageNumber(url) {
                if (!url) return 1;
                const params = new URL(url).searchParams;
                return params.get('page') || 1;
            }

            loadArticles();
        });
    </script>

@endsection