/**
 * Editor Keyboard Shortcuts Hook
 *
 * Provides keyboard shortcuts for inline context management in the block editor:
 * - Cmd+Shift+I: Insert new inline context (requires selection)
 * - Cmd+Shift+K: Edit existing context at cursor
 *
 * @package
 */

import { useEffect } from '@wordpress/element';
import { isCaretInFormat } from '../utils/format-navigation';

/**
 * Custom hook for editor-level keyboard shortcuts
 *
 * Attaches document-level keyboard event listeners when the popover is closed.
 * Shortcuts are disabled when the popover is open to avoid conflicts with
 * popover-specific shortcuts (Cmd+Enter to save, Escape to close).
 *
 * @param {Object}   params          - Hook parameters
 * @param {Object}   params.value    - Rich text value object from WordPress
 * @param {Function} params.onChange - Rich text onChange handler
 * @param {Function} params.onToggle - Function to toggle popover open/closed
 * @param {boolean}  params.isOpen   - Whether popover is currently open
 *
 * @example
 * useEditorKeyboardShortcuts({
 *   value,
 *   onChange,
 *   onToggle: toggle,
 *   isOpen
 * });
 */
export const useEditorKeyboardShortcuts = ( {
	value,
	onChange,
	onToggle,
	isOpen,
} ) => {
	useEffect( () => {
		// Only attach listeners when popover is closed
		// When open, popover shortcuts (Cmd+Enter, Escape) take priority
		if ( isOpen ) {
			return;
		}

		const handleKeyDown = ( e ) => {
			// Cmd+Shift+I: Insert inline context
			// Opens popover if text is selected
			if (
				( e.metaKey || e.ctrlKey ) &&
				e.shiftKey &&
				e.key.toLowerCase() === 'i'
			) {
				e.preventDefault();

				const { start, end } = value;
				if ( start < end ) {
					// Text is selected - open popover
					onToggle();
				}
				// No selection - do nothing (silent fail)

				return;
			}

			// Cmd+Shift+K: Edit existing context under cursor
			// Opens popover if cursor is inside an inline context format
			if (
				( e.metaKey || e.ctrlKey ) &&
				e.shiftKey &&
				e.key.toLowerCase() === 'k'
			) {
				e.preventDefault();

				const { start } = value;
				if ( isCaretInFormat( value, start ) && ! isOpen ) {
					// Cursor is inside a format - open popover to edit
					onToggle();
				}
				// Cursor not in format - do nothing (silent fail)
			}
		};

		// Attach listener
		document.addEventListener( 'keydown', handleKeyDown );

		// Cleanup on unmount or when dependencies change
		return () => document.removeEventListener( 'keydown', handleKeyDown );
	}, [ value, onChange, onToggle, isOpen ] );
};
