# Abilities API Integration

WordPress 6.9+ includes the Abilities API, which allows plugins to register their functionality in a machine-readable format. This enables AI assistants, automation tools, and external applications to discover and use plugin features through a standardized interface.

## Features

The Inline Context plugin exposes five core abilities:

1. **Create Note** - Create new inline context notes
2. **Search Notes** - Search existing notes by title/content
3. **Get Categories** - Retrieve all available categories
4. **Get Note** - Fetch a specific note by ID
5. **Create Inline Note** - Create note and get ready-to-embed HTML markup (for AI content generators)

All abilities are automatically available via WordPress Abilities API endpoints for authenticated users.

## Requirements

- WordPress 6.9 or higher (for Abilities API)
- Inline Context plugin 2.4.0+
- Authentication for REST API access (see below)

## REST API Endpoints

All abilities are exposed through the WordPress standard `/wp-json/wp-abilities/v1/` namespace:

### List All Abilities

```bash
GET /wp-json/wp-abilities/v1/abilities
```

### List Inline Context Abilities

```bash
GET /wp-json/wp-abilities/v1/abilities?category=inline-context
```

### Get Specific Ability

```bash
GET /wp-json/wp-abilities/v1/abilities/inline-context/create-note
```

### Execute an Ability

```bash
POST /wp-json/wp-abilities/v1/abilities/inline-context/create-note/run
```

## Authentication

REST API access requires authentication. Use one of these methods:

### 1. Application Passwords (Recommended)

```bash
curl -u 'USERNAME:APPLICATION_PASSWORD' \
  https://example.com/wp-json/wp-abilities/v1/abilities
```

To create an application password:

1. Go to Users → Profile
2. Scroll to "Application Passwords"
3. Enter a name and click "Add New Application Password"
4. Copy the generated password (shown once)

### 2. Cookie Authentication

For same-origin requests from the WordPress admin, cookies provide automatic authentication.

## Usage Examples

### Example 1: Create a Note

```bash
curl -u 'admin:APP_PASSWORD' \
  -X POST https://example.com/wp-json/wp-abilities/v1/abilities/inline-context/create-note/run \
  -H "Content-Type: application/json" \
  -d '{
    "input": {
      "title": "Example Note",
      "content": "<p>This is an example inline context note created via the Abilities API.</p>",
      "category": "definition",
      "is_reusable": true
    }
  }'
```

**Response:**

```json
{
  "success": true,
  "id": 123,
  "message": "Note created successfully"
}
```

### Example 2: Search Notes

```bash
curl -u 'admin:APP_PASSWORD' \
  -X POST https://example.com/wp-json/wp-abilities/v1/abilities/inline-context/search-notes/run \
  -H "Content-Type: application/json" \
  -d '{
    "input": {
      "search": "example",
      "reusable_only": true,
      "limit": 5
    }
  }'
```

**Response:**

```json
{
  "notes": [
    {
      "id": 123,
      "title": "Example Note",
      "content": "<p>This is an example...</p>",
      "excerpt": "This is an example...",
      "is_reusable": true,
      "category": "Definition"
    }
  ],
  "total": 1
}
```

### Example 3: Get Categories

```bash
curl -u 'admin:APP_PASSWORD' \
  -X POST https://example.com/wp-json/wp-abilities/v1/abilities/inline-context/get-categories/run \
  -H "Content-Type: application/json" \
  -d '{"input": {}}'
```

**Response:**

```json
{
  "categories": [
    {
      "id": 1,
      "slug": "definition",
      "name": "Definition",
      "description": "Explanations of terms",
      "color": "#0073aa",
      "icon_closed": "dashicons-editor-help",
      "icon_open": "dashicons-editor-help"
    }
  ]
}
```

### Example 4: Get Specific Note

```bash
curl -u 'admin:APP_PASSWORD' \
  -X POST https://example.com/wp-json/wp-abilities/v1/abilities/inline-context/get-note/run \
  -H "Content-Type: application/json" \
  -d '{"input": {"note_id": 123}}'
```

**Response:**

