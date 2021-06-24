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
use HF\ApiClient\Exception\GatewayException;
use HF\ApiClient\Query\Query;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;

/** @var $api ApiClient */
require_once __DIR__ . '/../setup.php';

try {
    // we must get a storeId and a catalogueId, which is different per environment.
    // as an example i'll show how you can do a search by name

    $query = Query::create()
        ->withFilter('query', 'shop.PLHW')
        ->withPage(1, 1);

    $api->commerce_listStores($query);

    // first result
    $storeId = \reset($api->cachedResources['commerce/store'])['id'];

    // now we search for a specific catalogue within that store
    $query = Query::create()
        ->withFilter('query', 'Sandalinos Catalogue')
        ->withParam('storeId', $storeId)
        ->withPage(1, 1);

    $api->commerce_listCataloguesOfStore($query);

    $catalogueId = \reset($api->cachedResources['commerce/catalogue'])['id'];

    $query = Query::create()
        ->withFilter('code', 'S:CM')// sandlinos custom made groep!
        ->withParam('storeId', $storeId)
        ->withParam('catalogueId', $catalogueId)
        ->withPage(1, 1);

    $api->commerce_listProductGroupsOfCatalogue($query);

    $productGroupId = \reset($api->cachedResources['commerce/product-group/product-group'])['id'];

    // once we have the storeId and the catalogue id, we can get list the product groups
    $query = Query::create()
        ->withIncluded('assigned-values')
        ->withParam('storeId', $storeId)
        ->withParam('catalogueId', $catalogueId)
        ->withParam('productGroupId', $productGroupId);

    $results = $api->commerce_listProductsOfProductGroup($query);

    // this is new and is an cache of loaded resources, by type and id.
} catch (IdentityProviderException $e) {
    exit($e->getMessage());
} catch (GatewayException $e) {
    \printf("%s\n\n", $e->getMessage());
    \printf('%s', $api->getLastResponseBody());
    exit();
}

if ($api->isSuccess() && $results) {
    foreach ($results['data'] as $result) {
        \printf(
            "Product %s : %s (%s)\n",
            $result['id'],
            $result['attributes']['description'],
            $result['attributes']['code']
        );

        $attributeValues = $result['relationships']['assigned-values']['data'] ?? [];

        foreach ($attributeValues as ['type' => $type, 'id' => $id]) {
            \printf(" %-15s: %s\n", $api->cachedResources[$type][$id]['attributes']['attribute-code'] ?? 'n/a', $api->cachedResources[$type][$id]['attributes']['value'] ?? 'n/a');
        }
    }
} else {
    \printf("Error (%d)\n", $api->getStatusCode());
    \print_r($results);
}
