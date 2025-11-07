<?php
/**
 * Admin Settings Page for Inline Context
 *
 * @package InlineContext
 */

defined( 'ABSPATH' ) || exit;

/**
 * Get default categories
 */
function inline_context_get_default_categories() {
	return array(
		'internal-article' => array(
			'id'          => 'internal-article',
			'name'        => __( 'Internal Article', 'inline-context' ),
			'icon_closed' => 'dashicons-admin-links',
			'icon_open'   => 'dashicons-admin-links',
			'color'       => '#2271b1',
		),
		'external-article' => array(
			'id'          => 'external-article',
			'name'        => __( 'External Article', 'inline-context' ),
			'icon_closed' => 'dashicons-external',
			'icon_open'   => 'dashicons-external',
			'color'       => '#d63638',
		),
		'definition'       => array(
			'id'          => 'definition',
			'name'        => __( 'Definition', 'inline-context' ),
			'icon_closed' => 'dashicons-book',
			'icon_open'   => 'dashicons-book-alt',
			'color'       => '#00a32a',
		),
		'tip'              => array(
			'id'          => 'tip',
			'name'        => __( 'Tip', 'inline-context' ),
			'icon_closed' => 'dashicons-lightbulb',
			'icon_open'   => 'dashicons-lightbulb',
			'color'       => '#dba617',
		),
	);
}

/**
 * Get categories (default or custom)
 */
function inline_context_get_categories() {
	$categories = get_option( 'inline_context_categories', inline_context_get_default_categories() );
	return is_array( $categories ) ? $categories : inline_context_get_default_categories();
}

/**
 * Get default CSS variables
 */
function inline_context_get_default_css_variables() {
	return array(
		'link-scroll-margin'      => '80px',
		'link-hover-color'        => '#2271b1',
		'link-focus-color'        => '#2271b1',
		'link-focus-border-color' => '#2271b1',
		'link-open-color'         => '#2271b1',
		'note-margin-y'           => '8px',
		'note-padding-y'          => '12px',
		'note-padding-x'          => '16px',
		'note-background'         => '#f9f9f9',
		'note-border-color'       => '#e0e0e0',
		'note-accent-width'       => '4px',
		'note-accent-color'       => '#2271b1',
		'note-radius'             => '4px',
		'note-shadow'             => '0 2px 4px rgba(0,0,0,0.1)',
		'note-font-size'          => '0.95em',
		'note-link-color'         => '#2271b1',
		'note-link-underline'     => 'underline',
		'chevron-default-color'   => '#666',
		'chevron-hover-color'     => '#2271b1',
		'chevron-size'            => '0.7em',
		'chevron-margin-left'     => '0.25em',
		'chevron-opacity'         => '0.7',
		'chevron-hover-opacity'   => '1',
	);
}

/**
 * Output custom CSS variables
 */
function inline_context_output_custom_css() {
	$options = get_option( 'inline_context_css_variables', inline_context_get_default_css_variables() );

	$css = ':root {';
	foreach ( $options as $key => $value ) {
		// Convert first hyphen to double hyphen for CSS custom property names.
		// Example: 'note-font-size' becomes 'note--font-size' (not 'note--font--size').
		$css_key = preg_replace( '/-/', '--', $key, 1 );
		$css    .= sprintf(
			'--wp--custom--inline-context--%s: %s;',
			$css_key,
			esc_attr( $value )
		);
	}
	$css .= '}';

	printf( '<style id="inline-context-custom-css">%s</style>', wp_kses( $css, array( 'style' => array() ) ) );
}
add_action( 'wp_head', 'inline_context_output_custom_css' );
add_action( 'admin_head', 'inline_context_output_custom_css' );

// Only load admin UI functions in WordPress admin.
if ( ! is_admin() ) {
	return;
}

/**
 * Register settings page
 */
function inline_context_add_settings_page() {
	add_options_page(
		__( 'Inline Context Settings', 'inline-context' ),
		__( 'Inline Context', 'inline-context' ),
		'manage_options',
		'inline-context-settings',
		'inline_context_render_settings_page'
	);
}
add_action( 'admin_menu', 'inline_context_add_settings_page' );

/**
 * Register settings
 */
