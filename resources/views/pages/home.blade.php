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

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        loadArticles();

        function loadArticles(page = 1) {
            fetch(`/api/articles?page=${page}`)
                .then(res => res.json())
                .then(data => {
                    const tbody = document.querySelector('#articlesTable tbody');
                    tbody.innerHTML = '';

                    if (!data.data || data.data.length === 0) {
                        tbody.innerHTML = `<tr><td colspan="4" class="text-center">Tidak ada artikel</td></tr>`;
                        return;
                    }

                    data.data.forEach(article => {
                        let createdAt = new Date(article.created_at);
                        let options = { month: 'short', day: '2-digit' };
                        let formattedDate = createdAt.toLocaleDateString('en-US', options);

                        tbody.innerHTML += `
                            <tr>
                                <td>${article.title} <br><small>${formattedDate}</small></td>
                                <td>${article.user.name}</td>
                                <td>${article.comments.length} komentar</td>
                                <td>${article.liked_users_count} like</td>
                            </tr>
                        `;
                    });

                    renderPagination(data);
                })
                .catch(err => {
                    console.error(err);
                    document.querySelector('#articlesTable tbody').innerHTML = `
                        <tr><td colspan="4" class="text-center text-danger">Gagal memuat data</td></tr>
                    `;
                });
        }

        function renderPagination(data) {
            const pagination = document.getElementById('pagination');
            pagination.innerHTML = '';

            data.links.forEach(link => {
                if (link.url === null) {
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

            // Event click pagination
            document.querySelectorAll('#pagination a').forEach(a => {
                a.addEventListener('click', function(e) {
                    e.preventDefault();
                    const page = this.dataset.page;
                    loadArticles(page);
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
