# Inline Context - Roadmap

This document outlines completed features and future improvements for the Inline Context plugin.

## ✅ Completed in v2.0.0

### Modular Architecture & Code Quality ✓
**Status**: SHIPPED in v2.0.0

Complete codebase refactoring and quality improvements:
- ✅ **Modular Structure**: Six dedicated class-based modules (2,291 → 391 lines main file, 83% reduction)
- ✅ **Class-Based Architecture**: 
  - `Inline_Context_CPT` - Custom Post Type, metaboxes, admin UI (855 lines)
  - `Inline_Context_Sync` - Usage tracking, reusable sync, category sync (496 lines)
  - `Inline_Context_Deletion` - Deletion protection and cleanup (198 lines)
  - `Inline_Context_REST_API` - REST endpoints (340 lines)
  - `Inline_Context_Frontend` - Noscript, KSES, assets (276 lines)
  - `Inline_Context_Utils` - Categories, CSS variables (182 lines)
- ✅ **Code Quality**: Full WordPress coding standards (JavaScript and PHP)
- ✅ **Linting**: ESLint and PHPCS compliance with pre-release gates
- ✅ **Maintainability**: Clean separation of concerns, testable code
- ✅ **Developer Experience**: Better documentation, extensibility, debugging
- ✅ **Backward Compatibility**: Seamless upgrade from v1.x, zero breaking changes

**Benefits Delivered:**
- Significantly easier to maintain and extend
- Better testability with modular components
- Cleaner codebase for onboarding contributors
- Foundation for rapid future development
- Professional-grade code organization

## ✅ Completed in v1.3.0

### Note Categories with Custom Icons ✓
**Status**: SHIPPED in v1.3.0

Implemented complete category management system:
- ✅ Category selector in editor popover
- ✅ Custom icons for each category (closed/open states)
- ✅ Visual icon picker with 30 curated Dashicons
- ✅ Support for all 300+ Dashicons via manual entry
- ✅ Color-coded categories
- ✅ Keyboard-accessible icon picker (Esc, Tab navigation)
- ✅ ARIA labels and focus management
- ✅ Default categories: Internal Article, External Article, Definition, Tip

### Comprehensive Styling Controls ✓
**Status**: SHIPPED in v1.3.0

Implemented full styling customization:
- ✅ Tabbed admin interface (Categories + Styling)
- ✅ Link styling controls (hover, focus, open states)
- ✅ Note styling controls (padding, margins, borders, shadows)
- ✅ Chevron styling options
- ✅ Live interactive preview
- ✅ Helpful descriptions for each setting
- ✅ CSS variable integration

## ✅ Completed in v1.4.x

### Low-Tech Accessibility & Text-Based Browser Support ✓
**Status**: SHIPPED in v1.4.1

Implemented true progressive enhancement:
- ✅ **Progressive Enhancement**: Server-side rendering with endnotes for no-JS environments
- ✅ **Text-Based Browsers**: Full support for Lynx, w3m, links, etc. via footnote fallback
- ✅ **NoScript Fallback**: Endnotes displayed at bottom when JavaScript disabled
- ✅ **JavaScript Enhancement**: Inline notes when JavaScript is available
- ✅ **Print-Friendly**: Auto-expand all notes for printing
- ✅ **RSS Feeds**: Note content included in feed output (always in HTML)
- ✅ **Graceful Degradation**: Content accessible across all user agents
- ✅ **Clean Implementation**: CSS-based hiding (.js class) with proper semantic HTML

**Implementation**:
- PHP filter on `the_content` always renders endnotes section at bottom
- JavaScript adds `.js` class to body on load
- CSS rule `.js .wp-inline-context-noscript-notes { display: none }` hides endnotes when JS available
- JavaScript creates inline notes on click (original v1.3.0 behavior)
- Zero configuration needed - works automatically

**Benefits**: 
- Universal accessibility across all browsing environments
- Respects user choice of technology
- Better SEO (search engines see all content)
- Improved accessibility for assistive technologies
- Content remains useful in email clients, readers, and aggregators

## High Priority Features (v2.1)

### 1. Context Library Panel
**Impact**: High | **Effort**: Medium | **Priority**: #1 for v2.1

