/**
 * Tests for CategorySelector component
 */

import { render, screen, fireEvent } from '@testing-library/react';
import CategorySelector from './CategorySelector';

describe( 'CategorySelector component', () => {
	const mockCategories = {
		definition: {
			name: 'Definition',
			slug: 'definition',
		},
		'technical-term': {
			name: 'Technical Term',
			slug: 'technical-term',
		},
		clarification: {
			name: 'Clarification',
			slug: 'clarification',
		},
	};

	describe( 'Rendering', () => {
		it( 'renders nothing when categories object is empty', () => {
			const { container } = render(
				<CategorySelector
					value=""
					onChange={ jest.fn() }
					categories={ {} }
				/>
			);

			expect( container.firstChild ).toBeNull();
		} );

		it( 'renders select control with label when categories exist', () => {
			render(
				<CategorySelector
					value=""
					onChange={ jest.fn() }
					categories={ mockCategories }
				/>
			);

			expect(
				screen.getByText( 'Category (optional)' )
			).toBeInTheDocument();
			expect( screen.getByRole( 'combobox' ) ).toBeInTheDocument();
		} );

		it( 'renders "No category" as first option', () => {
			render(
				<CategorySelector
					value=""
					onChange={ jest.fn() }
					categories={ mockCategories }
				/>
			);

			const select = screen.getByRole( 'combobox' );
			const options = select.querySelectorAll( 'option' );

			expect( options[ 0 ] ).toHaveTextContent( 'No category' );
			expect( options[ 0 ] ).toHaveValue( '' );
		} );

		it( 'renders all categories as options', () => {
			render(
				<CategorySelector
					value=""
					onChange={ jest.fn() }
					categories={ mockCategories }
				/>
			);

			const select = screen.getByRole( 'combobox' );
			const options = select.querySelectorAll( 'option' );

			// First option is "No category", then 3 category options
			expect( options.length ).toBe( 4 );
			expect( options[ 1 ] ).toHaveTextContent( 'Definition' );
			expect( options[ 2 ] ).toHaveTextContent( 'Technical Term' );
			expect( options[ 3 ] ).toHaveTextContent( 'Clarification' );
		} );

		it( 'renders help text', () => {
			render(
				<CategorySelector
					value=""
					onChange={ jest.fn() }
					categories={ mockCategories }
				/>
			);

			expect(
				screen.getByText( 'Choose a category to display a custom icon' )
			).toBeInTheDocument();
		} );
	} );

	describe( 'Value handling', () => {
		it( 'reflects current value prop', () => {
			const { rerender } = render(
				<CategorySelector
					value=""
					onChange={ jest.fn() }
					categories={ mockCategories }
				/>
			);

			let select = screen.getByRole( 'combobox' );
			expect( select ).toHaveValue( '' );

			rerender(
				<CategorySelector
					value="definition"
					onChange={ jest.fn() }
					categories={ mockCategories }
				/>
			);

			select = screen.getByRole( 'combobox' );
			expect( select ).toHaveValue( 'definition' );
		} );

		it( 'calls onChange with selected value', () => {
			const onChange = jest.fn();
			render(
				<CategorySelector
					value=""
					onChange={ onChange }
					categories={ mockCategories }
				/>
			);

			const select = screen.getByRole( 'combobox' );
			fireEvent.change( select, { target: { value: 'technical-term' } } );

			expect( onChange ).toHaveBeenCalledWith( 'technical-term' );
		} );

		it( 'calls onChange with empty string when "No category" is selected', () => {
			const onChange = jest.fn();
			render(
				<CategorySelector
					value="definition"
					onChange={ onChange }
					categories={ mockCategories }
				/>
			);

			const select = screen.getByRole( 'combobox' );
			fireEvent.change( select, { target: { value: '' } } );

			expect( onChange ).toHaveBeenCalledWith( '' );
		} );
	} );

	describe( 'Disabled state', () => {
		it( 'is not disabled by default', () => {
			render(
				<CategorySelector
					value=""
					onChange={ jest.fn() }
					categories={ mockCategories }
				/>
			);

			const select = screen.getByRole( 'combobox' );
			expect( select ).not.toBeDisabled();
		} );

		it( 'is disabled when disabled prop is true', () => {
			render(
				<CategorySelector
					value=""
					onChange={ jest.fn() }
					categories={ mockCategories }
					disabled={ true }
				/>
			);

			const select = screen.getByRole( 'combobox' );
			expect( select ).toBeDisabled();
		} );

		it( 'does not call onChange when disabled', () => {
			const onChange = jest.fn();
			render(
				<CategorySelector
					value=""
					onChange={ onChange }
					categories={ mockCategories }
					disabled={ true }
				/>
			);

			const select = screen.getByRole( 'combobox' );
			fireEvent.change( select, { target: { value: 'definition' } } );

			// onChange should not be called when disabled
			expect( onChange ).not.toHaveBeenCalled();
		} );
	} );

	describe( 'Edge cases', () => {
		it( 'handles single category', () => {
			const singleCategory = {
				definition: {
					name: 'Definition',
					slug: 'definition',
				},
			};

			render(
				<CategorySelector
					value=""
					onChange={ jest.fn() }
					categories={ singleCategory }
				/>
			);

			const select = screen.getByRole( 'combobox' );
			const options = select.querySelectorAll( 'option' );

			expect( options.length ).toBe( 2 ); // "No category" + 1 category
			expect( options[ 1 ] ).toHaveTextContent( 'Definition' );
		} );

		it( 'handles category with special characters in name', () => {
			const specialCategories = {
				'special-&-chars': {
					name: 'Special & Characters',
					slug: 'special-&-chars',
				},
			};

			render(
				<CategorySelector
					value=""
					onChange={ jest.fn() }
					categories={ specialCategories }
				/>
			);

			const select = screen.getByRole( 'combobox' );
			const options = select.querySelectorAll( 'option' );

			expect( options[ 1 ] ).toHaveTextContent( 'Special & Characters' );
			expect( options[ 1 ] ).toHaveValue( 'special-&-chars' );
		} );
	} );
} );
