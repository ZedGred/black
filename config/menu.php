<?php
return [
    [
        'title' => 'Home',
        'route' => 'home',
        'icon' => 'fas fa-home',
        'permissions' => ['menu.home'],
    ],
    [
        'title' => 'Profile',
        'route' => 'profile',
        'icon' => 'fas fa-user',
        'permissions' => ['articles.view'],
    ],
    [
        'title' => 'Write',
        'route' => 'write',
        'icon' => 'fas fa-pen',
        'permissions' => ['articles.create', 'articles.update'], // bisa lebih dari 1
    ],
    [
        'title' => 'Library',
        'route' => 'published',
        'icon' => 'bi bi-bookmarks-fill',
        'permissions' => ['articles.view'],
    ],
    [
        'title' => 'Stories',
        'route' => 'published',
        'icon' => 'fas fa-book',
        'permissions' => ['articles.create'],
    ],
    [
        'title' => 'Users',
        'route' => 'users.index',
        'icon' => 'fas fa-users',
        'permissions' => ['users.view', 'users.create', 'users.update', 'users.delete'],
    ],
];


