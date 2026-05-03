/**
 * Tests for anchor ID generation and validation utilities
 */

import {
	generateAnchorId,
	ensureUniqueAnchorId,
	hasDuplicateAnchorId,
} from './anchor';

describe( 'anchor.js utilities', () => {
	describe( 'generateAnchorId', () => {
		beforeEach( () => {
			// Set up global window object
			global.window = {};
			// Mock Date.now() for predictable testing
			jest.spyOn( Date, 'now' ).mockReturnValue( 1700000000000 );
			// Mock Math.random() for predictable testing
			jest.spyOn( Math, 'random' ).mockReturnValue( 0.123456789 );
		} );

		afterEach( () => {
			jest.restoreAllMocks();
			delete global.window;
		} );

		it( 'generates anchor ID with correct format', () => {
			const id = generateAnchorId();
			expect( id ).toMatch( /^context-note-[a-z0-9]+-[a-z0-9]+$/ );
		} );

		it( 'includes timestamp in base36 format', () => {
			const id = generateAnchorId();
			const timestamp = Date.now().toString( 36 );
			expect( id ).toContain( timestamp );
		} );

		it( 'includes random string component', () => {
			const id = generateAnchorId();
			const parts = id.split( '-' );
			expect( parts ).toHaveLength( 4 ); // context-note-{timestamp}-{random}
			expect( parts[ 3 ] ).toHaveLength( 5 ); // Random part is 5 chars
		} );

		it( 'generates unique IDs on subsequent calls', () => {
			jest.restoreAllMocks(); // Use real Date.now() and Math.random()

			const id1 = generateAnchorId();
			const id2 = generateAnchorId();

			expect( id1 ).not.toBe( id2 );
		} );

		it( 'applies WordPress filter hook if available', () => {
			const mockApplyFilters = jest.fn( () => 'filtered-anchor-id' );

			global.window = {
				wp: {
					hooks: {
						applyFilters: mockApplyFilters,
					},
				},
			};

			const id = generateAnchorId();

			expect( mockApplyFilters ).toHaveBeenCalledWith(
				'inline_context_generate_anchor_id',
				expect.stringContaining( 'context-note-' ),
				expect.objectContaining( {
					timestamp: expect.any( String ),
					random: expect.any( String ),
				} )
			);
			expect( id ).toBe( 'filtered-anchor-id' );

			delete global.window;
		} );

		it( 'works without WordPress hooks available', () => {
			global.window = {};

			const id = generateAnchorId();

			expect( id ).toMatch( /^context-note-[a-z0-9]+-[a-z0-9]+$/ );

			delete global.window;
		} );
	} );

	describe( 'ensureUniqueAnchorId', () => {
		beforeEach( () => {
			// Set up basic window object for all tests
			global.window = {};
		} );

		afterEach( () => {
			delete global.window;
		} );

		it( 'returns proposedId if it does not exist in editor', () => {
			const mockEditor = {
				getBlocks: jest.fn( () => [] ),
			};

			global.window = {
				wp: {
					data: {
						select: jest.fn( () => mockEditor ),
					},
				},
			};

			const result = ensureUniqueAnchorId( 'context-note-test-123' );

			expect( result ).toBe( 'context-note-test-123' );
		} );

		it( 'generates new ID if proposedId is empty', () => {
			const result = ensureUniqueAnchorId( '' );

			expect( result ).toMatch( /^context-note-[a-z0-9]+-[a-z0-9]+$/ );
		} );

		it( 'generates new ID if proposedId is null', () => {
			const result = ensureUniqueAnchorId( null );

			expect( result ).toMatch( /^context-note-[a-z0-9]+-[a-z0-9]+$/ );
		} );

		it( 'generates new ID if proposedId appears more than once', () => {
			// Create blocks that when stringified will have the data-anchor-id appear twice
			const mockBlocks = [
				{
					name: 'core/paragraph',
					attributes: {
						// This will create a JSON key-value pair that matches the regex
						'data-anchor-id': 'context-note-test-123',
					},
				},
				{
					name: 'core/paragraph',
					attributes: {
						// Second occurrence of the same ID
						'data-anchor-id': 'context-note-test-123',
					},
				},
			];

			const mockEditor = {
				getBlocks: jest.fn( () => mockBlocks ),
			};

			global.window = {
				wp: {
					data: {
						select: jest.fn( () => mockEditor ),
					},
				},
			};

			const result = ensureUniqueAnchorId( 'context-note-test-123' );

			// Should generate new ID since it appears twice in the stringified content
			expect( result ).not.toBe( 'context-note-test-123' );
			expect( result ).toMatch( /^context-note-[a-z0-9]+-[a-z0-9]+$/ );
		} );

		it( 'uses DOM fallback if editor API is unavailable', () => {
			// Create a mock DOM element
			const mockElement = document.createElement( 'a' );
			mockElement.setAttribute(
				'data-anchor-id',
				'context-note-existing'
			);
			document.body.appendChild( mockElement );

			global.window = {}; // No editor API

			const result1 = ensureUniqueAnchorId( 'context-note-existing' );
			const result2 = ensureUniqueAnchorId( 'context-note-new' );

			// Existing ID should generate new one
			expect( result1 ).not.toBe( 'context-note-existing' );
			expect( result1 ).toMatch( /^context-note-[a-z0-9]+-[a-z0-9]+$/ );

			// New ID should be accepted
			expect( result2 ).toBe( 'context-note-new' );

			// Cleanup
			document.body.removeChild( mockElement );
		} );

		it( 'escapes special regex characters in anchor ID', () => {
			const mockEditor = {
				getBlocks: jest.fn( () => [] ),
			};

			global.window = {
				wp: {
					data: {
						select: jest.fn( () => mockEditor ),
					},
				},
			};

			// ID with special regex characters that need escaping
			const specialId = 'context-note-test.with$pecial[chars]';
			const result = ensureUniqueAnchorId( specialId );

			// Should not throw error and should handle the ID correctly
			expect( result ).toBe( specialId );
		} );
	} );

	describe( 'hasDuplicateAnchorId', () => {
		beforeEach( () => {
			delete global.window;
		} );

		it( 'returns false if editor API is unavailable', () => {
			global.window = {};

			const result = hasDuplicateAnchorId( 'context-note-test' );

			expect( result ).toBe( false );
		} );

		it( 'returns false if anchor ID appears only once', () => {
			const mockEditor = {
				getEditedPostContent: jest.fn(
					() =>
						'<p>Some text <a data-anchor-id="context-note-test">link</a> more text</p>'
				),
			};

			global.window = {
				wp: {
					data: {
						select: jest.fn( () => mockEditor ),
					},
				},
			};

			const result = hasDuplicateAnchorId( 'context-note-test' );

			expect( result ).toBe( false );
		} );

		it( 'returns true if anchor ID appears multiple times', () => {
			const mockEditor = {
				getEditedPostContent: jest.fn(
					() =>
						'<p>First <a data-anchor-id="context-note-test">link</a> and second <a data-anchor-id="context-note-test">link</a></p>'
				),
			};

			global.window = {
				wp: {
					data: {
						select: jest.fn( () => mockEditor ),
					},
				},
			};

			const result = hasDuplicateAnchorId( 'context-note-test' );

			expect( result ).toBe( true );
		} );

		it( 'returns false if anchor ID is not found', () => {
			const mockEditor = {
				getEditedPostContent: jest.fn(
					() =>
						'<p>Some text <a data-anchor-id="context-note-other">link</a></p>'
				),
			};

			global.window = {
				wp: {
					data: {
						select: jest.fn( () => mockEditor ),
					},
				},
			};

			const result = hasDuplicateAnchorId( 'context-note-test' );

			expect( result ).toBe( false );
		} );

		it( 'escapes special regex characters in anchor ID', () => {
			const mockEditor = {
				getEditedPostContent: jest.fn(
					() =>
						'<p>Test <a data-anchor-id="context-note-test.with$pecial[chars]">link</a></p>'
				),
			};

			global.window = {
				wp: {
					data: {
						select: jest.fn( () => mockEditor ),
					},
				},
			};

			// Should not throw error
			const result = hasDuplicateAnchorId(
				'context-note-test.with$pecial[chars]'
			);

			expect( result ).toBe( false );
		} );
	} );
} );
