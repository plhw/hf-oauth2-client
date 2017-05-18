#!/usr/bin/env php
<?php

declare(strict_types=1);

use HF\ApiClient\Exception\GatewayException;
use HF\ApiClient\Query\Query;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;

require_once __DIR__ . '/../setup.php';

try {
    $query = Query::create()
        ->withIncluded('catalogues')
        ->withPage(1, 1);

    $results = $api->commerce_getStore($query, 'a0713068-8c35-51da-b578-a3cce5978221');
} catch (IdentityProviderException $e) {
    die($e->getMessage());
} catch (GatewayException $e) {
    printf("%s\n\n", $e->getMessage());
    printf('%s', $api->getLastResponseBody());
    die();
}

if ($api->isSuccess() && $results) {
    $result = $results['data'];
    printf("Store %s : %s (%s)\n", $result['id'], $result['attributes']['name'],
        $result['attributes']['description']);

    foreach ($result['relationships']['catalogues']['data'] as $rel) {
        foreach ($results['included'] as $include) {
            if ($include['id'] === $rel['id']) {
                printf(
                    "Catalogue %s : %s (%s)\n",
                    $include['id'],
                    $include['attributes']['name'],
                    $include['attributes']['description']
                );
            }
        }
    }
} else {
    printf("Error (%d)\n", $api->getStatusCode());
    print_r($results);
}
