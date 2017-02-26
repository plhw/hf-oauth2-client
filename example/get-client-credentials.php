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
        require_once $autoloadFile;
    }
}

use HF\OAuth2\Client\Provider\PLHWProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use Zend\Cache\StorageFactory;

$env          = 'development';
$clientId     = 'get from us';
$clientSecret = 'get from us';
$scope        = 'get_from_us';

$provider = new PLHWProvider([
    'clientId'     => $clientId,
    'clientSecret' => $clientSecret,
], [], $env);

// setup a cache
$cache = StorageFactory::factory([
    'adapter' => [
        'name'    => 'filesystem',
        'options' => [
            'cache_dir' => './data/cache',
        ],
    ],
    'plugins' => ['serializer'],
]);

$cacheKey = sha1('plhw-oauth2-token' . $scope);

/** @var AccessToken $accessToken */
try {
    // try to get a token from the cache
    $accessToken = $cache->getItem($cacheKey, $success);

    if ($accessToken === null || ! $success || $accessToken->hasExpired()) {
        // try to get a new access token
        $accessToken = $provider->getAccessToken('client_credentials', [
            'scope' => $scope,
        ]);

        $cache->setItem($cacheKey, $accessToken);
    }
} catch (IdentityProviderException $e) {
    print_r($e->getResponseBody());

    exit(1);
}

print $accessToken->getToken() . PHP_EOL;
print_r($accessToken->getValues()) . PHP_EOL;

return $accessToken;
