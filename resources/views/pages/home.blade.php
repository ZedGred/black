@extends('dashboard')

@section('title', 'Artikel')

@section('dashboard-content')
    <table id="articlesTable" class="table table-striped">
        <thead>
            <tr>
                <th>Judul</th>
                <th>Penulis</th>
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

            async function loadArticles(page = 1) {
                const tbody = document.querySelector('#articlesTable tbody');
                try {
                    const response = await axios.get(`/api/articles?page=${page}`, {
                        headers: {
                            'Accept': 'application/json'
                        }
                    });

                    const paginatedData = response.data.data;
                    const data = paginatedData.data || [];
                    tbody.innerHTML = '';

                    if (data.length === 0) {
                        tbody.innerHTML =
                            `<tr><td colspan="4" class="text-center">Tidak ada artikel publik</td></tr>`;
                        document.getElementById('pagination').innerHTML = '';
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
                    renderPagination(paginatedData, loadArticles);

                } catch (error) {
                    console.error(error);
                    tbody.innerHTML =
                        `<tr><td colspan="4" class="text-center text-danger">Gagal memuat data</td></tr>`;
                }
            }

            function renderPagination(paginatedData, callback) {
                const pagination = document.getElementById('pagination');
                pagination.innerHTML = '';

                paginatedData.links.forEach(link => {
                    const isDisabled = !link.url;
                    const isActive = link.active ? 'active' : '';
                    const page = getPageNumber(link.url);

                    pagination.innerHTML += `
            <li class="page-item ${isDisabled ? 'disabled' : ''} ${isActive}">
                ${isDisabled 
                    ? `<span class="page-link">${link.label}</span>` 
                    : `<a class="page-link" href="#" data-page="${page}">${link.label}</a>`}
            </li>
            `;
                });

                document.querySelectorAll('#pagination a').forEach(a => {
                    a.addEventListener('click', function(e) {
                        e.preventDefault();
                        callback(this.dataset.page);
                    });
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
