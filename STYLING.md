# Styling Inline Context

Inline Context provides extensive styling options through WordPress theme.json integration and CSS custom properties.

## How theme.json Integration Works

The plugin includes a `theme.json` file that registers CSS custom properties following WordPress conventions. These properties are automatically merged with your theme's configuration and become available as CSS variables.

### Important: No Visual UI (Yet)

**WordPress does not currently provide a visual interface** for editing these custom properties in the Site Editor. Instead, you customize them through:

1. **Additional CSS** (Appearance → Customize → Additional CSS)
2. **Your theme's theme.json** (for block themes)
3. **Your theme's stylesheet**

While this approach doesn't provide point-and-click styling controls, it does offer these benefits:
- ✅ Follows WordPress theming best practices
- ✅ Works seamlessly with block themes
- ✅ Theme developers can easily integrate and override styles
- ✅ Future WordPress improvements will automatically benefit the plugin

## Customization Methods

### Method 1: Additional CSS (Easiest for Most Users)

Go to **Appearance → Customize → Additional CSS** and add your custom properties:

```css
:root {
  /* Example: Change note background and accent color */
  --wp--custom--inline-context--note--background: #f0f0f0;
  --wp--custom--inline-context--note--accent-color: #ff6b35;
  --wp--custom--inline-context--link--hover-color: #ff6b35;
}
```

### Method 2: Block Theme theme.json

For block themes, add custom properties to your theme's `theme.json`:

```json
{
  "version": 3,
  "settings": {
    "custom": {
      "inlineContext": {
        "note": {
          "background": "#f0f0f0",
          "accentColor": "#ff6b35"
        },
        "link": {
          "hoverColor": "#ff6b35"
        }
      }
    }
  }
}
```

### Method 3: Theme Stylesheet

Add custom properties directly to your theme's `style.css` or custom CSS file:

```css
:root {
  --wp--custom--inline-context--note--background: #f0f0f0;
}
```

## Available Custom Properties

All custom properties follow WordPress naming conventions: `--wp--custom--inline-context--{category}--{property}`

### Link Styling
Control the appearance of inline context trigger links:

```css
/* Scroll margin for anchor navigation */
--wp--custom--inline-context--link--scroll-margin: 2rem;

/* Link colors */
--wp--custom--inline-context--link--hover-color: var(--wp--preset--color--primary, #0073aa);
--wp--custom--inline-context--link--focus-color: var(--wp--preset--color--primary, #0073aa);
--wp--custom--inline-context--link--open-color: var(--wp--preset--color--primary, #0073aa);

/* Focus border */
--wp--custom--inline-context--link--focus-border-color: var(--wp--preset--color--primary, #0073aa);
```

#### Note Block Styling
Control the appearance of expanded context notes:

```css
/* Spacing */
--wp--custom--inline-context--note--margin-y: 0.75em;
--wp--custom--inline-context--note--padding-y: 0.75em;
--wp--custom--inline-context--note--padding-x: 1em;

/* Colors and borders */
--wp--custom--inline-context--note--background: rgba(0, 0, 0, 0.04);
--wp--custom--inline-context--note--border-color: rgba(0, 0, 0, 0.08);
--wp--custom--inline-context--note--accent-width: 3px;
--wp--custom--inline-context--note--accent-color: var(--wp--preset--color--primary, #0073aa);

/* Visual effects */
--wp--custom--inline-context--note--radius: 6px;
--wp--custom--inline-context--note--shadow: 0 1px 2px rgba(0, 0, 0, 0.05);

/* Typography */
--wp--custom--inline-context--note--font-size: 0.95em;
--wp--custom--inline-context--note--link-color: inherit;
--wp--custom--inline-context--note--link-underline: currentColor;
```

#### Chevron Icon
Control the dropdown chevron indicator:

```css
/* Size and spacing */
--wp--custom--inline-context--chevron--size: 0.75em;
--wp--custom--inline-context--chevron--margin-left: 0.25em;

/* Opacity */
--wp--custom--inline-context--chevron--opacity: 0.7;
--wp--custom--inline-context--chevron--hover-opacity: 1;

/* Note: Chevron colors are hardcoded in SVG data URIs (see Class-Based Styling below) */
```

## Styling Examples

These examples show how to create different visual styles using CSS custom properties:

### Example: Dark Theme

```css
:root {
  /* Dark note blocks */
  --wp--custom--inline-context--note--background: rgba(255, 255, 255, 0.05);
  --wp--custom--inline-context--note--border-color: rgba(255, 255, 255, 0.1);
  --wp--custom--inline-context--note--accent-color: #4a9eff;
  
  /* Bright hover colors */
  --wp--custom--inline-context--link--hover-color: #4a9eff;
  --wp--custom--inline-context--link--open-color: #4a9eff;
}
```

### Example: Minimal Style

```css
:root {
  /* Subtle, minimal appearance */
  --wp--custom--inline-context--note--background: transparent;
  --wp--custom--inline-context--note--border-color: #e0e0e0;
  --wp--custom--inline-context--note--accent-width: 2px;
  --wp--custom--inline-context--note--radius: 0;
  --wp--custom--inline-context--note--shadow: none;
  --wp--custom--inline-context--note--padding-y: 0.5em;
  --wp--custom--inline-context--note--padding-x: 0.75em;
}
```

### Example: Colorful Accent

```css
:root {
  /* Bold, attention-grabbing */
  --wp--custom--inline-context--note--background: #fff9e6;
  --wp--custom--inline-context--note--border-color: #ffe066;
  --wp--custom--inline-context--note--accent-width: 5px;
  --wp--custom--inline-context--note--accent-color: #ff6b35;
  --wp--custom--inline-context--link--hover-color: #ff6b35;
  --wp--custom--inline-context--link--open-color: #ff6b35;
}
```

## Class-Based Styling

For advanced customization, you can also target specific CSS classes directly:

```css
/* The trigger link */
.wp-inline-context {
  /* Your custom styles */
}

.wp-inline-context:hover {
  /* Hover state */
}

.wp-inline-context--open {
  /* When note is expanded */
}

/* The expanded note */
.wp-inline-context-inline {
  /* Your custom styles */
}
```

## Using Theme Colors

The plugin automatically uses your theme's primary color for links and accents. To customize this with WordPress theme colors:

```css
:root {
  --wp--custom--inline-context--link--hover-color: var(--wp--preset--color--secondary);
  --wp--custom--inline-context--note--accent-color: var(--wp--preset--color--tertiary);
}
```

## Developer Filters

For programmatic customization, see [FILTERS.md](FILTERS.md) for available WordPress filters.

