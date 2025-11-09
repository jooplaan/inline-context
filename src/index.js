import { registerFormatType } from '@wordpress/rich-text';
import { __ } from '@wordpress/i18n';
import Edit from './edit';
import './style.scss';
import './editor.scss';
import 'react-quill/dist/quill.snow.css';

registerFormatType( 'jooplaan/inline-context', {
	title: __( 'Inline Context', 'inline-context' ),
	tagName: 'a',
	className: 'wp-inline-context',
	attributes: {
		'data-inline-context': 'data-inline-context',
		'data-anchor-id': 'data-anchor-id',
		'data-category-id': 'data-category-id',
		'data-note-id': 'data-note-id',
		href: 'href',
		id: 'id',
		role: 'role',
		'aria-expanded': 'aria-expanded',
	},
	edit: Edit,
} );
