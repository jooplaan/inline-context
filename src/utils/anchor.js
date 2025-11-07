/**
 * Anchor ID generation and validation utilities
 */

/**
 * Generate a unique anchor ID using timestamp and random string
 *
 * @return {string} Generated anchor ID
 */
export const generateAnchorId = () => {
	const timestamp = Date.now().toString( 36 );
	const random = Math.random().toString( 36 ).substring( 2, 7 );
	const anchorId = `context-note-${ timestamp }-${ random }`;

	// Allow developers to customize anchor ID generation
	if ( window.wp?.hooks ) {
		return window.wp.hooks.applyFilters(
			'inline_context_generate_anchor_id',
			anchorId,
			{ timestamp, random }
		);
	}
	return anchorId;
};

/**
 * Check if an anchor ID already exists in the document
 *
 * @param {string} proposedId - The proposed anchor ID to check
 * @return {string} Unique anchor ID (either the proposed one or a newly generated one)
 */
export const ensureUniqueAnchorId = ( proposedId ) => {
	if ( ! proposedId ) {
		return generateAnchorId();
	}

	const editor = window.wp?.data?.select( 'core/editor' );
	if ( ! editor ) {
		// Fallback: check DOM if editor API unavailable
		const existingTrigger = document.querySelector(
			`[data-anchor-id="${ proposedId }"]`
		);
		return existingTrigger ? generateAnchorId() : proposedId;
	}

	// Get all blocks and check for duplicate anchor IDs
	const allBlocks = editor.getBlocks();
	const allContent = JSON.stringify( allBlocks );

	// Count occurrences of this anchor ID in the content
	const regex = new RegExp(
		`"data-anchor-id":"${ proposedId.replace(
			/[.*+?^${}()|[\]\\]/g,
			'\\$&'
		) }"`,
		'g'
	);
	const matches = allContent.match( regex );

	// If found more than once (current instance + duplicate), generate new ID
	if ( matches && matches.length > 1 ) {
		return generateAnchorId();
	}

	return proposedId;
};

/**
 * Check if there are duplicate anchor IDs in the post content
 *
 * @param {string} anchorId - The anchor ID to check for duplicates
 * @return {boolean} True if duplicates exist
 */
export const hasDuplicateAnchorId = ( anchorId ) => {
	const editor = window.wp?.data?.select( 'core/editor' );
	if ( ! editor ) {
		return false;
	}

	const content = editor.getEditedPostContent();
	const anchorIdMatches =
		content.match(
			new RegExp(
				`data-anchor-id="${ anchorId.replace(
					/[.*+?^${}()|[\]\\]/g,
					'\\$&'
				) }"`,
				'g'
			)
		) || [];

	return anchorIdMatches.length > 1;
};
