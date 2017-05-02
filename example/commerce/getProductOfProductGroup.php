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
        ->withFilter('code', 'B')// bekleding!
        ->withPage(1, 1);

    $results = $api->commerce_listProductGroupsOfCatalogue($query, $storeId, $catalogueId);

    $productGroupId = $results['data'][0]['id'] ?? '';

    // once we have a storeId, catalogue id, productgroup id, we can get a list of products
    $results = $api->commerce_listProductsOfProductGroup(null, $storeId, $catalogueId, $productGroupId);


    // pick a random productId from the data (just for demo)
    $randomProductId = array_rand(array_flip(array_column($results['data'], 'id')));

    $results = $api->commerce_getProductOfProductGroup(null, $storeId, $catalogueId, $productGroupId, $randomProductId);
} catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
    die($e->getMessage());
} catch (\HF\ApiClient\Exception\GatewayException $e) {
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
