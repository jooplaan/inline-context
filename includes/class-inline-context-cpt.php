<?php
/**
 * Custom Post Type functionality for Inline Context plugin.
 *
 * Handles CPT registration, taxonomy, metaboxes, and admin UI.
 *
 * @package InlineContext
 * @since 1.5.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Inline_Context_CPT
 *
 * Manages the inline_context_note Custom Post Type.
 */
class Inline_Context_CPT {

	/**
	 * Initialize CPT functionality.
	 */
	public function init() {
		// Register CPT and taxonomy.
		add_action( 'init', array( $this, 'register_cpt_and_taxonomy' ), 9 );

		// Admin UI customization.
		add_filter( 'manage_inline_context_note_posts_columns', array( $this, 'add_custom_columns' ) );
		add_action( 'manage_inline_context_note_posts_custom_column', array( $this, 'populate_custom_columns' ), 10, 2 );
		add_filter( 'manage_edit-inline_context_note_sortable_columns', array( $this, 'make_columns_sortable' ) );
		add_action( 'pre_get_posts', array( $this, 'handle_column_sorting' ) );

		// Filter functionality.
		add_action( 'restrict_manage_posts', array( $this, 'add_reusable_filter' ) );
		add_filter( 'parse_query', array( $this, 'filter_by_reusable' ) );

		// Metaboxes.
		add_action( 'add_meta_boxes', array( $this, 'add_metaboxes' ) );

		// Save hooks.
		add_action( 'save_post_inline_context_note', array( $this, 'save_note_meta' ) );

		// Assets.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_cpt_editor_assets' ) );
		add_action( 'admin_footer', array( $this, 'add_delete_warnings' ) );

		// Cron job for cleanup.
		add_action( 'inline_context_cleanup_unused_notes', array( $this, 'cleanup_unused_notes' ) );
		add_action( 'init', array( $this, 'schedule_cleanup_cron' ) );
		register_deactivation_hook( dirname( __DIR__ ) . '/inline-context.php', array( $this, 'unschedule_cleanup_cron' ) );
	}

	/**
	 * Register the Custom Post Type and taxonomy.
	 */
	public function register_cpt_and_taxonomy() {
		// Register the note taxonomy first (needed for CPT registration).
		register_taxonomy(
			'inline_context_category',
			array( 'inline_context_note' ),
			array(
				'labels'            => array(
					'name'          => __( 'Note Categories', 'inline-context' ),
					'singular_name' => __( 'Note Category', 'inline-context' ),
					'add_new_item'  => __( 'Add New Category', 'inline-context' ),
					'edit_item'     => __( 'Edit Category', 'inline-context' ),
					'all_items'     => __( 'All Categories', 'inline-context' ),
					'search_items'  => __( 'Search Categories', 'inline-context' ),
				),
				'hierarchical'      => false, // Non-hierarchical to allow custom meta box.
				'public'            => false,
				'show_ui'           => true,
				'show_admin_column' => true,
				'show_in_rest'      => true,
				'rewrite'           => false,
				'meta_box_cb'       => false, // Disable default meta box, we'll add custom one.
			)
		);

		// Register the Custom Post Type for notes.
		register_post_type(
			'inline_context_note',
			array(
				'labels'          => array(
					'name'          => __( 'Inline Notes', 'inline-context' ),
					'singular_name' => __( 'Note', 'inline-context' ),
					'add_new'       => __( 'Add New Note', 'inline-context' ),
					'add_new_item'  => __( 'Add New Note', 'inline-context' ),
					'edit_item'     => __( 'Edit Note', 'inline-context' ),
					'new_item'      => __( 'New Note', 'inline-context' ),
					'view_item'     => __( 'View Note', 'inline-context' ),
					'search_items'  => __( 'Search Notes', 'inline-context' ),
					'not_found'     => __( 'No notes found', 'inline-context' ),
				),
				'public'          => false,
				'show_ui'         => true,
				'show_in_menu'    => true,
				'supports'        => array( 'title', 'editor', 'revisions', 'custom-fields' ), // Need 'editor' for REST API content field.
				'taxonomies'      => array( 'inline_context_category' ),
				'show_in_rest'    => true, // Keep for REST API access.
				'menu_icon'       => 'dashicons-info',
				'capability_type' => 'post',
				'map_meta_cap'    => true,
			)
		);

		// Force classic editor for this CPT (disable block editor).
		add_filter(
			'use_block_editor_for_post_type',
			function ( $use_block_editor, $post_type ) {
				if ( 'inline_context_note' === $post_type ) {
					return false; // Use classic editor.
				}
				return $use_block_editor;
			},
			10,
			2
		);
	}