```json
{
  "id": 123,
  "title": "Example Note",
  "content": "<p>Full note content here...</p>",
  "is_reusable": true,
  "usage_count": 3,
  "category": "Definition",
  "category_id": 1,
  "date_created": "2025-12-08 10:30:00"
}
```

### Example 5: Create Inline Note (AI Content Generators)

This ability is specifically designed for AI content generation systems. It creates a note and returns ready-to-embed HTML markup in one call.

```bash
curl -u 'admin:APP_PASSWORD' \
  -X POST https://example.com/wp-json/wp-abilities/v1/abilities/inline-context/create-inline-note/run \
  -H "Content-Type: application/json" \
  -d '{
    "input": {
      "text": "API",
      "note": "<p>Application Programming Interface - a set of protocols for building software.</p>",
      "category": "definition",
      "is_reusable": true
    }
  }'
```

**Response:**

```json
{
  "success": true,
  "note_id": 124,
  "html": "<a class=\"wp-inline-context\" data-note-id=\"124\" data-inline-context=\"<p>Application Programming Interface...</p>\" data-anchor-id=\"context-note-a4f3c2d1\" href=\"#context-note-a4f3c2d1\" role=\"button\" aria-expanded=\"false\">API</a>",
  "message": "Inline note created successfully. Use the HTML markup in your content."
}
```

The returned `html` field contains a complete anchor tag that can be directly inserted into post content. This is perfect for AI content generators that need to add inline context notes while generating blog posts or articles.

  -d '{
    "input": {
      "note_id": 123
    }
  }'

```txt

**Response:**
```json
{
  "id": 123,
  "title": "Example Note",
  "content": "<p>This is an example...</p>",
  "is_reusable": true,
  "usage_count": 3,
  "category": "Definition",
  "category_id": 1,
  "date_created": "2025-12-07 10:00:00"
}
```

## AI Assistant Integration

### Browser-Based AI (Claude, ChatGPT, etc.)

Browser-based AI assistants can use this plugin without any additional configuration when you're logged into WordPress:

**Discovery Endpoint:**

```bash
GET https://YOUR-SITE.com/wp-json/wp-abilities/v1/abilities?category=inline-context
```

**Example AI Workflow:**

1. **Tell the AI about the plugin:**

```txt
I'm using WordPress with the Inline Context plugin. It exposes capabilities 
via the WordPress Abilities API at:
https://my-site.com/wp-json/wp-abilities/v1/abilities?category=inline-context

You can execute abilities by POSTing to:
https://my-site.com/wp-json/wp-abilities/v1/abilities/{ability-name}/run

Authentication uses cookies since I'm logged into WordPress.
```

1. **Ask the AI to help:**

```txt
Create an inline context note explaining "API" with the definition category.
```

1. **The AI will execute:**

```bash
POST /wp-json/wp-abilities/v1/abilities/inline-context/create-note/run
Body: {
  "input": {
    "title": "API",
    "content": "<p>Application Programming Interface...</p>",
    "category": "definition",
    "is_reusable": true
  }
}
```

### Authentication for External Tools

**Application Passwords:**

To create an application password:

1. Go to Users → Profile in WordPress admin
2. Scroll to "Application Passwords"
3. Enter a name (e.g., "AI Assistant") and click "Add New"
4. Copy the generated password

Use with Authorization header:

```bash
Authorization: Basic base64(username:app_password)
```

### ChatGPT with MCP

### Common AI Workflows

#### workflow 1: Add Context While Writing

```txt
AI Prompt: "As I write this article about web development, add inline 
context notes for technical terms like 'REST API', 'JWT', and 'OAuth'."

The AI will:
1. Search for existing notes on these terms
2. Create new notes if they don't exist
3. Provide HTML markup to embed in your content
```

#### Workflow 2: Reuse Existing Notes

```txt
AI Prompt: "Search for notes about 'JavaScript' and show me what we have."

The AI will:
1. Execute search-notes ability
2. Show you matching notes
3. Let you choose which to reuse
```

#### Workflow 3: Bulk Content Enhancement

```txt
AI Prompt: "Read my draft post and suggest 5 inline context notes 
that would help readers understand the technical concepts."

