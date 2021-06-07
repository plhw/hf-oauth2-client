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

use Assert\Assert;
use HF\ApiClient\Query\Query;

/** @var Query $query */
$dossierId = $query->param('dossierId');

Assert::that($dossierId)->notBlank('provide a dossierId; "%s" is blank.')->uuid('dossierId "%s" is not a valid UUID.');

/* @var \HF\ApiClient\Query\Query $query */
return $query
    ->withResource(\sprintf('/dossier/dossiers/%s', $dossierId))
    ->withoutParam('dossierId');
