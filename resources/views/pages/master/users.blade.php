@extends('layouts.app')

@section('title', 'User Management')

@section('dashboard-content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fw-bold">User Management</h1>
        <a href="{{ route('home') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Add User
        </a>
    </div>

    <div class="table-responsive mb-5">
        <table class="table table-striped align-middle">
            <thead class="table-light">
                <tr>
                    <th scope="col" class="p-3 text-center" style="border-top-left-radius: 12px;">Picture</th>
                    <th scope="col" class="p-3">Name</th>
                    <th scope="col" class="p-3">Email</th>
                    <th scope="col" class="p-3">Role</th>
                    <th scope="col" class="p-3 text-end" style="border-top-right-radius: 12px;">Actions</th>
                </tr>
            </thead>
            <tbody id="userTableBody">
                <tr>
                    <td colspan="5" class="text-center">Loading...</td>
                </tr>
            </tbody>
        </table>
    </div>

    <nav>
        <ul id="pagination" class="pagination justify-content-center mt-4"></ul>
    </nav>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const container = document.getElementById('userTableBody');
            const pagination = document.getElementById('pagination');

            async function loadUsers(page = 1) {
                try {
                    const res = await axios.get(`/api/master-data/users?page=${page}`, {
                        headers: {
                            'Accept': 'application/json'
                        },
                        withCredentials: true
                    });

                    const users = res.data.data; // pagination object
                    const userList = users.data; // array user
                    container.innerHTML = '';

                    if (userList.length === 0) {
                        container.innerHTML =
                            '<tr><td colspan="5" class="text-center">No users found</td></tr>';
                        pagination.innerHTML = '';
                        return;
                    }

                    let rows = '';
                    userList.forEach(user => {
                        const picture = user.profile_picture || '/assets/media/avatars/blank.png';
                        rows += `
               <tr class="align-middle" style="border-radius: 0.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
    <td>
        <div style="padding: 8px 0;">
            <img src="${picture}" class="rounded-circle" width="40" height="40">
        </div>
    </td>
    <td>
        <div style="padding: 8px 0;">${user.name}</div>
    </td>
    <td>
        <div style="padding: 8px 0;">${user.email}</div>
    </td>
    <td>
        <div style="padding: 8px 0;">${user.role || '-'}</div>
    </td>
    <td class="text-end">
        <div style="padding: 8px 0;" class="dropdown">
    <a href="#" class="text-muted" role="button" data-bs-toggle="dropdown" data-bs-display="static">
        <i class="bi bi-three-dots-vertical"></i>
    </a>
    <ul class="dropdown-menu dropdown-menu-end">
        <li><a class="dropdown-item" href="/users/${user.id}">View</a></li>
        <li><a class="dropdown-item" href="/users/${user.id}/edit">Edit</a></li>
        <li><button class="dropdown-item text-danger" onclick="deleteUser('${user.id}')">Delete</button></li>
    </ul>
</div>

    </td>
</tr>


            `;
                    });

                    container.innerHTML = rows;
                    renderPagination(users); // kirim pagination object
                } catch (err) {
                    console.error(err);
                    container.innerHTML =
                        '<tr><td colspan="5" class="text-center text-danger">Failed to load users</td></tr>';
                }
            }


            function renderPagination(users) {
                pagination.innerHTML = '';
                users.links.forEach(link => {
                    const disabled = !link.url ? 'disabled' : '';
                    const active = link.active ? 'active' : '';
                    const page = new URL(link.url || window.location.href).searchParams.get('page') || 1;

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
                        loadUsers(a.dataset.page);
                    });
                });
            }

            window.deleteUser = async function(id) {
                if (!confirm('Are you sure you want to delete this user?')) return;
                try {
                    await axios.delete(`/api/master-data/users/${id}`, {
                        headers: {
                            'Accept': 'application/json'
                        },
                        withCredentials: true
                    });
                    loadUsers();
                } catch (err) {
                    console.error(err);
                    alert(err);
                }
            }

            loadUsers('${id}');
        });
    </script>
@endsection
