<?php
/**
 * MCP Adapter Initializer
 *
 * @package     mcp-adapter-initializer
 * @author      GoDaddy
 * @copyright   2025 GoDaddy
 * @license     GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       MCP Adapter Initializer
 * Plugin URI:        https://github.com/WordPress/mcp-adapter
 * Description:       Initialize a custom MCP server with custom tools and authentication.
 * Requires at least: 6.8
 * Version:           0.1.1
 * Requires PHP:      7.4
 * Author:            GoDaddy
 * Author URI:        https://www.godaddy.com
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * Text Domain:       mcp-adapter-initializer
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$autoloader = __DIR__ . '/vendor/autoload.php';

if ( file_exists( $autoloader ) ) {
	require $autoloader;
}

use GoDaddy\Auth\AuthKeyFileCache;
use GoDaddy\Auth\AuthManager;

// Define plugin constants
define( 'MCP_ADAPTER_INITIALIZER_VERSION', '0.1.1' );
define( 'MCP_ADAPTER_INITIALIZER_PLUGIN_FILE', __FILE__ );
define( 'MCP_ADAPTER_INITIALIZER_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'MCP_ADAPTER_INITIALIZER_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Main plugin class for MCP Adapter Initializer
 */
class MCP_Adapter_Initializer {

	/**
	 * Plugin instance
	 *
	 * @var MCP_Adapter_Initializer|null
	 */
	private static $instance = null;

	/**
	 * Server ID
	 *
	 * @var string
	 */
	private $server_id = 'gd-mcp';

	/**
	 * API namespace
	 *
	 * @var string
	 */
	private $api_namespace = 'gd-mcp/v1';

	/**
	 * API route
	 *
	 * @var string
	 */
	private $api_route = 'mcp';

	/**
	 * Plugin constructor
	 */
	private function __construct() {
		$this->init();
	}

