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
use Ramsey\Uuid\Uuid;

/** @var $api ApiClient */
require_once __DIR__ . '/../setup.php';

// required, must not exist
$orderLeadId = $argv[1] ?? (string) Uuid::uuid4();
$productGroupId = '7f392bb1-92cd-5407-9786-eb23929bbcfe'; // for sandals-custom-made (sandalinos)
$productGroupProperties = [
    'model' => '',
    'parts' => [
        'shaft' => '',
        'cover' => '',
        'footbed' => '',
        'midsole' => '',
        'outsole' => '',
    ],
];
$payload = [
    // uuid required, customer status must be 'opened'
    'customerId' => '81a526d5-9430-49b2-859e-af77a907fcd6', // Behandelaar.B1 (op testing)
    // uuid required, optional, if given the practice status must be 'opened' and must be part of customerId
    'practiceId' => '9c491672-5052-4b04-a38d-f7ea0680f76d', // Praktijk B1.A (op testing)

    // key must exist.
    'order' => [
        // uuid required, entity not exist, will be used to eventually create the order.
        'orderId' => (string) Uuid::uuid4(),

        // uuid required, should exists is but not validated on submit
        'productGroupId' => $productGroupId,

        // array required , should exists but are not validated on submit
        'properties' => $productGroupProperties,
    ],

    // key must exist.
    'dossier' => [
        // required, entity may or may not exist.
        //   when it exists the eventual order will be linked to that dossier
        //   when it does not exist a new dossier will be opened to create the eventual order for
        'dossierId' => (string) Uuid::uuid4(),

        // optional, string|null (is a reference the behandelaar might use)
        'externalReference' => 'my-ref',

        // key must exists if dossierId is unknown
        'name' => [
            // required, string
            'givenname' => 'Jan',
            // optional, string|null
            'middlename' => null,
            // required, string
            'familyname' => 'Jansen',
            // optional, string|null
            'familyname_preposition' => 'van',
        ],
        // optional, string YYYY-MM-DD
        'date-of-birth' => '1972-03-21',
        // optional, string male,female,intersex
        'sex' => 'male',
        // optional
        'email' => 'xxx@xxx.nl',
        // optional
        'phone' => 'xxxxxx',
        // optional
        'address' => [
            'street' => 'Kanaalstraat',
            'street_ordinality' => '149',
            'street_ordinality_suffix' => 'A',
            'postal_code' => '1054 XP',
            'populated_place' => 'Amsterdam',
            'country' => 'nl',
        ],
    ],
];

try {
    $result = $api->customer_submitOrderLead(
        Query::create()
            ->withParam('orderLeadId', $orderLeadId)
            ->withPayload($payload)
    );
} catch (ClientException $e) {
    \printf("%s\n\n", $e->getMessage());
    exit();
} catch (GatewayException $e) {
    \printf("%s\n\n", $e->getMessage());
    \printf('%s', $api->getLastResponseBody());
} catch (\Exception $e) {
    \printf("%s\n\n", $e->getMessage());
    \printf('%s', $api->getLastResponseBody());
} finally {
    if ($api->isSuccess()) {
        echo 'ok';

        // now do something with $orderLeadId.
    }
}
