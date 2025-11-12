<?php
/**
 * Plugin Name: Inline Context
 * Plugin URI: https://wordpress.org/plugins/inline-context/
 * Description: Add inline expandable context to selected text in the block editor with direct anchor linking. Click to reveal, click again to hide.
 * Version: 1.5.0
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

define( 'INLINE_CONTEXT_VERSION', '1.5.0' );

// Load modular classes.
require_once __DIR__ . '/includes/class-utils.php';
require_once __DIR__ . '/includes/class-cpt.php';
require_once __DIR__ . '/includes/class-sync.php';
require_once __DIR__ . '/includes/class-deletion.php';
require_once __DIR__ . '/includes/class-rest-api.php';
require_once __DIR__ . '/includes/class-frontend.php';

// Load admin-specific functionality (function-based, loaded conditionally).
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

// Initialize utilities (CSS output).
$inline_context_utils = new Inline_Context_Utils();
$inline_context_utils->init();

// Initialize CPT functionality.
$inline_context_cpt = new Inline_Context_CPT();
$inline_context_cpt->init();

// Initialize sync functionality.
$inline_context_sync = new Inline_Context_Sync();
$inline_context_sync->init();

// Initialize deletion functionality.
$inline_context_deletion = new Inline_Context_Deletion( $inline_context_sync );
$inline_context_deletion->init();

// Initialize REST API.
$inline_context_rest_api = new Inline_Context_REST_API();
$inline_context_rest_api->init();

// Initialize frontend rendering and assets.
$inline_context_frontend = new Inline_Context_Frontend();
$inline_context_frontend->init();

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

/**
 * Migrate meta-based categories to taxonomy terms (runs once)
 */
add_action(
	'admin_init',
	function () {
		// Check if migration has already been done.
		if ( get_option( 'inline_context_categories_migrated' ) ) {
			return;
		}

		// Get the old meta-based categories.
		$old_categories = get_option( 'inline_context_categories', inline_context_get_default_categories() );

		if ( ! is_array( $old_categories ) || empty( $old_categories ) ) {
			$old_categories = inline_context_get_default_categories();
		}

		// Create taxonomy terms from old categories.
		foreach ( $old_categories as $category_id => $category_data ) {
			$term_name = $category_data['name'] ?? $category_id;

			// Check if term already exists.
			$existing_term = get_term_by( 'slug', $category_id, 'inline_context_category' );

			if ( ! $existing_term ) {
				// Create the term.
				$term = wp_insert_term(
					$term_name,
					'inline_context_category',
					array(
						'slug' => $category_id,
					)
				);

				if ( ! is_wp_error( $term ) ) {
					$term_id = $term['term_id'];

					// Store the icon and color as term meta.
					if ( isset( $category_data['icon_closed'] ) ) {
						update_term_meta( $term_id, 'icon_closed', $category_data['icon_closed'] );
					}
					if ( isset( $category_data['icon_open'] ) ) {
						update_term_meta( $term_id, 'icon_open', $category_data['icon_open'] );
					}
					if ( isset( $category_data['color'] ) ) {
						update_term_meta( $term_id, 'color', $category_data['color'] );
					}
				}
			} else {
				// Update existing term meta.
				$term_id = $existing_term->term_id;
				if ( isset( $category_data['icon_closed'] ) ) {
					update_term_meta( $term_id, 'icon_closed', $category_data['icon_closed'] );
				}
				if ( isset( $category_data['icon_open'] ) ) {
					update_term_meta( $term_id, 'icon_open', $category_data['icon_open'] );
				}
				if ( isset( $category_data['color'] ) ) {
					update_term_meta( $term_id, 'color', $category_data['color'] );
				}
			}
		}

		// Mark migration as complete.
		update_option( 'inline_context_categories_migrated', true );
	}
);

/**
 * Initialize metadata for notes
 */
add_action(
	'init',
	function () {
		// Register metadata for tracking note usage.
		register_post_meta(
			'inline_context_note',
			'used_in_posts',
			array(
				'type'              => 'array',
				'description'       => __( 'Post IDs where this note is used', 'inline-context' ),
				'single'            => true,
				'default'           => array(),
				'sanitize_callback' => function( $value ) {
					if ( ! is_array( $value ) ) {
						return array();
					}
					// Sanitize array of objects: [['post_id' => int, 'count' => int], ...]
					$sanitized = array();
					foreach ( $value as $usage_data ) {
						if ( is_array( $usage_data ) && isset( $usage_data['post_id'] ) ) {
							$sanitized[] = array(
								'post_id' => absint( $usage_data['post_id'] ),
								'count'   => absint( $usage_data['count'] ?? 1 ),
							);
						}
					}
					return $sanitized;
				},
				'show_in_rest'      => array(
					'schema' => array(
						'type'  => 'array',
						'items' => array(
							'type'       => 'object',
							'properties' => array(
								'post_id' => array( 'type' => 'integer' ),
								'count'   => array( 'type' => 'integer' ),
							),
						),
					),
				),
			)
		);

		register_post_meta(
			'inline_context_note',
			'usage_count',
			array(
				'type'              => 'integer',
				'description'       => __( 'Number of times this note is used', 'inline-context' ),
				'single'            => true,
				'default'           => 0,
				'sanitize_callback' => 'absint',
				'show_in_rest'      => true,
			)
		);

		register_post_meta(
			'inline_context_note',
			'is_reusable',
			array(
				'type'              => 'boolean',
				'description'       => __( 'Whether this note is marked as reusable', 'inline-context' ),
				'single'            => true,
				'default'           => false,
				'sanitize_callback' => 'rest_sanitize_boolean',
				'show_in_rest'      => true,
			)
		);
	}
);

