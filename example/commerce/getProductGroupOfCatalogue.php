<?php

/**
 * Project 'Healthy Feet' by Podolab Hoeksche Waard.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @see       https://plhw.nl/
 *
 * @copyright Copyright (c) 2010 - 2018 bushbaby multimedia. (https://bushbaby.nl)
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
    $query = Query::create()
        ->withFilter('query', 'shop.PLHW')
        ->withPage(1, 1);

    $results = $api->commerce_listStores($query);
    $storeId = $results['data'][0]['id'] ?? '';

    // now we search for a specific catalogue within that store
    $query = Query::create()
        ->withFilter('query', 'Sandalinos Catalogue')
        ->withIncluded('product-groups')
        ->withPage(1, 1);

    $results = $api->commerce_listCataloguesOfStore($query, $storeId);

    $catalogueId = $results['data'][0]['id'] ?? '';
    // pick a random articleGroupId from the included (just for demo)
    $randomProductGroupId = \array_rand(\array_flip(\array_column($results['included'], 'id')));

    $results = $api->commerce_getProductGroupOfCatalogue(null, $storeId, $catalogueId, $randomProductGroupId);
} catch (IdentityProviderException $e) {
    die($e->getMessage());
} catch (GatewayException $e) {
    \printf("%s\n\n", $e->getMessage());
    \printf('%s', $api->getLastResponseBody());
    die();
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
