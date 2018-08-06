#!/usr/bin/env php
<?php

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
    printf("%s\n\n", $e->getMessage());
    printf('%s', $api->getLastResponseBody());
    die();
}

if ($api->isSuccess() && $results) {
    foreach ($results['data'] as $result) {
        printf("Store %s : %s (%s)\n", $result['id'], $result['attributes']['name'],
            $result['attributes']['description']);

        foreach ($result['relationships']['article-groups']['data'] as $rel) {
            foreach ($results['included'] as $include) {
                if ($include['id'] === $rel['id']) {
                    printf(
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
    printf("Error (%d)\n", $api->getStatusCode());
    print_r($results);
}
