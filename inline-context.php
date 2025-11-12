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

// Load common functions available on both frontend and admin.
require_once __DIR__ . '/inline-context-common.php';

// Load admin-specific functionality.
if ( is_admin() ) {
	require_once __DIR__ . '/admin-settings.php';
}

// Load modular classes.
require_once __DIR__ . '/includes/class-cpt.php';

// Load translations.
add_action(
	'init',
	function () {
		load_plugin_textdomain( 'inline-context', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}
);

// Initialize CPT functionality.
$inline_context_cpt = new Inline_Context_CPT();
$inline_context_cpt->init();

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
 * Prevent trashing a reusable note that's in use.
 */
add_action(
	'wp_trash_post',
	function ( $post_id ) {
		$post = get_post( $post_id );
		
		if ( ! $post || $post->post_type !== 'inline_context_note' ) {
			return;
		}

		$is_reusable = get_post_meta( $post_id, 'is_reusable', true );
		$used_in     = get_post_meta( $post_id, 'used_in_posts', true );

		// Only prevent trashing for REUSABLE notes that are in use.
		if ( $is_reusable && ! empty( $used_in ) && is_array( $used_in ) ) {
			wp_die(
				sprintf(
					/* translators: %d: number of posts using the note */
					_n(
						'This reusable note cannot be trashed because it is currently used in %d post. Please remove it from all posts first.',
						'This reusable note cannot be trashed because it is currently used in %d posts. Please remove it from all posts first.',
						count( $used_in ),
						'inline-context'
					),
					count( $used_in )
				),
				esc_html__( 'Cannot Trash Note', 'inline-context' ),
				array(
					'back_link' => true,
					'response'  => 403,
				)
			);
		}
	},
	10,
	1
);

/**
 * Clean up non-reusable notes from posts after they've been trashed.
 */
add_action(
	'trashed_post',
	function ( $post_id ) {
		$post = get_post( $post_id );
		
		if ( ! $post || $post->post_type !== 'inline_context_note' ) {
			return;
		}

		$is_reusable = get_post_meta( $post_id, 'is_reusable', true );
		$used_in     = get_post_meta( $post_id, 'used_in_posts', true );

		// For non-reusable notes that are in use, remove them from all posts.
		if ( ! $is_reusable && ! empty( $used_in ) && is_array( $used_in ) ) {
			inline_context_remove_note_from_posts( $post_id, $used_in );
		}
	},
	10,
	1
);

/**
 * Prevent deleting a reusable note that's in use.
 * For non-reusable notes, remove them from posts when permanently deleted.
 */
add_action(
	'before_delete_post',
	function ( $post_id, $post ) {
		if ( ! $post || $post->post_type !== 'inline_context_note' ) {
			return;
		}

		$is_reusable = get_post_meta( $post_id, 'is_reusable', true );
		$used_in     = get_post_meta( $post_id, 'used_in_posts', true );

		// Only prevent deletion for REUSABLE notes that are in use.
		if ( $is_reusable && ! empty( $used_in ) && is_array( $used_in ) ) {
			wp_die(
				sprintf(
					/* translators: %d: number of posts using the note */
					_n(
						'This reusable note cannot be deleted because it is currently used in %d post. Please remove it from all posts first.',
						'This reusable note cannot be deleted because it is currently used in %d posts. Please remove it from all posts first.',
						count( $used_in ),
						'inline-context'
					),
					count( $used_in )
				),
				esc_html__( 'Cannot Delete Note', 'inline-context' ),
				array(
					'back_link' => true,
					'response'  => 403,
				)
			);
		}

		// For non-reusable notes that are in use, remove them from all posts.
		if ( ! $is_reusable && ! empty( $used_in ) && is_array( $used_in ) ) {
			inline_context_remove_note_from_posts( $post_id, $used_in );
		}
	},
	10,
	2
);

/**
 * Helper function to remove a note from all posts where it's used.
 *
 * @param int   $note_id The note ID to remove.
 * @param array $used_in Array of usage data with post_id and count.
 */
function inline_context_remove_note_from_posts( $note_id, $used_in ) {
	foreach ( $used_in as $usage_data ) {
		$using_post_id = $usage_data['post_id'] ?? 0;
		
		if ( ! $using_post_id ) {
			continue;
		}

		$using_post = get_post( $using_post_id );
		if ( ! $using_post ) {
			continue;
		}

		// Remove the <a> tag but keep the link text.
		// Pattern matches: <a ...data-note-id="123"...>link text</a>
		// Replacement: link text (just the captured content)
		$pattern = '/<a\s+[^>]*?data-note-id=["\']' . preg_quote( (string) $note_id, '/' ) . '["\'][^>]*?>(.*?)<\/a>/is';
		
		$updated_content = preg_replace( $pattern, '$1', $using_post->post_content );

		if ( $updated_content !== $using_post->post_content ) {
			// Use remove_filter to prevent infinite loops with post_updated hooks.
			remove_action( 'post_updated', 'inline_context_sync_note_usage_on_post_save', 10 );
			remove_action( 'post_updated', 'inline_context_sync_reusable_note_content', 10 );
			
			wp_update_post(
				array(
					'ID'           => $using_post_id,
					'post_content' => $updated_content,
				)
			);
			
			// Re-add the hooks.
			add_action( 'post_updated', 'inline_context_sync_note_usage_on_post_save', 10, 3 );
			add_action( 'post_updated', 'inline_context_sync_reusable_note_content', 10, 3 );
		}
	}
}

/**
 * Update the data-category-id attribute for all usages of a note.
 *
 * @param int $note_id Note post ID.
 * @param int $category_id Category term ID (0 when uncategorized).
 * @return void
 */
function inline_context_sync_note_category_attribute( $note_id, $category_id ) {
	$used_in_posts = get_post_meta( $note_id, 'used_in_posts', true );

	if ( empty( $used_in_posts ) || ! is_array( $used_in_posts ) ) {
		return;
	}

	$category_id     = absint( $category_id );
	$attribute_value = $category_id ? esc_attr( (string) $category_id ) : '';

	foreach ( $used_in_posts as $usage_data ) {
		$using_post_id = $usage_data['post_id'] ?? 0;
		
		if ( ! $using_post_id ) {
			continue;
		}

		$using_post = get_post( $using_post_id );
		if ( ! $using_post ) {
			continue;
		}

		// Only touch published posts to mirror content sync behaviour.
		if ( 'publish' !== $using_post->post_status ) {
			continue;
		}

		$post_content = $using_post->post_content;
		if ( empty( $post_content ) ) {
			continue;
		}

		// Pattern matches <a> tags with both class="wp-inline-context" and data-note-id="X" in any order.
		$pattern = '/<a\b[^>]*\bclass="[^"]*\bwp-inline-context\b[^"]*"[^>]*\bdata-note-id="' . preg_quote( (string) $note_id, '/' ) . '"[^>]*>|<a\b[^>]*\bdata-note-id="' . preg_quote( (string) $note_id, '/' ) . '"[^>]*\bclass="[^"]*\bwp-inline-context\b[^"]*"[^>]*>/i';

		$updated_html = preg_replace_callback(
			$pattern,
			function ( $matches ) use ( $category_id, $attribute_value, $note_id, $using_post_id ) {
				$original_tag = $matches[0];
				$updated_tag  = $original_tag;

				if ( $category_id ) {
					if ( preg_match( '/\sdata-category-id="[^"]*"/', $updated_tag ) ) {
						$updated_tag = preg_replace(
							'/\sdata-category-id="[^"]*"/',
							' data-category-id="' . $attribute_value . '"',
							$updated_tag
						);
					} else {
						$updated_tag = rtrim( $updated_tag );
						if ( '>' === substr( $updated_tag, -1 ) ) {
							$updated_tag = substr_replace(
								$updated_tag,
								' data-category-id="' . $attribute_value . '"',
								-1,
								0
							);
						} else {
							$updated_tag .= ' data-category-id="' . $attribute_value . '"';
						}
					}
				} else {
					$updated_tag = preg_replace( '/\sdata-category-id="[^"]*"/', '', $updated_tag );
				}

				return $updated_tag;
			},
			$post_content,
			-1,
			$count
		);

		if ( $count > 0 && $updated_html !== $post_content ) {
			// Use remove_filter to prevent infinite loops with post_updated hooks.
			remove_action( 'post_updated', 'inline_context_sync_note_usage_on_post_save', 10 );
			remove_action( 'post_updated', 'inline_context_sync_reusable_note_content', 10 );
			
			wp_update_post(
				array(
					'ID'           => $using_post_id,
					'post_content' => $updated_html,
				)
			);
			
			// Re-add the hooks.
			add_action( 'post_updated', 'inline_context_sync_note_usage_on_post_save', 10, 3 );
			add_action( 'post_updated', 'inline_context_sync_reusable_note_content', 10, 3 );
		}
	}
}

/**
 * Sync category attribute when taxonomy terms are set via WordPress UI.
 * This handles changes made through the taxonomy metabox.
 */
add_action(
	'set_object_terms',
	function ( $object_id, $terms, $tt_ids, $taxonomy, $append, $old_tt_ids ) {
		// Only process our custom taxonomy on our CPT.
		if ( 'inline_context_category' !== $taxonomy ) {
			return;
		}

		$post = get_post( $object_id );
		if ( ! $post || 'inline_context_note' !== $post->post_type ) {
			return;
		}

		// Get the current category (first term if multiple).
		$category_id = ! empty( $tt_ids ) ? (int) $tt_ids[0] : 0;

		// Sync the category attribute to all posts using this note.
		inline_context_sync_note_category_attribute( $object_id, $category_id );
	},
	10,
	6
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
 * Update all instances of a reusable note when it's updated
 */
add_action(
	'post_updated',
	function ( $post_id, $post_after, $post_before ) {
		// Sync usage when a regular post/page is updated.
		inline_context_sync_note_usage_on_post_save( $post_id, $post_after, $post_before );

		// Sync reusable note content when the note itself is updated.
		inline_context_sync_reusable_note_content( $post_id, $post_after, $post_before );
	},
	10,
	3
);

/**
 * Syncs the usage count of notes when a post is saved.
 *
 * @param int      $post_id      The ID of the post being saved.
 * @param \WP_Post $post_after   The post object after the update.
 * @param \WP_Post $post_before  The post object before the update.
 */
function inline_context_sync_note_usage_on_post_save( $post_id, $post_after, $post_before ) {
	// Static variable to prevent infinite loops when wp_update_post triggers this hook again.
	static $processing = array();

	if ( isset( $processing[ $post_id ] ) ) {
		return;
	}

	// Only run on specific post types, exclude our own CPT.
	$supported_post_types = apply_filters( 'inline_context_supported_post_types', array( 'post', 'page' ) );
	if ( ! in_array( $post_after->post_type, $supported_post_types, true ) ) {
		return;
	}

	// Don't run on autosave or revisions.
	if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
		return;
	}

	// Mark as processing to prevent infinite loops.
	$processing[ $post_id ] = true;

	// Get note IDs from the current (after) content and count occurrences.
	preg_match_all( '/data-note-id="(\d+)"/i', $post_after->post_content, $matches_after );
	$notes_after_raw = ! empty( $matches_after[1] ) ? array_map( 'intval', $matches_after[1] ) : array();
	
	// Count occurrences of each note in the current content.
	$notes_after_counts = array();
	foreach ( $notes_after_raw as $note_id ) {
		if ( ! isset( $notes_after_counts[ $note_id ] ) ) {
			$notes_after_counts[ $note_id ] = 0;
		}
		$notes_after_counts[ $note_id ]++;
	}

	// Get note IDs from the previous (before) content.
	preg_match_all( '/data-note-id="(\d+)"/i', $post_before->post_content, $matches_before );
	$notes_before = ! empty( $matches_before[1] ) ? array_unique( array_map( 'intval', $matches_before[1] ) ) : array();

	// Get unique note IDs from after content.
	$notes_after = array_keys( $notes_after_counts );

	// Determine which notes were added and removed.
	$notes_added   = array_diff( $notes_after, $notes_before );
	$notes_removed = array_diff( $notes_before, $notes_after );
	$notes_updated = array_intersect( $notes_after, $notes_before );

	// Additional validation: Check all notes in the database that claim to be used in this post.
	// This catches cases where notes were removed outside of normal editing (manual HTML edits, etc.).
	$all_notes = get_posts(
		array(
			'post_type'      => 'inline_context_note',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'meta_query'     => array(
				array(
					'key'     => 'used_in_posts',
					'value'   => sprintf( '"post_id";i:%d', $post_id ),
					'compare' => 'LIKE',
				),
			),
		)
	);

	foreach ( $all_notes as $note_id ) {
		// If this note is NOT in the current content but claims to be used here, mark it for removal.
		if ( ! in_array( $note_id, $notes_after, true ) && ! in_array( $note_id, $notes_removed, true ) ) {
			$notes_removed[] = $note_id;
		}
	}

	// Process removed notes.
	foreach ( $notes_removed as $note_id ) {
		if ( ! $note_id ) {
			continue;
		}
		
		$used_in = get_post_meta( $note_id, 'used_in_posts', true );
		if ( ! is_array( $used_in ) ) {
			$used_in = array();
		}

		// Clean up corrupted data (integers instead of arrays with post_id/count).
		$needs_cleanup = false;
		foreach ( $used_in as $usage_data ) {
			if ( ! is_array( $usage_data ) ) {
				$needs_cleanup = true;
				break;
			}
		}
		
		if ( $needs_cleanup ) {
			$used_in = array();
		}

		// Find and remove this post from the usage list.
		$updated_usage = array();
		foreach ( $used_in as $usage_data ) {
			$stored_post_id = $usage_data['post_id'] ?? 0;
			if ( $stored_post_id !== $post_id ) {
				$updated_usage[] = $usage_data;
			}
		}

		update_post_meta( $note_id, 'used_in_posts', $updated_usage );
		
		// Recalculate total usage count.
		$total_count = 0;
		foreach ( $updated_usage as $usage_data ) {
			$total_count += $usage_data['count'] ?? 1;
		}
		update_post_meta( $note_id, 'usage_count', $total_count );

		// If a non-reusable note has no more usages, delete it.
		$is_reusable = (bool) get_post_meta( $note_id, 'is_reusable', true );
		if ( ! $is_reusable && empty( $updated_usage ) ) {
			wp_delete_post( $note_id, true ); // Force delete.
		}
	}

	// Process added and updated notes.
	$notes_to_update = array_merge( $notes_added, $notes_updated );
	foreach ( $notes_to_update as $note_id ) {
		if ( ! $note_id ) {
			continue;
		}
		
		$count_in_post = $notes_after_counts[ $note_id ] ?? 0;
		if ( $count_in_post <= 0 ) {
			continue;
		}

		$used_in = get_post_meta( $note_id, 'used_in_posts', true );
		if ( ! is_array( $used_in ) ) {
			$used_in = array();
		}

		// Clean up corrupted data (integers instead of arrays with post_id/count).
		$needs_cleanup = false;
		foreach ( $used_in as $usage_data ) {
			if ( ! is_array( $usage_data ) ) {
				$needs_cleanup = true;
				break;
			}
		}
		
		if ( $needs_cleanup ) {
			$used_in = array();
		}

		// Find if this post is already in the usage list and update the count.
		$found = false;
		foreach ( $used_in as $key => $usage_data ) {
			$stored_post_id = $usage_data['post_id'] ?? 0;
			if ( $stored_post_id === $post_id ) {
				$used_in[ $key ]['count'] = $count_in_post;
				$found = true;
				break;
			}
		}

		// If not found, add it.
		if ( ! $found ) {
			$used_in[] = array(
				'post_id' => $post_id,
				'count'   => $count_in_post,
			);
		}

		update_post_meta( $note_id, 'used_in_posts', $used_in );
		
		// Recalculate total usage count from all posts.
		$total_count = 0;
		foreach ( $used_in as $usage_data ) {
			$total_count += $usage_data['count'] ?? 1;
		}
		update_post_meta( $note_id, 'usage_count', $total_count );
	}

	// Unmark as processing.
	unset( $processing[ $post_id ] );
}

/**
 * Update all instances of a reusable note when it's updated
 *
 * @param int      $post_id      The ID of the post being saved.
 * @param \WP_Post $post_after   The post object after the update.
 * @param \WP_Post $post_before  The post object before the update.
 */
function inline_context_sync_reusable_note_content( $post_id, $post_after, $post_before ) {
	// Static variable to track if we're already processing this post (avoid infinite loops).
	static $processing = array();

	if ( isset( $processing[ $post_id ] ) ) {
		return;
	}

	// Only process inline_context_note CPT.
	if ( 'inline_context_note' !== get_post_type( $post_id ) ) {
		return;
	}

	// Skip if this is an autosave or revision.
	if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
		return;
	}

	// Only process if the note is marked as reusable.
	$is_reusable = get_post_meta( $post_id, 'is_reusable', true );
	if ( ! $is_reusable ) {
		return;
	}

	// Check if content changed.
	$content_changed = $post_before->post_content !== $post_after->post_content;

	// Don't check category here - it's not saved yet during post_updated.
	// Category sync will be handled by save_post hook which runs later.

	// Debug logging.
	error_log( 'Inline Context Sync - Post ID: ' . $post_id );
	error_log( 'Content changed: ' . ( $content_changed ? 'yes' : 'no' ) );

	// Only update if content changed.
	if ( ! $content_changed ) {
		error_log( 'No content changes detected, exiting' );
		return;
	}

	// Get the list of posts using this note.
	$used_in_posts = get_post_meta( $post_id, 'used_in_posts', true );
	if ( empty( $used_in_posts ) || ! is_array( $used_in_posts ) ) {
		return;
	}

	// Mark as processing.
	$processing[ $post_id ] = true;

	// Get the updated content.
	$updated_content = $post_after->post_content;

	// Update each post that uses this note.
	foreach ( $used_in_posts as $usage_data ) {
		$using_post_id = $usage_data['post_id'] ?? 0;
		if ( ! $using_post_id ) {
			continue;
		}
		
		// Get the post.
		$using_post = get_post( $using_post_id );
		if ( ! $using_post || 'publish' !== $using_post->post_status ) {
			continue;
		}

		// Update all instances of this note in the post content.
		$post_content = $using_post->post_content;

		if ( empty( $post_content ) ) {
			continue;
		}

		// Find all anchor tags with this note ID and update content only.
		$pattern = '/<a\s+([^>]*?)data-note-id="' . preg_quote( $post_id, '/' ) . '"([^>]*?)>/i';

		$updated_html = preg_replace_callback(
			$pattern,
			function ( $matches ) use ( $updated_content ) {
				$tag = $matches[0];

				// Update data-inline-context attribute.
				$tag = preg_replace(
					'/data-inline-context="[^"]*"/',
					'data-inline-context="' . esc_attr( $updated_content ) . '"',
					$tag
				);

				return $tag;
			},
			$post_content,
			-1,
			$count
		);

		// Save the updated post if changes were made.
		if ( $count > 0 && $updated_html !== $post_content ) {
			wp_update_post(
				array(
					'ID'           => $using_post_id,
					'post_content' => $updated_html,
				)
			);
		}
	}

	// Unmark as processing.
	unset( $processing[ $post_id ] );
}

