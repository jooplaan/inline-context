import { registerFormatType } from '@wordpress/rich-text';
import { __ } from '@wordpress/i18n';
import { store as keyboardShortcutsStore } from '@wordpress/keyboard-shortcuts';
import { dispatch } from '@wordpress/data';
import Edit from './edit';
import './style.scss';
import './editor.scss';
import 'react-quill/dist/quill.snow.css';
import './sidebar'; // Register the sidebar panel

// Register keyboard shortcuts with WordPress
dispatch( keyboardShortcutsStore ).registerShortcut( {
	name: 'inline-context/insert',
	category: 'block',
	description: __( 'Insert inline context', 'inline-context' ),
	keyCombination: {
		modifier: 'primaryShift',
		character: 'i',
	},
} );

dispatch( keyboardShortcutsStore ).registerShortcut( {
	name: 'inline-context/edit',
	category: 'block',
	description: __( 'Edit inline context at cursor', 'inline-context' ),
	keyCombination: {
		modifier: 'primaryShift',
		character: 'k',
	},
} );

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
