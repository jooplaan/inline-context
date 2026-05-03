# Inline Context - WordPress Plugin

## Architecture Overview

This is a WordPress Gutenberg **Rich Text Format** plugin that adds inline expandable context functionality with dual display modes (inline/tooltip), direct anchor linking, and reusable notes via Custom Post Type. The plugin extends the WordPress block editor toolbar rather than creating a standalone block.

**Version 2.0** introduced a complete modular refactoring from monolithic to class-based architecture (83% main file reduction from 2,291 to 395 lines).

**Version 2.1** added tooltip display mode as an alternative to inline expansion, with full accessibility support.

**Version 2.3** added hover activation option for tooltips with configurable delay and smart mouse interaction handling.

**Version 2.5** added keyboard shortcuts for faster inline context management in the editor (Cmd+Shift+I to insert, Cmd+Shift+K to edit).

**Version 2.4** added WordPress 6.9+ Abilities API integration — five server-side abilities (`create-note`, `search-notes`, `get-categories`, `get-note`, `create-inline-note`) make the plugin's functionality discoverable to AI assistants and headless clients.

**Version 2.6** added a color preset system (Modern Blue, Minimalist Gray, High Contrast, Warm Earth Tones, Dark Mode) with automatic detection, a "Custom" indicator when values diverge, and 37 CSS variables per preset (all WCAG 2.1 AA compliant).

**Version 2.7** added Export/Import Settings functionality for backing up and sharing configurations, plus comprehensive print styles with auto-expansion and footnote-style numbering.

**Version 2.8** added image support inside notes via the WordPress Media Library — custom Quill image blot preserves alt/loading/decoding attributes through editor round-trips, with size caps for tooltip vs inline modes and an `inline_context_allowed_image_protocols` filter for sites needing data URIs.

### Key Components

**Frontend Assets:**

- **Rich Text Format Registration** (`src/index.js`): Registers `jooplaan/inline-context` format type with WordPress
- **Editor Interface** (`src/edit.js`): React component with tabbed interface (Create/Search), QuillEditor, and category selector
- **Note Search Component** (`src/components/NoteSearch.js`): Live search interface for finding existing CPT notes
- **QuillEditor Component** (`src/components/QuillEditor.js`): Rich text editor with keyboard navigation
- **CPT Editor Enhancement** (`src/cpt-editor.js`): QuillEditor integration for inline_context_note CPT edit screen
- **Keyboard Shortcuts Hook** (`src/hooks/useEditorKeyboardShortcuts.js`): Editor-level keyboard shortcuts (Cmd+Shift+I to insert, Cmd+Shift+K to edit)
- **Format Navigation Utilities** (`src/utils/format-navigation.js`): Pure utility functions for detecting and navigating inline context formats (isCaretInFormat)
- **Frontend Interaction** (`src/frontend.js`): Dual-mode display system (inline/tooltip) with DOMPurify for secure HTML rendering, smart positioning, and full keyboard support

**Backend Modular Architecture (v2.0+):**

Class files live under `includes/` and follow the WordPress naming convention `class-inline-context-{slug}.php`:

- **`Inline_Context_CPT`** (`includes/class-inline-context-cpt.php`) — Custom Post Type registration, metaboxes, and admin UI
- **`Inline_Context_Taxonomy_Meta`** (`includes/class-inline-context-taxonomy-meta.php`) — Taxonomy meta fields for category icons, colors, and admin UI enhancements
- **`Inline_Context_Sync`** (`includes/class-inline-context-sync.php`) — Note usage tracking, reusable content synchronization, category sync
- **`Inline_Context_Deletion`** (`includes/class-inline-context-deletion.php`) — Deletion protection for reusable notes, cleanup for non-reusable
- **`Inline_Context_REST_API`** (`includes/class-inline-context-rest-api.php`) — REST API endpoints for search, usage tracking, and note removal handling
- **`Inline_Context_Frontend`** (`includes/class-inline-context-frontend.php`) — Noscript content generation, KSES filtering, asset enqueuing, display mode configuration
- **`Inline_Context_Utils`** (`includes/class-inline-context-utils.php`) — Category management, CSS variable management with backward compatibility
- **`Inline_Context_Abilities`** (`includes/class-inline-context-abilities.php`) — WordPress Abilities API integration (v2.4+); registers five server-side abilities for AI/headless discovery
- **Backward-compat wrappers** (`includes/functions.php`) — Re-exports legacy global functions kept for plugin upgrade compatibility

