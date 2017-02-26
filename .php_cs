<?php

$config = new \HF\CS\Config();
$config->getFinder()->in(__DIR__)->exclude(['data', 'docs', 'etc', 'templates']);

$cacheDir = getenv('TRAVIS') ? getenv('HOME') . '/.php-cs-fixer' : __DIR__;

$config->setCacheFile($cacheDir . '/.php_cs.cache');

return $config;
