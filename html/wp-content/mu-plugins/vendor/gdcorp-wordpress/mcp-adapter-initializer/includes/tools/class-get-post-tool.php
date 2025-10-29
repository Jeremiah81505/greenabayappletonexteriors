<?php
/**
 * Get Post Tool Class
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
 * Get Post Tool
 *
 * Handles the registration and execution of the get post ability
 * for the MCP adapter.
 */
class Get_Post_Tool extends Base_Tool {

	/**
	 * Tool identifier
	 *
	 * @var string
	 */
	const TOOL_ID = 'gd-mcp/get-post';

	/**
	 * Tool instance
	 *
	 * @var Get_Post_Tool|null
	 */
	private static $instance = null;

	/**
	 * Get singleton instance
	 *
	 * @return Get_Post_Tool
	 */
	public static function get_instance(): Get_Post_Tool {
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
	 * Register the get post ability
	 *
	 * @return void
	 */
	public function register(): void {
		wp_register_ability(
			self::TOOL_ID,
			array(
				'label'            => __( 'Get Post', 'mcp-adapter-initializer' ),
				'description'      => __( 'Retrieves a WordPress post by its ID', 'mcp-adapter-initializer' ),
				'input_schema'     => $this->get_input_schema(),
				'output_schema'    => $this->get_output_schema(),
				'execute_callback' => array( $this, 'execute_with_admin' ),
			)
		);
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
				'post_id'      => array(
					'type'        => 'integer',
					'description' => __( 'The ID of the post to retrieve', 'mcp-adapter-initializer' ),
					'minimum'     => 1,
				),
				'include_meta' => array(
					'type'        => 'boolean',
					'description' => __( 'Whether to include post meta data', 'mcp-adapter-initializer' ),
					'default'     => true,
				),
			),
			'required'   => array( 'post_id' ),
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
				'id'            => array(
					'type'        => 'integer',
					'description' => 'The post ID',
				),
				'title'         => array(
					'type'        => 'string',
					'description' => 'The post title',
				),
				'content'       => array(
					'type'        => 'string',
					'description' => 'The post content',
				),
				'excerpt'       => array(
					'type'        => 'string',
					'description' => 'The post excerpt',
				),
				'status'        => array(
					'type'        => 'string',
					'description' => 'The post status (publish, draft, etc.)',
				),
				'post_type'     => array(
					'type'        => 'string',
					'description' => 'The post type',
				),
				'author_id'     => array(
					'type'        => 'integer',
					'description' => 'The post author ID',
				),
				'date_created'  => array(
					'type'        => 'string',
					'description' => 'The post creation date',
				),
				'date_modified' => array(
					'type'        => 'string',
					'description' => 'The post modification date',
				),
				'slug'          => array(
					'type'        => 'string',
					'description' => 'The post slug',
				),
				'meta'          => array(
					'type'        => 'object',
					'description' => 'Post meta data (if requested)',
				),
			),
		);
	}

	/**
	 * Execute the get post tool
	 *
	 * @param array $input Input parameters
	 * @return array Post information or error
	 */
	public function execute( array $input ): array {
		// Use a default post ID if none provided (for testing)
		$post_id = ! empty( $input['post_id'] ) ? (int) $input['post_id'] : 1;
		$post    = get_post( $post_id );

		// If post doesn't exist, return a placeholder
		if ( ! $post ) {
			return array(
				'id'            => $post_id,
				'title'         => 'Post not found',
				'content'       => '',
				'excerpt'       => '',
				'status'        => 'not_found',
				'post_type'     => 'post',
				'author_id'     => 0,
				'date_created'  => '',
				'date_modified' => '',
				'slug'          => '',
				'meta'          => array(), // Always include meta field
			);
		}

		// Prepare result
		$result = array(
			'id'            => $post->ID,
			'title'         => $post->post_title,
			'content'       => $post->post_content,
			'excerpt'       => $post->post_excerpt,
			'status'        => $post->post_status,
			'post_type'     => $post->post_type,
			'author_id'     => (int) $post->post_author,
			'date_created'  => $post->post_date,
			'date_modified' => $post->post_modified,
			'slug'          => $post->post_name,
			'meta'          => array(), // Always include meta field, even if empty
		);

		// Add meta data if requested
		if ( ! empty( $input['include_meta'] ) ) {
			$result['meta'] = get_post_meta( $post_id );
		}

		return $result;
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
