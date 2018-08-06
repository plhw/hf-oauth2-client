#!/usr/bin/env php
<?php

declare(strict_types=1);

use HF\ApiClient\Exception\GatewayException;
use HF\ApiClient\Query\Query;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;

require_once __DIR__ . '/../setup.php';

try {
    // we must get a storeId and a catalogueId, which is different per environment.
    // as an example i'll show how you can do a search by name

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
        ->withFilter('code', 'S:CM:CV') // bekleding!
        ->withPage(1, 1);

    $results        = $api->commerce_listProductGroupsOfCatalogue($query, $storeId, $catalogueId);

    $productGroupId = $results['data'][0]['id'] ?? '';

    // once we have the storeId and the catalogue id, we can get list the product groups
    $query = Query::create();

    $results = $api->commerce_listProductsOfProductGroup($query, $storeId, $catalogueId, $productGroupId);
} catch (IdentityProviderException $e) {
    die($e->getMessage());
} catch (GatewayException $e) {
    printf("%s\n\n", $e->getMessage());
    printf('%s', $api->getLastResponseBody());
    die();
}

if ($api->isSuccess() && $results) {
    foreach ($results['data'] as $result) {
        printf("Product %s : %s (%s)\n",
            $result['id'],
            $result['attributes']['description'],
            $result['attributes']['code']
            );
    }
} else {
    printf("Error (%d)\n", $api->getStatusCode());
    print_r($results);
}
