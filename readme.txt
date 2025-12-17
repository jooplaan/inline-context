=== Inline Context ===
Contributors: joop
Tags: inline, footnote, tooltip, context, annotations
Requires at least: 6.0
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 2.3.8
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Add inline expandable notes or tooltips to provide context, definitions, and references without disrupting the reading flow.

== Description ==

**Inline Context** is a powerful Block Editor enhancement that lets you create inline expandable notes or clean tooltip-style popovers anywhere in your content. It is ideal for **content-rich websites**, including editorial platforms, research sites, online magazines, documentation hubs, and educational blogs that rely on clear explanation without breaking the reader’s focus.

Instead of sending readers to glossary pages or external links, Inline Context allows you to provide definitions, references, clarifications, and annotations *in place* — keeping readers engaged and your content structured.

Notes can be **reusable**, categorized, styled, centrally managed, and automatically updated everywhere they appear.

[Check the live preview](https://wordpress.org/plugins/inline-context/?preview=1)

= Why this is valuable for content-heavy websites =

Websites with substantial text often need:

* definitions and terminology
* source references
* background information
* contextual inline explanations
* mini footnotes without scrolling
* inline callouts, tips, or warnings

Inline Context delivers all of this with a frictionless, accessible user experience. It helps readers stay focused, reduces navigation fatigue, and improves knowledge retention — especially in long articles or research-based content.

= How it works =

1. Highlight text in the Block Editor.
2. Click **Inline Context**.
3. Enter your note content (rich text supported).
4. Optionally assign a category with custom icon & color.
5. Publish — your note appears inline or as a tooltip, depending on settings.

You can also create **reusable** notes from a dedicated Custom Post Type. Updating a reusable note updates all instances site-wide.

== Key Features ==

= Display modes =
* Inline expansion (reveals a small content panel)
* Tooltip popovers (floating contextual bubbles)
* Smart tooltip positioning to avoid off-screen display
* Direct anchor links (`#context-note-xxx`) for deep linking
* Auto-open on page load when accessed via link

= Editor productivity =
* Reusable notes with global updates
* Notes Library with usage tracking (shows where each note is used)
* Quick Search inside the editor to insert existing notes
* Rich text support via ReactQuill (bold, italic, lists, links)
* Clean, integrated Rich Text toolbar button

= Categories & icons =
* Create unlimited categories (Definition, Reference, External Article, Tip, Warning, etc.)
* Choose from curated Dashicons or any of 300+ icons
* Separate icons for open and closed states

= Styling & customization =
Full styling control from **Settings → Inline Context**:

* Link colors, hover, and focus states
* Note padding, spacing, borders, backgrounds, shadows
* Tooltip appearance
* Chevron/indicator styling
* Live interactive preview of all style changes

= Accessibility & security =
* ARIA support, focus lock, Escape key behavior
* Keyboard-navigable for both link and note
* DOMPurify sanitization of note content

== Internationalization ==

Inline Context is fully translation-ready.

== Examples & inspiration ==

The idea for this plugin originated from a project with Renée Kool — a visual artist working in public art, film, and emerging media. She wanted to create a website where a single link could reveal additional content containing multiple related links. We looked at the Dutch journalism platform De Correspondent, which use subtle inline notes to provide context without interrupting the flow of reading. You can see examples of their inline notes in this article: [Hoe Nederland kampioen deeltijdwerken werd](https://decorrespondent.nl/15887/hoe-nederland-kampioen-deeltijdwerken-werd/9053b712-3591-0002-29b3-8c7b69eae0c3)

== Source code ==

Inline Context uses `@wordpress/scripts` with webpack and npm to build assets.

Full source (including uncompiled JS and CSS) is available at:
[https://github.com/jooplaan/inline-context](https://github.com/jooplaan/inline-context)

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/inline-context`, or install via the Plugins screen.
2. Activate the plugin.
3. Configure categories and styling under **Settings → Inline Context**.
4. In the Block Editor, select text and click **Inline Context**.
5. Add your note content and choose a category (optional).

== Frequently Asked Questions ==

= Does this work with the Classic Editor? =
No. Inline Context is built specifically for the WordPress Block Editor (Gutenberg).

= Can I change the styles? =
Yes. Extensive visual customization options are available under **Settings → Inline Context → Styling**.

= Is there a limit to the number of categories? =
No. Create as many categories as your content structure requires.

= Can I use any Dashicon? =
Yes. You can type any Dashicon class name to use all 300+ icons.

== Screenshots ==

1. Editor popover for adding inline context with category selection
2. Modal window for writing an inline context note
3. Search interface for inserting reusable notes
4. Inline context note on the frontend (default expanded mode)
5. Tooltip version of the inline note on the frontend
6. Notes Library in the admin area showing usage count and linked posts
