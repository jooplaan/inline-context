# Inline Context

Add categorized "click to reveal" notes inline with your text. Create reusable notes via Custom Post Type and manage them centrally with modular, maintainable architecture. Perfect for short explanations, definitions, and asides with custom icons and full styling control.

## Version 2.0 Highlights

**Major refactoring and architectural improvements:**
- **Modular Architecture**: Complete codebase refactoring from monolithic to 7 class-based modules (83% main file reduction)
- **Enhanced Maintainability**: Separation of concerns with dedicated classes for CPT, Taxonomy Meta, Sync, Deletion, REST API, Frontend, and Utilities
- **Code Quality**: Full WordPress coding standards compliance (JavaScript and PHP)
- **Performance**: Optimized class autoloading and initialization
- **Developer Experience**: Clean, testable, and extensible codebase
- **Backward Compatibility**: Seamless upgrade from v1.x with preserved functionality

## What it does

- Lets you attach a brief note to any piece of text in the block editor
- **Create reusable notes** that can be used across multiple posts (v1.5)
- **Search and insert** existing notes instantly from the editor (v1.5)
- **Track usage** to see where each note is used on your site (v1.5)
- Organize notes with custom categories (Internal Article, External Article, Definition, Tip, etc.)
- Each category has distinct icons for closed/open states
- Readers click the highlighted text to show the note inline, and click again to hide it
- Full styling control through tabbed admin interface
- Keeps pages clean and easy to scan while still offering helpful context

## Key Features

### Reusable Notes System (v1.5)

- **Custom Post Type**: Notes stored as `inline_context_note` CPT for centralized management
- **Live Search**: Quickly find and insert existing notes from the editor popover
- **Create or Reuse**: Choose to create new notes or select from existing library
- **Usage Tracking**: See which posts use each note with enhanced list view
- **Filter by Type**: Filter notes marked as reusable in the admin list
- **Delete Protection**: Warnings when attempting to delete actively used notes
- **Cached Performance**: Notes cached in content for optimal frontend speed

### Category Management

- **Custom Categories**: Create unlimited categories to organize your notes
- **Icon Selection**: Choose from 30 curated Dashicons or use any of 300+ available
- **Visual Icon Picker**: Accessible modal with keyboard navigation (Esc, Tab, Enter)
- **Dual Icon States**: Different icons for closed/open states provide visual feedback
- **Color Coding**: Assign colors to each category for visual distinction

### Styling Controls

- **Tabbed Interface**: Clean admin settings with Categories and Styling tabs
- **Comprehensive Options**: Control colors, spacing, borders, shadows for every element
- **Live Preview**: Interactive preview shows exactly how notes will appear
- **Helpful Descriptions**: Every setting includes clear explanation of its purpose

### Rich Features

