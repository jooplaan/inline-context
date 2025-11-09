/**
 * QuillEditor for CPT edit screen
 */

/* global MutationObserver */

import { render, useState, useEffect, useRef } from '@wordpress/element';
import QuillEditor from './components/QuillEditor';

function CPTEditor() {
	const { content: initialContent = '' } =
		window.inlineContextCPTEditor || {};
	const [ text, setText ] = useState( initialContent );
	const [ isSourceMode, setIsSourceMode ] = useState( false );
	const quillRef = useRef( null );
	const sourceTextareaRef = useRef( null );

	// Update the hidden field whenever content changes.
	useEffect( () => {
		const hiddenField = document.getElementById(
			'inline-context-note-content'
		);
		if ( hiddenField ) {
			// Use textContent for textarea to preserve HTML properly
			hiddenField.value = text;
		}
	}, [ text ] );

	return (
		<div className="inline-context-cpt-editor-wrapper">
			<QuillEditor
				value={ text }
				onChange={ setText }
				isSourceMode={ isSourceMode }
				onSourceModeToggle={ setIsSourceMode }
				quillRef={ quillRef }
				sourceTextareaRef={ sourceTextareaRef }
				isOpen={ true }
			/>
		</div>
	);
}

// Mount the editor - try multiple strategies for reliability
function mountEditor() {
	const root = document.getElementById( 'inline-context-quill-root' );
	if ( root && ! root.dataset.mounted ) {
		root.dataset.mounted = 'true';
		render( <CPTEditor />, root );
	}
}

// Try mounting on DOMContentLoaded
if ( document.readyState === 'loading' ) {
	document.addEventListener( 'DOMContentLoaded', mountEditor );
} else {
	// DOM already loaded, mount immediately
	mountEditor();
}

// Also try when window loads (backup)
window.addEventListener( 'load', mountEditor );

// Final fallback: use MutationObserver to wait for the element
if ( ! document.getElementById( 'inline-context-quill-root' ) ) {
	const observer = new MutationObserver( () => {
		if ( document.getElementById( 'inline-context-quill-root' ) ) {
			mountEditor();
			observer.disconnect();
		}
	} );

	observer.observe( document.body, {
		childList: true,
		subtree: true,
	} );
}
