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

use HF\ApiClient\Exception\GatewayException;
use HF\ApiClient\Query\Query;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;

require_once __DIR__ . '/../setup.php';

try {
    // we must get a storeId, which is different per environment.
    // as an example i'll show how you can doe a search by name
    $query = Query::create()
        ->withFilter('query', 'shop.PLHW')
        ->withPage(1, 1);

    $results = $api->commerce_listStores($query);
    $storeId = $results['data'][0]['id'] ?? '';

    $query = Query::create()
        ->withIncluded('catalogues')
        ->withPage(1, 1);

    $results = $api->commerce_getStore($query, $storeId);
} catch (IdentityProviderException $e) {
    die($e->getMessage());
} catch (GatewayException $e) {
    \printf("%s\n\n", $e->getMessage());
    \printf('%s', $api->getLastResponseBody());
    die();
}

if ($api->isSuccess() && $results) {
    $result = $results['data'];
    \printf("Store %s : %s (%s)\n", $result['id'], $result['attributes']['name'],
        $result['attributes']['description']);

    foreach ($result['relationships']['catalogues']['data'] as $rel) {
        foreach ($results['included'] as $include) {
            if ($include['id'] === $rel['id']) {
                \printf(
                    "Catalogue %s : %s (%s)\n",
                    $include['id'],
                    $include['attributes']['name'],
                    $include['attributes']['description']
                );
            }
        }
    }
} else {
    \printf("Error (%d)\n", $api->getStatusCode());
    //   print_r($results);
}
