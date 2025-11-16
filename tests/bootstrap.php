<?php
/**
 * PHPUnit bootstrap file for Inline Context plugin
 *
 * @package InlineContext
 */

$inline_context_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( ! $inline_context_tests_dir ) {
	$inline_context_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

if ( ! file_exists( $inline_context_tests_dir . '/includes/functions.php' ) ) {
	echo "Could not find $inline_context_tests_dir/includes/functions.php, have you run bin/install-wp-tests.sh ?" . PHP_EOL; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	exit( 1 );
}

// Give access to tests_add_filter() function.
require_once $inline_context_tests_dir . '/includes/functions.php';

/**
 * Manually load the plugin being tested.
 */
function inline_context_manually_load_plugin() {
	require dirname( __DIR__ ) . '/inline-context.php';
}

tests_add_filter( 'muplugins_loaded', 'inline_context_manually_load_plugin' );

// Start up the WP testing environment.
require $inline_context_tests_dir . '/includes/bootstrap.php';
