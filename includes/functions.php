<?php
/**
 * Backward compatibility wrapper functions.
 *
 * @package Inline_Context
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'inline_context_get_default_categories' ) ) {
	/**
	 * Get default categories (backward compatibility wrapper).
	 *
	 * @return array Default categories.
	 */
	function inline_context_get_default_categories() {
		return Inline_Context_Utils::get_default_categories();
	}
}

if ( ! function_exists( 'inline_context_get_categories' ) ) {
	/**
	 * Get categories (backward compatibility wrapper).
	 *
	 * @return array Categories.
	 */
	function inline_context_get_categories() {
		return Inline_Context_Utils::get_categories();
	}
}

if ( ! function_exists( 'inline_context_get_default_css_variables' ) ) {
	/**
	 * Get default CSS variables (backward compatibility wrapper).
	 *
	 * @return array Default CSS variables.
	 */
	function inline_context_get_default_css_variables() {
		return Inline_Context_Utils::get_default_css_variables();
	}
}
