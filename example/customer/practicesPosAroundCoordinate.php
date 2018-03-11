#!/usr/bin/env php
<?php

declare(strict_types=1);

use HF\ApiClient\Exception\GatewayException;
use HF\ApiClient\Query\Query;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;

require_once __DIR__ . '/../setup.php';

$query = Query::create()
    ->withFilter('around', '52.3629882,4.8593175')
    ->withFilter('distance', 50000)
    ->withFilter('product', 'sandalinos')
    ->withPage(1, 3)
    ->withSort('name', false);

try {
    $results = $api->customer_listPosAroundCoordinate($query);
} catch (IdentityProviderException $e) {
    die($e->getMessage());
} catch (GatewayException $e) {
    printf("%s\n\n", $e->getMessage());
    printf('%s', $api->getLastResponseBody());
    die();
}

if ($api->isSuccess()) {
    foreach ($results['data'] as $result) {
        printf("Practice %s on %skm\n", $result['attributes']['name'],
            round(($result['attributes']['distance'] / 100)) / 10);
        printf(" - sells %s\n", implode(', ', $result['attributes']['products']));
    }
} else {
    printf("Error (%d)\n", $api->getStatusCode());
    print_r($results);
}
