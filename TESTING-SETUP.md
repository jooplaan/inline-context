# PHP Unit Testing Setup - Complete

## ✅ What Was Installed

### Composer Packages

```bash
composer require --dev phpunit/phpunit:"^9.0"
composer require --dev yoast/phpunit-polyfills:"^1.0"
```

**Total**: 29 new packages installed for testing infrastructure

## 📁 Files Created

### 1. Test Infrastructure

- **`bin/install-wp-tests.sh`** - WordPress test suite installer (supports `.env` and CLI arguments)
- **`bin/setup-tests.sh`** - Interactive setup wizard for first-time configuration
- **`.env.example`** - Template for database credentials
- **`phpunit.xml`** - PHPUnit configuration
- **`tests/bootstrap.php`** - Test bootstrap file
- **`tests/README.md`** - Testing documentation

### 2. Test Files

- **`tests/test-cpt.php`** - Custom Post Type tests (7 test methods)
- **`tests/test-rest-api.php`** - REST API tests (7 test methods)
- **`tests/test-sync.php`** - Synchronization tests (4 test methods)
- **`tests/test-abilities.php`** - Abilities API tests (14 test methods)

**Total**: 32 test methods covering core functionality

**Current Status**: ✅ All 32 tests passing, 104 assertions, 0 skipped (WordPress 6.9+)

## 🎯 Test Coverage

### Custom Post Type Tests

- ✅ CPT registration
- ✅ CPT properties and labels
- ✅ Taxonomy registration
- ✅ Note creation
- ✅ Meta fields (is_reusable, usage_count, used_in_posts)
- ✅ Reusable note defaults
- ✅ Category assignment

### REST API Tests

- ✅ REST namespace registration
- ✅ Search endpoint exists
- ✅ Search returns results
- ✅ Track usage endpoint exists
- ✅ Track usage updates meta
- ✅ Handle removals endpoint exists
- ✅ Search filters by reusable status

### Synchronization Tests

- ✅ Usage tracking on post save
- ✅ Removal tracking
- ✅ Multiple usage tracking
- ✅ Category synchronization

### Abilities API Tests (WordPress 6.9+)

**Status**: ✅ All 14 tests passing on WordPress 6.9+

The Abilities API tests validate the plugin's integration with WordPress 6.9's new Abilities API feature. These tests required special handling due to loading order issues.

**Test Coverage:**

- ✅ Create note ability execution
- ✅ Search notes with filters (reusable_only)
- ✅ Get categories ability
- ✅ Get specific note by ID
- ✅ Create inline note (AI content generator helper)
- ✅ Empty title handling (WordPress allows at execute level)
- ✅ Non-existent note handling
- ✅ Search limit bounds (1-50)
- ✅ HTML sanitization (XSS prevention)
- ✅ Permission requirements (edit_posts capability)
- ✅ HTML markup structure validation
- ✅ Anchor ID generation
- ✅ Category assignment
- ✅ Reusability flags

**Loading Order Solution:**

The plugin loads via `muplugins_loaded` hook in test bootstrap, but WordPress Abilities API actions fire later. The test suite solves this by:

1. Manually triggering abilities registration in `setUp()` method
2. Using a static flag to register abilities only once across all tests
3. Setting expected incorrect usage warnings for "already registered" messages

See `tests/test-abilities.php` lines 23-68 for implementation details.

## 🚀 How to Use

### First Time Setup (Recommended)

```bash
# 1. Install test dependencies
composer install

# 2. Configure environment (copy example and edit with your credentials)
cp .env.example .env
nano .env  # Edit with your database credentials

# 3. Run automated setup
composer test:setup
```

The setup script will:

- Create `.env` from `.env.example` if it doesn't exist
- Prompt you to edit database credentials
- Install WordPress test suite using your `.env` configuration
- **Drop and recreate** the test database to ensure clean state
- Verify the setup

**Default `.env` Configuration:**

```bash
DB_NAME=wordpress_test
DB_USER=root
DB_PASS=
DB_HOST=localhost
WP_VERSION=latest
```

**Note**: The install script will drop and recreate the test database on each run to ensure a clean testing environment. This idempotent behavior was added to handle cases where the database already exists from previous test runs.

### Manual Setup (Advanced)

If you prefer command-line arguments over `.env`:

```bash
# Install WordPress test suite with specific credentials
bin/install-wp-tests.sh wordpress_test root '' localhost latest
```

**Note:** Command line arguments take precedence over `.env` values.

### Running Tests

```bash
# Run all tests
composer test:unit

# Or directly with PHPUnit
vendor/bin/phpunit

# Run specific test file
vendor/bin/phpunit tests/test-cpt.php

# Run with code coverage
vendor/bin/phpunit --coverage-html coverage/
```

### Updated Composer Scripts

```text
inline-context/
├── bin/
│   ├── install-wp-tests.sh    # WordPress test suite installer
│   └── setup-tests.sh          # Interactive setup wizard
├── tests/
│   ├── bootstrap.php           # Test bootstrap
│   ├── README.md               # Testing docs
│   ├── test-cpt.php           # CPT tests (7 tests)
│   ├── test-rest-api.php      # REST API tests (7 tests)
│   ├── test-sync.php          # Sync tests (4 tests)
│   └── test-abilities.php     # Abilities API tests (14 tests)
├── phpunit.xml                 # PHPUnit config
└── composer.json               # Updated with test scripts
```

Now `composer test` runs both linting AND unit tests! 🎉

## 📊 Test Structure

