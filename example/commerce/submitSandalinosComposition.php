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

    if (($storeId === null) || ! $success) {
        $storeQueryResult = $api->commerce_listStores(
            Query::create()->withFilter('query', 'shop.PLHW')->withPage(1, 1)
        );

        $storeId = $storeQueryResult['data'][0]['id'] ?? '';

        $cache->setItem($cacheKey, [
            $storeId,
        ]);
    }

    printf("We have collected these id's to work with '%s'\n\n", implode("', '", [
        $storeId,
    ]));

    $result = $api->commerce_submitSandalinosComposition(
        Query::create()
            ->withParam('name', 'Some One')
            ->withParam('email', 'someone@example.com')
            ->withParam('locality', 'Kalverstraat 1, Amsterdam, NL')
            ->withParam('composition', [
                'model' => 'lotus-2016',
                'parts' => [
                    'SND:S:lotus-2016.G.001', // Shaft Lotus-2016 Smooth leather Black
                    'SND:B:NONE.NONE',        // Cover None
                    'SND:V:A.000',            // Footbed Alcatara White
                    'SND:TZ:NONE.NONE',       // Midsole None
                    'SND:LZ:AS.001',          // Outsole Astro Zwart
                ],
            ]),
        $storeId
    );

    if ($api->isSuccess()) {
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
