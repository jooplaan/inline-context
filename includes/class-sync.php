<?php
/**
 * Sync functionality for Inline Context plugin.
 *
 * Handles syncing note usage tracking, reusable note content updates,
 * and category attribute synchronization.
 *
 * @package InlineContext
 * @since 1.5.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Inline_Context_Sync
 *
 * Manages synchronization of note data across posts.
 */
class Inline_Context_Sync {

	/**
	 * Initialize sync functionality.
	 */
	public function init() {
		// Sync usage and content when posts are updated.
		add_action( 'post_updated', array( $this, 'handle_post_update' ), 10, 3 );

		// Sync category when taxonomy terms change.
		add_action( 'set_object_terms', array( $this, 'handle_category_change' ), 10, 6 );
	}

	/**
	 * Handle post updates - sync usage and reusable note content.
	 *
	 * @param int      $post_id     Post ID.
	 * @param \WP_Post $post_after  Post object after update.
	 * @param \WP_Post $post_before Post object before update.
	 */
	public function handle_post_update( $post_id, $post_after, $post_before ) {
		// Sync usage when a regular post/page is updated.
		$this->sync_note_usage_on_post_save( $post_id, $post_after, $post_before );

		// Sync reusable note content when the note itself is updated.
		$this->sync_reusable_note_content( $post_id, $post_after, $post_before );
	}

	/**
	 * Syncs the usage count of notes when a post is saved.
	 *
	 * @param int      $post_id     The ID of the post being saved.
	 * @param \WP_Post $post_after  The post object after the update.
	 * @param \WP_Post $post_before The post object before the update.
	 */
	public function sync_note_usage_on_post_save( $post_id, $post_after, $post_before ) {
		// Static variable to prevent infinite loops.
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

		// Additional validation: Check all notes that claim to be used in this post.
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
			$this->remove_note_usage( $note_id, $post_id );
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

			$this->add_or_update_note_usage( $note_id, $post_id, $count_in_post );
		}

		// Unmark as processing.
		unset( $processing[ $post_id ] );
	}

	/**
	 * Remove note usage from a post.
	 *
	 * @param int $note_id Note post ID.
	 * @param int $post_id Post ID using the note.
	 */
	private function remove_note_usage( $note_id, $post_id ) {
		if ( ! $note_id ) {
			return;
		}

		$used_in = get_post_meta( $note_id, 'used_in_posts', true );
		if ( ! is_array( $used_in ) ) {
			$used_in = array();
		}

		// Clean up corrupted data.
		$used_in = $this->clean_usage_data( $used_in );

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
		$total_count = $this->calculate_total_usage( $updated_usage );
		update_post_meta( $note_id, 'usage_count', $total_count );

		// If a non-reusable note has no more usages, delete it.
		$is_reusable = (bool) get_post_meta( $note_id, 'is_reusable', true );
		if ( ! $is_reusable && empty( $updated_usage ) ) {
			wp_delete_post( $note_id, true ); // Force delete.
		}
	}

	/**
	 * Add or update note usage in a post.
	 *
	 * @param int $note_id       Note post ID.
	 * @param int $post_id       Post ID using the note.
	 * @param int $count_in_post Number of times the note appears in the post.
	 */
	private function add_or_update_note_usage( $note_id, $post_id, $count_in_post ) {
		$used_in = get_post_meta( $note_id, 'used_in_posts', true );
		if ( ! is_array( $used_in ) ) {
			$used_in = array();
		}

		// Clean up corrupted data.
		$used_in = $this->clean_usage_data( $used_in );

		// Find if this post is already in the usage list and update the count.
		$found = false;
		foreach ( $used_in as $key => $usage_data ) {
			$stored_post_id = $usage_data['post_id'] ?? 0;
			if ( $stored_post_id === $post_id ) {
				$used_in[ $key ]['count'] = $count_in_post;
				$found                    = true;
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

		// Recalculate total usage count.
		$total_count = $this->calculate_total_usage( $used_in );
		update_post_meta( $note_id, 'usage_count', $total_count );
	}

	/**
	 * Clean corrupted usage data (integers instead of arrays).
	 *
	 * @param array $used_in Usage data array.
	 * @return array Cleaned usage data.
	 */
	private function clean_usage_data( $used_in ) {
		$needs_cleanup = false;
		foreach ( $used_in as $usage_data ) {
			if ( ! is_array( $usage_data ) ) {
				$needs_cleanup = true;
				break;
			}
		}

		return $needs_cleanup ? array() : $used_in;
	}

	/**
	 * Calculate total usage count from usage data.
	 *
	 * @param array $usage_data Usage data array.
	 * @return int Total count.
	 */
	private function calculate_total_usage( $usage_data ) {
		$total_count = 0;
		foreach ( $usage_data as $data ) {
			$total_count += $data['count'] ?? 1;
		}
		return $total_count;
	}

	/**
	 * Update all instances of a reusable note when it's updated.
	 *
	 * @param int      $post_id     The ID of the post being saved.
	 * @param \WP_Post $post_after  The post object after the update.
	 * @param \WP_Post $post_before The post object before the update.
	 */
	public function sync_reusable_note_content( $post_id, $post_after, $post_before ) {
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

		// Only update if content changed.
		if ( ! $content_changed ) {
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
	 * Sync note category attribute across all posts using the note.
	 *
	 * @param int $note_id     Note post ID.
	 * @param int $category_id Category term ID.
	 */
	public function sync_note_category_attribute( $note_id, $category_id ) {
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

			// Only touch published posts.
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
				function ( $matches ) use ( $category_id, $attribute_value ) {
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
				// Prevent infinite loops.
				remove_action( 'post_updated', array( $this, 'handle_post_update' ), 10 );

				wp_update_post(
					array(
						'ID'           => $using_post_id,
						'post_content' => $updated_html,
					)
				);

				// Re-add the hook.
				add_action( 'post_updated', array( $this, 'handle_post_update' ), 10, 3 );
			}
		}
	}

	/**
	 * Handle category changes via set_object_terms hook.
	 *
	 * @param int    $object_id  Object ID.
	 * @param array  $terms      Terms array.
	 * @param array  $tt_ids     Term taxonomy IDs.
	 * @param string $taxonomy   Taxonomy name.
	 * @param bool   $append     Whether to append terms.
	 * @param array  $old_tt_ids Old term taxonomy IDs.
	 */
	public function handle_category_change( $object_id, $terms, $tt_ids, $taxonomy, $append, $old_tt_ids ) {
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
		$this->sync_note_category_attribute( $object_id, $category_id );
	}
}
