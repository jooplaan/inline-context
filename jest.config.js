module.exports = {
	...require( '@wordpress/scripts/config/jest-unit.config' ),
	testMatch: [
		'**/__tests__/**/*.[jt]s?(x)',
		'**/?(*.)+(spec|test).[jt]s?(x)',
	],
	setupFilesAfterEnv: [ '<rootDir>/jest.setup.js' ],
	moduleNameMapper: {
		'\\.(css|less|scss|sass)$': 'identity-obj-proxy',
		// Let WordPress packages be imported normally
		'^@wordpress/(.*)$': '<rootDir>/node_modules/@wordpress/$1',
		// Mock memize to avoid ESM issues
		'^memize$': '<rootDir>/jest-mocks/memize.js',
	},
	coveragePathIgnorePatterns: [
		'/node_modules/',
		'/vendor/',
		'/build/',
		'/dist/',
	],
	testEnvironment: 'jsdom',
	collectCoverageFrom: [
		'src/**/*.{js,jsx}',
		'!src/**/*.test.{js,jsx}',
		'!src/**/*.spec.{js,jsx}',
		'!src/**/index.js',
	],
	transformIgnorePatterns: [
		'node_modules/(?!(@wordpress|memize|uuid)/)',
	],
};
