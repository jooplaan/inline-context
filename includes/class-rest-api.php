<?php
/**
 * REST API endpoints for Inline Context plugin.
 *
 * @package InlineContext
 */

/**
 * Class Inline_Context_REST_API
 *
 * Handles all REST API endpoints for searching notes, tracking usage,
 * and handling note removals.
 */
class Inline_Context_REST_API {

	/**
	 * API namespace for all endpoints.
	 */
	const NAMESPACE = 'inline-context/v1';

	/**
	 * Initialize REST API by registering hooks.
	 */
	public function init() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Register all REST API routes.
	 */
	public function register_routes() {
		$this->register_search_endpoint();
		$this->register_track_usage_endpoint();
		$this->register_handle_removals_endpoint();
	}

	/**
	 * Register the /notes/search endpoint for searching notes.
	 */
	private function register_search_endpoint() {
		register_rest_route(
			self::NAMESPACE,
			'/notes/search',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'search_notes' ),
				'permission_callback' => array( $this, 'check_edit_posts_permission' ),
			)
		);
	}

	/**
	 * Register the /notes/{id}/track-usage endpoint.
	 */
	private function register_track_usage_endpoint() {
		register_rest_route(
			self::NAMESPACE,
			'/notes/(?P<id>\d+)/track-usage',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'track_usage' ),
				'permission_callback' => array( $this, 'check_edit_posts_permission' ),
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
	}

	/**
	 * Register the /notes/handle-removals endpoint.
	 */
	private function register_handle_removals_endpoint() {
		register_rest_route(
			self::NAMESPACE,
			'/notes/handle-removals',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'handle_removals' ),
				'permission_callback' => array( $this, 'check_edit_posts_permission' ),
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

	/**
	 * Search notes endpoint callback.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 * @return WP_REST_Response The response containing search results.
	 */
	public function search_notes( $request ) {
		$search        = $request->get_param( 's' );
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
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Necessary for REST API filtering, limited to 20 posts with simple key/value comparison.
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
			$terms        = wp_get_post_terms( $post->ID, 'inline_context_category' );
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
	}

	/**
	 * Track usage endpoint callback.
	 *
	 * NOTE: This endpoint is now a no-op for tracking. Actual usage tracking
	 * is handled by the post_updated hook which accurately counts all note
	 * occurrences when the post is saved. This prevents duplicate/incorrect
	 * tracking from multiple JavaScript calls.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 * @return WP_REST_Response|WP_Error The response or error.
	 */
	public function track_usage( $request ) {
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
	}

	/**
	 * Handle note removals endpoint callback.
	 *
	 * Processes an array of note IDs that were removed from a post,
	 * updating their usage metadata and optionally deleting non-reusable notes.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 * @return WP_REST_Response|WP_Error The response or error.
	 */
	public function handle_removals( $request ) {
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
	}

	/**
	 * Permission callback to check if user can edit posts.
	 *
	 * @return bool True if user can edit posts.
	 */
	public function check_edit_posts_permission() {
		return current_user_can( 'edit_posts' );
	}
}
