<?php

return [
    'dashboard' => [
        'label' => 'Dashboard',
        'icon' => 'fas fa-tachometer-alt',
        'route' => 'admin.dashboard',
        'active' => 'admin.dashboard',
    ],
    'homepage' => [
        'label' => 'Homepage',
        'icon' => 'fas fa-home',
        'route' => 'admin.homepage.index',
        'active' => 'admin.homepage.*',
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
        'icon' => 'fas fa-user-cog',
        'route' => 'admin.users.index',
        'active' => 'admin.users.*',
    ],
    'customers' => [
        'label' => 'Customers',
        'icon' => 'fas fa-users',
        'route' => 'admin.customers.index',
        'active' => 'admin.customers.*',
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
    'coupons' => [
        'label' => 'Coupons',
        'icon' => 'fas fa-ticket-alt',
        'route' => 'admin.coupons.index',
        'active' => 'admin.coupons.*',
    ],
    'transactions' => [
        'label' => 'Transactions',
        'icon' => 'fas fa-money-check-alt',
        'route' => 'admin.transactions.index',
        'active' => 'admin.transactions.*',
    ],
    'payment_banks' => [
        'label' => 'Payment Banks',
        'icon' => 'fas fa-university',
        'route' => 'admin.payment-banks.index',
        'active' => 'admin.payment-banks.*',
    ],
    'api_settings' => [
        'label' => 'API Settings',
        'icon' => 'fas fa-plug',
        'route' => 'admin.api-settings.index',
        'active' => 'admin.api-settings.*',
    ],
    'api_content' => [
        'label' => 'Content',
        'icon' => 'fas fa-images',
        'route' => 'admin.content.index',
        'active' => 'admin.content.*',
    ],
    'api_processed' => [
        'label' => 'Processed',
        'icon' => 'fas fa-check-circle',
        'route' => 'admin.processed.index',
        'active' => 'admin.processed.*',
    ],
    'storage_link' => [
        'label' => 'Storage Link',
        'icon' => 'fas fa-link',
        'route' => 'admin.storage-link.index',
        'active' => 'admin.storage-link.*',
    ],
    'database_migration' => [
        'label' => 'Migration',
        'icon' => 'fas fa-database',
        'route' => 'admin.migration.index',
        'active' => 'admin.migration.*',
    ],
    'settings' => [
        'label' => 'Site Settings',
        'icon' => 'fas fa-cog',
        'route' => 'admin.settings.edit',
        'active' => 'admin.settings.*',
    ],
];
