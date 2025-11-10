/**
 * Refactored Edit component for WordPress inline context
 * Optimized for performance and maintainability
 */
import { useCallback, useEffect, useMemo, useRef, useState } from '@wordpress/element';
import { RichTextToolbarButton } from '@wordpress/block-editor';
import { Popover, Button } from '@wordpress/components';
import { applyFormat, removeFormat } from '@wordpress/rich-text';
import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';

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
import NoteSearch from './components/NoteSearch';

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
	const [ noteId, setNoteId ] = useState( null );
	const [ showNoteSearch, setShowNoteSearch ] = useState( false );
	const [ isReusedNote, setIsReusedNote ] = useState( false );
	const [ selectedNote, setSelectedNote ] = useState( null );

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
	const isSettingReusableNoteRef = useRef( false );

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

	const saveNoteToCPT = useCallback(
		async ( noteContent, currentNoteId = null ) => {
			try {
				const postData = {
					content: noteContent,
					status: 'publish',
				};

				// Only set title when creating a new note, not when updating
				if ( ! currentNoteId ) {
					postData.title =
						linkedText || __( 'Untitled Note', 'inline-context' );
				}

				// Always set category (even if empty to clear it)
				const cats = window.inlineContextData?.categories || {};
				if ( categoryId ) {
					// categoryId can be either a slug (string) or term ID (number/string number)
					// Find by both slug and ID to handle both cases
					const category = Object.values( cats ).find(
						( cat ) =>
							cat.slug === categoryId ||
							cat.id.toString() === categoryId.toString()
					);

					if ( category && category.id ) {
						postData.inline_context_category = [ category.id ];
					} else {
						postData.inline_context_category = [];
					}
				} else {
					// No category selected, send empty array to clear
					postData.inline_context_category = [];
				}

				let savedNoteId = currentNoteId;
				let shouldCreateNew = false;

				// If reusing an existing note, check if content or category changed
				if ( currentNoteId ) {
					try {
						const existingNote = await apiFetch( {
							path: `/wp/v2/inline_context_note/${ currentNoteId }`,
						} );

						// Get existing category
						const existingCategoryIds =
							existingNote.inline_context_category || [];
						const newCategoryIds =
							postData.inline_context_category || [];

						// Normalize content for comparison (remove extra whitespace/newlines)
						const normalizeContent = ( content ) => {
							return content
								? content.replace( /\s+/g, ' ' ).trim()
								: '';
						};

						const existingContent = normalizeContent(
							existingNote.content?.rendered || ''
						);
						const newContent = normalizeContent( noteContent );

						// Check if content or category changed
						const contentChanged = existingContent !== newContent;
						const categoryChanged =
							JSON.stringify( existingCategoryIds.sort() ) !==
							JSON.stringify( newCategoryIds.sort() );

						if ( contentChanged || categoryChanged ) {
							// Content or category changed - create a new note instead of updating
							shouldCreateNew = true;
							savedNoteId = null;
							// Set title for the new note
							postData.title =
								linkedText ||
								__( 'Untitled Note', 'inline-context' );
						}
					} catch ( error ) {
						// If we can't fetch the existing note, create a new one
						// eslint-disable-next-line no-console
						console.warn(
							'Could not fetch existing note, creating new:',
							error
						);
						shouldCreateNew = true;
						savedNoteId = null;
						postData.title =
							linkedText ||
							__( 'Untitled Note', 'inline-context' );
					}
				}

				if ( savedNoteId && ! shouldCreateNew ) {
					// Update existing note (only if content and category unchanged)
					await apiFetch( {
						path: `/wp/v2/inline_context_note/${ savedNoteId }`,
						method: 'POST',
						data: postData,
					} );
				} else {
					// Create new note
					const response = await apiFetch( {
						path: '/wp/v2/inline_context_note',
						method: 'POST',
						data: postData,
					} );
					savedNoteId = response.id;
				}

				// Track usage - add current post to the note's used_in_posts meta
				if (
					savedNoteId &&
					window.wp?.data?.select( 'core/editor' )?.getCurrentPostId
				) {
					const currentPostId = window.wp.data
						.select( 'core/editor' )
						.getCurrentPostId();
					if ( currentPostId ) {
						try {
							// Use custom endpoint to track usage
							await apiFetch( {
								path: `/inline-context/v1/notes/${ savedNoteId }/track-usage`,
								method: 'POST',
								data: {
									post_id: currentPostId,
								},
							} );
						} catch ( trackingError ) {
							// Non-critical - don't fail if tracking fails
							// eslint-disable-next-line no-console
							console.warn(
								'Could not track note usage:',
								trackingError
							);
						}
					}
				}
				return savedNoteId;
			} catch ( error ) {
				// eslint-disable-next-line no-console
				console.error( 'Error saving note to CPT:', error );
				return currentNoteId;
			}
		},
		[ linkedText, categoryId ]
	);

	const apply = useCallback( async () => {
		const proposedId = activeFormat?.attributes?.[ 'data-anchor-id' ];
		const anchorId = ensureUniqueAnchorId( proposedId );

		let savedNoteId = noteId;

		// Only save to CPT if this is NOT a reused note, or if the user modified it
		if ( ! isReusedNote || noteId === null ) {
			// This is a new note or a modified note - save to CPT
			savedNoteId = await saveNoteToCPT( text, noteId );
		} else {
			// This is a reused note - just track usage, don't modify the CPT
			if (
				noteId &&
				window.wp?.data?.select( 'core/editor' )?.getCurrentPostId
			) {
				const currentPostId = window.wp.data
					.select( 'core/editor' )
					.getCurrentPostId();
				if ( currentPostId ) {
					try {
						await apiFetch( {
							path: `/inline-context/v1/notes/${ noteId }/track-usage`,
							method: 'POST',
							data: {
								post_id: currentPostId,
							},
						} );
					} catch ( trackingError ) {
						// Non-critical - don't fail if tracking fails
						// eslint-disable-next-line no-console
						console.warn(
							'Could not track note usage:',
							trackingError
						);
					}
				}
			}
		}

		const formatAttributes = {
			'data-inline-context': text,
			'data-anchor-id': anchorId,
			'data-category-id': categoryId || '',
			href: `#${ anchorId }`,
			id: `trigger-${ anchorId }`,
			role: 'button',
			'aria-expanded': 'false',
		};

		// Add note ID if available
		if ( savedNoteId ) {
			formatAttributes[ 'data-note-id' ] = String( savedNoteId );
		}

		onChange(
			applyFormat( value, {
				type: FORMAT_TYPE,
				attributes: formatAttributes,
			} )
		);
		setIsOpen( false );
		setTimeout( () => prevFocusRef.current?.focus?.(), 0 );
	}, [
		onChange,
		text,
		categoryId,
		noteId,
		isReusedNote,
		value,
		activeFormat,
		saveNoteToCPT,
	] );

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
				const existingNoteId = fmt?.attributes?.[ 'data-note-id' ] || null;
				setNoteId( existingNoteId );
				setIsReusedNote( false ); // Will be set to true by useEffect if note is reusable
				setSelectedNote( null ); // Will be populated by useEffect

				// Always start in create mode (search is secondary workflow)
				setShowNoteSearch( false );
			}
			return next;
		} );
	}, [ isOpen, value ] );

	// Fetch note details when popover opens with an existing note ID
	useEffect( () => {
		if ( ! isOpen || ! noteId ) {
			return;
		}

		// Skip if already fetched
		if ( isReusedNote && selectedNote ) {
			return;
		}

		// Fetch the note to check if it's reusable
		apiFetch( {
			path: `/wp/v2/inline_context_note/${ noteId }`,
		} )
			.then( ( note ) => {
				// Check if the note is marked as reusable
				// The is_reusable field is now directly in the response (via REST API filter)
				const isReusable = note.is_reusable || false;
				
				if ( note && isReusable ) {
					// Set flag to prevent popover from closing during state update
					isSettingReusableNoteRef.current = true;
					setIsReusedNote( true );
					setSelectedNote( {
						id: note.id,
						title: note.title?.rendered || '',
						content: note.content?.rendered || '',
						is_reusable: isReusable,
						inline_context_category: note.inline_context_category || [],
					} );
					// Clear flag after a short delay to allow render to complete
					setTimeout( () => {
						isSettingReusableNoteRef.current = false;
					}, 100 );
				}
			} )
			.catch( ( error ) => {
				// Note might have been deleted, that's ok
				// eslint-disable-next-line no-console
				console.warn( 'Could not fetch note details:', error );
			} );
	}, [ isOpen, noteId ] ); // Only depend on isOpen and noteId to avoid infinite loops

	const handleSelectNote = useCallback( ( note ) => {
		setNoteId( note.id );
		setText( note.content );
		setIsReusedNote( true ); // Mark as reused note (don't modify CPT)
		setSelectedNote( note ); // Store the full note object

		// Load the category from the note's taxonomy
		if (
			note.inline_context_category &&
			note.inline_context_category.length > 0
		) {
			// Find the category slug from the term ID
			const cats = window.inlineContextData?.categories || {};
			const category = Object.values( cats ).find(
				( cat ) => cat.id === note.inline_context_category[ 0 ]
			);
			if ( category ) {
				setCategoryId( category.slug );
			}
		}

		setShowNoteSearch( false );
	}, [] );

	const handleCreateNewNote = useCallback( () => {
		setNoteId( null );
		setText( '' );
		setIsReusedNote( false );
		setSelectedNote( null );
		setShowNoteSearch( false );
	}, [] );

	const handleClose = useCallback( () => {
		// Don't close if we're in the middle of setting up reusable note data
		if ( isSettingReusableNoteRef.current ) {
			return;
		}
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
					focusOnMount={ isReusedNote ? false : 'firstElement' }
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

						{ /* Show reusable note info banner if this is a reused note */ }
						{ isReusedNote && selectedNote && selectedNote.is_reusable && (
							<div
								className="wp-inline-context-reusable-notice"
								style={ {
									background: '#f0f6fc',
									border: '1px solid #0073aa',
									borderRadius: '4px',
									padding: '12px',
									marginBottom: '16px',
									fontSize: '13px',
								} }
							>
								<div
									style={ {
										display: 'flex',
										alignItems: 'flex-start',
										gap: '8px',
									} }
								>
									<span
										style={ {
											fontSize: '16px',
											flexShrink: 0,
										} }
									>
										♻️
									</span>
									<div style={ { flex: 1 } }>
										<strong>
											{ __(
												'Reusable Note',
												'inline-context'
											) }
										</strong>
										<br />
										{ __(
											'This note is marked as reusable and cannot be edited here. Changes must be made to the source note.',
											'inline-context'
										) }
										<br />
										<a
											href={ `/wp-admin/post.php?post=${ selectedNote.id }&action=edit` }
											target="_blank"
											rel="noopener noreferrer"
											style={ {
												marginTop: '8px',
												display: 'inline-block',
											} }
										>
											{ __(
												'Edit source note →',
												'inline-context'
											) }
										</a>
									</div>
								</div>
							</div>
						) }

						{ showNoteSearch ? (
							<>
								<NoteSearch
									onSelectNote={ handleSelectNote }
									onCreateNew={ handleCreateNewNote }
								/>

								<div
									className="wp-reveal-quill-actions"
									style={ {
										marginTop: '12px',
										paddingTop: '12px',
										borderTop: '1px solid #ddd',
									} }
								>
									<Button
										variant="link"
										size="small"
										onClick={ () =>
											setShowNoteSearch( false )
										}
									>
										{ __(
											'← Create new note instead',
											'inline-context'
										) }
									</Button>
								</div>
							</>
						) : (
							<>
								<CategorySelector
									value={ categoryId }
									onChange={ setCategoryId }
									categories={ categories }
									disabled={ isReusedNote && selectedNote?.is_reusable }
								/>

								<QuillEditor
									value={ text }
									onChange={ setText }
									isSourceMode={ isSourceMode }
									onSourceModeToggle={ setIsSourceMode }
									quillRef={ quillRef }
									sourceTextareaRef={ sourceTextareaRef }
									onKeyDownCapture={
										handleEditorKeyDownCapture
									}
									isOpen={ isOpen }
									readOnly={ isReusedNote && selectedNote?.is_reusable }
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

								{ /* Only show action buttons and help text if not viewing a reusable note */ }
								{ ! ( isReusedNote && selectedNote?.is_reusable ) && (
									<>
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
													? __(
															'Hide Link Form',
															'inline-context'
													  )
													: __(
															'Add Link',
															'inline-context'
													  ) }
											</Button>

											{ activeFormat?.attributes?.[
												'data-anchor-id'
											] && (
												<Button
													ref={ copyLinkButtonRef }
													variant="link"
													size="small"
													onClick={ handleCopyLink }
													disabled={
														copyLinkStatus === 'copying'
													}
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
														__(
															'Link copied',
															'inline-context'
														) }
													{ copyLinkStatus === 'copying' &&
														__(
															'Copying…',
															'inline-context'
														) }
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
											<Button
												variant="link"
												size="small"
												onClick={ () =>
													setShowNoteSearch( true )
												}
												style={ {
													marginLeft: '8px',
													textDecoration: 'underline',
												} }
											>
												{ __(
													'Or search existing notes…',
													'inline-context'
												) }
											</Button>
										</div>
									</>
								) }
							</>
						) }
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
