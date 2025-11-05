<?php
/**
 * Admin Settings Page for Inline Context
 *
 * @package InlineContext
 */

defined( 'ABSPATH' ) || exit;

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
	register_setting(
		'inline_context_settings',
		'inline_context_css_variables',
		array(
			'type'              => 'array',
			'sanitize_callback' => 'inline_context_sanitize_css_variables',
			'default'           => inline_context_get_default_css_variables(),
		)
	);

	add_settings_section(
		'inline_context_link_section',
		__( 'Link Styling', 'inline-context' ),
		'inline_context_link_section_callback',
		'inline-context-settings'
	);

	add_settings_section(
		'inline_context_note_section',
		__( 'Note Block Styling', 'inline-context' ),
		'inline_context_note_section_callback',
		'inline-context-settings'
	);

	add_settings_section(
		'inline_context_chevron_section',
		__( 'Chevron Icon Styling', 'inline-context' ),
		'inline_context_chevron_section_callback',
		'inline-context-settings'
	);

	// Link settings.
	$link_fields = array(
		'link-scroll-margin'      => array(
			'label'   => __( 'Scroll Margin', 'inline-context' ),
			'default' => '80px',
			'type'    => 'text',
		),
		'link-hover-color'        => array(
			'label'   => __( 'Hover Color', 'inline-context' ),
			'default' => '#2271b1',
			'type'    => 'color',
		),
		'link-focus-color'        => array(
			'label'   => __( 'Focus Color', 'inline-context' ),
			'default' => '#2271b1',
			'type'    => 'color',
		),
		'link-focus-border-color' => array(
			'label'   => __( 'Focus Border Color', 'inline-context' ),
			'default' => '#2271b1',
			'type'    => 'color',
		),
		'link-open-color'         => array(
			'label'   => __( 'Open State Color', 'inline-context' ),
			'default' => '#2271b1',
			'type'    => 'color',
		),
	);

	foreach ( $link_fields as $key => $field ) {
		add_settings_field(
			'inline_context_' . $key,
			$field['label'],
			'inline_context_render_field',
			'inline-context-settings',
			'inline_context_link_section',
			array(
				'key'     => $key,
				'type'    => $field['type'],
				'default' => $field['default'],
			)
		);
	}

	// Note settings.
	$note_fields = array(
		'note-margin-y'       => array(
			'label'   => __( 'Vertical Margin', 'inline-context' ),
			'default' => '8px',
			'type'    => 'text',
		),
		'note-padding-y'      => array(
			'label'   => __( 'Vertical Padding', 'inline-context' ),
			'default' => '12px',
			'type'    => 'text',
		),
		'note-padding-x'      => array(
			'label'   => __( 'Horizontal Padding', 'inline-context' ),
			'default' => '16px',
			'type'    => 'text',
		),
		'note-background'     => array(
			'label'   => __( 'Background Color', 'inline-context' ),
			'default' => '#f9f9f9',
			'type'    => 'color',
		),
		'note-border-color'   => array(
			'label'   => __( 'Border Color', 'inline-context' ),
			'default' => '#e0e0e0',
			'type'    => 'color',
		),
		'note-accent-width'   => array(
			'label'   => __( 'Accent Width', 'inline-context' ),
			'default' => '4px',
			'type'    => 'text',
		),
		'note-accent-color'   => array(
			'label'   => __( 'Accent Color', 'inline-context' ),
			'default' => '#2271b1',
			'type'    => 'color',
		),
		'note-radius'         => array(
			'label'   => __( 'Border Radius', 'inline-context' ),
			'default' => '4px',
			'type'    => 'text',
		),
		'note-shadow'         => array(
			'label'   => __( 'Box Shadow', 'inline-context' ),
			'default' => '0 2px 4px rgba(0,0,0,0.1)',
			'type'    => 'text',
		),
		'note-font-size'      => array(
			'label'   => __( 'Font Size', 'inline-context' ),
			'default' => '0.95em',
			'type'    => 'text',
		),
		'note-link-color'     => array(
			'label'   => __( 'Link Color', 'inline-context' ),
			'default' => '#2271b1',
			'type'    => 'color',
		),
		'note-link-underline' => array(
			'label'   => __( 'Link Underline', 'inline-context' ),
			'default' => 'underline',
			'type'    => 'select',
			'options' => array(
				'none'      => 'None',
				'underline' => 'Underline',
			),
		),
	);

	foreach ( $note_fields as $key => $field ) {
		add_settings_field(
			'inline_context_' . $key,
			$field['label'],
			'inline_context_render_field',
			'inline-context-settings',
			'inline_context_note_section',
			array(
				'key'     => $key,
				'type'    => $field['type'],
				'default' => $field['default'],
				'options' => $field['options'] ?? array(),
			)
		);
	}

	// Chevron settings.
	$chevron_fields = array(
		'chevron-default-color' => array(
			'label'   => __( 'Default Color', 'inline-context' ),
			'default' => '#666',
			'type'    => 'color',
		),
		'chevron-hover-color'   => array(
			'label'   => __( 'Hover Color', 'inline-context' ),
			'default' => '#2271b1',
			'type'    => 'color',
		),
		'chevron-size'          => array(
			'label'   => __( 'Size', 'inline-context' ),
			'default' => '0.7em',
			'type'    => 'text',
		),
		'chevron-margin-left'   => array(
			'label'   => __( 'Left Margin', 'inline-context' ),
			'default' => '0.25em',
			'type'    => 'text',
		),
		'chevron-opacity'       => array(
			'label'   => __( 'Opacity', 'inline-context' ),
			'default' => '0.7',
			'type'    => 'text',
		),
		'chevron-hover-opacity' => array(
			'label'   => __( 'Hover Opacity', 'inline-context' ),
			'default' => '1',
			'type'    => 'text',
		),
	);

	foreach ( $chevron_fields as $key => $field ) {
		add_settings_field(
			'inline_context_' . $key,
			$field['label'],
			'inline_context_render_field',
			'inline-context-settings',
			'inline_context_chevron_section',
			array(
				'key'     => $key,
				'type'    => $field['type'],
				'default' => $field['default'],
			)
		);
	}
}
add_action( 'admin_init', 'inline_context_register_settings' );

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
 * Sanitize CSS variables
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
function inline_context_link_section_callback() {
	echo '<p>' . esc_html__( 'Customize the appearance of inline context links.', 'inline-context' ) . '</p>';
}

