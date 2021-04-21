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

$autoloadFiles = [
    __DIR__ . '/../../../../vendor/autoload.php',
    __DIR__ . '/../../../vendor/autoload.php',
    __DIR__ . '/../../vendor/autoload.php',
    __DIR__ . '/../vendor/autoload.php',
    __DIR__ . '/vendor/autoload.php',
];

\chdir(__DIR__ . '/..');

foreach ($autoloadFiles as $autoloadFile) {
    if (\file_exists($autoloadFile)) {
        \chdir(\dirname($autoloadFile) . '/../');

        require_once $autoloadFile;
    }
}

use HF\ApiClient\ApiClient;
use HF\ApiClient\Options\Options;
use Laminas\Cache\StorageFactory;

if (! \file_exists('.hf-api-client-secrets.php')) {
    exit('copy example/.hf-api-client-secrets.php.dist to APP_ROOT/.hf-api-client-secrets.php');
}

$options = Options::fromArray(include '.hf-api-client-secrets.php');

$cacheOptions = [
    'namespace' => \sha1($options->getClientId() . $options->getScope()),
    'dir_level' => 0,
];

if (\is_dir('./data/cache')) {
    $cacheOptions['cache_dir'] = './data/cache';
}

// optional but will then use filesystem default tmp directory
$cache = StorageFactory::factory([
    'adapter' => [
        'name' => 'filesystem',
        'options' => $cacheOptions,
    ],
    'plugins' => ['serializer'],
]);

$api = ApiClient::createClient($options, $cache);
