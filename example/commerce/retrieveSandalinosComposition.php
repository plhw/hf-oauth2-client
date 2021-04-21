<?php

/**
 * Project 'Healthy Feet' by Podolab Hoeksche Waard.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @see       https://plhw.nl/
 *
 * @copyright Copyright (c) 2010 - 2021 bushbaby multimedia. (https://bushbaby.nl)
 * @author    Bas Kamer <baskamer@gmail.com>
 * @license   Proprietary License
 *
 * @package   plhw/hf-api-client
 */

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
    $cacheKey = \sha1('__FILE__');

    // try to get $storeId, $catalogueId from the cache
    @[$storeId] = $cache->getItem($cacheKey, $success);

    if (! ($argv[1] ?? false)) {
        exit('Give snd code as argument');
    }

    if ((null === $storeId) || ! $success) {
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
        \print_r($result);
    } else {
        \print_r($api->getLastResponseBody());
    }
} catch (IdentityProviderException $e) {
    exit($e->getMessage());
} catch (GatewayException $e) {
    \printf("%s\n\n", $e->getMessage());
    \printf('%s', $api->getLastResponseBody());
}
