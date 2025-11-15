# Release Process

This document outlines the steps to create a new release of the Inline Context plugin.

## Pre-Release Checklist

### 1. Version Updates

Update version numbers in the following files:

- [ ] `inline-context.php` - Plugin header version
- [ ] `package.json` - npm version
- [ ] `readme.txt` - Stable tag
- [ ] `composer.json` - Version (if present)

### 2. Changelog & Documentation

- [ ] Add changelog entry to `readme.txt` with new features, improvements, and bug fixes
- [ ] Update `ROADMAP.md` if needed (mark completed features)
- [ ] Review and update `README.md` if needed
- [ ] Review and update `ROADMAP.md` if needed
- [ ] Update any relevant documentation files

### 3. Code Quality & Testing

Run all quality checks:

```bash
# Fix linting issues
npm run lint:fix

# Fix markdown linting
npm run lint:md:fix

# Run PHP unit tests
composer test:unit

# Build production assets
npm run build
```

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

## WordPress.org Distribution (Optional)

If distributing via WordPress.org:

```bash
# Check out SVN repository
svn co https://plugins.svn.wordpress.org/inline-context

# Copy files to trunk
cp -r build/* inline-context/trunk/
cp inline-context.php admin-settings.php uninstall.php readme.txt inline-context/trunk/
cp -r includes/ inline-context/trunk/
cp -r languages/ inline-context/trunk/

# Create new tag
svn cp trunk tags/X.X.X

# Commit changes
svn ci -m "Release X.X.X"
```

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

**Linting errors:**

- Run `npm run lint:fix` for auto-fixable issues
- Run `vendor/bin/phpcbf --standard=WordPress *.php` for PHP auto-fixes
- Manually fix remaining issues

**Package excludes wrong files:**

- Check `.distignore` for exclusion patterns
- Verify `bin/package.sh` script logic
