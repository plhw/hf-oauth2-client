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
    // we must get a storeId, which is different per environment.
    // as an example i'll show how you can doe a search by name
    $query = \HF\ApiClient\Query\Query::create()
        ->withIncluded('articleGroups')
        ->withFilter('query', 'shop.PLHW')
        ->withPage(1, 1);

    $results = $api->commerce_listStores($query);
    $storeId = $results['data'][0]['id'] ?? '';

    // pick a random articleGroupId from the included (just for demo)
    $randomArticleGroupId = array_rand(array_flip(array_column($results['included'], 'id')));

    $results = $api->commerce_getArticleGroupOfStore(null, $storeId, $randomArticleGroupId);
} catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
    die($e->getMessage());
} catch (\HF\ApiClient\Exception\GatewayException $e) {
    printf("%s\n\n", $e->getMessage());
    printf('%s', $api->getLastResponseBody());
    die();
}

if ($api->isSuccess() && $results) {
    $result = $results['data'];
    printf("ArticleGroup %s : %s (%s)\n", $result['id'], $result['attributes']['description'], $result['attributes']['code']);
} else {
    printf("Error (%d)\n", $api->getStatusCode());
    print_r($results);
}
