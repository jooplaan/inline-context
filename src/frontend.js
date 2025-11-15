import DOMPurify from 'dompurify';

document.addEventListener( 'DOMContentLoaded', () => {
	// Add 'js' class to body to enable JS-specific styles
	document.body.classList.add( 'js' );

	const { applyFilters } = wp.hooks || { applyFilters: ( name, val ) => val };

	// Get settings from localized data
	const { categories = {} } = window.inlineContextData || {};

	// Function to get current display mode (allows dynamic switching)
	const getDisplayMode = () => {
		const defaultMode =
			window.inlineContextData?.displayMode || 'inline';
		return applyFilters( 'inlineContext.displayMode', defaultMode );
	};

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

	// Add or update category icon for a trigger
	const addCategoryIcon = ( trigger, categoryId, isOpen ) => {
		// Find category by ID (categoryId is stored as term_id)
		const category = Object.values( categories ).find(
			( cat ) => cat.id && cat.id.toString() === categoryId.toString()
		);
		if ( ! category ) return;

		// Remove existing icon if present
		const existingIcon = trigger.querySelector(
			'.wp-inline-context-category-icon'
		);
		if ( existingIcon ) {
			existingIcon.remove();
		}

		// Create icon element
		const icon = document.createElement( 'span' );
		icon.className = `wp-inline-context-category-icon dashicons ${
			isOpen ? category.icon_open : category.icon_closed
		}`;
		icon.style.color = category.color;
		icon.setAttribute( 'aria-hidden', 'true' );

		// Insert icon at the end of the trigger (superscript style)
		trigger.appendChild( icon );
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

		// Add category icon if category is set
		const categoryId = trigger.dataset.categoryId;
		if ( categoryId ) {
			const category = Object.values( categories ).find(
				( cat ) => cat.id && cat.id.toString() === categoryId.toString()
			);
			if ( category ) {
				addCategoryIcon( trigger, categoryId, false );
			}
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

	/**
	 * Toggle tooltip display (accessibility-first implementation)
	 *
	 * @param {HTMLElement} trigger - The trigger element
	 */
	const toggleTooltip = ( trigger ) => {
		if ( ! trigger ) return;

		const tooltipId = `tooltip-${ trigger.dataset.anchorId }`;
		let tooltip = document.getElementById( tooltipId );

		// If tooltip exists, remove it (toggle off)
		if ( tooltip ) {
			tooltip.remove();
			trigger.classList.remove( revealedClass );
			trigger.setAttribute( 'aria-expanded', 'false' );
			trigger.removeAttribute( 'aria-describedby' );

			// Clean up event listeners stored on the trigger
			if ( trigger._handleOutsideClick ) {
				document.removeEventListener(
					'click',
					trigger._handleOutsideClick
				);
				delete trigger._handleOutsideClick;
			}
			if ( trigger._handleEscape ) {
				document.removeEventListener(
					'keydown',
					trigger._handleEscape
				);
				delete trigger._handleEscape;
			}

			// Update icon to closed state
			const categoryId = trigger.dataset.categoryId;
			if ( categoryId ) {
				const category = Object.values( categories ).find(
					( cat ) =>
						cat.id && cat.id.toString() === categoryId.toString()
				);
				if ( category ) {
					addCategoryIcon( trigger, categoryId, false );
				}
			}
			return;
		}

		// Build tooltip content
		const hiddenContent = trigger.dataset.inlineContext || '';
		if ( ! hiddenContent ) return;

		const anchorId = trigger.dataset.anchorId;
		if ( ! anchorId ) return;

		// Create tooltip element
		tooltip = document.createElement( 'div' );
		tooltip.id = tooltipId;
		tooltip.className = 'wp-inline-context-tooltip';
		tooltip.setAttribute( 'role', 'tooltip' );
		tooltip.setAttribute( 'tabindex', '-1' ); // Make focusable but not in tab order

		// Add content
		const isQuillContent =
			hiddenContent.includes( '<p>' ) ||
			hiddenContent.includes( '<strong>' ) ||
			hiddenContent.includes( '<em>' );
		if ( isQuillContent ) {
			tooltip.innerHTML = sanitizeHtml( hiddenContent );
			processLinksInNote( tooltip );
		} else {
			tooltip.textContent = decodeEntities( hiddenContent );
		}

		// Add close button for accessibility
		const closeBtn = document.createElement( 'button' );
		closeBtn.className = 'wp-inline-context-tooltip-close';
		closeBtn.setAttribute( 'aria-label', 'Close tooltip' );
		closeBtn.innerHTML = '&times;';
		closeBtn.addEventListener( 'click', ( e ) => {
			e.stopPropagation();
			toggleTooltip( trigger );
			trigger.focus();
		} );
		tooltip.appendChild( closeBtn );

		// Position tooltip
		document.body.appendChild( tooltip );
		positionTooltip( tooltip, trigger );

		// Update trigger state
		trigger.classList.add( revealedClass );
		trigger.setAttribute( 'aria-expanded', 'true' );
		trigger.setAttribute( 'aria-describedby', tooltipId );

		// Update icon to open state
		const categoryId = trigger.dataset.categoryId;
		if ( categoryId ) {
			const category = Object.values( categories ).find(
				( cat ) => cat.id && cat.id.toString() === categoryId.toString()
			);
			if ( category ) {
				addCategoryIcon( trigger, categoryId, true );
			}
		}

		// Auto-close on outside click
		const handleOutsideClick = ( e ) => {
			if (
				! tooltip.contains( e.target ) &&
				! trigger.contains( e.target )
			) {
				toggleTooltip( trigger );
			}
		};
		// Store reference on trigger for cleanup
		trigger._handleOutsideClick = handleOutsideClick;
		setTimeout( () => {
			document.addEventListener( 'click', handleOutsideClick );
		}, 0 );

		// Close on Escape key
		const handleEscape = ( e ) => {
			if ( e.key === 'Escape' ) {
				toggleTooltip( trigger );
				trigger.focus();
			}
		};
		// Store reference on trigger for cleanup
		trigger._handleEscape = handleEscape;
		document.addEventListener( 'keydown', handleEscape );

		// Set focus to tooltip for keyboard navigation of links inside
		setTimeout( () => {
			tooltip.focus();
		}, 100 );
	};

	/**
	 * Position tooltip with smart viewport boundary detection
	 *
	 * @param {HTMLElement} tooltip - The tooltip element
	 * @param {HTMLElement} trigger - The trigger element
	 */
	const positionTooltip = ( tooltip, trigger ) => {
		const triggerRect = trigger.getBoundingClientRect();
		const tooltipRect = tooltip.getBoundingClientRect();
		const viewportWidth = window.innerWidth;
		const scrollX =
			window.pageXOffset || document.documentElement.scrollLeft;
		const scrollY =
			window.pageYOffset || document.documentElement.scrollTop;

		const spacing = 10; // Gap between trigger and tooltip

		// Default: position above the trigger, centered
		let top = triggerRect.top + scrollY - tooltipRect.height - spacing;
		let left =
			triggerRect.left +
			scrollX +
			triggerRect.width / 2 -
			tooltipRect.width / 2;

		// Check if tooltip goes above viewport, flip to below
		if ( triggerRect.top - tooltipRect.height - spacing < 0 ) {
			top = triggerRect.bottom + scrollY + spacing;
			tooltip.classList.add( 'wp-inline-context-tooltip--below' );
		} else {
			tooltip.classList.add( 'wp-inline-context-tooltip--above' );
		}

		// Check horizontal boundaries
		if ( left < scrollX + 10 ) {
			left = scrollX + 10; // 10px from left edge
			tooltip.classList.add( 'wp-inline-context-tooltip--left' );
		} else if ( left + tooltipRect.width > scrollX + viewportWidth - 10 ) {
			left = scrollX + viewportWidth - tooltipRect.width - 10; // 10px from right edge
			tooltip.classList.add( 'wp-inline-context-tooltip--right' );
		}

		tooltip.style.top = `${ top }px`;
		tooltip.style.left = `${ left }px`;
	};

	const toggleNote = ( trigger ) => {
		if ( ! trigger ) return;

		// Get current display mode dynamically (allows switching)
		const currentDisplayMode = getDisplayMode();

		// Use tooltip mode if enabled
		if ( currentDisplayMode === 'tooltip' ) {
			toggleTooltip( trigger );
			return;
		}

		// Check if note already exists (toggle off)
		const existing = trigger.nextElementSibling;
		if ( existing?.classList.contains( 'wp-inline-context-inline' ) ) {
			existing.remove();
			trigger.classList.remove( revealedClass );
			trigger.setAttribute( 'aria-expanded', 'false' );
			trigger.removeAttribute( 'aria-describedby' );
			trigger.removeAttribute( 'aria-controls' );

			// Update icon to closed state
			const categoryId = trigger.dataset.categoryId;
			if ( categoryId ) {
				const category = Object.values( categories ).find(
					( cat ) =>
						cat.id && cat.id.toString() === categoryId.toString()
				);
				if ( category ) {
					addCategoryIcon( trigger, categoryId, false );
				}
			}
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
		span.setAttribute( 'tabindex', '-1' ); // Make focusable but not in tab order
		span.id = noteId;

		trigger.after( span );
		trigger.classList.add( revealedClass );
		trigger.setAttribute( 'aria-expanded', 'true' );
		trigger.setAttribute( 'aria-describedby', noteId );
		trigger.setAttribute( 'aria-controls', noteId );

		// Update icon to open state
		const categoryId = trigger.dataset.categoryId;
		if ( categoryId ) {
			const category = Object.values( categories ).find(
				( cat ) => cat.id && cat.id.toString() === categoryId.toString()
			);
			if ( category ) {
				addCategoryIcon( trigger, categoryId, true );
			}
		}

		// Set focus to note for keyboard navigation of links inside
		setTimeout( () => {
			span.focus();
		}, 100 );
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
			// Create the inline note if it doesn't exist
			const existing = trigger.nextElementSibling;
			if (
				! existing?.classList.contains( 'wp-inline-context-inline' )
			) {
				toggleNote( trigger );
			}

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
