<?php

declare(strict_types=1);

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude('var')
    ->exclude('src/Infrastructure/Symfony/config')
;

return (new PhpCsFixer\Config())
    ->setParallelConfig(PhpCsFixer\Runner\Parallel\ParallelConfigFactory::detect())
    ->setRules([
        '@Symfony' => true,
        '@Symfony:risky' => true,
        '@PHP80Migration' => true,
        '@PHP80Migration:risky' => true,
        'strict_comparison' => true,
        'array_syntax' => ['syntax' => 'short'],
        'native_function_invocation' => ['exclude' => []],
        'no_unreachable_default_argument_value' => true,
        "single_space_around_construct" => true,
        "control_structure_braces" => true,
        "control_structure_continuation_position" => true,
        "declare_parentheses" => true,
        "no_multiple_statements_per_line" => true,
        "braces_position" => true,
        "statement_indentation" => true,
        "no_extra_blank_lines" => true,
        'heredoc_to_nowdoc' => false,
        'phpdoc_annotation_without_dot' => false,
        'phpdoc_line_span' => false,
        'no_superfluous_phpdoc_tags' => false,
        'single_line_throw' => false,
        'void_return' => true,
        'simplified_null_return' => true,
    ])
    ->setRiskyAllowed(true)
    ->setLineEnding("\n")
    ->setFinder($finder)
;
