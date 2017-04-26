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
            'namespace' => 'a',
        ],
    ],
    'plugins' => ['serializer'],
]);

$options = Options::fromArray(include('.hf-api-client-secrets.php'));

$api = ApiClient::createClient($options, $cache);

$query = \HF\ApiClient\Query\Query::create()
    ->withFilter('around', '52.3629882,4.8593175')
    ->withFilter('distance', 50000)
    ->withFilter('product', 'insoles')
    ->withPage(1, 3)
    ->withSort('name', false);

try {
    $results = $api->customer_posAroundCoordinate($query);
} catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
    die($e->getMessage());
} catch (\HF\ApiClient\Exception\GatewayException $e) {
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
