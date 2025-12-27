<?php
/**
 * Utility functions for Inline Context plugin.
 *
 * @package InlineContext
 */

/**
 * Class Inline_Context_Utils
 *
 * Provides common utility functions for categories, CSS variables,
 * and other shared functionality used across the plugin.
 */
class Inline_Context_Utils {

	/**
	 * Initialize utilities by registering hooks.
	 */
	public function init() {
		add_action( 'wp_head', array( $this, 'output_custom_css' ) );
		add_action( 'admin_head', array( $this, 'output_custom_css' ) );
	}

	/**
	 * Get default categories.
	 *
	 * @return array Default categories.
	 */
	public static function get_default_categories() {
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
			'source'           => array(
				'id'          => 'source',
				'name'        => __( 'Source', 'inline-context' ),
				'icon_closed' => 'dashicons-book',
				'icon_open'   => 'dashicons-book-alt',
				'color'       => '#00a32a',
			),
			'infocard'         => array(
				'id'          => 'infocard',
				'name'        => __( 'Infocard', 'inline-context' ),
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
	public static function get_categories() {
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
			$icon_closed = get_term_meta( $term->term_id, 'icon_closed', true );
			$icon_open   = get_term_meta( $term->term_id, 'icon_open', true );
			$color       = get_term_meta( $term->term_id, 'color', true );

			$categories[ $term->slug ] = array(
				'id'          => $term->term_id,
				'slug'        => $term->slug,
				'name'        => $term->name,
				'icon_closed' => $icon_closed ? $icon_closed : 'dashicons-info',
				'icon_open'   => $icon_open ? $icon_open : 'dashicons-info',
				'color'       => $color ? $color : '#2271b1',
			);
		}

		return $categories;
	}

	/**
	 * Get default CSS variables.
	 *
	 * @return array Default CSS variables.
	 */
	public static function get_default_css_variables() {
		return array(
			'link-scroll-margin'      => '80px',
			'link-hover-color'        => '#2271b1',
			'link-focus-color'        => '#2271b1',
			'link-focus-border-color' => '#2271b1',
			'link-open-color'         => '#2271b1',
			'pill-border-color'       => '#d4850a',
			'pill-border-width'       => '1.5px',
			'pill-border-radius'      => '0.25rem',
			'pill-padding-y'          => '2px',
			'pill-padding-x'          => '8px',
			'pill-background'         => '#FFF4E6',
			'pill-hover-background'   => 'rgba(212, 133, 10, 0.08)',
			'pill-hover-border-color' => '#b87409',
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
			'note-text-color'         => 'inherit',
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
	 * Get color presets with their configurations.
	 *
	 * @return array Color presets with names, descriptions, and CSS variables.
	 */
	public static function get_color_presets() {
		return array(
			'modern-blue'      => array(
				'name'        => __( 'Modern Blue (Default)', 'inline-context' ),
				'description' => __( 'Clean, professional look with blue accents', 'inline-context' ),
				'variables'   => self::get_default_css_variables(),
			),
			'minimalist-gray'  => array(
				'name'        => __( 'Minimalist Gray', 'inline-context' ),
				'description' => __( 'Subtle, understated design in grayscale', 'inline-context' ),
				'variables'   => array(
					'link-scroll-margin'      => '80px',
					'link-hover-color'        => '#555555',
					'link-focus-color'        => '#555555',
					'link-focus-border-color' => '#555555',
					'link-open-color'         => '#333333',
					'pill-border-color'       => '#999999',
					'pill-border-width'       => '1px',
					'pill-border-radius'      => '0.125rem',
					'pill-padding-y'          => '2px',
					'pill-padding-x'          => '6px',
					'pill-background'         => '#f5f5f5',
					'pill-hover-background'   => '#e8e8e8',
					'pill-hover-border-color' => '#777777',
					'note-margin-y'           => '8px',
					'note-padding-y'          => '10px',
					'note-padding-x'          => '14px',
					'note-background'         => '#fafafa',
					'note-border-color'       => '#dddddd',
					'note-accent-width'       => '3px',
					'note-accent-color'       => '#999999',
					'note-radius'             => '2px',
					'note-shadow'             => '0 1px 2px rgba(0,0,0,0.05)',
					'note-font-size'          => '0.95em',
					'note-text-color'         => 'inherit',
					'note-link-color'         => '#555555',
					'note-link-underline'     => 'underline',
					'chevron-default-color'   => '#999999',
					'chevron-hover-color'     => '#555555',
					'chevron-size'            => '0.7em',
					'chevron-margin-left'     => '0.25em',
					'chevron-opacity'         => '0.6',
					'chevron-hover-opacity'   => '1',
				),
			),
			'high-contrast'    => array(
				'name'        => __( 'High Contrast', 'inline-context' ),
				'description' => __( 'Bold colors for maximum visibility and accessibility', 'inline-context' ),
				'variables'   => array(
					'link-scroll-margin'      => '80px',
					'link-hover-color'        => '#0000ff',
					'link-focus-color'        => '#0000ff',
					'link-focus-border-color' => '#0000ff',
					'link-open-color'         => '#0000cc',
					'pill-border-color'       => '#000000',
					'pill-border-width'       => '2px',
					'pill-border-radius'      => '0rem',
					'pill-padding-y'          => '4px',
					'pill-padding-x'          => '10px',
					'pill-background'         => '#ffff00',
					'pill-hover-background'   => '#ffcc00',
					'pill-hover-border-color' => '#000000',
					'note-margin-y'           => '12px',
					'note-padding-y'          => '16px',
					'note-padding-x'          => '20px',
					'note-background'         => '#ffffff',
					'note-border-color'       => '#000000',
					'note-accent-width'       => '6px',
					'note-accent-color'       => '#0000ff',
					'note-radius'             => '0px',
					'note-shadow'             => 'none',
					'note-font-size'          => '1em',
					'note-text-color'         => '#000000',
					'note-link-color'         => '#0000ff',
					'note-link-underline'     => 'underline',
					'chevron-default-color'   => '#000000',
					'chevron-hover-color'     => '#0000ff',
					'chevron-size'            => '0.8em',
					'chevron-margin-left'     => '0.3em',
					'chevron-opacity'         => '1',
					'chevron-hover-opacity'   => '1',
				),
			),
			'warm-earth'       => array(
				'name'        => __( 'Warm Earth Tones', 'inline-context' ),
				'description' => __( 'Cozy, natural palette with browns and oranges', 'inline-context' ),
				'variables'   => array(
					'link-scroll-margin'      => '80px',
					'link-hover-color'        => '#7A3D0F',
					'link-focus-color'        => '#7A3D0F',
					'link-focus-border-color' => '#7A3D0F',
					'link-open-color'         => '#8B4513',
					'pill-border-color'       => '#CD853F',
					'pill-border-width'       => '1.5px',
					'pill-border-radius'      => '0.5rem',
					'pill-padding-y'          => '3px',
					'pill-padding-x'          => '10px',
					'pill-background'         => '#FFF8DC',
					'pill-hover-background'   => '#FFEFD5',
					'pill-hover-border-color' => '#B8860B',
					'note-margin-y'           => '10px',
					'note-padding-y'          => '14px',
					'note-padding-x'          => '18px',
					'note-background'         => '#FDF5E6',
					'note-border-color'       => '#DEB887',
					'note-accent-width'       => '5px',
					'note-accent-color'       => '#D2691E',
					'note-radius'             => '6px',
					'note-shadow'             => '0 3px 6px rgba(139,69,19,0.15)',
					'note-font-size'          => '0.95em',
					'note-text-color'         => 'inherit',
					'note-link-color'         => '#7A3D0F',
					'note-link-underline'     => 'underline',
					'chevron-default-color'   => '#8B4513',
					'chevron-hover-color'     => '#7A3D0F',
					'chevron-size'            => '0.7em',
					'chevron-margin-left'     => '0.25em',
					'chevron-opacity'         => '0.75',
					'chevron-hover-opacity'   => '1',
				),
			),
			'dark-mode'        => array(
				'name'        => __( 'Dark Mode', 'inline-context' ),
				'description' => __( 'Dark theme with light text for reduced eye strain', 'inline-context' ),
				'variables'   => array(
					'link-scroll-margin'      => '80px',
					'link-hover-color'        => '#4A9EFF',
					'link-focus-color'        => '#4A9EFF',
					'link-focus-border-color' => '#4A9EFF',
					'link-open-color'         => '#66B2FF',
					'pill-border-color'       => '#5A5A5A',
					'pill-border-width'       => '1px',
					'pill-border-radius'      => '0.25rem',
					'pill-padding-y'          => '2px',
					'pill-padding-x'          => '8px',
					'pill-background'         => '#2A2A2A',
					'pill-hover-background'   => '#3A3A3A',
					'pill-hover-border-color' => '#707070',
					'note-margin-y'           => '8px',
					'note-padding-y'          => '12px',
					'note-padding-x'          => '16px',
					'note-background'         => '#1E1E1E',
					'note-border-color'       => '#404040',
					'note-accent-width'       => '4px',
					'note-accent-color'       => '#4A9EFF',
					'note-radius'             => '4px',
					'note-shadow'             => '0 4px 8px rgba(0,0,0,0.4)',
					'note-font-size'          => '0.95em',
					'note-text-color'         => '#E0E0E0',
					'note-link-color'         => '#66B2FF',
					'note-link-underline'     => 'underline',
					'chevron-default-color'   => '#AAAAAA',
					'chevron-hover-color'     => '#4A9EFF',
					'chevron-size'            => '0.7em',
					'chevron-margin-left'     => '0.25em',
					'chevron-opacity'         => '0.7',
					'chevron-hover-opacity'   => '1',
				),
			),
		);
	}

	/**
	 * Output custom CSS variables to the page head.
	 */
	public function output_custom_css() {
		$options = get_option( 'inline_context_css_variables', self::get_default_css_variables() );

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

		// Add icon placement setting as CSS variables.
		// Map setting values to CSS vertical-align values (for inline) and align-self (for flex).
		$icon_placement       = get_option( 'inline_context_icon_placement', 'middle' );
		$css_placement_map    = array(
			'top'    => 'super',       // Superscript position.
			'middle' => 'middle',      // Middle alignment.
			'bottom' => 'text-bottom', // Bottom of text (doesn't expand line height).
		);
		$css_flex_align_map   = array(
			'top'    => 'flex-start', // Align to top of flex container.
			'middle' => 'center',     // Align to center of flex container.
			'bottom' => 'flex-end',   // Align to bottom of flex container.
		);
		$css_placement_value  = isset( $css_placement_map[ $icon_placement ] ) ? $css_placement_map[ $icon_placement ] : 'middle';
		$css_flex_align_value = isset( $css_flex_align_map[ $icon_placement ] ) ? $css_flex_align_map[ $icon_placement ] : 'center';

		$css .= sprintf(
			'--wp--custom--inline-context--icon-placement: %s;',
			esc_attr( $css_placement_value )
		);
		$css .= sprintf(
			'--wp--custom--inline-context--icon-flex-align: %s;',
			esc_attr( $css_flex_align_value )
		);

		$css .= '}';

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '<style id="inline-context-custom-css">' . $css . '</style>';
	}
}
