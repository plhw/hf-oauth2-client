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
    // we must get a storeId, which is different per environment.
    // as an example i'll show how you can doe a search by name

    $results = $api->commerce_listStores(
        Query::create()
            ->withFilter('query', 'shop.PLHW')
            ->withPage(1, 1)
    );

    $storeId = $results['data'][0]['id'] ?? '';

    $results = $api->commerce_getStore(Query::create()
        ->withIncluded('catalogues')
        ->withParam('storeId', $storeId)
        ->withPage(1, 1));
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
        \dump($results);

        // or do something with $api->cachesResources (which contains a (flattened) array of json-api resources by resource type type)
        \dump($api->cachedResources);

        $result = $results['data'];
        \printf(
            "Store %s : %s (%s)\n",
            $result['id'],
            $result['attributes']['name'],
            $result['attributes']['description']
        );

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
    }
}
