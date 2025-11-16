/**
 * Jest setup file
 * Runs before each test file
 *
 * @jest-environment jsdom
 */

// Add custom matchers from jest-dom
import '@testing-library/jest-dom';

// Mock window.matchMedia (used by some WordPress components)
Object.defineProperty( window, 'matchMedia', {
	writable: true,
	value: jest.fn().mockImplementation( ( query ) => ( {
		matches: false,
		media: query,
		onchange: null,
		addListener: jest.fn(), // deprecated
		removeListener: jest.fn(), // deprecated
		addEventListener: jest.fn(),
		removeEventListener: jest.fn(),
		dispatchEvent: jest.fn(),
	} ) ),
} );

// Mock IntersectionObserver
global.IntersectionObserver = class IntersectionObserver {
	disconnect() {}
	observe() {}
	takeRecords() {
		return [];
	}
	unobserve() {}
};

// Mock @wordpress/compose to avoid mousetrap issues in jsdom
jest.mock( '@wordpress/compose', () => ( {
	useKeyboardShortcut: jest.fn(),
	useMediaQuery: jest.fn(),
	useResizeObserver: jest.fn(),
	useCopyToClipboard: jest.fn(),
	createHigherOrderComponent: jest.fn(
		( mapComponentToEnhancedComponent ) => ( Component ) => Component
	),
	pure: jest.fn( ( Component ) => Component ),
	compose: jest.fn(),
	ifCondition: jest.fn(),
	withState: jest.fn(),
	withInstanceId: jest.fn(),
	withSafeTimeout: jest.fn(),
	observableMap: jest.fn( () => ( {
		get: jest.fn(),
		set: jest.fn(),
		delete: jest.fn(),
		subscribe: jest.fn( () => jest.fn() ),
	} ) ),
} ) );

// Mock @wordpress/components to avoid ESM dependency issues
jest.mock( '@wordpress/components', () => {
	const React = require( 'react' );
	return {
		Button: ( {
			children,
			onClick,
			onKeyDown,
			ref,
			variant,
			isDestructive,
			disabled,
			size,
		} ) =>
			React.createElement(
				'button',
				{
					onClick,
					onKeyDown,
					ref,
					className: `wp-button wp-button--${ variant || 'default' }`,
					disabled,
					'data-destructive': isDestructive,
					'data-size': size,
				},
				children
			),
		Flex: ( { children, justify, align, gap } ) =>
			React.createElement(
				'div',
				{
					className: 'wp-flex',
					style: {
						display: 'flex',
						justifyContent: justify,
						alignItems: align,
						gap,
					},
				},
				children
			),
		FlexItem: ( { children } ) =>
			React.createElement(
				'div',
				{ className: 'wp-flex-item' },
				children
			),
		CheckboxControl: ( { label, checked, onChange } ) =>
			React.createElement( 'label', null, [
				React.createElement( 'input', {
					key: 'input',
					type: 'checkbox',
					checked,
					onChange: ( e ) => onChange( e.target.checked ),
				} ),
				React.createElement( 'span', { key: 'label' }, label ),
			] ),
		Modal: ( { title, children, size } ) =>
			React.createElement(
				'div',
				{
					className: 'components-modal__screen-overlay',
					role: 'dialog',
					'aria-label': title,
					'data-size': size,
				},
				[
					React.createElement(
						'div',
						{
							key: 'content',
							className: 'components-modal__content',
						},
						[
							React.createElement(
								'div',
								{
									key: 'header',
									className: 'components-modal__header',
								},
								[
									React.createElement(
										'h1',
										{ key: 'title' },
										title
									),
								]
							),
							React.createElement(
								'div',
								{ key: 'body' },
								children
							),
						]
					),
				]
			),
		SelectControl: ( {
			label,
			value,
			options,
			onChange,
			disabled,
			help,
		} ) =>
			React.createElement(
				'div',
				{ className: 'components-base-control' },
				[
					React.createElement(
						'label',
						{
							key: 'label',
							className: 'components-base-control__label',
						},
						label
					),
					React.createElement(
						'select',
						{
							key: 'select',
							value,
							onChange: disabled
								? undefined
								: ( e ) => onChange( e.target.value ),
							disabled,
						},
						options.map( ( opt, i ) =>
							React.createElement(
								'option',
								{ key: i, value: opt.value },
								opt.label
							)
						)
					),
					help &&
						React.createElement(
							'p',
							{
								key: 'help',
								className: 'components-base-control__help',
							},
							help
						),
				]
			),
		TextControl: ( {
			label,
			value,
			onChange,
			placeholder,
			onKeyDown,
			ref,
		} ) =>
			React.createElement(
				'div',
				{ className: 'components-base-control' },
				[
					React.createElement(
						'label',
						{
							key: 'label',
							className: 'components-base-control__label',
						},
						label
					),
					React.createElement( 'input', {
						key: 'input',
						type: 'text',
						value,
						onChange: ( e ) => onChange( e.target.value ),
						placeholder,
						onKeyDown,
						ref,
					} ),
				]
			),
	};
} );

// Mock @wordpress/block-editor
jest.mock( '@wordpress/block-editor', () => {
	const React = require( 'react' );
	return {
		URLInput: ( { id, value, onChange, placeholder, onKeyDown, ref } ) =>
			React.createElement( 'input', {
				id,
				type: 'text',
				value,
				onChange: ( e ) => {
					// URLInput calls onChange with url and optionally a post object
					const inputValue = e.target.value;
					onChange( inputValue, null );
				},
				placeholder,
				onKeyDown,
				ref,
				className: 'block-editor-url-input',
			} ),
	};
} );

// Suppress console errors and warnings in tests (optional)
// Uncomment if you want cleaner test output
// global.console = {
// 	...console,
// 	error: jest.fn(),
// 	warning: jest.fn(),
// };
