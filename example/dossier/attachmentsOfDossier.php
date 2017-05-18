#!/usr/bin/env php
<?php

declare(strict_types=1);

use HF\ApiClient\Exception\GatewayException;
use HF\ApiClient\Query\Query;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;

require_once __DIR__ . '/../setup.php';

$query = Query::create()
    ->withPage(1, 3)
    ->withSort('name', false);

try {
    $results = $api->dossier_getAttachmentsOfDossier($query, 'a652e443-ba3f-5841-936c-b6be5c5a2800');
} catch (IdentityProviderException $e) {
    die($e->getMessage());
} catch (GatewayException $e) {
    printf("%s\n\n", $e->getMessage());
    printf('%s', $api->getLastResponseBody());
    die();
}

if ($api->isSuccess()) {
    foreach ($results['data'] as $result) {
        print_r($result);
    }
} else {
    printf("Error (%d)\n", $api->getStatusCode());
    print_r($results);
}
