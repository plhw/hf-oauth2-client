<?php

/**
 * Project 'Healthy Feet' by Podolab Hoeksche Waard.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @see       https://plhw.nl/
 *
 * @copyright Copyright (c) 2010 - 2019 bushbaby multimedia. (https://bushbaby.nl)
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

$query = Query::create()
    ->withFilter('around', '52.3629882,4.8593175')
    ->withFilter('distance', 50000)
    ->withFilter('product', 'Sandalen')
    ->withPage(1, 10)
    ->withSort('distance', true);

try {
    $results = $api->customer_listPosAroundCoordinate($query);
} catch (IdentityProviderException $e) {
    die($e->getMessage());
} catch (GatewayException $e) {
    \printf("%s\n\n", $e->getMessage());
    \printf('%s', $api->getLastResponseBody());
    die();
}

if ($api->isSuccess()) {
    foreach ($results['data'] as $result) {
        \printf("Practice %s on %skm\n", $result['attributes']['name'], \round(($result['attributes']['distance'] / 100)) / 10);
        \printf(" - sells   : %s\n", \implode(', ', $result['attributes']['products']));
        \printf(" - address : %s, %s\n", \implode(', ', \explode("\n", $result['attributes']['address'])), $result['attributes']['country']);
        \printf(" - support : %s\n", \implode(', ', \array_filter([$result['attributes']['support-phone-number'], $result['attributes']['support-email-address'], $result['attributes']['support-url']])));
    }
} else {
    \printf("Error (%d)\n", $api->getStatusCode());
    \print_r($results);
}