Add a sidebar panel in the editor showing all inline contexts in the current post:
- Quick navigation to any note
- Edit notes directly from panel
- See which notes are linked/shared
- Sort by creation date or alphabetically
- Search/filter functionality
- Filter by category

**Benefits**: Essential for managing posts with many notes. Improves content workflow significantly.

**Technical Notes**: Can leverage existing CPT infrastructure from v2.0 modular architecture.

### 2. Auto-Sync for Reusable Notes (Phase 3)
**Impact**: High | **Effort**: Medium | **Priority**: #2 for v2.1

Automatic synchronization when reusable notes are updated:
- Hook into CPT save to update all posts using it
- Background job to prevent timeout on high-usage notes
- Option: "Update on save" vs "Manual refresh"
- Bulk refresh tool in admin
- Progress indicator for large updates

**Benefits**: Completes the "edit once, update everywhere" vision. Currently notes use cached content for performance.

**Technical Notes**: Build on `Inline_Context_Sync` class from v2.0 architecture.

### 3. Preset Themes
**Impact**: High | **Effort**: Low | **Priority**: #3 for v2.1

Include 3-5 pre-configured color schemes in admin settings:
- Modern Blue (current default)
- Minimalist Gray
- High Contrast
- Warm Earth Tones
- Dark Mode

**Benefits**: Makes styling accessible to non-technical users without CSS knowledge.

**Technical Notes**: Integrate with existing settings system via `admin-settings.php`.

### 4. Tooltip Mode
**Impact**: High | **Effort**: Medium | **Priority**: #4 for v2.1

Alternative display style as hoverable tooltips:
- Option in admin settings: "Inline" or "Tooltip" mode
- Tooltips appear on hover with optional click-to-pin
- Configurable tooltip position (top/bottom/auto)
- Mobile: Fall back to click behavior

**Benefits**: Many users expect tooltip-style functionality. Provides cleaner design for certain use cases.

**Technical Notes**: Frontend rendering handled by `Inline_Context_Frontend` class.

## Medium Priority Features (v2.2)

### 5. Export/Import Settings
**Impact**: Medium | **Effort**: Low

### 4. Auto-Sync for Reusable Notes (Phase 3)
**Impact**: High | **Effort**: Medium

Automatic synchronization when reusable notes are updated:
- Hook into CPT save to update all posts using it
- Background job to prevent timeout on high-usage notes
- Option: "Update on save" vs "Manual refresh"
- Bulk refresh tool in admin

**Benefits**: Completes the "edit once, update everywhere" vision. Currently notes use cached content for performance.

## ✅ Completed in v1.5.0

### Context Library (Notes as Custom Post Type) ✓
**Status**: SHIPPED in v1.5.0

Implemented complete reusable notes system powered by Custom Post Type:

**Phase 1: Basic CPT Infrastructure** ✓
- ✅ Custom Post Type (`inline_context_note`) with title and rich content
- ✅ Category Taxonomy (`inline_context_category`) replacing meta-based system
- ✅ Editor popup with live search (AJAX query CPT by title)
- ✅ Two modes: Create new note / Select existing note
- ✅ Dual data storage: `data-note-id` + cached `data-inline-context`
- ✅ Frontend uses cached content (zero performance penalty)
- ✅ Usage tracking via REST API (non-blocking)
- ✅ Backward compatible (existing notes continue working)

**Phase 2: Reusability Features** ✓
- ✅ CPT Post Meta: `used_in_posts`, `is_reusable`, `usage_count`
- ✅ Enhanced CPT list view with custom columns
  - ✅ Reusable column (Yes/No instead of emoji)
  - ✅ Usage Count column (sortable)
  - ✅ Used In column (linked posts)
- ✅ Filter dropdown for reusable notes
- ✅ Delete warnings in 3 locations (post list, quick edit, single post delete)
- ✅ QuillEditor component integration
- ✅ Comprehensive uninstall system with content cleanup

**Benefits Delivered:**
- ✅ True reusability (edit once, update everywhere via cached content)
- ✅ Built-in WordPress features (search, revisions, taxonomy)
- ✅ Scalable (handles thousands of notes)
- ✅ No frontend performance penalty
- ✅ Graceful degradation (works even if CPT deleted)