function inline_context_register_settings() {
	// Register categories setting.
	register_setting(
		'inline_context_categories_settings',
		'inline_context_categories',
		array(
			'type'              => 'array',
			'sanitize_callback' => 'inline_context_sanitize_categories',
			'default'           => inline_context_get_default_categories(),
		)
	);

	// Register CSS variables setting.
	register_setting(
		'inline_context_styling_settings',
		'inline_context_css_variables',
		array(
			'type'              => 'array',
			'sanitize_callback' => 'inline_context_sanitize_css_variables',
			'default'           => inline_context_get_default_css_variables(),
		)
	);

	// Categories tab section.
	add_settings_section(
		'inline_context_categories_section',
		__( 'Note Categories', 'inline-context' ),
		'inline_context_categories_section_callback',
		'inline-context-settings-categories'
	);

	// Styling tab sections.
	add_settings_section(
		'inline_context_link_section',
		__( 'Link Styling', 'inline-context' ),
		'inline_context_link_section_callback',
		'inline-context-settings-styling'
	);

	add_settings_section(
		'inline_context_note_section',
		__( 'Note Styling', 'inline-context' ),
		'inline_context_note_section_callback',
		'inline-context-settings-styling'
	);

	add_settings_section(
		'inline_context_chevron_section',
		__( 'Chevron Styling', 'inline-context' ),
		'inline_context_chevron_section_callback',
		'inline-context-settings-styling'
	);

	// Link settings.
	$link_fields = array(
		'link-scroll-margin'      => array(
			'label'       => __( 'Scroll Margin', 'inline-context' ),
			'default'     => '80px',
			'type'        => 'text',
			'description' => __( 'Space from top of screen when jumping to anchor links. Useful for fixed headers.', 'inline-context' ),
		),
		'link-hover-color'        => array(
			'label'       => __( 'Hover Color', 'inline-context' ),
			'default'     => '#2271b1',
			'type'        => 'color',
			'description' => __( 'Text color when hovering over the link.', 'inline-context' ),
		),
		'link-focus-color'        => array(
			'label'       => __( 'Focus Color', 'inline-context' ),
			'default'     => '#2271b1',
			'type'        => 'color',
			'description' => __( 'Text color when the link is focused (keyboard navigation).', 'inline-context' ),
		),
		'link-focus-border-color' => array(
			'label'       => __( 'Focus Border Color', 'inline-context' ),
			'default'     => '#2271b1',
			'type'        => 'color',
			'description' => __( 'Border color of the focus outline (accessibility feature).', 'inline-context' ),
		),
		'link-open-color'         => array(
			'label'       => __( 'Open State Color', 'inline-context' ),
			'default'     => '#2271b1',
			'type'        => 'color',
			'description' => __( 'Text color when the note is currently revealed.', 'inline-context' ),
		),
	);

	foreach ( $link_fields as $key => $field ) {
		add_settings_field(
			'inline_context_' . $key,
			$field['label'],
			'inline_context_render_field',
			'inline-context-settings-styling',
			'inline_context_link_section',
			array(
				'key'         => $key,
				'type'        => $field['type'],
				'default'     => $field['default'],
				'description' => $field['description'] ?? '',
			)
		);
	}

	// Note settings.
	$note_fields = array(
		'note-margin-y'       => array(
			'label'       => __( 'Vertical Margin', 'inline-context' ),
			'default'     => '8px',
			'type'        => 'text',
			'description' => __( 'Space above and below the note block.', 'inline-context' ),
		),
		'note-padding-y'      => array(
			'label'       => __( 'Vertical Padding', 'inline-context' ),
			'default'     => '12px',
			'type'        => 'text',
			'description' => __( 'Internal spacing at top and bottom of note content.', 'inline-context' ),
		),
		'note-padding-x'      => array(
			'label'       => __( 'Horizontal Padding', 'inline-context' ),
			'default'     => '16px',
			'type'        => 'text',
			'description' => __( 'Internal spacing at left and right of note content.', 'inline-context' ),
		),
		'note-background'     => array(
			'label'       => __( 'Background Color', 'inline-context' ),
			'default'     => '#f9f9f9',
			'type'        => 'color',
			'description' => __( 'Background color of the note block.', 'inline-context' ),
		),
		'note-border-color'   => array(
			'label'       => __( 'Border Color', 'inline-context' ),
			'default'     => '#e0e0e0',
			'type'        => 'color',
			'description' => __( 'Color of the border around the note.', 'inline-context' ),
		),
		'note-accent-width'   => array(
			'label'       => __( 'Accent Width', 'inline-context' ),
			'default'     => '4px',
			'type'        => 'text',
			'description' => __( 'Width of the colored accent bar on the left side.', 'inline-context' ),
		),
		'note-accent-color'   => array(
			'label'       => __( 'Accent Color', 'inline-context' ),
			'default'     => '#2271b1',
			'type'        => 'color',
			'description' => __( 'Color of the accent bar on the left side of the note.', 'inline-context' ),
		),
		'note-radius'         => array(
			'label'       => __( 'Border Radius', 'inline-context' ),
			'default'     => '4px',
			'type'        => 'text',
			'description' => __( 'Roundness of note corners. Use 0px for square corners.', 'inline-context' ),
		),
		'note-shadow'         => array(
			'label'       => __( 'Box Shadow', 'inline-context' ),
			'default'     => '0 2px 4px rgba(0,0,0,0.1)',
			'type'        => 'text',
			'description' => __( 'Drop shadow effect. Use CSS box-shadow format.', 'inline-context' ),
		),
		'note-font-size'      => array(
			'label'       => __( 'Font Size', 'inline-context' ),
			'default'     => '0.95em',
			'type'        => 'text',
			'description' => __( 'Text size within the note. Use em or px units.', 'inline-context' ),
		),
		'note-link-color'     => array(
			'label'       => __( 'Link Color', 'inline-context' ),
			'default'     => '#2271b1',
			'type'        => 'color',
			'description' => __( 'Color of links within the note content.', 'inline-context' ),
		),
		'note-link-underline' => array(
			'label'       => __( 'Link Underline', 'inline-context' ),
			'default'     => 'underline',
			'type'        => 'select',
			'options'     => array(
				'none'      => 'None',
				'underline' => 'Underline',
			),
			'description' => __( 'Whether links in notes should be underlined.', 'inline-context' ),
		),
	);

	foreach ( $note_fields as $key => $field ) {
		add_settings_field(
			'inline_context_' . $key,
			$field['label'],
			'inline_context_render_field',
			'inline-context-settings-styling',
			'inline_context_note_section',
			array(
				'key'         => $key,
				'type'        => $field['type'],
				'default'     => $field['default'],
				'options'     => $field['options'] ?? array(),
				'description' => $field['description'] ?? '',
			)
		);
	}

	// Chevron settings.
	$chevron_fields = array(
		'chevron-default-color' => array(
			'label'       => __( 'Default Color', 'inline-context' ),
			'default'     => '#666',
			'type'        => 'color',
			'description' => __( 'Color of the chevron in its normal state.', 'inline-context' ),
		),
		'chevron-hover-color'   => array(
			'label'       => __( 'Hover Color', 'inline-context' ),
			'default'     => '#2271b1',
			'type'        => 'color',
			'description' => __( 'Color of the chevron when hovering over the link.', 'inline-context' ),
		),
		'chevron-size'          => array(
			'label'       => __( 'Size', 'inline-context' ),
			'default'     => '0.7em',
			'type'        => 'text',
			'description' => __( 'Size of the chevron icon relative to text.', 'inline-context' ),
		),
		'chevron-margin-left'   => array(
			'label'       => __( 'Left Margin', 'inline-context' ),
			'default'     => '0.25em',
			'type'        => 'text',
			'description' => __( 'Space between the link text and the chevron.', 'inline-context' ),
		),
		'chevron-opacity'       => array(
			'label'       => __( 'Opacity', 'inline-context' ),
			'default'     => '0.7',
			'type'        => 'text',
			'description' => __( 'Transparency in normal state (0 = invisible, 1 = solid).', 'inline-context' ),
		),
		'chevron-hover-opacity' => array(
			'label'       => __( 'Hover Opacity', 'inline-context' ),
			'default'     => '1',
			'type'        => 'text',
			'description' => __( 'Transparency when hovering (0 = invisible, 1 = solid).', 'inline-context' ),
		),
	);

	foreach ( $chevron_fields as $key => $field ) {
		add_settings_field(
			'inline_context_' . $key,
			$field['label'],
			'inline_context_render_field',
			'inline-context-settings-styling',
			'inline_context_chevron_section',
			array(
				'key'         => $key,
				'type'        => $field['type'],
				'default'     => $field['default'],
				'description' => $field['description'] ?? '',
			)
		);
	}
}
add_action( 'admin_init', 'inline_context_register_settings' );

