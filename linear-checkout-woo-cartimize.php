<?php
/**
 * Plugin Name: Linear Checkout for WooCommerce by Cartimize
 * Plugin URI: https://cartimize.com/
 * Description: The Checkout You Deserve - Backed by <strong><em>134 checkout-specific guidelines</em></strong> distilled from Baymard Institute‘s 61,000+ hours of large-scale e-commerce UX research - insights already used by leading e-commerce companies like Amazon, Nike, Walmart, Target, Google, Macy’s, Office Depot etc.
 * Version: 1.1.7
 * Author: Cartimize
 * Author URI: https://revmakx.com/
 * Developer: Cartimize
 * Developer URI: https://cartimize.com/
 * Text Domain: linear-checkout-for-woo-by-cartimize
 * Domain Path: /languages
 * WC requires at least: 3.5
 * WC tested up to: 5.1.0
 */

/**
 * Linear Checkout for WooCommerce by Cartimize
 * Copyright (c) 2020 Revmakx LLC
 * revmakx.com
 */

if(!defined( 'ABSPATH' )){ exit;}

define( 'CARTIMIZE_VERSION', '1.1.7' );
define( 'CARTIMIZE_MAIN_FILE', __FILE__ );
define( 'CARTIMIZE_PATH', plugin_dir_path( CARTIMIZE_MAIN_FILE ) );

use Cartimize\Cartimize;
use Cartimize\Admin\Admin;

require_once CARTIMIZE_PATH . 'vendor/autoload.php';
require_once CARTIMIZE_PATH . '/resources/php/constants.php';
require_once CARTIMIZE_PATH . '/resources/php/functions.php';
require_once CARTIMIZE_PATH . '/resources/php/template-hooks.php';
require_once CARTIMIZE_PATH . '/resources/php/template-core-functions.php';
require_once CARTIMIZE_PATH . 'includes/Utility/CartimizeException.php';

function cartimize_plugin_init() {

	global $cartimize;

	$cartimize = Cartimize::instance();
	if( ! defined( 'CARTIMIZE_BYPASS_TEMPLATE' ) || ! CARTIMIZE_BYPASS_TEMPLATE  ) {
		$cartimize->run();
	}
}

function cartimize_get_main() {
	return Cartimize::instance();
}

cartimize_plugin_init();

// Use the global instance
global $cartimize;

/**
 * Activation hook
 */
register_activation_hook( __FILE__, array( $cartimize, 'activation' ) );

/**
 * Deactivation hook
 */
register_deactivation_hook( __FILE__, array( $cartimize, 'deactivation' ) );

/*----------------------------------------------------------------------------*
 * Dashboard and Administrative Functionality
 *----------------------------------------------------------------------------*/

if ( is_admin() ) {
	global $cartimize_admin, $cartimize;

	$cartimize_admin = new Admin( $cartimize );
	$cartimize_admin->run();
}
