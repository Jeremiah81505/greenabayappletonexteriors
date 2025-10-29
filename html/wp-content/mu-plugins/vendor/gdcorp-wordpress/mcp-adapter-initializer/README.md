# MCP Adapter Initializer

[![PHP Code Standards](https://github.com/gdcorp-wordpress/mcp-adapter-initializer/actions/workflows/phpcs.yml/badge.svg)](https://github.com/gdcorp-wordpress/mcp-adapter-initializer/actions/workflows/phpcs.yml)

A WordPress MU-plugin that initializes a custom Model Context Protocol (MCP) server with GoDaddy authentication and comprehensive WordPress management tools for AI assistants.

## Overview

This plugin creates an MCP server that exposes WordPress functionality as tools that can be consumed by AI assistants like Claude Desktop. It provides a clean, extensible architecture with 6 built-in tools for content management, plugin management, and site information retrieval, all secured through GoDaddy's authentication system.

## Features

- **GoDaddy JWT Authentication**: Server-level authentication using GoDaddy's auth system with customer ID validation
- **6 Built-in Tools**: Complete WordPress management capabilities (posts, plugins, site info)
- **Streamable Transport**: Modern HTTP streaming implementation for efficient real-time AI communication
- **Extensible Architecture**: Base tool class with admin context switching for secure operations  
- **REST API Integration**: Full REST API exposure with CORS configuration for dashboard integration
- **Namespace Organization**: Clean `GD\MCP\Tools` namespace structure for all tools
- **Admin Privilege Escalation**: Automatic admin user context for tool execution

## Requirements

- **WordPress**: 6.8+ 
- **PHP**: 7.4+
- **Dependencies**:
  - [MCP Adapter Plugin](https://github.com/wordpress/mcp-adapter) (WordPress MCP implementation)
  - [Abilities API Plugin](https://github.com/WordPress/abilities-api) (WordPress abilities system)
  - [GoDaddy PHP Auth Library](https://github.com/gdcorp-identity/PhpAuthLibrary) via Composer
- **Installation**: Standard WordPress plugin installation (no special requirements)

## Installation

### Plugin Dependencies

1. **Install Required WordPress Plugins**:
   ```bash
   # Navigate to your WordPress plugins directory
   cd wp-content/plugins/
   
   # Download and install the MCP Adapter plugin
   git clone https://github.com/wordpress/mcp-adapter.git
   
   # Download and install the Abilities API plugin  
   git clone https://github.com/WordPress/abilities-api.git
   
   # Download and install the MCP Adapter Initializer plugin
   git clone [repository-url] mcp-adapter-initializer
   ```

2. **Install Composer Dependencies**:
   ```bash
   # Navigate to the MCP Adapter Initializer plugin directory
   cd mcp-adapter-initializer
   composer install --no-dev --optimize-autoloader
   ```

3. **Activate All Plugins**:
   - Activate the **MCP Adapter** plugin through WordPress admin
   - Activate the **Abilities API** plugin through WordPress admin  
   - Activate the **MCP Adapter Initializer** plugin through WordPress admin

**Note**: All three plugins work together as standard WordPress plugins and can be managed through the WordPress admin interface.

## Server Configuration

The MCP server is automatically configured with the following settings:

- **Server ID**: `gd-mcp`
- **API Namespace**: `gd-mcp/v1` 
- **API Route**: `mcp`
- **Transport**: HTTP Streamable Transport (modern streaming implementation)
- **Authentication**: GoDaddy JWT with customer ID validation
- **Admin Context**: Automatically switches to admin user for tool execution
- **CORS**: Configured for `https://host.godaddy.com` origin
- **Permissions**: WordPress capability-based authorization per tool

### Authentication System

The server uses GoDaddy's JWT authentication system:

1. **JWT Header**: `X-GD-JWT` - GoDaddy-issued JWT token
2. **Site ID Header**: `X-GD-SITE-ID` - GoDaddy site identifier  
3. **Token Validation**: Supports both shopper and employee tokens
4. **Customer Matching**: Validates JWT customer ID against site configuration
5. **Caching**: 12-hour TTL key cache for performance

## Available Tools

The server provides 6 comprehensive tools for WordPress management:

### 1. Site Information Tool

**Tool ID**: `gd-mcp/get-site-info`  
**Namespace**: `GD\MCP\Tools\Site_Info_Tool`

Retrieves comprehensive information about the WordPress site including statistics, theme, and plugin data.

**Input Parameters**:
- `include_stats` (boolean): Include post/page statistics
- `include_theme_info` (boolean): Include active theme information  
- `include_plugin_count` (boolean): Include plugin count information

**Output Example**:
```json
{
  "site_name": "My WordPress Site",
  "site_url": "https://example.com", 
  "description": "Site tagline",
  "wordpress_version": "6.8",
  "stats": {
    "post_count": 42,
    "page_count": 7
  },
  "theme_info": {
    "name": "Twenty Twenty-Four",
    "version": "1.0",
    "author": "WordPress Team"
  },
  "plugin_count": 15
}
```

### 2. Get Post Tool

**Tool ID**: `gd-mcp/get-post`  
**Namespace**: `GD\MCP\Tools\Get_Post_Tool`

Retrieves a WordPress post by ID with optional meta data inclusion.

**Input Parameters**:
- `post_id` (integer, required): The ID of the post to retrieve
- `include_meta` (boolean, optional): Whether to include post meta data (default: true)

**Output Example**:
```json
{
  "id": 163,
  "title": "Sample Post Title", 
  "content": "<p>Post content here...</p>",
  "excerpt": "Post excerpt",
  "status": "publish",
  "post_type": "post",
  "author_id": 1,
  "date_created": "2024-12-15 10:30:00",
  "date_modified": "2024-12-15 11:00:00", 
  "slug": "sample-post-title",
  "meta": {
    "_yoast_wpseo_title": ["Custom SEO Title"],
    "_yoast_wpseo_metadesc": ["SEO Description"]
  }
}
```

### 3. Update Post Tool

**Tool ID**: `gd-mcp/update-post`  
**Namespace**: `GD\MCP\Tools\Update_Post_Tool`

Updates an existing WordPress post with new content, title, excerpt, or status.

**Input Parameters**:
- `post_id` (integer, required): The ID of the post to update
- `title` (string, optional): The new title for the post
- `content` (string, optional): The new content for the post
- `excerpt` (string, optional): The new excerpt for the post  
- `status` (string, optional): The post status (publish, draft, private, pending, future)

**Output Example**:
```json
{
  "success": true,
  "post_id": 163,
  "message": "Post updated successfully",
  "updated_fields": ["title", "content"]
}
```

### 4. Create Post Tool

**Tool ID**: `gd-mcp/create-post`  
**Namespace**: `GD\MCP\Tools\Create_Post_Tool`

Creates a new WordPress post, page, or custom post type with specified content.

**Input Parameters**:
- `title` (string, required): The title for the new post
- `post_type` (string, optional): The post type (defaults to "post")
- `content` (string, optional): The content for the new post
- `excerpt` (string, optional): The excerpt for the new post
- `status` (string, optional): The post status (defaults to "draft")

**Output Example**:
```json
{
  "success": true,
  "post_id": 164,
  "message": "Post created successfully"
}
```

### 5. Activate Plugin Tool

**Tool ID**: `gd-mcp/activate-plugin`  
**Namespace**: `GD\MCP\Tools\Activate_Plugin_Tool`

Installs and activates WordPress plugins from the WordPress.org repository by plugin slug.

**Input Parameters**:
- `plugin_slug` (string, required): The plugin slug from WordPress.org (e.g., "hello-dolly", "akismet")

**Output Example**:
```json
{
  "success": true,
  "message": "Plugin installed and activated successfully",
  "plugin": "hello-dolly",
  "version": "1.7.2"
}
```

**Capabilities**: Automatically handles installation if plugin doesn't exist, activation if already installed.

### 6. Deactivate Plugin Tool

**Tool ID**: `gd-mcp/deactivate-plugin`  
**Namespace**: `GD\MCP\Tools\Deactivate_Plugin_Tool`

Deactivates WordPress plugins with optional complete uninstall functionality.

**Input Parameters**:
- `plugin_slug` (string, required): The plugin slug to deactivate
- `uninstall` (boolean, optional): Whether to also uninstall the plugin after deactivation (default: false)

**Output Example**:
```json
{
  "success": true,
  "message": "Plugin deactivated successfully. Plugin uninstalled successfully",
  "plugin": "hello-dolly"
}
```

## Architecture

### File Structure

```
mcp-adapter-initializer/
├── mcp-adapter-initializer.php           # Main plugin file with server initialization
├── composer.json                         # Dependencies and scripts configuration  
├── phpcs.xml                            # PHP Code Sniffer configuration
├── includes/                            # Core functionality directory
│   ├── class-mcp-filters.php           # CORS and REST API configuration
│   └── tools/                          # Tool classes directory
│       ├── class-base-tool.php         # Abstract base class with admin switching
│       ├── class-site-info-tool.php    # Site information retrieval tool
│       ├── class-get-post-tool.php     # Post retrieval tool
│       ├── class-update-post-tool.php  # Post updating tool  
│       ├── class-create-post-tool.php  # Post creation tool
│       ├── class-activate-plugin-tool.php   # Plugin installation/activation tool
│       └── class-deactivate-plugin-tool.php # Plugin deactivation/uninstall tool
├── bin/                                # Development utilities
│   └── check-code-standards.sh        # Pre-commit hook for PHPCS
└── vendor/                            # Composer dependencies
    └── auth-contrib/php-auth-library/ # GoDaddy authentication library
```

### Core Classes

- **`MCP_Adapter_Initializer`**: Main singleton plugin class handling server setup and tool registration
- **`GD\MCP\Tools\Base_Tool`**: Abstract base class providing admin context switching for secure operations
- **`MCP_Filters`**: CORS configuration and REST API meta field exposure for Yoast SEO integration
- **Tool Classes**: Six specialized tool implementations extending Base_Tool

### Security Architecture

1. **Authentication Layer**: GoDaddy JWT validation with customer ID matching
2. **Admin Context**: Automatic privilege escalation to admin user for tool execution  
3. **Capability Checks**: WordPress permission validation per operation
4. **Input Sanitization**: All inputs sanitized using WordPress functions
5. **Error Handling**: Comprehensive error responses with debug logging

### Namespace Structure

All tools are organized under the `GD\MCP\Tools` namespace:
- Provides clear organizational structure
- Prevents naming conflicts
- Enables proper autoloading
- Maintains GoDaddy branding consistency

## Adding Custom Tools

### 1. Create Tool Class

Create a new file in the `includes/tools/` directory following the established pattern:

```php
// includes/tools/class-my-custom-tool.php
<?php
namespace GD\MCP\Tools;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once __DIR__ . '/class-base-tool.php';

class My_Custom_Tool extends Base_Tool {
    const TOOL_ID = 'gd-mcp/my-custom-tool';
    
    private static $instance = null;
    
    public static function get_instance(): My_Custom_Tool {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {}
    
    public function register(): void {
        wp_register_ability( 
            self::TOOL_ID, 
            array(
                'label'            => __( 'My Custom Tool', 'mcp-adapter-initializer' ),
                'description'      => __( 'Description of what this tool does', 'mcp-adapter-initializer' ),
                'input_schema'     => $this->get_input_schema(),
                'output_schema'    => $this->get_output_schema(),
                'execute_callback' => array( $this, 'execute_with_admin' ), // Uses base class admin switching
            ) 
        );
    }
    
    public function get_tool_id(): string {
        return self::TOOL_ID;
    }
    
    public function execute( array $input ): array {
        // Your tool implementation here
        // Current user is automatically switched to admin via Base_Tool
        
        // Permission check example
        if ( ! current_user_can( 'manage_options' ) ) {
            return array(
                'success' => false,
                'message' => __( 'Insufficient permissions', 'mcp-adapter-initializer' ),
            );
        }
        
        // Tool logic here
        return array( 
            'success' => true,
            'result' => 'Tool executed successfully'
        );
    }
    
    private function get_input_schema(): array {
        return array(
            'type'       => 'object',
            'properties' => array(
                'parameter_name' => array(
                    'type'        => 'string',
                    'description' => __( 'Parameter description', 'mcp-adapter-initializer' ),
                ),
            ),
            'required'   => array( 'parameter_name' ),
        );
    }
    
    private function get_output_schema(): array {
        return array(
            'type'       => 'object',
            'properties' => array(
                'success' => array(
                    'type'        => 'boolean',
                    'description' => 'Whether operation was successful',
                ),
                'result' => array(
                    'type'        => 'string',
                    'description' => 'Operation result',
                ),
            ),
        );
    }
    
    // Singleton protection
    private function __clone() {}
    public function __wakeup() {
        throw new \Exception( 'Cannot unserialize singleton' );
    }
}
```

### 2. Register Tool in Main Plugin

Add to the initializer's `load_dependencies()` method:

```php
require_once MCP_ADAPTER_INITIALIZER_PLUGIN_DIR . 'includes/tools/class-my-custom-tool.php';
```

Add to the `init_tools()` method:

```php
$this->tools['my_custom'] = \GD\MCP\Tools\My_Custom_Tool::get_instance();
```

### 3. Tool Development Guidelines

**Required Methods**:
- `register()`: Register the ability with WordPress using `wp_register_ability()`
- `get_tool_id()`: Return the unique tool identifier (format: `gd-mcp/tool-name`)
- `execute( array $input )`: Tool implementation (called via `execute_with_admin()`)

**Best Practices**:
- Extend `Base_Tool` for automatic admin context switching
- Use `execute_with_admin()` as the callback to ensure admin privileges  
- Implement proper permission checks using `current_user_can()`
- Sanitize all inputs using WordPress sanitization functions
- Return consistent output format with success/error indicators
- Use WordPress translation functions for all user-facing strings
- Follow singleton pattern for tool instantiation

## API Endpoints

Once configured, the MCP server exposes these streamable API endpoints:

```
Base URL: /wp-json/gd-mcp/v1/mcp/streamable

GET  /wp-json/gd-mcp/v1/mcp/streamable (tools/list method)
POST /wp-json/gd-mcp/v1/mcp/streamable (tools/call method)
```

### Authentication Headers

All requests must include:
```
X-GD-JWT: [GoDaddy JWT Token]
X-GD-SITE-ID: [GoDaddy Site ID]
```

### CORS Configuration

The server is configured to accept requests from:
- Origin: `https://host.godaddy.com`
- Methods: `GET, POST, PUT, OPTIONS`  
- Headers: `Content-Type, X-GD-JWT, X-GD-SITE-ID`

## Development

### Code Standards

This project follows WordPress Coding Standards with customizations for modern PHP development.

#### Development Setup

1. **Install development dependencies**:
   ```bash
   composer install --dev
   ```

2. **Check code standards**:
   ```bash
   # Run PHPCS to check for violations
   composer run phpcs
   
   # Run with summary report
   composer run phpcs:check
   
   # Use the pre-commit script
   ./bin/check-code-standards.sh
   ```

3. **Fix code standards automatically**:
   ```bash
   composer run phpcs:fix
   ```

#### PHPCS Configuration

- **Configuration**: `phpcs.xml`
- **Standards**: WordPress, PHPCompatibilityWP
- **PHP Compatibility**: 7.4+
- **Excluded Paths**: `vendor/`, `node_modules/`, minified files
- **Custom Rules**: 
  - Allows short array syntax (`[]`)
  - Relaxed comment requirements
  - Allows `error_log()` for debugging

#### Composer Scripts

Available composer scripts for development:

```bash
composer run phpcs        # Check code standards
composer run phpcs:fix    # Auto-fix code standards  
composer run phpcs:check  # Summary report
```

#### GitHub Actions

Automated code standards checking on:
- Push to `main` and `develop` branches
- Pull requests to `main` and `develop` branches

#### Pre-commit Hook Setup

Set up automatic code standards checking:

```bash
# Copy the script to git hooks
cp bin/check-code-standards.sh .git/hooks/pre-commit
chmod +x .git/hooks/pre-commit
```

### Debugging

#### WordPress Debug Mode

Enable WordPress debug logging:

```php
// wp-config.php
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
```

Check logs in `/wp-content/debug.log` for:
- Tool registration events
- Authentication attempts  
- Tool execution results
- GoDaddy auth library operations

#### Authentication Debug

Authentication errors are logged with details about:
- JWT validation failures
- Customer ID mismatches  
- Site ID validation issues
- Auth library exceptions

### Testing Tools via REST API

Test tools directly using cURL commands (this will only work from https://host.godaddy.com):

#### List Available Tools
```bash
curl -X POST "https://your-site.com/wp-json/gd-mcp/v1/mcp/streamable" \
  -H "Content-Type: application/json" \
  -H "X-GD-JWT: your-jwt-token" \
  -H "X-GD-SITE-ID: your-site-id" \
  -d '{
    "method": "tools/list",
    "params": {}
  }'
```

#### Get Site Information
```bash
curl -X POST "https://your-site.com/wp-json/gd-mcp/v1/mcp/streamable" \
  -H "Content-Type: application/json" \
  -H "X-GD-JWT: your-jwt-token" \
  -H "X-GD-SITE-ID: your-site-id" \
  -d '{
    "method": "tools/call",
    "params": {
      "name": "gd-mcp/get-site-info",
      "arguments": {
        "include_stats": true,
        "include_theme_info": true,
        "include_plugin_count": true
      }
    }
  }'
```

#### Get a Post by ID
```bash
curl -X POST "https://your-site.com/wp-json/gd-mcp/v1/mcp/streamable" \
  -H "Content-Type: application/json" \
  -H "X-GD-JWT: your-jwt-token" \
  -H "X-GD-SITE-ID: your-site-id" \
  -d '{
    "method": "tools/call",
    "params": {
      "name": "gd-mcp/get-post",
      "arguments": {
        "post_id": 1,
        "include_meta": true
      }
    }
  }'
```

#### Create a New Post
```bash
curl -X POST "https://your-site.com/wp-json/gd-mcp/v1/mcp/streamable" \
  -H "Content-Type: application/json" \
  -H "X-GD-JWT: your-jwt-token" \
  -H "X-GD-SITE-ID: your-site-id" \
  -d '{
    "method": "tools/call",
    "params": {
      "name": "gd-mcp/create-post",
      "arguments": {
        "title": "New Post via MCP",
        "content": "<p>This post was created via the MCP API.</p>",
        "status": "publish"
      }
    }
  }'
```

#### Update an Existing Post
```bash
curl -X POST "https://your-site.com/wp-json/gd-mcp/v1/mcp/streamable" \
  -H "Content-Type: application/json" \
  -H "X-GD-JWT: your-jwt-token" \
  -H "X-GD-SITE-ID: your-site-id" \
  -d '{
    "method": "tools/call",
    "params": {
      "name": "gd-mcp/update-post",
      "arguments": {
        "post_id": 1,
        "title": "Updated Post Title",
        "content": "<p>Updated post content</p>"
      }
    }
  }'
```

#### Install and Activate a Plugin
```bash
curl -X POST "https://your-site.com/wp-json/gd-mcp/v1/mcp/streamable" \
  -H "Content-Type: application/json" \
  -H "X-GD-JWT: your-jwt-token" \
  -H "X-GD-SITE-ID: your-site-id" \
  -d '{
    "method": "tools/call",
    "params": {
      "name": "gd-mcp/activate-plugin",
      "arguments": {
        "plugin_slug": "hello-dolly"
      }
    }
  }'
```

#### Deactivate and Uninstall a Plugin
```bash
curl -X POST "https://your-site.com/wp-json/gd-mcp/v1/mcp/streamable" \
  -H "Content-Type: application/json" \
  -H "X-GD-JWT: your-jwt-token" \
  -H "X-GD-SITE-ID: your-site-id" \
  -d '{
    "method": "tools/call",
    "params": {
      "name": "gd-mcp/deactivate-plugin",
      "arguments": {
        "plugin_slug": "hello-dolly",
        "uninstall": true
      }
    }
  }'
```

## License

This plugin is licensed under the GPL v2 or later.

## Support

For issues and questions:
- Review the documentation above
- Check the debug logs in `/wp-content/debug.log`
- Verify authentication headers are correctly set
- Ensure all required dependencies are installed and activated
- Check the open/closed issues in the repository
- Create an issue in the repository with detailed error information

## Changelog

### 0.1.0
- Initial release with comprehensive MCP server implementation
- **Transport**: Modern HTTP Streamable Transport for efficient real-time AI communication
- **Authentication**: GoDaddy JWT authentication with customer ID validation  
- **Tools**: 6 built-in tools for complete WordPress management
  - Site Information Tool: Comprehensive site data retrieval
  - Get Post Tool: Post retrieval with meta data support
  - Update Post Tool: Post content and status updates
  - Create Post Tool: New post/page/custom post type creation
  - Activate Plugin Tool: WordPress.org plugin installation and activation
  - Deactivate Plugin Tool: Plugin deactivation and uninstall
- **Architecture**: Base tool class with admin context switching
- **Security**: Permission-based access control and input sanitization
- **Integration**: CORS configuration for GoDaddy dashboard integration
- **Development**: Complete PHPCS configuration and pre-commit hooks
- **Namespace**: Organized `GD\MCP\Tools` namespace structure
