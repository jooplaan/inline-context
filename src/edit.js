/**
 * React Quill editor component for WordPress inline context
 * Features:
 * - Rich text editing with bold, italic, links, and lists (no underline)
 * - Add new inline context to selected text
 * - Edit existing inline context
 * - Remove existing inline context
 * - WordPress-friendly styling and keyboard shortcuts
 */
import { useEffect, useState } from '@wordpress/element';
import { RichTextToolbarButton } from '@wordpress/block-editor';
import { Popover, Button, Flex, FlexItem } from '@wordpress/components';
import { applyFormat, removeFormat } from '@wordpress/rich-text';
import { __ } from '@wordpress/i18n';
import ReactQuill from 'react-quill';

// WordPress-friendly Quill configuration
const QUILL_MODULES = {
	toolbar: [
		['bold', 'italic'],
		['link'],
		[{ 'list': 'ordered' }, { 'list': 'bullet' }],
		['clean']
	],
};

const QUILL_FORMATS = [
	'bold', 'italic', 'link', 'list', 'bullet'
];

export default function Edit({ isActive, value, onChange }) {
	const [isOpen, setIsOpen] = useState(false);
	const [anchor, setAnchor] = useState();


	const activeFormat = value.activeFormats?.find((f) => f.type === 'trybes/inline-context');
	const currentText = activeFormat?.attributes?.['data-inline-context'] || '';
	const [text, setText] = useState(currentText);

	const toggle = () => {
		setIsOpen((prev) => {
			const next = !prev;
			if (next) {
				// Sync the editor with the currently selected inline context when opening
				const fmt = value.activeFormats?.find((f) => f.type === 'trybes/inline-context');
				setText(fmt?.attributes?.['data-inline-context'] || '');
			}
			return next;
		});
	};



	// Compute a virtual element for the current selection so the Popover can anchor near the selected text
	useEffect(() => {
		if (!isOpen) return;
		const getSelectionAnchor = () => {
			const sel = globalThis.getSelection?.();
			if (!sel || sel.rangeCount === 0) return undefined;
			const range = sel.getRangeAt(0).cloneRange();
			try {
				if (range.collapsed) {
					// Insert a zero-width marker to measure caret position
					const marker = document.createElement('span');
					marker.appendChild(document.createTextNode('\u200b'));
					range.insertNode(marker);
					const rect = marker.getBoundingClientRect();
					marker.remove();
					return rect;
				}
				return range.getBoundingClientRect();
			} catch (error) {
				// eslint-disable-next-line no-console
				console.warn('Failed to get selection anchor:', error);
				return undefined;
			}
		};
		const rect = getSelectionAnchor();
		if (!rect) {
			setAnchor(undefined);
			return;
		}
		// Create a virtual element compatible with Floating UI/Popover expectations
		const virtualEl = {
			getBoundingClientRect: () => rect,
			contextElement: document.body,
		};
		setAnchor(virtualEl);
	}, [isOpen, value?.start, value?.end]);

	// Also resync text if the active format changes while popover is open
	useEffect(() => {
		if (!isOpen) return;
		setText(currentText);
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, [activeFormat?.attributes?.['data-inline-context']]);

	// Add handy keyboard shortcuts for the popover
	useEffect(() => {
		if (!isOpen) return;
		const onKeyDown = (e) => {
			if ((e.metaKey || e.ctrlKey) && e.key === 'Enter') {
				e.preventDefault();
				apply();
			}
			if (e.key === 'Escape') {
				e.preventDefault();
				setIsOpen(false);
			}
		};
		document.addEventListener('keydown', onKeyDown);
		return () => document.removeEventListener('keydown', onKeyDown);
	}, [isOpen]);



	const apply = () => {
		onChange(
			applyFormat(value, {
					type: 'trybes/inline-context',
				attributes: {
						'data-inline-context': text,
				},
			})
		);
		setIsOpen(false);
	};

	const remove = () => {
		onChange(removeFormat(value, 'trybes/inline-context'));
		setIsOpen(false);
	};

	// Handle React Quill editor changes
	const handleQuillChange = (content) => {
		setText(content);
	};

	return (
		<>
			<RichTextToolbarButton
				icon="editor-ol"
				title={__('Inline Context', 'inline-context')}
				onClick={toggle}
				isActive={isActive}
			/>

			{isOpen && (
				<Popover
					anchor={anchor}
					position="bottom center"
					focusOnMount="firstElement"
					onClose={() => setIsOpen(false)}
				>
					<div className="wp-reveal-popover wp-reveal-quill-editor">
						<div className="wp-reveal-quill-label">
							{__('Inline Context', 'inline-context')}
						</div>
						<ReactQuill
							value={text}
							onChange={handleQuillChange}
							modules={QUILL_MODULES}
							formats={QUILL_FORMATS}
							placeholder={__('Add inline context…', 'inline-context')}
							theme="snow"
						/>
						<div className="wp-reveal-quill-help">
							{__('Use the toolbar above to format your inline context with bold, italic, links, and lists.', 'inline-context')}
						</div>
					</div>
					<Flex justify="space-between" align="center">
						<FlexItem>
							{isActive && (
								<Button 
									variant="tertiary" 
									isDestructive
									onClick={remove}
								>
									{__('Remove Inline Context', 'inline-context')}
								</Button>
							)}
						</FlexItem>
						<FlexItem>
							<Flex gap={2}>
								<Button variant="secondary" onClick={() => setIsOpen(false)}>
									{__('Cancel', 'inline-context')}
								</Button>
								<Button variant="primary" onClick={apply}>
									{__('Save', 'inline-context')}
								</Button>
							</Flex>
						</FlexItem>
					</Flex>
				</Popover>
			)}
		</>
	);
}
