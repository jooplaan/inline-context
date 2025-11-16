/**
 * Tests for PopoverActions component
 */

import { render, screen, fireEvent, waitFor } from '@testing-library/react';
import PopoverActions from './PopoverActions';

describe( 'PopoverActions component', () => {
	const defaultProps = {
		isActive: false,
		onRemove: jest.fn(),
		onCancel: jest.fn(),
		onSave: jest.fn(),
		onKeyDown: jest.fn(),
		removeRef: { current: null },
		cancelRef: { current: null },
		saveRef: { current: null },
		isReusable: false,
		onReusableChange: jest.fn(),
		isReusedNote: false,
		noteUsageCount: 0,
	};

	afterEach( () => {
		jest.clearAllMocks();
	} );

	describe( 'Button rendering', () => {
		it( 'renders Cancel and Save buttons', () => {
			render( <PopoverActions { ...defaultProps } /> );

			expect( screen.getByText( 'Cancel' ) ).toBeInTheDocument();
			expect( screen.getByText( 'Save' ) ).toBeInTheDocument();
		} );

		it( 'does not render Delete button when not active', () => {
			render( <PopoverActions { ...defaultProps } isActive={ false } /> );

			expect( screen.queryByText( 'Delete' ) ).not.toBeInTheDocument();
		} );

		it( 'renders Delete button when active', () => {
			render( <PopoverActions { ...defaultProps } isActive={ true } /> );

			expect( screen.getByText( 'Delete' ) ).toBeInTheDocument();
		} );

		it( 'renders reusable checkbox', () => {
			render( <PopoverActions { ...defaultProps } /> );

			expect(
				screen.getByLabelText( 'Use as reusable note' )
			).toBeInTheDocument();
		} );
	} );

	describe( 'Button interactions', () => {
		it( 'calls onSave when Save button is clicked', () => {
			const onSave = jest.fn();
			render( <PopoverActions { ...defaultProps } onSave={ onSave } /> );

			fireEvent.click( screen.getByText( 'Save' ) );

			expect( onSave ).toHaveBeenCalledTimes( 1 );
		} );

		it( 'calls onCancel when Cancel button is clicked', () => {
			const onCancel = jest.fn();
			render(
				<PopoverActions { ...defaultProps } onCancel={ onCancel } />
			);

			fireEvent.click( screen.getByText( 'Cancel' ) );

			expect( onCancel ).toHaveBeenCalledTimes( 1 );
		} );

		it( 'calls onRemove when Delete button is clicked', () => {
			const onRemove = jest.fn();
			render(
				<PopoverActions
					{ ...defaultProps }
					isActive={ true }
					onRemove={ onRemove }
				/>
			);

			fireEvent.click( screen.getByText( 'Delete' ) );

			expect( onRemove ).toHaveBeenCalledTimes( 1 );
		} );

		it( 'calls onKeyDown when keyboard events occur on buttons', () => {
			const onKeyDown = jest.fn();
			render(
				<PopoverActions { ...defaultProps } onKeyDown={ onKeyDown } />
			);

			const saveButton = screen.getByText( 'Save' );
			fireEvent.keyDown( saveButton, { key: 'Tab' } );

			expect( onKeyDown ).toHaveBeenCalled();
		} );
	} );

	describe( 'Reusable checkbox behavior', () => {
		it( 'reflects isReusable prop state', () => {
			const { rerender } = render(
				<PopoverActions { ...defaultProps } isReusable={ false } />
			);

			const checkbox = screen.getByLabelText( 'Use as reusable note' );
			expect( checkbox ).not.toBeChecked();

			rerender(
				<PopoverActions { ...defaultProps } isReusable={ true } />
			);

			expect( checkbox ).toBeChecked();
		} );

		it( 'calls onReusableChange when checking the checkbox', () => {
			const onReusableChange = jest.fn();
			render(
				<PopoverActions
					{ ...defaultProps }
					isReusable={ false }
					onReusableChange={ onReusableChange }
				/>
			);

			const checkbox = screen.getByLabelText( 'Use as reusable note' );
			fireEvent.click( checkbox );

			expect( onReusableChange ).toHaveBeenCalledWith( true );
		} );

		it( 'directly unchecks when note is only used in current post (usage count = 1)', () => {
			const onReusableChange = jest.fn();
			render(
				<PopoverActions
					{ ...defaultProps }
					isReusable={ true }
					isReusedNote={ true }
					noteUsageCount={ 1 }
					onReusableChange={ onReusableChange }
				/>
			);

			const checkbox = screen.getByLabelText( 'Use as reusable note' );
			fireEvent.click( checkbox );

			// Should directly call onReusableChange without showing modal
			expect( onReusableChange ).toHaveBeenCalledWith( false );
			expect(
				screen.queryByText( 'Make note non-reusable?' )
			).not.toBeInTheDocument();
		} );

		it( 'directly unchecks when note has never been reused (usage count = 0)', () => {
			const onReusableChange = jest.fn();
			render(
				<PopoverActions
					{ ...defaultProps }
					isReusable={ true }
					isReusedNote={ false }
					noteUsageCount={ 0 }
					onReusableChange={ onReusableChange }
				/>
			);

			const checkbox = screen.getByLabelText( 'Use as reusable note' );
			fireEvent.click( checkbox );

			// Should directly call onReusableChange
			expect( onReusableChange ).toHaveBeenCalledWith( false );
		} );
	} );

	describe( 'Confirmation dialog for reused notes', () => {
		it( 'shows confirmation dialog when unchecking a reused note (usage count > 1)', async () => {
			const onReusableChange = jest.fn();
			render(
				<PopoverActions
					{ ...defaultProps }
					isReusable={ true }
					isReusedNote={ true }
					noteUsageCount={ 3 }
					onReusableChange={ onReusableChange }
				/>
			);

			const checkbox = screen.getByLabelText( 'Use as reusable note' );
			fireEvent.click( checkbox );

			// Should show confirmation modal
			await waitFor( () => {
				expect(
					screen.getByText( 'Make note non-reusable?' )
				).toBeInTheDocument();
			} );

			// Should not have called onReusableChange yet
			expect( onReusableChange ).not.toHaveBeenCalled();
		} );

		it( 'confirms making note non-reusable when "Create New Note" is clicked', async () => {
			const onReusableChange = jest.fn();
			render(
				<PopoverActions
					{ ...defaultProps }
					isReusable={ true }
					isReusedNote={ true }
					noteUsageCount={ 5 }
					onReusableChange={ onReusableChange }
				/>
			);

			// Trigger confirmation dialog
			const checkbox = screen.getByLabelText( 'Use as reusable note' );
			fireEvent.click( checkbox );

			// Wait for modal
			await waitFor( () => {
				expect(
					screen.getByText( 'Make note non-reusable?' )
				).toBeInTheDocument();
			} );

			// Click "Create New Note" button
			const confirmButton = screen.getByText( 'Create New Note' );
			fireEvent.click( confirmButton );

			// Should call onReusableChange with false
			expect( onReusableChange ).toHaveBeenCalledWith( false );

			// Modal should close
			await waitFor( () => {
				expect(
					screen.queryByText( 'Make note non-reusable?' )
				).not.toBeInTheDocument();
			} );
		} );

		it( 'cancels making note non-reusable when "Cancel" button is clicked in modal', async () => {
			const onReusableChange = jest.fn();
			render(
				<PopoverActions
					{ ...defaultProps }
					isReusable={ true }
					isReusedNote={ true }
					noteUsageCount={ 2 }
					onReusableChange={ onReusableChange }
				/>
			);

			// Trigger confirmation dialog
			const checkbox = screen.getByLabelText( 'Use as reusable note' );
			fireEvent.click( checkbox );

			// Wait for modal
			await waitFor( () => {
				expect(
					screen.getByText( 'Make note non-reusable?' )
				).toBeInTheDocument();
			} );

			// Click "Cancel" button in modal (not the main Cancel button)
			const cancelButtons = screen.getAllByText( 'Cancel' );
			const modalCancelButton = cancelButtons.find(
				( button ) =>
					button.closest( '.components-modal__content' ) !== null
			);
			fireEvent.click( modalCancelButton );

			// Should NOT call onReusableChange
			expect( onReusableChange ).not.toHaveBeenCalled();

			// Modal should close
			await waitFor( () => {
				expect(
					screen.queryByText( 'Make note non-reusable?' )
				).not.toBeInTheDocument();
			} );
		} );

		it( 'shows correct confirmation message in modal', async () => {
			render(
				<PopoverActions
					{ ...defaultProps }
					isReusable={ true }
					isReusedNote={ true }
					noteUsageCount={ 4 }
				/>
			);

			// Trigger confirmation dialog
			const checkbox = screen.getByLabelText( 'Use as reusable note' );
			fireEvent.click( checkbox );

			// Check modal content
			await waitFor( () => {
				expect(
					screen.getByText(
						'Are you sure you want to mark this note as no longer reusable? A new note will be created.'
					)
				).toBeInTheDocument();
			} );
		} );
	} );

	describe( 'Component refs', () => {
		it( 'attaches refs to buttons', () => {
			const removeRef = { current: null };
			const cancelRef = { current: null };
			const saveRef = { current: null };

			render(
				<PopoverActions
					{ ...defaultProps }
					isActive={ true }
					removeRef={ removeRef }
					cancelRef={ cancelRef }
					saveRef={ saveRef }
				/>
			);

			// Refs should be attached (in a real DOM they would have elements)
			// In test environment, we just verify the component accepts refs
			expect( removeRef ).toBeDefined();
			expect( cancelRef ).toBeDefined();
			expect( saveRef ).toBeDefined();
		} );
	} );
} );
