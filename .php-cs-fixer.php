<?php

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = Finder::create()
	->in(__DIR__)
	->exclude([
		'vendor/',
	]);

return (new Config())
	->setRiskyAllowed(true)
	->setRules([
		'@PSR12' => true,
		'@PhpCsFixer' => true,
		'@Symfony' => true,

		// Syntax & formatting
		'array_syntax' => ['syntax' => 'short'],
		'binary_operator_spaces' => [
			'operators' => [
				'='  => 'align_single_space',
				'=>' => 'align_single_space',
			],
		],
		'single_quote' => true,
		'no_extra_blank_lines' => true,

		// Imports
		'no_unused_imports' => true,
		'ordered_imports' => true,

		// Whitespace & style
		'concat_space' => ['spacing' => 'one'],
		'multiline_whitespace_before_semicolons' => ['strategy' => 'no_multi_line'],
		'align_multiline_comment' => true,
		'array_indentation' => true,
		'trailing_comma_in_multiline' => [
			'elements' => ['arrays'], // PHP 7.4-safe
		],

		// PHPDoc
		'phpdoc_align' => true,
		'phpdoc_no_package' => false,
		'phpdoc_summary' => false,
		'phpdoc_types_order' => false,
		'phpdoc_to_comment' => ['ignored_tags' => ['var']],
		'no_blank_lines_after_phpdoc' => false,
		'no_superfluous_phpdoc_tags' => false,
		'phpdoc_var_without_name' => true,

		// PHPUnit & general
		'php_unit_test_class_requires_covers' => false,
		'increment_style' => false,
		'yoda_style' => false,
		'no_null_property_initialization' => false,
	])
	->setFinder($finder);
