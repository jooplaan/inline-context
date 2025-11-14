# Inline Context - WordPress Plugin

## Architecture Overview

This is a WordPress Gutenberg **Rich Text Format** plugin that adds inline expandable context functionality with direct anchor linking and reusable notes via Custom Post Type. The plugin extends the WordPress block editor toolbar rather than creating a standalone block.

**Version 2.0** introduced a complete modular refactoring from monolithic to class-based architecture (83% main file reduction from 2,291 to 395 lines).

### Key Components

**Frontend Assets:**
- **Rich Text Format Registration** (`src/index.js`): Registers `jooplaan/inline-context` format type with WordPress
- **Editor Interface** (`src/edit.js`): React component with tabbed interface (Create/Search), QuillEditor, and category selector
- **Note Search Component** (`src/components/NoteSearch.js`): Live search interface for finding existing CPT notes
- **QuillEditor Component** (`src/components/QuillEditor.js`): Rich text editor with keyboard navigation
- **CPT Editor Enhancement** (`src/cpt-editor.js`): QuillEditor integration for inline_context_note CPT edit screen
- **Frontend Interaction** (`src/frontend.js`): Vanilla JS with DOMPurify for secure HTML rendering and anchor navigation

**Backend Modular Architecture (v2.0):**
- **`Inline_Context_CPT`** (`includes/class-cpt.php`, 855 lines) - Custom Post Type registration, metaboxes, and admin UI
- **`Inline_Context_Taxonomy_Meta`** (`includes/class-taxonomy-meta.php`, 372 lines) - Taxonomy meta fields for category icons, colors, and admin UI enhancements
- **`Inline_Context_Sync`** (`includes/class-sync.php`, 496 lines) - Note usage tracking, reusable content synchronization, category sync
- **`Inline_Context_Deletion`** (`includes/class-deletion.php`, 198 lines) - Deletion protection for reusable notes, cleanup for non-reusable
- **`Inline_Context_REST_API`** (`includes/class-rest-api.php`, 340 lines) - REST API endpoints for search, usage tracking, and note removal handling
- **`Inline_Context_Frontend`** (`includes/class-frontend.php`, 276 lines) - Noscript content generation, KSES filtering, asset enqueuing
- **`Inline_Context_Utils`** (`includes/class-utils.php`, 182 lines) - Category management, CSS variable management with backward compatibility

**Bootstrap & Settings:**
- **Asset Management** (`inline-context.php`, 395 lines) - WordPress coding standards compliant PHP with CPT registration, REST API, and asset enqueuing
- **Admin Settings** (`admin-settings.php`, 728 lines) - Tabbed admin interface for categories and styling options (function-based)
- **Uninstall System** (`uninstall.php`) - Comprehensive cleanup with content removal options

### Version 2.0 Modular Architecture Benefits

**Separation of Concerns:**
- Each class has a single, well-defined responsibility
- CPT registration separated from REST API, sync logic, and frontend rendering
- Taxonomy meta fields isolated in dedicated class

**Maintainability:**
- 83% reduction in main file size (2,291 → 395 lines)
- Clear file organization makes navigation and debugging easier
- Modular code easier to test and extend

**Performance:**
- Efficient class initialization with proper WordPress hooks
- Lazy loading where appropriate
- Optimized autoloading pattern

**Developer Experience:**
- Clean interfaces for adding features without touching core logic
- Testable code structure
- Full WordPress coding standards compliance

## Development Patterns

### Custom Post Type Architecture (v1.5.0)

The plugin uses a **dual-storage approach** combining CPT management with cached content for performance:

**Custom Post Type: `inline_context_note`**
- Title: Note identifier/name for searching
- Content: Rich text note content (ReactQuill HTML)
- Taxonomy: `inline_context_category` (replaces old meta-based categories)
- Meta fields:
  - `is_reusable`: Boolean flag (default: false)
  - `used_in_posts`: Array of post IDs using this note
  - `usage_count`: Number of times used

**Data Storage Strategy:**
```html
<a class="wp-inline-context" 
   data-note-id="123"
   data-inline-context="<p>Cached HTML content</p>"
   data-anchor-id="context-note-abc"
   href="#context-note-abc">link text</a>
```

- `data-note-id`: CPT post ID (enables reusability)
- `data-inline-context`: Cached HTML content (frontend performance)
- `data-anchor-id`: Unique anchor for direct linking
- `href`: Proper anchor link for accessibility

**Why Dual Storage?**
1. **Performance**: Frontend uses cached `data-inline-context` (zero database queries)
2. **Reusability**: CPT enables search, tracking, and centralized management
3. **Backward Compatibility**: Notes without `data-note-id` still work
4. **Graceful Degradation**: If CPT is deleted, cached content remains functional

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

- Uses React `useState` for popover visibility, tab mode (Create/Search), and QuillEditor content
- Leverages WordPress `toggleFormat` API for applying/removing formatting
- Finds active format from `value.activeFormats` array to populate existing content
- Generates unique anchor IDs using timestamp + random for new content
- Preserves existing anchor IDs when editing
- **v1.5.0**: Tracks CPT note ID via `data-note-id` attribute for reusability
- **v1.5.0**: Caches note content in `data-inline-context` for frontend performance

