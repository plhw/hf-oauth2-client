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

$orderId = $argv[1] ?? null;

$query = Query::create()
    ->withParam('orderId', $orderId)
->withIncluded('properties');

try {
    $results = $api->dossier_getOrder($query);
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
        \dump($results['data']['id']);

        // or do something with $api->cachesResources (which contains a (flattened) array of json-api resources by resource type type)
        //\dump($api->cachedResources);

        \printf("order: %s \n", $results['data']['attributes']['order-number']);
        \printf(" - status: %s \n", $results['data']['attributes']['order-status']);
        \printf(" - documentStatus: %s \n", $results['data']['attributes']['status']);

        // get first result
        $propertyId = \reset($api->cachedResources['dossier/order-property/sandals-custom-made-model-composition'])['id'];
        \print_r($api->cachedResources['dossier/order-property/sandals-custom-made-model-composition'][$propertyId]['attributes']['value']);
    }
}
