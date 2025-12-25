# Claude AI Assistant Instructions

This document provides specific guidance for Claude AI when working on the Inline Context WordPress plugin.

## Project Context

For comprehensive architecture, development patterns, and technical details, **always refer to [.github/copilot-instructions.md](.github/copilot-instructions.md)** first.

That file contains:
- Complete architecture overview
- Development patterns and best practices
- Build & development workflow
- File organization
- WordPress integration points
- Quality assurance standards

## Git Commit Guidelines

**IMPORTANT**: When creating git commits, follow these rules:

### Do NOT Mention AI Assistant Names

- ❌ **Never** include "Claude" in commit messages
- ❌ **Never** include "AI" or "AI-assisted" in commit messages
- ❌ **Never** reference that the code was written with AI assistance

### Commit Message Format

Write commit messages as if you are the developer:

```
✅ Good:
Add keyboard shortcuts for inline context management
Fix case-sensitivity issue with shortcut detection
Update version to 2.5.0

❌ Bad:
Add keyboard shortcuts (Claude-assisted)
Fix case-sensitivity issue with help from AI
Update version to 2.5.0 - implemented by Claude
```

### Commit Message Best Practices

1. **Use imperative mood**: "Add feature" not "Added feature" or "Adds feature"
2. **Be specific**: Describe what changed and why
3. **Keep it concise**: First line should be 50 characters or less
4. **Add details in body**: If needed, add detailed explanation after blank line
5. **Follow conventional commits**: Use prefixes like `feat:`, `fix:`, `docs:`, `refactor:` when appropriate

### Standard Commit Footer

Always include this footer in commit messages:

```
🤖 Generated with [Claude Code](https://claude.com/claude-code)

Co-Authored-By: Claude Sonnet 4.5 <noreply@anthropic.com>
```

This acknowledges AI assistance in the commit metadata without mentioning it in the actual commit message.

## Example Commit

```
feat: Add keyboard shortcuts for inline context editor

Implement Cmd+Shift+I and Cmd+Shift+K shortcuts to improve
workflow for power users creating content-heavy posts.

- Cmd+Shift+I: Insert inline context when text is selected
- Cmd+Shift+K: Edit existing context at cursor position
- Add useEditorKeyboardShortcuts hook for editor-level shortcuts
- Add format navigation utilities for cursor detection
- Update version to 2.5.0

🤖 Generated with [Claude Code](https://claude.com/claude-code)

Co-Authored-By: Claude Sonnet 4.5 <noreply@anthropic.com>
```

## Additional Guidelines

### Code Quality

- Always run `npm run lint:fix` before committing
- Follow WordPress coding standards (enforced by PHPCS and ESLint)
- Ensure all tests pass before creating a commit

### Documentation

- Update ROADMAP.md when completing features
- Keep inline comments clear and concise
- Update relevant documentation files when changing architecture

### Version Management

When bumping versions, update all three files consistently:
1. `package.json` - version field
2. `composer.json` - version field
3. `inline-context.php` - Version header and INLINE_CONTEXT_VERSION constant

## Quick Reference

- **Architecture docs**: [.github/copilot-instructions.md](.github/copilot-instructions.md)
- **Build commands**: `npm run start` (dev) or `npm run build` (prod)
- **Quality checks**: `npm run lint:fix && npm run test`
- **Release process**: See [RELEASE.md](RELEASE.md) if it exists
