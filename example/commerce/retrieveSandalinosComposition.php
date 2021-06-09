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
/** @var $cache StorageInterface */
require_once __DIR__ . '/../setup.php';

use Assert\Assert;
use HF\ApiClient\ApiClient;
use HF\ApiClient\Exception\ClientException;
use HF\ApiClient\Exception\GatewayException;
use HF\ApiClient\Query\Query;
use Laminas\Cache\Storage\StorageInterface;

try {
    $code = $argv[1] ?? null;

    Assert::that($code)->notEmpty('Enter a code as argument');

    $api->commerce_listStores(
        Query::create()
            ->withFilter('query', 'shop.PLHW')
            ->withPage(1, 1)
    );

    $storeId = \reset($api->cachedResources['commerce/store'])['id'];

    $results = $api->commerce_retrieveSandalinosCompositionByCode(
        Query::create()
            ->withParam('storeId', $storeId)
            ->withParam('code', $code),
        $storeId
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
        dump($results);

        // or do something with $api->cachesResources (which contains a (flattened) array of json-api resources by resource type type)
        dump($api->cachedResources);
    }
}