**Bootstrap & Settings:**

- **Plugin bootstrap** (`inline-context.php`) — Plugin header, constant definition, and class wiring (loads each class file then calls its `init()` hook)
- **Admin Settings** (`admin-settings.php`) — Tabbed admin interface (General, Import/Export, Styling, Uninstall) with display mode selection, settings backup/restore, organized styling sections, and color preset switcher
- **Uninstall System** (`uninstall.php`) — Comprehensive cleanup with content removal options
- **PHP dependencies** (`composer.json`) — PHPCS WordPress-Coding-Standards and PHPUnit; not loaded at runtime

### Version 2.0 Modular Architecture Benefits

**Separation of Concerns:** Each class has a single, well-defined responsibility — CPT registration is separated from REST API, sync logic, and frontend rendering; taxonomy meta fields are isolated in a dedicated class.

**Maintainability:** v2.0 reduced the main bootstrap file by ~83% (the original ~2,291-line monolith down to a few hundred lines that mostly wire classes together). Class files are sized to their domain rather than packed together.

**Developer Experience:** Clean interfaces for adding features without touching core logic; testable code structure; full WordPress coding standards compliance.

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
<a
 class="wp-inline-context"
 data-note-id="123"
 data-inline-context="<p>Cached HTML content</p>"
 data-anchor-id="context-note-abc"
 href="#context-note-abc"
 >link text</a
>
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

**Bulk Delete System (v2.1):**

- **Smart deletion**: Reusable notes can be deleted even when in use
- **Automatic cleanup**: Deletes note from all posts where it's used (removes `<a>` tag, preserves text)
- **Confirmation dialogs**: Show exact impact - "X note uses will be deleted in Y posts"
- **Usage vs Post count**: Distinguishes between total uses (3×) and number of posts (2)
- **JavaScript warnings**: Edit screen, list view individual, and bulk delete all show confirmation
- **PHP cleanup**: Hooks into `wp_trash_post` and `before_delete_post` to clean up content

### Display Modes (v2.1, enhanced in v2.3)

The plugin supports two display modes for showing notes on the frontend:

**Inline Mode (default):**

- Notes expand directly below the trigger text in the content flow
- Uses `insertAdjacentElement` to inject note div after trigger link
- Adds `.wp-inline-context-inline` class with slide-down animation
- Left accent bar for visual distinction
- Vertical margins for spacing in content flow

**Tooltip Mode:**

- Notes appear as floating positioned elements above or below trigger
- Smart positioning with viewport boundary detection
- Automatically flips above/below to prevent off-screen display
- Close button (×) in top-right corner
- Click/keyboard activation by default
- **v2.3**: Optional hover activation with 300ms delay
- **v2.3**: Smart hover behavior - keep tooltip open when moving mouse to tooltip
- Escape key closes tooltip and returns focus to trigger
- Positioned absolutely with z-index 10000
- Arrow pointer indicates which trigger opened it

**Hover Activation (v2.3):**

- Admin setting: `inline_context_tooltip_hover` (boolean, default: false)
- Configurable 300ms delay before showing tooltip on hover
- 100ms grace period when leaving trigger/tooltip for smooth mouse movement
- Tooltip stays open when moving mouse from trigger to tooltip
- Separate show/hide timeout management for robust interaction
- Only available when tooltip mode is selected

**Shared Features:**

- Both modes support full keyboard navigation (Space/Enter to activate)
- Automatic focus management (note receives focus when opened)
- DOMPurify sanitization for security
- CSS animations for smooth reveal
- Multiple notes can be open simultaneously
- Click toggle behavior (click again to close)

**Admin Configuration:**

- Setting stored as `inline_context_display_mode` option ('inline' or 'tooltip')
- **v2.3**: Hover setting stored as `inline_context_tooltip_hover` option (boolean)
- General tab in admin settings with radio button selection
- **v2.3**: Conditional checkbox for hover - only visible when tooltip mode selected
- **v2.3**: JavaScript toggles hover checkbox visibility on radio change
- Values passed to frontend via `wp_localize_script`: `displayMode` and `hoverEnabled`
- Frontend checks `window.inlineContextData.displayMode` and `window.inlineContextData.hoverEnabled`

**Implementation:**

