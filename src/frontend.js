import DOMPurify from 'dompurify';

document.addEventListener( 'DOMContentLoaded', () => {
	const revealedClass = 'wp-inline-context--open';

	const decodeEntities = ( str ) => {
		if ( ! str ) return '';
		const txt = document.createElement( 'textarea' );
		txt.innerHTML = str;
		return txt.value;
	};

	// Configure DOMPurify to allow the small subset used by our Quill config
	const ALLOWED_TAGS = [ 'p', 'strong', 'em', 'a', 'ol', 'ul', 'li', 'br' ];
	const ALLOWED_ATTR = [ 'href', 'rel', 'target' ];

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
		return DOMPurify.sanitize( html, {
			ALLOWED_TAGS,
			ALLOWED_ATTR,
			ALLOW_ARIA_ATTR: true,
			RETURN_TRUSTED_TYPE: false,
		} );
	};

	// Progressive enhancement: ensure keyboard accessibility
	// - Only add tabindex when href is missing (to avoid changing tab order unnecessarily)
	// - Ensure role and aria-expanded have sensible defaults
	for ( const trigger of document.querySelectorAll( '.wp-inline-context' ) ) {
		if ( ! trigger.hasAttribute( 'href' ) ) {
			if ( ! trigger.hasAttribute( 'tabindex' ) ) {
				trigger.setAttribute( 'tabindex', '0' );
			}
			if ( ! trigger.hasAttribute( 'role' ) ) {
				trigger.setAttribute( 'role', 'button' );
			}
		}
		if ( ! trigger.hasAttribute( 'aria-expanded' ) ) {
			trigger.setAttribute( 'aria-expanded', 'false' );
		}
	}

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
			console.warn( 'Inline context trigger missing anchor ID:', trigger );
			return;
		}
		const noteId = `note-${ anchorId }`;

		const span = document.createElement( 'span' );
		span.className = 'wp-inline-context-inline';
		// Use sanitized HTML for Quill content; for legacy plain text, insert as textContent
		const isQuillContent =
			hiddenContent.includes( '<p>' ) ||
			hiddenContent.includes( '<strong>' ) ||
			hiddenContent.includes( '<em>' );
		if ( isQuillContent ) {
			span.innerHTML = sanitizeHtml( hiddenContent );
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
		const trigger = document.querySelector( `[data-anchor-id="${ anchorId }"]` );
		
		if ( trigger ) {
			toggleNote( trigger );
			// Scroll to the trigger element for better user experience
			setTimeout( () => {
				trigger.scrollIntoView( { behavior: 'smooth', block: 'center' } );
			}, 100 );
		}
	};

	// Check for hash on initial load
	autoOpenFromHash();

	// Also check when hash changes (e.g., user clicks a link with hash)
	window.addEventListener( 'hashchange', autoOpenFromHash );
} );
