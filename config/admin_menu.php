<?php

return [
    'standalone' => [
        'dashboard',
    ],

    'groups' => [
        'settings' => [
            'label' => 'Settings',
            'icon' => 'fas fa-cog',
            'items' => [
                'settings',
            ],
        ],
        'product_content' => [
            'label' => 'Product & Content',
            'icon' => 'fas fa-box-open',
            'items' => [
                'homepage',
                'products',
                'categories',
                'api_content',
                'api_processed',
            ],
        ],
        'payment' => [
            'label' => 'Payment',
            'icon' => 'fas fa-credit-card',
            'items' => [
                'transactions',
                'payment_banks',
                'api_settings',
            ],
        ],
        'orders' => [
            'label' => 'Order Management',
            'icon' => 'fas fa-shopping-cart',
            'items' => [
                'orders',
                'coupons',
            ],
        ],
        'customers' => [
            'label' => 'Customers',
            'icon' => 'fas fa-users',
            'items' => [
                'customers',
            ],
        ],
        'users' => [
            'label' => 'Users',
            'icon' => 'fas fa-user-cog',
            'items' => [
                'users',
                'roles',
            ],
        ],
        'terminal' => [
            'label' => 'Terminal',
            'icon' => 'fas fa-terminal',
            'items' => [
                'database_migration',
                'storage_link',
            ],
        ],
    ],
];
