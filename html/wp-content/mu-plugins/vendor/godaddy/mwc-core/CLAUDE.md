# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

MWC Core is GoDaddy's Managed WooCommerce Core plugin - an enterprise-grade WordPress plugin that provides simplified eCommerce functionality. It's built with modern PHP practices, strict quality standards, and a modular architecture designed for GoDaddy's managed WooCommerce platform.

## Development Commands

### Testing
```bash
# Run all tests
vendor/bin/phpunit

# Run specific test suite
vendor/bin/phpunit tests/Unit
vendor/bin/phpunit tests/Integration

# Run tests with specific seed for reproducible random order
vendor/bin/phpunit --random-order-seed=12345
```

### Code Quality
```bash
# Run static analysis (PHPStan max level)
vendor/bin/phpstan analyse --memory-limit=2G
```

### Asset Building
```bash
# Install dependencies
yarn install

# Watch CSS compilation during development
yarn dev

# Build production CSS assets
yarn build
```

### Development Dependencies
```bash
# Install PHP dependencies
composer install

# Update development version of shared libraries
composer require "godaddy/mwc-common=dev-branch-name as 6.0"
composer require "godaddy/mwc-dashboard=dev-branch-name"
```

## Architecture

### Directory Structure
- `src/` - Main PHP source code with PSR-4 autoloading (`GoDaddy\WordPress\MWC\Core\`)
- `tests/Unit/` - Unit tests with strict PHPUnit configuration
- `tests/Integration/` - Integration tests
- `configurations/` - PHP configuration files for features
- `assets/css/src/` - Source CSS files processed with PostCSS and MWC design system
- `templates/` - Email and WooCommerce template overrides
- `languages/` - Internationalization files (36+ languages)
- `documentation/docs` - Project documentation powered by Docusaurus

### Core Architecture Patterns
- **Component-based system** with `HasComponentsFromContainerTrait`
- **Singleton pattern** for main Package class
- **Repository pattern** for data access
- **Gateway pattern** for external service integrations
- **Event-driven architecture** with producers/subscribers
- **Feature toggles** and runtime configuration

### MWC Common Integration
- **Location**: `vendor/godaddy/mwc-common`
- **Purpose**: Shared utilities, abstract classes, contracts, and helpers
- **Usage**: Always check mwc-common first for existing abstractions before creating new ones
- **Key Components**: Events, Repositories, Contracts, Traits, Helpers

### Key Dependencies
- **WordPress Plugin** architecture with WooCommerce 3.5+ requirement
- **PHP 7.4-8.3** compatibility enforced via PHPCS
- **Stripe PHP SDK** for payment processing
- **Multiple internal GoDaddy packages** (mwc-common, mwc-dashboard, etc.)
- **PostCSS** with MWC design system for CSS compilation

## Development Workflow

### Development Best Practices
- **Check MWC Common first**
	- Before creating new abstractions, events, or utilities, verify if they
	  already exist in `mwc-common`.
- **Coding Standards**
	- Avoid temporary variables where possible.
- **Docblocks**
	- New methods should have a short one line description. When updating existing methods confirm that the description still fits.
	- Import classes with `use` statements rather than using fully qualified names (e.g., `\Exception`) in docblocks.
	- Use PHPDoc generic type syntax (e.g., `array<int, string>`) for clarity in static analysis tools like Psalm or PHPStan.
- **Data Objects**
	- Data object classes should extend `GoDaddy\WordPress\MWC\Common\DataObjects\AbstractDataObject`
- **Comments**
	- Use comments to explain "why" something is done, not "what" is being done.
	- Avoid redundant comments that restate the obvious.
- **Tests**
	- test classes are `final`
	- use `@coversDefaultClass` with truncated `@covers` annotation
    - `@covers` annotations should not end with `()`
    - test extend `TestCase` from `GoDaddy\WordPress\MWC\Core\Tests\TestCase`
    - prefer a `@dataProvider` rather than multiple test methods for similar logic
      - provider method should be named `provider{TestMethodNameOmittingTheWordTest}`  
      - should return `Generator` yielding arrays with keys equal to the test method parameters
    - no need to test imported methods
    - no need to test constructor methods
    - test each method individually if possible, you can mock internal methods but in order to avoid relying on internal details of methods other than the one being tested, focus on the possible return values of those internal methods instead providing every input combination that those methods could receive
    - tests should validate both that methods are called and how they are called (with what arguments)
	- after creating or updating tests, run `phpunit` to confirm the tests pass
    - do not use `redefine()` (see helpers)
    - do not use `Mockery` `alias` unless absolutely necessary
    - do not create Mockery mocks inside data provider methods
    - there should be no reason to manually instantiate `ReflectionMethod` to work with inaccessible methods (see helpers for alternatives)
    - do not call `$this->assertTrue(true)` when there are no other assertions; instead call `$this->assertConditionsMet()`
    - `->expects()` and `->once()` in the same method chain is redundant because `expects()` already implies `once()`
    - use the available test helper methods:
      - use `$this->mockStaticMethod()` to mock static method calls
      - use `WP_Mock::userFunction()` to mock function calls
      - use `$this->invokeInaccessibleMethod()` to invoke non-public methods
      - use `$this->getInaccessiblePropertyValue()` and `$this->setInaccessiblePropertyValue()` to get and set non-public properties
- **Code Quality**
	- Code must pass `phpstan` static analysis 
- **Git**
    - Use single quotes instead of double quotes in git commit commands

### Testing Standards
- Tests run in **random order** for better isolation
- **Strict PHPUnit configuration** with coverage requirements
- **PHPStan max level** static analysis with baseline
- **Mock classes** for WooCommerce dependencies
- Both unit and integration test suites

### Local Development
- Symlink shared libraries in `vendor/godaddy/{package}` for development
- Define `MWC_DASHBOARD_PLUGIN_URL` in wp-config.php when using symlinks
- Clear cached data with provided snippet for clean testing environment

### Code Quality Requirements
- **PHPCompatibility** rules for PHP 7.4-8.3 support
- **PSR-4 autoloading** with strict namespace conventions
- **Exception handling** with typed exceptions and proper error flow
- **Security best practices** - never commit secrets or expose sensitive data

## Integration Points

### WordPress/WooCommerce Integration
- Custom payment gateways and processing
- Admin interface extensions and enhancements
- Email template system with custom templates
- Order lifecycle event hooks and handlers

### External Service Integrations
- GoDaddy Commerce API for platform features
- Stripe for payment processing
- Poynt for in-person payments
- Marketplace APIs (Amazon, eBay, etc.)
- Analytics services integration

## Release and Deployment

The project uses semantic versioning with automated processes for:
- TLA site development versions
- Managed WordPress platform deployment
- WooSaaS platform deployment

Refer to internal GoDaddy documentation for specific deployment procedures.
