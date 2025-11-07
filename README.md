# Inline Context

Add categorized "click to reveal" notes inline with your text. Perfect for short explanations, definitions, and asides with custom icons and full styling control.

## What it does

- Lets you attach a brief note to any piece of text in the block editor
- Organize notes with custom categories (Internal Article, External Article, Definition, Tip, etc.)
- Each category has distinct icons for closed/open states
- Readers click the highlighted text to show the note inline, and click again to hide it
- Full styling control through tabbed admin interface
- Keeps pages clean and easy to scan while still offering helpful context

## Key Features

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

### Setting up categories
1. Go to Settings > Inline Context
2. Click the Categories tab
3. Add or edit categories with custom names, icons, and colors
4. Click the icon button to open the visual picker
5. Save your settings

### Adding notes in the editor
1. Select the text you want to explain
2. Click the "Inline Context" button in the formatting toolbar
3. Choose a category (optional)
4. Type your note content
5. Save and view your page

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
- `npm run lint:php` — Check PHP coding standards
- `npm run lint:php:fix` — Auto-fix PHP coding standard violations
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
- `readme.txt` — WordPress.org readme
- `scripts/` — Build and packaging scripts

## Code Quality

This project enforces WordPress coding standards for both JavaScript and PHP:

- **JavaScript**: Uses `@wordpress/scripts` with ESLint for WordPress coding standards
- **PHP**: Uses PHP_CodeSniffer with WordPress-Coding-Standards ruleset
- **Automatic Quality Checks**: All packaging and release commands automatically fix and check linting before building

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
