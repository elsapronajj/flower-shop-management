<?php

return [
    'jwt_secret' => env('JWT_SECRET', env('APP_KEY')),
    'access_ttl_minutes' => (int) env('JWT_ACCESS_TTL_MINUTES', 15),
    'refresh_ttl_days' => (int) env('JWT_REFRESH_TTL_DAYS', 14),
    'refresh_cookie' => env('JWT_REFRESH_COOKIE', 'flower_shop_refresh_token'),
    'refresh_cookie_secure' => (bool) env('JWT_REFRESH_COOKIE_SECURE', env('APP_ENV') === 'production'),
    'refresh_cookie_same_site' => env('JWT_REFRESH_COOKIE_SAME_SITE', 'lax'),
];
