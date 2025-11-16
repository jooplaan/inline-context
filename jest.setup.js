/**
 * Jest setup file
 * Runs before each test file
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
	constructor() {}
	disconnect() {}
	observe() {}
	takeRecords() {
		return [];
	}
	unobserve() {}
};

// Suppress console errors and warnings in tests (optional)
// Uncomment if you want cleaner test output
// global.console = {
// 	...console,
// 	error: jest.fn(),
// 	warning: jest.fn(),
// };
