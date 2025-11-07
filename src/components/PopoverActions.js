/**
 * Popover action buttons component
 */

import { Button, Flex, FlexItem } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

export default function PopoverActions( {
	isActive,
	onRemove,
	onCancel,
	onSave,
	onKeyDown,
	removeRef,
	cancelRef,
	saveRef,
} ) {
	return (
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
				<Flex gap={ 2 }>
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
	);
}
