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

Write commit messages as if you are the developer. **NEVER mention Claude, AI, or any AI assistance in the commit message itself.**

```
✅ Good Commit Messages:
Add keyboard shortcuts for inline context management
Fix case-sensitivity issue with shortcut detection
Update version to 2.5.0
feat: Implement tooltip hover activation

❌ Bad Commit Messages (Never do this):
Add keyboard shortcuts (Claude-assisted)
Fix issue with help from AI
Update version - implemented by Claude
feat: Add feature (AI-generated)
```

### Commit Message Best Practices

1. **Use imperative mood**: "Add feature" not "Added feature" or "Adds feature"
2. **Be specific**: Describe what changed and why
3. **Keep it concise**: First line should be 50 characters or less
4. **Add details in body**: If needed, add detailed explanation after blank line
5. **Follow conventional commits**: Use prefixes like `feat:`, `fix:`, `docs:`, `refactor:` when appropriate
6. **Never mention AI**: The commit message should read as if written by a human developer

### Commit Footer (Metadata Only)

**IMPORTANT**: The footer below goes AFTER the commit message body, separated by blank lines. It's commit metadata, NOT part of the commit message description.

**Always include this footer:**

```
🤖 Generated with [Claude Code](https://claude.com/claude-code)

Co-Authored-By: Claude Sonnet 4.5 <noreply@anthropic.com>
```

This footer:

- Acknowledges AI assistance in the Git metadata
- Does NOT appear in the commit message description
- Is separated from the message by blank lines
- Gives proper attribution without cluttering the commit history

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
