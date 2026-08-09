<?php

return [

    'enabled' => env('INDEXNOW_ENABLED', true),

    'engines' => [
        'www.bing.com',
        'yandex.com',
        'search.seznam.cz',
        'searchadvisor.naver.com',
    ],

    'key_file' => 'indexnow-key.txt',

    'cache_ttl' => 30,

];
