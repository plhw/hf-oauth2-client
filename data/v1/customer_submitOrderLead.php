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

/* @var \HF\ApiClient\Query\Query $query */

use Assert\Assert;

$orderLeadId = $query->param('orderLeadId');

Assert::that($orderLeadId)->uuid('orderLeadId "%s" is not a valid UUID.');
Assert::that($query->payload())->notNull('payload is null')->notEmpty('payload is empty');

return $query
    ->withoutParam('orderLeadId')
    ->withMethod('POST')
    ->withResource(\sprintf('/customer/order-leads/%s', $orderLeadId));