function inline_context_note_section_callback() {
	echo '<p>' . esc_html__( 'Customize the appearance of revealed note blocks.', 'inline-context' ) . '</p>';
}

function inline_context_chevron_section_callback() {
	echo '<p>' . esc_html__( 'Customize the chevron icon that appears next to links.', 'inline-context' ) . '</p>';
}

/**
 * Render field
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
 * Render settings page
 */
function inline_context_render_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	// Show success message.
	if ( isset( $_GET['settings-updated'] ) ) {
		add_settings_error(
			'inline_context_messages',
			'inline_context_message',
			__( 'Settings saved.', 'inline-context' ),
			'success'
		);
	}

	settings_errors( 'inline_context_messages' );
	?>
	<div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		
		<p><?php esc_html_e( 'Customize the appearance of inline context notes using CSS custom properties. Changes will affect all notes on your site.', 'inline-context' ); ?></p>

		<form action="options.php" method="post">
			<?php
			settings_fields( 'inline_context_settings' );
			do_settings_sections( 'inline-context-settings' );
			?>
			
			<p class="submit">
				<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php esc_attr_e( 'Save Changes', 'inline-context' ); ?>">
				<a href="<?php echo esc_url( add_query_arg( 'reset', '1' ) ); ?>" class="button" onclick="return confirm('<?php esc_attr_e( 'Are you sure you want to reset all settings to defaults?', 'inline-context' ); ?>');">
					<?php esc_html_e( 'Reset to Defaults', 'inline-context' ); ?>
				</a>
			</p>
		</form>

		<hr>

		<h2><?php esc_html_e( 'Preview', 'inline-context' ); ?></h2>
		<p><?php esc_html_e( 'This is how your inline context will appear with current settings:', 'inline-context' ); ?></p>
		
		<div style="padding: 20px; background: #fff; border: 1px solid #ccc; margin-top: 10px;">
			<p>
				<?php esc_html_e( 'This is a sample paragraph with an', 'inline-context' ); ?>
				<a href="#preview-note" class="wp-inline-context" style="scroll-margin-top: var(--wp--custom--inline-context--link--scroll-margin, 80px);" data-inline-context="<p>This is a sample note showing how your styling looks.</p>" data-anchor-id="preview-note" id="trigger-preview-note" role="button" aria-expanded="false">
					<?php esc_html_e( 'inline context note', 'inline-context' ); ?>
				</a>
				<?php esc_html_e( 'that you can click to reveal.', 'inline-context' ); ?>
			</p>
		</div>
	</div>
	<?php
}

/**
 * Output custom CSS variables
 */
function inline_context_output_custom_css() {
	$options = get_option( 'inline_context_css_variables', inline_context_get_default_css_variables() );

	$css = ':root {';
	foreach ( $options as $key => $value ) {
		$css .= sprintf(
			'--wp--custom--inline-context--%s: %s;',
			$key,
			esc_attr( $value )
		);
	}
	$css .= '}';

	printf( '<style id="inline-context-custom-css">%s</style>', $css );
}
add_action( 'wp_head', 'inline_context_output_custom_css' );
add_action( 'admin_head', 'inline_context_output_custom_css' );

/**
 * Handle reset to defaults
 */
function inline_context_handle_reset() {
	if ( isset( $_GET['page'] ) && 'inline-context-settings' === $_GET['page'] && isset( $_GET['reset'] ) && current_user_can( 'manage_options' ) ) {
		update_option( 'inline_context_css_variables', inline_context_get_default_css_variables() );
		wp_safe_redirect( admin_url( 'options-general.php?page=inline-context-settings&settings-updated=1' ) );
		exit;
	}
}
add_action( 'admin_init', 'inline_context_handle_reset' );
