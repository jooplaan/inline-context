# Inline Context

Add small “click to reveal” notes inline with your text. Perfect for short explanations, definitions, and asides that shouldn’t break the reading flow.

## What it does

- Lets you attach a brief note to any piece of text in the block editor.
- Readers click the highlighted text to show the note inline, and click again to hide it.
- Keeps pages clean and easy to scan while still offering helpful context.

## How to use

1. In the editor, select the text you want to explain.
2. Click the “Inline Context” button in the formatting toolbar.
3. Type your note and save.
4. View your page and click the highlighted text to reveal/hide the note.

## Why it’s helpful

- Clean reading experience — extra info appears only when needed
- Simple controls in the block editor toolbar
- Theme‑friendly styles (easy to adjust with CSS variables)
- Accessible by default (proper ARIA attributes)

## Accessibility

Notes include `aria-expanded`, `aria-controls`, and `aria-describedby` on the trigger and `role="note"` on the revealed content.

## Privacy

This plugin does not collect data, set cookies, or connect to external services.

## Requirements

- WordPress 6.0 or newer
- PHP 7.4 or newer

## Development

### Getting started

1. Clone the repository
2. Install dependencies:

   ```bash
   npm install
   ```

3. Start development mode (watches files and rebuilds on change):

   ```bash
   npm run start
   ```

4. Build for production:

   ```bash
   npm run build
   ```

### Available scripts

- `npm run start` — Start development mode with hot reload
- `npm run build` — Build production assets
- `npm run lint:js` — Lint JavaScript files
- `npm run pot` — Generate translation template file
- `npm run package` — Create a distributable zip (requires build first)
- `npm run release` — Build and package in one step

### Project structure

- `src/` — Source files (JavaScript, SCSS)
- `build/` — Compiled assets (committed to repo for WordPress.org)
- `languages/` — Translation files
- `inline-context.php` — Main plugin bootstrap file
- `readme.txt` — WordPress.org readme
- `scripts/` — Build and packaging scripts

## Releasing a new version

1. Update version numbers in:
   - `inline-context.php` (plugin header)
   - `readme.txt` (Stable tag and Changelog)
   - `package.json` (version field)

2. Build and package:

   ```bash
   npm run release
   ```

3. The distributable zip will be at `dist/inline-context.zip`

4. Test the zip by installing it on a test WordPress site

5. Commit changes and tag the release:

   ```bash
   git add .
   git commit -m "Release version X.Y.Z"
   git tag vX.Y.Z
   git push origin main --tags
   ```

6. Upload `dist/inline-context.zip` to WordPress.org SVN (or use the zip for manual distribution)

## Support

Please use the support forum on WordPress.org once the plugin is published. We'll do our best to help.

## License

GPL v2 or later.
