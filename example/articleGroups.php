#!/usr/bin/env php
<?php

declare(strict_types = 1);

$autoloadFiles = [
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
        ],
    ],
    'plugins' => ['serializer'],
]);

$options = Options::fromArray(include('.hf-api-client-secrets.php'));

$api = ApiClient::createClient($options, $cache);

try {
    $results = $api->commerce_listArticleGroups();
} catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
    die($e->getMessage());
} catch (\Exception $e) {
    die($e->getMessage());
}

if ($api->isSuccess() && $results) {
    foreach ($results['data'] as $result) {
        printf("ArticleGroup %s : %s (%s)\n", $result['id'], $result['attributes']['description'], $result['attributes']['code']);
    }
} else {
    printf("Error (%d)\n", $api->getStatusCode());
    print_r($results);
}

try {
    $results = $api->commerce_getArticleGroup('fc7d0810-cc77-5c90-bea4-b92f6115a8a9');
} catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
    die($e->getMessage());
} catch (\Exception $e) {
    die($e->getMessage());
}
if ($api->isSuccess()) {
    print_r($results);
} else {
    printf("Error (%d)\n", $api->getStatusCode());
    print_r($results);
}
