# Inline Context - WordPress Plugin

## Architecture Overview

This is a WordPress Gutenberg **Rich Text Format** plugin that adds inline expandable context functionality with direct anchor linking. The plugin extends the WordPress block editor toolbar rather than creating a standalone block.

### Key Components

- **Rich Text Format Registration** (`src/index.js`): Registers `jooplaan/inline-context` format type with WordPress
- **Editor Interface** (`src/edit.js`): React component with ReactQuill rich text editor and popover interface
- **Frontend Interaction** (`src/frontend.js`): Vanilla JS with DOMPurify for secure HTML rendering and anchor navigation
- **Asset Management** (`inline-context.php`): WordPress coding standards compliant PHP with proper asset enqueuing

## Development Patterns

### WordPress Rich Text Format Structure

```javascript
// Format registration follows WordPress patterns
registerFormatType('jooplaan/inline-context', {
  tagName: 'a', // Output HTML element
  className: 'wp-inline-context', // CSS class for styling
  attributes: {
    'data-inline-context': 'data-inline-context', // Rich text content
    'data-anchor-id': 'data-anchor-id', // Unique anchor identifier
    href: 'href', // Proper anchor links (#context-note-XXX)
    role: 'role', // Accessibility role
    'aria-expanded': 'aria-expanded', // ARIA state
  },
});
```

### State Management Pattern

- Uses React `useState` for popover visibility and ReactQuill content
- Leverages WordPress `toggleFormat` API for applying/removing formatting
- Finds active format from `value.activeFormats` array to populate existing content
- Generates unique anchor IDs using timestamp + random for new content
- Preserves existing anchor IDs when editing

### Frontend Interaction Pattern

- Single event listener on `document.body` with event delegation
- Toggle behavior: clicking again removes revealed content
- Multiple contexts can stay open simultaneously
- DOM manipulation using `insertAdjacentElement` for inline insertion 
- Auto-opens notes when page loads with matching hash (#context-note-XXX)
- Smooth scrolling to auto-opened notes for better UX

## Build & Development Workflow

```bash
npm run start           # Development with hot reload
npm run build          # Production build
npm run lint:js        # JavaScript linting (ESLint)
npm run lint:php       # PHP coding standards (PHPCS)
npm run lint:php:fix   # Auto-fix PHP violations (PHPCBF)
npm run lint           # Check both JS and PHP standards
npm run test           # Run all quality checks
npm run package        # Create zip (runs test + build)
npm run release        # One-step release (runs test + build + package)
```

Uses `@wordpress/scripts` which provides:

- Webpack configuration with React/JSX support
- SCSS compilation with WordPress standards
- Asset optimization and file versioning
- ESLint with WordPress coding standards

### Quality Assurance

- **Pre-packaging checks**: Both `package` and `release` commands automatically run linting
- **WordPress standards**: PHP code must pass PHPCS with WordPress-Coding-Standards
- **JavaScript standards**: Uses `@wordpress/scripts` ESLint configuration
- **Fail-fast**: Packaging stops if any quality checks fail

## File Organization

- `src/index.js` - Entry point, format registration
- `src/edit.js` - Editor component (toolbar button + popover with ReactQuill)
- `src/frontend.js` - Frontend click handlers (vanilla JS with DOMPurify)
- `src/style.scss` - Frontend styles (loaded on both editor/frontend)
- `src/editor.scss` - Editor-only styles
- `inline-context.php` - WordPress plugin bootstrap with asset enqueuing
- `build/` - Compiled assets (committed for WordPress.org distribution)
- `dist/` - Packaged plugin zip files
- `scripts/package.sh` - Plugin packaging script

## WordPress Integration Points

### Asset Enqueuing Strategy

```php
// Editor assets (Block Editor only)
add_action('enqueue_block_editor_assets', function() {
    wp_enqueue_script('jooplaan-inline-context', 'build/index.js', [...]);
    wp_enqueue_style('jooplaan-inline-context', 'build/index.css', [...]);
});

// Frontend assets (Public site)
add_action('wp_enqueue_scripts', function() {
    wp_enqueue_script('jooplaan-inline-context-frontend', 'build/frontend.js', [...]);
    wp_enqueue_style('jooplaan-inline-context-frontend-style', 'build/style-index.css', [...]);
});
```

### Dependencies

- Editor: `wp-rich-text`, `wp-element`, `wp-components`, `wp-block-editor`, `wp-i18n`
- Frontend: `dompurify` (bundled for security)
- Build: `react-quill` for rich text editing

## Styling Approach

- Uses CSS custom properties with fallbacks: `var(--wp--preset--color--primary, #0073aa)`
- Semantic class naming: `.wp-inline-context`, `.wp-inline-context-inline`, `.wp-inline-context--open`
- Accessibility: `role="note"` on revealed content, proper ARIA attributes
- Animation: Smooth reveal/hide transitions with CSS animations
- Theme compatibility: CSS variables for easy customization

## Debugging Tips

- Check browser console for JavaScript errors in editor vs frontend contexts
- Use WordPress block editor's "Code Editor" view to inspect generated HTML
- Frontend issues: verify `data-inline-context` and `data-anchor-id` attributes exist on anchor elements
- Editor issues: check React DevTools for component state and WordPress format registration
- Anchor linking: Test URL hashes like `#context-note-XXX` for auto-opening functionality
- Quality issues: Run `npm run test` to check all coding standards

## Internationalization

Uses WordPress i18n functions: `__('Text', 'inline-context')` with 'inline-context' text domain.

## Version 1.0 Architecture Decisions

### Anchor-First Design
- **No Legacy Support**: Version 1.0 requires all inline contexts to have anchor IDs
- **Unique ID Generation**: Uses timestamp + random for collision-free anchor IDs
- **Direct Linking**: Every note can be shared via URL hash (#context-note-XXX)
- **Auto-Opening**: Notes automatically open when page loads with matching hash

### Security & Content Handling
- **DOMPurify Integration**: All rich text content is sanitized before rendering
- **Allowed HTML**: Limited to safe subset (p, strong, em, a, ol, ul, li, br)
- **Link Hardening**: External links get rel="noopener noreferrer" automatically
- **XSS Prevention**: No javascript: URLs allowed in content
- **Smart Link Behavior**: Internal links stay in same tab, external links open in new tab with security attributes

### Quality Assurance
- **WordPress Coding Standards**: Both PHP (PHPCS) and JavaScript (ESLint) standards enforced
- **Pre-Release Checks**: No packages can be created without passing all quality checks
- **Automated Workflow**: `npm run release` handles test → build → package pipeline
- **Version Consistency**: Plugin version must be updated in PHP header, readme.txt, and package.json

## Development Best Practices

### Code Organization
- **Separation of Concerns**: Editor (React + ReactQuill) vs Frontend (Vanilla JS + DOMPurify)
- **WordPress Integration**: Proper hook usage, asset enqueuing, and KSES filtering
- **Progressive Enhancement**: Fallback accessibility attributes for edge cases
- **Build Optimization**: Separate bundles for editor and frontend with proper dependencies

### Testing & Validation
- **Demo Page**: `demo.html` for testing functionality without WordPress setup
- **Quality Scripts**: `npm run test` for comprehensive code quality checks
- **Package Validation**: Automated zip creation with development file exclusion
- **Browser Testing**: Manual testing of anchor links and auto-opening functionality
