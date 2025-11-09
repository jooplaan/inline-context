# Inline Context - Version 2.0 Roadmap

This document outlines potential features and improvements for version 2.0 of the Inline Context plugin.

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

## High Priority Features

### 1. Preset Themes
**Impact**: High | **Effort**: Low

Include 3-5 pre-configured color schemes in admin settings:
- Modern Blue (current default)
- Minimalist Gray
- High Contrast
- Warm Earth Tones
- Dark Mode

**Benefits**: Makes styling accessible to non-technical users without CSS knowledge.

### 2. Context Library Panel
**Impact**: High | **Effort**: Medium

Add a sidebar panel in the editor showing all inline contexts in the current post:
- Quick navigation to any note
- Edit notes directly from panel
- See which notes are linked/shared
- Sort by creation date or alphabetically
- Search/filter functionality
- Filter by category

**Benefits**: Essential for managing posts with many notes. Improves content workflow significantly.

### 3. Tooltip Mode
**Impact**: High | **Effort**: Medium

Alternative display style as hoverable tooltips:
- Option in admin settings: "Inline" or "Tooltip" mode
- Tooltips appear on hover with optional click-to-pin
- Configurable tooltip position (top/bottom/auto)
- Mobile: Fall back to click behavior

**Benefits**: Many users expect tooltip-style functionality. Provides cleaner design for certain use cases.

### 4. Context Library (Global/Reusable Notes via CPT)
**Impact**: High | **Effort**: High

Create a "Context Library" of reusable notes powered by a dedicated Custom Post Type (CPT).
- **Centralized Management**: Manage all reusable notes from a single "Context Library" screen in the WP admin.
- **Custom Post Type**: Store notes in a dedicated `inline_context` CPT for robustness and compatibility.
- **Insert from Library**: Insert existing notes from the library via a searchable dropdown in the editor popover.
- **Update Once, Reflect Everywhere**: Edit a note in the library, and the changes automatically apply to all instances.
- **Track Usage**: See which posts and pages use a specific note.
- **Import/Export**: Allow bulk import/export of notes via CSV or JSON for easy migration and backup.

**Benefits**: Transforms the plugin into a powerful knowledge management tool. Huge time-saver for repeated information (product specs, author bios, legal disclaimers).

## 🚧 In Progress: Custom Post Type Implementation

### Context Library (Notes as Custom Post Type)
**Status**: IN DEVELOPMENT (feature/make-note-post-type branch)

**Core Goals:**
1. ✅ Reusable notes (edit once, update everywhere)
2. ✅ Search through all notes
3. ✅ Overview of notes per page
4. ✅ Track where each note is used

**Implementation Strategy: CPT with Cached Content**

The approach combines the benefits of Custom Post Types for management with cached content for performance:
- Each note is a CPT post (`inline_context_note`)
- Links store **both** `data-note-id` and cached `data-inline-context`
- Frontend uses cached content (no performance penalty)
- CPT enables centralized management and reusability

### Phase 1: Basic CPT Infrastructure (Current Sprint)
**Goal**: Create foundation without breaking existing functionality

**Technical Implementation:**
1. **Custom Post Type** (`inline_context_note`)
   - Title: Note identifier/name
   - Content: Rich text note content (replaces `data-inline-context`)
   - Supports: title, editor, revisions
   - Show in menu: true
   - Capability type: 'post'

2. **Category Taxonomy**
   - Replace current category system (meta-based)
   - Standard WordPress taxonomy: `inline_context_category`
   - Migrate existing categories to taxonomy terms
   - Store icon/color info in term meta

3. **Editor Popup Modifications**
   - Add live search field (AJAX query CPT by title)
   - Two modes:
     - "Create new note" → Creates CPT post
     - "Select existing" → Load from search results
   - On save: Create/update CPT post
   - On edit: Load content from CPT

4. **Data Storage** (Dual approach)
   ```html
   <a class="wp-inline-context" 
      data-note-id="123"
      data-inline-context="<p>Cached content</p>"
      data-anchor-id="context-note-abc">link text</a>
   ```
   - `data-note-id`: CPT post ID (new)
   - `data-inline-context`: Cached HTML content (current)
   - `data-anchor-id`: Unique anchor for linking (current)

5. **Content Caching Strategy**
   - ✅ Cache at editor save time (JavaScript)
   - ❌ NOT on `save_post` hook (breaks Gutenberg blocks)
   - Editor fetches CPT content and stores in `data-inline-context`
   - Usage tracking happens in editor when note is applied

6. **Frontend** 
   - **No changes** to display logic
   - Still uses `data-inline-context` attribute
   - Maintains current performance
   - Backward compatible with existing notes

