# Release Process

This document outlines the steps to create a new release of the Inline Context plugin.

## Pre-Release Checklist

### 1. Version Updates

Update version numbers in the following files:

- [ ] `inline-context.php` - Plugin header version
- [ ] `package.json` - npm version
- [ ] `readme.txt` - Stable tag
- [ ] `composer.json` - Version

### 2. Changelog & Documentation

- [ ] Add changelog entry to `readme.txt` with new features, improvements, and bug fixes
- [ ] Update `ROADMAP.md` (mark completed features)
- [ ] Review and update `changelog.txt
- [ ] Review and update `README.md`
- [ ] Review and update `ROADMAP.md``
- [ ] Update any relevant documentation files

### 3. Code Quality & Testing

Run all quality checks:

```bash
# Fix JavaScript linting (targeted to avoid node_modules)
npx wp-scripts lint-js src/ --fix

# Fix PHP linting
npm run lint:php:fix

# Fix markdown linting
npm run lint:md:fix

# Run JavaScript unit tests
npm run test:unit

# Run composer lint WordPress code standards
composer run lint

# Fix WordPress code errors automatic where possible
composer run lint:fix

# Fix rest of errors manually

# Run PHP unit tests
composer test:unit

# Update the POT file
wp i18n make-pot . languages/inline-context.pot --domain=inline-context

# Build production assets
npm run build

```

After all the tests are succesfull and the build is done, let the human do the manual testing.

Manual testing:

- [ ] Test in actual WordPress environment (not just demo.html)
- [ ] Test both Inline and Tooltip display modes
- [ ] Test new features added in this version
- [ ] Test on different browsers (Chrome, Firefox, Safari)
- [ ] Test accessibility with keyboard navigation
- [ ] Verify no console errors

### 4. Build & Package

```bash
# This automatically runs lint:fix, build, and package
npm run release
```

Verify:

- [ ] `dist/inline-context-X.X.X.zip` is created
- [ ] Zip contains only necessary files (excludes tests/, .env, node_modules/, etc.)
- [ ] Zip extracts to `inline-context/` directory
- [ ] Test installation from zip file in WordPress

## Release Process (Git Flow)

### Step 1: Finalize Feature Branch

```bash
# Ensure all changes are committed
git status

# Ensure you're on the feature branch
git checkout feature/version-X.X.X
```

### Step 2: Merge to Develop

```bash
# Switch to develop branch
git checkout develop

# Merge feature branch
git merge feature/version-X.X.X

# Push to remote
git push origin develop
```

### Step 3: Create Release Branch

```bash
# Create release branch from develop
git checkout -b release/X.X.X develop

# Push release branch
git push origin release/X.X.X
```

### Step 4: Merge to Main and Tag

```bash
# Switch to main branch
git checkout main

# Merge release branch
git merge release/X.X.X

# Create annotated tag
git tag -a X.X.X -m "Release version X.X.X"

# Push main and tags
git push origin main
git push origin X.X.X
```

### Step 5: Merge Back to Develop

```bash
# Switch to develop
git checkout develop

# Merge main back to develop (to include any fixes made during release)
git merge main

# Push develop
git push origin develop
```

### Step 6: Clean Up

```bash
# Delete local release branch
git branch -d release/X.X.X

# Delete remote release branch (optional)
git push origin --delete release/X.X.X

# Delete local feature branch
git branch -d feature/version-X.X.X

# Delete remote feature branch
git push origin --delete feature/version-X.X.X
```

## GitHub Release

1. Go to GitHub repository → Releases → Draft a new release
2. Choose tag: `X.X.X`
3. Release title: `Version X.X.X`
4. Description: Copy changelog from `readme.txt`
5. Attach `dist/inline-context-X.X.X.zip`
6. Publish release

## WordPress.org SVN Deployment

After completing the Git release process, deploy to WordPress.org SVN repository.

### SVN Repository Location

```text
Local: ~/Developer/wp/svn/inline-context
Remote: https://plugins.svn.wordpress.org/inline-context
```

### Step 1: Update Local SVN Repository

```bash
# Navigate to SVN repository
cd ~/Developer/wp/svn/inline-context

# Update to latest from WordPress.org
svn up
```

