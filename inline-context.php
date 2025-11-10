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

/**
 * Register Custom Post Type for Notes
 */
add_action(
	'init',
	function () {
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
				'supports'        => array( 'title', 'editor', 'revisions' ), // Need 'editor' for REST API content field.
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
	},
	9 // Run before default init priority to ensure translations are loaded.
);

/**
 * Add custom columns to the Notes CPT list
 */
add_filter(
	'manage_inline_context_note_posts_columns',
	function ( $columns ) {
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
);

/**
 * Populate custom columns in the Notes CPT list
 */
add_action(
	'manage_inline_context_note_posts_custom_column',
	function ( $column, $post_id ) {
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
					foreach ( array_slice( $used_in, 0, 3 ) as $used_post_id ) {
						$post = get_post( $used_post_id );
						if ( $post ) {
							$post_links[] = sprintf(
								'<a href="%s">%s</a>',
								esc_url( get_edit_post_link( $used_post_id ) ),
								esc_html( get_the_title( $used_post_id ) )
							);
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
	},
	10,
	2
);

/**
 * Make custom columns sortable
 */
add_filter(
	'manage_edit-inline_context_note_sortable_columns',
	function ( $columns ) {
		$columns['usage_count'] = 'usage_count';
		return $columns;
	}
);

/**
 * Handle custom column sorting
 */
add_action(
	'pre_get_posts',
	function ( $query ) {
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
);

/**
 * Add filter dropdown for reusable notes
 */
add_action(
	'restrict_manage_posts',
	function ( $post_type ) {
		if ( 'inline_context_note' !== $post_type ) {
			return;
		}

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
);

/**
 * Filter posts by reusable status
 */
add_filter(
	'parse_query',
	function ( $query ) {
		global $pagenow;

		if ( ! is_admin() || 'edit.php' !== $pagenow || ! isset( $query->query_vars['post_type'] ) || 'inline_context_note' !== $query->query_vars['post_type'] ) {
			return;
		}

		if ( ! isset( $_GET['reusable_filter'] ) || empty( $_GET['reusable_filter'] ) ) {
			return;
		}

		$reusable_filter = sanitize_text_field( wp_unslash( $_GET['reusable_filter'] ) );

		if ( 'reusable' === $reusable_filter ) {
			$query->query_vars['meta_query'] = array(
				array(
					'key'     => 'is_reusable',
					'value'   => '1',
					'compare' => '=',
				),
			);
		} elseif ( 'not_reusable' === $reusable_filter ) {
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
);

/**
 * Add custom meta box for QuillEditor content
 */
add_action(
	'add_meta_boxes',
	function () {
		// Remove the default editor.
		remove_post_type_support( 'inline_context_note', 'editor' );

		add_meta_box(
			'inline_context_note_content',
			__( 'Note Content', 'inline-context' ),
			function ( $post ) {
				// Get the current content.
				$content = $post->post_content;

				// Output the container for React to mount into.
				echo '<div id="inline-context-note-editor" data-post-id="' . esc_attr( $post->ID ) . '">';
				echo '<div id="inline-context-quill-root"></div>';
				echo '</div>';

				// Hidden textarea to store the content (textarea preserves HTML better than input).
				echo '<textarea id="inline-context-note-content" name="inline_context_note_content" style="display:none;">' . esc_textarea( $content ) . '</textarea>';

				wp_nonce_field( 'inline_context_save_note', 'inline_context_note_nonce' );
			},
			'inline_context_note',
			'normal',
			'default'
		);

		// Add custom category selector (radio buttons for single selection).
		add_meta_box(
			'inline_context_category_select',
			__( 'Note Category', 'inline-context' ),
			function ( $post ) {
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

				// Debug: Show what's happening.
				echo '<!-- Current term ID: ' . esc_html( $current_term_id ) . ' -->';
				echo '<!-- POST data will include: inline_context_category_nonce_field and inline_context_category_id -->';

				// "No category" option - checked by default if no category set.
				echo '<label style="display: block; margin-bottom: 8px;">';
				echo '<input type="radio" name="inline_context_category_id" value="0"';
				if ( $current_term_id === 0 ) {
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
			},
			'inline_context_note',
			'side',
			'default'
		);

		// Add usage statistics meta box.
		add_meta_box(
			'inline_context_usage_stats',
			__( 'Usage Statistics', 'inline-context' ),
			function ( $post ) {
				$used_in_posts = get_post_meta( $post->ID, 'used_in_posts', true );
				$is_reusable   = (bool) get_post_meta( $post->ID, 'is_reusable', true );

				if ( ! is_array( $used_in_posts ) ) {
					$used_in_posts = array();
				}

				// Sync usage_count with actual array length (in case they got out of sync).
				$actual_usage_count = count( $used_in_posts );
				$stored_usage_count = (int) get_post_meta( $post->ID, 'usage_count', true );

				if ( $actual_usage_count !== $stored_usage_count ) {
					update_post_meta( $post->ID, 'usage_count', $actual_usage_count );
				}

				$usage_count = $actual_usage_count;

				wp_nonce_field( 'inline_context_usage_meta_nonce', 'inline_context_usage_meta_nonce_field' );

				echo '<div style="padding: 10px 0;">';

				// Usage count.
				echo '<p><strong>' . esc_html__( 'Used in:', 'inline-context' ) . '</strong> ';
				if ( $usage_count > 0 ) {
					echo '<span style="font-size: 1.2em; color: #2271b1;">' . esc_html( $usage_count ) . '</span> ';
					echo esc_html( _n( 'post', 'posts', $usage_count, 'inline-context' ) );
				} else {
					echo '<span style="color: #999;">' . esc_html__( 'Not used yet', 'inline-context' ) . '</span>';
				}
				echo '</p>';

				// List of posts where used.
				if ( ! empty( $used_in_posts ) ) {
					echo '<div style="margin: 15px 0; padding: 10px; background: #f6f7f7; border-left: 3px solid #2271b1;">';
					echo '<p style="margin: 0 0 10px 0; font-weight: 600;">' . esc_html__( 'Used in these posts:', 'inline-context' ) . '</p>';
					echo '<ul style="margin: 0; padding-left: 20px;">';

					foreach ( $used_in_posts as $used_post_id ) {
						$used_post = get_post( $used_post_id );
						if ( $used_post ) {
							$edit_link = get_edit_post_link( $used_post_id );
							$view_link = get_permalink( $used_post_id );

							echo '<li style="margin-bottom: 8px;">';
							echo '<a href="' . esc_url( $edit_link ) . '" style="font-weight: 500;">';
							echo esc_html( get_the_title( $used_post_id ) );
							echo '</a>';
							echo ' <span style="color: #666;">(' . esc_html( get_post_type( $used_post_id ) ) . ')</span>';
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
				if ( $usage_count > 1 ) {
					echo '<div style="margin-top: 15px; padding: 10px; background: #fff3cd; border-left: 3px solid #ffc107;">';
					echo '<p style="margin: 0; font-size: 0.9em;">';
					echo '<strong>⚠️ ' . esc_html__( 'Note:', 'inline-context' ) . '</strong> ';
					echo esc_html__( 'This note is used in multiple posts. Changes to the content will not automatically update existing usages.', 'inline-context' );
					echo '</p>';
					echo '</div>';
				}

				// Delete warning if note is in use.
				if ( $usage_count > 0 ) {
					echo '<div style="margin-top: 15px; padding: 12px; background: #f8d7da; border-left: 3px solid #dc3545;">';
					echo '<p style="margin: 0 0 8px 0; font-weight: 600; color: #721c24;">';
					echo '🗑️ ' . esc_html__( 'Before deleting this note:', 'inline-context' );
					echo '</p>';
					echo '<p style="margin: 0; font-size: 0.9em; color: #721c24;">';
					echo esc_html(
						sprintf(
							/* translators: %d: number of posts */
							_n(
								'This note is currently used in %d post. Deleting it will not remove it from that post, but the note will no longer appear in search results.',
								'This note is currently used in %d posts. Deleting it will not remove it from those posts, but the note will no longer appear in search results.',
								$usage_count,
								'inline-context'
							),
							$usage_count
						)
					);
					echo '</p>';
					echo '</div>';
				}

				echo '</div>';
			},
			'inline_context_note',
			'side',
			'default'
		);
	}
);

/**
 * Enqueue QuillEditor assets for CPT edit screen
 */
add_action(
	'admin_enqueue_scripts',
	function ( $hook ) {
		// Only load on the CPT edit screen.
		$screen = get_current_screen();
		if ( ! $screen || 'inline_context_note' !== $screen->post_type || ( 'post.php' !== $hook && 'post-new.php' !== $hook ) ) {
			return;
		}

		// Enqueue the CPT editor assets.
		$asset_file = include plugin_dir_path( __FILE__ ) . 'build/cpt-editor.asset.php';

		wp_enqueue_script(
			'jooplaan-inline-context-cpt-editor',
			plugins_url( 'build/cpt-editor.js', __FILE__ ),
			$asset_file['dependencies'],
			$asset_file['version'],
			true
		);

		wp_enqueue_style(
			'jooplaan-inline-context-cpt-editor',
			plugins_url( 'build/index.css', __FILE__ ),
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
		$usage_count = $post_id ? (int) get_post_meta( $post_id, 'usage_count', true ) : 0;
		if ( $usage_count > 0 ) {
			wp_add_inline_script(
				'jooplaan-inline-context-cpt-editor',
				sprintf(
					'
					(function() {
						var usageCount = %d;
						var deleteLink = document.querySelector("a.submitdelete");
						if (deleteLink && usageCount > 0) {
							deleteLink.addEventListener("click", function(e) {
								var message = usageCount === 1
									? "This note is currently used in 1 post. Are you sure you want to delete it?\n\nDeleting will not remove it from the post, but the note will no longer appear in search results."
									: "This note is currently used in " + usageCount + " posts. Are you sure you want to delete it?\n\nDeleting will not remove it from those posts, but the note will no longer appear in search results.";

								if (!confirm(message)) {
									e.preventDefault();
									return false;
								}
							});
						}
					})();
					',
					$usage_count
				)
			);
		}
	}
);

/**
 * Add delete warnings to CPT list view
 */
add_action(
	'admin_footer',
	function () {
		$screen = get_current_screen();
		if ( ! $screen || 'edit-inline_context_note' !== $screen->id ) {
			return;
		}

		// Get all notes with their usage counts for JavaScript.
		global $wp_query;
		$note_usage = array();

		if ( isset( $wp_query->posts ) && is_array( $wp_query->posts ) ) {
			foreach ( $wp_query->posts as $post ) {
				$usage_count = (int) get_post_meta( $post->ID, 'usage_count', true );
				if ( $usage_count > 0 ) {
					$note_usage[ $post->ID ] = $usage_count;
				}
			}
		}

		if ( ! empty( $note_usage ) ) :
			?>
			<script>
			jQuery(document).ready(function($) {
				var noteUsage = <?php echo wp_json_encode( $note_usage ); ?>;

				// Confirm bulk delete
				$('#doaction, #doaction2').on('click', function(e) {
					var form = $(this).closest('form');
					var action = form.find('select[name="action"]').val();
					if (!action || action === '-1') {
						action = form.find('select[name="action2"]').val();
					}

					if (action === 'trash') {
						var checkedPosts = form.find('input[name="post[]"]:checked');
						var inUseCount = 0;
						var totalCount = checkedPosts.length;

						checkedPosts.each(function() {
							var postId = $(this).val();
							if (noteUsage[postId] && noteUsage[postId] > 0) {
								inUseCount++;
							}
						});

						if (totalCount > 0 && inUseCount > 0) {
							var message = 'You are about to delete ' + totalCount + ' note(s).\n\n';
							message += inUseCount + ' of them ' + (inUseCount === 1 ? 'is' : 'are') + ' currently in use in posts.\n\n';
							message += 'Deleting will not remove them from posts, but they will no longer appear in search results.\n\nContinue?';
							if (!confirm(message)) {
								e.preventDefault();
								return false;
							}
						}
					}
				});

				// Confirm individual delete (trash link in row actions)
				$('span.trash a').on('click', function(e) {
					var link = $(this);
					var row = link.closest('tr');
					var checkbox = row.find('input[name="post[]"]');

					if (checkbox.length) {
						var postId = checkbox.val();
						var usage = noteUsage[postId];

						if (usage && usage > 0) {
							var message = 'This note is currently used in ' + usage + ' post(s).\n\n';
							message += 'Deleting will not remove it from those posts, but the note will no longer appear in search results.\n\nContinue?';
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
);

/**
 * Save the QuillEditor content when the CPT is saved
 */
add_action(
	'save_post_inline_context_note',
	function ( $post_id ) {
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
				false, // Don't trigger wp_error
				false  // Don't fire hooks
			);
		}

		// Save the category (verify separate nonce).
		if ( isset( $_POST['inline_context_category_nonce_field'] ) &&
			wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['inline_context_category_nonce_field'] ) ), 'inline_context_category_nonce' ) &&
			isset( $_POST['inline_context_category_id'] ) ) {

			$category_id = intval( $_POST['inline_context_category_id'] );

			if ( $category_id > 0 ) {
				// Set single category.
				wp_set_post_terms( $post_id, array( $category_id ), 'inline_context_category', false );
			} else {
				// Remove all categories.
				wp_set_post_terms( $post_id, array(), 'inline_context_category', false );
			}
		}

		// Save the "is_reusable" flag (verify nonce).
		if ( isset( $_POST['inline_context_usage_meta_nonce_field'] ) &&
			wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['inline_context_usage_meta_nonce_field'] ) ), 'inline_context_usage_meta_nonce' ) ) {

			$is_reusable = isset( $_POST['inline_context_is_reusable'] ) ? true : false;
			update_post_meta( $post_id, 'is_reusable', $is_reusable );
		}

		$is_saving = false;
	},
	10,
	1
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
					return array_map( 'absint', $value );
				},
				'show_in_rest'      => array(
					'schema' => array(
						'type'  => 'array',
						'items' => array(
							'type' => 'integer',
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

					// Get current usage.
					$used_in_posts = get_post_meta( $note_id, 'used_in_posts', true );
					if ( ! is_array( $used_in_posts ) ) {
						$used_in_posts = array();
					}

					// Add post if not already tracked.
					if ( ! in_array( $post_id, $used_in_posts, true ) ) {
						$used_in_posts[] = $post_id;
						update_post_meta( $note_id, 'used_in_posts', $used_in_posts );
						update_post_meta( $note_id, 'usage_count', count( $used_in_posts ) );
					}

					return new WP_REST_Response(
						array(
							'success'       => true,
							'used_in_posts' => $used_in_posts,
							'usage_count'   => count( $used_in_posts ),
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
