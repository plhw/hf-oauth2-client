#!/usr/bin/env php
<?php

declare(strict_types = 1);

$autoloadFiles = [
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
        ],
    ],
    'plugins' => ['serializer'],
]);

$options = Options::fromArray(include('.hf-api-client-secrets.php'));

$api = ApiClient::createClient($options, $cache);

try {
    $results = $api->customer_posAroundCoordinate('52.3629882,4.8593175', 15000, 'insoles');
} catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
    die($e->getMessage());
} catch (\Exception $e) {
    die($e->getMessage());
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
