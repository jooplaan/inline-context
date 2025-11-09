/**
 * Note Search Component
 * Allows searching and selecting existing notes or creating new ones
 */
import { useState, useEffect, useCallback, useRef } from '@wordpress/element';
import { Button, TextControl, Spinner } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';

export default function NoteSearch( {
	onSelectNote,
	onCreateNew,
	initialSearch = '',
} ) {
	const [ searchTerm, setSearchTerm ] = useState( initialSearch );
	const [ notes, setNotes ] = useState( [] );
	const [ isLoading, setIsLoading ] = useState( false );
	const searchTimeoutRef = useRef( null );

	// Search notes with debounce
	const searchNotes = useCallback( ( term ) => {
		setIsLoading( true );

		const params = term ? `?s=${ encodeURIComponent( term ) }` : '';

		apiFetch( {
			path: `/inline-context/v1/notes/search${ params }`,
		} )
			.then( ( results ) => {
				setNotes( results );
				setIsLoading( false );
			} )
			.catch( ( error ) => {
				// eslint-disable-next-line no-console
				console.error( 'Error searching notes:', error );
				setNotes( [] );
				setIsLoading( false );
			} );
	}, [] );

	// Debounced search on term change
	useEffect( () => {
		if ( searchTimeoutRef.current ) {
			clearTimeout( searchTimeoutRef.current );
		}

		searchTimeoutRef.current = setTimeout( () => {
			searchNotes( searchTerm );
		}, 300 );

		return () => {
			if ( searchTimeoutRef.current ) {
				clearTimeout( searchTimeoutRef.current );
			}
		};
	}, [ searchTerm, searchNotes ] );

	// Initial load
	useEffect( () => {
		searchNotes( '' );
	}, [ searchNotes ] );

	const handleCreateNew = useCallback( () => {
		onCreateNew();
	}, [ onCreateNew ] );

	const handleSelectNote = useCallback(
		( note ) => {
			onSelectNote( note );
		},
		[ onSelectNote ]
	);

	return (
		<div className="wp-inline-context-note-search">
			<div className="wp-inline-context-note-search-header">
				<TextControl
					label={ __( 'Search existing notes', 'inline-context' ) }
					value={ searchTerm }
					onChange={ setSearchTerm }
					placeholder={ __( 'Type to search…', 'inline-context' ) }
				/>
				<Button
					variant="secondary"
					size="small"
					onClick={ handleCreateNew }
					style={ { marginTop: '8px' } }
				>
					{ __( 'Create New Note', 'inline-context' ) }
				</Button>
			</div>

			<div className="wp-inline-context-note-results">
				{ isLoading && (
					<div className="wp-inline-context-note-loading">
						<Spinner />
						{ __( 'Searching…', 'inline-context' ) }
					</div>
				) }

				{ ! isLoading && notes.length === 0 && (
					<div className="wp-inline-context-note-empty">
						{ searchTerm
							? __(
									'No notes found. Try a different search or create a new note.',
									'inline-context'
							  )
							: __(
									'No notes yet. Create your first note!',
									'inline-context'
							  ) }
					</div>
				) }

				{ ! isLoading && notes.length > 0 && (
					<ul className="wp-inline-context-note-list">
						{ notes.map( ( note ) => (
							<li key={ note.id }>
								<Button
									variant="link"
									onClick={ () => handleSelectNote( note ) }
									className="wp-inline-context-note-item"
								>
									<strong>
										{ note.title }
										{ note.is_reusable && (
											<span
												style={ {
													marginLeft: '6px',
													fontSize: '0.9em',
												} }
												title={ __(
													'Reusable note',
													'inline-context'
												) }
											>
												♻️
											</span>
										) }
									</strong>
									{ note.excerpt && (
										<span className="wp-inline-context-note-excerpt">
											{ note.excerpt }
										</span>
									) }
								</Button>
							</li>
						) ) }
					</ul>
				) }
			</div>
		</div>
	);
}
