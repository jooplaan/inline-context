/**
 * Sidebar Registration
 *
 * Registers the Notes Sidebar panel in the WordPress block editor.
 */

import { registerPlugin } from '@wordpress/plugins';
import { PluginSidebar, PluginSidebarMoreMenuItem } from '@wordpress/editor';
import { __ } from '@wordpress/i18n';
import NotesSidebar from './components/NotesSidebar';

const InlineContextSidebar = () => (
	<>
		<PluginSidebarMoreMenuItem target="inline-context-sidebar">
			{ __( 'Inline Context Notes', 'inline-context' ) }
		</PluginSidebarMoreMenuItem>
		<PluginSidebar
			name="inline-context-sidebar"
			title={ __( 'Inline Context Notes', 'inline-context' ) }
			icon="edit-page"
		>
			<NotesSidebar />
		</PluginSidebar>
	</>
);

registerPlugin( 'inline-context-sidebar', {
	render: InlineContextSidebar,
} );
