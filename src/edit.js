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
import { RichTextToolbarButton, URLInput } from '@wordpress/block-editor';
import {
	Popover,
	Button,
	Flex,
	FlexItem,
	TextControl,
} from '@wordpress/components';
import { applyFormat, removeFormat } from '@wordpress/rich-text';
import { __ } from '@wordpress/i18n';
import { select } from '@wordpress/data';
import ReactQuill from 'react-quill';

// WordPress-friendly Quill configuration
// Note: We add a custom 'code-view' button to the toolbar via container option
const QUILL_MODULES = {
	toolbar: {
		container: [
			['bold', 'italic'],
			['link'],
			[{ list: 'ordered' }, { list: 'bullet' }],
			['clean'],
			['code-view'], // Custom button for HTML source toggle
		],
	},
};

const QUILL_FORMATS = ['bold', 'italic', 'link', 'list', 'bullet'];

// Generate a unique ID for the anchor
const generateAnchorId = () => {
	// Create a short, unique identifier
	const timestamp = Date.now().toString(36);
	const random = Math.random().toString(36).substring(2, 7);
	const anchorId = `context-note-${timestamp}-${random}`;

	// Allow developers to customize anchor ID generation
	if (window.wp && window.wp.hooks) {
		return window.wp.hooks.applyFilters(
			'inline_context_generate_anchor_id',
			anchorId,
			{ timestamp, random }
		);
	}
	return anchorId;
};

// Check if an anchor ID already exists in the document and generate a unique one if needed
const ensureUniqueAnchorId = (proposedId) => {
	if (!proposedId) {
		return generateAnchorId();
	}

	// Check if this ID already exists in the current post content
	const editor = window.wp?.data?.select('core/editor');
	if (!editor) {
		// Fallback: check DOM if editor API unavailable
		const existingTrigger = document.querySelector(
			`[data-anchor-id="${proposedId}"]`
		);
		return existingTrigger ? generateAnchorId() : proposedId;
	}

	// Get all blocks and check for duplicate anchor IDs
	const allBlocks = editor.getBlocks();
	const allContent = JSON.stringify(allBlocks);

	// Count occurrences of this anchor ID in the content
	const regex = new RegExp(
		`"data-anchor-id":"${proposedId.replace(
			/[.*+?^${}()|[\]\\]/g,
			'\\$&'
		)}"`,
		'g'
	);
	const matches = allContent.match(regex);

	// If found more than once (current instance + duplicate), generate new ID
	if (matches && matches.length > 1) {
		return generateAnchorId();
	}

	return proposedId;
};

// Helper: derive linked text from current selection or active inline-context run
const getLinkedText = (value) => {
	if (!value) return '';
	const { text = '', start = 0, end = 0, formats = [] } = value;

	// If there's an explicit selection, use it.
	if (start < end) {
		return text.slice(start, end);
	}

	// Otherwise, if caret is within an inline-context, expand to that run.
	const TYPE = 'jooplaan/inline-context';
	if (!text || !formats || !formats.length) return '';

	const hasTypeAt = (i) => {
		const at = formats?.[i];
		if (!at) return false;
		const arr = Array.isArray(at) ? at : [at];
		return arr.some((f) => f && f.type === TYPE);
	};

	// Caret is between characters; prefer the left character, else the right.
	const leftIdx = Math.max(0, Math.min(text.length - 1, start - 1));
	const rightIdx = Math.max(0, Math.min(text.length - 1, start));

	let seedIdx = -1;
	if (hasTypeAt(leftIdx)) {
		seedIdx = leftIdx;
	} else if (hasTypeAt(rightIdx)) {
		seedIdx = rightIdx;
	}
	if (seedIdx < 0) return '';

	let left = seedIdx;
	let right = seedIdx;
	while (left - 1 >= 0 && hasTypeAt(left - 1)) left--;
	while (right + 1 < text.length && hasTypeAt(right + 1)) right++;

	return text.slice(left, right + 1);
};

