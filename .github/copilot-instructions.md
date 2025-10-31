# Reveal Text Block - WordPress Plugin

## Architecture Overview

This is a WordPress Gutenberg **Rich Text Format** plugin that adds inline expandable text functionality (similar to footnotes). The plugin extends the WordPress block editor toolbar rather than creating a standalone block.

### Key Components

- **Rich Text Format Registration** (`src/index.js`): Registers `trybes/reveal-text` format type with WordPress
- **Editor Interface** (`src/edit.js`): React component providing toolbar button and popover editor
- **Frontend Interaction** (`src/frontend.js`): Vanilla JS click handlers for revealing/hiding content
- **Dual Asset Loading** (`reveal-text.php`): Separate enqueuing for editor vs frontend assets

## Development Patterns

### WordPress Rich Text Format Structure

```javascript
// Format registration follows WordPress patterns
registerFormatType('trybes/reveal-text', {
  tagName: 'a', // Output HTML element
  className: 'wp-reveal-text', // CSS class for styling
  attributes: {
    'data-reveal-content': 'data-reveal-content', // Custom data attribute
  },
})
```

### State Management Pattern

- Uses React `useState` for popover visibility and text input
- Leverages WordPress `toggleFormat` API for applying/removing formatting
- Finds active format from `value.activeFormats` array to populate existing content

### Frontend Interaction Pattern

- Single event listener on `document.body` with event delegation
- Toggle behavior: clicking again removes revealed content
- Mutual exclusion: opening new reveal closes others
- DOM manipulation using `insertAdjacentElement` for inline insertion

## Build & Development Workflow

```bash
npm run start    # Development with hot reload
npm run build    # Production build
```

Uses `@wordpress/scripts` which provides:

- Webpack configuration with React/JSX support
- SCSS compilation
- Asset optimization and file versioning

## File Organization

- `src/index.js` - Entry point, format registration
- `src/edit.js` - Editor component (toolbar button + popover)
- `src/frontend.js` - Frontend click handlers (vanilla JS)
- `src/style.css` - Frontend styles (loaded on both editor/frontend)
- `src/editor.scss` - Editor-only styles
- `reveal-text.php` - WordPress plugin bootstrap with asset enqueuing

## WordPress Integration Points

### Asset Enqueuing Strategy

```php
// Editor assets (Block Editor only)
add_action('enqueue_block_editor_assets', function() {
    wp_enqueue_script('trybes-reveal-text', 'build/index.js', [...]);
});

// Frontend assets (Public site)
add_action('wp_enqueue_scripts', function() {
    wp_enqueue_script('trybes-reveal-text-frontend', 'src/frontend.js', [...]);
});
```

### Dependencies

- Editor: `wp-rich-text`, `wp-element`, `wp-components`, `wp-block-editor`, `wp-i18n`
- Frontend: No dependencies (vanilla JS)

## Styling Approach

- Uses CSS custom properties with fallbacks: `var(--wp--preset--color--primary, #0073aa)`
- Semantic class naming: `.wp-reveal-text`, `.wp-reveal-inline`, `.wp-reveal-text--open`
- Accessibility: `role="note"` on revealed content

## Debugging Tips

- Check browser console for JavaScript errors in editor vs frontend contexts
- Use WordPress block editor's "Code Editor" view to inspect generated HTML
- Frontend issues: verify `data-reveal-content` attribute exists on anchor elements
- Editor issues: check React DevTools for component state and WordPress format registration

## Internationalization

Uses WordPress i18n functions: `__('Text', 'trybes')` with 'trybes' text domain. Currently mixed Dutch/English strings.
