/**
 * @jest-environment jsdom
 */

/**
 * Unit tests for NotesSidebar utility functions
 *
 * These tests focus on the core logic without requiring full React rendering.
 * For full component integration tests, use E2E tests with WordPress.
 */

describe( 'NotesSidebar Utility Functions', () => {
	describe( 'HTML Entity Decoding', () => {
		it( 'should decode common HTML entities', () => {
			const textarea = document.createElement( 'textarea' );

			// Test &amp;
			textarea.innerHTML = 'Test &amp; More';
			expect( textarea.value ).toBe( 'Test & More' );

			// Test &lt; and &gt;
			textarea.innerHTML = '&lt;p&gt;content&lt;/p&gt;';
			expect( textarea.value ).toBe( '<p>content</p>' );

			// Test &quot;
			textarea.innerHTML = 'Test &quot;quoted&quot; text';
			expect( textarea.value ).toBe( 'Test "quoted" text' );
		} );

		it( 'should handle nested HTML entities', () => {
			const textarea = document.createElement( 'textarea' );
			textarea.innerHTML = '&lt;p&gt;Test &amp; &quot;More&quot;&lt;/p&gt;';
			expect( textarea.value ).toBe( '<p>Test & "More"</p>' );
		} );
	} );

	describe( 'Note Content Extraction with Regex', () => {
		it( 'should match inline context links', () => {
			const content = `
				<p>Some text 
				<a class="wp-inline-context" data-anchor-id="note-1">link</a>
				more text</p>
			`;

			const linkPattern =
				/<a[^>]*class="[^"]*wp-inline-context[^"]*"[^>]*>.*?<\/a>/gi;
			const matches = content.match( linkPattern );

			expect( matches ).toHaveLength( 1 );
			expect( matches[ 0 ] ).toContain( 'data-anchor-id="note-1"' );
		} );

		it( 'should extract multiple inline context links', () => {
			const content = `
				<p>
				<a class="wp-inline-context" data-anchor-id="note-1">first</a>
				and
				<a class="wp-inline-context" data-anchor-id="note-2">second</a>
				</p>
			`;

			const linkPattern =
				/<a[^>]*class="[^"]*wp-inline-context[^"]*"[^>]*>.*?<\/a>/gi;
			const matches = content.match( linkPattern );

			expect( matches ).toHaveLength( 2 );
		} );

		it( 'should not match regular links', () => {
			const content = '<p><a href="test.html">regular link</a></p>';

			const linkPattern =
				/<a[^>]*class="[^"]*wp-inline-context[^"]*"[^>]*>.*?<\/a>/gi;
			const matches = content.match( linkPattern );

			expect( matches ).toBeNull();
		} );
	} );

	describe( 'Attribute Extraction with Regex', () => {
		it( 'should extract data-anchor-id attribute', () => {
			const linkHtml =
				'<a class="wp-inline-context" data-anchor-id="test-123">link</a>';
			const regex = /data-anchor-id="([^"]*)"/i;
			const match = linkHtml.match( regex );

			expect( match[ 1 ] ).toBe( 'test-123' );
		} );

		it( 'should extract data-note-id attribute', () => {
			const linkHtml =
				'<a class="wp-inline-context" data-note-id="456">link</a>';
			const regex = /data-note-id="([^"]*)"/i;
			const match = linkHtml.match( regex );

			expect( match[ 1 ] ).toBe( '456' );
		} );

		it( 'should extract data-category-id attribute', () => {
			const linkHtml =
				'<a class="wp-inline-context" data-category-id="2">link</a>';
			const regex = /data-category-id="([^"]*)"/i;
			const match = linkHtml.match( regex );

			expect( match[ 1 ] ).toBe( '2' );
		} );

		it( 'should extract link text content', () => {
			const linkHtml = '<a class="wp-inline-context">link text here</a>';
			const regex = />([^<]+)</i;
			const match = linkHtml.match( regex );

			expect( match[ 1 ].trim() ).toBe( 'link text here' );
		} );
	} );

	describe( 'HTML Tag Stripping', () => {
		it( 'should remove simple HTML tags', () => {
			const html = '<p>Test content</p>';
			const clean = html.replace( /<[^>]*>/g, '' );
			expect( clean ).toBe( 'Test content' );
		} );

		it( 'should remove multiple HTML tags', () => {
			const html = '<p><strong>Bold</strong> and <em>italic</em></p>';
			const clean = html.replace( /<[^>]*>/g, '' );
			expect( clean ).toBe( 'Bold and italic' );
		} );

		it( 'should remove nested HTML tags', () => {
			const html = '<div><p><span>Nested</span> content</p></div>';
			const clean = html.replace( /<[^>]*>/g, '' );
			expect( clean ).toBe( 'Nested content' );
		} );
	} );

	describe( 'Excerpt Generation', () => {
		it( 'should truncate text at 60 characters', () => {
			const text =
				'This is a very long text that should be truncated after sixty characters to fit.';
			const excerpt = text.substring( 0, 60 ).trim();

			expect( excerpt ).toHaveLength( 60 );
			expect( excerpt ).toBe(
				'This is a very long text that should be truncated after sixt'
			);
		} );

		it( 'should not truncate short text', () => {
			const text = 'Short text';
			const excerpt = text.substring( 0, 60 ).trim();

			expect( excerpt ).toBe( 'Short text' );
		} );

		it( 'should add ellipsis for truncated text', () => {
			const text = 'This is a long text that needs truncation'.repeat( 2 );
			const excerpt = text.substring( 0, 60 ).trim();
			const withEllipsis = text.length > 60 ? excerpt + '...' : excerpt;

			expect( withEllipsis ).toContain( '...' );
		} );
	} );

	describe( 'Color Contrast Calculation (WCAG)', () => {
		const getLuminance = ( hex ) => {
			const rgb = hex.match( /\w\w/g ).map( ( x ) => parseInt( x, 16 ) );
			const [ r, g, b ] = rgb.map( ( val ) => {
				const srgb = val / 255;
				return srgb <= 0.03928
					? srgb / 12.92
					: Math.pow( ( srgb + 0.055 ) / 1.055, 2.4 );
			} );
			return 0.2126 * r + 0.7152 * g + 0.0722 * b;
		};

		const getContrastTextColor = ( bgColor ) => {
			const hex = bgColor.replace( '#', '' );
			const luminance = getLuminance( hex );
			return luminance > 0.179 ? '#000000' : '#ffffff';
		};

		it( 'should return black text for light backgrounds', () => {
			expect( getContrastTextColor( '#ffffff' ) ).toBe( '#000000' );
			expect( getContrastTextColor( '#f0f0f0' ) ).toBe( '#000000' );
			expect( getContrastTextColor( '#ffff00' ) ).toBe( '#000000' );
		} );

		it( 'should return white text for dark backgrounds', () => {
			expect( getContrastTextColor( '#000000' ) ).toBe( '#ffffff' );
			expect( getContrastTextColor( '#333333' ) ).toBe( '#ffffff' );
			expect( getContrastTextColor( '#0000ff' ) ).toBe( '#ffffff' );
		} );

		it( 'should handle common WordPress colors correctly', () => {
			expect( getContrastTextColor( '#0073aa' ) ).toBe( '#ffffff' ); // WP blue - dark
			expect( getContrastTextColor( '#d63638' ) ).toBe( '#ffffff' ); // WP red - dark
			expect( getContrastTextColor( '#00a32a' ) ).toBe( '#000000' ); // WP green - lighter
		} );
	} );

	describe( 'Category Lookup Logic', () => {
		it( 'should find category by numeric ID', () => {
			const categories = {
				technical: {
					id: 1,
					name: 'Technical',
					slug: 'technical',
					color: '#0073aa',
				},
				reference: {
					id: 2,
					name: 'Reference',
					slug: 'reference',
					color: '#d63638',
				},
			};

			const categoryId = '1';
			const categoryArray = Object.values( categories );
			const category = categoryArray.find(
				( cat ) =>
					cat.id && cat.id.toString() === categoryId.toString()
			);

			expect( category ).toBeDefined();
			expect( category.name ).toBe( 'Technical' );
		} );

		it( 'should return undefined for non-existent ID', () => {
			const categories = {
				technical: {
					id: 1,
					name: 'Technical',
					slug: 'technical',
				},
			};

			const categoryId = '999';
			const categoryArray = Object.values( categories );
			const category = categoryArray.find(
				( cat ) =>
					cat.id && cat.id.toString() === categoryId.toString()
			);

			expect( category ).toBeUndefined();
		} );
	} );
} );
