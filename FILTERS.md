# Developer Filters

Inline Context provides several WordPress filters to customize the plugin's behavior. These filters allow developers to extend functionality through themes or other plugins.

## PHP Filters

### `inline_context_allowed_attributes`

Customize which HTML attributes are allowed on anchor elements in the block editor.

**Type:** PHP  
**Location:** `inline-context.php`  
**Default Value:**
```php
array(
    'data-inline-context' => true,
    'data-anchor-id'      => true,
    'role'                => true,
    'aria-expanded'       => true,
)
```

**Example:**
```php
// Allow custom data attributes
add_filter( 'inline_context_allowed_attributes', function( $attributes ) {
    $attributes['data-custom-field'] = true;
    return $attributes;
} );
```

---

## JavaScript Filters (Frontend)

All JavaScript filters use the WordPress hooks API (`wp.hooks.applyFilters`). Add these in your theme's JavaScript or a separate plugin.

### `inline_context_revealed_class`

Change the CSS class applied to triggers when their note is open.

**Type:** JavaScript (Frontend)  
**Location:** `src/frontend.js`  
**Default Value:** `'wp-inline-context--open'`  
**Parameters:**
- `{string}` className - The CSS class to use

**Example:**
```javascript
wp.hooks.addFilter(
    'inline_context_revealed_class',
    'my-theme',
    ( className ) => 'my-custom-open-class'
);
```

---

### `inline_context_allowed_tags`

Customize which HTML tags are allowed in note content after sanitization.

**Type:** JavaScript (Frontend)  
**Location:** `src/frontend.js`  
**Default Value:** `['p', 'strong', 'em', 'a', 'ol', 'ul', 'li', 'br']`  
**Parameters:**
- `{Array<string>}` tags - Array of allowed HTML tag names

**Example:**
```javascript
// Allow additional tags like headings and blockquotes
wp.hooks.addFilter(
    'inline_context_allowed_tags',
    'my-theme',
    ( tags ) => {
        return [...tags, 'h2', 'h3', 'blockquote', 'code'];
    }
);
```

---

### `inline_context_allowed_attributes`

Customize which HTML attributes are allowed in note content after sanitization.

**Type:** JavaScript (Frontend)  
**Location:** `src/frontend.js`  
**Default Value:** `['href', 'rel', 'target']`  
**Parameters:**
- `{Array<string>}` attributes - Array of allowed HTML attribute names

**Example:**
```javascript
// Allow additional attributes like classes and IDs
wp.hooks.addFilter(
    'inline_context_allowed_attributes',
    'my-theme',
    ( attributes ) => {
        return [...attributes, 'class', 'id', 'data-custom'];
    }
);
```

---

### `inline_context_pre_sanitize_html`

Modify note HTML content before DOMPurify sanitization.

**Type:** JavaScript (Frontend)  
**Location:** `src/frontend.js`  
**Parameters:**
- `{string}` html - The HTML content to be sanitized

**Example:**
```javascript
// Add a wrapper div before sanitization
wp.hooks.addFilter(
    'inline_context_pre_sanitize_html',
    'my-theme',
    ( html ) => {
        return `<div class="note-wrapper">${html}</div>`;
    }
);
```

---

### `inline_context_post_sanitize_html`

Modify note HTML content after DOMPurify sanitization.

**Type:** JavaScript (Frontend)  
**Location:** `src/frontend.js`  
**Parameters:**
- `{string}` html - The sanitized HTML content

**Example:**
```javascript
// Add a footer after sanitization
wp.hooks.addFilter(
    'inline_context_post_sanitize_html',
    'my-theme',
    ( html ) => {
        return html + '<p class="note-footer">End of note</p>';
    }
);
```

---

### `inline_context_process_links`

Control whether links in notes should be processed for target behavior.

**Type:** JavaScript (Frontend)  
**Location:** `src/frontend.js`  
**Default Value:** `true`  
**Parameters:**
- `{boolean}` shouldProcess - Whether to process links
- `{HTMLElement}` noteElement - The note container element

**Example:**
```javascript
// Disable link processing for notes with a specific class
wp.hooks.addFilter(
    'inline_context_process_links',
    'my-theme',
    ( shouldProcess, noteElement ) => {
        return !noteElement.classList.contains('no-link-processing');
    }
);
```

---

### `inline_context_internal_link_target`

Customize the target attribute for internal links.

**Type:** JavaScript (Frontend)  
**Location:** `src/frontend.js`  
**Default Value:** `'_self'`  
**Parameters:**
- `{string}` target - The target attribute value
- `{string}` href - The link URL
- `{HTMLAnchorElement}` link - The link element

**Example:**
```javascript
// Open internal links in new tab if they match a pattern
wp.hooks.addFilter(
    'inline_context_internal_link_target',
    'my-theme',
    ( target, href, link ) => {
        if ( href.includes('/external-section/') ) {
            return '_blank';
        }
        return target;
    }
);
```