7. **Usage Tracking**
   - Track when note is applied/selected in editor
   - Update CPT meta: `used_in_posts`, `usage_count`
   - Non-blocking (failures don't prevent saving)

**Migration Strategy:**
- Existing notes continue to work (have `data-inline-context` but no `data-note-id`)
- New notes get both attributes
- Optional: Tool to migrate old notes to CPT

### Phase 2: Reusability Features (Next Sprint)
**Goal**: Enable true note reusability and tracking

1. **CPT Post Meta**
   - `used_in_posts`: Array of post IDs using this note
   - `is_reusable`: Boolean flag (default: false)
   - `usage_count`: Number of times used

2. **Admin Interface**
   - "Notes Library" admin page listing all CPT posts
   - Columns: Title, Category, Usage Count, Used In
   - Bulk actions: Delete, Change Category
   - Click "Used In" → Shows list of posts with edit links

3. **Note Editor Enhancements**
   - "Mark as Reusable" checkbox in CPT editor
   - "Update All Usages" button (refreshes cache in all posts)
   - Warning when deleting note with usages
   - Show "Used in X posts" in sidebar

4. **Usage Tracking**
   - On post save: Update CPT meta `used_in_posts`
   - On CPT delete: Option to keep cached content or remove
   - Dashboard widget: Most used notes

### Phase 3: Auto-Sync & Advanced Features (Future)
**Goal**: Automatic synchronization and power features

1. **Auto-Update System**
   - Hook into CPT save to update all posts using it
   - Background job to prevent timeout on high-usage notes
   - Option: "Update on save" vs "Manual refresh"
   - Bulk refresh tool in admin

2. **Advanced Search**
   - Search notes by content, not just title
   - Filter by category, usage count
   - "Unused notes" cleanup tool

3. **Import/Export**
   - Export notes library as JSON
   - Import notes from CSV/JSON
   - Bulk create notes from file

4. **Version History**
   - Leverage WordPress revisions (automatic with CPT)
   - "Restore previous version" → Option to update all usages
   - Compare revisions side-by-side

**Benefits of This Approach:**
- ✅ No frontend performance penalty (cached content)
- ✅ Graceful degradation (works even if CPT deleted)
- ✅ Backward compatible (existing notes keep working)
- ✅ True reusability (edit once, update everywhere)
- ✅ Built-in WordPress features (search, revisions, taxonomy)
- ✅ Scalable (can handle thousands of notes)
- ✅ Flexible (can add auto-sync later)

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

### 7. JavaScript Public API
**Impact**: Medium | **Effort**: Medium

Expose public API for programmatic control:
```javascript
window.InlineContext.open(noteId)
window.InlineContext.close(noteId)
window.InlineContext.toggle(noteId)
window.InlineContext.getAll()
```

**Benefits**: Enables advanced integrations and custom user experiences.

### 8. Export/Import Settings
**Impact**: Medium | **Effort**: Low

Allow users to save and share styling configurations:
- Export settings as JSON
- Import from file or clipboard
- Include with theme.json for theme developers
- Share presets between sites

**Benefits**: Simplifies multi-site management and theme distribution.

### 9. Note Statistics (Privacy-Friendly)
**Impact**: Medium | **Effort**: Medium

Track which notes are clicked most:
- Store data locally (no external services)
- View in admin dashboard widget
- Sort by popularity in Context Library
- Optional: Export to CSV

**Benefits**: Helps content creators understand what readers find valuable.

### 10. Print Styles
**Impact**: Medium | **Effort**: Low

Automatically optimize for printing:
- Expand all notes when printing
- Remove interactive elements
- Adjust colors for print
- Option to hide notes entirely in print

**Benefits**: Better user experience for readers who print content.

## Advanced Features (Future Consideration)

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

### Phase 1: Foundation (v2.0)
Focus on high-impact, lower-effort features:
1. Preset Themes
2. Export/Import Settings
3. Animation Options
4. Print Styles
5. JavaScript Public API

### Phase 2: Editor Experience (v2.1)
Improve content creation workflow:
1. Context Library Panel
2. Note Categories with Icons
3. Block Pattern Library

### Phase 3: Advanced Display (v2.2)
Alternative presentation modes:
1. Tooltip Mode
2. Position Control
3. Dark Mode Support

### Phase 4: Content Management (v2.3)
Power user features:
1. Global/Reusable Notes
2. Note Versioning
3. Bulk Import/Export

## Feedback Welcome

These are suggestions based on similar plugins and common user needs. Actual development priorities will depend on:
- User feedback and feature requests
- WordPress ecosystem changes
- Resource availability
- Performance considerations

Have suggestions or want to prioritize certain features? [Open an issue on GitHub](https://github.com/jooplaan/inline-context/issues)!
