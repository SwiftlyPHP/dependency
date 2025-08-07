<?php declare(strict_types=1);

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setUsingCache(false)
    ->setFinder(
        PhpCsFixer\Finder::create()->in(__DIR__),
    )
    ->setRules([
        '@PSR12' => true,

        // File layout
        'declare_strict_types' => true,
        'blank_line_after_opening_tag' => false,
        'no_closing_tag' => true,

        // Imports
        'global_namespace_import' => [
            'import_classes' => true,
            'import_functions' => true,
            'import_constants' => true,
        ],
        'ordered_imports' => [
            'sort_algorithm' => 'alpha',
            'imports_order' => ['class', 'function', 'const'],
        ],
        'no_unused_imports' => false,

        // Array usage
        'array_push' => true,
        'array_syntax' => ['syntax' => 'short'],
        'list_syntax' => ['syntax' => 'short'],

        // String usage
        'explicit_string_variable' => true,
        'single_quote' => true,

        // Function usage
        'strict_param' => true,

        // PHPDoc
        'phpdoc_add_missing_param_annotation' => [
            'only_untyped' => true,
        ],
        'phpdoc_indent' => true,
        'phpdoc_summary' => true,
        'phpdoc_order' => ['order' => ['pure', 'template', 'param', 'throws', 'return']],
    ]);
