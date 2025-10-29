<?php
/**
 * Create Post Tool Class
 *
 * @package     mcp-adapter-initializer
 * @author      GoDaddy
 * @copyright   2025 GoDaddy
 * @license     GPL-2.0-or-later
 */

namespace GD\MCP\Tools;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/class-base-tool.php';

/**
 * Create Post Tool
 *
 * Handles the registration and execution of the create post ability
 * for the MCP adapter.
 */
class Create_Post_Tool extends Base_Tool {
	/**
	 * Tool identifier
	 *
	 * @var string
	 */
	const TOOL_ID = 'gd-mcp/create-post';

	/**
	 * Tool instance
	 *
	 * @var Create_Post_Tool|null
	 */
	/**
	 * @var self|null
	 */
	private static $instance = null;

	/**
	 * Get singleton instance
	 *
	 * @return Create_Post_Tool
	 */

	/**
		* @return self
		*/
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Private constructor to prevent direct instantiation
	 */
	private function __construct() {}

	/**
	 * Register the create post ability
	 *
	 * @return void
	 */
	public function register(): void {
		if ( function_exists( 'wp_register_ability' ) ) {
			wp_register_ability(
				self::TOOL_ID,
				array(
					'label'            => __( 'Create Post', 'mcp-adapter-initializer' ),
					'description'      => __( 'Creates a new WordPress post, page, or custom post type', 'mcp-adapter-initializer' ),
					'input_schema'     => $this->get_input_schema(),
					'output_schema'    => $this->get_output_schema(),
					'execute_callback' => array( $this, 'execute_with_admin' ),
				)
			);
		}
	}

	/**
	 * Get the tool identifier
	 *
	 * @return string
	 */
	public function get_tool_id(): string {
		return self::TOOL_ID;
	}

	/**
	 * Get input schema for the tool
	 *
	 * @return array
	 */
	private function get_input_schema(): array {
		return array(
			'type'       => 'object',
			'properties' => array(
				'post_type' => array(
					'type'        => 'string',
					'description' => __( 'The post type (post, page, or custom post type). Defaults to post.', 'mcp-adapter-initializer' ),
				),
				'title'     => array(
					'type'        => 'string',
					'description' => __( 'The title for the new post', 'mcp-adapter-initializer' ),
				),
				'content'   => array(
					'type'        => 'string',
					'description' => __( 'The content for the new post', 'mcp-adapter-initializer' ),
				),
				'excerpt'   => array(
					'type'        => 'string',
					'description' => __( 'The excerpt for the new post', 'mcp-adapter-initializer' ),
				),
				'status'    => array(
					'type'        => 'string',
					'description' => __( 'The post status (publish, draft, private, etc.)', 'mcp-adapter-initializer' ),
					'enum'        => array( 'publish', 'draft', 'private', 'pending', 'future' ),
				),
			),
			'required'   => array( 'title' ),
		);
	}

	/**
	 * Get output schema for the tool
	 *
	 * @return array
	 */
	private function get_output_schema(): array {
		return array(
			'type'       => 'object',
			'properties' => array(
				'success' => array(
					'type'        => 'boolean',
					'description' => 'Whether the creation was successful',
				),
				'post_id' => array(
					'type'        => 'integer',
					'description' => 'The new post ID',
				),
				'message' => array(
					'type'        => 'string',
					'description' => 'Success or error message',
				),
			),
		);
	}

	/**
	 * Execute the create post tool
	 *
	 * @param array $input Input parameters
	 * @return array Creation result or error
	 */
	public function execute( array $input ): array {
		if ( empty( $input['title'] ) ) {
			return array(
				'success' => false,
				'message' => __( 'Title is required', 'mcp-adapter-initializer' ),
			);
		}

		$post_type = isset( $input['post_type'] ) && '' !== $input['post_type'] ? sanitize_key( $input['post_type'] ) : 'post';
		if ( ! post_type_exists( $post_type ) ) {
			return array(
				'success' => false,
				'message' => sprintf( __( 'Post type %s does not exist', 'mcp-adapter-initializer' ), $post_type ),
			);
		}

		if ( ! current_user_can( 'publish_posts' ) ) {
			return array(
				'success' => false,
				'message' => __( 'You do not have permission to create posts', 'mcp-adapter-initializer' ),
			);
		}

		$post_data = array(
			'post_type'    => $post_type,
			'post_title'   => sanitize_text_field( $input['title'] ),
			'post_content' => isset( $input['content'] ) ? wp_kses_post( $input['content'] ) : '',
			'post_excerpt' => isset( $input['excerpt'] ) ? sanitize_textarea_field( $input['excerpt'] ) : '',
			'post_status'  => isset( $input['status'] ) ? $input['status'] : 'draft',
		);

		$post_id = wp_insert_post( $post_data, true );

		if ( is_wp_error( $post_id ) ) {
			return array(
				'success' => false,
				'message' => sprintf( __( 'Failed to create post: %s', 'mcp-adapter-initializer' ), $post_id->get_error_message() ),
			);
		}

		return array(
			'success' => true,
			'post_id' => $post_id,
			'message' => __( 'Post created successfully', 'mcp-adapter-initializer' ),
		);
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
