<?php
/**
 * Taxonomy meta fields for Inline Context categories.
 *
 * Handles adding icon and color meta fields to the inline_context_category taxonomy.
 *
 * @package InlineContext
 * @since 2.0.1
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Inline_Context_Taxonomy_Meta
 *
 * Manages taxonomy meta fields for categories.
 */
class Inline_Context_Taxonomy_Meta {

	/**
	 * Initialize taxonomy meta functionality.
	 */
	public function init() {
		// Register hooks directly - taxonomy should be registered by now.
		// CPT class registers taxonomy at priority 9, so we hook at default 10.

		// Add form fields for add/edit term screens.
		add_action( 'inline_context_category_add_form_fields', array( $this, 'add_category_fields' ) );
		add_action( 'inline_context_category_edit_form_fields', array( $this, 'edit_category_fields' ), 10, 2 );

		// Save term meta.
		add_action( 'created_inline_context_category', array( $this, 'save_category_meta' ), 10, 2 );
		add_action( 'edited_inline_context_category', array( $this, 'save_category_meta' ), 10, 2 );

		// Add color picker assets.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );

		// Add custom columns to category list.
		add_filter( 'manage_edit-inline_context_category_columns', array( $this, 'add_category_columns' ) );
		add_filter( 'manage_inline_context_category_custom_column', array( $this, 'populate_category_columns' ), 10, 3 );
	}

	/**
	 * Enqueue admin assets for color picker and icon selector.
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function enqueue_admin_assets( $hook ) {
		// Only load on taxonomy edit screens.
		if ( 'edit-tags.php' !== $hook && 'term.php' !== $hook ) {
			return;
		}

		$screen = get_current_screen();
		if ( ! $screen || 'inline_context_category' !== $screen->taxonomy ) {
			return;
		}

		// Enqueue WordPress color picker.
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );

		// Enqueue Dashicons for icon preview.
		wp_enqueue_style( 'dashicons' );

		// Add inline script for color picker initialization.
		wp_add_inline_script(
			'wp-color-picker',
			'jQuery(document).ready(function($) {
				$(".inline-context-color-picker").wpColorPicker();
			});'
		);

		// Add inline script for icon preview functionality.
		$icon_preview_script = "
			jQuery(document).ready(function(\$) {
				// Icon preview for add/edit forms
				function updateIconPreview(inputId, previewId) {
					var input = \$('#' + inputId);
					var preview = \$('#' + previewId);

					input.on('input', function() {
						var iconClass = \$(this).val().trim();
						if (iconClass) {
							preview.attr('class', 'dashicons ' + iconClass);
							preview.css({
								'font-size': '20px',
								'width': '20px',
								'height': '20px',
								'display': 'inline-block'
							});
						} else {
							preview.attr('class', '').text('');
						}
					});

					// Trigger initial update
					input.trigger('input');
				}

				updateIconPreview('icon_closed', 'icon-closed-preview');
				updateIconPreview('icon_open', 'icon-open-preview');
			});
		";

		wp_add_inline_script( 'wp-color-picker', $icon_preview_script );
	}

	/**
	 * Add fields to the "Add New Category" form.
	 */
	public function add_category_fields() {
		wp_nonce_field( 'inline_context_category_meta', 'inline_context_category_meta_nonce' );
		?>
		<div class="form-field term-icon-closed-wrap">
			<label for="icon_closed"><?php esc_html_e( 'Icon (Closed State)', 'inline-context' ); ?></label>
			<input type="text"
				name="icon_closed"
				id="icon_closed"
				value="dashicons-info"
				class="inline-context-icon-field"
				placeholder="dashicons-info">
			<p class="description">
				<?php
				printf(
					/* translators: %s: URL to Dashicons reference */
					wp_kses_post( __( 'Enter a Dashicon class (e.g., <code>dashicons-info</code>). See <a href="%s" target="_blank">Dashicons reference</a>.', 'inline-context' ) ),
					'https://developer.wordpress.org/resource/dashicons/'
				);
				?>
			</p>
			<p class="description">
				<span class="dashicons dashicons-info" style="font-size: 20px; width: 20px; height: 20px;"></span>
				<span id="icon-closed-preview" style="font-size: 20px; width: 20px; height: 20px;"></span>
			</p>
		</div>

		<div class="form-field term-icon-open-wrap">
			<label for="icon_open"><?php esc_html_e( 'Icon (Open State)', 'inline-context' ); ?></label>
			<input type="text"
				name="icon_open"
				id="icon_open"
				value="dashicons-info-outline"
				class="inline-context-icon-field"
				placeholder="dashicons-info-outline">
			<p class="description">
				<?php esc_html_e( 'Icon to display when the note is expanded.', 'inline-context' ); ?>
			</p>
			<p class="description">
				<span class="dashicons dashicons-info-outline" style="font-size: 20px; width: 20px; height: 20px;"></span>
				<span id="icon-open-preview" style="font-size: 20px; width: 20px; height: 20px;"></span>
			</p>
		</div>

		<div class="form-field term-color-wrap">
			<label for="color"><?php esc_html_e( 'Icon Color', 'inline-context' ); ?></label>
			<input type="text"
				name="color"
				id="color"
				value="#0073aa"
				class="inline-context-color-picker">
			<p class="description">
				<?php esc_html_e( 'Color for the category icon.', 'inline-context' ); ?>
			</p>
		</div>
		<?php
	}