**Future Enhancements (Phase 3):**
- Auto-Update System: Background job to refresh cached content in all posts
- Advanced Search: Filter by content, category, usage count
- Import/Export: Bulk operations via CSV/JSON
- Version History UI: Compare and restore with usage updates

## Medium Priority Features

## Medium Priority Features

### 5. Keyboard Shortcuts
**Impact**: Medium | **Effort**: Low

Add editor keyboard shortcuts:
- `Ctrl/Cmd + Shift + I`: Insert inline context
- `Ctrl/Cmd + K`: Edit existing context under cursor
- Navigate between notes with arrow keys

**Benefits**: Faster workflow for power users writing content-heavy posts.

### 6. Export/Import Settings
**Impact**: Medium | **Effort**: Low

Allow backing up and sharing configurations:
- Export all settings as JSON
- Import from file
- Reset to defaults option
- Share configurations across sites

**Benefits**: Easy setup for multi-site networks. Share custom themes with community.

**Technical Notes**: JSON export/import via `Inline_Context_Utils` class.

### 6. Keyboard Shortcuts
**Impact**: Medium | **Effort**: Low

Add editor keyboard shortcuts:
- `Ctrl/Cmd + Shift + I`: Insert inline context
- `Ctrl/Cmd + K`: Edit existing context under cursor
- Navigate between notes with arrow keys

**Benefits**: Faster workflow for power users writing content-heavy posts.

### 7. Animation Options
**Impact**: Medium | **Effort**: Low

Add animation controls in admin settings:
- Slide, fade, or no animation
- Animation speed control
- Reduced motion preference detection

**Benefits**: Personalization and accessibility (respects `prefers-reduced-motion`).

### 8. Statistics Dashboard
**Impact**: Medium | **Effort**: Medium

Show usage metrics in admin:
- Total notes across site
- Most-used categories
- Posts with most notes
- Note engagement tracking (if analytics integrated)

**Benefits**: Content strategy insights. Identify popular note types.

### 9. Search Integration
**Impact**: Medium | **Effort**: Medium

Make note content searchable:
- Include notes in WordPress search results
- Show which notes contain search terms
- Highlight matches when expanded
- Optional: exclude from search

**Benefits**: Improves discoverability of content hidden in notes.

### 10. Print Styles
**Impact**: Medium | **Effort**: Low

Automatically optimize for printing:
- Expand all notes when printing
- Remove interactive elements
- Adjust colors for print
- Option to hide notes entirely in print

**Benefits**: Better user experience for readers who print content.

## ✅ Completed in v1.5.0

### Context Library (Notes as Custom Post Type) ✓
**Status**: SHIPPED in v1.5.0

Implemented complete reusable notes system powered by Custom Post Type:

**Phase 1: Basic CPT Infrastructure** ✓
- ✅ Custom Post Type (`inline_context_note`) with title and rich content
- ✅ Category Taxonomy (`inline_context_category`) replacing meta-based system
- ✅ Editor popup with live search (AJAX query CPT by title)
- ✅ Two modes: Create new note / Select existing note
- ✅ Dual data storage: `data-note-id` + cached `data-inline-context`
- ✅ Frontend uses cached content (zero performance penalty)
- ✅ Usage tracking via REST API (non-blocking)
- ✅ Backward compatible (existing notes continue working)

**Phase 2: Reusability Features** ✓
- ✅ CPT Post Meta: `used_in_posts`, `is_reusable`, `usage_count`
- ✅ Enhanced CPT list view with custom columns
- ✅ Filter dropdown for reusable notes
- ✅ Delete warnings in 3 locations
- ✅ QuillEditor component integration
- ✅ Comprehensive uninstall system with content cleanup

**Benefits Delivered:**
- ✅ True reusability (edit once, cached everywhere)
- ✅ Built-in WordPress features (search, revisions, taxonomy)
- ✅ Scalable (handles thousands of notes)
- ✅ No frontend performance penalty
- ✅ Graceful degradation (works even if CPT deleted)

## Advanced Features (Future Consideration)

### 11. JavaScript Public API
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

### 12. Position Control
Display notes above/below trigger instead of inline:
- Floating box above/below trigger
- Sidebar placement option
- Sticky positioning for long notes

