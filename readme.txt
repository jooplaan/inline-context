=== Inline Context ===
Contributors: Trybes
Tags: inline, footnote, tooltip, reveal, context
Requires at least: 6.0
Tested up to: 6.6
Requires PHP: 7.4
Stable tag: 0.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Add inline expandable context to selected text in the block editor. Click to reveal, click again to hide.

== Description ==
Inline Context lets you enrich content with inline expandable notes. Authors can attach a short note to any piece of text in the block editor; readers click to reveal it inline and click again to hide it. Notes support basic rich text (bold, italic, links, lists) and are accessible with proper ARIA attributes.

= Features =
* Adds a toolbar button to the block editor's Rich Text controls
* Store inline context alongside your content
* Frontend click-to-reveal behaviour with mutual exclusion
* Accessible: aria-expanded, aria-controls, aria-describedby, and role="note"
* Theme-friendly styles using CSS variables

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
= 0.1.0 =
* Initial release

== Upgrade Notice ==
= 0.1.0 =
Initial release.
