<?php

$finder = Symfony\Component\Finder\Finder::create()
    ->files()
    ->name('*.php')
    ->in(array('src', 'tests'));

return PhpCsFixer\Config::create()
    ->setFinder($finder)
    ->setRules([
        '@Symfony' => true,
        'array_syntax' => ['syntax' => 'short'],
    ]);