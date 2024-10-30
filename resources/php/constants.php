<?php
/**
 * Linear Checkout for WooCommerce by Cartimize
 * Copyright (c) 2020 Revmakx LLC
 * revmakx.com
 */

if(!defined('ABSPATH')){ exit; }

if(file_exists(CARTIMIZE_PATH.'_dev_config.php')){
	@include_once(CARTIMIZE_PATH.'_dev_config.php');
}

class CartimizeCheckoptConstants{

	public static  function init(){
		self::path();
		self::debug();
		self::general();
	}

	private static function define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}

	private static function debug(){

	}

	private static function general(){
		$plugin_slug = basename(dirname(dirname(dirname(__FILE__))));
		self::define( 'CARTIMIZE_PLUGIN_SLUG', $plugin_slug );
		if ( ! defined( 'CARTIMIZE_DEV' ) ) {
			// Dev Mode
			self::define( 'CARTIMIZE_DEV', getenv( 'CARTIMIZE_DEV' ) == 'true' ? true : false );
		}
	}

	private static function path(){

		self::define( 'CARTIMIZE_PLUGIN_URL', plugin_dir_url(CARTIMIZE_MAIN_FILE));
		self::define( 'CARTIMIZE_SLUG', 'cartimize_checkopt');
		self::define( 'CARTIMIZE_TEMPLATE_PATH', CARTIMIZE_PATH. 'templates' );
		self::define( 'CARTIMIZE_TEMPLATE_URL', CARTIMIZE_PLUGIN_URL. 'templates' );
		self::define( 'CARTIMIZE_SITE_URL', 'https://cartimize.com/' );
		self::define( 'CARTIMIZE_SERVICE_URL', CARTIMIZE_SITE_URL.'applogin/' );
		self::define( 'CARTIMIZE_SERVICE_REPORT_URL', 'https://service.cartimize.com/report/' );
		self::define( 'CARTIMIZE_MY_ACCOUNT_URL', CARTIMIZE_SITE_URL.'my-account/' );
		self::define( 'CARTIMIZE_SITE_LOST_PASS_URL', CARTIMIZE_SITE_URL.'my-account/lost-password/' );
		self::define( 'CARTIMIZE_GO_PRO_URL', CARTIMIZE_SITE_URL.'woocommerce-checkout-optimization/#buy-section' );
		self::define( 'CARTIMIZE_SUPPORT_DOC_URL', 'https://docs.cartimize.com/' );
		self::define( 'CARTIMIZE_HELP_TRACKING', '?utm_source=woo-domain&utm_medium=lcw-plugin&utm_campaign=lcw-plugin-co-page' );
		self::define( 'CARTIMIZE_LOGIN_PAGE_URL', admin_url( 'admin.php?page=cartimize-login' ) );
		self::define( 'CARTIMIZE_LOGIN_PAGE_URL_MUST_SIGNUP', admin_url( 'admin.php?page=cartimize-login&require_signup=1' ) );
		self::define( 'CARTIMIZE_SETTINGS_PAGE_URL', (admin_url( 'admin.php?page=wc-settings&tab=cartimize_checkopt_settings' )) );
		self::define( 'CARTIMIZE_ONBOARDING_PAGE_URL', (admin_url( 'admin.php?page=cartimize-pro-onboarding' )) );
		self::define( 'CARTIMIZE_PRO_SLUG', 'linear-checkout-for-woo-pro-by-cartimize/linear-checkout-for-woo-pro-by-cartimize.php' );
		self::define( 'CARTIMIZE_SERVICE_FEEDBACK_URL', 'https://service.cartimize.com/feedback/' );
		self::define( 'CARTIMIZE_REVIEW_STEP_SUPPORTED_PAYMENT', 'bacs,cheque,stripe_afterpay,ppcp-gateway,afterpay,ppec_paypal,cod,paypal' );
		self::define( 'CARTIMIZE_SKIP_LANGUAGE_PROMOTION', 'en_US,en_GB,en_ZA,en_CA,en_AU,en_NZ' );
	}
}

CartimizeCheckoptConstants::init();