### 13. Dark Mode Support
Auto-detect system preferences:
- Automatically adjust colors
- Separate light/dark color schemes in settings
- CSS `prefers-color-scheme` integration

### 14. Conditional Display
Show/hide notes based on context:
- User role/capabilities
- Logged in/out status
- Custom conditions via filters

### 15. Multi-language Support
Different note content per language:
- WPML integration
- Polylang integration
- Store translations in post meta

### 15. Lazy Loading
Only load note content when clicked:
- Reduce initial page weight
- AJAX load on first open
- Cache in browser storage

### 16. REST API Endpoints
Programmatic access to inline contexts:
- `/wp-json/inline-context/v1/notes`
- CRUD operations via REST
- Bulk operations support

### 17. Note Versioning
Track changes to note content:
- Revision history like posts
- Restore previous versions
- See who changed what (multi-author sites)

### 18. Block Pattern Library
Pre-built patterns with inline contexts:
- FAQ section with expandable answers
- Definition list with hover explanations
- Academic paper with citations
- Product page with spec details

### 19. Accessibility Enhancements
- Screen reader modes with customizable announcements
- Enhanced keyboard navigation between notes
- Built-in contrast checker with WCAG warnings
- Auto-expand notes for screen readers (optional)

### 20. SEO Optimization
Schema.org markup for special note types:
- Definition type → DefinedTerm schema
- FAQ type → FAQPage schema
- Include in Open Graph for social sharing

## Technical Improvements

### Developer Experience
- **Custom Post Type Support**: Make it work everywhere, not just posts/pages
- **Gutenberg Slot/Fill**: Let other plugins extend the settings panel
- **Webhook Support**: Trigger actions when notes are created/modified
- **WP-CLI Commands**: Bulk operations from command line

### Performance
- Optimize CSS output (minify, combine)
- Tree-shake unused JavaScript
- Conditional loading (only on posts with notes)

### Testing
- Add unit tests (Jest for JavaScript)
- Add integration tests (PHPUnit)
- E2E testing with Playwright
- Browser compatibility matrix

## Implementation Strategy

### v2.0 Foundation ✅ (COMPLETED)
Modular architecture and code quality:
1. ✅ Six class-based modules
2. ✅ WordPress coding standards compliance
3. ✅ Enhanced maintainability and testability
4. ✅ Clean separation of concerns
5. ✅ Backward compatibility preserved

### v2.1: Enhanced User Experience (Next Release)
Focus on high-impact user-facing features:
1. Context Library Panel (editor sidebar)
2. Auto-Sync for reusable notes
3. Preset Themes for easy styling
4. Tooltip Mode alternative display

### v2.2: Advanced Functionality
Power user and customization features:
1. Export/Import Settings
2. Keyboard Shortcuts
3. Animation Options
4. Statistics Dashboard
5. JavaScript Public API

### v2.3: Content Management
Enterprise and multi-site features:
1. Advanced Search and Filtering
2. Bulk Import/Export Operations
3. Note Versioning with History
4. Block Pattern Library

## Technical Improvements

### Developer Experience (Ongoing)
- ✅ **v2.0**: Modular class-based architecture
- **v2.1+**: Unit tests (Jest for JavaScript, PHPUnit for PHP)
- **v2.1+**: E2E testing with Playwright
- **v2.2+**: Custom Post Type Support beyond posts/pages
- **v2.2+**: Gutenberg Slot/Fill for extensibility
- **v2.3+**: WP-CLI Commands for bulk operations

### Performance (Ongoing)
- ✅ **v2.0**: Optimized class loading and initialization
- **v2.1+**: Conditional asset loading (only on posts with notes)
- **v2.2+**: Minify and combine CSS output
- **v2.2+**: Tree-shake unused JavaScript

## Feedback Welcome

These are suggestions based on user needs and WordPress ecosystem trends. Actual development priorities depend on:
- User feedback and feature requests
- WordPress core changes
- Resource availability
- Performance considerations
- Community contributions

**Have suggestions?** [Open an issue on GitHub](https://github.com/jooplaan/inline-context/issues)!

**Want to contribute?** The v2.0 modular architecture makes it easier than ever to add features. Check the [contributing guidelines](https://github.com/jooplaan/inline-context/blob/main/CONTRIBUTING.md) to get started.
