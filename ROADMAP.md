# Inline Context - Roadmap

This document outlines future improvements and completed features for the Inline Context plugin.

## High Priority Features (Next Release)

## Medium Priority Features

### 1. Export/Import Settings

**Impact**: Medium | **Effort**: Low

Allow backing up and sharing configurations:

- Export all settings as JSON
- Import from file
- Reset to defaults option
- Share configurations across sites

**Benefits**: Easy setup for multi-site networks. Share custom themes with community.

**Technical Notes**: JSON export/import via `Inline_Context_Utils` class.

### 2. Animation Options

**Impact**: Medium | **Effort**: Low

Add animation controls in admin settings:

- Slide, fade, or no animation
- Animation speed control
- Reduced motion preference detection

**Benefits**: Personalization and accessibility (respects `prefers-reduced-motion`).

### 3. Statistics Dashboard

**Impact**: Medium | **Effort**: Medium

Show usage metrics in admin:

- Total notes across site
- Most-used categories
- Posts with most notes
- Note engagement tracking (if analytics integrated)

**Benefits**: Content strategy insights. Identify popular note types.

### 4. Search Integration

**Impact**: Medium | **Effort**: Medium

Make note content searchable and discoverable:

- Include notes in WordPress search results
- Auto-expand notes containing search terms on search results pages
- Auto-show tooltips (tooltip mode) for notes with matching content
- Highlight search term matches within note content
- Show which notes contain search terms in search result excerpts
- Optional: exclude specific notes/categories from search

**Use Case Example**: User searches for "Understanding" - a page appears in results because the term exists in a note. When viewing the search results or clicking through to the page, the note containing "Understanding" is automatically expanded (inline mode) or shown as tooltip (tooltip mode), making the relevant content immediately visible.

**Benefits**: Improves discoverability of content hidden in notes. Users can find information in notes without manually expanding each one.

### 5. Print Styles Enhancement

**Impact**: Medium | **Effort**: Low

Improve printing experience:

- Auto-expand all notes when printing (already works)
- Remove interactive elements
- Adjust colors for print
- Option to hide notes entirely in print

**Benefits**: Better user experience for readers who print content.

## Advanced Features (Future Consideration)

### 6. JavaScript Public API

**Impact**: Medium | **Effort**: Medium

Expose public API for programmatic control:

```javascript
window.InlineContext.open(noteId)
window.InlineContext.close(noteId)
window.InlineContext.toggle(noteId)
window.InlineContext.getAll()
```

**Benefits**: Enables advanced integrations and custom user experiences.

**Technical Notes**: Frontend API via `Inline_Context_Frontend` class.

### 7. Position Control

Display notes above/below trigger instead of inline:

- Floating box above/below trigger
- Sidebar placement option
- Sticky positioning for long notes

### 8. Dark Mode Support

Auto-detect system preferences:

- Automatically adjust colors
- Separate light/dark color schemes in settings
- CSS `prefers-color-scheme` integration

### 9. Conditional Display

Show/hide notes based on context:

- User role/capabilities
- Logged in/out status
- Custom conditions via filters

### 10. Multi-language Support

Different note content per language:

- WPML integration
- Polylang integration
- Store translations in post meta

### 11. Lazy Loading

Only load note content when clicked:

- Reduce initial page weight
- AJAX load on first open
- Cache in browser storage

### 12. REST API Endpoints ✓

**Status**: Completed in v2.4.0 via WordPress Abilities API

Programmatic access to inline contexts:

- ✅ Five REST API abilities via WordPress 6.9+ Abilities API
- ✅ `create-note`, `search-notes`, `get-categories`, `get-note`, `create-inline-note`
- ✅ Full CRUD operations with JSON Schema validation
- ✅ Browser-based AI integration (Claude, ChatGPT)
- Future: Bulk operations support

### 13. Note Versioning

Track changes to note content:

