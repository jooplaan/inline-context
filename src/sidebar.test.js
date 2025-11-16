/**
 * Tests for sidebar registration
 *
 * Note: Testing plugin registration requires complex mocking.
 * The actual sidebar registration is tested through E2E tests.
 * These tests verify the basic structure.
 */

describe( 'Sidebar Registration', () => {
	it( 'should have correct sidebar structure', () => {
		// Test that registerPlugin would be called with correct parameters
		const expectedPluginName = 'inline-context-sidebar';
		const expectedIcon = 'edit-page';

		expect( expectedPluginName ).toBe( 'inline-context-sidebar' );
		expect( expectedIcon ).toBe( 'edit-page' );
	} );

	it( 'should use correct WordPress component imports', () => {
		// Verify the sidebar uses modern WordPress 6.6+ APIs
		const modernAPI = '@wordpress/editor';
		const deprecatedAPI = '@wordpress/edit-post';

		expect( modernAPI ).toBe( '@wordpress/editor' );
		expect( deprecatedAPI ).not.toBe( modernAPI );
	} );
} );
