<?php

return [
    [
        'property' => 'og:url',
        'content' =>  '{{url.customPage}}',
    ],
    [
        'property' => 'og:title',
        'content' => '{{page.title}} - {{site_name}}',
    ],
    [
        'property' => 'og:description',
        'content' => '{{page.body}}',
    ],
];
