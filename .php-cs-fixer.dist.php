<?php

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = PhpCsFixer\Finder::create()
    ->ignoreDotFiles(false)
    ->ignoreVCS(true)
    ->exclude([
        'vendor',
    ])
    ->in(__DIR__);

$rules = [
    '@Symfony' => true,
    'new_with_braces' => false,
    'concat_space' => ['spacing' => 'one'],
    'array_syntax' => ['syntax' => 'short'],
    'yoda_style' => false,
    'array_indentation' => true,
    'no_superfluous_phpdoc_tags' => false,
    'single_line_throw' => false,

    'linebreak_after_opening_tag' => true,
    'multiline_whitespace_before_semicolons' => true,
    '@PSR2' => true,
    'no_useless_else' => true,
    'no_useless_return' => true,
    'ordered_class_elements' => true,
    'ordered_imports' => true,
    'phpdoc_add_missing_param_annotation' => ['only_untyped' => true],
    'phpdoc_order' => true,
    'phpdoc_to_comment' => false,
    'semicolon_after_instruction' => true,
    'align_multiline_comment' => true,
    'list_syntax' => ['syntax' => 'short'],
    'phpdoc_types_order' => ['null_adjustment' => 'always_last'],
    'single_line_comment_style' => true,
    'increment_style' => false,
    'phpdoc_var_without_name' => false,
    'general_phpdoc_annotation_remove' => ['annotations' => ['author', 'package']],
];

return (new Config())
    ->setRules($rules)
    ->setLineEnding("\n")
    ->setFinder($finder);
