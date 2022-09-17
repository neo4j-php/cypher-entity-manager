<?php

$finder = (new PhpCsFixer\Finder())
    ->in([
        __DIR__.'/src/',
        __DIR__.'/tests/'
    ])
    ->exclude('var')
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
        'method_argument_space' => [
            'on_multiline' => 'ensure_fully_multiline',
            'keep_multiple_spaces_after_comma' => true
        ],
        'declare_strict_types' => true,
        'single_quote' => false,
        'phpdoc_to_comment' => false
    ])
    ->setFinder($finder)
;
