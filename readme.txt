=== Inline Context ===
Contributors: joop
Tags: inline, footnote, tooltip, reveal, context
Requires at least: 6.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 2.2.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Add inline expandable context notes with direct anchor linking. Optionally show the notes as tooltip popover.

== Description ==
Inline Context lets you enrich content with expandable context notes that maintain optimal reading flow. Create reusable notes via Custom Post Type, organize with custom categories (each with distinct icons and colors), and control all styling through a tabbed admin interface.

This plugin originated from a project with Renée Kool — a visual artist working in public art, film, and emerging media. She wanted to create a website where a single link could reveal additional content containing multiple related links.

As inspiration, we looked at the Dutch journalism platform De Correspondent, which uses inline contextual notes: small linked text fragments with an icon. When activated, they reveal an extra HTML element containing supplementary information. These contextual notes can:

- provide definitions
- offer additional context before the reader follows links in the main text
- stay out of the way to keep the article readable

You can see examples of their inline notes in this article:
https://decorrespondent.nl/16239/hoe-vriendschap-de-belangrijkste-relatie-werd-van-deze-tijd/2bc79aff-1546-08e1-349f-e865e38c46da

Renée Kool’s website:
https://reneekool.nl/


= Key Features Inline Context plugin =
* **Display Modes (NEW v2.1)**: Choose between inline expansion or floating tooltips
* **Smart Tooltips (NEW v2.1)**: Automatic positioning that prevents off-screen display
* **Reusable Notes (v1.5)**: Create notes as Custom Post Type entries and reuse them across multiple posts
* **Notes Library (v1.5)**: Centralized management of all notes with usage tracking
* **Quick Search (v1.5)**: Find and insert existing notes instantly from the editor
* **Usage Tracking (v1.5)**: See where each note is used across your site
* **Category Management**: Organize notes with custom categories (Internal Article, External Article, Definition, Tip, etc.)
* **Custom Icons**: Choose from 30 curated Dashicons or use any of 300+ available icons
* **Icon States**: Different icons for closed/open states provide visual feedback
* **Tabbed Settings**: Clean admin interface with General, Categories, Styling, and Uninstall tabs
* **Visual Icon Picker**: Accessible modal with keyboard navigation (Esc to close, Tab to navigate)
* **Direct Anchor Links**: Every note gets a unique URL anchor for easy sharing (#context-note-xxx)
* **Auto-Opening**: Notes automatically open when accessed via direct link
* **Smart Link Behavior**: Internal links stay in same tab, external links open in new tab with security
* **Rich Text Support**: Notes support bold, italic, links, lists with ReactQuill editor
* **Comprehensive Styling**: Control colors, spacing, borders, shadows for links and notes
* **Security First**: Content sanitized with DOMPurify before frontend display
* **Full Accessibility**: ARIA support, keyboard navigation, focus management, Escape key support
* **WordPress Integration**: Clean toolbar button in Rich Text format controls

= Reusable Notes System (v1.5) =
Create and manage notes efficiently:
* **Custom Post Type**: Notes stored as `inline_context_note` CPT
* **Search Interface**: Live search in editor popover to find existing notes
* **Create or Select**: Choose to create new notes or reuse existing ones
* **Usage Overview**: Enhanced list view shows usage count and which posts use each note
* **Filter by Reusability**: Filter notes marked as reusable in the admin list
* **Delete Protection**: Warnings when deleting notes that are actively used
* **Cached Performance**: Notes cached in content for fast frontend performance

= Category System =
Create custom categories with:
* Unique names (e.g., "Internal Article", "Definition", "Quick Tip")
* Closed state icon (shown on trigger link)
* Open state icon (shown when note is revealed)
* Custom color for icon styling
* Visual icon picker with 30 commonly used Dashicons
* Support for all 300+ Dashicons via manual entry

= Styling Controls =
Customize every aspect of appearance:
* **Link Styling**: Hover colors, focus states, open state colors
* **Note Styling**: Padding, margins, background, borders, accent bar, shadows
* **Chevron Styling**: Size, color, opacity for the expand indicator
* **Live Preview**: See changes immediately with interactive example

= Security =
Inline Context renders note content on the frontend. Content authored in the editor is sanitized before display. We recommend using the latest WordPress for improved security and KSES handling.

= Internationalization =
This plugin is translation-ready. POT files can be generated from source with `npm run pot` and placed under the `languages/` directory.

== Installation ==
1. Upload the plugin files to the `/wp-content/plugins/inline-context` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Configure categories and styling in Settings > Inline Context.
4. In the editor, select text and click the "Inline Context" button to add a note.
5. Choose a category (optional) and write your note content.

== Frequently Asked Questions ==
= Does it work with classic editor? =
No. This plugin extends the block editor's Rich Text controls.

= Can I change the styles? =
Yes. Go to Settings > Inline Context > Styling tab for comprehensive visual customization.

= How many categories can I create? =
There's no hard limit. Create as many categories as needed to organize your notes.

= Can I use any Dashicon? =
Yes. The icon picker shows 30 commonly used icons, but you can type any dashicon class name to access all 300+ icons.

= Do the open/closed icons change automatically? =
Yes. Icons automatically switch between closed and open states when users click the note.

== Screenshots ==
1. Editor popover for adding inline context with category selection
2. Category management in admin settings
3. Visual icon picker modal with keyboard navigation
4. Styling tab with comprehensive controls and live preview
5. Frontend rendering with category icon

== Changelog ==

= 2.2.0 =
**Reusable Note Management & Testing Infrastructure**

*Released: January 2025*

**✨ Note Management Features**
* **NEW**: Convert reusable notes to non-reusable with automatic synchronization
* **NEW**: Modal confirmation dialog prevents accidental conversions
* **NEW**: PopoverActions component with reusable checkbox control
* **IMPROVED**: Note edit interface with clear reusability status

**🧪 Testing & Quality**
* **NEW**: PHPUnit testing infrastructure with WordPress Test Suite integration
* **NEW**: 18 comprehensive test methods covering CPT, REST API, and sync functionality
* **NEW**: .env configuration support for secure database credentials
* **NEW**: Testing documentation (TESTING.md, tests/README.md, TESTING-SETUP.md)
* **NEW**: Interactive test setup wizard (bin/setup-tests.sh)
* **IMPROVED**: Code quality checks integrated into release workflow

**🎨 Demo & Display**
* **NEW**: Display mode switcher in demo.html for testing inline/tooltip modes
* **FIX**: Dynamic display mode detection instead of static configuration
* **FIX**: Tooltip styles properly loaded via build/style-index.css
* **IMPROVED**: Demo page now fully demonstrates all plugin capabilities

**🔧 Developer Experience**
* **NEW**: Consolidated bin/ directory for all scripts (package.sh, test setup)
* **IMPROVED**: Markdown linting for documentation consistency
* **IMPROVED**: RELEASE.md documentation with complete release process
* **IMPROVED**: Better separation of development vs production assets

**Migration Notes**
* Seamless upgrade from v2.1.0 - no breaking changes
* Existing reusable notes can now be converted to non-reusable when needed
* All tests passing (18 tests, 38 assertions)

= 2.0.0 =
**Major Release: Modular Architecture & Enhanced Code Quality**

*Released: November 12, 2025*

**🏗️ Architecture Overhaul**
* **REFACTOR**: Complete modular restructuring - main file reduced from 2,291 to 391 lines (83% reduction)
* **NEW**: Six dedicated class-based modules for optimal separation of concerns:
  - `Inline_Context_CPT` (855 lines) - Custom Post Type, metaboxes, admin UI
  - `Inline_Context_Sync` (496 lines) - Usage tracking, reusable content sync, category sync
  - `Inline_Context_Deletion` (198 lines) - Deletion protection, cleanup logic
  - `Inline_Context_REST_API` (340 lines) - REST endpoints for search and tracking
  - `Inline_Context_Frontend` (276 lines) - Noscript generation, KSES filtering, assets
  - `Inline_Context_Utils` (182 lines) - Category management, CSS variables
* **NEW**: Clean bootstrap pattern with class initialization and dependency injection
* **IMPROVED**: Function-based admin settings (678 lines) kept for optimal structure

**💎 Code Quality & Standards**
* **IMPROVED**: Full WordPress coding standards compliance (JavaScript and PHP)
* **IMPROVED**: ESLint fixes - resolved 125 formatting issues and React Hooks dependencies
* **IMPROVED**: PHPCS fixes - proper indentation, translators comments, documented patterns
* **IMPROVED**: Pre-release quality gates - automatic linting before build/package
* **IMPROVED**: Comprehensive inline documentation and phpcs:ignore explanations
* **FIX**: All critical linting errors resolved (0 errors across codebase)

**🔧 Developer Experience**
* **IMPROVED**: Testable, maintainable modular architecture
* **IMPROVED**: Clear separation of concerns for easier debugging
* **IMPROVED**: Backward compatibility wrappers for legacy function calls
* **IMPROVED**: Enhanced extensibility through clean class interfaces
* **IMPROVED**: Optimized class autoloading and initialization
* **IMPROVED**: Better code organization for future feature additions

**📦 Build & Release**
* **IMPROVED**: Streamlined build process with automatic quality checks
* **IMPROVED**: Pre-packaging linting ensures clean releases
* **IMPROVED**: Verified clean compilation with webpack 5
* **IMPROVED**: Production-ready minified assets

**🔄 Migration Notes**
* **Seamless upgrade** from v1.5.0 - no data migration required
* **Backward compatible** - all v1.x functionality preserved
* **Zero breaking changes** - existing sites upgrade without issues
* **Performance neutral** - modular code has same runtime performance

**Why version 2.0?**
This release represents a fundamental architectural improvement that sets the foundation for future development. The modular structure makes the plugin significantly easier to maintain, test, and extend while maintaining full backward compatibility.

= 1.5.0 =
* **NEW**: Custom Post Type for reusable notes - create once, use everywhere
* **NEW**: Notes Library admin page with enhanced list view and filtering
* **NEW**: Live search in editor popover to find and insert existing notes
* **NEW**: Usage tracking - see which posts use each note
* **NEW**: Custom columns in CPT list (Reusable: Yes/No, Usage Count, Used In)
* **NEW**: Filter dropdown to show only reusable notes
* **NEW**: Delete warnings when removing notes that are actively used (3 locations)
* **NEW**: Comprehensive uninstall system with content cleanup options
* **NEW**: QuillEditor component for rich text editing with keyboard navigation
* **IMPROVED**: Editor popover with tabbed interface (Create/Search modes)
* **IMPROVED**: REST API endpoints for note search and usage tracking
* **IMPROVED**: Enhanced CPT editor with category taxonomy integration
* **IMPROVED**: Cached content architecture for optimal frontend performance
* **IMPROVED**: WordPress coding standards compliance (JavaScript and PHP)
* **FIX**: All JavaScript linting errors resolved (Prettier, ESLint)
* **FIX**: All PHP linting errors in new code resolved (PHPCS)
* **FIX**: DOMNode property snake_case warnings properly handled

= 1.4.1 =
* **FIX**: Restored proper progressive enhancement - inline notes when JavaScript enabled
* **FIX**: Endnotes section now correctly hidden when JavaScript is available
* **IMPROVED**: Simplified architecture - removed unnecessary admin settings
* **IMPROVED**: Better fallback for no-JS environments with footnotes at bottom

= 1.4.0 =
* **NEW**: Full accessibility support with server-side rendered endnotes
* **NEW**: Progressive enhancement for text-based browsers and RSS feeds
* **NEW**: Notes work in both JavaScript and no-JavaScript environments
* **NEW**: Print-friendly note display
* **IMPROVED**: Better WordPress coding standards compliance
* **FIX**: Updated composer.json version number to match plugin version

= 1.3.1 =
* **IMPROVED**: Refactored PHP code to split front-end and admin code
* **IMPROVED**: Refactored CSS code for compatibilitybility with themes

= 1.3.1 =
* **IMPROVED**: Refactored edit.js for better maintainability (919 lines → 375 lines, 59% reduction)
* **IMPROVED**: Extracted utility functions into separate modules (anchor.js, text.js, clipboard.js)
* **IMPROVED**: Created custom hooks for state management (useInlineContext.js, useQuillKeyboardNav.js)
* **IMPROVED**: Split UI into reusable components (CategorySelector, QuillEditor, LinkControl, PopoverActions)
* **FIX**: Eliminated React Hook complexity warnings from ESLint
* **IMPROVED**: Updated webpack config to suppress bundle size warnings with documentation
* **IMPROVED**: Better code organization for easier testing and maintenance

= 1.3.0 =
* Added: Category management system with custom icons and colors
* Added: Visual icon picker modal with 30 curated Dashicons
* Added: Keyboard-accessible icon picker (Esc to close, Tab navigation, focus trapping)
* Added: Dual icon states (closed/open) with automatic toggling
* Added: Tabbed admin interface (Categories and Styling)
* Added: Comprehensive styling controls with helpful descriptions
* Added: Live preview with interactive note reveal
* Added: Superscript-style icon positioning
* Added: Category selector in editor popover
* Improved: Admin settings organization with clear sections
* Improved: Accessibility with ARIA labels and keyboard support
* Improved: Help text with visual examples and documentation links
* Fixed: Settings page now shows single success message
* Fixed: Preview shows accurate frontend styling with CSS variables

== Screenshots ==
1. Editor popover for adding inline context
2. Frontend rendering of an inline note

== Changelog ==

= 2.1.0 =
* **NEW**: Tooltip display mode as alternative to inline expansion
* **NEW**: General settings tab with display mode selection (inline/tooltip)
* **NEW**: Smart tooltip positioning with viewport boundary detection
* **NEW**: Automatic position flipping to prevent off-screen tooltips
* **NEW**: Close button on tooltips with proper event cleanup
* **NEW**: Full keyboard support (Space/Enter to activate, Escape to close)
* **NEW**: Automatic focus management for keyboard users
* **IMPROVED**: Organized styling sections (shared settings first, mode-specific after)
* **IMPROVED**: Admin settings reorganized into 4 tabs (General, Categories, Styling, Uninstall)
* **IMPROVED**: Click/keyboard-only activation for tooltips (no hover)
* **IMPROVED**: Comprehensive tooltip accessibility with ARIA attributes
* **REMOVED**: Non-functional live preview from Styling tab (will be reimplemented properly later)

= 2.0.1 =
* **NEW**: Taxonomy meta fields UI for managing category icons and colors
* **NEW**: Live icon preview in category add/edit forms
* **NEW**: WordPress color picker integration for category colors
* **NEW**: Custom columns in category list showing icon previews and colors
* **IMPROVED**: Frontend category icon rendering now works correctly
* **IMPROVED**: Post list display in Uninstall tab shows which posts contain inline context links
* **IMPROVED**: Uninstall cleanup query accuracy (excludes revisions, more specific matching)
* **IMPROVED**: Added backup warning for content cleanup option in Uninstall settings
* **FIX**: Category icons now display properly on frontend with correct colors
* **FIX**: Default chevron appears when no category is selected (proper CSS selector)

= 2.0.0 =
* **NEW**: Admin settings page for customizing CSS variables in WordPress admin
* **NEW**: 23 configurable styling options across Link, Note Block, and Chevron sections
* **NEW**: Color pickers for easy color customization
* **NEW**: Text inputs for dimensions, shadows, and other properties
* **NEW**: Reset to Defaults button for quick restoration
* **NEW**: Live preview section in settings page
* **IMPROVED**: CSS custom properties now properly injected to frontend
* **IMPROVED**: Clean separation of admin UI and frontend CSS output

= 1.1.5 =
* **NEW**: HTML source editor toggle in ReactQuill toolbar for direct HTML editing
* **NEW**: Visual toggle between WYSIWYG and HTML source modes with dedicated icon
* **IMPROVED**: Better icon sizing and styling for toolbar buttons
* **IMPROVED**: Smooth switching between visual and source editing modes
* **FIX**: Toggle button functionality working correctly after multiple switches

= 1.1.4 =
* **NEW**: Theme.json integration for WordPress Site Editor customization
* **NEW**: STYLING.md documentation with comprehensive theming examples
* **IMPROVED**: Conditional asset versioning (filemtime for dev, constant for production)
* **IMPROVED**: CSS custom properties now use --wp--custom--inline-context--* namespace
* **REMOVED**: Legacy --jooplaan-* CSS properties (breaking change)

= 1.2.1 =
* **FIX**: Updated composer.json version number to match plugin version

= 1.2.0 =
* **NEW**: HTML source editor toggle in ReactQuill toolbar
* **NEW**: Visual toggle between WYSIWYG and HTML source modes
* **IMPROVED**: Better icon sizing and styling for toolbar buttons
* **IMPROVED**: Smooth switching between visual and source editing
* **IMPROVED**: Automated linting and fixing before releases
* **FIX**: Toggle button functionality after multiple switches

= 1.1.4 =

= 1.1.3 =

= 1.1.2 =
* **NEW**: Copy link functionality - users can copy direct anchor links to any context note
* **NEW**: Developer filters for extensive plugin customization (11 filters available)
* **IMPROVED**: Complete namespace refactoring from trybes to jooplaan
* **IMPROVED**: Duplicate ID prevention system for copy/paste scenarios
* **FIX**: Frontend display issue - added wp-hooks dependency
* **FIX**: Demo.html compatibility with standalone usage

= 1.1.1 =
* **FIX**: Added wp-hooks dependency for frontend filters support
* **FIX**: Resolved console error preventing notes from displaying on frontend

= 1.1.0 =
* **NEW**: WordPress LinkControl integration for easy internal page/post linking
* **NEW**: Familiar WordPress interface for selecting content and adding external URLs
* **NEW**: Developer filters for customizing plugin behavior (see FILTERS.md)
* **IMPROVED**: Enhanced rich text editor with better link management capabilities

= 1.0.1 =
* **IMPROVED**: Enhanced VS Code development setup with WordPress coding standards
* **IMPROVED**: Build process optimization and error fixes
* **IMPROVED**: Better development workflow with automated formatting configuration
* **FIX**: Resolved SCSS compilation errors in build process
* **FIX**: PHP coding standards compliance issues resolved

= 1.1.2 =
* **NEW**: Copy link functionality - users can copy direct anchor links to any context note
* **IMPROVED**: HTML validity with semantic button elements instead of anchor tags
* **IMPROVED**: Duplicate ID prevention system for copy/paste scenarios
* **FIX**: ESLint compatibility issues with Node.js v24
* **FIX**: Automatic unique ID generation when duplicates are detected

= 1.0.0 =
* **NEW**: Unique anchor IDs for every context note with direct URL linking
* **NEW**: Auto-opening notes when accessed via URL hash (#context-note-xxx)
* **NEW**: Subtle design - context links appear as regular text with icon indicator only
* **NEW**: Hover/focus states change text color to primary for clear interaction feedback
* **NEW**: Smart link behavior - internal links same tab, external links new tab with security
* **NEW**: ReactQuill rich text editor for enhanced note authoring experience
* **NEW**: DOMPurify integration for robust XSS protection on frontend rendering
* **IMPROVED**: Full WordPress coding standards compliance (PHP and JavaScript)
* **IMPROVED**: Comprehensive quality assurance with automated linting pipeline
* **IMPROVED**: Enhanced accessibility with better ARIA attributes and focus management
* **REMOVED**: Legacy support - v1.0 requires anchor IDs for all context notes

== Upgrade Notice ==
= 2.0.0 =
Major architectural overhaul with modular class-based structure (83% main file reduction). Full WordPress coding standards compliance. Seamless upgrade from v1.x with zero breaking changes.

= 1.0.0 =
Major release with anchor links, subtle design, and enhanced security. All context notes now get unique anchor IDs for direct linking.
