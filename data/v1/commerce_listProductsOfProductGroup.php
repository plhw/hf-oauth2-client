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

/** @var \HF\ApiClient\Query\Query $query */
$query = $params[0] ?? \HF\ApiClient\Query\Query::create();
$storeId = $params[1] ?? null;
$catalogueId = $params[2] ?? null;
$productGroupId = $params[3] ?? null;

if (! $storeId) {
    throw new \Exception('You must provide a storeId');
}
if (! $catalogueId) {
    throw new \Exception('You must provide a catalogueId');
}
if (! $productGroupId) {
    throw new \Exception('You must provide a productGroupId');
}

return [
    'url' => \sprintf('/commerce/stores/%s/catalogues/%s/product-groups/%s/products%s', $storeId, $catalogueId, $productGroupId, (string) $query),
    'method' => 'GET',
    'header' => $query->headers(),
    'response' => [
        'format' => 'json',
        'valid_codes' => ['200'],
    ],
];
