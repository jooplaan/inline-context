#!/usr/bin/env bash

# WordPress Test Environment Setup Script
# This script helps you set up the testing environment quickly

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"

echo "==================================="
echo "WordPress Test Environment Setup"
echo "==================================="
echo ""

# Check if .env exists
if [ -f "$PROJECT_ROOT/.env" ]; then
	echo "✓ .env file found"
else
	echo "Creating .env file from .env.example..."
	cp "$PROJECT_ROOT/.env.example" "$PROJECT_ROOT/.env"
	echo "✓ .env file created"
	echo ""
	echo "⚠️  Please edit .env and update your database credentials:"
	echo "   nano .env"
	echo ""
	echo "Default values:"
	echo "  DB_NAME=wordpress_test"
	echo "  DB_USER=root"
	echo "  DB_PASS="
	echo "  DB_HOST=localhost"
	echo ""
	read -p "Press Enter to continue after editing .env..."
fi

echo ""
echo "Installing WordPress test suite..."
bash "$SCRIPT_DIR/install-wp-tests.sh"

if [ $? -eq 0 ]; then
	echo ""
	echo "==================================="
	echo "✓ Setup Complete!"
	echo "==================================="
	echo ""
	echo "You can now run tests with:"
	echo "  composer test:unit"
	echo "  vendor/bin/phpunit"
	echo ""
else
	echo ""
	echo "==================================="
	echo "✗ Setup Failed"
	echo "==================================="
	echo ""
	echo "Please check:"
	echo "  1. MySQL/MariaDB is running"
	echo "  2. Database credentials in .env are correct"
	echo "  3. Database user has permission to create databases"
	echo ""
fi