### Editor Popover Interface (v1.5.0)

The editor popover has two tabbed modes:

**Create Tab:**
- QuillEditor for rich text input
- Category selector dropdown
- Creates new CPT note on save
- Caches content in `data-inline-context`
- Assigns `data-note-id` from created CPT post

**Search Tab:**
- Live search input (queries CPT by title via REST API)
- Displays results with title and excerpt
- Click to select existing note
- Loads CPT content into cache
- Reuses existing `data-note-id`
- Supports filtering by reusable status

### REST API Endpoints (v1.5.0)

**`/wp-json/inline-context/v1/notes/search`**
- Method: GET
- Params: `s` (search term), `reusable_only` (boolean)
- Returns: Array of notes with ID, title, content, excerpt

**`/wp-json/inline-context/v1/notes/{id}/track-usage`**
- Method: POST
- Params: `post_id` (current post ID)
- Updates: `used_in_posts` array and `usage_count`
- Non-blocking: Failures don't prevent saving

**`/wp-json/inline-context/v1/notes/handle-removals`**
- Method: POST
- Params: `post_id` (post ID), `note_ids` (array of note IDs)
- Updates: Removes post ID from `used_in_posts` and decrements `usage_count`
- Non-blocking: Cleans up usage tracking when notes are removed from content

### CPT List View Enhancements (v1.5.0)

**Custom Columns:**
- **Title**: Default WordPress column
- **Reusable**: "Yes" or "No" (from `is_reusable` meta)
- **Usage Count**: Number from `usage_count` meta (sortable)
- **Used In**: Linked list of posts using this note

**Custom Filter:**
- Dropdown: "All Notes" / "Reusable Notes Only"
- Filters by `is_reusable` meta value

**Delete Protection:**
- Warning in post list bulk/quick actions
- Warning in trash/delete links
- Warning on single post delete screen
- Shows which posts will be affected

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
npm run lint:js:fix    # Auto-fix JavaScript violations
npm run lint:php       # PHP coding standards (PHPCS)
npm run lint:php:fix   # Auto-fix PHP violations (PHPCBF)
npm run lint           # Check both JS and PHP standards
npm run lint:fix       # Auto-fix both JS and PHP violations
npm run test           # Run all quality checks (runs lint)
npm run package        # Create zip (runs lint:fix + build automatically)
npm run release        # One-step release (runs lint:fix + build + package)
```

Uses `@wordpress/scripts` which provides:

- Webpack configuration with React/JSX support
- SCSS compilation with WordPress standards
- Asset optimization and file versioning
- ESLint with WordPress coding standards

### Quality Assurance

- **Pre-packaging checks**: Both `package` and `release` commands automatically run `lint:fix` before building
- **WordPress standards**: PHP code must pass PHPCS with WordPress-Coding-Standards
- **JavaScript standards**: Uses `@wordpress/scripts` ESLint configuration
- **Fail-fast**: Packaging stops if any quality checks fail

## File Organization

- `src/index.js` - Entry point, format registration
- `src/edit.js` - Editor component (toolbar button + popover with tabbed interface)
- `src/components/NoteSearch.js` - Search interface for existing notes
- `src/components/QuillEditor.js` - Rich text editor component
- `src/cpt-editor.js` - CPT edit screen enhancements
- `src/frontend.js` - Frontend click handlers (vanilla JS with DOMPurify)
- `src/style.scss` - Frontend styles (loaded on both editor/frontend)
- `src/editor.scss` - Editor-only styles
- `inline-context.php` - WordPress plugin bootstrap (395 lines)
- `admin-settings.php` - Admin settings UI with tabbed interface (728 lines)
- `includes/class-cpt.php` - Custom Post Type class (855 lines)
- `includes/class-taxonomy-meta.php` - Taxonomy meta fields class (372 lines)
- `includes/class-sync.php` - Synchronization class (496 lines)
- `includes/class-deletion.php` - Deletion handling class (198 lines)
- `includes/class-rest-api.php` - REST API endpoints class (340 lines)
- `includes/class-frontend.php` - Frontend rendering class (276 lines)
- `includes/class-utils.php` - Utility functions class (182 lines)
- `uninstall.php` - Plugin uninstall cleanup
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

## Uninstall System (v1.5.0)

### Content Cleanup Strategy

The plugin provides comprehensive cleanup via `uninstall.php`:

**CPT Deletion:**
- Deletes all `inline_context_note` CPT posts
- Removes associated taxonomy terms (`inline_context_category`)
- Cleans up post meta (`is_reusable`, `used_in_posts`, `usage_count`)

**Content Removal (Optional):**
- Option: Remove inline context links from all posts/pages
- Replaces `<a class="wp-inline-context">` with plain text
- Uses DOMDocument to safely parse and modify HTML
- Preserves link text, removes all data attributes
- Handles edge cases (malformed HTML, empty content)

**Settings Cleanup:**
- Removes all plugin options from database
- Cleans up transients and cached data

**Safety Features:**
- Warning message before uninstall
- Non-blocking cleanup (failures logged, not thrown)
- Batch processing for large sites
- Respects WordPress multisite boundaries

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
