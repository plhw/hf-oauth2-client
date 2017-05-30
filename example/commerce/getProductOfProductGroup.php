#!/usr/bin/env php
<?php

declare(strict_types=1);

use HF\ApiClient\Exception\GatewayException;
use HF\ApiClient\Query\Query;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;

require_once __DIR__ . '/../setup.php';

try {
    $query = Query::create()
        ->withFilter('query', 'shop.PLHW')
        ->withPage(1, 1);

    $results = $api->commerce_listStores($query);
    $storeId = $results['data'][0]['id'] ?? '';

    // now we search for a specific catalogue within that store
    $query = Query::create()
        ->withFilter('query', 'Sandalinos Catalogue')
        ->withPage(1, 1);

    $results     = $api->commerce_listCataloguesOfStore($query, $storeId);
    $catalogueId = $results['data'][0]['id'] ?? '';

    $query = Query::create()
        ->withFilter('code', 'SND:B')// bekleding!
        ->withPage(1, 1);

    $results = $api->commerce_listProductGroupsOfCatalogue($query, $storeId, $catalogueId);

    $productGroupId = $results['data'][0]['id'] ?? '';

    // once we have a storeId, catalogue id, productgroup id, we can get a list of products
    $results = $api->commerce_listProductsOfProductGroup(null, $storeId, $catalogueId, $productGroupId);

    // pick a random productId from the data (just for demo)
    $randomProductId = array_rand(array_flip(array_column($results['data'], 'id')));

    $results = $api->commerce_getProductOfProductGroup(null, $storeId, $catalogueId, $productGroupId, $randomProductId);
} catch (IdentityProviderException $e) {
    die($e->getMessage());
} catch (GatewayException $e) {
    printf("%s\n\n", $e->getMessage());
    printf('%s', $api->getLastResponseBody());
    die();
}

if ($api->isSuccess() && $results) {
    $result = $results['data'];
    printf("Product %s : %s (%s)\n",
        $result['id'], $result['attributes']['description'],
        $result['attributes']['code']
    );
} else {
    printf("Error (%d)\n", $api->getStatusCode());
    print_r($results);
}
