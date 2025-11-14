# Inline Context Plugin Tests

## Quick Setup (Recommended)

### 1. Install Dependencies

```bash
composer install
```

### 2. Configure Environment

```bash
# Copy the example environment file
cp .env.example .env

# Edit .env with your database credentials
nano .env
```

Default `.env` configuration:

```bash
DB_NAME=wordpress_test
DB_USER=root
DB_PASS=
DB_HOST=localhost
WP_VERSION=latest
```

### 3. Run Setup Script

```bash
# Automated setup (uses .env configuration)
composer test:setup

# Or manually
bash bin/setup-tests.sh
```

This will:

- Create `.env` from `.env.example` if it doesn't exist
- Install WordPress test suite using your `.env` configuration
- Verify the setup

### 4. Run Tests

```bash
# Run all tests
composer test:unit

# Or use PHPUnit directly
vendor/bin/phpunit

# Run specific test file
vendor/bin/phpunit tests/test-cpt.php

# Run with coverage (requires xdebug)
vendor/bin/phpunit --coverage-html coverage/
```

## Manual Setup (Advanced)

If you prefer to specify credentials via command line:

```bash
bin/install-wp-tests.sh <db-name> <db-user> <db-pass> [db-host] [wp-version]

# Example:
bin/install-wp-tests.sh wordpress_test root '' localhost latest
```

Parameters:

- `wordpress_test` - Database name for tests
- `root` - Database user
- `''` - Database password (empty in this example)
- `localhost` - Database host
- `latest` - WordPress version (or specific version like 6.4)

**Note:** Command line arguments take precedence over `.env` values.

### 3. Run Tests

```bash
# Run all tests
composer test:unit

# Or use PHPUnit directly
vendor/bin/phpunit

# Run specific test file
vendor/bin/phpunit tests/test-cpt.php

# Run with coverage (requires xdebug)
vendor/bin/phpunit --coverage-html coverage/
```

## Test Structure

```text
tests/
├── bootstrap.php          # Test bootstrap file
├── test-cpt.php          # Custom Post Type tests
├── test-rest-api.php     # REST API endpoint tests
└── test-sync.php         # Synchronization tests
```

## What's Being Tested

### CPT Tests (`test-cpt.php`)

- Custom post type registration
- Taxonomy registration
- Note creation and meta fields
- Category assignment
- Reusable note defaults

### REST API Tests (`test-rest-api.php`)

- REST namespace registration
- Search endpoint functionality
- Track usage endpoint
- Handle removals endpoint
- Reusable filter functionality

### Sync Tests (`test-sync.php`)

- Usage tracking on post save
- Removal tracking
- Multiple usage tracking
- Category synchronization

## Environment Variables

You can customize the test environment:

```bash
# Custom test library location
export WP_TESTS_DIR=/path/to/wordpress-tests-lib

# Custom WordPress installation location
export WP_CORE_DIR=/path/to/wordpress

# Then run tests
vendor/bin/phpunit
```

## Continuous Integration

For CI environments (GitHub Actions, etc.):

```yaml
- name: Install WordPress Test Suite
  run: bash bin/install-wp-tests.sh wordpress_test root 'root' 127.0.0.1 latest

- name: Run tests
  run: composer test:unit
```

## Troubleshooting

### "Could not find .../includes/functions.php"

Run the install script:

```bash
bin/install-wp-tests.sh wordpress_test root '' localhost latest
```

### Database connection errors

Check your MySQL credentials and ensure the database user has permission to create databases:

```sql
GRANT ALL PRIVILEGES ON wordpress_test.* TO 'root'@'localhost';
```

### Class not found errors

Ensure the plugin is being loaded correctly in `tests/bootstrap.php` and that all dependencies are installed:

```bash
composer install
```

## Writing New Tests

1. Create a new file in `tests/` with prefix `test-`
2. Extend `WP_UnitTestCase`
3. Write test methods with prefix `test_`

Example:

```php
<?php
class Test_My_Feature extends WP_UnitTestCase {
    public function test_my_functionality() {
        $this->assertTrue( true );
    }
}
```

## Code Coverage

Generate code coverage report:

```bash
# Requires xdebug
vendor/bin/phpunit --coverage-html coverage/
open coverage/index.html
```