	/**
	 * Add custom columns to the Notes CPT list.
	 *
	 * @param array $columns Existing columns.
	 * @return array Modified columns.
	 */
	public function add_custom_columns( $columns ) {
		// Insert custom columns after title.
		$new_columns = array();
		foreach ( $columns as $key => $value ) {
			$new_columns[ $key ] = $value;
			if ( 'title' === $key ) {
				$new_columns['reusable']    = __( 'Marked as reusable', 'inline-context' );
				$new_columns['usage_count'] = __( 'Usage Count', 'inline-context' );
				$new_columns['used_in']     = __( 'Used In', 'inline-context' );
			}
		}
		return $new_columns;
	}

	/**
	 * Populate custom columns in the Notes CPT list.
	 *
	 * @param string $column  Column name.
	 * @param int    $post_id Post ID.
	 */
	public function populate_custom_columns( $column, $post_id ) {
		switch ( $column ) {
			case 'reusable':
				$is_reusable = (bool) get_post_meta( $post_id, 'is_reusable', true );
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Already escaped by esc_html__().
				echo $is_reusable ? esc_html__( 'Yes', 'inline-context' ) : esc_html__( 'No', 'inline-context' );
				break;

			case 'usage_count':
				$usage_count = (int) get_post_meta( $post_id, 'usage_count', true );
				echo esc_html( $usage_count );
				break;

			case 'used_in':
				$used_in = get_post_meta( $post_id, 'used_in_posts', true );
				if ( ! empty( $used_in ) && is_array( $used_in ) ) {
					$post_links = array();
					// Limit to showing the first 3 posts for brevity.
					foreach ( array_slice( $used_in, 0, 3 ) as $usage_data ) {
						$used_post_id = $usage_data['post_id'] ?? 0;
						$count        = $usage_data['count'] ?? 1;

						if ( ! $used_post_id ) {
							continue;
						}

						$post = get_post( $used_post_id );
						if ( $post ) {
							if ( $count > 1 ) {
								$post_links[] = sprintf(
									'<a href="%s">%s</a> (×%d)',
									esc_url( get_edit_post_link( $used_post_id ) ),
									esc_html( get_the_title( $used_post_id ) ),
									esc_html( $count )
								);
							} else {
								$post_links[] = sprintf(
									'<a href="%s">%s</a>',
									esc_url( get_edit_post_link( $used_post_id ) ),
									esc_html( get_the_title( $used_post_id ) )
								);
							}
						}
					}
					echo wp_kses(
						implode( ', ', $post_links ),
						array(
							'a' => array(
								'href' => array(),
							),
						)
					);
					if ( count( $used_in ) > 3 ) {
						echo esc_html( ' +' . ( count( $used_in ) - 3 ) );
					}
				} else {
					echo '—';
				}
				break;
		}
	}

	/**
	 * Make custom columns sortable.
	 *
	 * @param array $columns Existing sortable columns.
	 * @return array Modified sortable columns.
	 */
	public function make_columns_sortable( $columns ) {
		$columns['usage_count'] = 'usage_count';
		return $columns;
	}

	/**
	 * Handle custom column sorting.
	 *
	 * @param WP_Query $query The WordPress query object.
	 */
	public function handle_column_sorting( $query ) {
		if ( ! is_admin() || ! $query->is_main_query() ) {
			return;
		}

		if ( 'inline_context_note' !== $query->get( 'post_type' ) ) {
			return;
		}

		$orderby = $query->get( 'orderby' );

		if ( 'usage_count' === $orderby ) {
			$query->set( 'meta_key', 'usage_count' );
			$query->set( 'orderby', 'meta_value_num' );
		}
	}

