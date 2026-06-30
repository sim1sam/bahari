<?php

return [
    'code' => env('APP_CURRENCY', 'BDT'),
    'symbol' => env('APP_CURRENCY_SYMBOL', '৳'),
    'name' => env('APP_CURRENCY_NAME', 'Taka'),
    'decimals' => 2,
    'free_shipping_threshold' => (float) env('FREE_SHIPPING_THRESHOLD', 2000),
    'shipping_fee_inside_dhaka' => (float) env('SHIPPING_FEE_INSIDE_DHAKA', 80),
    'shipping_fee_outside_dhaka' => (float) env('SHIPPING_FEE_OUTSIDE_DHAKA', 150),
];
