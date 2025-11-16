/**
 * Tests for text extraction utilities
 */

import { getLinkedText } from './text';

describe( 'text.js utilities', () => {
	describe( 'getLinkedText', () => {
		const FORMAT_TYPE = 'jooplaan/inline-context';

		it( 'returns empty string if value is null or undefined', () => {
			expect( getLinkedText( null ) ).toBe( '' );
			expect( getLinkedText( undefined ) ).toBe( '' );
		} );

		it( 'returns empty string if value has no text', () => {
			const value = {
				text: '',
				start: 0,
				end: 0,
				formats: [],
			};
			expect( getLinkedText( value ) ).toBe( '' );
		} );

		it( 'returns selected text when there is an explicit selection', () => {
			const value = {
				text: 'Hello world, this is a test',
				start: 6,
				end: 11, // Selects "world"
				formats: [],
			};
			expect( getLinkedText( value ) ).toBe( 'world' );
		} );

		it( 'returns empty string when caret is not within inline-context format', () => {
			const value = {
				text: 'Hello world',
				start: 5,
				end: 5, // Caret position, no selection
				formats: [], // No formats
			};
			expect( getLinkedText( value ) ).toBe( '' );
		} );

		it( 'expands to full inline-context run when caret is within it (left side)', () => {
			const inlineContextFormat = { type: FORMAT_TYPE };

			const value = {
				text: 'Click this link here',
				start: 8, // Caret is in "this"
				end: 8,
				formats: [
					undefined, // C
					undefined, // l
					undefined, // i
					undefined, // c
					undefined, // k
					undefined, // (space)
					[ inlineContextFormat ], // t
					[ inlineContextFormat ], // h
					[ inlineContextFormat ], // i
					[ inlineContextFormat ], // s
					undefined, // (space)
					undefined, // l
					undefined, // i
					undefined, // n
					undefined, // k
				],
			};

			expect( getLinkedText( value ) ).toBe( 'this' );
		} );

		it( 'expands to full inline-context run when caret is at start of format', () => {
			const inlineContextFormat = { type: FORMAT_TYPE };

			const value = {
				text: 'Click this link here',
				start: 6, // Caret before "this"
				end: 6,
				formats: [
					undefined,
					undefined,
					undefined,
					undefined,
					undefined,
					undefined, // (space before "this")
					[ inlineContextFormat ], // t - start of format
					[ inlineContextFormat ], // h
					[ inlineContextFormat ], // i
					[ inlineContextFormat ], // s
					undefined,
				],
			};

			expect( getLinkedText( value ) ).toBe( 'this' );
		} );

		it( 'expands to full inline-context run when caret is at end of format', () => {
			const inlineContextFormat = { type: FORMAT_TYPE };

			const value = {
				text: 'Click this link',
				start: 10, // Caret after "this"
				end: 10,
				formats: [
					undefined,
					undefined,
					undefined,
					undefined,
					undefined,
					undefined,
					[ inlineContextFormat ], // t
					[ inlineContextFormat ], // h
					[ inlineContextFormat ], // i
					[ inlineContextFormat ], // s - end of format
					undefined, // (space after "this")
				],
			};

			expect( getLinkedText( value ) ).toBe( 'this' );
		} );

		it( 'handles format as single object instead of array', () => {
			const inlineContextFormat = { type: FORMAT_TYPE };

			const value = {
				text: 'Click link',
				start: 7,
				end: 7,
				formats: [
					undefined,
					undefined,
					undefined,
					undefined,
					undefined,
					undefined,
					inlineContextFormat, // Not in array
					inlineContextFormat,
					inlineContextFormat,
					inlineContextFormat,
				],
			};

			expect( getLinkedText( value ) ).toBe( 'link' );
		} );

		it( 'returns longest contiguous run of inline-context format', () => {
			const inlineContextFormat = { type: FORMAT_TYPE };

			const value = {
				text: 'one two three four',
				start: 10, // Caret in "three"
				end: 10,
				formats: [
					undefined, // o
					undefined, // n
					undefined, // e
					undefined, // (space)
					undefined, // t
					undefined, // w
					undefined, // o
					undefined, // (space)
					[ inlineContextFormat ], // t
					[ inlineContextFormat ], // h
					[ inlineContextFormat ], // r
					[ inlineContextFormat ], // e
					[ inlineContextFormat ], // e
					undefined, // (space)
					undefined, // f
					undefined, // o
					undefined, // u
					undefined, // r
				],
			};

			expect( getLinkedText( value ) ).toBe( 'three' );
		} );

		it( 'handles multiple formats on same character', () => {
			const inlineContextFormat = { type: FORMAT_TYPE };
			const boldFormat = { type: 'core/bold' };

			const value = {
				text: 'bold link',
				start: 6,
				end: 6,
				formats: [
					undefined,
					undefined,
					undefined,
					undefined,
					undefined,
					[ boldFormat, inlineContextFormat ], // l - has both formats
					[ boldFormat, inlineContextFormat ], // i
					[ boldFormat, inlineContextFormat ], // n
					[ boldFormat, inlineContextFormat ], // k
				],
			};

			expect( getLinkedText( value ) ).toBe( 'link' );
		} );

		it( 'returns empty string when formats array is missing', () => {
			const value = {
				text: 'Hello world',
				start: 5,
				end: 5,
				// formats array missing
			};

			expect( getLinkedText( value ) ).toBe( '' );
		} );

		it( 'handles edge case with caret at beginning of text', () => {
			const inlineContextFormat = { type: FORMAT_TYPE };

			const value = {
				text: 'link here',
				start: 0,
				end: 0,
				formats: [
					[ inlineContextFormat ], // l
					[ inlineContextFormat ], // i
					[ inlineContextFormat ], // n
					[ inlineContextFormat ], // k
					undefined, // (space)
				],
			};

			expect( getLinkedText( value ) ).toBe( 'link' );
		} );

		it( 'handles edge case with caret at end of text', () => {
			const inlineContextFormat = { type: FORMAT_TYPE };

			const value = {
				text: 'Click link',
				start: 10,
				end: 10,
				formats: [
					undefined,
					undefined,
					undefined,
					undefined,
					undefined,
					undefined,
					[ inlineContextFormat ], // l
					[ inlineContextFormat ], // i
					[ inlineContextFormat ], // n
					[ inlineContextFormat ], // k
				],
			};

			expect( getLinkedText( value ) ).toBe( 'link' );
		} );

		it( 'returns empty string when caret is between two inline-context runs', () => {
			const inlineContextFormat = { type: FORMAT_TYPE };

			const value = {
				text: 'link1 link2',
				start: 5, // Space between the two links
				end: 5,
				formats: [
					[ inlineContextFormat ], // l
					[ inlineContextFormat ], // i
					[ inlineContextFormat ], // n
					[ inlineContextFormat ], // k
					[ inlineContextFormat ], // 1
					undefined, // (space - caret here)
					[ inlineContextFormat ], // l
					[ inlineContextFormat ], // i
					[ inlineContextFormat ], // n
					[ inlineContextFormat ], // k
					[ inlineContextFormat ], // 2
				],
			};

			// Caret at index 5 (space), check left (index 4) and right (index 5)
			// Left (4) has format, so it should expand left
			expect( getLinkedText( value ) ).toBe( 'link1' );
		} );
	} );
} );
