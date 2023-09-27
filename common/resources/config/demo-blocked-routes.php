<?php

return [
    // admin
    ['method' => 'POST', 'name' => 'settings'],
    ['method' => 'POST', 'name' => 'admin/appearance'],
    ['method' => 'POST', 'name' => 'cache/clear'],
    ['method' => 'POST', 'name' => 'artisan/call'],
    ['method' => 'POST', 'name' => 'admin/search/import'],
    ['method' => 'POST', 'name' => 'import-media/single-item'],
    ['method' => 'POST', 'name' => 'sitemap/generate'],

    // css theme
    ['method' => 'POST', 'name' => 'css-theme'],
    ['method' => 'PUT', 'name' => 'css-theme/{css_theme}'],
    ['method' => 'DELETE', 'name' => 'css-theme/{css_theme}'],

    // favicon
    ['method' => 'POST', 'name' => 'uploads/favicon'],

    // localizations
    ['method' => 'DELETE', 'name' => 'localizations/{id}'],
    ['method' => 'PUT', 'name' => 'localizations/{langCode}'],
    ['method' => 'POST', 'name' => 'localizations'],

    // custom pages
    [
        'method' => 'DELETE',
        'name' => 'custom-pages/{custom_page}',
        'origin' => 'admin',
    ],
    [
        'method' => 'PUT',
        'name' => 'custom-pages/{custom_page}',
        'origin' => 'admin',
    ],

    // products/prices
    ['method' => 'POST', 'name' => 'billing/products'],
    ['method' => 'POST', 'name' => 'billing/products/sync'],
    ['method' => 'PUT', 'name' => 'billing/products/{product}'],
    ['method' => 'DELETE', 'name' => 'billing/products/{product}'],

    // subscriptions
    [
        'method' => 'POST',
        'origin' => 'admin',
        'name' => 'billing/subscriptions',
    ],
    [
        'method' => 'PUT',
        'origin' => 'admin',
        'name' => 'billing/subscriptions/{id}',
    ],
    [
        'method' => 'DELETE',
        'origin' => 'admin',
        'name' => 'billing/subscriptions/{id}',
    ],

    // users
    ['method' => 'PUT', 'name' => 'auth/user/password'],
    ['method' => 'PUT', 'origin' => 'admin', 'name' => 'users/{user}'],
    ['method' => 'POST', 'origin' => 'admin', 'name' => 'users'],
    ['method' => 'DELETE', 'name' => 'users/{ids}'],
    ['method' => 'POST', 'name' => 'user-sessions/logout-other'],
    [
        'method' => 'POST',
        'name' => 'auth/user/confirmed-two-factor-authentication',
    ],

    // bans
    ['method' => 'POST', 'name' => 'users/{user}/ban'],
    ['method' => 'DELETE', 'name' => 'users/{user}/unban'],

    // tags
    ['method' => 'POST', 'origin' => 'admin', 'name' => 'tags'],
    ['method' => 'PUT', 'origin' => 'admin', 'name' => 'tags/{id}'],
    ['method' => 'DELETE', 'origin' => 'admin', 'name' => 'tags/{tagIds}'],

    // roles
    ['method' => 'DELETE', 'name' => 'roles/{id}'],
    ['method' => 'PUT', 'name' => 'roles/{id}'],
    ['method' => 'POST', 'name' => 'roles'],
    ['method' => 'POST', 'name' => 'roles/{id}/add-users'],
    ['method' => 'POST', 'name' => 'roles/{id}/remove-users'],

    // CUSTOM DOMAINS
    [
        'method' => 'DELETE',
        'name' => 'custom-domain/{custom_domain}',
        'origin' => 'admin',
    ],
    [
        'method' => 'PUT',
        'name' => 'custom-domain/{custom_domain}',
        'origin' => 'admin',
    ],

    // contact
    ['method' => 'POST', 'name' => 'contact-page'],

    // uploads
    [
        'method' => 'DELETE',
        'name' => 'file-entries/{entryIds}',
        'origin' => 'admin',
    ],

    // admin
    [
        'method' => 'POST',
        'name' => ' s3/cors/upload',
    ],
];
