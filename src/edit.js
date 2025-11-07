/**
 * Refactored Edit component for WordPress inline context
 * Optimized for performance and maintainability
 */
import { useCallback, useMemo, useRef, useState } from '@wordpress/element';
import { RichTextToolbarButton } from '@wordpress/block-editor';
import { Popover, Button } from '@wordpress/components';
import { applyFormat, removeFormat } from '@wordpress/rich-text';
import { __ } from '@wordpress/i18n';

// Utils
import { ensureUniqueAnchorId } from './utils/anchor';
import { getLinkedText } from './utils/text';
import { copyAnchorLinkToClipboard } from './utils/clipboard';

// Hooks
import {
	useAnchorIdDuplicateCheck,
	usePopoverAnchor,
	usePopoverKeyboardShortcuts,
	useSyncEditorContent,
	useCopyLinkStatus,
} from './hooks/useInlineContext';

// Components
import CategorySelector from './components/CategorySelector';
import QuillEditor from './components/QuillEditor';
import LinkControl from './components/LinkControl';
import PopoverActions from './components/PopoverActions';

// Quill keyboard navigation utilities
import { useQuillKeyboardNav } from './hooks/useQuillKeyboardNav';

const FORMAT_TYPE = 'jooplaan/inline-context';

export default function Edit( { isActive, value, onChange } ) {
	// State
	const [ isOpen, setIsOpen ] = useState( false );
	const [ text, setText ] = useState( '' );
	const [ categoryId, setCategoryId ] = useState( '' );
	const [ showLinkInput, setShowLinkInput ] = useState( false );
	const [ linkUrl, setLinkUrl ] = useState( '' );
	const [ linkText, setLinkText ] = useState( '' );
	const [ isSourceMode, setIsSourceMode ] = useState( false );

	// Refs
	const prevFocusRef = useRef( null );
	const rootRef = useRef( null );
	const quillRef = useRef( null );
	const cancelRef = useRef( null );
	const saveRef = useRef( null );
	const removeRef = useRef( null );
	const addLinkButtonRef = useRef( null );
	const urlInputRef = useRef( null );
	const linkTextInputRef = useRef( null );
	const insertLinkButtonRef = useRef( null );
	const linkCancelButtonRef = useRef( null );
	const copyLinkButtonRef = useRef( null );
	const sourceTextareaRef = useRef( null );

	// Derived state
	const activeFormat = value.activeFormats?.find(
		( f ) => f.type === FORMAT_TYPE
	);
	const categories = useMemo( () => {
		const data = window.inlineContextData || {};
		return data.categories || {};
	}, [] );
	const popoverId = useMemo(
		() =>
			`inline-context-popover-${ Math.random()
				.toString( 36 )
				.slice( 2 ) }`,
		[]
	);
	const labelId = `${ popoverId }-label`;
	const linkedText = useMemo(
		() => ( getLinkedText( value ) || '' ).trim(),
		[ value ]
	);

	// Auto-fix duplicate anchor IDs
	useAnchorIdDuplicateCheck( activeFormat, value, onChange );

	// Get popover anchor position
	const anchor = usePopoverAnchor( isOpen, value );

	// Handlers
	const remove = useCallback( () => {
		onChange( removeFormat( value, FORMAT_TYPE ) );
		setIsOpen( false );
		setTimeout( () => prevFocusRef.current?.focus?.(), 0 );
	}, [ onChange, value ] );

	const apply = useCallback( () => {
		const proposedId = activeFormat?.attributes?.[ 'data-anchor-id' ];
		const anchorId = ensureUniqueAnchorId( proposedId );

		onChange(
			applyFormat( value, {
				type: FORMAT_TYPE,
				attributes: {
					'data-inline-context': text,
					'data-anchor-id': anchorId,
					'data-category-id': categoryId || '',
					href: `#${ anchorId }`,
					id: `trigger-${ anchorId }`,
					role: 'button',
					'aria-expanded': 'false',
				},
			} )
		);
		setIsOpen( false );
		setTimeout( () => prevFocusRef.current?.focus?.(), 0 );
	}, [ onChange, text, categoryId, value, activeFormat ] );

	const toggle = useCallback( () => {
		if ( ! isOpen ) {
			const doc = rootRef.current?.ownerDocument || document;
			prevFocusRef.current = doc.activeElement;
		}
		setIsOpen( ( prev ) => {
			const next = ! prev;
			if ( next ) {
				const fmt = value.activeFormats?.find(
					( f ) => f.type === FORMAT_TYPE
				);
				setText( fmt?.attributes?.[ 'data-inline-context' ] || '' );
				setCategoryId( fmt?.attributes?.[ 'data-category-id' ] || '' );
			}
			return next;
		} );
	}, [ isOpen, value ] );

	const handleClose = useCallback( () => {
		setIsOpen( false );
		setTimeout( () => prevFocusRef.current?.focus?.(), 0 );
	}, [] );

	const insertLink = useCallback( () => {
		const quillInstance = quillRef.current?.getEditor();
		if ( ! quillInstance || ! linkUrl ) {
			return;
		}

		const range = quillInstance.getSelection();
		const displayText = linkText || linkUrl;

		if ( range ) {
			quillInstance.insertText( range.index, displayText );
			quillInstance.formatText(
				range.index,
				displayText.length,
				'link',
				linkUrl
			);
			quillInstance.setSelection( range.index + displayText.length );
		} else {
			const length = quillInstance.getLength();
			quillInstance.insertText( length - 1, ' ' + displayText );
			quillInstance.formatText(
				length,
				displayText.length,
				'link',
				linkUrl
			);
		}

		setText( quillInstance.root.innerHTML );
		setShowLinkInput( false );
		setLinkUrl( '' );
		setLinkText( '' );
	}, [ linkUrl, linkText ] );

	const handleCancelLink = useCallback( () => {
		setShowLinkInput( false );
		setLinkUrl( '' );
		setLinkText( '' );
	}, [] );

	// Copy link functionality with status
	const copyLinkFunction = useCallback(
		( onSuccess, onError ) => {
			const anchorId = activeFormat?.attributes?.[ 'data-anchor-id' ];
			return copyAnchorLinkToClipboard( anchorId, onSuccess, onError );
		},
		[ activeFormat ]
	);
	const { status: copyLinkStatus, copyWithStatus: handleCopyLink } =
		useCopyLinkStatus( copyLinkFunction );

	// Sync editor content when format changes
	useSyncEditorContent( isOpen, activeFormat, setText, setCategoryId );

	// Keyboard shortcuts
	usePopoverKeyboardShortcuts( isOpen, apply, handleClose );

	// Keyboard navigation
	const { handleEditorKeyDownCapture, handleActionButtonsKeyDown } =
		useQuillKeyboardNav( {
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
		} );

	return (
		<span ref={ rootRef }>
			<RichTextToolbarButton
				icon="editor-ol"
				title={ __( 'Inline context', 'inline-context' ) }
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
					noArrow={ false }
					resize={ true }
					flip={ true }
					shift={ true }
					onClose={ handleClose }
				>
					<div className="wp-reveal-popover wp-reveal-quill-editor">
						<div className="wp-reveal-quill-label" id={ labelId }>
							{ __( 'Inline context', 'inline-context' ) }
							{ linkedText ? ': ' : '' }
							{ linkedText ? (
								<span
									title={ linkedText }
									style={ {
										display: 'inline-block',
										maxWidth: '32ch',
										overflow: 'hidden',
										textOverflow: 'ellipsis',
										whiteSpace: 'nowrap',
										verticalAlign: 'bottom',
									} }
								>
									{ linkedText }
								</span>
							) : null }
						</div>

						<CategorySelector
							value={ categoryId }
							onChange={ setCategoryId }
							categories={ categories }
						/>

						<QuillEditor
							value={ text }
							onChange={ setText }
							isSourceMode={ isSourceMode }
							onSourceModeToggle={ setIsSourceMode }
							quillRef={ quillRef }
							sourceTextareaRef={ sourceTextareaRef }
							onKeyDownCapture={ handleEditorKeyDownCapture }
							isOpen={ isOpen }
						/>

						<LinkControl
							isVisible={ showLinkInput }
							linkUrl={ linkUrl }
							linkText={ linkText }
							onUrlChange={ setLinkUrl }
							onTextChange={ setLinkText }
							onInsert={ insertLink }
							onCancel={ handleCancelLink }
							urlInputRef={ urlInputRef }
							linkTextInputRef={ linkTextInputRef }
							insertButtonRef={ insertLinkButtonRef }
							cancelButtonRef={ linkCancelButtonRef }
							onKeyDown={ handleActionButtonsKeyDown }
						/>

						<div className="wp-reveal-quill-actions">
							<Button
								ref={ addLinkButtonRef }
								variant="secondary"
								size="small"
								onClick={ () =>
									setShowLinkInput( ! showLinkInput )
								}
								onKeyDown={ ( e ) =>
									handleActionButtonsKeyDown(
										e,
										addLinkButtonRef
									)
								}
							>
								{ showLinkInput
									? __( 'Hide Link Form', 'inline-context' )
									: __( 'Add Link', 'inline-context' ) }
							</Button>

							{ activeFormat?.attributes?.[
								'data-anchor-id'
							] && (
								<Button
									ref={ copyLinkButtonRef }
									variant="link"
									size="small"
									onClick={ handleCopyLink }
									disabled={ copyLinkStatus === 'copying' }
									onKeyDown={ ( e ) =>
										handleActionButtonsKeyDown(
											e,
											copyLinkButtonRef
										)
									}
									style={ {
										marginLeft: '8px',
										textDecoration:
											copyLinkStatus === 'idle'
												? 'underline'
												: 'none',
									} }
								>
									{ copyLinkStatus === 'copied' &&
										__( 'Link copied', 'inline-context' ) }
									{ copyLinkStatus === 'copying' &&
										__( 'Copying…', 'inline-context' ) }
									{ copyLinkStatus === 'idle' &&
										__(
											'Copy link to this note',
											'inline-context'
										) }
								</Button>
							) }
						</div>

						<div className="wp-reveal-quill-help">
							{ __(
								'Use the toolbar to format your inline context with bold, italic, links, and lists. Use "Add Link" for WordPress internal links, or click the code icon (&lt;/&gt;) to edit HTML source.',
								'inline-context'
							) }
						</div>
					</div>

					<PopoverActions
						isActive={ isActive }
						onRemove={ remove }
						onCancel={ handleClose }
						onSave={ apply }
						onKeyDown={ handleActionButtonsKeyDown }
						removeRef={ removeRef }
						cancelRef={ cancelRef }
						saveRef={ saveRef }
					/>
				</Popover>
			) }
		</span>
	);
}