/**
 * Sanitize CSS variables
 *
 * @param array $input The input array of CSS variables to sanitize.
 * @return array The sanitized CSS variables.
 */
function inline_context_sanitize_css_variables( $input ) {
	$sanitized = array();
	$defaults  = inline_context_get_default_css_variables();

	foreach ( $defaults as $key => $default ) {
		if ( isset( $input[ $key ] ) ) {
			$sanitized[ $key ] = sanitize_text_field( $input[ $key ] );
		} else {
			$sanitized[ $key ] = $default;
		}
	}

	return $sanitized;
}

/**
 * Sanitize categories
 *
 * @param array $input The input array of categories to sanitize.
 * @return array The sanitized categories.
 */
function inline_context_sanitize_categories( $input ) {
	if ( ! is_array( $input ) ) {
		return inline_context_get_default_categories();
	}

	$sanitized = array();

	foreach ( $input as $id => $category ) {
		if ( ! is_array( $category ) ) {
			continue;
		}

		$sanitized_id               = sanitize_key( $id );
		$sanitized[ $sanitized_id ] = array(
			'id'          => $sanitized_id,
			'name'        => isset( $category['name'] ) ? sanitize_text_field( $category['name'] ) : '',
			'icon_closed' => isset( $category['icon_closed'] ) ? sanitize_text_field( $category['icon_closed'] ) : 'dashicons-admin-links',
			'icon_open'   => isset( $category['icon_open'] ) ? sanitize_text_field( $category['icon_open'] ) : 'dashicons-admin-links',
			'color'       => isset( $category['color'] ) ? sanitize_hex_color( $category['color'] ) : '#2271b1',
		);
	}

	return ! empty( $sanitized ) ? $sanitized : inline_context_get_default_categories();
}

/**
 * Section callbacks
 */

/**
 * Categories section callback
 */
function inline_context_categories_section_callback() {
	?>
	<p><?php esc_html_e( 'Define categories for your inline context notes. Each category can have distinct icons for closed and open states.', 'inline-context' ); ?></p>
	<div class="notice notice-info inline">
		<p>
			<strong><?php esc_html_e( 'How to use icons:', 'inline-context' ); ?></strong><br>
			<?php
			printf(
				/* translators: 1: Opening link tag to Dashicons, 2: Closing link tag */
				esc_html__( 'Use WordPress %1$sDashicons%2$s class names (e.g., %3$s, %4$s, %5$s). Click the icon button next to each field to browse 30 commonly used icons, or type any dashicon class name directly for access to all 300+ icons.', 'inline-context' ),
				'<a href="https://developer.wordpress.org/resource/dashicons/" target="_blank" rel="noopener noreferrer">',
				'</a>',
				'<code>dashicons-book</code>',
				'<code>dashicons-external</code>',
				'<code>dashicons-lightbulb</code>'
			);
			?>
		</p>
		<p>
			<strong><?php esc_html_e( 'Icon picker shows:', 'inline-context' ); ?></strong>
			<?php esc_html_e( 'Links, books, lightbulb, info, warning, help, location, flag, documents, portfolio, analytics, charts, stars, sticky, edit, money, calendar, arrows, download, share, tag, and category icons.', 'inline-context' ); ?>
		</p>
	</div>
	<div id="inline-context-categories-editor">
		<?php inline_context_render_categories_editor(); ?>
	</div>
	<?php
}

/**
 * Render categories editor
 */
