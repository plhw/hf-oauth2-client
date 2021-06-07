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
    $results = $api->dossier_queryOrders(
        Query::create()
            //->withIncluded('dossier') // include releationships order.dossier (resource "dossier/dossier")
            //->withFilter('query', 'nnnnn') // filter by on dossier.dossierNumber
            //->withFilter('query', 'nnn.') // filtered by customer.accountNumber
            //->withFilter('query', 'nnn.nnn') // filtered by order.orderNumber
            ->withFilter('status', 'opened') // sort order.status (OPENED, ARCHIVED, DELETED)
            ->withFilter('orderStatus', 'cad') // sort order.orderStatus (inception, placed, rejected, accepted, cad, cam, production, shipment)
            //->withSort('accountNumber', false) // sort order.accountNumber descending
            ->withPage(1, 10)
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
        \var_dump($results);

        // or do something with $api->cachesResources (which contains a (flattened) array of json-api resources by resource type type)
        \var_dump($api->cachedResources);
    }
}
