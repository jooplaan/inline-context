/**
 * Custom hooks for inline context editor
 */

import { useEffect, useState, useCallback } from '@wordpress/element';
import { applyFormat } from '@wordpress/rich-text';
import { hasDuplicateAnchorId, generateAnchorId } from '../utils/anchor';

/**
 * Hook to auto-fix duplicate anchor IDs
 *
 * @param {Object}   activeFormat - The active format object
 * @param {Object}   value        - The rich text value
 * @param {Function} onChange     - Change handler
 */
export const useAnchorIdDuplicateCheck = ( activeFormat, value, onChange ) => {
	useEffect( () => {
		if ( ! activeFormat?.attributes?.[ 'data-anchor-id' ] ) {
			return;
		}

		const currentId = activeFormat.attributes[ 'data-anchor-id' ];

		if ( hasDuplicateAnchorId( currentId ) ) {
			const uniqueId = generateAnchorId();

			onChange(
				applyFormat( value, {
					type: 'jooplaan/inline-context',
					attributes: {
						...activeFormat.attributes,
						'data-anchor-id': uniqueId,
						id: `trigger-${ uniqueId }`,
					},
				} )
			);
		}
	}, [ activeFormat, onChange, value ] );
};

/**
 * Hook for managing popover anchor (selection positioning)
 *
 * @param {boolean} isOpen - Whether the popover is open
 * @param {Object}  value  - The rich text value
 * @return {Object} Virtual anchor element
 */
export const usePopoverAnchor = ( isOpen, value ) => {
	const [ anchor, setAnchor ] = useState();

	useEffect( () => {
		if ( ! isOpen ) {
			return;
		}

		const getSelectionAnchor = () => {
			// We need global getSelection here as we're working with rich text selection
			// eslint-disable-next-line @wordpress/no-global-get-selection
			const sel = window.getSelection?.();
			if ( ! sel || sel.rangeCount === 0 ) {
				return undefined;
			}
			const range = sel.getRangeAt( 0 ).cloneRange();
			try {
				if ( range.collapsed ) {
					// Insert a zero-width marker to measure caret position
					const marker = document.createElement( 'span' );
					marker.appendChild( document.createTextNode( '\u200b' ) );
					range.insertNode( marker );
					const rect = marker.getBoundingClientRect();
					marker.remove();
					return rect;
				}
				return range.getBoundingClientRect();
			} catch ( error ) {
				// eslint-disable-next-line no-console
				console.warn( 'Failed to get selection anchor:', error );
				return undefined;
			}
		};

		const rect = getSelectionAnchor();
		if ( ! rect ) {
			setAnchor( undefined );
			return;
		}

		// Create a virtual element compatible with Floating UI/Popover
		const virtualEl = {
			getBoundingClientRect: () => rect,
		};
		setAnchor( virtualEl );
	}, [ isOpen, value?.start, value?.end ] );

	return anchor;
};

/**
 * Hook for keyboard shortcuts in the popover
 *
 * @param {boolean}  isOpen  - Whether the popover is open
 * @param {Function} onSave  - Save handler (Cmd/Ctrl+Enter)
 * @param {Function} onClose - Close handler (Escape)
 */
export const usePopoverKeyboardShortcuts = ( isOpen, onSave, onClose ) => {
	useEffect( () => {
		if ( ! isOpen ) {
			return;
		}

		const onKeyDown = ( e ) => {
			if ( ( e.metaKey || e.ctrlKey ) && e.key === 'Enter' ) {
				e.preventDefault();
				onSave();
			}
			if ( e.key === 'Escape' ) {
				e.preventDefault();
				onClose();
			}
		};

		document.addEventListener( 'keydown', onKeyDown );
		return () => document.removeEventListener( 'keydown', onKeyDown );
	}, [ isOpen, onSave, onClose ] );
};

/**
 * Hook to sync editor content when popover opens or format changes
 *
 * @param {boolean}  isOpen        - Whether the popover is open
 * @param {Object}   activeFormat  - The active format object
 * @param {Function} setText       - Text setter
 * @param {Function} setCategoryId - Category ID setter
 */
export const useSyncEditorContent = (
	isOpen,
	activeFormat,
	setText,
	setCategoryId
) => {
	useEffect( () => {
		if ( ! isOpen ) {
			return;
		}
		const content =
			activeFormat?.attributes?.[ 'data-inline-context' ] || '';
		const categoryIdFromHtml =
			activeFormat?.attributes?.[ 'data-category-id' ] || '';
		
		setText( content );
		
		// Convert term ID from HTML back to slug for CategorySelector
		if ( categoryIdFromHtml ) {
			const cats = window.inlineContextData?.categories || {};
			const category = Object.values( cats ).find(
				( cat ) => cat.id.toString() === categoryIdFromHtml.toString()
			);
			if ( category ) {
				setCategoryId( category.slug );
			} else {
				// Fallback: might be an old slug-based value
				setCategoryId( categoryIdFromHtml );
			}
		} else {
			setCategoryId( '' );
		}
		// Deliberately using complex expressions in deps to track specific attribute changes
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, [
		isOpen,
		activeFormat?.attributes?.[ 'data-inline-context' ],
		activeFormat?.attributes?.[ 'data-category-id' ],
		setText,
		setCategoryId,
	] );
};

/**
 * Hook for copy link functionality with status management
 *
 * @param {Function} copyFunction - The async function that performs the copy
 * @return {Object} { status, copyWithStatus }
 */
export const useCopyLinkStatus = ( copyFunction ) => {
	const [ status, setStatus ] = useState( 'idle' ); // 'idle', 'copying', 'copied'

	const copyWithStatus = useCallback( async () => {
		setStatus( 'copying' );

		await copyFunction(
			() => {
				setStatus( 'copied' );
				setTimeout( () => setStatus( 'idle' ), 2000 );
			},
			() => {
				setStatus( 'idle' );
			}
		);
	}, [ copyFunction ] );

	return { status, copyWithStatus };
};
