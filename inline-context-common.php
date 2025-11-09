<?php
/**
 * Common functions for Inline Context, loaded on both frontend and admin.
 *
 * @package InlineContext
 */

defined( 'ABSPATH' ) || exit;

/**
 * Get default categories.
 *
 * @return array Default categories.
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
 * Get categories (from taxonomy).
 *
 * @return array Categories.
 */
function inline_context_get_categories() {
	$terms = get_terms(
		array(
			'taxonomy'   => 'inline_context_category',
			'hide_empty' => false,
		)
	);

	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return array();
	}

	$categories = array();
	foreach ( $terms as $term ) {
		$categories[ $term->slug ] = array(
			'id'          => $term->term_id,
			'slug'        => $term->slug,
			'name'        => $term->name,
			'icon_closed' => get_term_meta( $term->term_id, 'icon_closed', true ) ?: 'dashicons-info',
			'icon_open'   => get_term_meta( $term->term_id, 'icon_open', true ) ?: 'dashicons-info',
			'color'       => get_term_meta( $term->term_id, 'color', true ) ?: '#2271b1',
		);
	}

	return $categories;
}

/**
 * Get default CSS variables.
 *
 * @return array Default CSS variables.
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
 * Output custom CSS variables to the page head.
 */
function inline_context_output_custom_css() {
	$options = get_option( 'inline_context_css_variables', inline_context_get_default_css_variables() );

	$css = ':root {';
	foreach ( $options as $key => $value ) {
		// Convert first hyphen to double hyphen for CSS custom property names.
		$css_key = preg_replace( '/-/', '--', $key, 1 );
		$css    .= sprintf(
			'--wp--custom--inline-context--%s: %s;',
			$css_key,
			esc_attr( $value )
		);
	}
	$css .= '}';

	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo '<style id="inline-context-custom-css">' . $css . '</style>';
}
add_action( 'wp_head', 'inline_context_output_custom_css' );
add_action( 'admin_head', 'inline_context_output_custom_css' );
