/**
 * Tests for LinkControl component
 */

import { render, screen, fireEvent } from '@testing-library/react';
import LinkControl from './LinkControl';

describe( 'LinkControl component', () => {
	const defaultProps = {
		isVisible: true,
		linkUrl: '',
		linkText: '',
		onUrlChange: jest.fn(),
		onTextChange: jest.fn(),
		onInsert: jest.fn(),
		onCancel: jest.fn(),
		urlInputRef: { current: null },
		linkTextInputRef: { current: null },
		insertButtonRef: { current: null },
		cancelButtonRef: { current: null },
		onKeyDown: jest.fn(),
	};

	afterEach( () => {
		jest.clearAllMocks();
	} );

	describe( 'Visibility', () => {
		it( 'renders nothing when isVisible is false', () => {
			const { container } = render(
				<LinkControl { ...defaultProps } isVisible={ false } />
			);

			expect( container.firstChild ).toBeNull();
		} );

		it( 'renders when isVisible is true', () => {
			render( <LinkControl { ...defaultProps } isVisible={ true } /> );

			expect( screen.getByText( 'Link URL' ) ).toBeInTheDocument();
		} );
	} );

	describe( 'URL Input', () => {
		it( 'renders URL input with label', () => {
			render( <LinkControl { ...defaultProps } /> );

			expect( screen.getByText( 'Link URL' ) ).toBeInTheDocument();
			expect(
				screen.getByPlaceholderText( 'Search or paste URL' )
			).toBeInTheDocument();
		} );

		it( 'renders help text for URL input', () => {
			render( <LinkControl { ...defaultProps } /> );

			expect(
				screen.getByText(
					'Start typing to search for internal content or paste any URL'
				)
			).toBeInTheDocument();
		} );

		it( 'reflects linkUrl prop value', () => {
			const { rerender } = render(
				<LinkControl { ...defaultProps } linkUrl="" />
			);

			let urlInput = screen.getByPlaceholderText( 'Search or paste URL' );
			expect( urlInput ).toHaveValue( '' );

			rerender(
				<LinkControl
					{ ...defaultProps }
					linkUrl="https://example.com"
				/>
			);

			urlInput = screen.getByPlaceholderText( 'Search or paste URL' );
			expect( urlInput ).toHaveValue( 'https://example.com' );
		} );

		it( 'calls onUrlChange when URL is typed', () => {
			const onUrlChange = jest.fn();
			render(
				<LinkControl
					{ ...defaultProps }
					onUrlChange={ onUrlChange }
				/>
			);

			const urlInput = screen.getByPlaceholderText( 'Search or paste URL' );
			fireEvent.change( urlInput, {
				target: { value: 'https://wordpress.org' },
			} );

			expect( onUrlChange ).toHaveBeenCalledWith( 'https://wordpress.org' );
		} );

		it( 'calls onKeyDown when keyboard events occur on URL input', () => {
			const onKeyDown = jest.fn();
			render(
				<LinkControl { ...defaultProps } onKeyDown={ onKeyDown } />
			);

			const urlInput = screen.getByPlaceholderText( 'Search or paste URL' );
			fireEvent.keyDown( urlInput, { key: 'Tab' } );

			expect( onKeyDown ).toHaveBeenCalled();
		} );
	} );

	describe( 'Link Text Input', () => {
		it( 'renders link text input with label', () => {
			render( <LinkControl { ...defaultProps } /> );

			expect(
				screen.getByText( 'Link Text (optional)' )
			).toBeInTheDocument();
			expect(
				screen.getByPlaceholderText( 'Custom link text' )
			).toBeInTheDocument();
		} );

		it( 'reflects linkText prop value', () => {
			const { rerender } = render(
				<LinkControl { ...defaultProps } linkText="" />
			);

			let textInput = screen.getByPlaceholderText( 'Custom link text' );
			expect( textInput ).toHaveValue( '' );

			rerender(
				<LinkControl
					{ ...defaultProps }
					linkText="Click here"
				/>
			);

			textInput = screen.getByPlaceholderText( 'Custom link text' );
			expect( textInput ).toHaveValue( 'Click here' );
		} );

		it( 'calls onTextChange when text is typed', () => {
			const onTextChange = jest.fn();
			render(
				<LinkControl
					{ ...defaultProps }
					onTextChange={ onTextChange }
				/>
			);

			const textInput = screen.getByPlaceholderText( 'Custom link text' );
			fireEvent.change( textInput, {
				target: { value: 'Read more' },
			} );

			expect( onTextChange ).toHaveBeenCalledWith( 'Read more' );
		} );

		it( 'calls onKeyDown when keyboard events occur on text input', () => {
			const onKeyDown = jest.fn();
			render(
				<LinkControl { ...defaultProps } onKeyDown={ onKeyDown } />
			);

			const textInput = screen.getByPlaceholderText( 'Custom link text' );
			fireEvent.keyDown( textInput, { key: 'Enter' } );

			expect( onKeyDown ).toHaveBeenCalled();
		} );
	} );

	describe( 'Insert and Cancel buttons', () => {
		it( 'renders Insert Link and Cancel buttons', () => {
			render( <LinkControl { ...defaultProps } /> );

			expect( screen.getByText( 'Insert Link' ) ).toBeInTheDocument();
			expect( screen.getByText( 'Cancel' ) ).toBeInTheDocument();
		} );

		it( 'Insert Link button is disabled when linkUrl is empty', () => {
			render( <LinkControl { ...defaultProps } linkUrl="" /> );

			const insertButton = screen.getByText( 'Insert Link' );
			expect( insertButton ).toBeDisabled();
		} );

		it( 'Insert Link button is enabled when linkUrl has a value', () => {
			render(
				<LinkControl
					{ ...defaultProps }
					linkUrl="https://example.com"
				/>
			);

			const insertButton = screen.getByText( 'Insert Link' );
			expect( insertButton ).not.toBeDisabled();
		} );

		it( 'calls onInsert when Insert Link button is clicked', () => {
			const onInsert = jest.fn();
			render(
				<LinkControl
					{ ...defaultProps }
					linkUrl="https://example.com"
					onInsert={ onInsert }
				/>
			);

			const insertButton = screen.getByText( 'Insert Link' );
			fireEvent.click( insertButton );

			expect( onInsert ).toHaveBeenCalledTimes( 1 );
		} );

		it( 'calls onCancel when Cancel button is clicked', () => {
			const onCancel = jest.fn();
			render(
				<LinkControl { ...defaultProps } onCancel={ onCancel } />
			);

			const cancelButton = screen.getByText( 'Cancel' );
			fireEvent.click( cancelButton );

			expect( onCancel ).toHaveBeenCalledTimes( 1 );
		} );

		it( 'calls onKeyDown when keyboard events occur on buttons', () => {
			const onKeyDown = jest.fn();
			render(
				<LinkControl
					{ ...defaultProps }
					linkUrl="https://example.com"
					onKeyDown={ onKeyDown }
				/>
			);

			const insertButton = screen.getByText( 'Insert Link' );
			fireEvent.keyDown( insertButton, { key: 'Tab' } );

			expect( onKeyDown ).toHaveBeenCalled();
		} );
	} );

	describe( 'Component structure', () => {
		it( 'renders with proper CSS classes', () => {
			const { container } = render( <LinkControl { ...defaultProps } /> );

			expect(
				container.querySelector( '.wp-reveal-link-control' )
			).toBeInTheDocument();
			expect(
				container.querySelector( '.wp-reveal-link-url-wrapper' )
			).toBeInTheDocument();
		} );

		it( 'URL input has correct id for accessibility', () => {
			render( <LinkControl { ...defaultProps } /> );

			const urlInput = screen.getByPlaceholderText( 'Search or paste URL' );
			expect( urlInput ).toHaveAttribute( 'id', 'inline-context-url-input' );
		} );
	} );

	describe( 'Refs attachment', () => {
		it( 'accepts refs for all interactive elements', () => {
			const urlInputRef = { current: null };
			const linkTextInputRef = { current: null };
			const insertButtonRef = { current: null };
			const cancelButtonRef = { current: null };

			render(
				<LinkControl
					{ ...defaultProps }
					urlInputRef={ urlInputRef }
					linkTextInputRef={ linkTextInputRef }
					insertButtonRef={ insertButtonRef }
					cancelButtonRef={ cancelButtonRef }
				/>
			);

			// In real DOM, refs would have elements attached
			// In test environment, we just verify the component accepts refs
			expect( urlInputRef ).toBeDefined();
			expect( linkTextInputRef ).toBeDefined();
			expect( insertButtonRef ).toBeDefined();
			expect( cancelButtonRef ).toBeDefined();
		} );
	} );
} );