	/**
	 * Add fields to the "Edit Category" form.
	 *
	 * @param WP_Term $term Current taxonomy term object.
	 * @param string  $taxonomy Current taxonomy slug.
	 */
	public function edit_category_fields( $term, $taxonomy ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		$icon_closed = get_term_meta( $term->term_id, 'icon_closed', true );
		$icon_open   = get_term_meta( $term->term_id, 'icon_open', true );
		$color       = get_term_meta( $term->term_id, 'color', true );

		// Set defaults if empty.
		$icon_closed = $icon_closed ? $icon_closed : 'dashicons-info';
		$icon_open   = $icon_open ? $icon_open : 'dashicons-info-outline';
		$color       = $color ? $color : '#0073aa';

		wp_nonce_field( 'inline_context_category_meta', 'inline_context_category_meta_nonce' );
		?>
		<tr class="form-field term-icon-closed-wrap">
			<th scope="row">
				<label for="icon_closed"><?php esc_html_e( 'Icon (Closed State)', 'inline-context' ); ?></label>
			</th>
			<td>
				<input type="text"
					name="icon_closed"
					id="icon_closed"
					value="<?php echo esc_attr( $icon_closed ); ?>"
					class="regular-text inline-context-icon-field">
				<p class="description">
					<?php
					printf(
						/* translators: %s: URL to Dashicons reference */
						wp_kses_post( __( 'Enter a Dashicon class (e.g., <code>dashicons-info</code>). See <a href="%s" target="_blank">Dashicons reference</a>.', 'inline-context' ) ),
						'https://developer.wordpress.org/resource/dashicons/'
					);
					?>
				</p>
				<p class="description">
					<span class="dashicons" id="icon-closed-preview" style="font-size: 24px; width: 24px; height: 24px;"></span>
				</p>
			</td>
		</tr>

		<tr class="form-field term-icon-open-wrap">
			<th scope="row">
				<label for="icon_open"><?php esc_html_e( 'Icon (Open State)', 'inline-context' ); ?></label>
			</th>
			<td>
				<input type="text"
					name="icon_open"
					id="icon_open"
					value="<?php echo esc_attr( $icon_open ); ?>"
					class="regular-text inline-context-icon-field">
				<p class="description">
					<?php esc_html_e( 'Icon to display when the note is expanded.', 'inline-context' ); ?>
				</p>
				<p class="description">
					<span class="dashicons" id="icon-open-preview" style="font-size: 24px; width: 24px; height: 24px;"></span>
				</p>
			</td>
		</tr>

		<tr class="form-field term-color-wrap">
			<th scope="row">
				<label for="color"><?php esc_html_e( 'Icon Color', 'inline-context' ); ?></label>
			</th>
			<td>
				<input type="text"
					name="color"
					id="color"
					value="<?php echo esc_attr( $color ); ?>"
					class="inline-context-color-picker">
				<p class="description">
					<?php esc_html_e( 'Color for the category icon.', 'inline-context' ); ?>
				</p>
			</td>
		</tr>
		<?php
	}

