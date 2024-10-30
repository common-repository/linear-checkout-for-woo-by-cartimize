<?php 
/**
 * Linear Checkout for WooCommerce by Cartimize
 * Copyright (c) 2020 Revmakx LLC
 * revmakx.com
 */

namespace Cartimize\Core;

use Cartimize\Cartimize;
use Cartimize\Utility\SSLChecker;

Class TemplateRedirect{

	private static $settings_controller;

	public static function checkout_page($template_controller, $settings_controller){
		self::$settings_controller = $settings_controller;

		if ( apply_filters( 'cartimize_load_checkout_template', Cartimize::is_cartimize_checkout() ) ) {

			self::suppress_errors();
			self::disable_caching();


			wc_maybe_define_constant( 'WOOCOMMERCE_CHECKOUT', true );
			WC()->payment_gateways();
			WC()->shipping();
			if ( WC()->cart->is_empty() && ! is_customize_preview() && apply_filters( 'woocommerce_checkout_redirect_empty_cart', true ) ) {
				wc_add_notice( __( 'Checkout is not available whilst your cart is empty.', 'woocommerce' ), 'notice' );
				wp_redirect( wc_get_cart_url() );
				exit;
			}

			do_action( 'woocommerce_check_cart_items' );
			WC()->cart->calculate_totals();  

			do_action( 'cartimize_checkout_loaded_pre_head' ); 
			self::suppress_assets();
			self::header($template_controller);
			if ( empty( $_POST ) && wc_notice_count( 'error' ) > 0 ) { // WPCS: input var ok, CSRF ok.
				cartimize_cart_error_html();
				wc_clear_notices();
				exit;

			}
			self::body($template_controller, 'content.php');
			self::footer( $template_controller );


			exit;
		}
	}

	public static function header($template_controller){
		?>
		<!DOCTYPE html>
		<html <?php language_attributes(); ?>>
		<head>
			<?php self::wp_head()?>
		</head>
		<body class="woocommerce default checkout woocommerce-checkout">
			<div id="clc-container">
		<?php
		$template_controller->get_active_template()->view( 'header.php' );
		do_action( 'cartimize_after_header' );
		
	}

	public static function body( $templates_manager, $template_file, $parameters = array() ) {
		$templates_manager->get_active_template()->view( $template_file, $parameters);
	}

	public static function wp_head(){
		WC()->payment_gateways->get_available_payment_gateways();
		\WC_Payment_Gateways::instance();

		wp_head();
		self::meta_tags();
		self::wp_get_document_title();
		self::wp_styles();
		do_action( 'cartimize_wp_head' );
	}

	public static function wp_get_document_title() {
		echo '<title>' . esc_html(wp_get_document_title()) . '</title>' . "\n";
	}

	public static function wp_styles() {
		wp_print_styles();
	}

	public static function meta_tags(){
		?>
		<meta charset="<?php bloginfo( 'charset' ); ?>">
		<meta name="viewport" content="width=device-width, initial-scale=1.0, height=device-height, minimum-scale=1.0, user-scalable=0">
		<?php
	}

	public static function remove_styles() {
		$blocked_style_handles = apply_filters( 'cartimize_remove_style_handles', array() );

		foreach ( $blocked_style_handles as $blocked_style_handle ) {
			wp_dequeue_style( $blocked_style_handle );
			wp_deregister_style( $blocked_style_handle );
		}
	}

	public static function remove_scripts() {
		$blocked_script_handles = apply_filters( 'cartimize_remove_script_handles', array() );

		foreach ( $blocked_script_handles as $blocked_script_handle ) {
			wp_dequeue_script( $blocked_script_handle );
			wp_deregister_script( $blocked_script_handle );
		}
	}

	public static function footer( $templates_manager ) {

		$templates_manager->get_active_template()->view( 'footer.php' );
		
		do_action( 'cartimize_wp_footer_before_scripts' );

		// Prevent themes and plugins from injecting HTML on wp_footer
		echo "</div>";
		echo '<div id="wp_footer">';
		wp_footer();
		echo '</div>';


		do_action( 'cartimize_wp_footer' );
		?>
		</body>
		</html>
		<?php
	}

	public static function output_custom_styles(){
		$settings_controller = Cartimize::instance()->get_settings_controller();
		if ( $settings_controller->get_setting( 'additional_css_enable' ) == '1' ) :
			wp_add_inline_style("cartimize_front_template_css", $settings_controller->get_setting( 'additional_css' ));
		endif;
	}

	public static function suppress_errors() {
		/**
		 * PHP Warning / Notice Suppression
		 */
		if ( ! defined( 'CARTIMIZE_DEV' ) || ! CARTIMIZE_DEV ) {
			ini_set( 'display_errors', 'Off' );
		}
	}

	public static function disable_caching() {
		header( 'Cache-Control: no-store, no-cache, must-revalidate, max-age=0' );
		header( 'Cache-Control: post-check=0, pre-check=0', false );
		header( 'Pragma: no-cache' );
	}

	public static function suppress_assets() {
		add_action( 'wp_head', array( 'Cartimize\Core\TemplateRedirect', 'remove_styles' ), 100000 );
		add_action( 'wp_head', array( 'Cartimize\Core\TemplateRedirect', 'remove_scripts' ), 100000 );
		add_action( 'wp_enqueue_scripts', array( 'Cartimize\Core\TemplateRedirect', 'remove_styles' ), 100000 );
		add_action( 'wp_enqueue_scripts', array( 'Cartimize\Core\TemplateRedirect', 'remove_scripts' ), 100000 );
		add_action( 'wp_enqueue_scripts', array( 'Cartimize\Core\TemplateRedirect', 'output_custom_styles' ), 40, 5 );
		add_action( 'wp_footer', array( 'Cartimize\Core\TemplateRedirect', 'remove_styles' ), 19 ); // 20 is when footer scripts are output
		add_action( 'wp_footer', array( 'Cartimize\Core\TemplateRedirect', 'remove_scripts' ), 19 ); // 20 is 
	}
}