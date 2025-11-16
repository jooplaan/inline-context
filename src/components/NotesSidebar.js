/**
 * Notes Sidebar Panel Component
 *
 * Displays all inline context notes in the current post with quick navigation.
 */

import { __ } from '@wordpress/i18n';
import { PanelBody, PanelRow, Button } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { useEffect, useState } from '@wordpress/element';

/**
 * Calculate luminance of a color to determine if text should be light or dark
 *
 * @param {string} color - Hex color code (e.g., '#FF0000')
 * @return {string} 'light' or 'dark' text color
 */
const getContrastTextColor = ( color ) => {
	if ( ! color ) {
		return '#000000';
	}

	// Remove # if present
	const hex = color.replace( '#', '' );

	// Convert to RGB
	const r = Number.parseInt( hex.substring( 0, 2 ), 16 );
	const g = Number.parseInt( hex.substring( 2, 4 ), 16 );
	const b = Number.parseInt( hex.substring( 4, 6 ), 16 );

	// Calculate relative luminance using WCAG formula
	const luminance = ( 0.299 * r + 0.587 * g + 0.114 * b ) / 255;

	// Return white for dark backgrounds, black for light backgrounds
	return luminance > 0.5 ? '#000000' : '#ffffff';
};

/**
 * Decode HTML entities
 *
 * @param {string} html - HTML string with entities
 * @return {string} Decoded string
 */
const decodeHtmlEntities = ( html ) => {
	const textarea = document.createElement( 'textarea' );
	textarea.innerHTML = html;
	return textarea.value;
};

/**
 * Extract inline context notes from post content
 *
 * @param {string} content - Post content HTML
 * @return {Array} Array of note objects
 */
const extractNotesFromContent = ( content ) => {
	if ( ! content ) {
		return [];
	}

	const notes = [];
	let counter = 1;

	// Use regex to find all inline context links
	const linkPattern =
		/<a[^>]*class="[^"]*wp-inline-context[^"]*"[^>]*>.*?<\/a>/gi;
	const matches = content.match( linkPattern );

	if ( ! matches ) {
		return [];
	}

	matches.forEach( ( linkHtml ) => {
		// Extract attributes using regex
		const getAttr = ( attr ) => {
			const regex = new RegExp( `${ attr }="([^"]*)"`, 'i' );
			const match = linkHtml.match( regex );
			return match ? match[ 1 ] : null;
		};

		const anchorId = getAttr( 'data-anchor-id' );
		const noteId = getAttr( 'data-note-id' );
		const categoryId = getAttr( 'data-category-id' );
		const noteContent = getAttr( 'data-inline-context' );

		// Extract link text (content between tags)
		const textMatch = linkHtml.match( />([^<]+)</i );
		const linkText = textMatch
			? decodeHtmlEntities( textMatch[ 1 ].trim() )
			: '';

		if ( anchorId ) {
			// Get excerpt from note content (first 60 chars)
			let excerpt = '';
			if ( noteContent ) {
				// Decode HTML entities first
				const decodedContent = decodeHtmlEntities( noteContent );
				// Remove HTML tags for excerpt
				const cleanText = decodedContent.replace( /<[^>]*>/g, '' );
				excerpt = cleanText.substring( 0, 60 ).trim();
				if ( cleanText.length > 60 ) {
					excerpt += '...';
				}
			}

			notes.push( {
				id: anchorId,
				noteId: noteId || null,
				categoryId: categoryId || null,
				linkText,
				excerpt,
				number: counter++,
			} );
		}
	} );

	return notes;
};

/**
 * Notes Sidebar Component
 */
const NotesSidebar = () => {
	const [ notes, setNotes ] = useState( [] );

	// Get current post content and categories
	const { postContent } = useSelect( ( select ) => {
		const { getEditedPostContent } = select( 'core/editor' );
		return {
			postContent: getEditedPostContent(),
		};
	}, [] );

	// Get categories from window (outside useSelect to avoid stale closure)
	/* global globalThis */
	const categories = globalThis.inlineContextData?.categories || {};

	// Update notes when content changes
	useEffect( () => {
		const extractedNotes = extractNotesFromContent( postContent );
		setNotes( extractedNotes );
	}, [ postContent ] );

	/**
	 * Scroll to a note in the editor
	 *
	 * @param {string} anchorId - The anchor ID to scroll to
	 */
	const scrollToNote = ( anchorId ) => {
		// Find the link in the editor
		const editorElement = document.querySelector(
			'.editor-styles-wrapper'
		);
		if ( ! editorElement ) {
			return;
		}

		const link = editorElement.querySelector(
			`a[data-anchor-id="${ anchorId }"]`
		);
		if ( link ) {
			// Scroll into view with smooth behavior
			link.scrollIntoView( {
				behavior: 'smooth',
				block: 'center',
			} );

			// Highlight the link temporarily
			link.classList.add( 'wp-inline-context-highlight' );
			setTimeout( () => {
				link.classList.remove( 'wp-inline-context-highlight' );
			}, 2000 );

			// Focus the link for keyboard users
			link.focus();
		}
	};

	/**
	 * Get category info for a note
	 *
	 * @param {string} categoryId - Category ID (numeric ID from data-category-id)
	 * @return {Object|null} Category object or null
	 */
	const getCategory = ( categoryId ) => {
		if ( ! categoryId || ! categories ) {
			return null;
		}

		// Categories object is keyed by slug, but data-category-id contains numeric ID
		// Find the category by matching the ID
		const categoryArray = Object.values( categories );
		const category = categoryArray.find(
			( cat ) => cat.id && cat.id.toString() === categoryId.toString()
		);

		return category || null;
	};

	if ( notes.length === 0 ) {
		return (
			<PanelBody
				title={ __( 'Inline Context Notes', 'inline-context' ) }
				initialOpen={ true }
			>
				<PanelRow>
					<p className="wp-inline-context-sidebar-empty">
						{ __(
							'No inline context notes in this post.',
							'inline-context'
						) }
					</p>
				</PanelRow>
			</PanelBody>
		);
	}

	return (
		<PanelBody
			title={ __( 'Inline Context Notes', 'inline-context' ) }
			initialOpen={ true }
		>
			<div className="wp-inline-context-sidebar-notes">
				<p className="wp-inline-context-sidebar-count">
					{ notes.length === 1
						? __( '1 note in this post', 'inline-context' )
						: /* translators: %d: number of notes */
						  __(
								'%d notes in this post',
								'inline-context'
						  ).replace( '%d', notes.length ) }
				</p>

				<ol className="wp-inline-context-sidebar-list">
					{ notes.map( ( note ) => {
						const category = getCategory( note.categoryId );
						return (
							<li
								key={ note.id }
								className="wp-inline-context-sidebar-item"
							>
								<div className="wp-inline-context-sidebar-content">
									{ category && (
										<span
											className="wp-inline-context-sidebar-category"
											style={ {
												backgroundColor: category.color,
												color: getContrastTextColor(
													category.color
												),
											} }
										>
											{ category.name }
										</span>
									) }
									<Button
										variant="link"
										className="wp-inline-context-sidebar-link"
										onClick={ () =>
											scrollToNote( note.id )
										}
									>
										{ note.linkText }
									</Button>
									{ note.excerpt && (
										<div className="wp-inline-context-sidebar-excerpt">
											{ note.excerpt }
										</div>
									) }
								</div>
							</li>
						);
					} ) }
				</ol>
			</div>
		</PanelBody>
	);
};

export default NotesSidebar;