export default function Edit( { isActive, value, onChange } ) {
	const [ isOpen, setIsOpen ] = useState( false );
	const [ anchor, setAnchor ] = useState();
	const [ showLinkInput, setShowLinkInput ] = useState( false );
	const [ linkUrl, setLinkUrl ] = useState( '' );
	const [ linkText, setLinkText ] = useState( '' );
	const [ copyLinkStatus, setCopyLinkStatus ] = useState( 'idle' ); // 'idle', 'copying', 'copied'
	const [ isSourceMode, setIsSourceMode ] = useState( false ); // Toggle between WYSIWYG and HTML source
	const prevFocusRef = useRef( null );
	const rootRef = useRef( null );
	const popoverId = useMemo(
		() => `inline-context-popover-${Math.random().toString(36).slice(2)}`,
		[]
	);
	const labelId = `${popoverId}-label`;
	const cancelRef = useRef(null);
	const saveRef = useRef(null);
	const removeRef = useRef(null);
	const quillRef = useRef(null);
	const addLinkButtonRef = useRef( null );
	const urlInputRef = useRef( null );
	const linkTextInputRef = useRef( null );
	const insertLinkButtonRef = useRef( null );
	const linkCancelButtonRef = useRef( null );
	const copyLinkButtonRef = useRef( null );
	const sourceTextareaRef = useRef( null );

	const activeFormat = value.activeFormats?.find(
		(f) => f.type === 'jooplaan/inline-context'
	);
	const remove = () => {
		onChange(removeFormat(value, 'jooplaan/inline-context'));
		setIsOpen(false);
		setTimeout(() => prevFocusRef.current?.focus?.(), 0);
	};
	const currentText = activeFormat?.attributes?.['data-inline-context'] || '';
	const [text, setText] = useState(currentText);

	// Auto-fix duplicate IDs when component mounts or activeFormat changes
	useEffect(() => {
		if (!activeFormat?.attributes?.['data-anchor-id']) {
			return; // No existing format to check
		}

		const currentId = activeFormat.attributes['data-anchor-id'];

		// Always check for duplicates, regardless of when this runs
		const editor = window.wp?.data?.select('core/editor');
		let hasDuplicate = false;

		if (editor) {
			// Get the entire post content as HTML
			const content = editor.getEditedPostContent();

			// Count occurrences of this anchor ID in the raw content
			const anchorIdMatches =
				content.match(
					new RegExp(
						`data-anchor-id="${currentId.replace(
							/[.*+?^${}()|[\]\\]/g,
							'\\$&'
						)}"`,
						'g'
					)
				) || [];

			hasDuplicate = anchorIdMatches.length > 1;
		}

		// If duplicate detected, generate new ID immediately
		if (hasDuplicate) {
			const uniqueId = generateAnchorId();

			onChange(
				applyFormat(value, {
					type: 'jooplaan/inline-context',
					attributes: {
						...activeFormat.attributes,
						'data-anchor-id': uniqueId,
						id: `trigger-${uniqueId}`,
					},
				})
			);
		}
	}, [activeFormat, onChange, value]);

	// Linked text for label (single-line with ellipsis)
	const linkedText = useMemo(() => {
		const s = (getLinkedText(value) || '').trim();
		return s;
	}, [value]);

	// Apply the inline context to the current selection
	const apply = useCallback(() => {
		// Get proposed ID (existing or new)
		const proposedId = activeFormat?.attributes?.['data-anchor-id'];
		// Ensure uniqueness (handles copy/paste duplicates)
		const anchorId = ensureUniqueAnchorId(proposedId);

		onChange(
			applyFormat(value, {
				type: 'jooplaan/inline-context',
				attributes: {
					'data-inline-context': text,
					'data-anchor-id': anchorId,
					href: `#${anchorId}`,
					id: `trigger-${anchorId}`,
					role: 'button',
					'aria-expanded': 'false',
				},
			})
		);
		setIsOpen(false);
		// Restore focus to the previously focused control for smooth keyboard workflow
		setTimeout(() => prevFocusRef.current?.focus?.(), 0);
	}, [onChange, text, value, activeFormat]);

	const toggle = () => {
		// Remember the element that had focus before opening so we can restore it on close
		if (!isOpen) {
			const doc = rootRef.current?.ownerDocument || document;
			prevFocusRef.current = doc.activeElement;
		}
		setIsOpen((prev) => {
			const next = !prev;
			if (next) {
				// Sync the editor with the currently selected inline context when opening
				const fmt = value.activeFormats?.find(
					(f) => f.type === 'jooplaan/inline-context'
				);
				setText(fmt?.attributes?.['data-inline-context'] || '');
			}
			return next;
		});
	};

	// Compute a virtual element for the current selection so the Popover can anchor near the selected text
	useEffect(() => {
		if (!isOpen) return;
		const getSelectionAnchor = () => {
			const view = rootRef.current?.ownerDocument?.defaultView || window;
			const sel = view.getSelection?.();
			if (!sel || sel.rangeCount === 0) return undefined;
			const range = sel.getRangeAt(0).cloneRange();
			try {
				if (range.collapsed) {
					// Insert a zero-width marker to measure caret position
					const marker = document.createElement('span');
					marker.appendChild(document.createTextNode('\u200b'));
					range.insertNode(marker);
					const rect = marker.getBoundingClientRect();
					marker.remove();
					return rect;
				}
				return range.getBoundingClientRect();
			} catch (error) {
				// eslint-disable-next-line no-console
				console.warn('Failed to get selection anchor:', error);
				return undefined;
			}
		};
		const rect = getSelectionAnchor();
		if (!rect) {
			setAnchor(undefined);
			return;
		}
		// Create a virtual element compatible with Floating UI/Popover expectations
		const virtualEl = {
			getBoundingClientRect: () => rect,
		};
		setAnchor(virtualEl);
	}, [isOpen, value?.start, value?.end]);

	// Also resync text if the active format changes while popover is open
	useEffect(() => {
		if (!isOpen) return;
		setText(currentText);
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, [activeFormat?.attributes?.['data-inline-context']]);

	// When opening, place focus inside the Quill editor for immediate typing
	// Also set up custom toolbar handler for code-view button
	useEffect(() => {
		if (!isOpen || isSourceMode) return;
		const t = setTimeout(() => {
			const inst = quillRef.current;
			const editor = inst?.getEditor?.();
			
			if (editor) {
				// Set up custom toolbar button handler BEFORE focusing
				const toolbar = editor.getModule('toolbar');
				if (toolbar) {
					toolbar.addHandler('code-view', () => {
						setIsSourceMode((prev) => !prev);
					});
					
					// Add click listener to the button as fallback
					const container = editor.container;
					const codeViewBtn = container?.previousSibling?.querySelector?.('.ql-code-view');
					if (codeViewBtn) {
						codeViewBtn.onclick = (e) => {
							e.preventDefault();
							setIsSourceMode((prev) => !prev);
						};
					}
				}
			}
			
			// Focus the editor
			if (inst?.focus) {
				inst.focus();
			} else if (editor?.focus) {
				editor.focus();
			}
		}, 0);
		return () => clearTimeout(t);
	}, [isOpen, isSourceMode]);

	// Add handy keyboard shortcuts for the popover
	useEffect(() => {
		if (!isOpen) return;
		const onKeyDown = (e) => {
			if ((e.metaKey || e.ctrlKey) && e.key === 'Enter') {
				e.preventDefault();
				apply();
			}
			if (e.key === 'Escape') {
				e.preventDefault();
				setIsOpen(false);
			}
		};
		document.addEventListener('keydown', onKeyDown);
		return () => document.removeEventListener('keydown', onKeyDown);
	}, [isOpen, apply]);

	// Allow Tab to move focus from the Quill editor to the first action button,
	// and Shift+Tab to return to the toolbar toggle button.
	const handleEditorKeyDownCapture = (e) => {
		if (e.key === 'Tab') {
			e.preventDefault();
			if (e.shiftKey) {
				// Back to the toolbar button
				prevFocusRef.current?.focus?.();
			} else {
				// Forward tab: go to Add Link button first, then to other action buttons
				addLinkButtonRef.current?.focus?.();
			}
		}
	};

	// Handle Tab navigation within the action buttons area
	const handleActionButtonsKeyDown = (e, currentRef) => {
		if (e.key !== 'Tab') return;
		e.preventDefault();

		// Define the tab order based on current state
		const tabOrder = [
			addLinkButtonRef,
			// If editing existing note and has anchor ID, include copy link button
			...( activeFormat?.attributes?.[ 'data-anchor-id' ]
				? [ copyLinkButtonRef ]
				: [] ),
			// If link form is visible, include those fields
			...(showLinkInput
				? [
						urlInputRef,
						linkTextInputRef,
						insertLinkButtonRef,
						linkCancelButtonRef,
				  ]
				: []),
			// Always include the main action buttons
			...(activeFormat ? [removeRef] : []),
			cancelRef,
			saveRef,
		].filter((ref) => ref.current);

		const currentIndex = tabOrder.indexOf(currentRef);

		if (e.shiftKey) {
			// Shift+Tab: go backwards
			if (currentIndex > 0) {
				tabOrder[currentIndex - 1].current?.focus?.();
			} else {
				// From first button, go back to editor
				quillRef.current?.focus?.();
			}
		} else if (currentIndex < tabOrder.length - 1) {
			// Tab: go forwards
			tabOrder[currentIndex + 1].current?.focus?.();
		} else {
			// From last button, stay on it (or could cycle back)
			tabOrder[currentIndex].current?.focus?.();
		}
	};

	// Handle inserting a link at cursor position in ReactQuill
	const insertLink = () => {
		const quillInstance = quillRef.current?.getEditor();
		if (!quillInstance || !linkUrl) return;

		const range = quillInstance.getSelection();
		const displayText = linkText || linkUrl;

		if (range) {
			// Insert the link at cursor position
			quillInstance.insertText(range.index, displayText);
			quillInstance.formatText(
				range.index,
				displayText.length,
				'link',
				linkUrl
			);
			// Move cursor after the link
			quillInstance.setSelection(range.index + displayText.length);
		} else {
			// If no selection, append at the end
			const length = quillInstance.getLength();
			quillInstance.insertText(length - 1, ' ' + displayText);
			quillInstance.formatText(
				length,
				displayText.length,
				'link',
				linkUrl
			);
		}

		// Update our text state and close link input
		setText(quillInstance.root.innerHTML);
		setShowLinkInput(false);
		setLinkUrl('');
		setLinkText('');
	};

	// Handle copying the anchor link to clipboard
	const copyAnchorLink = async () => {
		const anchorId = activeFormat?.attributes?.['data-anchor-id'];
		if (!anchorId) return;

		// Get the frontend permalink of the current post
		const postId = select('core/editor')?.getCurrentPostId();
		let frontendUrl = '';

		if (postId) {
			// Try to get the permalink from the editor store
			const permalink = select('core/editor')?.getPermalink();
			const editedPostContent =
				select('core/editor')?.getEditedPostAttribute('link');

			// Use permalink if available, otherwise fall back to the edited post link
			frontendUrl =
				permalink ||
				editedPostContent ||
				window.location.href.split('#')[0];
		} else {
			// Fallback to current URL if we can't get post data
			frontendUrl = window.location.href.split('#')[0];
		}

		const fullAnchorUrl = `${frontendUrl}#${anchorId}`;

		setCopyLinkStatus('copying');

		try {
			await window.navigator.clipboard.writeText(fullAnchorUrl);
			setCopyLinkStatus('copied');

			// Reset status after 2 seconds
			setTimeout(() => {
				setCopyLinkStatus('idle');
			}, 2000);
		} catch (error) {
			// Fallback for older browsers or when clipboard API fails
			const textArea = document.createElement('textarea');
			textArea.value = fullAnchorUrl;
			textArea.style.position = 'fixed';
			textArea.style.opacity = '0';
			document.body.appendChild(textArea);
			textArea.select();

			try {
				document.execCommand('copy');
				setCopyLinkStatus('copied');
				setTimeout(() => {
					setCopyLinkStatus('idle');
				}, 2000);
			} catch (fallbackError) {
				// eslint-disable-next-line no-console
				console.warn('Failed to copy link:', fallbackError);
				setCopyLinkStatus('idle');
			}

			document.body.removeChild(textArea);
		}
	};

	// Handle React Quill editor changes
	const handleQuillChange = (content) => {
		setText(content);
	};

	return (
		<span ref={rootRef}>
			<RichTextToolbarButton
				icon="editor-ol"
				title={__('Inline context', 'inline-context')}
				onClick={toggle}
				isActive={isActive}
				aria-expanded={isOpen}
				aria-controls={isOpen ? popoverId : undefined}
			/>

			{isOpen && (
				<Popover
					id={popoverId}
					anchor={anchor}
					position="bottom center"
					focusOnMount="firstElement"
					role="dialog"
					aria-modal={false}
					aria-labelledby={labelId}
					noArrow={false}
					resize={true}
					flip={true}
					shift={true}
					onClose={() => {
						setIsOpen(false);
						setTimeout(() => prevFocusRef.current?.focus?.(), 0);
					}}
				>
					<div className="wp-reveal-popover wp-reveal-quill-editor">
						<div className="wp-reveal-quill-label" id={labelId}>
							{__('Inline context', 'inline-context')}
							{linkedText ? ': ' : ''}
							{linkedText ? (
								<span
									title={linkedText}
									style={{
										display: 'inline-block',
										maxWidth: '32ch',
										overflow: 'hidden',
										textOverflow: 'ellipsis',
										whiteSpace: 'nowrap',
										verticalAlign: 'bottom',
									}}
								>
									{linkedText}
								</span>
							) : null}
						</div>
						<div onKeyDownCapture={ handleEditorKeyDownCapture }>
							{ ! isSourceMode ? (
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
							) : (
								<>
									<div className="wp-reveal-source-header">
										<span className="wp-reveal-source-label">
											{ __( 'HTML Source', 'inline-context' ) }
										</span>
										<Button
											variant="link"
											size="small"
											onClick={ () => setIsSourceMode( false ) }
											style={ {
												fontSize: '12px',
												textDecoration: 'underline',
											} }
										>
											{ __( 'Back to Visual Editor', 'inline-context' ) }
										</Button>
									</div>
									<textarea
										ref={ sourceTextareaRef }
										value={ text }
										onChange={ ( e ) =>
											setText( e.target.value )
										}
										className="wp-reveal-source-editor"
										placeholder={ __(
											'Edit HTML source…',
											'inline-context'
										) }
										rows={ 10 }
									/>
								</>
							) }
						</div>

						{ showLinkInput && (
							<div className="wp-reveal-link-control">
								<div className="wp-reveal-link-url-wrapper">
									<label
										htmlFor="inline-context-url-input"
										className="components-base-control__label"
									>
										{__('Link URL', 'inline-context')}
									</label>
									<URLInput
										id="inline-context-url-input"
										ref={urlInputRef}
										value={linkUrl}
										onChange={(url, post) => {
											setLinkUrl(url);
											// If a post is selected, use its title as link text
											if (post?.title && !linkText) {
												setLinkText(post.title);
											}
										}}
										placeholder={__(
											'Search or paste URL',
											'inline-context'
										)}
										onKeyDown={(e) =>
											handleActionButtonsKeyDown(
												e,
												urlInputRef
											)
										}
										__nextHasNoMarginBottom
									/>
									<p className="components-base-control__help">
										{__(
											'Start typing to search for internal content or paste any URL',
											'inline-context'
										)}
									</p>
								</div>
								<TextControl
									ref={linkTextInputRef}
									label={__(
										'Link Text (optional)',
										'inline-context'
									)}
									value={linkText}
									onChange={setLinkText}
									placeholder={__(
										'Custom link text',
										'inline-context'
									)}
									onKeyDown={(e) =>
										handleActionButtonsKeyDown(
											e,
											linkTextInputRef
										)
									}
								/>
								<Flex gap={2} style={{ marginTop: '8px' }}>
									<Button
										ref={insertLinkButtonRef}
										variant="primary"
										size="small"
										onClick={insertLink}
										disabled={!linkUrl}
										onKeyDown={(e) =>
											handleActionButtonsKeyDown(
												e,
												insertLinkButtonRef
											)
										}
									>
										{__('Insert Link', 'inline-context')}
									</Button>
									<Button
										ref={linkCancelButtonRef}
										variant="secondary"
										size="small"
										onClick={() => {
											setShowLinkInput(false);
											setLinkUrl('');
											setLinkText('');
										}}
										onKeyDown={(e) =>
											handleActionButtonsKeyDown(
												e,
												linkCancelButtonRef
											)
										}
									>
										{__('Cancel', 'inline-context')}
									</Button>
								</Flex>
							</div>
						)}

						<div className="wp-reveal-quill-actions">
							<Button
								ref={addLinkButtonRef}
								variant="secondary"
								size="small"
								onClick={() => setShowLinkInput(!showLinkInput)}
								onKeyDown={(e) =>
									handleActionButtonsKeyDown(
										e,
										addLinkButtonRef
									)
								}
							>
								{showLinkInput
									? __('Hide Link Form', 'inline-context')
									: __('Add Link', 'inline-context')}
							</Button>

							{activeFormat?.attributes?.['data-anchor-id'] && (
								<Button
									ref={copyLinkButtonRef}
									variant="link"
									size="small"
									onClick={copyAnchorLink}
									disabled={copyLinkStatus === 'copying'}
									onKeyDown={(e) =>
										handleActionButtonsKeyDown(
											e,
											copyLinkButtonRef
										)
									}
									style={{
										marginLeft: '8px',
										textDecoration:
											copyLinkStatus === 'idle'
												? 'underline'
												: 'none',
									}}
								>
									{copyLinkStatus === 'copied' &&
										__('Link copied', 'inline-context')}
									{copyLinkStatus === 'copying' &&
										__('Copying…', 'inline-context')}
									{copyLinkStatus === 'idle' &&
										__(
											'Copy link to this note',
											'inline-context'
										)}
								</Button>
							)}
						</div>

						<div className="wp-reveal-quill-help">
							{ __(
								'Use the toolbar to format your inline context with bold, italic, links, and lists. Use "Add Link" for WordPress internal links, or click the code icon (&lt;/&gt;) to edit HTML source.',
								'inline-context'
							) }
						</div>
					</div>
					<Flex justify="space-between" align="center">
						<FlexItem>
							{isActive && (
								<Button
									ref={removeRef}
									variant="tertiary"
									isDestructive
									onClick={remove}
									onKeyDown={(e) =>
										handleActionButtonsKeyDown(e, removeRef)
									}
								>
									{__('Delete', 'inline-context')}
								</Button>
							)}
						</FlexItem>
						<FlexItem>
							<Flex gap={2}>
								<Button
									ref={cancelRef}
									variant="secondary"
									onClick={() => setIsOpen(false)}
									onKeyDown={(e) =>
										handleActionButtonsKeyDown(e, cancelRef)
									}
								>
									{__('Cancel', 'inline-context')}
								</Button>
								<Button
									ref={saveRef}
									variant="primary"
									onClick={apply}
									onKeyDown={(e) =>
										handleActionButtonsKeyDown(e, saveRef)
									}
								>
									{__('Save', 'inline-context')}
								</Button>
							</Flex>
						</FlexItem>
					</Flex>
				</Popover>
			)}
		</span>
	);
}
