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

require_once __DIR__ . '/../setup.php';

use HF\ApiClient\Exception\GatewayException;
use HF\ApiClient\Query\Query;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;

$query = Query::create()
    ->withIncluded('article-groups')
    ->withPage(1, 1);

try {
    $results = $api->commerce_listStores($query);
} catch (IdentityProviderException $e) {
    die($e->getMessage());
} catch (GatewayException $e) {
    \printf("%s\n\n", $e->getMessage());
    \printf('%s', $api->getLastResponseBody());
    die();
}

if ($api->isSuccess() && $results) {
    foreach ($results['data'] as $result) {
        \printf("Store %s : %s (%s)\n", $result['id'], $result['attributes']['name'],
            $result['attributes']['description']);

        foreach ($result['relationships']['article-groups']['data'] as $rel) {
            foreach ($results['included'] as $include) {
                if ($include['id'] === $rel['id']) {
                    \printf(
                        "ArticleGroup %s : %s (%s:%s)\n",
                        $include['id'],
                        $include['attributes']['description'],
                        $include['attributes']['ledger-number'],
                        $include['attributes']['code']
                    );
                }
            }
        }
    }
} else {
    \printf("Error (%d)\n", $api->getStatusCode());
    \print_r($results);
}
