<?php

return [
    'code' => env('APP_CURRENCY', 'BDT'),
    'symbol' => env('APP_CURRENCY_SYMBOL', '৳'),
    'name' => env('APP_CURRENCY_NAME', 'Taka'),
    'decimals' => 2,
    'free_shipping_threshold' => (float) env('FREE_SHIPPING_THRESHOLD', 2000),
    'shipping_fee' => (float) env('SHIPPING_FEE', 120),
];
