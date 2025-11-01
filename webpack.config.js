const path = require( 'node:path' );
const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );

module.exports = {
	...defaultConfig,
	entry: {
		index: path.resolve( process.cwd(), 'src', 'index.js' ),
		frontend: path.resolve( process.cwd(), 'src', 'frontend.js' ),
	},
};
