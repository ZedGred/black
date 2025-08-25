<?php
return [
    'admin' => [
        // General App
        ['title' => 'Home', 'route' => 'home', 'icon' => 'fas fa-users'],
        ['title' => 'Profile', 'route' => 'profile', 'icon' => 'fas fa-users'],
        //['title' => 'Stats', 'route' => 'home', 'icon' => 'fas fa-users'],
        //['title' => 'Library', 'route' => 'home', 'icon' => 'fas fa-users'],
        
        // Master Data Section
        ['title' => 'Users', 'route' => 'home', 'icon' => 'fas fa-users'],
        //['title' => 'Role', 'route' => 'home', 'icon' => 'fas fa-users'],
        //['title' => 'Setting', 'route' => 'home', 'icon' => 'fas fa-users'],
    ],
    
    'writer' => [
        // General App
        ['title' => 'Home', 'route' => 'home', 'icon' => 'fas fa-file-alt'],
        ['title' => 'Profile', 'route' => 'profile', 'icon' => 'fas fa-file-alt'],
        //['title' => 'Following', 'route' => 'home', 'icon' => 'fas fa-users'],
        
        ['title' => 'Write', 'route' => 'write', 'icon' => 'fas fa-users'],
        ['title' => 'Stories', 'route' => 'stories', 'icon' => 'fas fa-users'],
    ],
    
    'user' => [
        // General App
        ['title' => 'Home', 'route' => 'home', 'icon' => 'fas fa-user'],
        ['title' => 'Profile', 'route' => 'profile', 'icon' => 'fas fa-user'],
        //['title' => 'Stats', 'route' => 'home', 'icon' => 'fas fa-users'],
        //['title' => 'Following', 'route' => 'home', 'icon' => 'fas fa-users'],
        ['title' => 'Register Writer', 'route' => 'registerwriter', 'icon' => 'fas fa-users'],
        
        //['title' => 'Library', 'route' => 'home', 'icon' => 'fas fa-users'],
    ],
];