function inline_context_render_categories_editor() {
	$categories = inline_context_get_categories();

	// Enqueue Dashicons for icon preview.
	wp_enqueue_style( 'dashicons' );

	// Common Dashicons for inline context use.
	$common_icons = array(
		'dashicons-admin-links',
		'dashicons-external',
		'dashicons-book',
		'dashicons-book-alt',
		'dashicons-lightbulb',
		'dashicons-info',
		'dashicons-warning',
		'dashicons-editor-help',
		'dashicons-location',
		'dashicons-location-alt',
		'dashicons-flag',
		'dashicons-media-document',
		'dashicons-media-text',
		'dashicons-portfolio',
		'dashicons-analytics',
		'dashicons-chart-area',
		'dashicons-chart-bar',
		'dashicons-star-filled',
		'dashicons-star-empty',
		'dashicons-sticky',
		'dashicons-edit',
		'dashicons-money',
		'dashicons-calendar',
		'dashicons-arrow-right-alt',
		'dashicons-arrow-down-alt',
		'dashicons-download',
		'dashicons-share',
		'dashicons-tag',
		'dashicons-category',
	);
	?>
	<style>
		.category-item {
			background: #fff;
			border: 1px solid #ccd0d4;
			padding: 15px;
			margin-bottom: 10px;
			border-radius: 4px;
		}
		.category-item h4 {
			margin-top: 0;
			display: flex;
			align-items: center;
			gap: 10px;
		}
		.category-fields {
			display: grid;
			grid-template-columns: 2fr 2fr 2fr 1fr;
			gap: 15px;
			margin: 10px 0;
		}
		.category-field label {
			display: block;
			margin-bottom: 5px;
			font-weight: 600;
		}
		.category-field input[type="text"],
		.category-field input[type="color"] {
			width: 100%;
		}
		.category-icon-preview {
			width: 20px;
			height: 20px;
		}
		.category-actions {
			margin-top: 10px;
		}
		.icon-field-wrapper {
			position: relative;
		}
		.icon-picker-button {
			margin-top: 5px;
			display: inline-flex;
			align-items: center;
			gap: 5px;
		}
		.icon-picker-modal {
			display: none;
			position: fixed;
			z-index: 100000;
			left: 0;
			top: 0;
			width: 100%;
			height: 100%;
			background-color: rgba(0,0,0,0.5);
		}
		.icon-picker-modal.active {
			display: flex;
			align-items: center;
			justify-content: center;
		}
		.icon-picker-content {
			background-color: #fff;
			padding: 20px;
			border-radius: 8px;
			max-width: 600px;
			max-height: 80vh;
			overflow-y: auto;
			box-shadow: 0 5px 15px rgba(0,0,0,0.3);
		}
		.icon-picker-header {
			display: flex;
			justify-content: space-between;
			align-items: center;
			margin-bottom: 15px;
			padding-bottom: 10px;
			border-bottom: 1px solid #ddd;
		}
		.icon-picker-grid {
			display: grid;
			grid-template-columns: repeat(8, 1fr);
			gap: 10px;
		}
		.icon-picker-item {
			display: flex;
			align-items: center;
			justify-content: center;
			padding: 10px;
			border: 2px solid #ddd;
			border-radius: 4px;
			cursor: pointer;
			transition: all 0.2s;
		}
		.icon-picker-item:hover {
			border-color: #2271b1;
			background-color: #f0f6fc;
		}
		.icon-picker-item .dashicons {
			font-size: 20px;
		}
		.add-category-btn {
			margin-top: 15px;
		}
	</style>

	<div class="categories-list">
		<?php foreach ( $categories as $id => $category ) : ?>
			<div class="category-item" data-category-id="<?php echo esc_attr( $id ); ?>">
				<h4>
					<span class="dashicons <?php echo esc_attr( $category['icon_closed'] ); ?> category-icon-preview" style="color: <?php echo esc_attr( $category['color'] ); ?>;"></span>
					<?php echo esc_html( $category['name'] ); ?>
				</h4>
				<div class="category-fields">
					<div class="category-field">
						<label><?php esc_html_e( 'Category Name', 'inline-context' ); ?></label>
						<input type="text" 
							name="inline_context_categories[<?php echo esc_attr( $id ); ?>][name]" 
							value="<?php echo esc_attr( $category['name'] ); ?>" 
							required />
					</div>
					<div class="category-field">
						<label><?php esc_html_e( 'Icon (Closed)', 'inline-context' ); ?></label>
						<div class="icon-field-wrapper">
							<input type="text"
								class="icon-input"
								name="inline_context_categories[<?php echo esc_attr( $id ); ?>][icon_closed]"
								value="<?php echo esc_attr( $category['icon_closed'] ); ?>"
								placeholder="dashicons-admin-links" />
							<button type="button" class="button button-small icon-picker-button" data-target="icon_closed_<?php echo esc_attr( $id ); ?>">
								<span class="dashicons dashicons-art"></span>
								<?php esc_html_e( 'Choose Icon', 'inline-context' ); ?>
							</button>
						</div>
					</div>
					<div class="category-field">
						<label><?php esc_html_e( 'Icon (Open)', 'inline-context' ); ?></label>
						<div class="icon-field-wrapper">
							<input type="text"
								class="icon-input"
								name="inline_context_categories[<?php echo esc_attr( $id ); ?>][icon_open]"
								value="<?php echo esc_attr( $category['icon_open'] ); ?>"
								placeholder="dashicons-book-alt" />
							<button type="button" class="button button-small icon-picker-button" data-target="icon_open_<?php echo esc_attr( $id ); ?>">
								<span class="dashicons dashicons-art"></span>
								<?php esc_html_e( 'Choose Icon', 'inline-context' ); ?>
							</button>
						</div>
					</div>
					<div class="category-field">
						<label><?php esc_html_e( 'Color', 'inline-context' ); ?></label>
						<input type="color" 
							name="inline_context_categories[<?php echo esc_attr( $id ); ?>][color]" 
							value="<?php echo esc_attr( $category['color'] ); ?>" />
					</div>
				</div>
				<input type="hidden" 
					name="inline_context_categories[<?php echo esc_attr( $id ); ?>][id]" 
					value="<?php echo esc_attr( $id ); ?>" />
				<div class="category-actions">
					<button type="button" class="button button-secondary button-small delete-category" data-category-id="<?php echo esc_attr( $id ); ?>">
						<?php esc_html_e( 'Delete Category', 'inline-context' ); ?>
					</button>
				</div>
			</div>
		<?php endforeach; ?>
	</div>

	<button type="button" class="button button-secondary add-category-btn" id="add-new-category">
		<?php esc_html_e( '+ Add New Category', 'inline-context' ); ?>
	</button>

	<!-- Icon Picker Modal -->
	<div id="icon-picker-modal" class="icon-picker-modal" role="dialog" aria-modal="true" aria-labelledby="icon-picker-title">
		<div class="icon-picker-content">
			<div class="icon-picker-header">
				<h3 id="icon-picker-title"><?php esc_html_e( 'Choose an Icon', 'inline-context' ); ?></h3>
				<button type="button" class="button" id="close-icon-picker" aria-label="<?php esc_attr_e( 'Close icon picker', 'inline-context' ); ?>">
					<span class="dashicons dashicons-no-alt" aria-hidden="true"></span>
				</button>
			</div>
			<div class="icon-picker-grid" role="list">
				<?php foreach ( $common_icons as $icon_class ) : ?>
					<button type="button" class="icon-picker-item" data-icon="<?php echo esc_attr( $icon_class ); ?>" role="listitem" aria-label="<?php echo esc_attr( str_replace( array( 'dashicons-', '-' ), array( '', ' ' ), $icon_class ) ); ?>">
						<span class="dashicons <?php echo esc_attr( $icon_class ); ?>" aria-hidden="true"></span>
					</button>
				<?php endforeach; ?>
			</div>
		</div>
	</div>

	<script>
		(function() {
			let categoryCounter = <?php echo count( $categories ); ?>;
			let currentTargetInput = null;
			let focusedElementBeforeModal = null;

			// Icon picker functionality
			const modal = document.getElementById('icon-picker-modal');
			const closeBtn = document.getElementById('close-icon-picker');
			const iconPickerContent = modal.querySelector('.icon-picker-content');
			const iconItems = modal.querySelectorAll('.icon-picker-item');
			const firstIconItem = iconItems[0];
			const lastIconItem = iconItems[iconItems.length - 1];

			// Get all focusable elements in modal
			function getFocusableElements() {
				return modal.querySelectorAll('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
			}

			// Open icon picker
			document.addEventListener('click', function(e) {
				if (e.target.closest('.icon-picker-button')) {
					e.preventDefault();
					const button = e.target.closest('.icon-picker-button');
					const wrapper = button.closest('.icon-field-wrapper');
					currentTargetInput = wrapper.querySelector('.icon-input');
					
					// Store currently focused element
					focusedElementBeforeModal = document.activeElement;
					
					// Open modal
					modal.classList.add('active');
					
					// Focus on close button when modal opens
					setTimeout(() => {
						closeBtn.focus();
					}, 100);
				}
			});

			// Close icon picker function
			function closeModal() {
				modal.classList.remove('active');
				currentTargetInput = null;
				
				// Restore focus to the button that opened the modal
				if (focusedElementBeforeModal) {
					focusedElementBeforeModal.focus();
					focusedElementBeforeModal = null;
				}
			}

			// Close button click
			closeBtn.addEventListener('click', function() {
				closeModal();
			});

			// Close on outside click
			modal.addEventListener('click', function(e) {
				if (e.target === modal) {
					closeModal();
				}
			});

			// Close on Escape key
			modal.addEventListener('keydown', function(e) {
				if (e.key === 'Escape' || e.key === 'Esc') {
					closeModal();
				}
			});

			// Trap focus within modal
			modal.addEventListener('keydown', function(e) {
				if (e.key !== 'Tab') return;

				const focusableElements = getFocusableElements();
				const firstElement = focusableElements[0];
				const lastElement = focusableElements[focusableElements.length - 1];

				if (e.shiftKey) {
					// Shift + Tab
					if (document.activeElement === firstElement) {
						e.preventDefault();
						lastElement.focus();
					}
				} else {
					// Tab
					if (document.activeElement === lastElement) {
						e.preventDefault();
						firstElement.focus();
					}
				}
			});

			// Select icon with click or keyboard
			iconItems.forEach(function(item) {
				item.addEventListener('click', function() {
					selectIcon(this.dataset.icon);
				});
			});

			function selectIcon(iconClass) {
				if (currentTargetInput) {
					currentTargetInput.value = iconClass;
					// Update preview in category header if visible
					const categoryItem = currentTargetInput.closest('.category-item');
					if (categoryItem) {
						const preview = categoryItem.querySelector('.category-icon-preview');
						if (preview) {
							preview.className = 'dashicons ' + iconClass + ' category-icon-preview';
						}
					}
				}
				closeModal();
			}

			// Add new category
			document.getElementById('add-new-category').addEventListener('click', function() {
				const newId = 'category-' + Date.now();
				const newCategoryHtml = `
					<div class="category-item" data-category-id="${newId}">
						<h4>
							<span class="dashicons dashicons-admin-links category-icon-preview" style="color: #2271b1;"></span>
							<?php echo esc_js( __( 'New Category', 'inline-context' ) ); ?>
						</h4>
						<div class="category-fields">
							<div class="category-field">
								<label><?php echo esc_js( __( 'Category Name', 'inline-context' ) ); ?></label>
								<input type="text" 
									name="inline_context_categories[${newId}][name]" 
									value="<?php echo esc_js( __( 'New Category', 'inline-context' ) ); ?>" 
									required />
							</div>
							<div class="category-field">
								<label><?php echo esc_js( __( 'Icon (Closed)', 'inline-context' ) ); ?></label>
								<input type="text" 
									name="inline_context_categories[${newId}][icon_closed]" 
									value="dashicons-admin-links" 
									placeholder="dashicons-admin-links" />
								<div class="dashicons-examples">
									<?php
									echo wp_kses(
										__(
											'Examples: <code>dashicons-book</code>, <code>dashicons-external</code>, <code>dashicons-lightbulb</code>',
											'inline-context'
										),
										array(
											'code' => array(),
										)
									);
									?>
								</div>
							</div>
							<div class="category-field">
								<label><?php echo esc_js( __( 'Icon (Open)', 'inline-context' ) ); ?></label>
								<input type="text" 
									name="inline_context_categories[${newId}][icon_open]" 
									value="dashicons-admin-links" 
									placeholder="dashicons-book-alt" />
								<div class="dashicons-examples">
									<?php
									echo wp_kses(
										__(
											'See <a href="https://developer.wordpress.org/resource/dashicons/" target="_blank">all dashicons</a>',
											'inline-context'
										),
										array(
											'a' => array(
												'href'   => array(),
												'target' => array(),
											),
										)
									);
									?>
								</div>
							</div>
							<div class="category-field">
								<label><?php echo esc_js( __( 'Color', 'inline-context' ) ); ?></label>
								<input type="color" 
									name="inline_context_categories[${newId}][color]" 
									value="#2271b1" />
							</div>
						</div>
						<input type="hidden" name="inline_context_categories[${newId}][id]" value="${newId}" />
						<div class="category-actions">
							<button type="button" class="button button-secondary button-small delete-category" data-category-id="${newId}">
								<?php echo esc_js( __( 'Delete Category', 'inline-context' ) ); ?>
							</button>
						</div>
					</div>
				`;
				
				document.querySelector('.categories-list').insertAdjacentHTML('beforeend', newCategoryHtml);
				categoryCounter++;
				attachDeleteListeners();
			});

			// Delete category
			function attachDeleteListeners() {
				document.querySelectorAll('.delete-category').forEach(function(btn) {
					btn.replaceWith(btn.cloneNode(true)); // Remove old listeners
				});
				
				document.querySelectorAll('.delete-category').forEach(function(btn) {
					btn.addEventListener('click', function() {
						if (confirm('<?php echo esc_js( __( 'Are you sure you want to delete this category?', 'inline-context' ) ); ?>')) {
							const categoryItem = this.closest('.category-item');
							categoryItem.remove();
						}
					});
				});
			}

			attachDeleteListeners();
		})();
	</script>
	<?php
}

/**
 * Section callbacks
 */

/**
 * Link section callback
 */
function inline_context_link_section_callback() {
	echo '<p>' . esc_html__( 'Customize how inline context links appear before they are clicked. These are the underlined trigger words that users click to reveal notes.', 'inline-context' ) . '</p>';
}

/**
 * Note section callback
 */
function inline_context_note_section_callback() {
	echo '<p>' . esc_html__( 'Customize the appearance of the revealed note blocks that appear below the trigger link when clicked.', 'inline-context' ) . '</p>';
}

/**
 * Chevron section callback
 */
function inline_context_chevron_section_callback() {
	echo '<p>' . esc_html__( 'Customize the small arrow (chevron) icon that appears next to inline context links. The chevron indicates that the link can be expanded.', 'inline-context' ) . '</p>';
}

/**
 * Render field
 *
 * @param array $args The field arguments including key, type, default, and options.
 */
function inline_context_render_field( $args ) {
	$options = get_option( 'inline_context_css_variables', inline_context_get_default_css_variables() );
	$value   = $options[ $args['key'] ] ?? $args['default'];

	if ( 'color' === $args['type'] ) {
		printf(
			'<input type="color" name="inline_context_css_variables[%s]" value="%s" />',
			esc_attr( $args['key'] ),
			esc_attr( $value )
		);
	} elseif ( 'select' === $args['type'] ) {
		printf(
			'<select name="inline_context_css_variables[%s]">',
			esc_attr( $args['key'] )
		);
		foreach ( $args['options'] as $option_value => $option_label ) {
			printf(
				'<option value="%s" %s>%s</option>',
				esc_attr( $option_value ),
				selected( $value, $option_value, false ),
				esc_html( $option_label )
			);
		}
		echo '</select>';
	} else {
		printf(
			'<input type="text" name="inline_context_css_variables[%s]" value="%s" class="regular-text" />',
			esc_attr( $args['key'] ),
			esc_attr( $value )
		);
	}

	if ( ! empty( $args['description'] ) ) {
		printf( '<p class="description">%s</p>', esc_html( $args['description'] ) );
	}
}

/**
 * Render settings page with tabs
 */
function inline_context_render_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	// Define tabs.
	$tabs = array(
		'categories' => __( 'Categories', 'inline-context' ),
		'styling'    => __( 'Styling', 'inline-context' ),
	);

	// Get current tab.
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- WordPress handles nonce for settings page.
	$tab_param   = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'categories';
	$current_tab = isset( $tabs[ $tab_param ] ) ? $tab_param : 'categories';

	// Handle reset for current tab.
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- WordPress handles nonce for settings page.
	$reset_param = isset( $_GET['reset'] ) ? sanitize_key( wp_unslash( $_GET['reset'] ) ) : '';
	if ( '1' === $reset_param ) {
		if ( 'categories' === $current_tab ) {
			update_option( 'inline_context_categories', inline_context_get_default_categories() );
		} else {
			update_option( 'inline_context_css_variables', inline_context_get_default_css_variables() );
		}
		wp_safe_redirect( admin_url( 'options-general.php?page=inline-context-settings&tab=' . $current_tab ) );
		exit;
	}

	// WordPress Settings API automatically shows success message, no need to add it manually.
	settings_errors( 'inline_context_messages' );
	?>
	<div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

		<nav class="nav-tab-wrapper">
			<?php
			foreach ( $tabs as $tab => $name ) {
				$current = $tab === $current_tab ? ' nav-tab-active' : '';
				$url     = add_query_arg(
					array(
						'page' => 'inline-context-settings',
						'tab'  => $tab,
					),
					admin_url( 'options-general.php' )
				);
				printf(
					'<a class="nav-tab%s" href="%s">%s</a>',
					esc_attr( $current ),
					esc_url( $url ),
					esc_html( $name )
				);
			}
			?>
		</nav>

		<?php if ( 'categories' === $current_tab ) : ?>
			<p><?php esc_html_e( 'Manage categories for your inline context notes. Each category can have its own icon and color.', 'inline-context' ); ?></p>

			<form action="options.php" method="post">
				<?php
				settings_fields( 'inline_context_categories_settings' );
				do_settings_sections( 'inline-context-settings-categories' );
				?>

				<p class="submit">
					<input type="submit" name="submit" class="button button-primary" value="<?php esc_attr_e( 'Save Changes', 'inline-context' ); ?>">
					<a href="
					<?php
					echo esc_url(
						add_query_arg(
							array(
								'reset' => '1',
								'tab'   => 'categories',
							)
						)
					);
					?>
								" class="button" onclick="return confirm('<?php esc_attr_e( 'Are you sure you want to reset categories to defaults?', 'inline-context' ); ?>');">
						<?php esc_html_e( 'Reset to Defaults', 'inline-context' ); ?>
					</a>
				</p>
			</form>

		<?php else : ?>
			<p><?php esc_html_e( 'Customize the appearance of inline context notes using CSS custom properties. Changes will affect all notes on your site.', 'inline-context' ); ?></p>

			<form action="options.php" method="post">
				<?php
				settings_fields( 'inline_context_styling_settings' );
				do_settings_sections( 'inline-context-settings-styling' );
				?>

				<p class="submit">
					<input type="submit" name="submit" class="button button-primary" value="<?php esc_attr_e( 'Save Changes', 'inline-context' ); ?>">
					<a href="
					<?php
					echo esc_url(
						add_query_arg(
							array(
								'reset' => '1',
								'tab'   => 'styling',
							)
						)
					);
					?>
								" class="button" onclick="return confirm('<?php esc_attr_e( 'Are you sure you want to reset styling to defaults?', 'inline-context' ); ?>');">
						<?php esc_html_e( 'Reset to Defaults', 'inline-context' ); ?>
					</a>
				</p>
			</form>

			<hr>

			<h2><?php esc_html_e( 'Live Preview', 'inline-context' ); ?></h2>
			<p><?php esc_html_e( 'See how your styling looks in action. Hover over the link and click it to reveal the note.', 'inline-context' ); ?></p>

			<div id="inline-context-preview-area" style="padding: 30px; background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; margin-top: 10px; box-shadow: 0 1px 1px rgba(0,0,0,0.04);">
				<h3 style="margin-top: 0; color: #1d2327;"><?php esc_html_e( 'Example Article', 'inline-context' ); ?></h3>
				<p style="line-height: 1.6; color: #2c3338;">
					<?php esc_html_e( 'WordPress is a popular content management system. You can add', 'inline-context' ); ?>
					<a href="#preview-note" class="wp-inline-context" style="scroll-margin-top: var(--wp--custom--inline-context--link--scroll-margin, 80px);" data-inline-context="<p><strong>Inline Context</strong> allows you to add expandable notes directly in your content without breaking the reading flow.</p><p>Readers can click to reveal additional information, definitions, or related content when they need it.</p>" data-anchor-id="preview-note" id="trigger-preview-note" role="button" aria-expanded="false">
						<?php esc_html_e( 'inline context notes', 'inline-context' ); ?>
					</a>
					<?php esc_html_e( 'to provide additional information without cluttering your content.', 'inline-context' ); ?>
				</p>
				<p style="line-height: 1.6; color: #2c3338; margin-top: 20px;">
					<small style="color: #646970;">
						<strong><?php esc_html_e( 'Tip:', 'inline-context' ); ?></strong>
						<?php esc_html_e( 'Try hovering over the link above to see hover effects, then click it to reveal the note and see how it appears with your current styling.', 'inline-context' ); ?>
					</small>
				</p>
			</div>

			<script>
				// Simple frontend preview functionality
				(function() {
					const trigger = document.getElementById('trigger-preview-note');
					if (!trigger) return;

					trigger.addEventListener('click', function(e) {
						e.preventDefault();
						
						const existingNote = document.querySelector('.wp-inline-context-inline[data-anchor-id="preview-note"]');
						
						if (existingNote) {
							// Remove note
							existingNote.remove();
							trigger.setAttribute('aria-expanded', 'false');
							trigger.classList.remove('wp-inline-context--open');
						} else {
							// Add note
							const note = document.createElement('div');
							note.className = 'wp-inline-context-inline';
							note.setAttribute('data-anchor-id', 'preview-note');
							note.setAttribute('role', 'note');
							note.innerHTML = trigger.getAttribute('data-inline-context');
							
							trigger.parentElement.insertAdjacentElement('afterend', note);
							trigger.setAttribute('aria-expanded', 'true');
							trigger.classList.add('wp-inline-context--open');
						}
					});
				})();
			</script>

			<style>
				/* Preview-specific styles - mimic frontend appearance */
				#inline-context-preview-area .wp-inline-context {
					cursor: pointer;
					background: none;
					border: none;
					padding: 0;
					margin: 0;
					font-family: inherit;
					font-size: inherit;
					font-weight: inherit;
					line-height: inherit;
					text-align: inherit;
					text-decoration: underline;
					color: inherit;
					white-space: normal;
					transition: color 0.2s ease;
					display: inline;
				}

				#inline-context-preview-area .wp-inline-context:hover,
				#inline-context-preview-area .wp-inline-context:focus {
					color: var(--wp--custom--inline-context--link--hover-color, #2271b1);
				}

				#inline-context-preview-area .wp-inline-context:focus {
					outline: 2px solid var(--wp--custom--inline-context--link--focus-border-color, #2271b1);
					outline-offset: 2px;
					border-radius: 2px;
				}

				#inline-context-preview-area .wp-inline-context--open {
					color: var(--wp--custom--inline-context--link--open-color, #2271b1);
				}

				#inline-context-preview-area .wp-inline-context::after {
					content: '\00A0';
					display: inline-block;
					width: var(--wp--custom--inline-context--chevron--size, 0.7em);
					height: var(--wp--custom--inline-context--chevron--size, 0.7em);
					margin-left: var(--wp--custom--inline-context--chevron--margin-left, 0.25em);
					vertical-align: middle;
					text-decoration: none;
					background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="%23666"><path d="M4.22 6.22a.75.75 0 0 1 1.06 0L8 8.94l2.72-2.72a.75.75 0 1 1 1.06 1.06l-3.25 3.25a.75.75 0 0 1-1.06 0L4.22 7.28a.75.75 0 0 1 0-1.06z"/></svg>');
					background-repeat: no-repeat;
					background-size: contain;
					background-position: center;
					transition: transform 0.4s ease, background-image 0.2s ease;
					opacity: var(--wp--custom--inline-context--chevron--opacity, 0.7);
				}

				#inline-context-preview-area .wp-inline-context:hover::after,
				#inline-context-preview-area .wp-inline-context:focus::after {
					background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="%232271b1"><path d="M4.22 6.22a.75.75 0 0 1 1.06 0L8 8.94l2.72-2.72a.75.75 0 1 1 1.06 1.06l-3.25 3.25a.75.75 0 0 1-1.06 0L4.22 7.28a.75.75 0 0 1 0-1.06z"/></svg>');
					opacity: var(--wp--custom--inline-context--chevron--hover-opacity, 1);
				}

				#inline-context-preview-area .wp-inline-context[aria-expanded='true']::after,
				#inline-context-preview-area .wp-inline-context--open::after {
					transform: rotate(180deg);
				}

				#inline-context-preview-area .wp-inline-context-inline {
					display: block;
					margin: var(--wp--custom--inline-context--note--margin-y, 8px) 0;
					padding: var(--wp--custom--inline-context--note--padding-y, 12px) var(--wp--custom--inline-context--note--padding-x, 16px);
					background: var(--wp--custom--inline-context--note--background, #f9f9f9);
					border: 1px solid var(--wp--custom--inline-context--note--border-color, #e0e0e0);
					border-left: var(--wp--custom--inline-context--note--accent-width, 4px) solid var(--wp--custom--inline-context--note--accent-color, #2271b1);
					border-radius: var(--wp--custom--inline-context--note--radius, 4px);
					box-shadow: var(--wp--custom--inline-context--note--shadow, 0 2px 4px rgba(0,0,0,0.1));
					font-size: var(--wp--custom--inline-context--note--font-size, 0.95em);
					animation: wp-inline-context-reveal 0.3s ease-out;
					transform-origin: top left;
				}

				@keyframes wp-inline-context-reveal {
					from {
						opacity: 0;
						transform: translateY(-8px);
					}
					to {
						opacity: 1;
						transform: translateY(0);
					}
				}

				#inline-context-preview-area .wp-inline-context-inline > :first-child {
					margin-top: 0;
				}

				#inline-context-preview-area .wp-inline-context-inline > :last-child {
					margin-bottom: 0;
				}

				#inline-context-preview-area .wp-inline-context-inline a {
					color: var(--wp--custom--inline-context--note--link-color, #2271b1);
					text-decoration: var(--wp--custom--inline-context--note--link-underline, underline);
				}
			</style>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Handle reset request
 */
function inline_context_handle_reset() {
	// Reset is now handled within each page's render function.
}


