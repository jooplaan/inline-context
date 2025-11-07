/**
 * Text extraction utilities for inline context
 */

const FORMAT_TYPE = 'jooplaan/inline-context';

/**
 * Check if a format at a given index is an inline-context format
 *
 * @param {Array}  formats - The formats array
 * @param {number} i       - The index to check
 * @return {boolean} True if the format is inline-context
 */
const hasInlineContextAt = ( formats, i ) => {
	const at = formats?.[ i ];
	if ( ! at ) {
		return false;
	}
	const arr = Array.isArray( at ) ? at : [ at ];
	return arr.some( ( f ) => f && f.type === FORMAT_TYPE );
};

/**
 * Derive linked text from current selection or active inline-context run
 *
 * @param {Object} value - The rich text value object
 * @return {string} The linked text
 */
export const getLinkedText = ( value ) => {
	if ( ! value ) {
		return '';
	}
	const { text = '', start = 0, end = 0, formats = [] } = value;

	// If there's an explicit selection, use it
	if ( start < end ) {
		return text.slice( start, end );
	}

	// Otherwise, if caret is within an inline-context, expand to that run
	if ( ! text || ! formats || ! formats.length ) {
		return '';
	}

	// Caret is between characters; prefer the left character, else the right
	const leftIdx = Math.max( 0, Math.min( text.length - 1, start - 1 ) );
	const rightIdx = Math.max( 0, Math.min( text.length - 1, start ) );

	let seedIdx = -1;
	if ( hasInlineContextAt( formats, leftIdx ) ) {
		seedIdx = leftIdx;
	} else if ( hasInlineContextAt( formats, rightIdx ) ) {
		seedIdx = rightIdx;
	}
	if ( seedIdx < 0 ) {
		return '';
	}

	let left = seedIdx;
	let right = seedIdx;
	while ( left - 1 >= 0 && hasInlineContextAt( formats, left - 1 ) ) {
		left--;
	}
	while (
		right + 1 < text.length &&
		hasInlineContextAt( formats, right + 1 )
	) {
		right++;
	}

	return text.slice( left, right + 1 );
};