- Revision history like posts
- Restore previous versions
- See who changed what (multi-author sites)

### 14. Block Pattern Library

Pre-built patterns with inline contexts:

- FAQ section with expandable answers
- Definition list with hover explanations
- Academic paper with citations
- Product page with spec details

### 15. Accessibility Enhancements

- Screen reader modes with customizable announcements
- Enhanced keyboard navigation between notes
- Built-in contrast checker with WCAG warnings
- Auto-expand notes for screen readers (optional)

### 16. SEO Optimization

Schema.org markup for special note types:

- Definition type → DefinedTerm schema
- FAQ type → FAQPage schema
- Include in Open Graph for social sharing

## Technical Improvements

### Developer Experience (Ongoing)

- ✅ **v2.0**: Modular class-based architecture
- ✅ **v2.2**: PHPUnit testing infrastructure with WordPress Test Suite
- **Future**: E2E testing with Playwright
- **Future**: Custom Post Type Support beyond posts/pages
- **Future**: Gutenberg Slot/Fill for extensibility
- **Future**: WP-CLI Commands for bulk operations

### Performance (Ongoing)

- ✅ **v2.0**: Optimized class loading and initialization
- **Future**: Conditional asset loading (only on posts with notes)
- **Future**: Minify and combine CSS output
- **Future**: Tree-shake unused JavaScript

---

## Completed Features

### v2.6.0 - Preset Color Themes ✓

Released: December 2025

**Color Preset System:**

- ✅ Five pre-configured color schemes for one-click styling
- ✅ Modern Blue (Default) - Clean, professional look with blue accents
- ✅ Minimalist Gray - Subtle, understated design in grayscale
- ✅ High Contrast - Bold colors for maximum visibility and accessibility
- ✅ Warm Earth Tones - Cozy, natural palette with browns and oranges
- ✅ Dark Mode - Dark theme with light text for reduced eye strain
- ✅ Each preset includes complete configuration for all 36 CSS variables

**Smart Preset Management:**

- ✅ Automatic preset detection - system recognizes which preset is active
- ✅ Custom indicator - displays "Custom" when user modifies preset values
- ✅ Preset selection persistence - remembers which preset was applied
- ✅ Override warning - confirms before applying preset over custom settings
- ✅ Seamless integration with existing styling system via WordPress Settings API

**User Experience:**

- ✅ Dropdown selector with dynamic preset descriptions
- ✅ One-click preset application with instant feedback
- ✅ Full customization still available after applying preset
- ✅ Makes advanced styling accessible to non-technical users

**Benefits**: Eliminates the need for CSS knowledge to achieve professional-looking inline contexts. Users can start with a preset and customize individual values as needed, or use presets as-is for instant styling.

### v2.4.0-2.4.1 - AI Integration & Visual Enhancements ✓

Released: December 2025

**WordPress Abilities API Integration:**

- ✅ WordPress 6.9+ Abilities API integration for AI assistant discovery
- ✅ Five REST API abilities for AI-powered content enhancement:
  - `create-note` - Create new inline context notes via API
  - `search-notes` - Search existing notes by title/content with filters
  - `get-categories` - Retrieve all available categories with metadata
  - `get-note` - Fetch specific note by ID with usage data
  - `create-inline-note` - Create note and get ready-to-embed HTML markup (AI helper)
- ✅ Browser-based AI integration (Claude, ChatGPT) with automatic capability discovery
- ✅ Authentication support via cookie auth (browser) or Application Passwords
- ✅ JSON Schema validation for all ability inputs with permission callbacks
- ✅ Comprehensive ABILITIES-API.md documentation with workflows and examples
- ✅ Complete test coverage (32 tests, 110 assertions) for Abilities API
- ✅ Backward compatible - works on WordPress 6.0+ (Abilities API optional on 6.9+)

**Visual Enhancements:**

