<?php
/**
 * Deletion functionality for Inline Context plugin.
 *
 * Handles deletion protection for reusable notes and cleanup
 * of non-reusable notes from posts.
 *
 * @package InlineContext
 * @since 1.5.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Inline_Context_Deletion
 *
 * Manages note deletion and cleanup.
 */
class Inline_Context_Deletion {

	/**
	 * Reference to sync class for hook management.
	 *
	 * @var Inline_Context_Sync
	 */
	private $sync;

	/**
	 * Constructor.
	 *
	 * @param Inline_Context_Sync $sync Sync class instance.
	 */
	public function __construct( $sync ) {
		$this->sync = $sync;
	}

	/**
	 * Initialize deletion functionality.
	 */
	public function init() {
		// Prevent trashing reusable notes in use.
		add_action( 'wp_trash_post', array( $this, 'prevent_trash_reusable_note' ), 10, 1 );

		// Clean up non-reusable notes after trash.
		add_action( 'trashed_post', array( $this, 'cleanup_after_trash' ), 10, 1 );

		// Handle permanent deletion.
		add_action( 'before_delete_post', array( $this, 'handle_permanent_delete' ), 10, 2 );
	}

	/**
	 * Prevent trashing a reusable note that's in use.
	 *
	 * @param int $post_id Post ID being trashed.
	 */
	public function prevent_trash_reusable_note( $post_id ) {
		$post = get_post( $post_id );

		if ( ! $post || 'inline_context_note' !== $post->post_type ) {
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
	}

	/**
	 * Clean up non-reusable notes from posts after they've been trashed.
	 *
	 * @param int $post_id Post ID that was trashed.
	 */
	public function cleanup_after_trash( $post_id ) {
		$post = get_post( $post_id );

		if ( ! $post || 'inline_context_note' !== $post->post_type ) {
			return;
		}

		$is_reusable = get_post_meta( $post_id, 'is_reusable', true );
		$used_in     = get_post_meta( $post_id, 'used_in_posts', true );

		// For non-reusable notes that are in use, remove them from all posts.
		if ( ! $is_reusable && ! empty( $used_in ) && is_array( $used_in ) ) {
			$this->remove_note_from_posts( $post_id, $used_in );
		}
	}

	/**
	 * Prevent deleting a reusable note that's in use.
	 * For non-reusable notes, remove them from posts when permanently deleted.
	 *
	 * @param int      $post_id Post ID being deleted.
	 * @param \WP_Post $post    Post object being deleted.
	 */
	public function handle_permanent_delete( $post_id, $post ) {
		if ( ! $post || 'inline_context_note' !== $post->post_type ) {
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
			$this->remove_note_from_posts( $post_id, $used_in );
		}
	}

	/**
	 * Remove a note from all posts where it's used.
	 *
	 * Removes the <a> tag but preserves the link text.
	 *
	 * @param int   $note_id The note ID to remove.
	 * @param array $used_in Array of usage data with post_id and count.
	 */
	private function remove_note_from_posts( $note_id, $used_in ) {
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
			// Replacement: link text (just the captured content).
			$pattern = '/<a\s+[^>]*?data-note-id=["\']' . preg_quote( (string) $note_id, '/' ) . '["\'][^>]*?>(.*?)<\/a>/is';

			$updated_content = preg_replace( $pattern, '$1', $using_post->post_content );

			if ( $updated_content !== $using_post->post_content ) {
				// Prevent infinite loops by temporarily removing sync hooks.
				remove_action( 'post_updated', array( $this->sync, 'handle_post_update' ), 10 );

				wp_update_post(
					array(
						'ID'           => $using_post_id,
						'post_content' => $updated_content,
					)
				);

				// Re-add the hooks.
				add_action( 'post_updated', array( $this->sync, 'handle_post_update' ), 10, 3 );
			}
		}
	}
}
