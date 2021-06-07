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

/** @var $api ApiClient */
require_once __DIR__ . '/../setup.php';

use HF\ApiClient\ApiClient;
use HF\ApiClient\Exception\GatewayException;
use HF\ApiClient\Query\Query;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;

try {
    $api->commerce_listStores(
        Query::create()
            ->withIncluded('article-groups')
            ->withFilter('query', 'shop.PLHW')
            ->withPage(1, 1)
    );

    // first result
    $storeId = \reset($api->cachedResources['commerce/store'])['id'];

    $results = $api->commerce_listCataloguesOfStore(
        Query::create()
            ->withParam('storeId', $storeId)
            ->withFilter('query', 'Sandalinos Catalogue')
            ->withIncluded('product-groups')
            ->withPage(1, 1)
    );

    $catalogueId = \reset($api->cachedResources['commerce/catalogue'])['id'];

    // pick a random articleGroupId from the included (just for demo)
    $randomProductGroupId = \array_rand(\array_flip(\array_keys($api->cachedResources['commerce/product-group/product-group'])));

    $results = $api->commerce_getProductGroupOfCatalogue(
        Query::create()
            ->withParam('storeId', $storeId)
            ->withParam('catalogueId', $catalogueId)
            ->withParam('productGroupId', $randomProductGroupId)
    );
} catch (IdentityProviderException $e) {
    exit($e->getMessage());
} catch (GatewayException $e) {
    \printf("%s\n\n", $e->getMessage());
    \printf('%s', $api->getLastResponseBody());
    exit();
}

if ($api->isSuccess() && $results) {
    $result = $results['data'];
    \printf("ProductGroup %s : %s (%s)\n",
        $result['id'], $result['attributes']['description'],
        $result['attributes']['code']
    );
} else {
    \printf("Error (%d)\n", $api->getStatusCode());
    \print_r($results);
}
