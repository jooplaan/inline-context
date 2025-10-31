# Reveal Text Block - React Quill Integration

A WordPress Gutenberg Rich Text Format plugin with React Quill integration for creating inline expandable inline context with rich text content.

## Features

### 🎨 Rich Text Editing
- **React Quill Editor**: Full-featured WYSIWYG editor in the WordPress block editor
- **Formatting Options**: Bold, italic, links, ordered/unordered lists
- **WordPress Integration**: Seamlessly integrated with WordPress block editor toolbar

### 🔒 Security & Safety
- **HTML Sanitization**: Safe rendering of rich content on the frontend
- **XSS Protection**: Removes dangerous scripts and event handlers
- **Link Security**: Automatic `rel="noopener"` for external links

### 🔄 Backward Compatibility
- **Legacy Support**: Existing plain text inline context continues to work
- **Auto-Detection**: Automatically detects Quill vs plain text content
- **Smooth Migration**: No manual intervention required for existing content

### ♿ Accessibility
- **ARIA Attributes**: Proper `aria-expanded`, `aria-describedby`, and `aria-controls`
- **Semantic HTML**: Uses `role="note"` for inline context content
- **Keyboard Support**: Full keyboard navigation support

## Installation

1. Install dependencies:
```bash
npm install
```

2. Build the plugin:
```bash
npm run build
```

3. Activate the plugin in WordPress admin

## Usage

### In the WordPress Editor

1. **Select Text**: Highlight the text where you want to add inline context
2. **Open Editor**: Click the "Inline Context" button in the formatting toolbar
3. **Rich Editing**: Use the React Quill editor to format your inline context:
   - **Bold/Italic**: Format text styling
   - **Links**: Add internal or external links
   - **Lists**: Create ordered or unordered lists
4. **Save**: Click "Save" to apply the inline context
5. **Remove**: For existing inline contexts, click "Remove Inline Context" to delete them

### On the Frontend

- **Click to Reveal**: Click any inline context link to show the rich content
- **Click to Hide**: Click again to hide the content
- **Mutual Exclusion**: Opening a new inline context automatically closes others

## Technical Implementation

### Editor Component (`src/edit.js`)
- React Quill integration with WordPress-friendly configuration
- Custom toolbar with essential formatting options
- Popover positioning based on text selection
- Keyboard shortcuts (Cmd/Ctrl+Enter to save, Escape to cancel)

### Frontend Rendering (`src/frontend.js`)
- Safe HTML sanitization for React Quill content
- Backward compatibility with plain text inline context
- Event delegation for performance
- Proper ARIA state management

### Styling (`src/editor.scss`)
- WordPress-consistent design
- Responsive layout
- Quill toolbar customization to match WordPress UI

## Configuration

### Quill Modules
```javascript
const QUILL_MODULES = {
  toolbar: [
    ['bold', 'italic'],
    ['link'],
    [{ 'list': 'ordered' }, { 'list': 'bullet' }],
    ['clean']
  ],
};
```

### Allowed Formats
```javascript
const QUILL_FORMATS = [
  'bold', 'italic', 'link', 'list', 'bullet'
];
```

## Security Features

### HTML Sanitization
- Removes `<script>` tags and dangerous content
- Strips event handlers (`onclick`, `onload`, etc.)
- Validates link `href` attributes
- Adds security attributes to external links

### Content Detection
```javascript
// Detects Quill content vs plain text
const isQuillContent = hiddenContent.includes('<p>') || 
                      hiddenContent.includes('<strong>') || 
                      hiddenContent.includes('<em>');
```

## File Structure

```
reveal-text-block/
├── src/
│   ├── index.js          # Plugin registration & imports
│   ├── edit.js           # React Quill editor component  
│   ├── frontend.js       # Frontend interaction & sanitization
│   ├── editor.scss       # Editor-specific styles
│   └── style.scss        # Frontend styles
├── build/                # Compiled assets
├── reveal-text.php       # WordPress plugin bootstrap
├── demo.html            # Standalone demo
└── package.json         # Dependencies & scripts
```

## Dependencies

- **react-quill**: Rich text editor component
- **dompurify**: HTML sanitization (frontend safety)
- **@wordpress/scripts**: Build tooling and WordPress integration

## Development

### Start Development Server
```bash
npm run start
```

### Build for Production  
```bash
npm run build
```

### Demo
Open `demo.html` in your browser to see the frontend functionality in action.

## Browser Support

- Modern browsers with ES6+ support
- WordPress 5.0+ (Gutenberg editor)
- React 16.8+ (hooks support)

## License

GPL v2 or later - consistent with WordPress licensing.