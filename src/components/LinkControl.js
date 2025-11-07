/**
 * Link control component for adding links to inline context
 */

import { Button, Flex, TextControl } from '@wordpress/components';
import { URLInput } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';

export default function LinkControl( {
	isVisible,
	linkUrl,
	linkText,
	onUrlChange,
	onTextChange,
	onInsert,
	onCancel,
	urlInputRef,
	linkTextInputRef,
	insertButtonRef,
	cancelButtonRef,
	onKeyDown,
} ) {
	if ( ! isVisible ) {
		return null;
	}

	return (
		<div className="wp-reveal-link-control">
			<div className="wp-reveal-link-url-wrapper">
				<label
					htmlFor="inline-context-url-input"
					className="components-base-control__label"
				>
					{ __( 'Link URL', 'inline-context' ) }
				</label>
				<URLInput
					id="inline-context-url-input"
					ref={ urlInputRef }
					value={ linkUrl }
					onChange={ ( url, post ) => {
						onUrlChange( url );
						// If a post is selected, use its title as link text
						if ( post?.title && ! linkText ) {
							onTextChange( post.title );
						}
					} }
					placeholder={ __(
						'Search or paste URL',
						'inline-context'
					) }
					onKeyDown={ ( e ) => onKeyDown( e, urlInputRef ) }
					__nextHasNoMarginBottom
				/>
				<p className="components-base-control__help">
					{ __(
						'Start typing to search for internal content or paste any URL',
						'inline-context'
					) }
				</p>
			</div>
			<TextControl
				ref={ linkTextInputRef }
				label={ __( 'Link Text (optional)', 'inline-context' ) }
				value={ linkText }
				onChange={ onTextChange }
				placeholder={ __( 'Custom link text', 'inline-context' ) }
				onKeyDown={ ( e ) => onKeyDown( e, linkTextInputRef ) }
			/>
			<Flex gap={ 2 } style={ { marginTop: '8px' } }>
				<Button
					ref={ insertButtonRef }
					variant="primary"
					size="small"
					onClick={ onInsert }
					disabled={ ! linkUrl }
					onKeyDown={ ( e ) => onKeyDown( e, insertButtonRef ) }
				>
					{ __( 'Insert Link', 'inline-context' ) }
				</Button>
				<Button
					ref={ cancelButtonRef }
					variant="secondary"
					size="small"
					onClick={ onCancel }
					onKeyDown={ ( e ) => onKeyDown( e, cancelButtonRef ) }
				>
					{ __( 'Cancel', 'inline-context' ) }
				</Button>
			</Flex>
		</div>
	);
}
