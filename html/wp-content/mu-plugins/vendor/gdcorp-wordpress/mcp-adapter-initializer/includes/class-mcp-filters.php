<?php

class MCP_Filters {

	/**
	 * Register hooks.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'white_list_cors_headers' ) );
		add_action( 'init', array( $this, 'expose_meta_fields_rest' ) );
	}

	/**
	 * Allow CORS for dashboard origin.
	 */
	public function white_list_cors_headers() {
		$allowed_origin = 'https://host.godaddy.com';

		// Bail if no Origin or not matching our allowed one
		if ( ! isset( $_SERVER['HTTP_ORIGIN'] ) || $_SERVER['HTTP_ORIGIN'] !== $allowed_origin ) {
			return;
		}

		// Common headers
		header( "Access-Control-Allow-Origin: $allowed_origin" );
		header( 'Access-Control-Allow-Methods: GET, POST, PUT, OPTIONS' );
		header( 'Access-Control-Allow-Credentials: true' );
		header( 'Access-Control-Allow-Headers: Content-Type, X-GD-JWT, X-GD-SITE-ID' );

		// Handle preflight request early and exit
		if ( 'OPTIONS' === $_SERVER['REQUEST_METHOD'] ) {
			status_header( 200 );
			exit;
		}
	}

	/**
	 * Expose meta fields in the REST API, so they can be edited via the MCP.
	 */
	public function expose_meta_fields_rest() {
		$meta_to_expose = array(
			'_yoast_wpseo_title',
			'_yoast_wpseo_metadesc',
		);

		// Get all registered post types
		$post_types = get_post_types( array( 'public' => true ), 'names' );
		unset( $post_types['attachment'] );

		foreach ( $post_types as $post_type ) {
			foreach ( $meta_to_expose as $meta_key ) {
				register_post_meta(
					$post_type,
					$meta_key,
					array(
						'show_in_rest' => true,
						'single'       => true,
						'type'         => 'string',
					)
				);
			}
		}
	}
}

new MCP_Filters();
