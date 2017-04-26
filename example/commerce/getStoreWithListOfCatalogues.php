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
        ->withIncluded('catalogues')
        ->withPage(1, 1);

    $results = $api->commerce_getStore($query, 'a0713068-8c35-51da-b578-a3cce5978221');
} catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
    die($e->getMessage());
} catch (\HF\ApiClient\Exception\GatewayException $e) {
    printf("%s\n\n", $e->getMessage());
    printf('%s', $api->getLastResponseBody());
    die();
}

if ($api->isSuccess() && $results) {
    $result = $results['data'];
    printf("Store %s : %s (%s)\n", $result['id'], $result['attributes']['name'],
        $result['attributes']['description']);

    foreach ($result['relationships']['catalogues']['data'] as $rel) {
        foreach ($results['included'] as $include) {
            if ($include['id'] === $rel['id']) {
                printf(
                    "Catalogue %s : %s (%s)\n",
                    $include['id'],
                    $include['attributes']['name'],
                    $include['attributes']['description']
                );
            }
        }
    }
} else {
    printf("Error (%d)\n", $api->getStatusCode());
    print_r($results);
}
