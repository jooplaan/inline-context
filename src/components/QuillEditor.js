/**
 * Quill editor wrapper component with source mode toggle
 */

import { useEffect } from '@wordpress/element';
import { Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import ReactQuill from 'react-quill';

const QUILL_MODULES = {
	toolbar: {
		container: [
			[ 'bold', 'italic' ],
			[ 'link' ],
			[ { list: 'ordered' }, { list: 'bullet' } ],
			[ 'clean' ],
			[ 'code-view' ], // Custom button for HTML source toggle
		],
	},
};

const QUILL_FORMATS = [ 'bold', 'italic', 'link', 'list', 'bullet' ];

export default function QuillEditor( {
	value,
	onChange,
	isSourceMode,
	onSourceModeToggle,
	quillRef,
	sourceTextareaRef,
	onKeyDownCapture,
	isOpen,
} ) {
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
					/>
				</>
			) }
		</div>
	);
}
