/**
 * Category selector component
 */

import { SelectControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

export default function CategorySelector( { value, onChange, categories } ) {
	if ( Object.keys( categories ).length === 0 ) {
		return null;
	}

	return (
		<SelectControl
			label={ __( 'Category (optional)', 'inline-context' ) }
			value={ value }
			options={ [
				{
					label: __( 'No category', 'inline-context' ),
					value: '',
				},
				...Object.values( categories ).map( ( cat ) => ( {
					label: cat.name,
					value: cat.id,
				} ) ),
			] }
			onChange={ onChange }
			help={ __(
				'Choose a category to display a custom icon',
				'inline-context'
			) }
			__nextHasNoMarginBottom
		/>
	);
}
