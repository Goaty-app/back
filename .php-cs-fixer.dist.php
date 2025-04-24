<?php

$finder = (new PhpCsFixer\Finder())
    ->in([
        __DIR__ . '/src',
        // __DIR__ . '/tests',
        // __DIR__ . '/config',
        // __DIR__ . '/public',
    ])
    ->exclude([
        'var',
        'vendor',
        'node_modules',
        'bin/.phpunit',
    ])
    ->notPath([
        'Kernel.php',
        'bootstrap.php',
    ]);

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
        '@Symfony:risky' => true,
        '@PHP80Migration:risky' => true,
        '@PHP83Migration' => true,

        'declare_strict_types' => false,

        'method_argument_space' => [
            'on_multiline' => 'ensure_fully_multiline',
            'keep_multiple_spaces_after_comma' => false,
            'after_heredoc' => true,
        ],

        'method_chaining_indentation' => true,
        'function_declaration' => [
            'closure_function_spacing' => 'one',
        ],
        'single_line_throw' => false,

        'line_ending' => true,

        'binary_operator_spaces' => [
            'default' => 'single_space',
            'operators' => [
                '=>' => 'align_single_space_minimal',
            ],
        ],

        'array_indentation' => true,
        'trailing_comma_in_multiline' => [
            'elements' => ['arrays', 'arguments', 'parameters'],
        ],
        'multiline_whitespace_before_semicolons' => [
            'strategy' => 'new_line_for_chained_calls',
        ],

        'function_typehint_space' => true,
        'no_spaces_after_function_name' => true,

        'array_syntax' => ['syntax' => 'short'],
        'list_syntax' => ['syntax' => 'short'],
        'modernize_types_casting' => true,
        'use_arrow_functions' => true,
        'ternary_to_null_coalescing' => true,

        'no_unused_imports' => true,
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'no_superfluous_phpdoc_tags' => [
            'allow_mixed' => true,
            'remove_inheritdoc' => true,
        ],

        'phpdoc_align' => [
            'tags' => ['param', 'return', 'throws', 'type', 'var'],
        ],
        'phpdoc_separation' => true,
        'doctrine_annotation_array_assignment' => true,
        'doctrine_annotation_spaces' => true,
        'global_namespace_import' => [
            'import_classes' => true,
            'import_constants' => false,
            'import_functions' => false,
        ],
    ])
    ->setRiskyAllowed(true)
    ->setFinder($finder)
    ->setLineEnding("\n")
;
