<?php

$config = new PhpCsFixer\Config();
return $config->setRules([
    '@PSR12' => true,
    // WordPress-like formatting rules
    'array_syntax' => ['syntax' => 'long'], // Use array() instead of []
    'concat_space' => ['spacing' => 'one'], // Space around concatenation
    'single_quote' => true, // Use single quotes
    'trailing_comma_in_multiline' => ['elements' => ['arrays']], // Trailing commas in arrays
    'yoda_style' => ['equal' => true, 'identical' => true], // Yoda conditions like WordPress
    'cast_spaces' => ['space' => 'single'], // Space after type casts
    'no_short_echo_tag' => true, // No <?= tags
    'braces' => [
        'allow_single_line_closure' => true,
        'position_after_functions_and_oop_constructs' => 'next',
        'position_after_control_structures' => 'same',
    ],
    'method_argument_space' => [
        'on_multiline' => 'ensure_fully_multiline',
        'keep_multiple_spaces_after_comma' => false,
    ],
    'function_declaration' => [
        'closure_function_spacing' => 'one',
    ],
    'blank_line_after_opening_tag' => false, // Don't force blank line after <?php
    'linebreak_after_opening_tag' => true,
])
->setFinder(
    PhpCsFixer\Finder::create()
        ->exclude('vendor')
        ->exclude('node_modules')
        ->in(__DIR__)
        ->name('*.php')
);
