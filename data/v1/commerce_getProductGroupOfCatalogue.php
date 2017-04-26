<?php

/** @var \HF\ApiClient\Query\Query $query */
$query          = $params[0] ?? \HF\ApiClient\Query\Query::create();
$storeId        = $params[1] ?? null;
$catalogueId    = $params[2] ?? null;
$productGroupId = $params[3] ?? null;

if (! $storeId) {
    throw new \Exception('You must provide a storeId');
}

if (! $catalogueId) {
    throw new \Exception('You must provide a catalogueId');
}

if (! $productGroupId) {
    throw new \Exception('You must provide a productGroupId');
}

return [
    'url'      => sprintf('/commerce/stores/%s/catalogues/%s/product-groups/%s%s', $storeId, $catalogueId, $productGroupId, $query),
    'method'   => 'GET',
    'response' => [
        'format'      => 'json',
        'valid_codes' => ['200'],
    ],
];
