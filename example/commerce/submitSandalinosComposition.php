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

use HF\ApiClient\ApiClient;
use HF\ApiClient\Exception\ClientException;
use HF\ApiClient\Exception\GatewayException;
use HF\ApiClient\Query\Query;
use Laminas\Cache\Storage\StorageInterface;

try {
    $api->commerce_listStores(
        Query::create()
            ->withFilter('query', 'shop.PLHW')
            ->withPage(1, 1)
    );

    $storeId = \reset($api->cachedResources['commerce/store'])['id'];

    $result = $api->commerce_submitSandalinosComposition(
        Query::create()
            ->withParam('storeId', $storeId)
            ->withParam('name', 'Some One')
            ->withParam('email', 'someone@example.com')
            ->withParam('locality', 'Kalverstraat 1, Amsterdam, NL')
            ->withParam('composition', [
                'model' => 'lotus-2016',
                'parts' => [
                    'S:CM:SH:lotus-2016.G.001', // Shaft Lotus-2016 Smooth leather Black
                    'S:CM:CV:NONE.NONE',        // Cover None
                    'S:CM:FB:E.000',           // Footbed Eva White
                    'S:CM:MS:NONE.NONE',       // Midsole None
                    'S:CM:OS:AS.001',          // Outsole Astro Zwart
                ],
            ])
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
        \var_dump($result);

        // or do something with $api->cachesResources (which contains a (flattened) array of json-api resources by resource type type)
        \var_dump($api->cachedResources);
    }
}