- `toggleNote(trigger)` function routes to either `toggleInlineNote()` or `toggleTooltip()`
- **v2.3**: Hover event listeners conditionally attached when `hoverEnabled` is true
- Both modes store event listeners on trigger element for proper cleanup
- Tooltip positioning handled by `positionTooltip()` with viewport calculations
- Focus moved to note content for keyboard link navigation in both modes
- **v2.3**: Separate `showTimeout` and `hideTimeout` for hover interaction management

### Frontend Interaction Pattern

- Single event listener on `document.body` with event delegation
- Toggle behavior: clicking again removes revealed content
- Multiple contexts can stay open simultaneously
- DOM manipulation using `insertAdjacentElement` for inline insertion
- Auto-opens notes when page loads with matching hash (#context-note-XXX)
- Smooth scrolling to auto-opened notes for better UX

### Keyboard Shortcuts (v2.5)

**Editor Shortcuts:**

- **Cmd+Shift+I** (Ctrl+Shift+I on Windows): Insert inline context when text is selected
- **Cmd+Shift+K** (Ctrl+Shift+K on Windows): Edit existing inline context at cursor position

**Implementation Pattern:**

- Custom hook `useEditorKeyboardShortcuts` attaches document-level keyboard listeners
- Shortcuts only active when popover is closed (avoids conflicts with popover shortcuts)
- Uses WordPress Rich Text API (`value.start`, `value.end`, `onChange`)
- Format detection via `isCaretInFormat()` utility function
- Case-insensitive key matching using `.toLowerCase()`
- Silent fail behavior when no selection or cursor not in format
- Event listeners cleaned up on unmount

**Format Navigation Utilities:**

- `isCaretInFormat(value, position)`: Checks if cursor is inside an inline-context format
- Checks both left and right positions around caret for boundary detection
- Returns boolean indicating format presence

## Build & Development Workflow

```bash
npm run start              # Development with hot reload
npm run build              # Production build
npm run lint:js            # JavaScript linting (ESLint)
npm run lint:js:fix        # Auto-fix JavaScript violations
npm run lint:php           # PHP coding standards (PHPCS)
npm run lint:php:fix       # Auto-fix PHP violations (PHPCBF)
npm run lint:md            # Markdown linting (markdownlint-cli2)
npm run lint               # Check JS, PHP, and Markdown standards
npm run lint:fix           # Auto-fix JS, PHP, and Markdown violations
npm run test:unit          # Run Jest unit tests for JS modules and components
npm run test:unit:watch    # Jest watch mode
npm run test:unit:coverage # Jest with coverage report
npm run test               # Lint + unit tests (full quality gate)
npm run package            # Create zip (runs lint:fix + build automatically)
npm run release            # One-step release (runs lint:fix + build + package)
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

**JavaScript source (`src/`):**

- `src/index.js` — Entry point, format registration, sidebar import, keyboard-shortcut registration
- `src/edit.js` — Editor component (toolbar button + popover with tabbed interface)
- `src/sidebar.js` — Block-editor sidebar panel registering reusable-note management
- `src/cpt-editor.js` — CPT edit-screen enhancements (QuillEditor mounting on `inline_context_note` posts)
- `src/frontend.js` — Frontend dual-mode display system (inline/tooltip) with DOMPurify
- `src/style.scss` — Frontend styles including tooltip positioning, animations, and print rules
- `src/editor.scss` — Editor-only styles
- `src/components/QuillEditor.js` — Rich text editor used in popover and CPT edit screen
- `src/components/NoteSearch.js` — Live search interface for finding existing CPT notes
- `src/components/CategorySelector.js` — Dropdown for assigning a category to a note
- `src/components/LinkControl.js` — URL/text input with validation for in-note links
- `src/components/PopoverActions.js` — Save/delete/reusable-toggle buttons in the popover footer
- `src/components/NotesSidebar.js` — Sidebar list of notes used in the current post
- `src/components/AIFeatures.js` — Scaffold for future in-editor AI UI (currently disabled by default)
- `src/hooks/useEditorKeyboardShortcuts.js` — Document-level shortcut listener (Cmd+Shift+I/K)
- `src/hooks/useInlineContext.js` — Core toggle/save/load logic for the format
- `src/hooks/useQuillKeyboardNav.js` — Quill toolbar/link-input keyboard navigation
- `src/utils/anchor.js` — Anchor ID generation and uniqueness checks
- `src/utils/copy-link.js` — Clipboard copy utility for anchor links
- `src/utils/format-navigation.js` — `isCaretInFormat` and other rich-text cursor utilities
- `src/utils/text.js` — Linked-text extraction helpers
- `src/api/note-actions.js` — Wrapper around the plugin's REST endpoints

**PHP source:**

- `inline-context.php` — Plugin bootstrap (header, constants, class wiring)
- `admin-settings.php` — Admin settings UI (General, Import/Export, Styling, Uninstall tabs)
- `uninstall.php` — Plugin uninstall cleanup
- `includes/class-inline-context-cpt.php` — Custom Post Type class
- `includes/class-inline-context-taxonomy-meta.php` — Taxonomy meta fields class
- `includes/class-inline-context-sync.php` — Reusable-note synchronization
- `includes/class-inline-context-deletion.php` — Deletion protection and cleanup
- `includes/class-inline-context-rest-api.php` — REST API endpoints
- `includes/class-inline-context-frontend.php` — Frontend rendering and asset enqueuing
- `includes/class-inline-context-utils.php` — Category management and CSS variable handling
- `includes/class-inline-context-abilities.php` — WordPress Abilities API integration (v2.4+)
- `includes/functions.php` — Backward-compat global function wrappers

**Tests:**

- `src/**/*.test.js` — Jest unit tests (110 tests across 8 suites)
- `tests/test-*.php` — PHPUnit integration tests for CPT, REST API, sync, and abilities
- `tests/bootstrap.php` — PHPUnit bootstrap
- `jest.config.js`, `jest.setup.js`, `jest-mocks/` — Jest configuration and WordPress package mocks

**Build & tooling:**

- `webpack.config.js` — Webpack entries (`index`, `frontend`, `cpt-editor`)
- `build/` — Compiled assets (committed for WordPress.org distribution)
- `dist/` — Packaged plugin zip files
- `bin/package.sh` — Plugin packaging script
- `bin/install-wp-tests.sh` — WordPress test suite installer
- `bin/setup-tests.sh` — Interactive test setup wizard
- `composer.json` / `composer.lock` — PHPCS and PHPUnit dev dependencies

**Documentation:**

- `README.md` — Project README
- `readme.txt` — WordPress.org plugin readme
- `ROADMAP.md` — Future improvements and completed-feature log
- `ABILITIES-API.md` — Abilities API integration reference (v2.4+ abilities, schemas, examples)
- `FILTERS.md` — Public WordPress filters exposed by the plugin
- `RELEASE.md` — Release process notes
- `changelog.txt` — Full per-version changelog
- `tests/README.md` — How to run the PHPUnit suite locally

## WordPress Integration Points

### Asset Enqueuing Strategy

The plugin emits three webpack entries — `index` (block editor), `frontend` (public site), and `cpt-editor` (the `inline_context_note` edit screen). Each is enqueued through `Inline_Context_Frontend` (block-editor + public) and `Inline_Context_CPT` (CPT edit screen). Dependencies are sourced from each entry's generated `*.asset.php` file rather than hand-listed, so adding a `@wordpress/*` import is enough — no PHP change required.

```php
// Block editor (Inline_Context_Frontend::enqueue_editor_assets)
add_action( 'enqueue_block_editor_assets', /* enqueues inline-context-editor + style */ );

