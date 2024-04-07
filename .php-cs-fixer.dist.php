<?php declare(strict_types=1);

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude([
       'tests'
    ]);

return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR12' => true,
        // ** File **
        'declare_parentheses' => true,
        'blank_line_after_opening_tag' => false,
        'declare_strict_types' => true,
        // ** Syntax **
        'list_syntax' => [
            'syntax' => 'short',
        ],
        'single_quote' => true,
        // ** Imports **
        'global_namespace_import' => [
            'import_classes' => true,
            'import_functions' => true,
            'import_constants' => true,
        ],
        'no_unused_imports' => false,
        // ** PHPDoc **
        'phpdoc_add_missing_param_annotation' => [
            'only_untyped' => false,
        ],
        'phpdoc_indent' => true
    ])
    ->setRiskyAllowed(true)
    ->setUsingCache(false)
    ->setFinder($finder);