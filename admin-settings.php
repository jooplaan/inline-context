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
		'styling'   => __( 'Styling', 'inline-context' ),
		'uninstall' => __( 'Uninstall', 'inline-context' ),
	);

	// Get current tab.
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- WordPress handles nonce for settings page.
	$tab_param   = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'styling';
	$current_tab = isset( $tabs[ $tab_param ] ) ? $tab_param : 'styling';

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

		<?php if ( 'styling' === $current_tab ) : ?>
			<p><?php esc_html_e( 'Customize the appearance of inline context notes using CSS custom properties. Changes will affect all notes on your site.', 'inline-context' ); ?></p>

			<form action="options.php" method="post">
				<?php
				settings_fields( 'inline_context_styling_settings' );
				do_settings_sections( 'inline-context-settings-styling' );
				?>

			<p class="submit">
				<input type="submit" name="submit" class="button button-primary" value="<?php esc_attr_e( 'Save Changes', 'inline-context' ); ?>">
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
					background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill=
				#inline-context-preview-area .wp-inline-context-inline > :last-child {
					margin-bottom: 0;
				}

			#inline-context-preview-area .wp-inline-context-inline a {
				color: var(--wp--custom--inline-context--note--link-color, #2271b1);
				text-decoration: var(--wp--custom--inline-context--note--link-underline, underline);
			}
		</style>

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
