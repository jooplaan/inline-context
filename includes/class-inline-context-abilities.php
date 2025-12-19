<?php
/**
 * Abilities API Integration
 *
 * Registers plugin functionality with WordPress 6.9+ Abilities API
 * to enable AI assistant discovery and REST API access.
 *
 * @package Inline_Context
 * @since 2.4.0
 */

/**
 * Class Inline_Context_Abilities
 *
 * Handles registration of plugin capabilities with the WordPress Abilities API.
 */
class Inline_Context_Abilities {

	/**
	 * Initialize the abilities integration.
	 *
	 * @since 2.4.0
	 */
	public function init() {
		// Only register if Abilities API is available (WordPress 6.9+).
		if ( ! function_exists( 'wp_register_ability' ) ) {
			return;
		}

		add_action( 'wp_abilities_api_categories_init', array( $this, 'register_categories' ) );
		add_action( 'wp_abilities_api_init', array( $this, 'register_abilities' ) );
	}

	/**
	 * Register ability categories.
	 *
	 * @since 2.4.0
	 */
	public function register_categories() {
		wp_register_ability_category(
			'inline-context',
			array(
				'label'       => __( 'Inline Context', 'inline-context' ),
				'description' => __( 'Manage inline expandable context notes within WordPress content', 'inline-context' ),
			)
		);
	}

	/**
	 * Register plugin abilities.
	 *
	 * @since 2.4.0
	 */
	public function register_abilities() {
		$this->register_create_note_ability();
		$this->register_search_notes_ability();
		$this->register_get_categories_ability();
		$this->register_get_note_ability();
		$this->register_create_inline_note_ability(); // AI content generation helper.
	}

