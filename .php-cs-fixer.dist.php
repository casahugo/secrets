<?php

return (new PhpCsFixer\Config())
    ->setRules([
        'array_syntax' => ['syntax' => 'short'],
        'binary_operator_spaces' => [
            'default' => null,
        ],
        'function_declaration' => ['closure_function_spacing' => 'none'],
        'increment_style' => false,
        'no_superfluous_phpdoc_tags' => true,
        'ordered_imports' => true,
        'single_quote' => false,
        'trailing_comma_in_multiline' => true,
        'php_unit_method_casing' => ['case' => 'camel_case']
    ])
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->exclude(['var/', 'vendor/'])
            ->in(__DIR__)
    )
;
