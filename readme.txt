=== Inline Context ===
Contributors: joop
Tags: inline, footnote, tooltip, reveal, context
Requires at least: 6.0
Tested up to: 6.6
Requires PHP: 7.4
Stable tag: 1.2.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Add inline expandable context notes with direct anchor linking. Features subtle design for optimal reading flow and smart link behavior.

== Description ==
Inline Context lets you enrich content with expandable context notes that maintain optimal reading flow. Context links appear as regular text with only a subtle icon indicator, changing to primary color on hover. Each note gets a unique anchor ID for direct linking and sharing.

= Key Features =
* **Subtle Design**: Context links appear as regular text with minimal visual disruption
* **Direct Anchor Links**: Every note gets a unique URL anchor for easy sharing (#context-note-xxx)
* **Auto-Opening**: Notes automatically open when accessed via direct link
* **Smart Link Behavior**: Internal links stay in same tab, external links open in new tab with security
* **Rich Text Support**: Notes support bold, italic, links, lists with ReactQuill editor
* **Security First**: Content sanitized with DOMPurify before frontend display
* **Accessibility**: Full ARIA support with proper focus management
* **WordPress Integration**: Clean toolbar button in Rich Text format controls

= Security =
Inline Context renders note content on the frontend. Content authored in the editor is sanitized before display. We recommend using the latest WordPress for improved security and KSES handling.

= Internationalization =
This plugin is translation-ready. POT files can be generated from source with `npm run pot` and placed under the `languages/` directory.

== Installation ==
1. Upload the plugin files to the `/wp-content/plugins/inline-context` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. In the editor, select text and click the "Inline Context" button to add a note.

== Frequently Asked Questions ==
= Does it work with classic editor? =
No. This plugin extends the block editor's Rich Text controls.

= Can I change the styles? =
Yes. The frontend uses CSS custom properties that you can override in your theme.

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

= 1.1.5 =
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
