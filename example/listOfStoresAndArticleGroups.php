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

$query = \HF\ApiClient\Query\Query::create();

try {
    $results = $api->commerce_listStores($query);
} catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
    die($e->getMessage());
} catch (\HF\ApiClient\Exception\GatewayException $e) {
    printf("%s\n\n", $e->getMessage());
    printf('%s', $api->getLastResponseBody());
    die();
}

if ($api->isSuccess() && $results) {
    foreach ($results['data'] as $result) {
        printf("Store %s : %s (%s)\n", $result['id'], $result['attributes']['name'],
            $result['attributes']['description']);

        foreach ($result['relationships']['article_groups']['data'] as $rel) {
            try {
                $results = $api->commerce_getArticleGroup($rel['id']);

                if ($api->isSuccess() && $results) {
                    printf(
                        "ArticleGroup %s : %s (%s:%s)\n",
                        $results['data']['id'],
                        $results['data']['attributes']['description'],
                        $results['data']['attributes']['ledger_number'],
                        $results['data']['attributes']['code']
                    );
                } else {
                    printf("Error (%d)\n", $api->getStatusCode());
                    print_r($results);
                }
            } catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
                die($e->getMessage());
            } catch (\Exception $e) {
                die($e->getMessage());
            }
        }
    }
} else {
    printf("Error (%d)\n", $api->getStatusCode());
    print_r($results);
}
