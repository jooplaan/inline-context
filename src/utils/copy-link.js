/**
 * Clipboard utilities for copying links
 */

import { select } from '@wordpress/data';

/**
 * Get the frontend permalink for the current post
 *
 * @return {string} The frontend URL
 */
const getFrontendUrl = () => {
	const postId = select( 'core/editor' )?.getCurrentPostId();

	if ( postId ) {
		const permalink = select( 'core/editor' )?.getPermalink();
		const editedPostLink =
			select( 'core/editor' )?.getEditedPostAttribute( 'link' );

		return (
			permalink ||
			editedPostLink ||
			window.location.href.split( '#' )[ 0 ]
		);
	}

	return window.location.href.split( '#' )[ 0 ];
};

/**
 * Copy anchor link to clipboard
 *
 * @param {string}   anchorId  - The anchor ID to copy
 * @param {Function} onSuccess - Callback when copy succeeds
 * @param {Function} onError   - Callback when copy fails
 */
export const copyAnchorLinkToClipboard = async (
	anchorId,
	onSuccess,
	onError
) => {
	if ( ! anchorId ) {
		return;
	}

	const frontendUrl = getFrontendUrl();
	const fullAnchorUrl = `${ frontendUrl }#${ anchorId }`;

	try {
		await window.navigator.clipboard.writeText( fullAnchorUrl );
		onSuccess?.();
	} catch ( error ) {
		// Fallback for older browsers
		try {
			const textArea = document.createElement( 'textarea' );
			textArea.value = fullAnchorUrl;
			textArea.style.position = 'fixed';
			textArea.style.opacity = '0';
			document.body.appendChild( textArea );
			textArea.select();

			document.execCommand( 'copy' );
			document.body.removeChild( textArea );
			onSuccess?.();
		} catch ( fallbackError ) {
			onError?.( fallbackError );
		}
	}
};
