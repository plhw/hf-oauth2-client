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
    $results = $api->customer_queryOrderLeads(
        Query::create()
            //->withIncluded('customer.settings') // side load customer.settings
            ->withPage(1, 10)
            ->withFilter('customerId', '81a526d5-9430-49b2-859e-af77a907fcd6')
            ->withFilter('practiceId', '9c491672-5052-4b04-a38d-f7ea0680f76d')
            ->withSort('createdAt', true)
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
        \dump($results);

        // or do something with $api->cachesResources (which contains a (flattened) array of json-api resources by resource type type)
        \dump($api->cachedResources);
    }
}
