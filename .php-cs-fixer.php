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

$config = new \HF\CS\Config([
    'native_function_invocation' => [
    'include' => ['@all'],
    'scope' => 'all',
    'strict' => true, // or remove this line, as false is default value
    'exclude' => ['time'],
],
]);

$config
  ->getFinder()
  ->in(__DIR__)
  ->exclude(['data/cache'])
  ->append(['.php_cs']);

$cacheDir = \getenv('TRAVIS') ? \getenv('HOME') . '/.php-cs-fixer' : __DIR__;

$config->setCacheFile($cacheDir . '/.php_cs.cache');

return $config;