### Step 2: Copy Files to Trunk

```bash
# From the plugin source directory, copy all distribution files to SVN trunk
# Using rsync to handle deletions and updates properly

# Copy main PHP files
cp /Users/joop/Developer/wp/plugins/inline-context/inline-context.php trunk/
cp /Users/joop/Developer/wp/plugins/inline-context/admin-settings.php trunk/
cp /Users/joop/Developer/wp/plugins/inline-context/uninstall.php trunk/
cp /Users/joop/Developer/wp/plugins/inline-context/readme.txt trunk/
cp /Users/joop/Developer/wp/plugins/inline-context/changelog.txt trunk/

# Copy build directory
cp -r /Users/joop/Developer/wp/plugins/inline-context/build/* trunk/build/

# Copy includes directory
cp -r /Users/joop/Developer/wp/plugins/inline-context/includes/* trunk/includes/

# Copy languages directory
cp -r /Users/joop/Developer/wp/plugins/inline-context/languages/* trunk/languages/
```

### Step 3: Verify Files Before Committing

```bash
# Check SVN status for changes
svn status

# Verify version numbers match
grep "Version:" trunk/inline-context.php
grep "Stable tag:" trunk/readme.txt

# Compare file count with packaged zip (should be 27 files)
find trunk -type f | wc -l
```

### Step 4: Add/Remove Changed Files

```bash
# Add any new files to SVN
svn add trunk/* --force 2>/dev/null || true

# Remove deleted files (if any)
svn status | grep '^!' | awk '{print $2}' | xargs -I {} svn rm {}
```

### Step 5: Commit Trunk

```bash
# Commit trunk changes
svn ci -m "Update trunk to version X.X.X"
```

### Step 6: Create SVN Tag

```bash
# Create tag from trunk
svn cp trunk tags/X.X.X

# Commit the tag
svn ci -m "Release X.X.X"
```

### SVN Verification

After committing, verify the release:

- [ ] Check <https://wordpress.org/plugins/inline-context/> shows new version
- [ ] Verify "Stable tag" matches in readme.txt
- [ ] Test update notification in WordPress admin (may take 5-15 minutes to propagate)

## Post-Release Verification

- [ ] Verify version numbers display correctly in WordPress admin
- [ ] Test installation from WordPress.org (if applicable)
- [ ] Test installation from GitHub release zip
- [ ] Verify changelog displays correctly
- [ ] Check that update notifications work (if updating from previous version)

## Rollback Procedure

If issues are discovered after release:

```bash
# Revert tag on main
git checkout main
git revert <commit-hash>
git push origin main

# Create hotfix tag
git tag -a X.X.X-hotfix.1 -m "Hotfix for X.X.X"
git push origin X.X.X-hotfix.1
```

## Version Numbering

Follow [Semantic Versioning](https://semver.org/):

- **Major (X.0.0)**: Breaking changes, major rewrites
- **Minor (0.X.0)**: New features, backwards-compatible
- **Patch (0.0.X)**: Bug fixes, minor improvements

## Common Commands

```bash
# Check current version
grep "Version:" inline-context.php

# List all tags
git tag -l

# View commits since last tag
git log $(git describe --tags --abbrev=0)..HEAD --oneline

# Create changelog from commits
git log $(git describe --tags --abbrev=0)..HEAD --pretty=format:"- %s"
```

## Troubleshooting

**Build fails:**

- Run `npm install` to ensure dependencies are up to date
- Run `composer install` for PHP dependencies
- Check Node.js version (requires 14.x or higher)

**Tests fail:**

- Ensure MySQL is running for PHP unit tests
- Check `.env` file configuration
- Run `composer test:unit -- --verbose` for detailed output
- For JavaScript tests: Run `npm run test:unit -- --verbose` for detailed output
- Check that all WordPress package mocks are properly configured in `jest.setup.js`

**Linting errors:**

- Run `npm run lint:fix` for auto-fixable issues
- Run `vendor/bin/phpcbf --standard=WordPress *.php` for PHP auto-fixes
- Manually fix remaining issues

**Package excludes wrong files:**

- Check `.distignore` for exclusion patterns
- Verify `bin/package.sh` script logic
