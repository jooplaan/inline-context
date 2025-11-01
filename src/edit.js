/**
 * React Quill editor component for WordPress inline context
 * Features:
 * - Rich text editing with bold, italic, links, and lists (no underline)
 * - Add new inline context to selected text
 * - Edit existing inline context
 * - Remove existing inline context
 * - WordPress-friendly styling and keyboard shortcuts
 */
import {
	useCallback,
	useEffect,
	useMemo,
	useRef,
	useState,
} from '@wordpress/element';
import { RichTextToolbarButton } from '@wordpress/block-editor';
import { Popover, Button, Flex, FlexItem } from '@wordpress/components';
import { applyFormat, removeFormat } from '@wordpress/rich-text';
import { __ } from '@wordpress/i18n';
import ReactQuill from 'react-quill';

// WordPress-friendly Quill configuration
const QUILL_MODULES = {
	toolbar: [
		[ 'bold', 'italic' ],
		[ 'link' ],
		[ { list: 'ordered' }, { list: 'bullet' } ],
		[ 'clean' ],
	],
};

const QUILL_FORMATS = [ 'bold', 'italic', 'link', 'list', 'bullet' ];

export default function Edit( { isActive, value, onChange } ) {
	const [ isOpen, setIsOpen ] = useState( false );
	const [ anchor, setAnchor ] = useState();
	const prevFocusRef = useRef( null );
	const rootRef = useRef( null );
	const popoverId = useMemo(
		() =>
			`inline-context-popover-${ Math.random()
				.toString( 36 )
				.slice( 2 ) }`,
		[]
	);
	const labelId = `${ popoverId }-label`;
	const cancelRef = useRef( null );
	const saveRef = useRef( null );
	const removeRef = useRef( null );
	const quillRef = useRef( null );

	const activeFormat = value.activeFormats?.find(
		( f ) => f.type === 'trybes/inline-context'
	);
	const remove = () => {
		onChange( removeFormat( value, 'trybes/inline-context' ) );
		setIsOpen( false );
		setTimeout( () => prevFocusRef.current?.focus?.(), 0 );
	};
	const currentText =
		activeFormat?.attributes?.[ 'data-inline-context' ] || '';
	const [ text, setText ] = useState( currentText );

	// Generate a unique ID for the anchor
	const generateAnchorId = () => {
		// Create a short, unique identifier
		const timestamp = Date.now().toString(36);
		const random = Math.random().toString(36).substring(2, 7);
		return `context-note-${timestamp}-${random}`;
	};

	// Apply the inline context to the current selection
	const apply = useCallback( () => {
		// Use existing ID if editing, or generate new one if creating
		const anchorId = activeFormat?.attributes?.['data-anchor-id'] || generateAnchorId();
		
		onChange(
			applyFormat( value, {
				type: 'trybes/inline-context',
				attributes: {
					'data-inline-context': text,
					'data-anchor-id': anchorId,
					href: `#${anchorId}`,
					role: 'button',
					'aria-expanded': 'false',
				},
			} )
		);
		setIsOpen( false );
		// Restore focus to the previously focused control for smooth keyboard workflow
		setTimeout( () => prevFocusRef.current?.focus?.(), 0 );
	}, [ onChange, text, value, activeFormat ] );

	const toggle = () => {
		// Remember the element that had focus before opening so we can restore it on close
		if ( ! isOpen ) {
			const doc = rootRef.current?.ownerDocument || document;
			prevFocusRef.current = doc.activeElement;
		}
		setIsOpen( ( prev ) => {
			const next = ! prev;
			if ( next ) {
				// Sync the editor with the currently selected inline context when opening
				const fmt = value.activeFormats?.find(
					( f ) => f.type === 'trybes/inline-context'
				);
				setText( fmt?.attributes?.[ 'data-inline-context' ] || '' );
			}
			return next;
		} );
	};

	// Compute a virtual element for the current selection so the Popover can anchor near the selected text
	useEffect( () => {
		if ( ! isOpen ) return;
		const getSelectionAnchor = () => {
			const view = rootRef.current?.ownerDocument?.defaultView || window;
			const sel = view.getSelection?.();
			if ( ! sel || sel.rangeCount === 0 ) return undefined;
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
		// Create a virtual element compatible with Floating UI/Popover expectations
		const virtualEl = {
			getBoundingClientRect: () => rect,
		};
		setAnchor( virtualEl );
	}, [ isOpen, value?.start, value?.end ] );

	// Also resync text if the active format changes while popover is open
	useEffect( () => {
		if ( ! isOpen ) return;
		setText( currentText );
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, [ activeFormat?.attributes?.[ 'data-inline-context' ] ] );

	// When opening, place focus inside the Quill editor for immediate typing
	useEffect( () => {
		if ( ! isOpen ) return;
		const t = setTimeout( () => {
			const inst = quillRef.current;
			if ( inst?.focus ) {
				inst.focus();
			} else if ( inst?.getEditor ) {
				inst.getEditor()?.focus?.();
			}
		}, 0 );
		return () => clearTimeout( t );
	}, [ isOpen ] );

	// Add handy keyboard shortcuts for the popover
	useEffect( () => {
		if ( ! isOpen ) return;
		const onKeyDown = ( e ) => {
			if ( ( e.metaKey || e.ctrlKey ) && e.key === 'Enter' ) {
				e.preventDefault();
				apply();
			}
			if ( e.key === 'Escape' ) {
				e.preventDefault();
				setIsOpen( false );
			}
		};
		document.addEventListener( 'keydown', onKeyDown );
		return () => document.removeEventListener( 'keydown', onKeyDown );
	}, [ isOpen, apply ] );



	// Allow Tab to move focus from the Quill editor to the first action button,
	// and Shift+Tab to return to the toolbar toggle button.
	const handleEditorKeyDownCapture = ( e ) => {
		if ( e.key === 'Tab' ) {
			e.preventDefault();
			if ( e.shiftKey ) {
				// Back to the toolbar button
				prevFocusRef.current?.focus?.();
			} else {
				// To the first available action (Remove if present, else Cancel, else Save)
				(
					removeRef.current ||
					cancelRef.current ||
					saveRef.current
				)?.focus?.();
			}
		}
	};

	// Handle React Quill editor changes
	const handleQuillChange = ( content ) => {
		setText( content );
	};

	return (
		<span ref={ rootRef }>
			<RichTextToolbarButton
				icon="editor-ol"
				title={ __( 'Inline Context', 'inline-context' ) }
				onClick={ toggle }
				isActive={ isActive }
				aria-expanded={ isOpen }
				aria-controls={ isOpen ? popoverId : undefined }
			/>

			{ isOpen && (
				<Popover
					id={ popoverId }
					anchor={ anchor }
					position="bottom center"
					focusOnMount="firstElement"
					role="dialog"
					aria-modal={ false }
					aria-labelledby={ labelId }
					onClose={ () => {
						setIsOpen( false );
						setTimeout( () => prevFocusRef.current?.focus?.(), 0 );
					} }
				>
					<div className="wp-reveal-popover wp-reveal-quill-editor">
						<div className="wp-reveal-quill-label" id={ labelId }>
							{ __( 'Inline Context', 'inline-context' ) }
						</div>
						<div onKeyDownCapture={ handleEditorKeyDownCapture }>
							<ReactQuill
								ref={ quillRef }
								value={ text }
								onChange={ handleQuillChange }
								modules={ QUILL_MODULES }
								formats={ QUILL_FORMATS }
								placeholder={ __(
									'Add inline context…',
									'inline-context'
								) }
								theme="snow"
							/>
						</div>
						<div className="wp-reveal-quill-help">
							{ __(
								'Use the toolbar above to format your inline context with bold, italic, links, and lists.',
								'inline-context'
							) }
						</div>
					</div>
					<Flex justify="space-between" align="center">
						<FlexItem>
							{ isActive && (
								<Button
									ref={ removeRef }
									variant="tertiary"
									isDestructive
									onClick={ remove }
								>
									{ __(
										'Remove Inline Context',
										'inline-context'
									) }
								</Button>
							) }
						</FlexItem>
						<FlexItem>
							<Flex gap={ 2 }>
								<Button
									ref={ cancelRef }
									variant="secondary"
									onClick={ () => setIsOpen( false ) }
								>
									{ __( 'Cancel', 'inline-context' ) }
								</Button>
								<Button
									ref={ saveRef }
									variant="primary"
									onClick={ apply }
								>
									{ __( 'Save', 'inline-context' ) }
								</Button>
							</Flex>
						</FlexItem>
					</Flex>
				</Popover>
			) }
		</span>
	);
}
