=== Inline Context ===
Contributors: joop
Tags: inline, footnote, tooltip, reveal, context
Requires at least: 6.0
Tested up to: 6.6
Requires PHP: 7.4
Stable tag: 1.3.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Add inline expandable context notes with direct anchor linking. Features category management, customizable icons, and comprehensive styling controls.

== Description ==
Inline Context lets you enrich content with expandable context notes that maintain optimal reading flow. Organize notes with custom categories, each with distinct icons and colors. Full styling control through tabbed admin interface.

= Key Features =
* **Category Management**: Organize notes with custom categories (Internal Article, External Article, Definition, Tip, etc.)
* **Custom Icons**: Choose from 30 curated Dashicons or use any of 300+ available icons
* **Icon States**: Different icons for closed/open states provide visual feedback
* **Tabbed Settings**: Clean admin interface with Categories and Styling tabs
* **Visual Icon Picker**: Accessible modal with keyboard navigation (Esc to close, Tab to navigate)
* **Direct Anchor Links**: Every note gets a unique URL anchor for easy sharing (#context-note-xxx)
* **Auto-Opening**: Notes automatically open when accessed via direct link
* **Smart Link Behavior**: Internal links stay in same tab, external links open in new tab with security
* **Rich Text Support**: Notes support bold, italic, links, lists with ReactQuill editor
* **Comprehensive Styling**: Control colors, spacing, borders, shadows for links and notes
* **Live Preview**: Interactive preview shows exactly how notes will appear
* **Security First**: Content sanitized with DOMPurify before frontend display
* **Accessibility**: Full ARIA support, keyboard navigation, and focus management
* **WordPress Integration**: Clean toolbar button in Rich Text format controls

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
== Changelog ==

= 1.2.0 =
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
= 1.0.0 =
Major release with anchor links, subtle design, and enhanced security. All context notes now get unique anchor IDs for direct linking.
