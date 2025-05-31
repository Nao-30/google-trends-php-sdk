<?php
// .php-cs-fixer.dist.php

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = Finder::create()
    ->in([__DIR__ . '/src', __DIR__ . '/tests'])
    ->exclude(['vendor', 'node_modules'])
    ->notName('*.js')
    ->notName('*.css')
;

return (new Config('Gtrends PHP SDK Coding Standard'))
    // use PSR12 as a base
    ->setRules([
        '@PSR12'                 => true,
        // add or override any other rules here…
    ])
    // require PHP 7.4 compatibility
    ->setRiskyAllowed(true)
//    ->setPhpVersion(70400)
    ->setFinder($finder)
    ->setUsingCache(true)
    ->setCacheFile(__DIR__.'/.php-cs-fixer.cache')
    ;

