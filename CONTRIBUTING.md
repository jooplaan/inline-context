# Contributing to Inline Context

Thanks for taking the time to contribute. This document covers the practical workflow for changes — what to install, what to run before opening a PR, and the conventions the project follows. For an architecture overview, read [AGENTS.md](AGENTS.md). For the release/cut-a-version process, read [RELEASE.md](RELEASE.md).

## Getting started

```bash
git clone https://github.com/jooplaan/inline-context.git
cd inline-context
nvm use            # honors .nvmrc
npm install
composer install   # PHPCS + PHPUnit
npm run start      # webpack dev mode with hot reload
```

To work against a local WordPress install, symlink the plugin folder into `wp-content/plugins/`:

```bash
ln -s "$(pwd)" /path/to/wp-content/plugins/inline-context
```

The plugin requires WordPress 6.0+ and PHP 7.4+. The Abilities API integration activates automatically on WordPress 6.9+; image-in-note support uses the WordPress Media Library; the rest of the plugin works on 6.0+ unchanged.

## Project layout

See [AGENTS.md](AGENTS.md) — it lists every source file with a one-line description, the modular PHP class layout under `includes/`, the Jest + PHPUnit test layout, and the build/tooling files.

## Development workflow

```bash
npm run start              # dev build with watcher
npm run build              # production build (also runs in package/release)
npm run lint:fix           # auto-fix JS, PHP, and Markdown
npm run test:unit          # Jest unit tests (110 tests)
npm run test               # full quality gate: lint + unit tests
```

PHP integration tests run separately:

```bash
bin/install-wp-tests.sh wordpress_test root '' localhost latest   # one-time
vendor/bin/phpunit
```

## Code style

- **PHP** — WordPress Coding Standards, enforced by PHPCS. Auto-fix with `npm run lint:php:fix` (or `composer run lint:fix`). Files under `includes/` follow the `class-inline-context-{slug}.php` naming convention and use the `Inline_Context_` class prefix.
- **JavaScript** — `@wordpress/scripts` ESLint config + Prettier. Auto-fix with `npm run lint:js:fix`. Tests are excluded from lint via `.eslintignore`.
- **Markdown** — `markdownlint-cli2`, auto-fix with `npm run lint:md:fix`.
- **i18n** — All user-facing strings use the `'inline-context'` text domain (`__( 'Text', 'inline-context' )`). When adding strings, regenerate the POT file: `wp i18n make-pot . languages/inline-context.pot --domain=inline-context`.

## Testing

Run the relevant suite for what you touched, and the full gate before opening a PR:

- Editing JS in `src/` → `npm run test:unit` (or `:watch` while iterating)
- Editing PHP under `includes/` or `inline-context.php` → `vendor/bin/phpunit`
- Editing rich-text format / popover behavior → also smoke-test in a real block editor; `demo.html` covers the frontend rendering only.
- Editing the Abilities API (`includes/class-inline-context-abilities.php`) → run `tests/test-abilities.php`.

New features should land with at least one Jest test (utils/components) or PHPUnit test (REST/CPT/sync). Bug fixes should add a regression test that fails before the fix and passes after.

## Commit messages

- Use the **imperative mood** ("Add X", not "Added X" or "Adds X").
- Keep the subject line under ~70 characters; put detail in the body, separated by a blank line.
- Use a conventional prefix where it fits: `feat:`, `fix:`, `docs:`, `refactor:`, `chore:`, `test:`. Prefixes are not required for every commit but help when scanning history.

Example:

```text
fix: Prevent popover from closing on Media Library click

The Media Library frame is a body-level modal, so its clicks register
as "outside the popover" and trigger the close handler. Flip a guard
ref while the frame is open and release it 200ms after close to also
cover the focus-restore click that fires immediately after teardown.
```

## Branching model

The project follows a Git Flow variant:

- `main` — production-ready, tagged releases only
- `develop` — integration branch for the next release
- `feature/<short-name>` — new features, branched from `develop`
- `hotfix/<version>` — urgent fixes, branched from `main`
- `release/<version>` — release-prep branch, merged to both `main` and `develop`

PRs should target `develop` for normal work and `main` only for hotfixes.

## Pull requests

- One logical change per PR. Refactors should be separate from feature work.
- Ensure `npm run test` passes (lint + unit) before opening the PR.
- Update `ROADMAP.md` if a roadmap item is now done; update `changelog.txt` and `readme.txt` if user-visible behavior changed.
- Document new public WordPress filters in `FILTERS.md`; new abilities in `ABILITIES-API.md`.
- For UI changes, include a screenshot or short clip in the PR body.

New to GitHub pull requests? See GitHub's guide: [Creating a pull request](https://docs.github.com/en/pull-requests/collaborating-with-pull-requests/proposing-changes-to-your-work-with-pull-requests/creating-a-pull-request).

## Releasing

Maintainers only. The full process — version bumping (across `inline-context.php`, `package.json`, `readme.txt`, `composer.json`), POT regeneration, manual test checklist, branch merges, and tag — is documented in [RELEASE.md](RELEASE.md).

## Reporting bugs / requesting features

Open an issue at <https://github.com/jooplaan/inline-context/issues>. For bugs, include:

- WordPress version, PHP version, browser
- Steps to reproduce
- Expected vs. actual behavior
- Console / PHP error output if any

For features, describe the use case before the proposed implementation — it's usually the use case that determines the right shape of the feature.
