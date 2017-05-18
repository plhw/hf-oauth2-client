<?php


/** @var \HF\ApiClient\Query\Query $query */
$query = $params[0] ?? \HF\ApiClient\Query\Query::create();

return [
    'url'      => '/customer/practices/pos/around-coordinate' . (string) $query,
    'method'   => 'GET',
    'header'   => $query->headers(),
    'response' => [
        'format' => 'json',
        'valid_codes' => ['200'],
    ],
];
