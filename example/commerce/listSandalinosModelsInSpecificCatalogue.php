#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once __DIR__ . '/../setup.php';

use HF\ApiClient\Exception\GatewayException;
use HF\ApiClient\Query\Query;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;

try {
    /**
     * First task is to get a storeId and catalogueId and the 'root' productGroup for the Sandalinos
     * products. We do this via queries by name since these id's are different per deployment environment.
     *
     * Since we have a cache setup (via '../setup.php' we will use that
     */
    $cacheKey = sha1('id-key');

    // try to get $storeId, $catalogueId from the cache
    @list(
        $storeId,
        $catalogueId,
        $sndProductGroupId,
        $sndShaftsProductGroupId,
        $sndCoverProductGroupId,
        $sndFootbedProductGroupId,
        $sndMidsoleProductGroupId,
        $sndOutsoleProductGroupId) = $cache->getItem($cacheKey, $success);

    if (($storeId === null || $catalogueId === null || $sndProductGroupId === null) || ! $success) {
        $storeQueryResult = $api->commerce_listStores(
            Query::create()->withFilter('query', 'shop.PLHW')->withPage(1, 1)
        );

        $storeId = $storeQueryResult['data'][0]['id'] ?? '';

        $catalogueQueryResult = $api->commerce_listCataloguesOfStore(
            Query::create()->withFilter('query', 'Sandalinos Catalogue')->withPage(1, 1),
            $storeId
        );

        $catalogueId = $catalogueQueryResult['data'][0]['id'] ?? '';

        $sndProductGroupQueryResult = $api->commerce_listProductGroupsOfCatalogue(
            $query = Query::create()->withFilter('code', 'SND')->withPage(1, 1),
            $storeId,
            $catalogueId
        );
        $sndProductGroupId          = $sndProductGroupQueryResult['data'][0]['id'] ?? '';

        $sndShaftsProductGroupQueryResult = $api->commerce_listProductGroupsOfCatalogue(
            $query = Query::create()->withFilter('code', 'SND:S')->withPage(1, 1),
            $storeId,
            $catalogueId
        );
        $sndShaftsProductGroupId          = $sndShaftsProductGroupQueryResult['data'][0]['id'] ?? '';

        $sndCoverProductGroupQueryResult = $api->commerce_listProductGroupsOfCatalogue(
            $query = Query::create()->withFilter('code', 'SND:B')->withPage(1, 1),
            $storeId,
            $catalogueId
        );
        $sndCoverProductGroupId          = $sndCoverProductGroupQueryResult['data'][0]['id'] ?? '';

        $sndFootbedProductGroupQueryResult = $api->commerce_listProductGroupsOfCatalogue(
            $query = Query::create()->withFilter('code', 'SND:F')->withPage(1, 1),
            $storeId,
            $catalogueId
        );
        $sndFootbedProductGroupId          = $sndFootbedProductGroupQueryResult['data'][0]['id'] ?? '';

        $sndMidsoleProductGroupQueryResult = $api->commerce_listProductGroupsOfCatalogue(
            $query = Query::create()->withFilter('code', 'SND:TZ')->withPage(1, 1),
            $storeId,
            $catalogueId
        );
        $sndMidsoleProductGroupId          = $sndMidsoleProductGroupQueryResult['data'][0]['id'] ?? '';

        $sndOutsoleProductGroupId = $api->commerce_listProductGroupsOfCatalogue(
            $query = Query::create()->withFilter('code', 'SND:LZ')->withPage(1, 1),
            $storeId,
            $catalogueId
        );
        $sndOutsoleProductGroupId = $sndOutsoleProductGroupId['data'][0]['id'] ?? '';

        $cache->setItem($cacheKey, [
            $storeId,
            $catalogueId,
            $sndProductGroupId,
            $sndShaftsProductGroupId,
            $sndCoverProductGroupId,
            $sndFootbedProductGroupId,
            $sndMidsoleProductGroupId,
            $sndOutsoleProductGroupId,
        ]);
    }

    printf("We have collected these id's to work with '%s'\n\n", implode("', '", [
        $storeId,
        $catalogueId,
        $sndProductGroupId,
        $sndShaftsProductGroupId,
        $sndCoverProductGroupId,
        $sndFootbedProductGroupId,
        $sndMidsoleProductGroupId,
        $sndOutsoleProductGroupId,
    ]));

    /**
     * Step 2. Getting all models per gender.
     *
     * Since we now have the correct id's we'll be able to query 'products' linked to the main sandalinos group
     * Here we will search for 'products' that have a 'searchable' attribute assigned called 'gender'. While
     * we're at it we'll include the any assigned values (which will include its gender, but also the model code)
     */
    $results = $api->commerce_listProductsOfProductGroup(
        $query = Query::create()
            ->withFilter('assignedValues', [
                ['attributeCode' => 'gender', 'value' => 'female'],
                ['attributeCode' => 'model', 'available' => true],
            ])
            ->withIncluded('assignedValues'),
        $storeId,
        $catalogueId,
        $sndProductGroupId
    );

    if ($api->isSuccess()) {
        printf("\nLADIES MODELS\n\n");

        // loop over the loaded product(s)
        foreach ($results['data'] as $product) {
            if (null !== $product['attributes']['sales_price']) {
                // money comes in as cents; eg. '999 EUR' for €9.99
                $amount = explode(' ', $product['attributes']['sales_price'])[0];

                // so it must be divided by a hundred
                $amount /= 100;

                $salesPrice = sprintf('€%01.2f', $amount);
            } else { // no price available?
                $salesPrice = 'n/a';
            }

            printf("- %s (%s) %s\n", $product['attributes']['description'], $product['attributes']['code'], $salesPrice);

            // loop over the assigned_values for a product (one-to-many)
            // we'll extract the type and id from data inside the loop
            if (isset($product['relationships']['assigned_values'])) {
                foreach ($product['relationships']['assigned_values']['data'] as ['type' => $type, 'id' => $id]) {

                    // get assigned value resource
                    $assignedValue = $api->cachedResources[$type][$id];

                    if (isset($assignedValue['relationships']['attribute'])) {
                        // a one-2-one relationship exists between an assignedValue and an attribute resource, therefore type and id
                        // extraction is a little different (not an array)
                        ['type' => $type, 'id' => $id] = $assignedValue['relationships']['attribute']['data'];

                        // get assigned value attribute resource
                        $attribute = $api->cachedResources[$type][$id];

                        printf(
                            " - ATTR: %s (%s) %s (%s)\n",
                            $attribute['attributes']['label'],
                            $attribute['attributes']['code'],
                            $assignedValue['attributes']['label'],
                            $assignedValue['attributes']['value']
                        );
                    }
                }
            }
        }
    } else {
        printf("Error (%d)\n", $api->getStatusCode());
        print_r($results);
    }

    /**
     * Step 3.
     *
     * Since we now have the correct id's we'll be able to query 'products' linked to that ProductGroup
     */
    $results = $api->commerce_listProductsOfProductGroup(
        $query = Query::create()
            ->withFilter('assignedValues', [
                ['attributeCode' => 'model', 'value' => 'lotus-2016'],
            ])
            ->withIncluded('assignedValues.attribute.attributeValues')
            ->withSort('assignedValue.ordinality', false),
//            ->withSort('assignedAttributes.ordinality', true),
        $storeId,
        $catalogueId,
        $sndShaftsProductGroupId
    );

    if ($api->isSuccess()) {
        printf("\nPRODUCT VARIANTS FOR MODEL\n\n");

        // loop over the loaded product(s)
        foreach ($results['data'] as $product) {
            printf("- %s (%s)\n", $product['attributes']['description'], $product['attributes']['code']);

            // loop over the assigned_values for a product (one-to-many)
            // we'll extract the type and id from data inside the loop
            foreach ($product['relationships']['assigned_values']['data'] as ['type' => $type, 'id' => $id]) {

                // get assigned value resource
                $assignedValue = $api->cachedResources[$type][$id];

                // a one-2-one relationship exists between an assignedValue and an attribute resource, therefore type and id
                // extraction is a little different (not an array)
                ['type' => $type, 'id' => $id] = $assignedValue['relationships']['attribute']['data'];

                // get assigned value attribute resource
                $attribute = $api->cachedResources[$type][$id];

                printf(
                    " - ATTR: %s (%s) %s (%s)\n",
                    $attribute['attributes']['label'],
                    $attribute['attributes']['code'],
                    $assignedValue['attributes']['label'],
                    $assignedValue['attributes']['value']
                );
            }
        }
    } else {
        printf("Error (%d)\n", $api->getStatusCode());
        print_r($results);
    }
} catch (IdentityProviderException $e) {
    die($e->getMessage());
} catch (GatewayException $e) {
    printf("%s\n\n", $e->getMessage());
    printf('%s', $api->getLastResponseBody());
    die();
}
