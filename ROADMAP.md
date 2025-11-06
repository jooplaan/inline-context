# Inline Context - Version 2.0 Roadmap

This document outlines potential features and improvements for version 2.0 of the Inline Context plugin.

## High Priority Features

### 1. Note Categories with Custom Icons
**Impact**: High | **Effort**: Medium

Allow users to categorize notes (definition, example, warning, tip) with distinct icons:
- Replace chevron with category-specific icons
- Color-code categories automatically
- Filter/search notes by category in editor
- Add category selector to note editor

**Benefits**: Significantly improves visual organization and helps readers quickly identify note types.

### 2. Preset Themes
**Impact**: High | **Effort**: Low

Include 3-5 pre-configured color schemes in admin settings:
- Modern Blue (current default)
- Minimalist Gray
- High Contrast
- Warm Earth Tones
- Dark Mode

**Benefits**: Makes styling accessible to non-technical users without CSS knowledge.

### 3. Context Library Panel
**Impact**: High | **Effort**: Medium

Add a sidebar panel in the editor showing all inline contexts in the current post:
- Quick navigation to any note
- Edit notes directly from panel
- See which notes are linked/shared
- Sort by creation date or alphabetically
- Search/filter functionality

**Benefits**: Essential for managing posts with many notes. Improves content workflow significantly.

### 4. Tooltip Mode
**Impact**: High | **Effort**: Medium

Alternative display style as hoverable tooltips:
- Option in admin settings: "Inline" or "Tooltip" mode
- Tooltips appear on hover with optional click-to-pin
- Configurable tooltip position (top/bottom/auto)
- Mobile: Fall back to click behavior

**Benefits**: Many users expect tooltip-style functionality. Provides cleaner design for certain use cases.

### 5. Global/Reusable Notes
**Impact**: High | **Effort**: High

Create reusable notes that can be inserted anywhere:
- Store notes in custom post type
- Insert via dropdown in editor
- Update once, reflect everywhere
- Track usage locations

**Benefits**: Huge time-saver for repeated information (product specs, author bios, legal disclaimers).

## Medium Priority Features

### 6. Animation Options
**Impact**: Medium | **Effort**: Low

Let users choose reveal animation style:
- Fade (current default)
- Slide down
- Scale
- No animation (instant)
- Respect `prefers-reduced-motion`

**Benefits**: Improves accessibility and gives users control over visual experience.

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

### 11. Position Control
Display notes above/below trigger instead of inline:
- Floating box above/below trigger
- Sidebar placement option
- Sticky positioning for long notes

### 12. Dark Mode Support
Auto-detect system preferences:
- Automatically adjust colors
- Separate light/dark color schemes in settings
- CSS `prefers-color-scheme` integration

### 13. Conditional Display
Show/hide notes based on context:
- User role/capabilities
- Logged in/out status
- Custom conditions via filters

### 14. Multi-language Support
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
