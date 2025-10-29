#!/usr/bin/bash

# Pre-commit hook script for running PHPCS
# This script can be placed in .git/hooks/pre-commit to run automatically

echo "Running PHP Code Sniffer..."

# Check if composer dependencies are installed
if [ ! -d "vendor" ]; then
    echo "Installing composer dependencies..."
    composer install --dev
fi

# Run PHPCS
./vendor/bin/phpcs

# Check the exit status
if [ $? -ne 0 ]; then
    echo ""
    echo "PHPCS found coding standard violations."
    echo "Please fix the issues above before committing."
    echo ""
    echo "You can try to automatically fix some issues by running:"
    echo "  composer run phpcs:fix"
    echo ""
    echo "Or run PHPCS manually with:"
    echo "  composer run phpcs"
    echo ""
    exit 1
fi

echo "PHPCS passed! ✅"