// Public site (Inline_Context_Frontend::enqueue_frontend_assets)
add_action( 'wp_enqueue_scripts', /* enqueues inline-context-frontend + style */ );

// CPT edit screen (Inline_Context_CPT)
add_action( 'admin_enqueue_scripts', /* enqueues inline-context-cpt-editor on inline_context_note */ );
```

### Dependencies

- **Editor bundle (`build/index.js`):** the generated `build/index.asset.php` is the source of truth; current deps include `wp-rich-text`, `wp-element`, `wp-components`, `wp-block-editor`, `wp-data`, `wp-i18n`, `wp-api-fetch`, `wp-keyboard-shortcuts`, `wp-keycodes`, `wp-plugins`, `react`, `react-dom`.
- **Frontend bundle (`build/frontend.js`):** plus `wp-hooks` declared at enqueue time for filter support; `dompurify` is bundled in for HTML sanitization.
- **Rich text editor:** `react-quill` is bundled into the editor and CPT-editor entries.

## WordPress Abilities API Integration (v2.4+)

The plugin registers five server-side abilities through `Inline_Context_Abilities` (gated on `function_exists( 'wp_register_ability' )` so it no-ops on WP < 6.9):

- **`inline-context/create-note`** — Create a new inline context note with rich-text content, optional category, and reusability flag.
- **`inline-context/search-notes`** — Search existing notes by title/content, with `only_reusable` filter.
- **`inline-context/get-categories`** — List configured categories with their meta (icon, color).
- **`inline-context/get-note`** — Fetch a single note by ID, returning content + metadata.
- **`inline-context/create-inline-note`** — AI helper that creates a note and returns ready-to-embed HTML markup with anchor + cached content attributes already populated.

Each ability declares an `input_schema` and `output_schema` (JSON Schema), so AI clients and automation tools get validated, structured I/O without extra glue.

On WordPress 7.0+, core's bundled `core-abilities` script module auto-registers every server-side ability into the client-side `core/abilities` `@wordpress/data` store, so the same abilities are also callable in the browser via `dispatch( 'core/abilities' ).executeAbility( name, input )` with no additional plugin code. See `ABILITIES-API.md` for the full schema reference.

## Styling Approach

- Uses CSS custom properties with fallbacks: `var(--wp--preset--color--primary, #0073aa)`
- Semantic class naming: `.wp-inline-context`, `.wp-inline-context-inline`, `.wp-inline-context--open`
- Accessibility: `role="note"` on revealed content, proper ARIA attributes
- Animation: Smooth reveal/hide transitions with CSS animations
- Theme compatibility: CSS variables for easy customization

## Export/Import Settings (v2.7)

**Functionality:**

- JSON-based export/import system for all plugin settings
- Accessible via Import/Export tab in admin settings
- Export includes: display mode, tooltip hover, animations, link style, icon placement, CSS variables, active preset
- Import validates JSON format and sanitizes all values
- Timestamped filenames: `inline-context-settings-YYYY-MM-DD-HHMMSS.json`

**Implementation:**

- `inline_context_export_settings()` - Generates JSON file with all options
- `inline_context_import_settings($file_path)` - Validates and imports settings
- `inline_context_render_import_export_tab()` - Admin UI with export button and file upload
- Settings mapped to sanitization callbacks for security
- WordPress Settings API integration for error/success messages

**Use Cases:**

- Backup configurations before making changes
- Share custom styling setups across multiple sites
- Quick setup for multisite networks
- Migrate settings between staging and production

## Print Styles (v2.7)

**Auto-Expansion:**

- All inline context notes automatically expand when printing
- Footnote-style numbering using CSS counters (e.g., [1], [2], [3])
- Counter initialized on body, incremented per note
- Notes display with "Note: " prefix and left border accent

**Print Optimization:**

- Removes interactive elements (chevron icons, close buttons, tooltips)
- Print-friendly color scheme (black text, light gray backgrounds)
- Smart link handling - shows URLs after link text (except anchor links)
- Page break avoidance inside notes
- Category icons rendered in grayscale
- Optimized typography (0.9em font size, 1.5 line height)

**Implementation:**

- `@media print` block in `src/style.scss`
- Uses CSS counters: `counter-reset`, `counter-increment`, `content: '[' counter(inline-context-counter) ']'`
- Link URL display via `::after` pseudo-element with `attr(href)`
- Tooltips hidden completely in print mode
- Notes indented (2em left margin) for clear visual hierarchy

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

- **Jest unit tests:** `npm run test:unit` runs 110 tests across `src/**/*.test.js` covering utils (`anchor`, `copy-link`, `text`), components (`PopoverActions`, `CategorySelector`, `LinkControl`, `NotesSidebar`), and `sidebar.js`. Use `--watch` during development.
- **PHPUnit integration tests:** `tests/test-*.php` cover CPT registration, REST API, sync logic, and Abilities API. Bootstrap via `tests/bootstrap.php`. Run with `bin/install-wp-tests.sh` (one-time setup) then `vendor/bin/phpunit`.
- **Quality gate:** `npm run test` runs `lint` + `test:unit`. Used in pre-package and pre-release scripts.
- **Demo page:** `demo.html` for testing the frontend rendering without a WordPress install.
- **Package validation:** Automated zip creation with development-file exclusion.
- **Manual browser testing:** Anchor links, auto-opening, tooltip positioning, hover behavior.
