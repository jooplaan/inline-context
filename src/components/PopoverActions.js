/**
 * Popover action buttons component
 */

import {
	Button,
	Flex,
	FlexItem,
	CheckboxControl,
	Modal,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';

export default function PopoverActions( {
	isActive,
	onRemove,
	onCancel,
	onSave,
	onKeyDown,
	removeRef,
	cancelRef,
	saveRef,
	isReusable,
	onReusableChange,
	isReusedNote,
} ) {
	const [ showConfirmDialog, setShowConfirmDialog ] = useState( false );

	const handleReusableChange = ( checked ) => {
		// If unchecking and this is a reused note, show confirmation
		if ( ! checked && isReusedNote && isReusable ) {
			setShowConfirmDialog( true );
		} else {
			onReusableChange( checked );
		}
	};

	const handleConfirmUnreusable = () => {
		setShowConfirmDialog( false );
		onReusableChange( false );
	};

	const handleCancelUnreusable = () => {
		setShowConfirmDialog( false );
	};

	return (
		<>
			{ showConfirmDialog && (
				<Modal
					title={ __( 'Make note non-reusable?', 'inline-context' ) }
					onRequestClose={ handleCancelUnreusable }
					size="small"
				>
					<p>
						{ __(
							'Are you sure you want to mark this note as no longer reusable? A new note will be created.',
							'inline-context'
						) }
					</p>
					<Flex gap={ 2 } justify="flex-end">
						<Button
							variant="secondary"
							onClick={ handleCancelUnreusable }
						>
							{ __( 'Cancel', 'inline-context' ) }
						</Button>
						<Button
							variant="primary"
							onClick={ handleConfirmUnreusable }
						>
							{ __( 'Create New Note', 'inline-context' ) }
						</Button>
					</Flex>
				</Modal>
			) }

			<Flex justify="space-between" align="center">
				<FlexItem>
					{ isActive && (
						<Button
							ref={ removeRef }
							variant="tertiary"
							isDestructive
							onClick={ onRemove }
							onKeyDown={ ( e ) => onKeyDown( e, removeRef ) }
						>
							{ __( 'Delete', 'inline-context' ) }
						</Button>
					) }
				</FlexItem>
				<FlexItem>
					<Flex gap={ 3 } align="center">
						<CheckboxControl
							label={ __(
								'Use as reusable note',
								'inline-context'
							) }
							checked={ isReusable }
							onChange={ handleReusableChange }
						/>
						<Button
							ref={ cancelRef }
							variant="secondary"
							onClick={ onCancel }
							onKeyDown={ ( e ) => onKeyDown( e, cancelRef ) }
						>
							{ __( 'Cancel', 'inline-context' ) }
						</Button>
						<Button
							ref={ saveRef }
							variant="primary"
							onClick={ onSave }
							onKeyDown={ ( e ) => onKeyDown( e, saveRef ) }
						>
							{ __( 'Save', 'inline-context' ) }
						</Button>
					</Flex>
				</FlexItem>
			</Flex>
		</>
	);
}