The AI will:
1. Analyze your content
2. Create appropriate notes
3. Provide ready-to-embed HTML for each
```

## Automation & Integrations

### Zapier/Make.com

Use the REST API endpoints to:

- Auto-create notes from research tools
- Import notes from note-taking apps
- Sync definitions with knowledge bases

### Custom Scripts

Example PHP script to bulk import notes:

```php
<?php
$notes = [
    ['title' => 'API', 'content' => 'Application Programming Interface...'],
    ['title' => 'REST', 'content' => 'Representational State Transfer...'],
];

foreach ($notes as $note) {
    $response = wp_remote_post(
        'https://example.com/wp-json/wp-abilities/v1/abilities/inline-context/create-note/run',
        [
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode('username:app_password'),
                'Content-Type' => 'application/json',
            ],
            'body' => json_encode([
                'input' => [
                    'title' => $note['title'],
                    'content' => $note['content'],
                    'is_reusable' => true,
                ]
            ]),
        ]
    );
}
```

## Permissions

Each ability requires specific WordPress capabilities:

- **create-note**: `edit_posts`
- **search-notes**: `edit_posts`
- **get-categories**: `read`
- **get-note**: `read`

Users without the required capability will receive a `403 Forbidden` error.

## Error Handling

Abilities return `WP_Error` objects for errors:

```json
{
  "code": "note_not_found",
  "message": "Note not found",
  "data": {
    "status": 404
  }
}
```

Common error codes:

- `note_not_found` - Note ID doesn't exist
- `rest_forbidden` - User lacks required capability
- `invalid_json` - Malformed request body
- `rest_no_route` - Invalid endpoint

## Input Validation

All inputs are validated against JSON Schema:

- **Strings**: Min/max length constraints
- **Integers**: Minimum/maximum values  
- **Booleans**: Type checking
- **Required fields**: Enforced

Invalid inputs return `400 Bad Request` with details.

## Backward Compatibility

The Abilities API integration is optional and only loads on WordPress 6.9+:

```php
if ( function_exists( 'wp_register_ability' ) ) {
    // Abilities API is available
}
```

The plugin functions normally on older WordPress versions without any changes.

## Development

### Testing Abilities

Use WP-CLI to test abilities without HTTP requests:

```bash
# List all abilities
wp eval "print_r(wp_get_abilities());"

# Get specific ability
wp eval "print_r(wp_get_ability('inline-context/create-note'));"

# Execute ability
wp eval "
\$ability = wp_get_ability('inline-context/create-note');
\$result = \$ability->execute([
    'title' => 'Test Note',
    'content' => '<p>Test content</p>',
]);
print_r(\$result);
"
```

### Adding New Abilities

To add new abilities:

1. Add private method to `Inline_Context_Abilities` class:

   ```php
   private function register_my_ability() {
       wp_register_ability('inline-context/my-ability', [...]);
   }
   ```

2. Call in `register_abilities()` method:

   ```php
   public function register_abilities() {
       $this->register_create_note_ability();
       $this->register_my_ability(); // Add here
   }
   ```

3. Implement execute callback:

   ```php
   public function execute_my_ability($input) {
       // Your logic here
       return $result;
   }
   ```

## Resources

- [WordPress Abilities API Documentation](https://make.wordpress.org/core/2025/11/10/abilities-api-in-wordpress-6-9/)
- [REST API Authentication](https://developer.wordpress.org/rest-api/using-the-rest-api/authentication/)
- [Application Passwords Guide](https://make.wordpress.org/core/2020/11/05/application-passwords-integration-guide/)
- [MCP Adapter for WordPress](https://github.com/WordPress/mcp-adapter)
- [PHP AI Client SDK](https://github.com/WordPress/php-ai-client)

## Support

For issues or questions about the Abilities API integration:

1. Check WordPress is version 6.9 or higher
2. Verify authentication is working
3. Check WordPress debug log for errors
4. Test with WP-CLI first
5. Open an issue on GitHub

## Future Enhancements

Planned for future versions:

- **AI-Powered Note Generation** - Automatically create notes using AI
- **Batch Operations** - Bulk create/update notes
- **Note Templates** - Predefined note structures
- **Advanced Search** - Filter by multiple criteria
- **Import/Export** - Bulk data management