- **Direct Anchor Links**: Every note gets a unique URL anchor for easy sharing (#context-note-xxx)
- **Auto-Opening**: Notes automatically open when accessed via direct link
- **Smart Link Behavior**: Internal links stay in same tab, external links open in new tab with security
- **Rich Text Support**: Notes support bold, italic, links, lists with ReactQuill editor
- **Security First**: Content sanitized with DOMPurify before frontend display
- **Full Accessibility**: ARIA support, keyboard navigation, focus management

## How to use

### Managing notes library (v1.5)

1. Go to Inline Context > All Notes in WordPress admin
2. Create new notes or edit existing ones
3. Mark notes as "Reusable" to use them across multiple posts
4. View usage statistics to see where each note is used
5. Use the filter dropdown to show only reusable notes

### Setting up categories
1. Go to Settings > Inline Context
2. Click the Categories tab
3. Add or edit categories with custom names, icons, and colors
4. Click the icon button to open the visual picker
5. Save your settings

### Adding notes in the editor

1. Select the text you want to explain
2. Click the "Inline Context" button in the formatting toolbar
3. **NEW in v1.5**: Use the Search tab to find existing notes, or Create tab for new ones
4. Choose a category (optional)
5. Type your note content (or select from search results)
6. Save and view your page

### Customizing appearance
1. Go to Settings > Inline Context > Styling tab
2. Adjust link colors, note styling, chevron appearance
3. See changes immediately in the live preview
4. Save settings

## Customization

The plugin supports extensive styling customization through:
- **Admin Settings**: Tabbed interface with comprehensive visual controls
- **CSS Variables**: All settings use CSS custom properties for easy override
- **Live Preview**: See changes in real-time before saving

For detailed styling instructions and examples, see [STYLING.md](STYLING.md).

For developer filters and programmatic customization, see [FILTERS.md](FILTERS.md).

For future feature ideas and version 2.0 roadmap, see [ROADMAP.md](ROADMAP.md).

## Architecture (v2.0)

The plugin uses a modular, class-based architecture for optimal maintainability:

### Core Classes

- **`Inline_Context_CPT`** (855 lines) - Custom Post Type registration, metaboxes, and admin UI
- **`Inline_Context_Sync`** (496 lines) - Note usage tracking, reusable content synchronization, category sync
- **`Inline_Context_Deletion`** (198 lines) - Deletion protection for reusable notes, cleanup for non-reusable
- **`Inline_Context_REST_API`** (340 lines) - REST API endpoints for search, usage tracking, and note removal handling
- **`Inline_Context_Frontend`** (276 lines) - Noscript content generation, KSES filtering, asset enqueuing
- **`Inline_Context_Taxonomy_Meta`** (372 lines) - Taxonomy meta fields for category icons, colors, and admin UI enhancements
- **`Inline_Context_Utils`** (182 lines) - Category management, CSS variable management with backward compatibility

### Main Bootstrap File

- **`inline-context.php`** (395 lines, down from 2,291) - Clean plugin initialization and class loading
- **`admin-settings.php`** (728 lines) - Admin settings UI with tabbed interface (function-based)

### Benefits

- **Separation of Concerns**: Each class has a single, well-defined responsibility
- **Testability**: Modular code is easier to unit test and debug
- **Maintainability**: 83% reduction in main file size makes codebase navigable
- **Extensibility**: Clean interfaces for adding features without touching core logic
- **Performance**: Efficient class initialization and lazy loading where appropriate

## Accessibility

- Full keyboard navigation in icon picker (Esc to close, Tab/Shift+Tab to navigate)
- ARIA attributes on all interactive elements (`role="dialog"`, `aria-modal`, `aria-label`)
- Focus management (auto-focus on modal open, focus restoration on close)
- Focus trapping within modal
- Proper button semantics for icon selection
- Screen reader friendly labels on all icons
- Notes include `aria-expanded` and `role="note"` attributes

## Privacy

This plugin does not collect data, set cookies, or connect to external services.

## Requirements

- WordPress 6.0 or newer
- PHP 7.4 or newer

## Installation

### Via WordPress Admin (Recommended)
1. Download from WordPress.org plugin directory
2. Upload via Plugins → Add New in your WordPress admin
3. Activate the plugin

### Via Composer (For Developers)
```bash
composer config repositories.inline-context vcs https://github.com/jooplaan/inline-context
composer require jooplaan/inline-context
```

This will install the plugin to `wp-content/plugins/inline-context/` automatically using `composer/installers`.

### Manual Installation
1. Download the latest release from GitHub
2. Extract to `wp-content/plugins/inline-context/`
3. Activate via WordPress admin

## Development

### Getting started

1. Clone the repository
2. Install dependencies:

   ```bash
   npm install
   composer install
   ```

3. Start development mode (watches files and rebuilds on change):

   ```bash
   npm run start
   ```

4. Build for production:

   ```bash
   npm run build
   ```

### Available scripts

- `npm run start` — Start development mode with hot reload
- `npm run build` — Build production assets
- `npm run lint:js` — Lint JavaScript files
- `npm run lint:php` — Check PHP coding standards (uses local PHPCS via Composer)
- `npm run lint:php:fix` — Auto-fix PHP coding standard violations (uses local PHPCBF via Composer)
- `npm run lint` — Check both JavaScript and PHP standards
- `npm run test` — Run all quality checks (currently linting)
- `npm run pot` — Show instructions for generating translation template file (requires WP-CLI)
- `npm run package` — Create a distributable zip (runs tests + build automatically)
- `npm run release` — Build and package in one step (runs tests + build automatically)

### Project structure

- `src/` — Source files (JavaScript, SCSS)
- `build/` — Compiled assets (committed to repo for WordPress.org)
- `languages/` — Translation files
- `inline-context.php` — Main plugin bootstrap file
- `admin-settings.php` — Admin settings interface
- `includes/` — Modular class-based architecture (v2.0)
- `readme.txt` — WordPress.org readme
- `scripts/` — Build and packaging scripts
- `vendor/` — Composer dependencies (PHP CodeSniffer, WordPress Coding Standards)

## Code Quality

This project enforces WordPress coding standards for both JavaScript and PHP:

- **JavaScript**: Uses `@wordpress/scripts` with ESLint for WordPress coding standards
- **PHP**: Uses PHP_CodeSniffer 3.13.5+ with WordPress-Coding-Standards 3.2.0+ ruleset (installed via Composer)
- **Automatic Quality Checks**: All packaging and release commands automatically fix and check linting before building

### Development tools

The project uses local PHP CodeSniffer tools installed via Composer:

```bash
# Install PHP development tools
composer install

# Run PHPCS manually
vendor/bin/phpcs --standard=WordPress inline-context.php

# Run PHPCBF manually  
vendor/bin/phpcbf --standard=WordPress inline-context.php
```

### Quality assurance workflow

```bash
# Check code quality
npm run test

# Fix auto-fixable issues
npm run lint:fix       # Fixes both JS and PHP
npm run lint:js:fix    # Fix JavaScript only
npm run lint:php:fix   # Fix PHP only

# Package with automatic linting fixes and checks
npm run package  # automatically runs: lint:fix → build → package

# Release workflow (same as package)
npm run release  # automatically runs: lint:fix → build → package
```

## Releasing a new version

1. **Code quality is automatic**: `npm run release` automatically fixes linting issues before building

2. **Update version numbers** in:
   - `inline-context.php` (plugin header and INLINE_CONTEXT_VERSION constant)
   - `package.json` (version field)
   - `readme.txt` (Stable tag and Changelog)
   - `composer.json` (version field)

3. **Build and package** (includes automatic quality checks):

   ```bash
   npm run release
   ```

   This automatically runs:
   - ✅ JavaScript linting (ESLint)
   - ✅ PHP coding standards (PHPCS)
   - ✅ Production build
   - ✅ Zip packaging

4. **Test the package**: The distributable zip will be at `dist/inline-context.zip`

5. **Commit and tag**:

   ```bash
   git add .
   git commit -m "Release version X.Y.Z"
   git tag vX.Y.Z
   git push origin main --tags
   ```

6. **Deploy**: Upload `dist/inline-context.zip` to WordPress.org SVN (or use the zip for manual distribution)

## Support

Please use the support forum on WordPress.org once the plugin is published. We'll do our best to help.

## License

GPL v2 or later.
