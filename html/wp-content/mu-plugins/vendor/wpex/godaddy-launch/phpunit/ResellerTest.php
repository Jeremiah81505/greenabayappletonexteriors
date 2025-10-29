<?php
/**
 * Test the Reseller environment.
 *
 * @package GoDaddy_Launch
 */

namespace GoDaddy\WordPress\Plugins\Launch\Tests;

/**
 * Tests the Reseller condition for the plugin.
 */
class Reseller_Test extends \WP_UnitTestCase {

	/**
	 * Test that the plugin does not load when is a reseller.
	 */
	public function test_plugin_does_not_load_for_reseller() {
		define( 'configData', json_encode( array( 'GD_RESELLER' => '123' ) ) ); // phpcs:ignore Generic.NamingConventions.UpperCaseConstantName.ConstantNotUpperCase

		$actions_before = count( $GLOBALS['wp_filter']['plugins_loaded']->callbacks ?? array() );

		require_once dirname( dirname( __FILE__ ) ) . '/godaddy-launch.php';

		$actions_after = count( $GLOBALS['wp_filter']['plugins_loaded']->callbacks ?? array() );

		$this->assertEquals( $actions_before, $actions_after, 'Plugin should not register any hooks when reseller condition is met' );
	}

}
