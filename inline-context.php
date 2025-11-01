<?php

/**
 * Plugin Name: Inline Context
 * Plugin URI: https://github.com/trybes/inline-context
 * Description: Add inline expandable context to selected text in the block editor with direct anchor linking. Click to reveal, click again to hide.
 * Version: 1.0.0
 * Author: Trybes
 * Author URI: https://trybes.nl/
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Requires at least: 6.0
 * Tested up to: 6.6
 * Requires PHP: 7.4
 * Text Domain: inline-context
 * Domain Path: /languages
 */

defined('ABSPATH') || exit;

// Load translations.
add_action('init', function () {
    load_plugin_textdomain('inline-context', false, dirname(plugin_basename(__FILE__)) . '/languages');
});

// Ensure the custom data attributes are allowed by KSES for post content.
add_filter('wp_kses_allowed_html', function ($tags, $context) {
    if ($context === 'post') {
        if (!isset($tags['a'])) {
            $tags['a'] = [];
        }
        // Allow our custom attributes for inline context functionality
        $tags['a']['data-inline-context'] = true;
        $tags['a']['data-anchor-id'] = true;
        $tags['a']['role'] = true;
        $tags['a']['aria-expanded'] = true;
    }
    return $tags;
}, 10, 2);

add_action('enqueue_block_editor_assets', function () {
    // Use generated asset metadata for dependencies and versioning
    $asset_file = __DIR__ . '/build/index.asset.php';
    $asset = file_exists($asset_file) ? include_once $asset_file : [
        'dependencies' => ['wp-rich-text', 'wp-element', 'wp-components', 'wp-block-editor', 'wp-i18n'],
        'version' => filemtime(__DIR__ . '/build/index.js'),
    ];

    wp_enqueue_script(
        'trybes-inline-context',
        plugins_url('build/index.js', __FILE__),
        $asset['dependencies'],
        $asset['version'],
        true
    );

    // Enable JS translations for strings in the editor script
    if (function_exists('wp_set_script_translations')) {
        wp_set_script_translations('trybes-inline-context', 'inline-context', plugin_dir_path(__FILE__) . 'languages');
    }

    wp_enqueue_style(
        'trybes-inline-context',
        plugins_url('build/index.css', __FILE__),
        [],
        filemtime(__DIR__ . '/build/index.css')
    );
});

add_action('wp_enqueue_scripts', function () {
    // Enqueue bundled frontend JS with asset metadata
    $frontend_asset_file = __DIR__ . '/build/frontend.asset.php';
    $frontend_asset = file_exists($frontend_asset_file) ? include_once $frontend_asset_file : [
        'dependencies' => [],
        'version' => filemtime(__DIR__ . '/build/frontend.js'),
    ];
    wp_enqueue_script(
        'trybes-inline-context-frontend',
        plugins_url('build/frontend.js', __FILE__),
        $frontend_asset['dependencies'],
        $frontend_asset['version'],
        true
    );
    // Use compiled frontend styles from SCSS build
    wp_enqueue_style(
        'trybes-inline-context-frontend-style',
        plugins_url('build/style-index.css', __FILE__),
        [],
        filemtime(__DIR__ . '/build/style-index.css')
    );
});
