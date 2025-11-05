<?php
/**
 * Plugin Name: Inline Context
 * Plugin URI: https://wordpress.org/plugins/inline-context/
 * Description: Add inline expandable context to selected text in the block editor with direct anchor linking. Click to reveal, click again to hide.
 * Version: 1.1.5
 * Author: Joop Laan
 * Author URI: https://profiles.wordpress.org/joop/
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

define( 'INLINE_CONTEXT_VERSION', '1.1.5' );

// Load admin settings page.
if ( is_admin() ) {
	require_once __DIR__ . '/admin-settings.php';
}

// Load translations.
add_action(
	'init',
	function () {
		load_plugin_textdomain( 'inline-context', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}
);

// Register theme.json for Site Editor styling support.
add_action(
	'after_setup_theme',
	function () {
		// Add theme.json support for customizing inline context styles in Site Editor.
		add_filter(
			'wp_theme_json_data_default',
			function ( $theme_json ) {
				$plugin_theme_json_path = __DIR__ . '/theme.json';
				if ( file_exists( $plugin_theme_json_path ) ) {
					// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Reading local theme.json file.
					$plugin_theme_json_data = json_decode( file_get_contents( $plugin_theme_json_path ), true );
					if ( is_array( $plugin_theme_json_data ) ) {
						$theme_json->update_with( $plugin_theme_json_data );
					}
				}
				return $theme_json;
			}
		);
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

		// Use plugin version for production, filemtime for development.
		$version = defined( 'WP_DEBUG' ) && WP_DEBUG && isset( $asset['version'] )
			? filemtime( __DIR__ . '/build/index.js' )
			: INLINE_CONTEXT_VERSION;

		wp_enqueue_script(
			'jooplaan-inline-context',
			plugins_url( 'build/index.js', __FILE__ ),
			$asset['dependencies'],
			$version,
			true
		);

		// Enable JS translations for strings in the editor script.
		if ( function_exists( 'wp_set_script_translations' ) ) {
			wp_set_script_translations( 'jooplaan-inline-context', 'inline-context', plugin_dir_path( __FILE__ ) . 'languages' );
		}

		wp_enqueue_style(
			'jooplaan-inline-context',
			plugins_url( 'build/index.css', __FILE__ ),
			array(),
			defined( 'WP_DEBUG' ) && WP_DEBUG
				? filemtime( __DIR__ . '/build/index.css' )
				: INLINE_CONTEXT_VERSION
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

		// Use plugin version for production, filemtime for development.
		$version = defined( 'WP_DEBUG' ) && WP_DEBUG
			? filemtime( __DIR__ . '/build/frontend.js' )
			: INLINE_CONTEXT_VERSION;

		wp_enqueue_script(
			'jooplaan-inline-context-frontend',
			plugins_url( 'build/frontend.js', __FILE__ ),
			$dependencies,
			$version,
			true
		);
		// Use compiled frontend styles from SCSS build.
		wp_enqueue_style(
			'jooplaan-inline-context-frontend-style',
			plugins_url( 'build/style-index.css', __FILE__ ),
			array(),
			defined( 'WP_DEBUG' ) && WP_DEBUG
				? filemtime( __DIR__ . '/build/style-index.css' )
				: INLINE_CONTEXT_VERSION
		);
	}
);
