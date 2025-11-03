<?php
/**
 * Plugin Name: Inline Context
 * Plugin URI: https://wordpress.org/plugins/inline-context/
 * Description: Add inline expandable context to selected text in the block editor with direct anchor linking. Click to reveal, click again to hide.
 * Version: 1.1.1
 * Author: Trybes
 * Author URI: https://profiles.wordpress.org/trybes/
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Requires at least: 6.0
 * Tested up to: 6.6
 * Requires PHP: 7.4
 * Text Domain: inline-context
 * Domain Path: /languages
 *
 * @package InlineContext
 */

defined( 'ABSPATH' ) || exit;

// Load translations.
add_action(
	'init',
	function () {
		load_plugin_textdomain( 'inline-context', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}
);

// Ensure the custom data attributes are allowed by KSES for post content.
add_filter(
	'wp_kses_allowed_html',
	function ( $tags, $context ) {
		if ( 'post' === $context ) {
			if ( ! isset( $tags['a'] ) ) {
				$tags['a'] = array();
			}
			// Allow our custom attributes for inline context functionality.
			$allowed_attributes = apply_filters(
				'inline_context_allowed_attributes',
				array(
					'data-inline-context' => true,
					'data-anchor-id'      => true,
					'role'                => true,
					'aria-expanded'       => true,
				)
			);
			$tags['a']          = array_merge( $tags['a'], $allowed_attributes );
		}
		return $tags;
	},
	10,
	2
);

add_action(
	'enqueue_block_editor_assets',
	function () {
		// Use generated asset metadata for dependencies and versioning.
		$asset_file = __DIR__ . '/build/index.asset.php';
		// phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingVariable -- Including generated asset file.
		$asset = file_exists( $asset_file ) ? include_once $asset_file : array(
			'dependencies' => array( 'wp-rich-text', 'wp-element', 'wp-components', 'wp-block-editor', 'wp-i18n' ),
			'version'      => filemtime( __DIR__ . '/build/index.js' ),
		);

		wp_enqueue_script(
			'trybes-inline-context',
			plugins_url( 'build/index.js', __FILE__ ),
			$asset['dependencies'],
			$asset['version'],
			true
		);

		// Enable JS translations for strings in the editor script.
		if ( function_exists( 'wp_set_script_translations' ) ) {
			wp_set_script_translations( 'trybes-inline-context', 'inline-context', plugin_dir_path( __FILE__ ) . 'languages' );
		}

		wp_enqueue_style(
			'trybes-inline-context',
			plugins_url( 'build/index.css', __FILE__ ),
			array(),
			filemtime( __DIR__ . '/build/index.css' )
		);
	}
);

add_action(
	'wp_enqueue_scripts',
	function () {
		// Enqueue bundled frontend JS with asset metadata.
		$frontend_asset_file = __DIR__ . '/build/frontend.asset.php';
		// phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingVariable -- Including generated asset file.
		$frontend_asset = file_exists( $frontend_asset_file ) ? include_once $frontend_asset_file : array(
			'dependencies' => array(),
			'version'      => filemtime( __DIR__ . '/build/frontend.js' ),
		);

		// Add wp-hooks as a dependency for filter support.
		$dependencies = array_merge( $frontend_asset['dependencies'], array( 'wp-hooks' ) );

		wp_enqueue_script(
			'trybes-inline-context-frontend',
			plugins_url( 'build/frontend.js', __FILE__ ),
			$dependencies,
			$frontend_asset['version'],
			true
		);
		// Use compiled frontend styles from SCSS build.
		wp_enqueue_style(
			'trybes-inline-context-frontend-style',
			plugins_url( 'build/style-index.css', __FILE__ ),
			array(),
			filemtime( __DIR__ . '/build/style-index.css' )
		);
	}
);
