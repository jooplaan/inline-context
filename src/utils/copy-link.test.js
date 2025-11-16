/**
 * Tests for copy-link utilities
 *
 * @jest-environment jsdom
 */

// Note: This file has limited testing of getFrontendUrl() because it relies on
// @wordpress/data which is not available in the test environment. In a real WordPress
// environment, @wordpress/data is loaded globally. We focus on testing the clipboard
// functionality which is the core feature.

describe( 'copy-link.js utilities', () => {
	let mockClipboard;
	let copyAnchorLinkToClipboard;

	beforeEach( () => {
		// Clear module cache to get fresh copy
		jest.resetModules();

		// Mock clipboard API
		mockClipboard = {
			writeText: jest.fn().mockResolvedValue( undefined ),
		};

		// Setup minimal window object
		global.window = {
			navigator: { clipboard: mockClipboard },
			location: { href: 'https://example.com/current-page/' },
		};

		// Mock document methods for fallback tests
		global.document = {
			createElement: jest.fn( ( tag ) => {
				const el = {
					tagName: tag,
					value: '',
					style: {},
					select: jest.fn(),
				};
				return el;
			} ),
			body: {
				appendChild: jest.fn(),
				removeChild: jest.fn(),
			},
			execCommand: jest.fn( () => true ),
		};

		// Import after mocks are setup
		const copyLinkModule = require( './copy-link' );
		copyAnchorLinkToClipboard = copyLinkModule.copyAnchorLinkToClipboard;
	} );

	afterEach( () => {
		delete global.window;
		delete global.document;
		jest.clearAllMocks();
	} );

	describe( 'copyAnchorLinkToClipboard', () => {
		it( 'does nothing if anchorId is empty', async () => {
			const onSuccess = jest.fn();
			const onError = jest.fn();

			await copyAnchorLinkToClipboard( '', onSuccess, onError );

			expect( mockClipboard.writeText ).not.toHaveBeenCalled();
			expect( onSuccess ).not.toHaveBeenCalled();
			expect( onError ).not.toHaveBeenCalled();
		} );

		it( 'does nothing if anchorId is null', async () => {
			const onSuccess = jest.fn();
			const onError = jest.fn();

			await copyAnchorLinkToClipboard( null, onSuccess, onError );

			expect( mockClipboard.writeText ).not.toHaveBeenCalled();
			expect( onSuccess ).not.toHaveBeenCalled();
			expect( onError ).not.toHaveBeenCalled();
		} );

		it( 'copies link with window.location.href as base URL', async () => {
			const onSuccess = jest.fn();
			const onError = jest.fn();

			await copyAnchorLinkToClipboard(
				'context-note-abc123',
				onSuccess,
				onError
			);

			// Should use window.location.href and append anchor
			expect( mockClipboard.writeText ).toHaveBeenCalledWith(
				'https://example.com/current-page/#context-note-abc123'
			);
			expect( onSuccess ).toHaveBeenCalled();
			expect( onError ).not.toHaveBeenCalled();
		} );

		it( 'strips existing hash from URL before adding anchor', async () => {
			global.window.location.href =
				'https://example.com/page#existing-hash';

			const onSuccess = jest.fn();

			await copyAnchorLinkToClipboard(
				'context-note-test',
				onSuccess,
				null
			);

			// Should strip #existing-hash and add #context-note-test
			expect( mockClipboard.writeText ).toHaveBeenCalledWith(
				'https://example.com/page#context-note-test'
			);
			expect( onSuccess ).toHaveBeenCalled();
		} );

		it( 'uses fallback method with execCommand if clipboard API fails', async () => {
			// Make clipboard API fail
			mockClipboard.writeText.mockRejectedValue(
				new Error( 'Clipboard denied' )
			);

			const onSuccess = jest.fn();
			const onError = jest.fn();

			await copyAnchorLinkToClipboard(
				'context-note-fallback',
				onSuccess,
				onError
			);

			// Verify fallback was used
			expect( global.document.createElement ).toHaveBeenCalledWith(
				'textarea'
			);
			expect( global.document.execCommand ).toHaveBeenCalledWith(
				'copy'
			);
			expect( global.document.body.appendChild ).toHaveBeenCalled();
			expect( global.document.body.removeChild ).toHaveBeenCalled();
			expect( onSuccess ).toHaveBeenCalled();
			expect( onError ).not.toHaveBeenCalled();
		} );

		it( 'calls onError callback if both methods fail', async () => {
			// Make clipboard API fail
			mockClipboard.writeText.mockRejectedValue(
				new Error( 'Clipboard denied' )
			);

			// Make execCommand fail
			global.document.execCommand = jest.fn( () => {
				throw new Error( 'execCommand failed' );
			} );

			const onSuccess = jest.fn();
			const onError = jest.fn();

			await copyAnchorLinkToClipboard(
				'context-note-error',
				onSuccess,
				onError
			);

			expect( onSuccess ).not.toHaveBeenCalled();
			expect( onError ).toHaveBeenCalledWith( expect.any( Error ) );
		} );

		it( 'works without onSuccess or onError callbacks', async () => {
			// Should not throw
			await expect(
				copyAnchorLinkToClipboard( 'context-note-test', null, null )
			).resolves.not.toThrow();

			expect( mockClipboard.writeText ).toHaveBeenCalled();
		} );
	} );
} );