	/**
	 * Add filter dropdown for reusable notes.
	 *
	 * @param string $post_type Current post type.
	 */
	public function add_reusable_filter( $post_type ) {
		if ( 'inline_context_note' !== $post_type ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Safe read-only GET parameter for filtering list table.
		$reusable_filter = isset( $_GET['reusable_filter'] ) ? sanitize_text_field( wp_unslash( $_GET['reusable_filter'] ) ) : '';
		?>
		<select name="reusable_filter">
			<option value=""><?php esc_html_e( 'All Notes', 'inline-context' ); ?></option>
			<option value="reusable" <?php selected( $reusable_filter, 'reusable' ); ?>>
				<?php esc_html_e( 'Reusable Only', 'inline-context' ); ?>
			</option>
			<option value="not_reusable" <?php selected( $reusable_filter, 'not_reusable' ); ?>>
				<?php esc_html_e( 'Not Reusable', 'inline-context' ); ?>
			</option>
		</select>
		<?php
	}

	/**
	 * Filter posts by reusable status.
	 *
	 * @param WP_Query $query The WordPress query object.
	 */
	public function filter_by_reusable( $query ) {
		global $pagenow;

		if ( ! is_admin() || 'edit.php' !== $pagenow || ! isset( $query->query_vars['post_type'] ) || 'inline_context_note' !== $query->query_vars['post_type'] ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Safe read-only GET parameter for filtering list table.
		if ( ! isset( $_GET['reusable_filter'] ) || empty( $_GET['reusable_filter'] ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Safe read-only GET parameter for filtering list table.
		$reusable_filter = sanitize_text_field( wp_unslash( $_GET['reusable_filter'] ) );

		if ( 'reusable' === $reusable_filter ) {
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Necessary for admin filtering, limited to CPT admin list view only.
			$query->query_vars['meta_query'] = array(
				array(
					'key'     => 'is_reusable',
					'value'   => '1',
					'compare' => '=',
				),
			);
		} elseif ( 'not_reusable' === $reusable_filter ) {
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Necessary for admin filtering, limited to CPT admin list view only.
			$query->query_vars['meta_query'] = array(
				'relation' => 'OR',
				array(
					'key'     => 'is_reusable',
					'value'   => '1',
					'compare' => '!=',
				),
				array(
					'key'     => 'is_reusable',
					'compare' => 'NOT EXISTS',
				),
			);
		}
	}

	/**
	 * Add custom metaboxes for the CPT edit screen.
	 */
	public function add_metaboxes() {
		// Remove the default editor.
		remove_post_type_support( 'inline_context_note', 'editor' );

		// Remove custom fields metabox.
		remove_meta_box( 'postcustom', 'inline_context_note', 'normal' );

		// Add QuillEditor content metabox.
		add_meta_box(
			'inline_context_note_content',
			__( 'Note Content', 'inline-context' ),
			array( $this, 'render_content_metabox' ),
			'inline_context_note',
			'normal',
			'default'
		);

		// Add custom category selector.
		add_meta_box(
			'inline_context_category_select',
			__( 'Note Category', 'inline-context' ),
			array( $this, 'render_category_metabox' ),
			'inline_context_note',
			'side',
			'default'
		);

		// Add usage statistics metabox.
		add_meta_box(
			'inline_context_usage_stats',
			__( 'Usage Statistics', 'inline-context' ),
			array( $this, 'render_usage_metabox' ),
			'inline_context_note',
			'side',
			'default'
		);
	}

	/**
	 * Render the QuillEditor content metabox.
	 *
	 * @param WP_Post $post Current post object.
	 */
	public function render_content_metabox( $post ) {
		// Get the current content.
		$content = $post->post_content;

		// Output the container for React to mount into.
		echo '<div id="inline-context-note-editor" data-post-id="' . esc_attr( $post->ID ) . '">';
		echo '<div id="inline-context-quill-root"></div>';
		echo '</div>';

		// Hidden textarea to store the content.
		echo '<textarea id="inline-context-note-content" name="inline_context_note_content" style="display:none;">' . esc_textarea( $content ) . '</textarea>';

		wp_nonce_field( 'inline_context_save_note', 'inline_context_note_nonce' );
	}

	/**
	 * Render the category selector metabox.
	 *
	 * @param WP_Post $post Current post object.
	 */
	public function render_category_metabox( $post ) {
		// Get current category.
		$terms           = wp_get_post_terms( $post->ID, 'inline_context_category' );
		$current_term_id = ! empty( $terms ) && ! is_wp_error( $terms ) ? $terms[0]->term_id : 0;

		// Get all categories.
		$categories = get_terms(
			array(
				'taxonomy'   => 'inline_context_category',
				'hide_empty' => false,
			)
		);

		wp_nonce_field( 'inline_context_category_nonce', 'inline_context_category_nonce_field' );

		echo '<div style="padding: 10px 0;">';

		// "No category" option.
		echo '<label style="display: block; margin-bottom: 8px;">';
		echo '<input type="radio" name="inline_context_category_id" value="0"';
		if ( 0 === $current_term_id ) {
			echo ' checked="checked"';
		}
		echo ' /> ';
		echo esc_html__( 'No category', 'inline-context' );
		echo '</label>';

		// Category options.
		if ( ! is_wp_error( $categories ) && ! empty( $categories ) ) {
			foreach ( $categories as $category ) {
				echo '<label style="display: block; margin-bottom: 8px;">';
				echo '<input type="radio" name="inline_context_category_id" value="' . esc_attr( $category->term_id ) . '"';
				if ( $current_term_id === $category->term_id ) {
					echo ' checked="checked"';
				}
				echo ' /> ';
				echo esc_html( $category->name );
				echo '</label>';
			}
		}

		echo '</div>';
	}

	/**
	 * Render the usage statistics metabox.
	 *
	 * @param WP_Post $post Current post object.
	 */
	public function render_usage_metabox( $post ) {
		$used_in_posts = get_post_meta( $post->ID, 'used_in_posts', true );
		$is_reusable   = (bool) get_post_meta( $post->ID, 'is_reusable', true );

		if ( ! is_array( $used_in_posts ) ) {
			$used_in_posts = array();
		}

		// Calculate actual usage count.
		$actual_usage_count = 0;
		foreach ( $used_in_posts as $usage_data ) {
			$actual_usage_count += $usage_data['count'] ?? 1;
		}

		$stored_usage_count = (int) get_post_meta( $post->ID, 'usage_count', true );

		if ( $actual_usage_count !== $stored_usage_count ) {
			update_post_meta( $post->ID, 'usage_count', $actual_usage_count );
		}

		$usage_count = $actual_usage_count;
		$post_count  = count( $used_in_posts ); // Number of distinct posts.

		wp_nonce_field( 'inline_context_usage_meta_nonce', 'inline_context_usage_meta_nonce_field' );

		echo '<div style="padding: 10px 0;">';

		// Usage count.
		echo '<p><strong>' . esc_html__( 'Used in:', 'inline-context' ) . '</strong> ';
		if ( $post_count > 0 ) {
			echo '<span style="font-size: 1.2em; color: #2271b1;">' . esc_html( $post_count ) . '</span> ';
			echo esc_html( _n( 'post', 'posts', $post_count, 'inline-context' ) );
			if ( $usage_count > $post_count ) {
				echo ' <span style="color: #666; font-size: 0.9em;">(' . esc_html( $usage_count ) . ' ' . esc_html__( 'times total', 'inline-context' ) . ')</span>';
			}
		} else {
			echo '<span style="color: #999;">' . esc_html__( 'Not used yet', 'inline-context' ) . '</span>';
		}
		echo '</p>';

		// List of posts where used.
		if ( ! empty( $used_in_posts ) ) {
			echo '<div style="margin: 15px 0; padding: 10px; background: #f6f7f7; border-left: 3px solid #2271b1;">';
			echo '<p style="margin: 0 0 10px 0; font-weight: 600;">' . esc_html__( 'Used in these posts:', 'inline-context' ) . '</p>';
			echo '<ul style="margin: 0; padding-left: 20px;">';

			foreach ( $used_in_posts as $usage_data ) {
				$used_post_id = $usage_data['post_id'] ?? 0;
				$count        = $usage_data['count'] ?? 1;

				if ( ! $used_post_id ) {
					continue;
				}

				$used_post = get_post( $used_post_id );
				if ( $used_post ) {
					$edit_link = get_edit_post_link( $used_post_id );
					$view_link = get_permalink( $used_post_id );

					echo '<li style="margin-bottom: 8px;">';
					echo '<a href="' . esc_url( $edit_link ) . '" style="font-weight: 500;">';
					echo esc_html( get_the_title( $used_post_id ) );
					echo '</a>';
					echo ' <span style="color: #666;">(' . esc_html( get_post_type( $used_post_id ) ) . ')</span>';
					if ( $count > 1 ) {
						echo ' <span style="color: #2271b1; font-weight: 600;">×' . esc_html( $count ) . '</span>';
					}
					if ( 'publish' === $used_post->post_status ) {
						echo ' <a href="' . esc_url( $view_link ) . '" target="_blank" style="font-size: 0.9em;">↗</a>';
					}
					echo '</li>';
				}
			}

			echo '</ul>';
			echo '</div>';
		}

		// Reusable checkbox.
		echo '<div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #ddd;">';
		echo '<label style="display: flex; align-items: center; cursor: pointer;">';
		echo '<input type="checkbox" name="inline_context_is_reusable" value="1"';
		if ( $is_reusable ) {
			echo ' checked="checked"';
		}
		echo ' style="margin-right: 8px;" />';
		echo '<span>' . esc_html__( 'Mark as reusable', 'inline-context' ) . '</span>';
		echo '</label>';
		echo '<p style="margin: 8px 0 0 0; font-size: 0.9em; color: #666;">';
		echo esc_html__( 'Reusable notes appear in search results and can be used across multiple posts.', 'inline-context' );
		echo '</p>';
		echo '</div>';

		// Warning if used in multiple posts.
		if ( $post_count > 1 ) {
			echo '<div style="margin-top: 15px; padding: 10px; background: #fff3cd; border-left: 3px solid #ffc107;">';
			echo '<p style="margin: 0; font-size: 0.9em;">';
			echo '<strong>⚠️ ' . esc_html__( 'Note:', 'inline-context' ) . '</strong> ';
			echo esc_html__( 'This note is used in multiple posts. Changes to the content will not automatically update existing usages.', 'inline-context' );
			echo '</p>';
			echo '</div>';
		}

		// Delete warning if note is in use.
		if ( $post_count > 0 ) {
			echo '<div style="margin-top: 15px; padding: 12px; background: #f8d7da; border-left: 3px solid #dc3545;">';
			echo '<p style="margin: 0 0 8px 0; font-weight: 600; color: #721c24;">';
			echo '🗑️ ' . esc_html__( 'Before deleting this note:', 'inline-context' );
			echo '</p>';
			echo '<p style="margin: 0; font-size: 0.9em; color: #721c24;">';

			// Show usage count and post count.
			echo esc_html(
				sprintf(
					/* translators: 1: number of times used, 2: number of posts */
					_n(
						'Deleting this note will remove %1$d use from %2$d post.',
						'Deleting this note will remove %1$d uses from %2$d posts.',
						$usage_count,
						'inline-context'
					),
					$usage_count,
					$post_count
				)
			);

			echo '</p>';
			echo '</div>';
		}

		echo '</div>';
	}

	/**
	 * Save note metadata when the CPT is saved.
	 *
	 * @param int $post_id Post ID being saved.
	 */
	public function save_note_meta( $post_id ) {
		// Check autosave.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Check permissions.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Prevent infinite loop.
		static $is_saving = false;
		if ( $is_saving ) {
			return;
		}
		$is_saving = true;

		// Save the content (only if nonce is present and valid).
		if ( isset( $_POST['inline_context_note_nonce'] ) &&
			wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['inline_context_note_nonce'] ) ), 'inline_context_save_note' ) &&
			isset( $_POST['inline_context_note_content'] ) ) {

			// Update content directly in database to avoid triggering save_post again.
			wp_update_post(
				array(
					'ID'           => $post_id,
					'post_content' => wp_kses_post( wp_unslash( $_POST['inline_context_note_content'] ) ),
				),
				false, // Don't trigger wp_error.
				false  // Don't fire hooks.
			);
		}

		// Save the category (verify separate nonce).
		if ( isset( $_POST['inline_context_category_nonce_field'] ) &&
			wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['inline_context_category_nonce_field'] ) ), 'inline_context_category_nonce' ) &&
			isset( $_POST['inline_context_category_id'] ) ) {

			$submitted_category_id = intval( $_POST['inline_context_category_id'] );

			if ( $submitted_category_id > 0 ) {
				// Set single category.
				wp_set_post_terms( $post_id, array( $submitted_category_id ), 'inline_context_category', false );
			} else {
				// Remove all categories.
				wp_set_post_terms( $post_id, array(), 'inline_context_category', false );
			}
		}

		// Save the "is_reusable" flag (verify nonce).
		if ( isset( $_POST['inline_context_usage_meta_nonce_field'] ) &&
			wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['inline_context_usage_meta_nonce_field'] ) ), 'inline_context_usage_meta_nonce' ) ) {

			$new_is_reusable = isset( $_POST['inline_context_is_reusable'] ) ? true : false;
			$old_is_reusable = get_post_meta( $post_id, 'is_reusable', true );

			// Check if trying to unmark a reusable note that's in use.
			if ( $old_is_reusable && ! $new_is_reusable ) {
				$used_in = get_post_meta( $post_id, 'used_in_posts', true );
				if ( ! empty( $used_in ) && is_array( $used_in ) ) {
					// Prevent unchecking - keep it reusable.
					update_post_meta( $post_id, 'is_reusable', true );

					// Show admin notice.
					$usage_count = count( $used_in );
					add_settings_error(
						'inline_context_messages',
						'inline_context_reusable_in_use',
						sprintf(
							/* translators: %d: number of posts using the note */
							_n(
								'This note is currently used in %d post. To unmark as reusable, first remove it from all posts.',
								'This note is currently used in %d posts. To unmark as reusable, first remove it from all posts.',
								$usage_count,
								'inline-context'
							),
							$usage_count
						),
						'error'
					);

					// Store the notice to display on redirect.
					set_transient( 'inline_context_admin_notice_' . $post_id, get_settings_errors( 'inline_context_messages' ), 30 );
				} else {
					// Not in use, allow unchecking.
					update_post_meta( $post_id, 'is_reusable', $new_is_reusable );
				}
			} else {
				// Allow checking or no change.
				update_post_meta( $post_id, 'is_reusable', $new_is_reusable );
			}
		}

		$is_saving = false;
	}

	/**
	 * Enqueue QuillEditor assets for CPT edit screen.
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function enqueue_cpt_editor_assets( $hook ) {
		// Only load on the CPT edit screen.
		$screen = get_current_screen();
		if ( ! $screen || 'inline_context_note' !== $screen->post_type || ( 'post.php' !== $hook && 'post-new.php' !== $hook ) ) {
			return;
		}

		// Enqueue the CPT editor assets.
		$asset_file = include plugin_dir_path( __DIR__ ) . 'build/cpt-editor.asset.php';

		wp_enqueue_script(
			'jooplaan-inline-context-cpt-editor',
			plugins_url( 'build/cpt-editor.js', __DIR__ ),
			$asset_file['dependencies'],
			$asset_file['version'],
			true
		);

		wp_enqueue_style(
			'jooplaan-inline-context-cpt-editor',
			plugins_url( 'build/index.css', __DIR__ ),
			array(),
			$asset_file['version']
		);

		// Pass categories and initial content to JavaScript.
		$categories = inline_context_get_categories();
		$post_id    = get_the_ID();
		$content    = $post_id ? get_post_field( 'post_content', $post_id ) : '';

		wp_localize_script(
			'jooplaan-inline-context-cpt-editor',
			'inlineContextCPTEditor',
			array(
				'categories' => $categories,
				'content'    => $content,
				'postId'     => $post_id,
			)
		);

		// Add delete confirmation for notes with usage.
		$usage_count   = $post_id ? (int) get_post_meta( $post_id, 'usage_count', true ) : 0;
		$is_reusable   = $post_id ? get_post_meta( $post_id, 'is_reusable', true ) : false;
		$used_in_posts = $post_id ? get_post_meta( $post_id, 'used_in_posts', true ) : array();
		$post_count    = is_array( $used_in_posts ) ? count( $used_in_posts ) : 0;

		if ( $usage_count > 0 && $post_count > 0 ) {
			if ( $is_reusable ) {
				// Confirm deletion for reusable notes with usage.
				wp_add_inline_script(
					'jooplaan-inline-context-cpt-editor',
					sprintf(
						'
						(function() {
							var usageCount = %d;
							var postCount = %d;
							var deleteLink = document.querySelector("a.submitdelete");
							if (deleteLink && usageCount > 0) {
								deleteLink.addEventListener("click", function(e) {
									var message = "Are you sure you want to delete this reusable note?\\n\\n" +
										usageCount + " note " + (usageCount === 1 ? "use" : "uses") + " will be deleted in " +
										postCount + " post" + (postCount === 1 ? "" : "s") + ".";

									if (!confirm(message)) {
										e.preventDefault();
										return false;
									}
								});
							}
						})();
						',
						$usage_count,
						$post_count
					)
				);
			} else {
				// Warn but allow deletion for non-reusable notes.
				wp_add_inline_script(
					'jooplaan-inline-context-cpt-editor',
					sprintf(
						'
						(function() {
							var usageCount = %d;
							var postCount = %d;
							var deleteLink = document.querySelector("a.submitdelete");
							if (deleteLink && usageCount > 0) {
								deleteLink.addEventListener("click", function(e) {
									var message = "This note is currently used " + usageCount + " " + (usageCount === 1 ? "time" : "times") +
										" in " + postCount + " post" + (postCount === 1 ? "" : "s") + ".\\n\\n" +
										"Deleting it will also remove it from " + (postCount === 1 ? "that post" : "those posts") + ".\\n\\nContinue?";

									if (!confirm(message)) {
										e.preventDefault();
										return false;
									}
								});
							}
						})();
						',
						$usage_count,
						$post_count
					)
				);
			}
		}
	}

	/**
	 * Add delete warnings to CPT list view.
	 */
	public function add_delete_warnings() {
		$screen = get_current_screen();
		if ( ! $screen || 'edit-inline_context_note' !== $screen->id ) {
			return;
		}

		// Get all notes with their usage counts for JavaScript.
		global $wp_query;
		$note_usage      = array();
		$note_post_count = array();
		$reusable_in_use = array();

		if ( isset( $wp_query->posts ) && is_array( $wp_query->posts ) ) {
			foreach ( $wp_query->posts as $post ) {
				$usage_count   = (int) get_post_meta( $post->ID, 'usage_count', true );
				$is_reusable   = get_post_meta( $post->ID, 'is_reusable', true );
				$used_in_posts = get_post_meta( $post->ID, 'used_in_posts', true );
				$post_count    = is_array( $used_in_posts ) ? count( $used_in_posts ) : 0;

				if ( $usage_count > 0 ) {
					$note_usage[ $post->ID ]      = $usage_count;
					$note_post_count[ $post->ID ] = $post_count;

					// Track if it's reusable and in use (cannot be deleted).
					if ( $is_reusable ) {
						$reusable_in_use[ $post->ID ] = true;
					}
				}
			}
		}

		if ( ! empty( $note_usage ) ) :
			?>
			<script>
			jQuery(document).ready(function($) {
				var noteUsage = <?php echo wp_json_encode( $note_usage ); ?>;
				var notePostCount = <?php echo wp_json_encode( $note_post_count ); ?>;
				var reusableInUse = <?php echo wp_json_encode( $reusable_in_use ); ?>;

				// Confirm bulk delete.
				$('#doaction, #doaction2').on('click', function(e) {
					var form = $(this).closest('form');
					var action = form.find('select[name="action"]').val();
					if (!action || action === '-1') {
						action = form.find('select[name="action2"]').val();
					}

					if (action === 'trash') {
						var checkedPosts = form.find('input[name="post[]"]:checked');
						var totalNotesCount = 0;
						var totalUsageCount = 0;
						var totalPostsAffected = 0;

						checkedPosts.each(function() {
							var postId = $(this).val();
							if (noteUsage[postId] && reusableInUse[postId]) {
								totalNotesCount++;
								totalUsageCount += noteUsage[postId];
								totalPostsAffected += notePostCount[postId];
							}
						});

						// Show confirmation for reusable notes that are in use
						if (totalNotesCount > 0 && totalPostsAffected > 0) {
							var message = 'Are you sure you want to delete ' +
								(totalNotesCount === 1 ? 'this reusable note' : 'these ' + totalNotesCount + ' reusable notes') +
								'?\n\n' + totalUsageCount + ' note ' + (totalUsageCount === 1 ? 'use' : 'uses') +
								' will be deleted in ' + totalPostsAffected + ' post' +
								(totalPostsAffected === 1 ? '' : 's') + '.';

							if (!confirm(message)) {
								e.preventDefault();
								return false;
							}
						}
					}
				});

				// Confirm individual delete (trash link in row actions).
				$('span.trash a').on('click', function(e) {
					var link = $(this);
					var row = link.closest('tr');
					var checkbox = row.find('input[name="post[]"]');

					if (checkbox.length) {
						var postId = checkbox.val();

						// Show confirmation for reusable notes that are in use.
						if (reusableInUse[postId]) {
							var usage = noteUsage[postId];
							var postCount = notePostCount[postId];
							var message = 'Are you sure you want to delete this reusable note?\n\n' +
								usage + ' note ' + (usage === 1 ? 'use' : 'uses') +
								' will be deleted in ' + postCount + ' post' + (postCount === 1 ? '' : 's') + '.';

							if (!confirm(message)) {
								e.preventDefault();
								return false;
							}
						}

						// Warn about non-reusable notes that are in use (but allow deletion).
						if (noteUsage[postId] && !reusableInUse[postId]) {
							var usage = noteUsage[postId];
							var postCount = notePostCount[postId];
							var message = 'This note is currently used ' + usage + ' ' + (usage === 1 ? 'time' : 'times') +
								' in ' + postCount + ' post' + (postCount === 1 ? '' : 's') + '.\n\n' +
								'Deleting it will also remove it from ' + (postCount === 1 ? 'that post' : 'those posts') + '.\n\nContinue?';
							if (!confirm(message)) {
								e.preventDefault();
								return false;
							}
						}
					}
				});
			});
			</script>
			<?php
		endif;
	}

	/**
	 * Schedule the cleanup cron job.
	 *
	 * Runs daily to clean up unused non-reusable notes.
	 */
	public function schedule_cleanup_cron() {
		if ( ! wp_next_scheduled( 'inline_context_cleanup_unused_notes' ) ) {
			wp_schedule_event( time(), 'daily', 'inline_context_cleanup_unused_notes' );
		}
	}

	/**
	 * Unschedule the cleanup cron job on plugin deactivation.
	 */
	public function unschedule_cleanup_cron() {
		$timestamp = wp_next_scheduled( 'inline_context_cleanup_unused_notes' );
		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, 'inline_context_cleanup_unused_notes' );
		}
	}

	/**
	 * Clean up unused non-reusable notes.
	 *
	 * Deletes notes that are:
	 * - Not marked as reusable (is_reusable != 1 or not set)
	 * - Have zero usage count (usage_count = 0 or not set)
	 */
	public function cleanup_unused_notes() {
		// Find notes that are not reusable and have zero usage.
		// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Necessary for cleanup, runs once daily via cron.
		$unused_notes = get_posts(
			array(
				'post_type'      => 'inline_context_note',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'meta_query'     => array(
					'relation' => 'AND',
					array(
						'relation' => 'OR',
						array(
							'key'     => 'is_reusable',
							'value'   => '1',
							'compare' => '!=',
						),
						array(
							'key'     => 'is_reusable',
							'compare' => 'NOT EXISTS',
						),
					),
					array(
						'relation' => 'OR',
						array(
							'key'     => 'usage_count',
							'value'   => '0',
							'compare' => '=',
						),
						array(
							'key'     => 'usage_count',
							'compare' => 'NOT EXISTS',
						),
					),
				),
			)
		);

		// Delete found notes.
		$deleted_count = 0;
		foreach ( $unused_notes as $note_id ) {
			if ( wp_delete_post( $note_id, true ) ) {
				++$deleted_count;
			}
		}

		// Output results for CLI and logs.
		$message = sprintf( 'Inline Context: Cleaned up %d unused note(s)', $deleted_count );

		// Output to CLI if running in WP-CLI context.
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			\WP_CLI::success( $message );
		}

		// Log cleanup activity.
		if ( $deleted_count > 0 ) {
			error_log( $message ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		}

		return $deleted_count;
	}
}
