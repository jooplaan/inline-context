import DOMPurify from 'dompurify';

document.addEventListener( 'DOMContentLoaded', () => {
	const { applyFilters } = wp.hooks || { applyFilters: ( name, val ) => val };

	// Allow developers to customize the CSS class used for revealed notes
	const revealedClass = applyFilters(
		'inline_context_revealed_class',
		'wp-inline-context--open'
	);

	const decodeEntities = ( str ) => {
		if ( ! str ) return '';
		const txt = document.createElement( 'textarea' );
		txt.innerHTML = str;
		return txt.value;
	};

	// Configure DOMPurify - allow developers to extend allowed tags/attributes
	const ALLOWED_TAGS = applyFilters( 'inline_context_allowed_tags', [
		'p',
		'strong',
		'em',
		'a',
		'ol',
		'ul',
		'li',
		'br',
	] );
	const ALLOWED_ATTR = applyFilters( 'inline_context_allowed_attributes', [
		'href',
		'rel',
		'target',
	] );

	// Add a hook to harden links (no javascript:, add rel)
	if ( typeof DOMPurify.addHook === 'function' ) {
		DOMPurify.addHook( 'afterSanitizeAttributes', ( node ) => {
			if ( node.nodeName && node.nodeName.toLowerCase() === 'a' ) {
				const href = node.getAttribute( 'href' ) || '';
				// Strip dangerous protocols
				if ( /^\s*javascript:/i.test( href ) ) {
					node.removeAttribute( 'href' );
				}
				// Add rel to external links
				if ( /^https?:\/\//i.test( href ) ) {
					const currentRel = (
						node.getAttribute( 'rel' ) || ''
					).toLowerCase();
					const tokens = new Set(
						( currentRel ? currentRel.split( /\s+/ ) : [] ).concat(
							[ 'noopener', 'noreferrer' ]
						)
					);
					node.setAttribute(
						'rel',
						Array.from( tokens ).join( ' ' )
					);
				}
			}
		} );
	}

	const sanitizeHtml = ( html ) => {
		if ( ! html ) return '';
		// Allow developers to modify HTML before sanitization
		const filteredHtml = applyFilters(
			'inline_context_pre_sanitize_html',
			html
		);
		const sanitized = DOMPurify.sanitize( filteredHtml, {
			ALLOWED_TAGS,
			ALLOWED_ATTR,
			ALLOW_ARIA_ATTR: true,
			RETURN_TRUSTED_TYPE: false,
		} );
		// Allow developers to modify HTML after sanitization
		return applyFilters( 'inline_context_post_sanitize_html', sanitized );
	};

	// Progressive enhancement: ensure proper accessibility attributes
	for ( const trigger of document.querySelectorAll( '.wp-inline-context' ) ) {
		// Ensure aria-expanded is set for all triggers
		if ( ! trigger.hasAttribute( 'aria-expanded' ) ) {
			trigger.setAttribute( 'aria-expanded', 'false' );
		}
		// Ensure role="button" is set for accessibility
		if ( ! trigger.hasAttribute( 'role' ) ) {
			trigger.setAttribute( 'role', 'button' );
		}
	}

	// Process links in notes to set appropriate target behavior
	const processLinksInNote = ( noteElement ) => {
		// Allow developers to customize link behavior
		const shouldProcessLinks = applyFilters(
			'inline_context_process_links',
			true,
			noteElement
		);
		if ( ! shouldProcessLinks ) return;

		const links = noteElement.querySelectorAll( 'a[href]' );
		const currentDomain = window.location.hostname;

		for ( const link of links ) {
			const href = link.getAttribute( 'href' );

			// Skip if not a valid URL
			if ( ! href ) continue;

			try {
				// Handle relative URLs (internal links)
				if (
					href.startsWith( '/' ) ||
					href.startsWith( '#' ) ||
					href.startsWith( '?' )
				) {
					// Internal relative link - apply filter
					const internalTarget = applyFilters(
						'inline_context_internal_link_target',
						'_self',
						href,
						link
					);
					if ( internalTarget === '_self' ) {
						link.removeAttribute( 'target' );
					} else {
						link.setAttribute( 'target', internalTarget );
					}
					continue;
				}

				// Handle absolute URLs
				const url = new URL( href );

				if ( url.hostname === currentDomain ) {
					// Internal absolute link - apply filter
					const internalTarget = applyFilters(
						'inline_context_internal_link_target',
						'_self',
						href,
						link
					);
					if ( internalTarget === '_self' ) {
						link.removeAttribute( 'target' );
					} else {
						link.setAttribute( 'target', internalTarget );
					}
				} else {
					// External link - apply filter for target
					const externalTarget = applyFilters(
						'inline_context_external_link_target',
						'_blank',
						href,
						link
					);
					link.setAttribute( 'target', externalTarget );

					// Ensure security attributes are present for external links
					const rel = link.getAttribute( 'rel' ) || '';
					const relTokens = new Set(
						rel.split( ' ' ).filter( Boolean )
					);
					relTokens.add( 'noopener' );
					relTokens.add( 'noreferrer' );
					link.setAttribute(
						'rel',
						Array.from( relTokens ).join( ' ' )
					);
				}
			} catch ( error ) {
				// Invalid URL - treat as internal link
				link.removeAttribute( 'target' );
			}
		}
	};

	const toggleNote = ( trigger ) => {
		if ( ! trigger ) return;

		// If already open, close and clean ARIA state
		const existing = trigger.nextElementSibling;
		if ( existing?.classList.contains( 'wp-inline-context-inline' ) ) {
			existing.remove();
			trigger.classList.remove( revealedClass );
			trigger.setAttribute( 'aria-expanded', 'false' );
			trigger.removeAttribute( 'aria-describedby' );
			trigger.removeAttribute( 'aria-controls' );
			return;
		}

		// Build the new inline note
		const hiddenContent = trigger.dataset.inlineContext || '';
		if ( ! hiddenContent ) return;

		// Use the anchor ID to create the note ID
		const anchorId = trigger.dataset.anchorId;
		if ( ! anchorId ) {
			// Skip triggers without anchor IDs (shouldn't happen in v1.0+)
			return;
		}
		const noteId = `note-${ anchorId }`;

		const span = document.createElement( 'span' );
		// Allow developers to customize the note container class
		const noteClass = applyFilters(
			'inline_context_note_class',
			'wp-inline-context-inline',
			trigger
		);
		span.className = noteClass;

		// Use sanitized HTML for Quill content; for legacy plain text, insert as textContent
		const isQuillContent =
			hiddenContent.includes( '<p>' ) ||
			hiddenContent.includes( '<strong>' ) ||
			hiddenContent.includes( '<em>' );
		if ( isQuillContent ) {
			span.innerHTML = sanitizeHtml( hiddenContent );
			// Process links for proper target behavior
			processLinksInNote( span );
		} else {
			span.textContent = decodeEntities( hiddenContent );
		}
		span.setAttribute( 'role', 'note' );
		span.id = noteId;

		trigger.after( span );
		trigger.classList.add( revealedClass );
		trigger.setAttribute( 'aria-expanded', 'true' );
		trigger.setAttribute( 'aria-describedby', noteId );
		trigger.setAttribute( 'aria-controls', noteId );
	};

	// Handle click events
	document.body.addEventListener( 'click', ( e ) => {
		const trigger = e.target.closest( '.wp-inline-context' );
		if ( ! trigger ) return;
		e.preventDefault();
		toggleNote( trigger );
	} );

	// Handle keyboard events (Enter or Space)
	document.body.addEventListener( 'keydown', ( e ) => {
		const trigger = e.target.closest( '.wp-inline-context' );
		if ( ! trigger ) return;

		if ( e.key === 'Enter' || e.key === ' ' ) {
			e.preventDefault();
			toggleNote( trigger );
		}
	} );

	// Auto-open note if URL has a matching anchor hash
	const autoOpenFromHash = () => {
		const hash = window.location.hash;
		if ( ! hash || ! hash.startsWith( '#context-note-' ) ) return;

		const anchorId = hash.substring( 1 ); // Remove the '#'
		const trigger = document.querySelector(
			`[data-anchor-id="${ anchorId }"]`
		);

		if ( trigger ) {
			toggleNote( trigger );
			// Scroll to the trigger element for better user experience
			setTimeout( () => {
				trigger.scrollIntoView( {
					behavior: 'smooth',
					block: 'center',
				} );
			}, 100 );
		}
	};

	// Check for hash on initial load
	autoOpenFromHash();

	// Also check when hash changes (e.g., user clicks a link with hash)
	window.addEventListener( 'hashchange', autoOpenFromHash );
} );
