#!/usr/bin/env bash
set -euo pipefail

# Package the plugin into a zip suitable for uploading to WordPress.org.
# This script zips the working tree (not just committed files) and excludes
# development-only files. Result is written to dist/inline-context.zip.

PLUGIN_SLUG="inline-context"
ROOT_DIR="$(cd "$(dirname "$0")/.." && pwd)"
DIST_DIR="$ROOT_DIR/dist"
ZIP_PATH="$DIST_DIR/$PLUGIN_SLUG.zip"

mkdir -p "$DIST_DIR"
cd "$ROOT_DIR"

# Ensure build artifacts exist (optional; comment out if you prefer running build outside)
# npm run build

# Mac/BSD zip supports -x patterns; use quotes to avoid shell globbing.
# Keep: PHP, readme.txt, build/, languages/
# Exclude: dotfiles, node_modules, source, tooling, CI, editor configs, lockfiles, dist, scripts

zip -rq "$ZIP_PATH" . \
  -x ".git/*" \
  -x ".github/*" \
  -x ".vscode/*" \
  -x "node_modules/*" \
  -x "dist/*" \
  -x "scripts/*" \
  -x "src/*" \
  -x "*.map" \
  -x "*.log" \
  -x "package.json" \
  -x "package-lock.json" \
  -x "pnpm-lock.yaml" \
  -x "yarn.lock" \
  -x "webpack.config.js" \
  -x "README.md" \
  -x ".gitignore" \
  -x ".gitattributes" \
  -x ".babelrc" \
  -x ".nvmrc" \
  -x "__MACOSX/*" \
  -x ".env*" \
  -x ".DS_Store" \
  -x "demo.html" \
  -x "composer.json" \
  -x "languages/.gitkeep" \
  -x ".editorconfig" \
  -x ".prettierrc.json" \
  -x ".eslintignore" \
  -x "coverage/*" \
  -x "bin/*" \
  -x "vendor/*" \
  -x ".markdownlint.json" \
  -x "jest-mocks/*" \
  -x "test/*" \
  -x "jest.config.js" \
  -x "jest.setup.js" \
  -x "phpunit.xml" \
  -x "RELEASE.md" \
  -x "ROADMAP.md" \
  -x "TESTING-SETUP.md" \
  -x "TESTING.md" \
  -x ".phpunit.result.cache" \
  -x "tests/*" \
  -x "composer.lock"


# Validate main plugin file exists in the archive context
if [[ ! -f "inline-context.php" ]]; then
  echo "Warning: inline-context.php not found at repository root. Check plugin structure." >&2
fi

echo "Packaged: $ZIP_PATH"
