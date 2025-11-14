<?php
/**
 * Frontend rendering and asset management for Inline Context plugin.
 *
 * @package InlineContext
 */

/**
 * Class Inline_Context_Frontend
 *
 * Handles frontend content rendering, noscript fallback generation,
 * and asset enqueuing for both editor and public-facing views.
 */
class Inline_Context_Frontend {

	/**
	 * Initialize frontend functionality by registering hooks.
	 */
	public function init() {
		// Content filters.
		add_filter( 'the_content', array( $this, 'add_noscript_content' ), 1 );
		add_filter( 'wp_kses_allowed_html', array( $this, 'allow_custom_attributes' ), 10, 2 );

		// Asset enqueuing.
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_editor_assets' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );
	}

	/**
	 * Add noscript content to posts for accessibility and SEO.
	 *
	 * Creates a notes section at the end of the content with all inline
	 * context notes displayed as a numbered list with back-to-text links.
	 *
	 * @param string $content The post content.
	 * @return string The modified post content with notes section.
	 */
	public function add_noscript_content( $content ) {
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

	/**
	 * Allow custom data attributes in post content for KSES filtering.
	 *
	 * @param array  $tags    Allowed HTML tags.
	 * @param string $context The context (post, data, etc.).
	 * @return array Modified allowed tags.
	 */
	public function allow_custom_attributes( $tags, $context ) {
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
	}

	/**
	 * Enqueue editor assets for the block editor.
	 */
	public function enqueue_editor_assets() {
		// Use generated asset metadata for dependencies and versioning.
		$asset_file = __DIR__ . '/../build/index.asset.php';
		// phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingVariable -- Including generated asset file.
		$asset = file_exists( $asset_file ) ? include_once $asset_file : array(
			'dependencies' => array( 'wp-rich-text', 'wp-element', 'wp-components', 'wp-block-editor', 'wp-i18n' ),
			'version'      => filemtime( dirname( __DIR__ ) . '/build/index.js' ),
		);

		// Use plugin version for production, filemtime for development.
		$version = defined( 'WP_DEBUG' ) && WP_DEBUG && isset( $asset['version'] )
			? filemtime( dirname( __DIR__ ) . '/build/index.js' )
			: INLINE_CONTEXT_VERSION;

		wp_enqueue_script(
			'jooplaan-inline-context',
			plugins_url( 'build/index.js', dirname( __FILE__ ) ),
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
			wp_set_script_translations( 'jooplaan-inline-context', 'inline-context', plugin_dir_path( dirname( __FILE__ ) ) . 'languages' );
		}

		wp_enqueue_style(
			'jooplaan-inline-context',
			plugins_url( 'build/index.css', dirname( __FILE__ ) ),
			array(),
			defined( 'WP_DEBUG' ) && WP_DEBUG
				? filemtime( dirname( __DIR__ ) . '/build/index.css' )
				: INLINE_CONTEXT_VERSION
		);
	}

	/**
	 * Enqueue frontend assets for public-facing pages.
	 */
	public function enqueue_frontend_assets() {
		// Enqueue bundled frontend JS with asset metadata.
		$frontend_asset_file = dirname( __DIR__ ) . '/build/frontend.asset.php';
		// phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingVariable -- Including generated asset file.
		$frontend_asset = file_exists( $frontend_asset_file ) ? include_once $frontend_asset_file : array(
			'dependencies' => array(),
			'version'      => filemtime( dirname( __DIR__ ) . '/build/frontend.js' ),
		);

		// Add wp-hooks as a dependency for filter support.
		$dependencies = array_merge( $frontend_asset['dependencies'], array( 'wp-hooks' ) );

		// Use plugin version for production, filemtime for development.
		$version = defined( 'WP_DEBUG' ) && WP_DEBUG
			? filemtime( dirname( __DIR__ ) . '/build/frontend.js' )
			: INLINE_CONTEXT_VERSION;

		wp_enqueue_script(
			'jooplaan-inline-context-frontend',
			plugins_url( 'build/frontend.js', dirname( __FILE__ ) ),
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
			plugins_url( 'build/style-index.css', dirname( __FILE__ ) ),
			array(),
			defined( 'WP_DEBUG' ) && WP_DEBUG
				? filemtime( dirname( __DIR__ ) . '/build/style-index.css' )
				: INLINE_CONTEXT_VERSION
		);
	}
}
