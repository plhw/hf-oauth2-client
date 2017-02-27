<?php

$aroundCoordinate = $params[0] ?? null;
$distance         = $params[1] ?? null;
$product          = $params[2] ?? null;
$country          = $params[3] ?? null;

$query           = [];
$query['filter'] = [];

if ($aroundCoordinate) {
    $query['filter']['around'] = (string) $aroundCoordinate;
}

if ($distance) {
    $query['filter']['distance'] = (int) $distance;
}

if ($product) {
    $query['filter']['product'] = $product;
}

if ($country) {
    $query['filter']['country'] = $country;
}

$queryString = http_build_query($query);
$queryString = $queryString ? '?' . $queryString : '';

return [
    'url'      => '/commerce/article-groups' . $queryString,
    'method'   => 'POST',
    'response' => [
        'format' => 'json',
        'valid_codes' => ['200'],
    ],
];