/**
 * Register REST API endpoint for searching notes
 */
add_action(
	'rest_api_init',
	function () {
		// Search endpoint.
		register_rest_route(
			'inline-context/v1',
			'/notes/search',
			array(
				'methods'             => 'GET',
				'callback'            => function ( $request ) {
					$search       = $request->get_param( 's' );
					$reusable_only = $request->get_param( 'reusable_only' );

					$args = array(
						'post_type'      => 'inline_context_note',
						'post_status'    => 'publish',
						'posts_per_page' => 20,
						'orderby'        => 'title',
						'order'          => 'ASC',
					);

					if ( ! empty( $search ) ) {
						$args['s'] = $search;
					}

					// Filter by reusable notes only.
					if ( $reusable_only ) {
						$args['meta_query'] = array(
							array(
								'key'     => 'is_reusable',
								'value'   => '1',
								'compare' => '=',
							),
						);
					}

					$query = new WP_Query( $args );
					$notes = array();

					foreach ( $query->posts as $post ) {
						// Get the category terms for this note.
						$terms = wp_get_post_terms( $post->ID, 'inline_context_category' );
						$category_ids = array();
						if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
							$category_ids = wp_list_pluck( $terms, 'term_id' );
						}

						// Get reusable flag.
						$is_reusable = (bool) get_post_meta( $post->ID, 'is_reusable', true );

						$notes[] = array(
							'id'                      => $post->ID,
							'title'                   => $post->post_title,
							'content'                 => $post->post_content,
							'excerpt'                 => wp_trim_words( wp_strip_all_tags( $post->post_content ), 20 ),
							'inline_context_category' => $category_ids,
							'is_reusable'             => $is_reusable,
						);
					}

					// Sort: reusable notes first, then by title.
					usort(
						$notes,
						function ( $a, $b ) {
							if ( $a['is_reusable'] === $b['is_reusable'] ) {
								return strcmp( $a['title'], $b['title'] );
							}
							return $b['is_reusable'] ? 1 : -1;
						}
					);

					return new WP_REST_Response( $notes, 200 );
				},
				'permission_callback' => function () {
					return current_user_can( 'edit_posts' );
				},
			)
		);

		// Track usage endpoint.
		register_rest_route(
			'inline-context/v1',
			'/notes/(?P<id>\d+)/track-usage',
			array(
				'methods'             => 'POST',
				'callback'            => function ( $request ) {
					$note_id = (int) $request->get_param( 'id' );
					$post_id = (int) $request->get_param( 'post_id' );

					if ( ! $note_id || ! $post_id ) {
						return new WP_Error( 'missing_params', 'Note ID and Post ID are required', array( 'status' => 400 ) );
					}

					// Verify the note exists and is the correct post type.
					$note = get_post( $note_id );
					if ( ! $note || 'inline_context_note' !== $note->post_type ) {
						return new WP_Error( 'invalid_note', 'Invalid note ID', array( 'status' => 404 ) );
					}

					// Verify the post exists.
					$post = get_post( $post_id );
					if ( ! $post ) {
						return new WP_Error( 'invalid_post', 'Invalid post ID', array( 'status' => 404 ) );
					}

					// Verify user can edit the post where the note is being used.
					if ( ! current_user_can( 'edit_post', $post_id ) ) {
						return new WP_Error( 'forbidden', 'You do not have permission to edit this post', array( 'status' => 403 ) );
					}

					// NOTE: This endpoint is now a no-op.
					// Actual usage tracking is handled by the post_updated hook which
					// accurately counts all note occurrences when the post is saved.
					// This prevents duplicate/incorrect tracking from multiple JavaScript calls.

					// Get current usage for response (read-only).
					$used_in_posts = get_post_meta( $note_id, 'used_in_posts', true );
					if ( ! is_array( $used_in_posts ) ) {
						$used_in_posts = array();
					}

					// Calculate total usage count from the data structure.
					$total_count = 0;
					foreach ( $used_in_posts as $usage_data ) {
						$total_count += $usage_data['count'] ?? 1;
					}

					return new WP_REST_Response(
						array(
							'success'       => true,
							'used_in_posts' => $used_in_posts,
							'usage_count'   => $total_count,
						),
						200
					);
				},
				'permission_callback' => function () {
					// User must be logged in and able to edit posts.
					return current_user_can( 'edit_posts' );
				},
				'args'                => array(
					'id'      => array(
						'description'       => __( 'Note ID', 'inline-context' ),
						'type'              => 'integer',
						'required'          => true,
						'sanitize_callback' => 'absint',
						'validate_callback' => function ( $param ) {
							return is_numeric( $param );
						},
					),
					'post_id' => array(
						'description'       => __( 'Post ID where the note is used', 'inline-context' ),
						'type'              => 'integer',
						'required'          => true,
						'sanitize_callback' => 'absint',
						'validate_callback' => function ( $param ) {
							return is_numeric( $param );
						},
					),
				),
			)
		);

		// Handle note removals endpoint.
		register_rest_route(
			'inline-context/v1',
			'/notes/handle-removals',
			array(
				'methods'             => 'POST',
				'callback'            => function ( $request ) {
					$post_id  = $request->get_param( 'post_id' );
					$note_ids = $request->get_param( 'note_ids' );

					if ( empty( $post_id ) || ! is_numeric( $post_id ) ) {
						return new WP_Error( 'invalid_post_id', 'A valid post ID is required.', array( 'status' => 400 ) );
					}

					if ( empty( $note_ids ) || ! is_array( $note_ids ) ) {
						return new WP_Error( 'invalid_note_ids', 'An array of note IDs is required.', array( 'status' => 400 ) );
					}

					// Verify user can edit the post from which notes are being removed.
					if ( ! current_user_can( 'edit_post', $post_id ) ) {
						return new WP_Error( 'forbidden', 'You do not have permission to edit this post.', array( 'status' => 403 ) );
					}

					$results = array();

					foreach ( $note_ids as $note_id ) {
						$note_id = absint( $note_id );
						if ( ! $note_id || 'inline_context_note' !== get_post_type( $note_id ) ) {
							$results[ $note_id ] = 'invalid_note';
							continue;
						}

						// Get current usage.
						$used_in_posts = get_post_meta( $note_id, 'used_in_posts', true );
						if ( ! is_array( $used_in_posts ) ) {
							$used_in_posts = array();
						}

						// Find and remove this post from the usage list.
						$updated_usage = array();
						$found         = false;
						foreach ( $used_in_posts as $usage_data ) {
							$stored_post_id = $usage_data['post_id'] ?? 0;
							if ( $stored_post_id !== $post_id ) {
								$updated_usage[] = $usage_data;
							} else {
								$found = true;
							}
						}

						if ( $found ) {
							// Update meta.
							update_post_meta( $note_id, 'used_in_posts', $updated_usage );
							
							// Recalculate total usage count.
							$total_count = 0;
							foreach ( $updated_usage as $usage_data ) {
								$total_count += $usage_data['count'] ?? 1;
							}
							update_post_meta( $note_id, 'usage_count', $total_count );

							// Check if non-reusable note should be deleted.
							$is_reusable = (bool) get_post_meta( $note_id, 'is_reusable', true );
							if ( ! $is_reusable && empty( $updated_usage ) ) {
								// Force delete, skipping trash.
								$deleted = wp_delete_post( $note_id, true );
								if ( $deleted ) {
									$results[ $note_id ] = 'deleted';
								} else {
									$results[ $note_id ] = 'delete_failed';
								}
							} else {
								$results[ $note_id ] = 'usage_updated';
							}
						} else {
							$results[ $note_id ] = 'not_found_in_usage';
						}
					}

					return new WP_REST_Response( array( 'success' => true, 'results' => $results ), 200 );
				},
				'permission_callback' => function () {
					return current_user_can( 'edit_posts' );
				},
				'args'                => array(
					'post_id'  => array(
						'description'       => 'The ID of the post from which the notes were removed.',
						'type'              => 'integer',
						'required'          => true,
						'sanitize_callback' => 'absint',
					),
					'note_ids' => array(
						'description' => 'An array of note IDs to process.',
						'type'        => 'array',
						'required'    => true,
						'items'       => array(
							'type' => 'integer',
						),
					),
				),
			)
		);
	}
);

