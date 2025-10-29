<?php
/*
Plugin Name: elementor-pro-security-patch
Description: Plugin Patch that resolves the vulnerability to redirect visitors to malicious domains or upload backdoors to the breached site.
Version: 1.0.0
*/

function patch_update_option() {
    $requests = [];

    if ( ! empty( $_REQUEST['actions'] ) ) {
        $requests = json_decode( wp_unslash( $_REQUEST['actions'] ), true );
    }

    foreach ( $requests as $id => $action_data ) {
        if (  $action_data['action'] == "pro_woocommerce_update_page_option" ) {
            $is_admin = current_user_can( 'manage_options' );
            $is_shop_manager = current_user_can( 'manage_woocommerce' );
            $is_allowed = $is_admin || $is_shop_manager;

            if ( ! $is_allowed ) {
                exit;
            }
        }
    }
}
add_action('wp_ajax_elementor_ajax', 'patch_update_option');

