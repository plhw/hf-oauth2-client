<?php

return [
    'url'      => '/commerce/article-groups/' . $params[0],
    'method'   => 'GET',
    'response' => [
        'format' => 'json',
        'valid_codes' => ['200'],
    ],
];