---

### `inline_context_external_link_target`

Customize the target attribute for external links.

**Type:** JavaScript (Frontend)  
**Location:** `src/frontend.js`  
**Default Value:** `'_blank'`  
**Parameters:**
- `{string}` target - The target attribute value
- `{string}` href - The link URL
- `{HTMLAnchorElement}` link - The link element

**Example:**
```javascript
// Open trusted external domains in same tab
wp.hooks.addFilter(
    'inline_context_external_link_target',
    'my-theme',
    ( target, href, link ) => {
        const trustedDomains = ['wikipedia.org', 'github.com'];
        const url = new URL( href );
        if ( trustedDomains.some( domain => url.hostname.includes( domain ) ) ) {
            return '_self';
        }
        return target;
    }
);
```

---

### `inline_context_note_class`

Customize the CSS class for the revealed note container.

**Type:** JavaScript (Frontend)  
**Location:** `src/frontend.js`  
**Default Value:** `'wp-inline-context-inline'`  
**Parameters:**
- `{string}` className - The CSS class to use
- `{HTMLElement}` trigger - The trigger element that was clicked

**Example:**
```javascript
// Add custom class based on trigger attributes
wp.hooks.addFilter(
    'inline_context_note_class',
    'my-theme',
    ( className, trigger ) => {
        const customType = trigger.dataset.customType;
        return customType ? `${className} note-${customType}` : className;
    }
);
```

---

## JavaScript Filters (Block Editor)

### `inline_context_generate_anchor_id`

Customize how unique anchor IDs are generated for new notes.

**Type:** JavaScript (Block Editor)  
**Location:** `src/edit.js`  
**Default Value:** `'context-note-{timestamp}-{random}'`  
**Parameters:**
- `{string}` anchorId - The generated anchor ID
- `{Object}` context - Object containing `timestamp` and `random` values

**Example:**
```javascript
// Use a custom prefix and format
wp.hooks.addFilter(
    'inline_context_generate_anchor_id',
    'my-theme',
    ( anchorId, context ) => {
        // Use a simpler format or add site-specific prefix
        return `note-${context.timestamp}`;
    }
);
```

---

## Complete Example: Custom Theme Integration

Here's a complete example showing how to use multiple filters together:

```php
// functions.php

// Enqueue custom JavaScript for filters
function mytheme_inline_context_filters() {
    if ( ! is_admin() ) {
        wp_enqueue_script(
            'mytheme-inline-context',
            get_template_directory_uri() . '/js/inline-context-filters.js',
            array( 'wp-hooks', 'trybes-inline-context-frontend' ),
            '1.0.0',
            true
        );
    }
}
add_action( 'wp_enqueue_scripts', 'mytheme_inline_context_filters' );

// Allow additional KSES attributes
add_filter( 'inline_context_allowed_attributes', function( $attributes ) {
    $attributes['data-note-type'] = true;
    return $attributes;
} );
```

```javascript
// js/inline-context-filters.js

// Allow code blocks in notes
wp.hooks.addFilter(
    'inline_context_allowed_tags',
    'my-theme',
    ( tags ) => [...tags, 'code', 'pre']
);

// Allow class attributes for styling
wp.hooks.addFilter(
    'inline_context_allowed_attributes',
    'my-theme',
    ( attributes ) => [...attributes, 'class']
);

// Add custom styling to notes based on type
wp.hooks.addFilter(
    'inline_context_note_class',
    'my-theme',
    ( className, trigger ) => {
        const noteType = trigger.dataset.noteType;
        return noteType ? `${className} note-type-${noteType}` : className;
    }
);

// Open all links in same tab
wp.hooks.addFilter(
    'inline_context_external_link_target',
    'my-theme',
    () => '_self'
);
```

---

## Best Practices

1. **Always use a unique namespace** for your filter callbacks (e.g., `'my-theme'` or `'my-plugin'`)
2. **Return the expected data type** - don't return `undefined` or change types
3. **Be defensive** - check if values exist before manipulating them
4. **Test thoroughly** - filters affect core functionality, so test edge cases
5. **Document your customizations** - help future maintainers understand your changes
6. **Check dependencies** - ensure `wp-hooks` is loaded before adding filters
7. **Consider performance** - filters run frequently, avoid expensive operations

---

## Security Considerations

When extending allowed tags or attributes:

- **Never disable sanitization** - always use DOMPurify or equivalent
- **Be cautious with script tags** - they're blocked by default for security
- **Validate link targets** - prevent javascript: URLs and other XSS vectors
- **Test with malicious input** - ensure your filters don't introduce vulnerabilities

---

## Need Help?

If you're building something complex with these filters or need additional hooks, please open an issue on GitHub or contact the plugin author.