	/**
	 * Register create-note ability.
	 *
	 * Creates a new inline context note with optional category and reusability settings.
	 *
	 * @since 2.4.0
	 */
	private function register_create_note_ability() {
		wp_register_ability(
			'inline-context/create-note',
			array(
				'label'               => __( 'Create Inline Context Note', 'inline-context' ),
				'description'         => __( 'Create a new inline context note with rich text content, optional category, and reusability settings', 'inline-context' ),
				'category'            => 'inline-context',
				'input_schema'        => array(
					'type'       => 'object',
					'properties' => array(
						'title'       => array(
							'type'        => 'string',
							'description' => __( 'Note title for identification and search', 'inline-context' ),
							'minLength'   => 1,
							'maxLength'   => 200,
						),
						'content'     => array(
							'type'        => 'string',
							'description' => __( 'Note content in HTML format (supports rich text)', 'inline-context' ),
							'minLength'   => 1,
						),
						'category'    => array(
							'type'        => 'string',
							'description' => __( 'Category slug (optional)', 'inline-context' ),
						),
						'is_reusable' => array(
							'type'        => 'boolean',
							'description' => __( 'Whether the note can be reused across multiple posts', 'inline-context' ),
							'default'     => false,
						),
					),
					'required'   => array( 'title', 'content' ),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'success' => array(
							'type'        => 'boolean',
							'description' => __( 'Whether the note was created successfully', 'inline-context' ),
						),
						'note_id' => array(
							'type'        => 'integer',
							'description' => __( 'ID of the created note', 'inline-context' ),
						),
						'message' => array(
							'type'        => 'string',
							'description' => __( 'Success or error message', 'inline-context' ),
						),
					),
				),
				'execute_callback'    => array( $this, 'execute_create_note' ),
				'permission_callback' => function () {
					return current_user_can( 'edit_posts' );
				},
				'meta'                => array(
					'show_in_rest' => true,
				),
			)
		);
	}

	/**
	 * Register search-notes ability.
	 *
	 * Search for existing inline context notes by title or content.
	 *
	 * @since 2.4.0
	 */
	private function register_search_notes_ability() {
		wp_register_ability(
			'inline-context/search-notes',
			array(
				'label'               => __( 'Search Inline Context Notes', 'inline-context' ),
				'description'         => __( 'Search for existing inline context notes by title or content', 'inline-context' ),
				'category'            => 'inline-context',
				'input_schema'        => array(
					'type'       => 'object',
					'properties' => array(
						'search'        => array(
							'type'        => 'string',
							'description' => __( 'Search term to match against title and content', 'inline-context' ),
							'minLength'   => 1,
						),
						'reusable_only' => array(
							'type'        => 'boolean',
							'description' => __( 'Only return reusable notes', 'inline-context' ),
							'default'     => false,
						),
						'limit'         => array(
							'type'        => 'integer',
							'description' => __( 'Maximum number of results to return', 'inline-context' ),
							'default'     => 10,
							'minimum'     => 1,
							'maximum'     => 50,
						),
					),
					'required'   => array( 'search' ),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'notes' => array(
							'type'  => 'array',
							'items' => array(
								'type'       => 'object',
								'properties' => array(
									'id'          => array(
										'type'        => 'integer',
										'description' => __( 'Note ID', 'inline-context' ),
									),
									'title'       => array(
										'type'        => 'string',
										'description' => __( 'Note title', 'inline-context' ),
									),
									'content'     => array(
										'type'        => 'string',
										'description' => __( 'Note content (HTML)', 'inline-context' ),
									),
									'excerpt'     => array(
										'type'        => 'string',
										'description' => __( 'Short excerpt of note content', 'inline-context' ),
									),
									'is_reusable' => array(
										'type'        => 'boolean',
										'description' => __( 'Whether note is reusable', 'inline-context' ),
									),
									'category'    => array(
										'type'        => 'string',
										'description' => __( 'Category name', 'inline-context' ),
									),
								),
							),
						),
						'total' => array(
							'type'        => 'integer',
							'description' => __( 'Total number of results', 'inline-context' ),
						),
					),
					'required'   => array( 'notes', 'total' ),
				),
				'execute_callback'    => array( $this, 'execute_search_notes' ),
				'permission_callback' => function () {
					return current_user_can( 'edit_posts' );
				},
				'meta'                => array(
					'show_in_rest' => true,
				),
			)
		);
	}

	/**
	 * Register get-categories ability.
	 *
	 * Retrieve all available inline context categories.
	 *
	 * @since 2.4.0
	 */
	private function register_get_categories_ability() {
		wp_register_ability(
			'inline-context/get-categories',
			array(
				'label'               => __( 'Get Inline Context Categories', 'inline-context' ),
				'description'         => __( 'Retrieve all available inline context categories with their metadata', 'inline-context' ),
				'category'            => 'inline-context',
				'input_schema'        => array(
					'type'       => 'object',
					'properties' => array(
						'include_empty' => array(
							'type'        => 'boolean',
							'description' => __( 'Include categories with no notes', 'inline-context' ),
							'default'     => false,
						),
					),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'categories' => array(
							'type'  => 'array',
							'items' => array(
								'type'       => 'object',
								'properties' => array(
									'id'          => array(
										'type'        => 'integer',
										'description' => __( 'Category term ID', 'inline-context' ),
									),
									'slug'        => array(
										'type'        => 'string',
										'description' => __( 'Category slug', 'inline-context' ),
									),
									'name'        => array(
										'type'        => 'string',
										'description' => __( 'Category name', 'inline-context' ),
									),
									'description' => array(
										'type'        => 'string',
										'description' => __( 'Category description', 'inline-context' ),
									),
									'color'       => array(
										'type'        => 'string',
										'description' => __( 'Category color (hex)', 'inline-context' ),
									),
									'icon_closed' => array(
										'type'        => 'string',
										'description' => __( 'Dashicon class for closed state', 'inline-context' ),
									),
									'icon_open'   => array(
										'type'        => 'string',
										'description' => __( 'Dashicon class for open state', 'inline-context' ),
									),
								),
							),
						),
					),
					'required'   => array( 'categories' ),
				),
				'execute_callback'    => array( $this, 'execute_get_categories' ),
				'permission_callback' => function () {
					return current_user_can( 'read' );
				},
				'meta'                => array(
					'show_in_rest' => true,
				),
			)
		);
	}

	/**
	 * Register get-note ability.
	 *
	 * Retrieve a specific inline context note by ID.
	 *
	 * @since 2.4.0
	 */
	private function register_get_note_ability() {
		wp_register_ability(
			'inline-context/get-note',
			array(
				'label'               => __( 'Get Inline Context Note', 'inline-context' ),
				'description'         => __( 'Retrieve a specific inline context note by ID', 'inline-context' ),
				'category'            => 'inline-context',
				'input_schema'        => array(
					'type'       => 'object',
					'properties' => array(
						'note_id' => array(
							'type'        => 'integer',
							'description' => __( 'ID of the note to retrieve', 'inline-context' ),
							'minimum'     => 1,
						),
					),
					'required'   => array( 'note_id' ),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'id'           => array(
							'type'        => 'integer',
							'description' => __( 'Note ID', 'inline-context' ),
						),
						'title'        => array(
							'type'        => 'string',
							'description' => __( 'Note title', 'inline-context' ),
						),
						'content'      => array(
							'type'        => 'string',
							'description' => __( 'Note content (HTML)', 'inline-context' ),
						),
						'is_reusable'  => array(
							'type'        => 'boolean',
							'description' => __( 'Whether note is reusable', 'inline-context' ),
						),
						'usage_count'  => array(
							'type'        => 'integer',
							'description' => __( 'Number of times note is used', 'inline-context' ),
						),
						'category'     => array(
							'type'        => 'string',
							'description' => __( 'Category name', 'inline-context' ),
						),
						'category_id'  => array(
							'type'        => 'integer',
							'description' => __( 'Category term ID', 'inline-context' ),
						),
						'date_created' => array(
							'type'        => 'string',
							'description' => __( 'Date created (ISO 8601)', 'inline-context' ),
						),
					),
				),
				'execute_callback'    => array( $this, 'execute_get_note' ),
				'permission_callback' => function () {
					return current_user_can( 'read' );
				},
				'meta'                => array(
					'show_in_rest' => true,
				),
			)
		);
	}

	/**
	 * Register create-inline-note ability for AI content generators.
	 *
	 * This ability creates a note AND returns ready-to-use HTML markup
	 * that AI systems can directly embed in generated content.
	 *
	 * @since 2.4.0
	 */
	private function register_create_inline_note_ability() {
		wp_register_ability(
			'inline-context/create-inline-note',
			array(
				'label'               => __( 'Create Inline Note with Markup', 'inline-context' ),
				'description'         => __( 'Create an inline context note and get HTML markup to embed in content. Perfect for AI content generators.', 'inline-context' ),
				'category'            => 'inline-context',
				'input_schema'        => array(
					'type'       => 'object',
					'properties' => array(
						'text'        => array(
							'type'        => 'string',
							'description' => __( 'The text that will be linked (e.g., "API", "Machine Learning")', 'inline-context' ),
							'minLength'   => 1,
							'maxLength'   => 200,
						),
						'note'        => array(
							'type'        => 'string',
							'description' => __( 'The explanatory note content in HTML format', 'inline-context' ),
							'minLength'   => 1,
						),
						'category'    => array(
							'type'        => 'string',
							'description' => __( 'Category slug (optional, e.g., "definition", "example", "source")', 'inline-context' ),
						),
						'is_reusable' => array(
							'type'        => 'boolean',
							'description' => __( 'Whether this note can be reused in other content', 'inline-context' ),
							'default'     => true,
						),
					),
					'required'   => array( 'text', 'note' ),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'success' => array(
							'type'        => 'boolean',
							'description' => __( 'Whether the note was created successfully', 'inline-context' ),
						),
						'note_id' => array(
							'type'        => 'integer',
							'description' => __( 'ID of the created note', 'inline-context' ),
						),
						'html'    => array(
							'type'        => 'string',
							'description' => __( 'Ready-to-use HTML markup to embed in content', 'inline-context' ),
						),
						'message' => array(
							'type'        => 'string',
							'description' => __( 'Success or error message', 'inline-context' ),
						),
					),
				),
				'execute_callback'    => array( $this, 'execute_create_inline_note' ),
				'permission_callback' => function () {
					return current_user_can( 'edit_posts' );
				},
				'meta'                => array(
					'show_in_rest' => true,
				),
			)
		);
	}

	/**
	 * Execute create-note ability.
	 *
	 * @since 2.4.0
	 * @param array $input Input parameters.
	 * @return array|WP_Error Result array or WP_Error on failure.
	 */
	public function execute_create_note( $input ) {
		// Create the post.
		$post_id = wp_insert_post(
			array(
				'post_type'    => 'inline_context_note',
				'post_title'   => sanitize_text_field( $input['title'] ),
				'post_content' => wp_kses_post( $input['content'] ),
				'post_status'  => 'publish',
			)
		);

		if ( is_wp_error( $post_id ) ) {
			return $post_id;
		}

		// Set reusability.
		$is_reusable = isset( $input['is_reusable'] ) ? (bool) $input['is_reusable'] : false;
		update_post_meta( $post_id, 'is_reusable', $is_reusable );
		update_post_meta( $post_id, 'used_in_posts', array() );
		update_post_meta( $post_id, 'usage_count', 0 );

		// Set category if provided.
		if ( ! empty( $input['category'] ) ) {
			$term = get_term_by( 'slug', $input['category'], 'inline_context_category' );
			if ( $term ) {
				wp_set_object_terms( $post_id, array( $term->term_id ), 'inline_context_category', false );
			}
		}

		return array(
			'success' => true,
			'note_id' => $post_id,
			'message' => __( 'Note created successfully', 'inline-context' ),
		);
	}

	/**
	 * Execute search-notes ability.
	 *
	 * @since 2.4.0
	 * @param array $input Input parameters.
	 * @return array Search results.
	 */
	public function execute_search_notes( $input ) {
		$args = array(
			'post_type'      => 'inline_context_note',
			'post_status'    => 'publish',
			's'              => sanitize_text_field( $input['search'] ),
			'posts_per_page' => isset( $input['limit'] ) ? absint( $input['limit'] ) : 10,
		);

		// Filter by reusable only if requested.
		if ( ! empty( $input['reusable_only'] ) ) {
			$args['meta_query'] = array(
				array(
					'key'   => 'is_reusable',
					'value' => true,
				),
			);
		}

		$query   = new WP_Query( $args );
		$results = array();

		foreach ( $query->posts as $post ) {
			$is_reusable   = (bool) get_post_meta( $post->ID, 'is_reusable', true );
			$categories    = wp_get_object_terms( $post->ID, 'inline_context_category' );
			$category_name = ! empty( $categories ) ? $categories[0]->name : '';

			$results[] = array(
				'id'          => $post->ID,
				'title'       => $post->post_title,
				'content'     => $post->post_content,
				'excerpt'     => wp_trim_words( wp_strip_all_tags( $post->post_content ), 20 ),
				'is_reusable' => $is_reusable,
				'category'    => $category_name,
			);
		}

		return array(
			'notes' => $results,
			'total' => count( $results ),
		);
	}

	/**
	 * Execute get-categories ability.
	 *
	 * @since 2.4.0
	 * @param mixed $input Input parameters (unused).
	 * @return array Categories data.
	 */
	public function execute_get_categories( $input ) {
		$categories = inline_context_get_categories();
		$results    = array();

		foreach ( $categories as $category ) {
			$results[] = array(
				'id'          => $category['id'],
				'slug'        => $category['slug'],
				'name'        => $category['name'],
				'color'       => $category['color'],
				'icon_closed' => $category['icon_closed'],
				'icon_open'   => $category['icon_open'],
			);
		}

		return array(
			'categories' => $results,
		);
	}

	/**
	 * Execute get-note ability.
	 *
	 * @since 2.4.0
	 * @param array $input Input parameters.
	 * @return array|WP_Error Note data or error.
	 */
	public function execute_get_note( $input ) {
		$note_id = absint( $input['note_id'] );
		$post    = get_post( $note_id );

		if ( ! $post || 'inline_context_note' !== $post->post_type ) {
			return new WP_Error(
				'note_not_found',
				__( 'Note not found', 'inline-context' ),
				array( 'status' => 404 )
			);
		}

		$is_reusable   = (bool) get_post_meta( $post->ID, 'is_reusable', true );
		$usage_count   = absint( get_post_meta( $post->ID, 'usage_count', true ) );
		$categories    = wp_get_object_terms( $post->ID, 'inline_context_category' );
		$category_name = ! empty( $categories ) ? $categories[0]->name : '';
		$category_id   = ! empty( $categories ) ? $categories[0]->term_id : 0;

		return array(
			'id'           => $post->ID,
			'title'        => $post->post_title,
			'content'      => $post->post_content,
			'is_reusable'  => $is_reusable,
			'usage_count'  => $usage_count,
			'category'     => $category_name,
			'category_id'  => $category_id,
			'date_created' => $post->post_date,
		);
	}

	/**
	 * Execute create-inline-note ability.
	 *
	 * Creates a note and returns HTML markup ready to embed.
	 *
	 * @since 2.4.0
	 * @param array $input Input parameters.
	 * @return array|WP_Error Result array or WP_Error on failure.
	 */
	public function execute_create_inline_note( $input ) {
		$text = sanitize_text_field( $input['text'] );
		$note = wp_kses_post( $input['note'] );

		// Create the note post.
		$post_id = wp_insert_post(
			array(
				'post_type'    => 'inline_context_note',
				'post_title'   => $text, // Use the linked text as title.
				'post_content' => $note,
				'post_status'  => 'publish',
			)
		);

		if ( is_wp_error( $post_id ) ) {
			return $post_id;
		}

		// Set reusability (default to true for AI-generated).
		$is_reusable = isset( $input['is_reusable'] ) ? (bool) $input['is_reusable'] : true;
		update_post_meta( $post_id, 'is_reusable', $is_reusable );

		// Set category if provided.
		if ( ! empty( $input['category'] ) ) {
			$term = get_term_by( 'slug', $input['category'], 'inline_context_category' );
			if ( $term ) {
				wp_set_object_terms( $post_id, array( $term->term_id ), 'inline_context_category' );
			}
		}

		// Generate unique anchor ID.
		$anchor_id = 'context-note-' . substr( md5( $text . $post_id . time() ), 0, 8 );

		// Build the HTML markup.
		$html = sprintf(
			'<a class="wp-inline-context" data-note-id="%d" data-inline-context="%s" data-anchor-id="%s" href="#%s" role="button" aria-expanded="false">%s</a>',
			$post_id,
			esc_attr( $note ),
			esc_attr( $anchor_id ),
			esc_attr( $anchor_id ),
			esc_html( $text )
		);

		return array(
			'success' => true,
			'note_id' => $post_id,
			'html'    => $html,
			'message' => __( 'Inline note created successfully. Use the HTML markup in your content.', 'inline-context' ),
		);
	}
}
