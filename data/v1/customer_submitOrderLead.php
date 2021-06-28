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

$payload = $query->payload();

Assert::that($orderLeadId)->uuid('orderLeadId "%s" is not a valid UUID.');
Assert::that($payload)->notNull('payload is null')->isArray('payload must be an array')->notEmpty('payload is empty');

Assert::that($payload)->keyExists('customerId');
Assert::that($payload['customerId'])->uuid('payload.customerId "%s" is not a valid UUID.');

Assert::that($payload)->keyExists('practiceId');
Assert::that($payload['practiceId'])->uuid('payload.practiceId "%s" is not a valid UUID.');

Assert::that($payload)->keyExists('order');
$order = $payload['order'];

Assert::that($order)->keyExists('orderId');
Assert::that($order['orderId'])->uuid('payload.order.orderId "%s" is not a valid UUID.');
Assert::that($order)->keyExists('productGroupId');
Assert::that($order['productGroupId'])->uuid('payload.order.productGroupId "%s" is not a valid UUID.');
Assert::that($order)->keyExists('properties');
Assert::that($order['properties'])->notNull()->isArray('payload.order.properties "%s" must be an array.');

$dossier = $payload['dossier'];
Assert::that($dossier)->keyExists('dossierId');
Assert::that($dossier['dossierId'])->uuid('payload.dossier.dossierId "%s" is not a valid UUID.');
Assert::that($dossier['externalReference'])
    ->nullOr()
    ->string('payload.dossier.externalReference must null or string')
    ->notEmpty('payload.dossier.externalReference must not be empty');

Assert::that($dossier)->notNull()->isArray('payload.dossier.properties "%s" must be an array.');

return $query
    ->withoutParam('orderLeadId')
    ->withMethod('POST')
    ->withResource(\sprintf('/customer/order-leads/%s', $orderLeadId));
