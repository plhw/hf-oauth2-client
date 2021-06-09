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

use HF\ApiClient\ApiClient;
use HF\ApiClient\Exception\ClientException;
use HF\ApiClient\Exception\GatewayException;
use HF\ApiClient\Query\Query;

/** @var $api ApiClient */
require_once __DIR__ . '/../setup.php';

try {
    $results = $api->commerce_listStores(
        Query::create()
            ->withFilter('query', 'shop.PLHW')
            ->withPage(1, 1)
    );

    $storeId = $results['data'][0]['id'] ?? '';

    $results = $api->commerce_listCataloguesOfStore(
        Query::create()
            ->withFilter('query', 'Sandalinos Catalogue')
            ->withPage(1, 1)
            ->withParam('storeId', $storeId)
    );

    $catalogueId = $results['data'][0]['id'] ?? '';

    $results = $api->commerce_listProductGroupsOfCatalogue(
        Query::create()
            ->withFilter('code', 'S:CM:CV')// bekleding!
            ->withPage(1, 1)
            ->withParam('storeId', $storeId)
            ->withParam('catalogueId', $catalogueId)
    );

    $productGroupId = $results['data'][0]['id'] ?? '';

    // once we have a storeId, catalogue id, productgroup id, we can get a list of products
    $results = $api->commerce_listProductsOfProductGroup(
        Query::create()
            ->withParam('storeId', $storeId)
            ->withParam('catalogueId', $catalogueId)
            ->withParam('productGroupId', $productGroupId)
    );

    // pick a random productId from the data (just for demo)
    $randomProductId = \array_rand(\array_flip(\array_column($results['data'], 'id')));

    $results = $api->commerce_getProductOfProductGroup(
        Query::create()
            ->withParam('storeId', $storeId)
            ->withParam('catalogueId', $catalogueId)
            ->withParam('productGroupId', $productGroupId)
            ->withParam('productId', $randomProductId)
    );
} catch (ClientException $e) {
    \printf("%s\n\n", $e->getMessage());
    exit();
} catch (GatewayException $e) {
    \printf("%s\n\n", $e->getMessage());
    \printf('%s', $api->getLastResponseBody());
    exit();
} catch (\Exception $e) {
    \printf("%s\n\n", $e->getMessage());
} finally {
    if ($api->isSuccess()) {
        // do something with $results (which is the parsed response object)
        dump($results);

        // or do something with $api->cachesResources (which contains a (flattened) array of json-api resources by resource type type)
        dump($api->cachedResources);

        $result = $results['data'];
        \printf("Product %s : %s (%s)\n",
            $result['id'], $result['attributes']['description'],
            $result['attributes']['code']
        );
    }
}
