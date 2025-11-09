const path = require( 'node:path' );
const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );

module.exports = {
	...defaultConfig,
	entry: {
		index: path.resolve( process.cwd(), 'src', 'index.js' ),
		frontend: path.resolve( process.cwd(), 'src', 'frontend.js' ),
		'cpt-editor': path.resolve( process.cwd(), 'src', 'cpt-editor.js' ),
	},
	// Performance: Suppress size warnings for editor bundle
	// The index.js bundle (~245 KB) includes ReactQuill, which is necessary for rich text editing.
	// This is editor-only code (not loaded on frontend), properly cached by WordPress,
	// and only downloaded once per version per user. The size is acceptable for admin context.
	performance: {
		...defaultConfig.performance,
		maxAssetSize: 300000, // 300 KB - reasonable for editor bundles with rich text components
		maxEntrypointSize: 300000,
	},
};
