<?php

/** @var \HF\ApiClient\Query\Query $query */
$query = $params[0] ?? \HF\ApiClient\Query\Query::create();
$storeId = $params[1] ?? null;
$articleGroupId = $params[2] ?? null;

if (! $storeId) {
    throw new \Exception('You must provide a storeId');
}


if (! $articleGroupId) {
    throw new \Exception('You must provide a articleGroupId');
}

return [
    'url'      => sprintf('/commerce/stores/%s/article-groups/%s%s', $storeId, $articleGroupId, $query),
    'method'   => 'GET',
    'response' => [
        'format' => 'json',
        'valid_codes' => ['200'],
    ],
];
