#!/bin/bash

# Test script for WordPress Abilities API
# Replace YOUR_SITE_URL, USERNAME, and PASSWORD with your actual values

SITE_URL="http://localhost:8888"  # Change this to your WordPress URL
USERNAME="admin"                   # Your WordPress username
PASSWORD="your-app-password"       # Create in Users > Profile > Application Passwords

echo "=== Testing WordPress Abilities API ==="
echo ""

echo "1. Testing if Abilities API is available (WordPress 6.9+):"
curl -s "$SITE_URL/wp-json/wp-abilities/v1/abilities" | jq -r '.message // "✓ Abilities API is available"'
echo ""

echo "2. Listing all inline-context abilities:"
curl -s "$SITE_URL/wp-json/wp-abilities/v1/abilities?category=inline-context" \
  -u "$USERNAME:$PASSWORD" | jq -r '.[] | "  - \(.name): \(.description)"'
echo ""

echo "3. Testing create-note ability:"
curl -s -X POST "$SITE_URL/wp-json/wp-abilities/v1/abilities/inline-context/create-note/run" \
  -u "$USERNAME:$PASSWORD" \
  -H "Content-Type: application/json" \
  -d '{
    "input": {
      "title": "Test Note",
      "content": "<p>Test content from abilities API</p>",
      "is_reusable": true
    }
  }' | jq '.'
echo ""

echo "=== Test complete ==="
