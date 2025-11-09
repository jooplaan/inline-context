<?php
/**
 * Uninstall script for Inline Context plugin
 *
 * This file runs when the plugin is deleted from WordPress.
 * It offers to clean up all inline context links from post content.
 *
 * @package InlineContext
 */

// Exit if accessed directly or not uninstalling.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/**
 * Clean inline context formatting from content
 *
 * @param string $content The post content.
 * @return string The cleaned content.
 */
function inline_context_cleanup_content( $content ) {
	if ( empty( $content ) ) {
		return $content;
	}

	// Use DOMDocument to safely parse and modify HTML.
	$doc = new DOMDocument();
	// Suppress warnings from invalid HTML.
	libxml_use_internal_errors( true );
	$doc->loadHTML(
		'<?xml encoding="utf-8" ?>' . $content,
		LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
	);
	libxml_clear_errors();

	$xpath = new DOMXPath( $doc );
	// Find all links with data-inline-context attribute.
	$inline_links = $xpath->query( '//a[@data-inline-context]' );

	$modified = false;

	// Convert NodeList to array to avoid live collection issues.
	$links_to_process = array();
	foreach ( $inline_links as $link ) {
		$links_to_process[] = $link;
	}

	foreach ( $links_to_process as $link ) {
		$modified = true;

		// Get the text content of the link.
		// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- DOMNode property.
		$text_content = $link->textContent;

		// Create a text node with the original text.
		$text_node = $doc->createTextNode( $text_content );

		// Replace the link with plain text.
		// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- DOMNode property.
		$link->parentNode->replaceChild( $text_node, $link );
	}

	if ( ! $modified ) {
		return $content;
	}

	// Save the modified HTML.
	$body = $doc->getElementsByTagName( 'body' )->item( 0 );
	if ( $body ) {
		$cleaned = '';
		// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- DOMNode property.
		foreach ( $body->childNodes as $node ) {
			$cleaned .= $doc->saveHTML( $node );
		}
		return $cleaned;
	}

	return $doc->saveHTML();
}

/**
 * Count posts with inline context links
 *
 * @return int Number of posts with inline context formatting.
 */
function inline_context_count_posts_with_links() {
	global $wpdb;

	$count = $wpdb->get_var(
		"SELECT COUNT(DISTINCT ID)
		FROM {$wpdb->posts}
		WHERE post_content LIKE '%data-inline-context%'
		AND post_status != 'trash'"
	);

	return (int) $count;
}

/**
 * Clean up all inline context data
 */
function inline_context_uninstall_cleanup() {
	global $wpdb;

	// Check if we should clean up content.
	// This option would be set by an admin page or confirmation.
	$cleanup_content = get_option( 'inline_context_cleanup_on_uninstall', false );

	if ( $cleanup_content ) {
		// Get all posts with inline context links.
		$posts = $wpdb->get_results(
			"SELECT ID, post_content
			FROM {$wpdb->posts}
			WHERE post_content LIKE '%data-inline-context%'
			AND post_status != 'trash'",
			ARRAY_A
		);

		foreach ( $posts as $post ) {
			$cleaned_content = inline_context_cleanup_content( $post['post_content'] );

			if ( $cleaned_content !== $post['post_content'] ) {
				wp_update_post(
					array(
						'ID'           => $post['ID'],
						'post_content' => $cleaned_content,
					)
				);
			}
		}
	}

	// Delete all inline context notes (CPT).
	$note_posts = get_posts(
		array(
			'post_type'      => 'inline_context_note',
			'posts_per_page' => -1,
			'post_status'    => 'any',
			'fields'         => 'ids',
		)
	);

	foreach ( $note_posts as $note_id ) {
		wp_delete_post( $note_id, true ); // Force delete.
	}

	// Delete all inline context categories (taxonomy terms).
	$terms = get_terms(
		array(
			'taxonomy'   => 'inline_context_category',
			'hide_empty' => false,
			'fields'     => 'ids',
		)
	);

	if ( ! is_wp_error( $terms ) ) {
		foreach ( $terms as $term_id ) {
			wp_delete_term( $term_id, 'inline_context_category' );
		}
	}

	// Delete plugin options.
	delete_option( 'inline_context_categories' );
	delete_option( 'inline_context_css_variables' );
	delete_option( 'inline_context_cleanup_on_uninstall' );

	// Delete all post meta related to inline context notes.
	$wpdb->query(
		"DELETE FROM {$wpdb->postmeta}
		WHERE meta_key IN ('usage_count', 'used_in_posts', 'is_reusable')"
	);
}

// Run the cleanup.
inline_context_uninstall_cleanup();
