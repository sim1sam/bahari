<?php

return [
    'standalone' => [
        'dashboard',
    ],

    'groups' => [
        'orders' => [
            'label' => 'Order Management',
            'icon' => 'fas fa-shopping-cart',
            'items' => [
                'orders_create',
                'orders_list',
            ],
        ],
        'account' => [
            'label' => 'Account',
            'icon' => 'fas fa-calculator',
            'items' => [
                'bank_payments',
                'account_heads_list',
                'account_types_list',
                'account_expenses',
            ],
        ],
        'payment' => [
            'label' => 'Payment Settings',
            'icon' => 'fas fa-credit-card',
            'items' => [
                'payment_banks',
                'customer_ledgers',
                'transactions',
            ],
        ],
        'customers' => [
            'label' => 'Customers',
            'icon' => 'fas fa-users',
            'items' => [
                'customers',
            ],
        ],
        'product_content' => [
            'label' => 'Ecommerce',
            'icon' => 'fas fa-box-open',
            'items' => [
                'products',
                'categories',
                'api_brands',
                'api_content',
                'api_processed',
            ],
        ],
        'finance' => [
            'label' => 'Reports',
            'icon' => 'fas fa-chart-line',
            'items' => [
                'reports',
            ],
        ],
        'api' => [
            'label' => 'API Settings',
            'icon' => 'fas fa-plug',
            'items' => [
                'order_api_settings',
                'api_settings',
                'ssl_settings',
            ],
        ],
        'settings' => [
            'label' => 'Settings',
            'icon' => 'fas fa-cog',
            'items' => [
                'homepage',
                'settings_branding',
                'settings_footer',
                'settings_top_bar',
                'settings_website_colors',
                'settings_gtm',
                'coupons',
                'shipping',
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
                'terminal',
                'database_migration',
                'npm_build',
                'storage_link',
            ],
        ],
    ],
];