- ✅ Pill-style link display option with button-like appearance
- ✅ Configurable pill styling: border, padding, background, hover states
- ✅ Icon placement control system (top, middle, bottom alignment)
- ✅ Flexible icon alignment for both text and pill display modes
- ✅ Separate styling controls in admin settings panel
- ✅ Enhanced CSS variable system for icon positioning

**Editor Experience:**

- ✅ Context Library Panel (basic version) - Sidebar showing all inline contexts in current post
- ✅ Quick navigation to any note with scroll, highlight, and block selection
- ✅ Note list with category badges and content excerpts
- ✅ Usage count display for each note
- Future enhancements (if needed): Direct editing from panel, sorting options, search/filter, reusable note indicators

**Developer Experience:**

- ✅ Test infrastructure for Abilities API (test-abilities.sh, tests/test-abilities.php)
- ✅ AIFeatures component foundation (disabled by default, ready for future UI)
- ✅ PHP coding standards fixes (whitespace and alignment)
- ✅ Updated "Tested up to" WordPress 6.9

### v2.5.0 - Editor Keyboard Shortcuts ✓

Released: December 2025

**Keyboard Shortcuts:**

- ✅ Cmd+Shift+I (Ctrl+Shift+I on Windows) - Insert inline context when text is selected
- ✅ Cmd+Shift+K (Ctrl+Shift+K on Windows) - Edit existing inline context at cursor position
- ✅ Custom useEditorKeyboardShortcuts hook for editor-level shortcuts
- ✅ Format navigation utilities (isCaretInFormat) for detecting cursor position
- ✅ Shortcuts only active when popover is closed (no conflicts with popover shortcuts)
- ✅ Silent fail behavior for invalid contexts (no selection, cursor not in format)
- ✅ Keyboard accessibility for power users and editors

**Discoverability:**

- ✅ Toolbar button tooltip shows keyboard shortcut (platform-aware: ⇧⌘I on Mac, Ctrl+Shift+I on Windows)
- ✅ Shortcuts registered with WordPress keyboard shortcuts help panel (Shift+Alt+H)
- ✅ Integration with WordPress native help system for better user onboarding

**Benefits**: Significantly faster workflow for power users writing content-heavy posts. Reduces reliance on mouse for inline context management. Shortcuts are discoverable through standard WordPress UI patterns.

### v2.3.0 - Tooltip Hover Activation ✓

Released: November 16, 2025

- ✅ Optional hover activation for tooltips with configurable 300ms delay
- ✅ Admin setting to enable tooltip display on mouse hover
- ✅ Smart hover behavior - tooltip stays open when moving mouse to tooltip content
- ✅ Conditional admin UI - hover option only visible when tooltip mode is selected
- ✅ 100ms grace period for smooth mouse transition between trigger and tooltip
- ✅ Separate show/hide timeout management for robust interaction
- ✅ Enhanced user experience for interacting with tooltip content and links
- ✅ Settings persistence in demo.html using localStorage
- ✅ Updated documentation (README.md, ROADMAP.md, copilot-instructions.md)

### v2.2.0 - Reusable Note Management & Testing Infrastructure ✓

Released: November 15, 2025

- ✅ Convert reusable notes to non-reusable with automatic synchronization
- ✅ Modal confirmation dialog prevents accidental conversions
- ✅ PopoverActions component with reusable checkbox control
- ✅ PHPUnit testing infrastructure with WordPress Test Suite integration
- ✅ 18 comprehensive test methods covering CPT, REST API, and sync functionality
- ✅ .env configuration support for secure database credentials
- ✅ Testing documentation (TESTING.md, tests/README.md, TESTING-SETUP.md)
- ✅ Interactive test setup wizard (bin/setup-tests.sh)
- ✅ Display mode switcher in demo.html for testing inline/tooltip modes
- ✅ Dynamic display mode detection instead of static configuration
- ✅ Consolidated bin/ directory for all scripts
- ✅ Markdown linting for documentation consistency
- ✅ RELEASE.md documentation with complete release process

