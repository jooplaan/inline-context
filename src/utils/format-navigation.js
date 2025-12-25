/**
 * Format Navigation Utilities
 *
 * Pure utility functions for detecting and navigating inline context formats
 * in WordPress Rich Text values.
 *
 * @package InlineContext
 */

const FORMAT_TYPE = 'jooplaan/inline-context';

/**
 * Find all ranges of inline-context formats in the rich text value
 *
 * Scans through the formats array and identifies continuous ranges
 * where the inline-context format is applied.
 *
 * @param {Object} value - Rich text value object from WordPress
 * @param {Array}  value.formats - Array of format arrays
 * @param {string} value.text - The text content
 * @return {Array<{start: number, end: number}>} Array of range objects
 */
export const findAllInlineContextRanges = ( value ) => {
	const { formats } = value;
	if ( ! formats ) {
		return [];
	}

	const ranges = [];
	let currentRange = null;

	formats.forEach( ( formatArray, index ) => {
		const hasFormat = formatArray?.some(
			( f ) => f?.type === FORMAT_TYPE
		);

		if ( hasFormat ) {
			if ( ! currentRange ) {
				// Start new range
				currentRange = { start: index, end: index + 1 };
			} else {
				// Extend current range
				currentRange.end = index + 1;
			}
		} else if ( currentRange ) {
			// End current range
			ranges.push( currentRange );
			currentRange = null;
		}
	} );

	// Don't forget the last range if it extends to the end
	if ( currentRange ) {
		ranges.push( currentRange );
	}

	return ranges;
};

/**
 * Find the next format range after the cursor position
 *
 * @param {Array<{start: number, end: number}>} ranges - Array of format ranges
 * @param {number}                               currentPosition - Current cursor position
 * @return {Object|null} Range object or null if no next format found
 */
export const findNextFormat = ( ranges, currentPosition ) => {
	return ranges.find( ( range ) => range.start > currentPosition ) || null;
};

/**
 * Find the previous format range before the cursor position
 *
 * Searches in reverse to find the format that ends before or at the cursor.
 *
 * @param {Array<{start: number, end: number}>} ranges - Array of format ranges
 * @param {number}                               currentPosition - Current cursor position
 * @return {Object|null} Range object or null if no previous format found
 */
export const findPreviousFormat = ( ranges, currentPosition ) => {
	// Search in reverse
	for ( let i = ranges.length - 1; i >= 0; i-- ) {
		if ( ranges[ i ].end <= currentPosition ) {
			return ranges[ i ];
		}
	}
	return null;
};

/**
 * Check if the caret is positioned inside an inline-context format
 *
 * Checks both the left and right positions around the caret to determine
 * if it's within a format. Prefers the left side for boundary cases.
 *
 * @param {Object} value          - Rich text value object
 * @param {Array}  value.formats  - Array of format arrays
 * @param {number} caretPosition  - Current caret position
 * @return {boolean} True if caret is inside an inline-context format
 */
export const isCaretInFormat = ( value, caretPosition ) => {
	const { formats } = value;
	if ( ! formats || ! formats.length ) {
		return false;
	}

	// Check positions on both sides of the caret
	const leftIdx = Math.max( 0, caretPosition - 1 );
	const rightIdx = Math.min( formats.length - 1, caretPosition );

	const hasFormatLeft = formats[ leftIdx ]?.some(
		( f ) => f?.type === FORMAT_TYPE
	);
	const hasFormatRight = formats[ rightIdx ]?.some(
		( f ) => f?.type === FORMAT_TYPE
	);

	return hasFormatLeft || hasFormatRight;
};
