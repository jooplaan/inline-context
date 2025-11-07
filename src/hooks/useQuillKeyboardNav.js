/**
 * Custom hook for keyboard navigation in the Quill editor popover
 */

import { useCallback } from '@wordpress/element';

export const useQuillKeyboardNav = ( {
	showLinkInput,
	activeFormat,
	prevFocusRef,
	quillRef,
	addLinkButtonRef,
	copyLinkButtonRef,
	urlInputRef,
	linkTextInputRef,
	insertLinkButtonRef,
	linkCancelButtonRef,
	removeRef,
	cancelRef,
	saveRef,
} ) => {
	// Handle Tab from Quill editor
	const handleEditorKeyDownCapture = useCallback(
		( e ) => {
			if ( e.key === 'Tab' ) {
				e.preventDefault();
				if ( e.shiftKey ) {
					// Back to the toolbar button
					prevFocusRef.current?.focus?.();
				} else {
					// Forward to Add Link button
					addLinkButtonRef.current?.focus?.();
				}
			}
		},
		[ prevFocusRef, addLinkButtonRef ]
	);

	// Handle Tab navigation within action buttons
	const handleActionButtonsKeyDown = useCallback(
		( e, currentRef ) => {
			if ( e.key !== 'Tab' ) {
				return;
			}
			e.preventDefault();

			// Build tab order based on current state
			const tabOrder = [
				addLinkButtonRef,
				...( activeFormat?.attributes?.[ 'data-anchor-id' ]
					? [ copyLinkButtonRef ]
					: [] ),
				...( showLinkInput
					? [
							urlInputRef,
							linkTextInputRef,
							insertLinkButtonRef,
							linkCancelButtonRef,
					  ]
					: [] ),
				...( activeFormat ? [ removeRef ] : [] ),
				cancelRef,
				saveRef,
			].filter( ( ref ) => ref.current );

			const currentIndex = tabOrder.indexOf( currentRef );

			if ( e.shiftKey ) {
				// Shift+Tab: go backwards
				if ( currentIndex > 0 ) {
					tabOrder[ currentIndex - 1 ].current?.focus?.();
				} else {
					// From first button, go back to editor
					quillRef.current?.focus?.();
				}
			} else if ( currentIndex < tabOrder.length - 1 ) {
				// Tab: go forwards
				tabOrder[ currentIndex + 1 ].current?.focus?.();
			} else {
				// From last button, stay on it
				tabOrder[ currentIndex ].current?.focus?.();
			}
		},
		[
			showLinkInput,
			activeFormat,
			quillRef,
			addLinkButtonRef,
			copyLinkButtonRef,
			urlInputRef,
			linkTextInputRef,
			insertLinkButtonRef,
			linkCancelButtonRef,
			removeRef,
			cancelRef,
			saveRef,
		]
	);

	return {
		handleEditorKeyDownCapture,
		handleActionButtonsKeyDown,
	};
};