	/**
	 * Get singleton instance
	 *
	 * @return MCP_Adapter_Initializer
	 */
	public static function get_instance(): MCP_Adapter_Initializer {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Available tools
	 *
	 * @var array
	 */
	private $tools = array();

	/**
	 * Initialize the plugin
	 */
	private function init(): void {
		// Check if the MCP Adapter class isn't already loaded by another plugin
		if ( ! class_exists( 'WP\MCP\Core\McpAdapter' ) ) {
			$this->load_mcp_adapter_dependencies();
		}

		// Load dependencies
		$this->load_dependencies();

		// Initialize tools
		$this->init_tools();

		// Hook into WordPress
		add_action( 'abilities_api_init', array( $this, 'register_abilities' ) );
		add_action( 'mcp_adapter_init', array( $this, 'initialize_mcp_server' ) );

		add_filter( 'gdl_unrestricted_rest_endpoints', array( $this, 'add_unrestricted_endpoints' ) );
	}

	/**
	 * Load MCP Adapter dependencies if not already loaded
	 */
	private function load_mcp_adapter_dependencies(): void {
		// Try to load from standalone plugin first.
		$wp_content_adapter = WP_CONTENT_DIR . '/plugins/mcp-adapter/mcp-adapter.php';
		if ( file_exists( $wp_content_adapter ) ) {
			require_once $wp_content_adapter;
			return;
		}

		// Fallback: load from mu-plugins/gd-system-plugin/vendor/wordpress/mcp-adapter/mcp-adapter.php
		$mu_vendor_adapter = WPMU_PLUGIN_DIR . '/gd-system-plugin/vendor/wordpress/mcp-adapter/mcp-adapter.php';
		if ( file_exists( $mu_vendor_adapter ) ) {
			require_once $mu_vendor_adapter;
		}
	}

	/**
	 * Load required dependencies
	 */
	private function load_dependencies(): void {
		// Load tool classes
		require_once MCP_ADAPTER_INITIALIZER_PLUGIN_DIR . 'includes/tools/class-site-info-tool.php';
		require_once MCP_ADAPTER_INITIALIZER_PLUGIN_DIR . 'includes/tools/class-get-post-tool.php';
		require_once MCP_ADAPTER_INITIALIZER_PLUGIN_DIR . 'includes/tools/class-update-post-tool.php';
		require_once MCP_ADAPTER_INITIALIZER_PLUGIN_DIR . 'includes/tools/class-create-post-tool.php';
		require_once MCP_ADAPTER_INITIALIZER_PLUGIN_DIR . 'includes/tools/class-activate-plugin-tool.php';
		require_once MCP_ADAPTER_INITIALIZER_PLUGIN_DIR . 'includes/tools/class-deactivate-plugin-tool.php';

		// Filters
		require_once MCP_ADAPTER_INITIALIZER_PLUGIN_DIR . 'includes/class-mcp-filters.php';
	}

	/**
	 * Initialize tools
	 */
	private function init_tools(): void {
		$this->tools['site_info']         = \GD\MCP\Tools\Site_Info_Tool::get_instance();
		$this->tools['get_post']          = \GD\MCP\Tools\Get_Post_Tool::get_instance();
		$this->tools['update_post']       = \GD\MCP\Tools\Update_Post_Tool::get_instance();
		$this->tools['create_post']       = \GD\MCP\Tools\Create_Post_Tool::get_instance();
		$this->tools['activate_plugin']   = \GD\MCP\Tools\Activate_Plugin_Tool::get_instance();
		$this->tools['deactivate_plugin'] = \GD\MCP\Tools\Deactivate_Plugin_Tool::get_instance();
	}

	/**
	 * Register plugin abilities
	 */
	public function register_abilities(): void {
		// Register all tools
		foreach ( $this->tools as $tool ) {
			if ( method_exists( $tool, 'register' ) ) {
				$tool->register();
			}
		}
	}

	/**
	 * Initialize MCP server
	 *
	 * @param \WP\MCP\Core\McpAdapter $adapter MCP adapter instance
	 */
	public function initialize_mcp_server( $adapter ): void {
		$adapter->create_server(
			$this->server_id,
			$this->api_namespace,
			$this->api_route,
			__( 'MCP Server', 'mcp-adapter-initializer' ),
			__( 'An MCP server for executing tools.', 'mcp-adapter-initializer' ),
			MCP_ADAPTER_INITIALIZER_VERSION,
			$this->get_transport_methods(),
			$this->get_error_handler(),
			null,
			$this->get_exposed_abilities(),
			array(), // Resources
			array(), // Prompts
			array( $this, 'authenticate_request' )
		);
	}

	/**
	 * Add unrestricted endpoints for MCP
	 *
	 * @param array $endpoints Existing unrestricted endpoints
	 *
	 * @return array Modified endpoints
	 */
	public function add_unrestricted_endpoints( $endpoints ) {

		$endpoints[] = '/gd-mcp/v1';

		return $endpoints;
	}

	/**
	 * Get transport methods
	 *
	 * @return array
	 */
	private function get_transport_methods(): array {
		return array(
			\WP\MCP\Transport\Http\StreamableTransport::class,
		);
	}

	/**
	 * Get error handler class
	 *
	 * @return string
	 */
	private function get_error_handler(): string {
		return \WP\MCP\Infrastructure\ErrorHandling\ErrorLogMcpErrorHandler::class;
	}

	/**
	 * Get abilities to expose as tools
	 *
	 * @return array
	 */
	private function get_exposed_abilities(): array {
		$abilities = array();

		// Get tool IDs from all registered tools
		foreach ( $this->tools as $tool ) {
			if ( method_exists( $tool, 'get_tool_id' ) ) {
				$abilities[] = $tool->get_tool_id();
			}
		}

		return $abilities;
	}

	/**
	 * Authenticate MCP requests with a JWT in the X-GD-JWT header
	 *
	 * @return bool Whether request is authenticated
	 */
	public function authenticate_request(): bool {
		// Get the custom JWT header from the request
		$jwt     = isset( $_SERVER['HTTP_X_GD_JWT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_GD_JWT'] ) ) : null;
		$site_id = isset( $_SERVER['HTTP_X_GD_SITE_ID'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_GD_SITE_ID'] ) ) : null;

		if ( empty( $jwt ) || empty( $site_id ) ) {
			return false;
		}

		try {
			// Initialize the auth manager with cache and app code
			$upload_dir   = function_exists( 'wp_upload_dir' ) ? wp_upload_dir() : array( 'basedir' => sys_get_temp_dir() );
			$cache_dir    = $upload_dir['basedir'] . '/gd-auth-cache';
			$cache        = new AuthKeyFileCache( $cache_dir, 60 * 60 * 12 ); // 12 hour TTL
			$auth_manager = new AuthManager( null, $cache, 'gd-mcp' );

			// Try to validate as shopper token (most common case).
			$auth_host = 'sso.godaddy.com';
			$payload   = $auth_manager->getAuthPayloadShopper( $auth_host, $jwt, null, 1 );

			// If shopper validation fails, try employee token.
			if ( ! $payload ) {
				$payload = $auth_manager->getAuthPayloadEmployee( $auth_host, $jwt, 1 );
			}

			// Return true if we got a valid payload
			if ( null === $payload ) {
				return false;
			}

			return $this->validate_jwt( $jwt, $site_id );
		} catch ( Exception $e ) {
			error_log( 'MCP authentication failed: ' . $e->getMessage() );
			return false;
		}
	}

	/**
	 * Validate that the JWT belongs to the correct customer.
	 *
	 * @param string $jwt     The JWT token.
	 * @param string $site_id The site ID to validate against.
	 *
	 * @return boolean
	 */
	public function validate_jwt( $jwt, $site_id ): bool {

		$parts = explode( '.', $jwt );

		if ( count( $parts ) !== 3 ) {

			return false;

		}

		$payload = base64_decode( strtr( $parts[1], '-_', '+/' ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions -- Needed for JWT decoding.

		$payload_customer_id = json_decode( $payload, true )['cid'] ?? null;

		if ( null === $payload_customer_id ) {

			return false;

		}

		$config_data        = defined( 'configData' ) ? json_decode( constant( 'configData' ), true ) : array();
		$config_customer_id = isset( $config_data['GD_CUSTOMER_ID'] ) ? $config_data['GD_CUSTOMER_ID'] : ( defined( 'GD_CUSTOMER_ID' ) ? GD_CUSTOMER_ID : null );
		$config_site_id     = isset( $config_data['GD_ACCOUNT_UID'] ) ? $config_data['GD_ACCOUNT_UID'] : ( defined( 'GD_ACCOUNT_UID' ) ? GD_ACCOUNT_UID : null );

		return ( $config_customer_id === $payload_customer_id && $config_site_id === $site_id );
	}

	/**
	 * Prevent cloning
	 */
	private function __clone() {}

	/**
	 * Prevent unserialization
	 */
	public function __wakeup() {
		throw new \Exception( 'Cannot unserialize singleton' );
	}
}

// Initialize the plugin
MCP_Adapter_Initializer::get_instance();
