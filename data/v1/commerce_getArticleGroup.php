<?php

/** @var \HF\ApiClient\Query\Query $query */
$query = $params[0] ?? \HF\ApiClient\Query\Query::create();

return [
    'url'      => '/commerce/article-groups/' . (string) $query,
    'method'   => 'GET',
    'response' => [
        'format' => 'json',
        'valid_codes' => ['200'],
    ],
];