/**
 * Add noscript content to posts
 *
 * @param string $content The post content.
 * @return string The modified post content.
 */
function inline_context_add_noscript_content( $content ) {
	// Skip in admin.
	if ( is_admin() ) {
		return $content;
	}

	// Only run if the content contains our specific links.
	if ( false === strpos( $content, 'wp-inline-context' ) ) {
		return $content;
	}

	// Use DOMDocument to safely manipulate HTML.
	$doc = new DOMDocument();
	// Suppress warnings from invalid HTML.
	@$doc->loadHTML( '<?xml encoding="utf-8" ?>' . $content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );

	$links            = $doc->getElementsByTagName( 'a' );
	$notes_to_append  = array();
	$nodes_to_process = array();
	$note_counter     = 1;

	foreach ( $links as $link ) {
		if ( $link->hasAttribute( 'data-inline-context' ) && $link->hasAttribute( 'data-anchor-id' ) ) {
			$nodes_to_process[] = $link;
		}
	}

	if ( empty( $nodes_to_process ) ) {
		return $content;
	}

	// Process nodes in reverse to avoid issues with live DOM modification.
	foreach ( array_reverse( $nodes_to_process ) as $link ) {
		$note_content_html = $link->getAttribute( 'data-inline-context' );
		$anchor_id         = $link->getAttribute( 'data-anchor-id' ); // This is the unified ID, e.g., "context-note-...".
		$trigger_id        = 'trigger-' . $anchor_id;

		// 1. Modify the trigger link for non-JS view to point to the unified anchor.
		$link->setAttribute( 'href', '#' . $anchor_id );
		$link->setAttribute( 'id', $trigger_id );
		$link->setAttribute( 'role', 'link' ); // It's a standard link now.
		$link->removeAttribute( 'aria-expanded' );

		// 2. Store note content for appending later.
		$notes_to_append[] = array(
			'note_id'           => $anchor_id, // Use the unified anchor ID for the note itself.
			'trigger_id'        => $trigger_id,
			'note_content_html' => $note_content_html,
			'note_number'       => $note_counter++,
		);
	}

	// 3. Create the notes section at the end of the content.
	$notes_section_html = '';
	if ( ! empty( $notes_to_append ) ) {
		$notes_doc     = new DOMDocument();
		$notes_section = $notes_doc->createElement( 'section' );
		$notes_section->setAttribute( 'class', 'wp-inline-context-noscript-notes' );
		$notes_section->setAttribute( 'aria-label', __( 'Context Notes', 'inline-context' ) );

		$heading = $notes_doc->createElement( 'h2', __( 'Notes', 'inline-context' ) );
		$notes_section->appendChild( $heading );

		$list = $notes_doc->createElement( 'ol' );
		$notes_section->appendChild( $list );

		// Reverse the notes to append them in the correct order.
		foreach ( array_reverse( $notes_to_append ) as $note_data ) {
			$item = $notes_doc->createElement( 'li' );
			$item->setAttribute( 'id', $note_data['note_id'] );

			// Append the note content.
			$fragment = $notes_doc->createDocumentFragment();
			@$fragment->appendXML( $note_data['note_content_html'] );

			// Only append fragment if it has content.
			if ( $fragment->hasChildNodes() ) {
				$item->appendChild( $fragment );
			}

			// Add a "back to text" link.
			$back_link = $notes_doc->createElement( 'a', ' &#8617;' ); // Using ↩ character.
			$back_link->setAttribute( 'href', '#' . $note_data['trigger_id'] );
			$back_link->setAttribute( 'aria-label', __( 'Back to text', 'inline-context' ) );
			$back_link->setAttribute( 'class', 'wp-inline-context-back-link' );

			// Find the last paragraph in the note to append the back link.
			$paragraphs = $item->getElementsByTagName( 'p' );
			if ( $paragraphs->length > 0 ) {
				$last_paragraph = $paragraphs->item( $paragraphs->length - 1 );
				$last_paragraph->appendChild( $back_link );
			} else {
				// If no paragraphs, append directly to the list item.
				$item->appendChild( $back_link );
			}

			$list->appendChild( $item );
		}

		$notes_doc->appendChild( $notes_section );
		$notes_section_html = $notes_doc->saveHTML( $notes_section );
	}

	// Get the modified content and append the notes section.
	$modified_content = $doc->saveHTML();

	// Don't wrap in <noscript> - let CSS hide by default and JS can toggle visibility.
	return $modified_content . $notes_section_html;
}
add_filter( 'the_content', 'inline_context_add_noscript_content', 1 );

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
					'data-category-id'    => true,
					'data-note-id'        => true,
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

		// Pass categories to the editor.
		wp_localize_script(
			'jooplaan-inline-context',
			'inlineContextData',
			array(
				'categories' => inline_context_get_categories(),
			)
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

		// Pass settings and categories to frontend.
		wp_localize_script(
			'jooplaan-inline-context-frontend',
			'inlineContextData',
			array(
				'categories' => inline_context_get_categories(),
			)
		);

		// Enqueue Dashicons for category icons.
		wp_enqueue_style( 'dashicons' );

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
