#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once __DIR__ . '/../setup.php';

use HF\ApiClient\Exception\GatewayException;
use HF\ApiClient\Query\Query;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;

try {
    /**
     * Assuming you have a storeId, proceed to setp two.
     *
     * Since we have a cache setup (via '../setup.php' we will use that
     */
    $cacheKey = sha1('__FILE__');

    // try to get $storeId, $catalogueId from the cache
    @list($storeId) = $cache->getItem($cacheKey, $success);

    if (!($argv[1] ?? false)) {
      die('Give snd code as argument');
    }

    if (($storeId === null) || ! $success) {
        $storeQueryResult = $api->commerce_listStores(
            Query::create()->withFilter('query', 'shop.PLHW')->withPage(1, 1)
        );

        $storeId = $storeQueryResult['data'][0]['id'] ?? '';

        $cache->setItem($cacheKey, [
            $storeId,
        ]);
    }

    $result = $api->commerce_retrieveSandalinosCompositionByCode(
        Query::create()
            ->withParam('code', $argv[1]),
        $storeId
    );

    if ($result && $api->isSuccess()) {
        print_r($result);
    } else {
        print_r($api->getLastResponseBody());
    }
} catch (IdentityProviderException $e) {
    die($e->getMessage());
} catch (GatewayException $e) {
    printf("%s\n\n", $e->getMessage());
    printf('%s', $api->getLastResponseBody());
    die();
}
