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
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;

/** @var $api ApiClient */
require_once __DIR__ . '/../setup.php';

$customerId = $argv[1] ?? exit('uuid required');

try {
    $results = $api->customer_getCustomer($customerId);
} catch (IdentityProviderException $e) {
    exit($e->getMessage());
} catch (GatewayException $e) {
    \printf("%s\n\n", $e->getMessage());
    \printf('%s', $api->getLastResponseBody());
    exit();
}

if ($api->isSuccess()) {
    \print_r($results);
} else {
    \printf("Error (%d)\n", $api->getStatusCode());
    \print_r($results);
}
