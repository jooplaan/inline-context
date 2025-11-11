/**
 * API functions for handling note actions like deletion and usage tracking.
 */
import apiFetch from '@wordpress/api-fetch';

/**
 * Informs the backend that one or more notes have been removed from a post.
 *
 * This function is designed to be "fire-and-forget". It will not block
 * the UI, and failures are handled silently in the console.
 *
 * @param {number} postId The ID of the post being updated.
 * @param {number[]} noteIds An array of note IDs that were removed.
 * @returns {void}
 */
export function handleNoteRemoval( postId, noteIds ) {
	if ( ! postId || ! Array.isArray( noteIds ) || noteIds.length === 0 ) {
		return;
	}

	apiFetch( {
		path: '/inline-context/v1/notes/handle-removals',
		method: 'POST',
		data: {
			post_id: postId,
			note_ids: noteIds,
		},
	} ).catch( ( error ) => {
		// eslint-disable-next-line no-console
		console.warn( 'Could not handle note removal:', error );
	} );
}
