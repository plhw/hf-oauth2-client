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
    $results = $api->customer_listPosAroundCoordinate(
        Query::create()
            ->withFilter('around', '52.3629882,4.8593175')
            ->withFilter('distance', 50000)
            ->withFilter('product', 'Sandalen')
            ->withPage(1, 3)
            ->withSort('name', false)
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

        foreach ($results['data'] as $result) {
            \printf(
                "Practice %s on %skm\n",
                $result['attributes']['name'],
                \round(($result['attributes']['distance'] / 100)) / 10
            );
            \printf(" - sells %s\n", \implode(', ', $result['attributes']['products']));
        }
    }
}
