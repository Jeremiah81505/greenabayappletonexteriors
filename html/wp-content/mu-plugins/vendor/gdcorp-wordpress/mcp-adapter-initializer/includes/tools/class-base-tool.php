<?php
namespace GD\MCP\Tools;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class Base_Tool {
	/**
	 * Execute wrapper that sets admin user before calling the actual execute method
	 *
	 * @param array $input Input parameters
	 * @return array Execution result
	 */
	final public function execute_with_admin( array $input ): array {
		$this->set_admin_user();
		return $this->execute( $input );
	}

	/**
	 * Execute the tool - to be implemented by child classes
	 *
	 * @param array $input Input parameters
	 * @return array Execution result
	 */
	abstract public function execute( array $input ): array;

	/**
	 * Set the current user to an administrator for permission purposes
	 *
	 * @return void
	 */
	protected function set_admin_user(): void {
		$admins = get_users(
			array(
				'role'    => 'Administrator',
				'orderby' => 'ID',
				'order'   => 'ASC',
				'number'  => 1,
			)
		);

		if ( ! empty( $admins ) ) {
			wp_set_current_user( $admins[0]->ID );
		}
	}
}
