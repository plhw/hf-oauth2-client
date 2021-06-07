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

/** @var $api ApiClient */
require_once __DIR__ . '/../setup.php';

use HF\ApiClient\ApiClient;
use HF\ApiClient\Exception\ClientException;
use HF\ApiClient\Exception\GatewayException;
use HF\ApiClient\Query\Query;

try {
    $results = $api->commerce_listStores(Query::create()
        ->withIncluded('article-groups')
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
        \var_dump($results);

        // or do something with $api->cachesResources (which contains a (flattened) array of json-api resources by resource type type)
        \var_dump($api->cachedResources);

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
    }
}