### v2.1.0 - Tooltip Display Mode ✓

Released: November 14, 2025

- ✅ Tooltip display mode as alternative to inline expansion
- ✅ General settings tab with display mode selection (inline/tooltip)
- ✅ Smart tooltip positioning with viewport boundary detection
- ✅ Automatic position flipping to prevent off-screen tooltips
- ✅ Close button on tooltips with proper event cleanup
- ✅ Full keyboard support (Space/Enter to activate, Escape to close)
- ✅ Automatic focus management for keyboard users
- ✅ DOMPurify integration for secure HTML rendering
- ✅ CSS animations for smooth tooltip reveal
- ✅ Click toggle behavior (click again to close)

### v2.0.0 - Modular Architecture & Code Quality ✓

Released: November 12, 2025

- ✅ Complete modular restructuring (main file reduced from 2,291 to 391 lines, 83% reduction)
- ✅ Six dedicated class-based modules for optimal separation of concerns
- ✅ Full WordPress coding standards compliance (JavaScript and PHP)
- ✅ ESLint and PHPCS compliance with pre-release gates
- ✅ Clean bootstrap pattern with class initialization
- ✅ Enhanced maintainability and testability
- ✅ Backward compatibility preserved (zero breaking changes)

### v1.5.0 - Context Library (Custom Post Type) ✓

Released: 2025

- ✅ Custom Post Type (`inline_context_note`) for reusable notes
- ✅ Category Taxonomy (`inline_context_category`)
- ✅ Editor popup with live search (AJAX query CPT by title)
- ✅ Two modes: Create new note / Select existing note
- ✅ Dual data storage: `data-note-id` + cached `data-inline-context`
- ✅ Frontend uses cached content (zero performance penalty)
- ✅ Usage tracking via REST API (non-blocking)
- ✅ **Auto-sync for reusable notes**: When a reusable note is updated, content automatically syncs to all posts using it
- ✅ Automatic `data-inline-context` attribute updates across all using posts
- ✅ Enhanced CPT list view with custom columns (Reusable, Usage Count, Used In)
- ✅ Filter dropdown for reusable notes
- ✅ Delete warnings in 3 locations (post list, quick edit, single post delete)
- ✅ QuillEditor component for rich text editing
- ✅ Comprehensive uninstall system with content cleanup options

### v1.4.1 - Progressive Enhancement ✓

Released: 2025

- ✅ Server-side rendering with endnotes for no-JS environments
- ✅ Full support for text-based browsers (Lynx, w3m, links)
- ✅ NoScript fallback with endnotes at bottom
- ✅ Print-friendly auto-expand all notes
- ✅ RSS feed content inclusion
- ✅ Graceful degradation across all user agents

### v1.3.0 - Categories & Styling ✓

Released: 2025

- ✅ Category management system with custom icons and colors
- ✅ Visual icon picker with 30 curated Dashicons
- ✅ Support for all 300+ Dashicons via manual entry
- ✅ Dual icon states (closed/open) with automatic toggling
- ✅ Keyboard-accessible icon picker (Esc, Tab navigation)
- ✅ Tabbed admin interface (Categories and Styling)
- ✅ Comprehensive styling controls
- ✅ Live preview with interactive note reveal
- ✅ CSS variable integration

---

## Feedback Welcome

These are suggestions based on user needs and WordPress ecosystem trends. Actual development priorities depend on:

- User feedback and feature requests
- WordPress core changes
- Resource availability
- Performance considerations
- Community contributions

**Have suggestions?** [Open an issue on GitHub](https://github.com/jooplaan/inline-context/issues)!

**Want to contribute?** The v2.0 modular architecture makes it easier than ever to add features. Check the [contributing guidelines](https://github.com/jooplaan/inline-context/blob/main/CONTRIBUTING.md) to get started.
