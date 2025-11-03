import { registerFormatType } from '@wordpress/rich-text';
import { __ } from '@wordpress/i18n';
import Edit from './edit';
import './style.scss';
import './editor.scss';
import 'react-quill/dist/quill.snow.css';

registerFormatType( 'trybes/inline-context', {
	title: __( 'Inline Context', 'inline-context' ),
	tagName: 'button',
	className: 'wp-inline-context',
	attributes: {
		'data-inline-context': 'data-inline-context',
		'data-anchor-id': 'data-anchor-id',
		id: 'id',
		type: 'type',
		'aria-expanded': 'aria-expanded',
	},
	edit: Edit,
} );
