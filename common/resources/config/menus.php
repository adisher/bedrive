<?php

use Common\Pages\LoadCustomPageMenuItems;

return [
    [
        'name' => 'Custom Page',
        'type' => 'customPage',
        'itemsLoader' => LoadCustomPageMenuItems::class,
    ],
];
