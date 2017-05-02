<?php

/** @var \HF\ApiClient\Query\Query $query */
$query   = $params[0] ?? \HF\ApiClient\Query\Query::create();
$storeId = $params[1] ?? null;
$catalogueId = $params[2] ?? null;
$productGroupId = $params[3] ?? null;
$productId = $params[4] ?? null;

if (! $storeId) {
    throw new \Exception('You must provide a storeId');
}
if (! $catalogueId) {
    throw new \Exception('You must provide a catalogueId');
}
if (! $productGroupId) {
    throw new \Exception('You must provide a productGroupId');
}
if (! $productId) {
    throw new \Exception('You must provide a productId');
}

return [
    'url'      => sprintf('/commerce/stores/%s/catalogues/%s/product-groups/%s/products/%s%s', $storeId, $catalogueId, $productGroupId, $productId, (string) $query),
    'method'   => 'GET',
    'response' => [
        'format'      => 'json',
        'valid_codes' => ['200'],
    ],
];
