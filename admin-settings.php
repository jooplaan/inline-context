<?php
/**
 * Admin Settings Page for Inline Context
 *
 * @package InlineContext
 */

defined( 'ABSPATH' ) || exit;

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
	// Register display mode setting.
	register_setting(
		'inline_context_general_settings',
		'inline_context_display_mode',
		array(
			'type'              => 'string',
			'sanitize_callback' => 'inline_context_sanitize_display_mode',
			'default'           => 'inline',
		)
	);

	// Register tooltip hover setting.
	register_setting(
		'inline_context_general_settings',
		'inline_context_tooltip_hover',
		array(
			'type'              => 'boolean',
			'sanitize_callback' => 'rest_sanitize_boolean',
			'default'           => false,
		)
	);

	// Register animation setting.
	register_setting(
		'inline_context_general_settings',
		'inline_context_enable_animations',
		array(
			'type'              => 'boolean',
			'sanitize_callback' => 'rest_sanitize_boolean',
			'default'           => true,
		)
	);

	// Register CSS variables setting.
	register_setting(
		'inline_context_general_settings',
		'inline_context_enable_animations',
		array(
			'type'              => 'boolean',
			'sanitize_callback' => 'rest_sanitize_boolean',
			'default'           => true,
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

	// Register uninstall setting.
	register_setting(
		'inline_context_uninstall_settings',
		'inline_context_cleanup_on_uninstall',
		array(
			'type'              => 'boolean',
			'sanitize_callback' => 'rest_sanitize_boolean',
			'default'           => false,
		)
	);

	// Styling tab sections.
	// First: Shared settings for both modes.
	add_settings_section(
		'inline_context_link_section',
		__( 'Trigger Link Styling', 'inline-context' ),
		'inline_context_link_section_callback',
		'inline-context-settings-styling'
	);

	add_settings_section(
		'inline_context_note_section',
		__( 'Note Appearance', 'inline-context' ),
		'inline_context_note_section_callback',
		'inline-context-settings-styling'
	);

	add_settings_section(
		'inline_context_chevron_section',
		__( 'Chevron Icon', 'inline-context' ),
		'inline_context_chevron_section_callback',
		'inline-context-settings-styling'
	);

	// Then: Mode-specific settings.
	add_settings_section(
		'inline_context_inline_section',
		__( 'Inline Mode Specific', 'inline-context' ),
		'inline_context_inline_section_callback',
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

	// Note settings (shared between both inline and tooltip modes).
	$note_fields = array(
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
			'description' => __( 'Drop shadow effect. Use CSS box-shadow format. Tooltips use enhanced shadow.', 'inline-context' ),
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

	// Chevron settings (shared between both inline and tooltip modes).
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

	// Inline mode specific settings.
	$inline_fields = array(
		'note-margin-y'     => array(
			'label'       => __( 'Vertical Margin', 'inline-context' ),
			'default'     => '8px',
			'type'        => 'text',
			'description' => __( 'Space above and below the inline note block. Not used in tooltip mode.', 'inline-context' ),
		),
		'note-accent-width' => array(
			'label'       => __( 'Accent Bar Width', 'inline-context' ),
			'default'     => '4px',
			'type'        => 'text',
			'description' => __( 'Width of the colored accent bar on the left side. Not used in tooltip mode.', 'inline-context' ),
		),
		'note-accent-color' => array(
			'label'       => __( 'Accent Bar Color', 'inline-context' ),
			'default'     => '#2271b1',
			'type'        => 'color',
			'description' => __( 'Color of the accent bar on the left side. Not used in tooltip mode.', 'inline-context' ),
		),
	);

	foreach ( $inline_fields as $key => $field ) {
		add_settings_field(
			'inline_context_' . $key,
			$field['label'],
			'inline_context_render_field',
			'inline-context-settings-styling',
			'inline_context_inline_section',
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
 * Sanitize display mode setting
 *
 * @param string $input The input value to sanitize.
 * @return string The sanitized display mode ('inline' or 'tooltip').
 */
function inline_context_sanitize_display_mode( $input ) {
	$valid_modes = array( 'inline', 'tooltip' );
	return in_array( $input, $valid_modes, true ) ? $input : 'inline';
}

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
 * Section callbacks
 */

/**
 * Link section callback
 */
function inline_context_link_section_callback() {
	echo '<p>' . esc_html__( 'Customize how trigger links appear. These settings apply to both inline and tooltip display modes.', 'inline-context' ) . '</p>';
}

/**
 * Note section callback
 */
function inline_context_note_section_callback() {
	echo '<p>' . esc_html__( 'Customize the appearance of note content. These settings apply to both inline notes and tooltips.', 'inline-context' ) . '</p>';
}

/**
 * Inline mode section callback
 */
function inline_context_inline_section_callback() {
	echo '<p>' . esc_html__( 'These settings only apply when Display Mode is set to "Inline notes". They are not used in tooltip mode.', 'inline-context' ) . '</p>';
}

/**
 * Chevron section callback
 */
function inline_context_chevron_section_callback() {
	echo '<p>' . esc_html__( 'Customize the chevron icon that appears next to trigger links. These settings apply to both display modes.', 'inline-context' ) . '</p>';
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
		'general'   => __( 'General', 'inline-context' ),
		'styling'   => __( 'Styling', 'inline-context' ),
		'uninstall' => __( 'Uninstall', 'inline-context' ),
	);

	// Get current tab.
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- WordPress handles nonce for settings page.
	$tab_param   = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'general';
	$current_tab = isset( $tabs[ $tab_param ] ) ? $tab_param : 'general';

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

		<?php if ( 'general' === $current_tab ) : ?>
			<h2><?php esc_html_e( 'Display Settings', 'inline-context' ); ?></h2>
			<p><?php esc_html_e( 'Choose how inline context notes appear to your visitors on the frontend.', 'inline-context' ); ?></p>

			<form action="options.php" method="post">
				<?php settings_fields( 'inline_context_general_settings' ); ?>

				<table class="form-table" role="presentation">
					<tr>
						<th scope="row">
							<?php esc_html_e( 'Display Mode', 'inline-context' ); ?>
						</th>
						<td>
							<fieldset>
								<legend class="screen-reader-text">
									<span><?php esc_html_e( 'Display Mode', 'inline-context' ); ?></span>
								</legend>
								<?php
								$display_mode = get_option( 'inline_context_display_mode', 'inline' );
								?>
								<label>
									<input type="radio" name="inline_context_display_mode" value="inline" <?php checked( $display_mode, 'inline' ); ?>>
									<?php esc_html_e( 'Show notes as inline note (default)', 'inline-context' ); ?>
								</label>
								<br>
								<label>
									<input type="radio" name="inline_context_display_mode" value="tooltip" <?php checked( $display_mode, 'tooltip' ); ?>>
									<?php esc_html_e( 'Show notes as tooltips', 'inline-context' ); ?>
								</label>
							<p class="description">
								<?php esc_html_e( 'Inline notes expand below the trigger link when clicked. Tooltips appear as a popup near the trigger link when clicked or activated with keyboard.', 'inline-context' ); ?>
							</p>
							<div id="tooltip-hover-option" style="margin-left: 25px; margin-top: 10px; <?php echo ( 'tooltip' !== $display_mode ? 'display: none;' : '' ); ?>">
								<label>
									<input type="checkbox" name="inline_context_tooltip_hover" value="1" <?php checked( get_option( 'inline_context_tooltip_hover', false ) ); ?>>
									<?php esc_html_e( 'Also display the tooltip on mouse hover', 'inline-context' ); ?>
								</label>
								<p class="description" style="margin-left: 25px;">
									<?php esc_html_e( 'When enabled, tooltips will appear when hovering over the link, in addition to click/keyboard activation.', 'inline-context' ); ?>
								</p>
							</div>							</fieldset>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Animations', 'inline-context' ); ?></th>
					<td>
						<fieldset>
							<label>
								<input type="checkbox" name="inline_context_enable_animations" value="1" <?php checked( get_option( 'inline_context_enable_animations', true ) ); ?>>
								<?php esc_html_e( 'Enable subtle animations when notes appear', 'inline-context' ); ?>
							</label>
							<p class="description">
								<?php esc_html_e( 'When enabled, notes will smoothly fade and slide in. When disabled, notes appear instantly. Always respects user preference for reduced motion.', 'inline-context' ); ?>
							</p>
						</fieldset>
					</td>
				</tr>
			</table>				<p class="submit">
					<input type="submit" name="submit" class="button button-primary" value="<?php esc_attr_e( 'Save Changes', 'inline-context' ); ?>">
				</p>
			</form>

		<?php elseif ( 'styling' === $current_tab ) : ?>
			<p><?php esc_html_e( 'Customize the appearance of inline context notes. Settings are organized by what they apply to: both display modes, or inline mode only.', 'inline-context' ); ?></p>

			<form action="options.php" method="post">
				<?php
				settings_fields( 'inline_context_styling_settings' );
				do_settings_sections( 'inline-context-settings-styling' );
				?>

				<p class="submit">
					<input type="submit" name="submit" class="button button-primary" value="<?php esc_attr_e( 'Save Changes', 'inline-context' ); ?>">
				</p>
			</form>

		<?php elseif ( 'uninstall' === $current_tab ) : ?>
			<?php inline_context_render_uninstall_tab(); ?>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Render the Uninstall tab content
 */
function inline_context_render_uninstall_tab() {
	// Count posts with inline context links.
	global $wpdb;
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- One-time count for display only.
	$posts_with_links = $wpdb->get_var(
		"SELECT COUNT(DISTINCT ID)
		FROM {$wpdb->posts}
		WHERE post_content LIKE '%class=\"wp-inline-context\"%'
		AND post_status IN ('publish', 'draft', 'pending', 'private', 'future', 'trash')"
	);

	// Get list of posts with inline context links.
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- One-time query for display only.
	$posts_list = $wpdb->get_results(
		"SELECT ID, post_title, post_type, post_status
		FROM {$wpdb->posts}
		WHERE post_content LIKE '%class=\"wp-inline-context\"%'
		AND post_status IN ('publish', 'draft', 'pending', 'private', 'future', 'trash')
		ORDER BY post_type, post_title
		LIMIT 100"
	);

	// Count total notes.
	$notes_count = wp_count_posts( 'inline_context_note' );
	$total_notes = $notes_count->publish + $notes_count->draft + $notes_count->pending + $notes_count->private;

	$cleanup_content = get_option( 'inline_context_cleanup_on_uninstall', false );
	?>

	<div class="notice notice-warning">
		<p>
			<strong><?php esc_html_e( 'Important:', 'inline-context' ); ?></strong>
			<?php esc_html_e( 'These settings determine what happens when you delete this plugin from WordPress.', 'inline-context' ); ?>
		</p>
	</div>

	<form action="options.php" method="post">
		<?php settings_fields( 'inline_context_uninstall_settings' ); ?>

		<table class="form-table" role="presentation">
			<tbody>
				<tr>
					<th scope="row">
						<?php esc_html_e( 'Current Usage', 'inline-context' ); ?>
					</th>
					<td>
						<p>
							<strong><?php echo esc_html( number_format_i18n( $total_notes ) ); ?></strong>
							<?php echo esc_html( _n( 'note', 'notes', $total_notes, 'inline-context' ) ); ?>
						</p>
						<p>
							<strong><?php echo esc_html( number_format_i18n( $posts_with_links ) ); ?></strong>
							<?php echo esc_html( _n( 'post with inline context links', 'posts with inline context links', $posts_with_links, 'inline-context' ) ); ?>
						</p>
						<?php if ( ! empty( $posts_list ) ) : ?>
							<details style="margin-top: 10px;">
								<summary style="cursor: pointer; color: #2271b1;">
									<?php esc_html_e( 'View list of posts', 'inline-context' ); ?>
								</summary>
								<ul style="margin: 10px 0 0 20px; list-style: disc;">
									<?php foreach ( $posts_list as $post ) : ?>
										<li>
											<a href="<?php echo esc_url( get_edit_post_link( $post->ID ) ); ?>">
												<?php echo esc_html( $post->post_title ? $post->post_title : __( '(no title)', 'inline-context' ) ); ?>
											</a>
											<span style="color: #646970;">
												(<?php echo esc_html( get_post_type_object( $post->post_type )->labels->singular_name ); ?>
												<?php if ( 'publish' !== $post->post_status ) : ?>
													— <?php echo esc_html( $post->post_status ); ?>
													<?php endif; ?>)
											</span>
										</li>
									<?php endforeach; ?>
								</ul>
								<?php if ( $posts_with_links > 100 ) : ?>
									<p style="margin-left: 20px; color: #646970; font-style: italic;">
										<?php
										printf(
											/* translators: %s: number of additional posts */
											esc_html__( '...and %s more', 'inline-context' ),
											esc_html( number_format_i18n( $posts_with_links - 100 ) )
										);
										?>
									</p>
								<?php endif; ?>
							</details>
						<?php endif; ?>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<?php esc_html_e( 'When Plugin is Deleted', 'inline-context' ); ?>
					</th>
					<td>
						<p><?php esc_html_e( 'The following will always be removed:', 'inline-context' ); ?></p>
						<ul style="list-style: disc; margin-left: 25px;">
							<li><?php esc_html_e( 'All stored notes (Custom Post Type data)', 'inline-context' ); ?></li>
							<li><?php esc_html_e( 'All note categories', 'inline-context' ); ?></li>
							<li><?php esc_html_e( 'Plugin settings and options', 'inline-context' ); ?></li>
						</ul>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="inline_context_cleanup_on_uninstall">
							<?php esc_html_e( 'Clean Up Post Content', 'inline-context' ); ?>
						</label>
					</th>
					<td>
						<fieldset>
							<label>
								<input type="checkbox"
									name="inline_context_cleanup_on_uninstall"
									id="inline_context_cleanup_on_uninstall"
									value="1"
									<?php checked( $cleanup_content, true ); ?>>
								<?php esc_html_e( 'Remove inline context links from post content', 'inline-context' ); ?>
							</label>
							<p class="description">
								<?php
								esc_html_e(
									'If checked, when the plugin is deleted, all inline context links will be converted to plain text in your posts. The link text will remain, but the expandable functionality will be removed.',
									'inline-context'
								);
								?>
							</p>
							<p class="description" style="background: #fff8e5; padding: 10px; border-left: 4px solid #dba617;">
								<strong><?php esc_html_e( '⚠️ Important:', 'inline-context' ); ?></strong>
								<?php esc_html_e( 'Always create a complete database backup before uninstalling with this option enabled. This operation modifies your post content and cannot be automatically reversed.', 'inline-context' ); ?>
							</p>
							<?php if ( $posts_with_links > 0 ) : ?>
								<p class="description" style="color: #d63638;">
									<strong><?php esc_html_e( 'Warning:', 'inline-context' ); ?></strong>
									<?php
									printf(
										esc_html(
											/* translators: %s: number of posts */
											_n(
												'This will modify %s post in your database.',
												'This will modify %s posts in your database.',
												$posts_with_links,
												'inline-context'
											)
										),
										'<strong>' . esc_html( number_format_i18n( $posts_with_links ) ) . '</strong>'
									);
									?>
								</p>
							<?php endif; ?>
							<p class="description">
								<?php esc_html_e( 'If unchecked, the links will remain in your content but will no longer be functional since the plugin code will be removed.', 'inline-context' ); ?>
							</p>
						</fieldset>
					</td>
				</tr>
			</tbody>
		</table>

		<p class="submit">
			<input type="submit" name="submit" class="button button-primary" value="<?php esc_attr_e( 'Save Uninstall Settings', 'inline-context' ); ?>">
		</p>
	</form>

	<hr>

	<h2><?php esc_html_e( 'How Uninstall Works', 'inline-context' ); ?></h2>
	<ol>
		<li><?php esc_html_e( 'Configure the settings above to choose whether to clean up post content.', 'inline-context' ); ?></li>
		<li><?php esc_html_e( 'Deactivate the plugin (Plugins → Deactivate).', 'inline-context' ); ?></li>
		<li><?php esc_html_e( 'Delete the plugin (Plugins → Delete).', 'inline-context' ); ?></li>
		<li><?php esc_html_e( 'WordPress will automatically run the cleanup based on your settings above.', 'inline-context' ); ?></li>
	</ol>

	<div class="notice notice-info inline" style="margin-top: 20px;">
		<p>
			<strong><?php esc_html_e( 'Recommendation:', 'inline-context' ); ?></strong>
			<?php esc_html_e( 'Before deleting the plugin, we recommend exporting your content as a backup. Go to Tools → Export in your WordPress admin.', 'inline-context' ); ?>
		</p>
	</div>
	<?php
}

/**
 * Enqueue admin scripts for settings page.
 */
function inline_context_admin_scripts() {
	$screen = get_current_screen();
	if ( ! $screen || 'settings_page_inline-context' !== $screen->id ) {
		return;
	}

	// Register and enqueue a plugin-specific script handle.
	wp_register_script(
		'inline-context-admin-settings',
		false,
		array( 'jquery' ),
		INLINE_CONTEXT_VERSION,
		true
	);
	wp_enqueue_script( 'inline-context-admin-settings' );

	// Add inline script for display mode toggling.
	$inline_script = "
		document.addEventListener('DOMContentLoaded', function() {
			const radios = document.querySelectorAll('input[name=\"inline_context_display_mode\"]');
			const hoverOption = document.getElementById('tooltip-hover-option');

			if (!hoverOption || !radios.length) {
				return;
			}

			// Function to update hover option visibility
			const updateHoverOptionVisibility = function() {
				const selectedRadio = document.querySelector('input[name=\"inline_context_display_mode\"]:checked');
				if (selectedRadio) {
					hoverOption.style.display = selectedRadio.value === 'tooltip' ? 'block' : 'none';
				}
			};

			// Update visibility on radio button change
			radios.forEach(function(radio) {
				radio.addEventListener('change', function() {
					updateHoverOptionVisibility();
				});
			});

			// Update visibility immediately on page load
			updateHoverOptionVisibility();
		});
	";

	wp_add_inline_script( 'inline-context-admin-settings', $inline_script );
}
add_action( 'admin_footer', 'inline_context_admin_scripts' );
