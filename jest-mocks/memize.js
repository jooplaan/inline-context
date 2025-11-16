/**
 * Mock implementation of memize for Jest tests
 * Memize is a memoization library - for tests we just pass through the function
 */

// Simple passthrough mock - just return the function without memoization
const memize = ( fn ) => fn;

module.exports = memize;
module.exports.default = memize;
