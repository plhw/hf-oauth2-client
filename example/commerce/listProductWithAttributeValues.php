#!/usr/bin/env php
<?php

declare(strict_types=1);

$autoloadFiles = [
    __DIR__ . '/../../../../vendor/autoload.php',
    __DIR__ . '/../../../vendor/autoload.php',
    __DIR__ . '/../../vendor/autoload.php',
    __DIR__ . '/../vendor/autoload.php',
    __DIR__ . '/vendor/autoload.php',
];

chdir(__DIR__ . '/..');

foreach ($autoloadFiles as $autoloadFile) {
    if (file_exists($autoloadFile)) {
        chdir(dirname($autoloadFile) . '/..');
        require_once $autoloadFile;
    }
}

use HF\ApiClient\ApiClient;
use HF\ApiClient\Options\Options;
use Zend\Cache\StorageFactory;

if (! file_exists('.hf-api-client-secrets.php')) {
    die('copy example/.hf-api-client-secrets.php.dist to APP_ROOT/.hf-api-client-secrets.php');
}

// optional but will then use filesystem default tmp directory
$cache = StorageFactory::factory([
    'adapter' => [
        'name'    => 'filesystem',
        'options' => [
            'cache_dir' => './data/cache',
            'dir_level' => 0,
        ],
    ],
    'plugins' => ['serializer'],
]);

$options = Options::fromArray(include('.hf-api-client-secrets.php'));

$api = ApiClient::createClient($options, $cache);

try {
    // we must get a storeId and a catalogueId, which is different per environment.
    // as an example i'll show how you can do a search by name

    $query = \HF\ApiClient\Query\Query::create()
        ->withFilter('query', 'shop.PLHW')
        ->withPage(1, 1);

    $results = $api->commerce_listStores($query);
    $storeId = $results['data'][0]['id'] ?? '';

    // now we search for a specific catalogue within that store
    $query = \HF\ApiClient\Query\Query::create()
        ->withFilter('query', 'Sandalinos Catalogue')
        ->withPage(1, 1);

    $results     = $api->commerce_listCataloguesOfStore($query, $storeId);
    $catalogueId = $results['data'][0]['id'] ?? '';

    $query = \HF\ApiClient\Query\Query::create()
        ->withFilter('code', 'SND')// sandlinso custome made groep!
        ->withPage(1, 1);

    $results        = $api->commerce_listProductGroupsOfCatalogue($query, $storeId, $catalogueId);
    $productGroupId = $results['data'][0]['id'] ?? '';





    // once we have the storeId and the catalogue id, we can get list the product groups
    $query = \HF\ApiClient\Query\Query::create();
    $query = $query->withIncluded('attributeValues');
    $results = $api->commerce_listProductsOfProductGroup($query, $storeId, $catalogueId, $productGroupId);

    // this is new and is an cache of loaded resources, by type and id.
} catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
    die($e->getMessage());
} catch (\HF\ApiClient\Exception\GatewayException $e) {
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

       $attributeValues= $result['relationships']['attribute_values']['data'] ?? [];

       foreach($attributeValues as $attributeValue) {
           printf(" attributeValue : %s\n", $api->cachedResources[$attributeValue['type']][$attributeValue['id']]['value']);
       }

    }
} else {
    printf("Error (%d)\n", $api->getStatusCode());
    print_r($results);
}
