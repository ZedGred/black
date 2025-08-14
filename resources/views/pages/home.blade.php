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

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        fetch('/api/articles')
            .then(res => res.json())
            .then(data => {
                const tbody = document.querySelector('#articlesTable tbody');
                tbody.innerHTML = '';

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
            })
            .catch(err => {
                console.error(err);
                document.querySelector('#articlesTable tbody').innerHTML = `
                    <tr><td colspan="4" class="text-center text-danger">Gagal memuat data</td></tr>
                `;
            });
    });
    </script>
@endsection
