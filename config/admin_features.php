<?php

return [
    'dashboard' => [
        'label' => 'Dashboard',
        'icon' => 'fas fa-tachometer-alt',
        'route' => 'admin.dashboard',
        'active' => 'admin.dashboard',
    ],
    'products' => [
        'label' => 'Products',
        'icon' => 'fas fa-tshirt',
        'route' => 'admin.products.index',
        'active' => 'admin.products.*',
    ],
    'categories' => [
        'label' => 'Categories',
        'icon' => 'fas fa-tags',
        'route' => 'admin.categories.index',
        'active' => 'admin.categories.*',
    ],
    'users' => [
        'label' => 'Users',
        'icon' => 'fas fa-users',
        'route' => 'admin.users.index',
        'active' => 'admin.users.*',
    ],
    'roles' => [
        'label' => 'Roles',
        'icon' => 'fas fa-user-shield',
        'route' => 'admin.roles.index',
        'active' => 'admin.roles.*',
    ],
    'orders' => [
        'label' => 'Orders',
        'icon' => 'fas fa-shopping-cart',
        'route' => 'admin.orders.index',
        'active' => 'admin.orders.*',
    ],
    'settings' => [
        'label' => 'Site Settings',
        'icon' => 'fas fa-cog',
        'route' => 'admin.settings.edit',
        'active' => 'admin.settings.*',
    ],
];