/**
 * Add is_reusable field to REST API response for inline_context_note
 */
add_filter(
	'rest_prepare_inline_context_note',
	function ( $response, $post, $request ) {
		$data = $response->get_data();
		$data['is_reusable'] = (bool) get_post_meta( $post->ID, 'is_reusable', true );
		$response->set_data( $data );
		return $response;
	},
	10,
	3
);

/**
 * One-time cleanup function to rebuild usage data for all notes.
 * This fixes corrupted data from the old data structure.
 * 
 * To use: Add ?inline_context_rebuild=1 to any admin URL while logged in as admin.
 * Example: wp-admin/edit.php?post_type=inline_context_note&inline_context_rebuild=1
 */
add_action(
	'admin_init',
	function () {
		if ( ! isset( $_GET['inline_context_rebuild'] ) || ! current_user_can( 'manage_options' ) ) {
			return;
		}


	// Reset all note usage data - use direct DB delete to avoid cache issues.
	$all_notes = get_posts(
		array(
			'post_type'      => 'inline_context_note',
			'posts_per_page' => -1,
			'fields'         => 'ids',
		)
	);


	global $wpdb;
	foreach ( $all_notes as $note_id ) {
		// Delete from database directly to avoid cache issues.
		$wpdb->delete(
			$wpdb->postmeta,
			array(
				'post_id'  => $note_id,
				'meta_key' => 'used_in_posts',
			)
		);
		$wpdb->delete(
			$wpdb->postmeta,
			array(
				'post_id'  => $note_id,
				'meta_key' => 'usage_count',
			)
		);
		// Clear the cache for this post.
		wp_cache_delete( $note_id, 'post_meta' );
	}

	// Scan all posts and pages to rebuild usage data.
	$all_posts = get_posts(
		array(
			'post_type'      => array( 'post', 'page' ),
			'post_status'    => 'any',
			'posts_per_page' => -1,
		)
	);

	// Build usage data structure: note_id => [['post_id' => X, 'count' => Y], ...]
	$all_usage_data = array();

	foreach ( $all_posts as $post ) {
		// Get note IDs from content and count occurrences.
		preg_match_all( '/data-note-id="(\d+)"/i', $post->post_content, $matches );
		$notes_raw = ! empty( $matches[1] ) ? array_map( 'intval', $matches[1] ) : array();
		
		// Count occurrences of each note.
		$notes_counts = array();
		foreach ( $notes_raw as $note_id ) {
			if ( ! isset( $notes_counts[ $note_id ] ) ) {
				$notes_counts[ $note_id ] = 0;
			}
			$notes_counts[ $note_id ]++;
		}

		// Accumulate usage data for each note.
		foreach ( $notes_counts as $note_id => $count ) {
			if ( ! isset( $all_usage_data[ $note_id ] ) ) {
				$all_usage_data[ $note_id ] = array();
			}

			$all_usage_data[ $note_id ][] = array(
				'post_id' => $post->ID,
				'count'   => $count,
			);
		}
	}

	// Now save all the accumulated usage data.
	foreach ( $all_usage_data as $note_id => $used_in ) {
		update_post_meta( $note_id, 'used_in_posts', $used_in );
		
		// Recalculate total usage count.
		$total_count = 0;
		foreach ( $used_in as $usage_data ) {
			$total_count += $usage_data['count'] ?? 1;
		}
		update_post_meta( $note_id, 'usage_count', $total_count );
	}

	// Redirect to notes list with success message.
		wp_safe_redirect(
			add_query_arg(
				array(
					'post_type' => 'inline_context_note',
					'rebuilt'   => '1',
				),
				admin_url( 'edit.php' )
			)
		);
		exit;
	}
);

/**
 * Show success message after rebuild.
 */
add_action(
	'admin_notices',
	function () {
		// Display rebuild success notice.
		if ( isset( $_GET['rebuilt'] ) && $_GET['rebuilt'] === '1' && isset( $_GET['post_type'] ) && $_GET['post_type'] === 'inline_context_note' ) {
			echo '<div class="notice notice-success is-dismissible">';
			echo '<p><strong>' . esc_html__( 'Success!', 'inline-context' ) . '</strong> ';
			echo esc_html__( 'Usage data has been rebuilt for all inline context notes.', 'inline-context' );
			echo '</p>';
			echo '</div>';
		}

		// Display transient admin notices (for post save validations).
		$screen = get_current_screen();
		if ( $screen && $screen->post_type === 'inline_context_note' && $screen->base === 'post' && isset( $_GET['post'] ) ) {
			$post_id = intval( $_GET['post'] );
			$notices = get_transient( 'inline_context_admin_notice_' . $post_id );
			if ( $notices ) {
				foreach ( $notices as $notice ) {
					$type = $notice['type'] === 'error' ? 'error' : 'warning';
					echo '<div class="notice notice-' . esc_attr( $type ) . ' is-dismissible">';
					echo '<p>' . esc_html( $notice['message'] ) . '</p>';
					echo '</div>';
				}
				delete_transient( 'inline_context_admin_notice_' . $post_id );
			}
		}
	}
);