	/**
	 * Save category meta fields.
	 *
	 * @param int $term_id  Term ID.
	 * @param int $tt_id    Term taxonomy ID.
	 */
	public function save_category_meta( $term_id, $tt_id ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		// Verify nonce.
		if ( ! isset( $_POST['inline_context_category_meta_nonce'] ) ||
			! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['inline_context_category_meta_nonce'] ) ), 'inline_context_category_meta' ) ) {
			return;
		}

		// Save icon_closed.
		if ( isset( $_POST['icon_closed'] ) ) {
			$icon_closed = sanitize_text_field( wp_unslash( $_POST['icon_closed'] ) );
			update_term_meta( $term_id, 'icon_closed', $icon_closed );
		}

		// Save icon_open.
		if ( isset( $_POST['icon_open'] ) ) {
			$icon_open = sanitize_text_field( wp_unslash( $_POST['icon_open'] ) );
			update_term_meta( $term_id, 'icon_open', $icon_open );
		}

		// Save color.
		if ( isset( $_POST['color'] ) ) {
			$color = sanitize_hex_color( wp_unslash( $_POST['color'] ) );
			if ( $color ) {
				update_term_meta( $term_id, 'color', $color );
			}
		}
	}

	/**
	 * Add custom columns to category list table.
	 *
	 * @param array $columns Existing columns.
	 * @return array Modified columns.
	 */
	public function add_category_columns( $columns ) {
		// Add after name column.
		$new_columns = array();
		foreach ( $columns as $key => $value ) {
			$new_columns[ $key ] = $value;
			if ( 'name' === $key ) {
				$new_columns['icon_preview'] = __( 'Icons', 'inline-context' );
				$new_columns['color']        = __( 'Color', 'inline-context' );
			}
		}
		return $new_columns;
	}

	/**
	 * Populate custom columns in category list table.
	 *
	 * @param string $content     Column content.
	 * @param string $column_name Column name.
	 * @param int    $term_id     Term ID.
	 * @return string Modified column content.
	 */
	public function populate_category_columns( $content, $column_name, $term_id ) {
		switch ( $column_name ) {
			case 'icon_preview':
				$icon_closed = get_term_meta( $term_id, 'icon_closed', true );
				$icon_open   = get_term_meta( $term_id, 'icon_open', true );
				$color       = get_term_meta( $term_id, 'color', true );

				if ( $icon_closed || $icon_open ) {
					$icon_closed = $icon_closed ? $icon_closed : 'dashicons-info';
					$icon_open   = $icon_open ? $icon_open : 'dashicons-info-outline';
					$color       = $color ? $color : '#0073aa';

					$content = sprintf(
						'<span class="dashicons %s" style="color: %s; font-size: 20px;"></span> / <span class="dashicons %s" style="color: %s; font-size: 20px;"></span>',
						esc_attr( $icon_closed ),
						esc_attr( $color ),
						esc_attr( $icon_open ),
						esc_attr( $color )
					);
				} else {
					$content = '<span style="color: #999;">—</span>';
				}
				break;

			case 'color':
				$color = get_term_meta( $term_id, 'color', true );
				if ( $color ) {
					$content = sprintf(
						'<span style="display: inline-block; width: 20px; height: 20px; background-color: %s; border: 1px solid #ddd; border-radius: 3px; vertical-align: middle;"></span> <code>%s</code>',
						esc_attr( $color ),
						esc_html( $color )
					);
				} else {
					$content = '<span style="color: #999;">—</span>';
				}
				break;
		}

		return $content;
	}
}
