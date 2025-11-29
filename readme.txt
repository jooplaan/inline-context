=== Inline Context ===
Contributors: joop
Tags: inline, footnote, tooltip, reveal, context
Requires at least: 6.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 2.3.8
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Add inline expandable context notes with direct anchor linking. Optionally show the notes as tooltip popover.

== Description ==
Inline Context lets you enrich content with expandable context notes that maintain optimal reading flow. Alternatively, the context notes can be displayed as tooltips. The context notes can be re-usable. When a reusable inline context is updated, all instances where the note is used will be updated.

Create reusable notes via Custom Post Type, organize with categories (each with distinct icons and colors), and control all styling through a tabbed admin interface.

This plugin originated from a project with Renée Kool — a visual artist working in public art, film, and emerging media. She wanted to create a website where a single link could reveal additional content containing multiple related links.

As inspiration, we looked at the Dutch journalism platform De Correspondent, which uses inline contextual notes: small linked text fragments with an icon. When activated, they reveal an extra HTML element containing supplementary information. These contextual notes can:

- provide definitions
- offer additional context before the reader follows links in the main text
- stay out of the way to keep the article readable

You can see examples of their inline notes in this article:
[Hoe Nederland kampioen deeltijdwerken werd](https://decorrespondent.nl/15887/hoe-nederland-kampioen-deeltijdwerken-werd/9053b712-3591-0002-29b3-8c7b69eae0c3)

= Source Code =

This plugin uses build tools (npm and webpack via @wordpress/scripts) to compile JavaScript and CSS.

**Source code repository:** https://github.com/jooplaan/inline-context

The complete source code, including all uncompiled JavaScript and CSS files, is available in the `/src` directory of the GitHub repository. You can review, build, and modify the source code following the instructions in the repository's README.md.

= Key Features Inline Context plugin =

* **Display Modes**: Choose between inline expansion or floating tooltips
* **Smart Tooltips**: Automatic positioning that prevents off-screen display
* **Reusable Notes**: Create notes as Custom Post Type entries and reuse them across multiple posts
* **Notes Library**: Centralized management of all notes with usage tracking
* **Quick Search**: Find and insert existing notes instantly from the editor
* **Category Management**: Organize notes with custom categories (Internal Article, External Article, Definition, Tip, etc.)
* **Custom Icons**: Choose from 30 curated Dashicons or use any of 300+ available icons
* **Icon States**: Different icons for closed/open states provide visual feedback
* **Direct Anchor Links**: Every note gets a unique URL anchor for easy sharing (#context-note-xxx)
* **Auto-Opening**: Notes automatically open when accessed via direct link
* **Rich Text Support**: Notes support bold, italic, links, lists with ReactQuill editor
* **Comprehensive Styling**: Control colors, spacing, borders, shadows for links and notes
* **Security First**: Content sanitized with DOMPurify before frontend display
* **Full Accessibility**: ARIA support, keyboard navigation, focus management, Escape key support
* **WordPress Integration**: Clean toolbar button in Rich Text format controls

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

= Can I use any Dashicon for the categories? =
Yes. You can type any dashicon class name to access all 300+ icons.

== Screenshots ==
1. Editor popover for adding inline context with category selection
2. Pop up in editor to add a inline context note to content
3. Search existing re-usable notes in the editor pop-up
4. The inline context note displayed on website, default view
5. The inline context note displayed as tooltip on website
6. List of inline context notes in WordPress admin, showing usage count and where it is used in content
