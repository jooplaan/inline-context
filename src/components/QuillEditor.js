/**
 * Quill editor wrapper component with source mode toggle
 */

import { useEffect, useMemo } from '@wordpress/element';
import { Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import ReactQuill from 'react-quill';

const imagesEnabled = () => window.inlineContextData?.imagesEnabled !== false;

/**
 * Open the WordPress Media Library and insert the chosen image into Quill.
 *
 * @param {Object} editor Quill editor instance.
 */
function openMediaLibrary( editor ) {
	if ( ! window.wp?.media ) {
		// eslint-disable-next-line no-console
		console.warn(
			'wp.media not available — image insertion requires the WordPress media library.'
		);
		return;
	}

	const frame = window.wp.media( {
		title: __( 'Select or upload image', 'inline-context' ),
		button: { text: __( 'Use this image', 'inline-context' ) },
		library: { type: 'image' },
		multiple: false,
	} );

	frame.on( 'select', () => {
		const attachment = frame.state().get( 'selection' ).first().toJSON();

		// Prefer a "medium" size if available — keeps tooltip mode reasonable.
		// Falls back to the full-size URL.
		const sizes = attachment.sizes || {};
		const chosen = sizes.medium ||
			sizes.large ||
			sizes.full || { url: attachment.url };
		const src = chosen.url || attachment.url;
		const alt = attachment.alt || '';

		const range = editor.getSelection( true );
		const insertIndex =
			range && typeof range.index === 'number'
				? range.index
				: editor.getLength();

		editor.insertEmbed( insertIndex, 'image', src, 'user' );
		editor.setSelection( insertIndex + 1 );

		// Quill's default image embed doesn't set alt — set it on the
		// just-inserted <img> directly. Always set alt (empty if the
		// attachment has none) so screen readers don't fall back to
		// announcing the filename.
		setTimeout( () => {
			const root = editor.root;
			if ( ! root ) return;
			const imgs = root.querySelectorAll( 'img' );
			const img = imgs[ imgs.length - 1 ];
			if ( img ) {
				img.setAttribute( 'alt', alt );
				img.setAttribute( 'loading', 'lazy' );
				img.setAttribute( 'decoding', 'async' );
				img.classList.add( 'wp-inline-context-image' );
			}
		}, 0 );
	} );

	frame.open();
}

export default function QuillEditor( {
	value,
	onChange,
	isSourceMode,
	onSourceModeToggle,
	quillRef,
	sourceTextareaRef,
	onKeyDownCapture,
	isOpen,
	readOnly = false,
} ) {
	// Build modules/formats lists once per mount — including the image button
	// only when image support is enabled site-wide.
	const { quillModules, quillFormats } = useMemo( () => {
		const allowImages = imagesEnabled();
		const toolbarRow1 = [ 'bold', 'italic' ];
		const toolbarRow2 = [ 'link' ];
		if ( allowImages ) {
			toolbarRow2.push( 'image' );
		}
		return {
			quillModules: {
				toolbar: {
					container: [
						toolbarRow1,
						toolbarRow2,
						[ { list: 'ordered' }, { list: 'bullet' } ],
						[ 'clean' ],
						[ 'code-view' ], // Custom button for HTML source toggle
					],
				},
			},
			quillFormats: allowImages
				? [ 'bold', 'italic', 'link', 'list', 'bullet', 'image' ]
				: [ 'bold', 'italic', 'link', 'list', 'bullet' ],
		};
	}, [] );

	// Set up custom toolbar handler for code-view button
	useEffect( () => {
		if ( ! isOpen || isSourceMode ) {
			return;
		}

		const t = setTimeout( () => {
			const inst = quillRef.current;
			const editor = inst?.getEditor?.();

			if ( editor ) {
				// Set up custom toolbar button handler
				const toolbar = editor.getModule( 'toolbar' );
				if ( toolbar ) {
					toolbar.addHandler( 'code-view', () => {
						onSourceModeToggle( ( prev ) => ! prev );
					} );

					// Override the default image handler to use the WP Media
					// Library instead of Quill's URL-prompt dialog.
					if ( imagesEnabled() ) {
						toolbar.addHandler( 'image', () => {
							openMediaLibrary( editor );
						} );
					}

					// Add click listener to the button as fallback
					const container = editor.container;
					const codeViewBtn =
						container?.previousSibling?.querySelector?.(
							'.ql-code-view'
						);
					if ( codeViewBtn ) {
						codeViewBtn.onclick = ( e ) => {
							e.preventDefault();
							onSourceModeToggle( ( prev ) => ! prev );
						};
					}
				}
			}

			// Focus the editor
			if ( inst?.focus ) {
				inst.focus();
			} else if ( editor?.focus ) {
				editor.focus();
			}
		}, 0 );

		return () => clearTimeout( t );
	}, [ isOpen, isSourceMode, onSourceModeToggle, quillRef ] );

	return (
		<div onKeyDownCapture={ onKeyDownCapture }>
			{ ! isSourceMode ? (
				<ReactQuill
					ref={ quillRef }
					value={ value }
					onChange={ onChange }
					modules={ quillModules }
					formats={ quillFormats }
					placeholder={ __(
						'Add inline context…',
						'inline-context'
					) }
					theme="snow"
					readOnly={ readOnly }
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
							onClick={ () => onSourceModeToggle( false ) }
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
						value={ value }
						onChange={ ( e ) => onChange( e.target.value ) }
						className="wp-reveal-source-editor"
						placeholder={ __(
							'Edit HTML source…',
							'inline-context'
						) }
						rows={ 10 }
						readOnly={ readOnly }
					/>
				</>
			) }
		</div>
	);
}
