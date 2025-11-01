=== Inline Context ===
Contributors: Trybes
Tags: inline, footnote, tooltip, reveal, context
Requires at least: 6.0
Tested up to: 6.6
Requires PHP: 7.4
Stable tag: 1.0.0
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