```text
inline-context/
├── bin/
│   └── install-wp-tests.sh    # WordPress test suite installer
├── tests/
│   ├── bootstrap.php           # Test bootstrap
│   ├── README.md               # Testing docs
│   ├── test-cpt.php           # CPT tests
│   ├── test-rest-api.php      # REST API tests
│   └── test-sync.php          # Sync tests
├── phpunit.xml                 # PHPUnit config
└── composer.json               # Updated with test scripts
```

## 🔧 Configuration

### phpunit.xml

- **Bootstrap**: `tests/bootstrap.php`
- **Test directory**: `./tests`
- **Test pattern**: `*-test.php`
- **Coverage**: Includes `includes/` and `inline-context.php`
- **Excludes**: `vendor/`, `node_modules/`

### WordPress Test Environment

- **WordPress Version**: 6.9+ (includes Abilities API)
- **Test library**: `/tmp/wordpress-tests-lib` (or macOS system temp)
- **WordPress core**: `/tmp/wordpress/` (or macOS system temp)
- **Database**: `wordpress_test` (configurable)
- **Abilities API**: Automatically tested when WordPress 6.9+ is present

**Note**: On macOS, WordPress may install to `/var/folders/.../T/wordpress/` instead of `/tmp/wordpress/`.

## 🎓 Next Steps

### JavaScript Unit Tests (✅ Complete)

**Status**: ✅ Implemented

JavaScript unit tests have been added to test sidebar functionality and utility functions.

#### What Was Added

**Test Files:**

- `src/components/NotesSidebar.test.js` - Tests for sidebar utility functions (20 tests)
- `src/sidebar.test.js` - Tests for sidebar registration (2 tests)

**Configuration:**

- `jest.config.js` - Jest configuration extending `@wordpress/scripts`
- `jest.setup.js` - Global test setup and mocks

**Dependencies Added:**

```json
{
  "devDependencies": {
    "@testing-library/react": "^14.x",
    "@testing-library/jest-dom": "^6.x",
    "@testing-library/user-event": "^14.x"
  }
}
```

#### Test Coverage

**NotesSidebar Utility Functions (20 tests):**

- ✅ HTML entity decoding (`&amp;`, `&lt;`, `&gt;`, `&quot;`)
- ✅ Regex-based note extraction from content
- ✅ Attribute extraction (anchor-id, note-id, category-id)
- ✅ Link text extraction
- ✅ HTML tag stripping for excerpts
- ✅ Excerpt generation (60 char truncation)
- ✅ WCAG color contrast calculation
- ✅ Category lookup by numeric ID

**Sidebar Registration (2 tests):**

- ✅ Sidebar structure validation
- ✅ Modern WordPress API usage verification

#### Running JavaScript Tests

```bash
# Run all JavaScript tests
npm run test:unit

# Run tests in watch mode
npm run test:unit:watch

# Run tests with coverage report
npm run test:unit:coverage

# Run all quality checks (linting + JS tests)
npm test
```

#### Test Philosophy

**What We Test:**

- ✅ Pure utility functions with predictable inputs/outputs
- ✅ Core logic (regex patterns, parsing, calculations)
- ✅ Edge cases (empty content, malformed HTML, missing data)
- ✅ Standards compliance (WCAG, HTML entities)

**What We Don't Test (Use E2E Instead):**

- ❌ WordPress integration (requires actual WP environment)
- ❌ React component rendering (complex mocking not worth maintaining)
- ❌ Block editor interactions (better tested in browser)
- ❌ User interactions (click handlers, scrolling, focus)

#### Test Results

```text
Test Suites: 2 passed, 2 total
Tests:       22 passed, 22 total
Snapshots:   0 total
Time:        ~2s
```

**Note:** Coverage shows 0% for React components because our tests focus on utility logic rather than component rendering. This is intentional - component integration is better tested through E2E tests in a real WordPress environment.

#### Example Test

```javascript
describe( 'HTML Entity Decoding', () => {
  it( 'should decode common HTML entities', () => {
    const textarea = document.createElement( 'textarea' );
    textarea.innerHTML = 'Test &amp; More';
    expect( textarea.value ).toBe( 'Test & More' );
  } );
} );
```

### Priority 3: E2E Tests (Optional)

```bash
# Install Playwright
npm install --save-dev @playwright/test

# Would need to create:
# - e2e/ directory
# - playwright.config.js
# - Test scenarios for full user workflows
```

## 📝 Current Test Quality Status

### Linting Warnings

The test files have some PHPCS warnings:

- Class names with underscores (WordPress test convention)
- Tabs vs spaces (test file convention)
- Some unused variables

These are **acceptable** for test files and follow WordPress testing conventions.

### To Exclude Tests from PHPCS

Add to `phpunit.xml` or create `.phpcs.xml.dist`:

```xml
<exclude-pattern>tests/</exclude-pattern>
```

## 🎯 Summary

**Status**: ✅ Complete PHP unit testing infrastructure

**Capabilities:**

- Test WordPress integration (CPT, REST API, hooks)
- Test WordPress 6.9+ Abilities API integration
- Test plugin functionality in isolation
- Generate code coverage reports
- CI/CD ready

**Commands Added:**

- `composer test:unit` - Run PHPUnit tests
- `composer test` - Run linting + tests

**Test Count**: 32 test methods across 4 test files, 104 assertions

**Test Results**: ✅ All tests passing on WordPress 6.9+ (0 skipped, 0 failures)

**Ready for**: Development, CI/CD, code coverage analysis
