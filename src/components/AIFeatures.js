/**
 * AI Features for Inline Context Editor
 *
 * Adds AI-powered note generation and category suggestions to the editor.
 * This is a mock implementation to demonstrate UI/UX.
 */

import { __ } from '@wordpress/i18n';
import { Button, Spinner, Notice } from '@wordpress/components';
import { useState } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';

/**
 * AI Generate Button Component
 *
 * Displays a "Generate with AI" button that creates a note from selected text.
 *
 * @param {Object}   root0                 - Component props.
 * @param {string}   root0.selectedText    - The text selected in the editor.
 * @param {Function} root0.onNoteGenerated - Callback when note is generated.
 */
export function AIGenerateButton( { selectedText, onNoteGenerated } ) {
	const [ isGenerating, setIsGenerating ] = useState( false );
	const [ error, setError ] = useState( null );

	const handleGenerate = async ( event ) => {
		// Prevent button click from closing popover or losing focus
		if ( event ) {
			event.preventDefault();
			event.stopPropagation();
		}

		if ( ! selectedText || selectedText.length === 0 ) {
			setError( __( 'Please select some text first', 'inline-context' ) );
			return;
		}

		setIsGenerating( true );
		setError( null );

		try {
			const response = await apiFetch( {
				path: '/inline-context/v1/ai/generate-note',
				method: 'POST',
				data: {
					text: selectedText,
					context: '', // Could add post title/excerpt here
				},
			} );

			if ( response.success ) {
				onNoteGenerated( response.note );
			} else {
				setError( __( 'Failed to generate note', 'inline-context' ) );
			}
		} catch ( err ) {
			setError(
				err.message || __( 'An error occurred', 'inline-context' )
			);
		} finally {
			setIsGenerating( false );
		}
	};

	return (
		<div className="inline-context-ai-generate">
			<Button
				variant="secondary"
				icon={ isGenerating ? <Spinner /> : '✨' }
				onClick={ handleGenerate }
				onMouseDown={ ( e ) => e.stopPropagation() }
				disabled={ isGenerating || ! selectedText }
				className="inline-context-ai-button"
				size="small"
			>
				{ isGenerating
					? __( 'Generating…', 'inline-context' )
					: __( 'Generate with AI', 'inline-context' ) }
			</Button>
			{ error && (
				<Notice status="error" isDismissible={ false }>
					{ error }
				</Notice>
			) }
		</div>
	);
}

/**
 * AI Category Suggester Component
 *
 * Suggests the best category for note content.
 *
 * @param {Object}   root0                     - Component props.
 * @param {string}   root0.noteContent         - The content of the note.
 * @param {Function} root0.onCategorySuggested - Callback when category is suggested.
 */
export function AICategorySuggester( { noteContent, onCategorySuggested } ) {
	const [ isSuggesting, setIsSuggesting ] = useState( false );
	const [ suggestion, setSuggestion ] = useState( null );

	const handleSuggest = async ( event ) => {
		// Prevent button click from closing popover or losing focus
		if ( event ) {
			event.preventDefault();
			event.stopPropagation();
		}

		if ( ! noteContent || noteContent.length < 10 ) {
			return;
		}

		setIsSuggesting( true );
		setSuggestion( null );

		try {
			const response = await apiFetch( {
				path: '/inline-context/v1/ai/suggest-category',
				method: 'POST',
				data: {
					content: noteContent,
				},
			} );

			if ( response.success && response.category ) {
				setSuggestion( response.category );
				onCategorySuggested( response.category );
			}
		} catch ( err ) {
			// Silently fail for suggestions - error details are not shown to user
		} finally {
			setIsSuggesting( false );
		}
	};

	return (
		<div className="inline-context-ai-category-suggest">
			<Button
				variant="link"
				icon={ isSuggesting ? <Spinner /> : '🎯' }
				onClick={ handleSuggest }
				onMouseDown={ ( e ) => e.stopPropagation() }
				disabled={ isSuggesting || ! noteContent }
				className="inline-context-ai-suggest-button"
				isSmall
			>
				{ isSuggesting
					? __( 'Suggesting…', 'inline-context' )
					: __( 'Suggest category', 'inline-context' ) }
			</Button>
			{ suggestion && (
				<span className="inline-context-ai-suggestion">
					{ __( 'Suggested:', 'inline-context' ) }{ ' ' }
					<strong>{ suggestion }</strong>
				</span>
			) }
		</div>
	);
}

/**
 * Check if AI features are enabled
 *
 * @return {boolean} True if AI features are enabled.
 */
export function isAIEnabled() {
	return window.inlineContextData?.aiEnabled === true;
}
