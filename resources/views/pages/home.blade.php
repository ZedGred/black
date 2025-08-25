@extends('dashboard')

@section('title', 'Artikel')

@section('dashboard-content')
<div id="articlesContainer">
    <div class="text-center" id="loadingMessage">
        <p>Loading...</p>
    </div>
</div>

<nav>
    <ul id="pagination" class="pagination justify-content-center mt-4"></ul>
</nav>

<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", () => {
    const container = document.getElementById('articlesContainer');

    async function loadArticles(page = 1) {
        const loadingMessage = document.getElementById('loadingMessage');
        loadingMessage && loadingMessage.remove();

        try {
            const response = await axios.get(`/api/articles?page=${page}`, {
                headers: { 'Accept': 'application/json' }
            });

            const paginatedData = response.data.data;
            const data = paginatedData.data || [];

            container.innerHTML = '';
            if (data.length === 0) {
                container.innerHTML = '<div class="text-center">Tidak ada artikel publik</div>';
                document.getElementById('pagination').innerHTML = '';
                return;
            }

            data.forEach(article => {
                const createdAt = new Date(article.created_at);
                const formattedDate = createdAt.toLocaleDateString('en-US', { month: 'short', day: '2-digit' });
                const userName = article.user?.name || '—';

                const card = document.createElement('div');
                card.className = 'card mb-3';
                card.style.cursor = 'pointer';
                card.innerHTML = `
                    <div class="card-body">
                        <h5 class="card-title">${article.title}</h5>
                        <p class="card-subtitle mb-2 text-muted">by ${userName} | ${formattedDate}</p>
                        <p class="card-text">${article.excerpt || article.content.substring(0, 150)}...</p>
                    </div>
                `;
                card.addEventListener('click', () => {
                    window.location.href = `/${userName}/${article.id}`;
                });
                container.appendChild(card);
            });

            renderPagination(paginatedData, loadArticles);

        } catch (error) {
            console.error(error);
            container.innerHTML = '<div class="text-center text-danger">Gagal memuat data</div>';
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
