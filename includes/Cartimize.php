<?php 
/**
 * Linear Checkout for WooCommerce by Cartimize
 * Copyright (c) 2020 Revmakx LLC
 * revmakx.com
 */

namespace Cartimize;
use Cartimize\Base\Singleton;
use Cartimize\Core\TemplateRedirect;
use Cartimize\Core\FrontEndElement;
use Cartimize\Controllers\TemplatesController;
use Cartimize\Controllers\AjaxController;
use Cartimize\Controllers\SettingsController;
use Cartimize\Controllers\ActivationController;
use Cartimize\Actions\LogInAction;
use Cartimize\Actions\UpdateCheckoutAction;
use Cartimize\Actions\UpdatePaymentMethodAction;
use Cartimize\Actions\ApplyCouponAction;
use Cartimize\Actions\RemoveCouponAction;
use Cartimize\Actions\CompleteOrderAction;
use Cartimize\Actions\TermsAndPrivacyAction;
use Cartimize\Compatibility\Compatibility;
use Cartimize\Core\Form;
use Cartimize\Stats\AnonymousDataCollection;
use Cartimize\License\License;

class Cartimize extends Singleton{

	private $template_controller;
	private $ajax_manager;
	private $settings_controller;
	private $license_controller;
	private $activation_controller;
	private $license_instance;
	private $form;
	private $version;
	private $frontend_controller;
	

	public function __construct() {
		// Program Details
		$this->version     = CARTIMIZE_VERSION;
	}

	/**
	 * @since 2.0.0
	 * @access public
	 * @return Form The form object
	 */
	public function get_form() {
		return $this->form;
	}
	
	public function run(){
		add_action( 'cartimize_refresh_license_controller', array($this, 'init_license_controller'), 10);
		$this->create_objects();
		add_action( 'plugins_loaded', array( $this, 'add_action_hooks' ), 1 );
		add_action( 'init', array( $this, 'init' ), 1 );
	}

	public function create_objects(){
		$this->activation_controller = new ActivationController( $this );
		$this->settings_controller = new SettingsController();
		$this->init_license_controller();
		$this->license_controller = new SettingsController( '', '_license' );
		$this->frontend_controller = new FrontEndElement();
		$this->stat_collection = new AnonymousDataCollection( $this->settings_controller, $this->license_controller );
		$this->license_instance = new License( $this );

	}

	public function add_action_hooks(){

		// Handle the Activation notices
		add_action(
			'admin_notices', function() {
				$this->get_activation_controller()->check_all_requirements(true);
			}
		);
		
		if ($this->is_enabled()) {
			$this->ajax_manager = new AjaxController( $this->get_ajax_actions() );
			$this->configure_objects();
			/**
			 * Override some WooCommerce Options
			 */
			// WooCommerce - Registration Generate Username
			add_filter( 'pre_option_woocommerce_registration_generate_username', array( $this, 'override_woocommerce_registration_generate_username' ), 10, 1 );

			add_filter( 'woocommerce_coupons_enabled', array($this, 'modify_woocommerce_coupons_enabled'), 10, 1);

			add_action('woocommerce_add_to_cart_redirect', array($this, 'checkout_redirect'));

			add_filter( 'cartimize_header_logo', array($this, 'cartimize_header_logo_div'), 10, 1);

			new Compatibility();
			$this->set_license_check_cron();
		}
		add_action( 'cartimize_setting_page', array( $this, 'feedback_integration' ), 10 );
		add_action('cartimize_before_setting_page', array($this, 'setup_custom_logo'));
		add_action( 'wp', array( $this, 'init_hooks' ), 1 );
	}

	public function init_hooks(){
		if ( $this->is_enabled() && ( ! defined( 'CARTIMIZE_BYPASS_TEMPLATE' ) || ! CARTIMIZE_BYPASS_TEMPLATE ) ) {

			$this->form = new Form();
			if ( apply_filters( 'cartimize_load_checkout_template', Cartimize::is_cartimize_checkout() ) ) {
				$this->load_actions();
			}
		}
	}

	public function init(){
		
		$this->load_plugin_textdomain();
		$this->verify_setting();
	}

	public function load_plugin_textdomain(){
		load_plugin_textdomain(
			'linear-checkout-for-woo-by-cartimize',
			false,
			dirname( plugin_basename( CARTIMIZE_MAIN_FILE ) ) . '/languages'
		);
	}

	public function verify_setting(){
		$site_token = $this->get_license_controller()->get_setting( 'site_token' );
		$skip_signup = $this->get_license_controller()->get_setting( 'skip_signup' );
		if ( $site_token || $skip_signup) {
			$this->get_settings_controller()->add_setting('shipping_methods_order', 'recommend');
		}
		do_action( 'cartimize_init_setting_action');
	}

	private function load_actions() {
		add_action( 'wp_enqueue_scripts', array( $this, 'load_assets' ), 11 ); 
		$this->create_template_object();
		add_action( 'template_redirect', array( $this, 'cartimize_template_redirect' ), 11 );
	}

	private function configure_objects() {
		$this->ajax_manager->load_all();
	}

	public function get_ajax_actions() {
		// Setting no_privilege to false because wc_ajax doesn't have a no privilege endpoint.
		return apply_filters('cartimize_ajax_actions', array(
			new LogInAction( 'login', false, 'wc_ajax_' ),
			new CompleteOrderAction( 'complete_order', false, 'wc_ajax_' ),
			new ApplyCouponAction( 'cartimize_apply_coupon', false, 'wc_ajax_' ),
			new RemoveCouponAction( 'cartimize_remove_coupon', false, 'wc_ajax_' ),
			new UpdateCheckoutAction( 'update_order_review', false, 'wc_ajax_' ),
			new UpdatePaymentMethodAction( 'update_payment_method', false, 'wc_ajax_' ),
			new TermsAndPrivacyAction( 'terms_link', false, 'wc_ajax_' ),
		));
	}
	public function is_enabled() {
		$result = true;
		
		if ( ! function_exists( 'WC' ) && $this->activation_controller->check_min_version_requirements( false ) != false ) {
			$result = false; // superfluous, but sure
		}
		
		$queries = array();
		if (isset($_SERVER['HTTP_REFERER'])) {
			$url = $_SERVER['HTTP_REFERER'];
			$query = parse_url($url, PHP_URL_QUERY);
			parse_str($query, $queries);
		}
		if ( ($this->settings_controller->get_setting( 'lcw_enable' ) == false) || ($this->get_license_controller()->get_setting( 'site_token' ) == false && $this->settings_controller->get_setting( 'skip_signup' ) == false) || (isset($_GET['disable_cartimize']) && $_GET['disable_cartimize'] ==1 || isset($queries['disable_cartimize']) && $queries['disable_cartimize'] ==1) ) {
			$result = false; 
		}
		return $result;

	}

	public static function is_cartimize_page() {
		return Cartimize::is_cartimize_checkout();
	}

	public static function is_cartimize_checkout() {
		return function_exists( 'is_checkout' ) && is_checkout() && ! is_order_received_page() && ! is_checkout_pay_page();
	}

	public static function is_cartimize_checkout_pay_page() {
		return apply_filters( 'cartimize_is_checkout_pay_page', function_exists( 'is_checkout_pay_page' ) && is_checkout_pay_page());
	}

	public function load_assets() {
		if ( !apply_filters( 'cartimize_load_checkout_template', Cartimize::is_cartimize_checkout() ) ) {
			return;
		}
		/**
		 * WP Rocket
		 *
		 * Disable minify / cdn features while we're on the checkout page due to strange issues.
		 */
		if ( ! defined( 'DONOTROCKETOPTIMIZE' ) ) {
			define( 'DONOTROCKETOPTIMIZE', true );
		}

		wp_dequeue_script( 'woocommerce' );
		wp_deregister_script( 'woocommerce' );
		wp_dequeue_script( 'wc-checkout' );
		wp_deregister_script( 'wc-checkout' );
		// Minified extension
		$min = ( ! CARTIMIZE_DEV ) ? '.min' : '';
		global $wp;

		$front = trailingslashit( CARTIMIZE_PLUGIN_URL ) . 'assets/dist';
		$version = CARTIMIZE_VERSION;
		
		wp_enqueue_script( 'cartimize_vendor_js', "{$front}/js/cartimize-vendor-{$version}{$min}.js", array( 'jquery', 'jquery-blockui', 'jquery-migrate', 'js-cookie' ) );

		if ( apply_filters( 'cartimize_load_checkout_js', Cartimize::is_cartimize_checkout() ) ) {
			wp_enqueue_script( 'woocommerce', "{$front}/js/cartimize-main-{$version}{$min}.js", array( 'jquery', 'jquery-blockui', 'jquery-migrate', 'js-cookie' ) );
		}

		do_action('cartimize_load_template_assets');

		$cartimizeConfigData = apply_filters(
			'cartimize_config_data', array(
				'elements'        => array(
					'deliveryInfoElId'     => apply_filters( 'cartimize_template_delivery_info_el', '#cartimize-delivery-info' ),
					'shippingMethodElId'   => apply_filters( 'cartimize_template_shipping_method_el', '#cartimize-shipping-method' ),
					'paymentMethodElId'    => apply_filters( 'cartimize_template_payment_method_el', '#cartimize-payment-method' ),
					'tabContainerElId'     => apply_filters( 'cartimize_template_tab_container_el', '#cartimize' ),
					'alertContainerId'     => apply_filters( 'cartimize_template_alert_container_el', '.cartimize-alert-container' ),
					'checkoutFormSelector' => apply_filters( 'cartimize_checkout_form_selector', 'form.woocommerce-checkout' ),
				),
				'ajaxInfo'        => array(
					'url' => trailingslashit( get_home_url() ),
				),
				'compatibility'   => apply_filters( 'cartimize_typescript_compatibility_classes_and_params', array() ),
				'settings'        => array(
					'user_logged_in'                    => ( is_user_logged_in() ) ? true : false,
					'is_checkout_pay_page'              => Cartimize::is_cartimize_checkout_pay_page(),
					'default_address_fields'            => array_keys( Cartimize::modify_woocommerce_default_address_fields() ),
					'needs_shipping_address'            => cartimize_show_shipping_tab(),
					'skip_billing_validation_in_cus_info_tab' => ! WC()->cart->needs_shipping(),
					'wc_ship_to_billing_address_only'   => wc_ship_to_billing_address_only(),
					'desktop_width'   => (int)apply_filters( 'cartimize_desktop_width', 899 ),
				),
				'checkout_params' => array(
					'ajax_url'                  => WC()->ajax_url(),
					'wc_ajax_url'               => \WC_AJAX::get_endpoint( '%%endpoint%%' ),
					'update_order_review_nonce' => wp_create_nonce( 'update-order-review' ),
					'apply_coupon_nonce'        => wp_create_nonce( 'apply-coupon' ),
					'remove_coupon_nonce'       => wp_create_nonce( 'remove-coupon' ),
					'option_guest_checkout'     => get_option( 'woocommerce_enable_guest_checkout' ),
					'checkout_url'              => \WC_AJAX::get_endpoint( 'checkout' ),
					'is_checkout'               => is_checkout() && empty( $wp->query_vars['order-pay'] ) && ! isset( $wp->query_vars['order-received'] ) ? 1 : 0,
					'debug_mode'                => defined( 'WP_DEBUG' ) && WP_DEBUG,
					'i18n_checkout_error'       => esc_attr__( 'Error processing checkout. Please try again.', 'woocommerce' ),
					'is_cartimize_load_checkout_component' => apply_filters( 'cartimize_load_checkout_component', true ),
				),
				'frontend_element'				=> $this->frontend_controller->getFrontEndElement(),
				'runtime_email_matched_user'    => false, // default to false,
				'active_filling_step'			=> cartimize_get_active_filing_step(),
				'all_step_status'            => cartime_get_all_step_status(),
				'incompatible_review_step_payment' => apply_filters( 'cartimize_incompatible_review_step_payment', array() ),
				'is_review_step_supported' => cartimize_review_step_supported_payment(),
				'checkout_fields' => WC()->checkout->get_checkout_fields()
			)
		);

		wp_localize_script(
			'woocommerce', 'cartimizeConfigData', $cartimizeConfigData
		);

		wp_localize_script(
			'woocommerce', 'cartimizeInstance', ''
		);

		wp_localize_script(
			'woocommerce', 'cartimizeGlobalhook', ''
		);

		// Some plugins (WooCommerce Square for example?) want to use wc_cart_fragments_params on the checkout page
		wp_localize_script( 'woocommerce', 'wc_cart_fragments_params', array(
			'ajax_url'        => WC()->ajax_url(),
			'wc_ajax_url'     => \WC_AJAX::get_endpoint( '%%endpoint%%' ),
		) );

		if ( Cartimize::is_cartimize_checkout() ) {
			// Workaround for WooCommerce 3.8 Beta 1
			global $wp_scripts;
			$wp_scripts->registered['wc-country-select']->deps = array( 'jquery' );

			// WooCommerce Native Localization Handling
			wp_enqueue_script( 'wc-country-select' );

			$params = array(
					'locale'             => wp_json_encode( WC()->countries->get_country_locale() ),
					'locale_fields'      => wp_json_encode( WC()->countries->get_country_locale_field_selectors() ),
					'i18n_required_text' => esc_attr__( 'required', 'woocommerce' ),
					'i18n_optional_text' => esc_html__( 'optional', 'woocommerce' ),
				);
			wp_dequeue_script( 'wc-address-i18n' );
			wp_deregister_script( 'wc-address-i18n' );
			wp_localize_script( 'woocommerce', 'wc_address_i18n_params', $params );
			// wp_enqueue_script( 'wc-address-i18n' );
		}
	}

	public function cartimize_template_redirect() {
		if ( Cartimize::is_cartimize_checkout() ) {
			TemplateRedirect::checkout_page($this->template_controller, $this->settings_controller);
		}
	}

	private function create_template_object(){
		$this->template_controller = new TemplatesController('default');
	}

	public function is_phone_fields_enabled() {
		$is_phone_fields_enabled = get_option( 'woocommerce_checkout_phone_field' );
		return apply_filters( 'cartimize_enable_phone_fields', ('hidden' !== $is_phone_fields_enabled || $is_phone_fields_enabled == false ) );
	}

	public static function modify_woocommerce_default_address_fields( ){
			$fields = WC()->countries->get_default_address_fields();
			return $fields;
	}

	public function get_settings_controller() {
		return $this->settings_controller;
	}

	public function get_license_controller() {
		return $this->license_controller;
	}

	public function get_template_controller() {
		return $this->template_controller;
	}

	public function get_activation_controller() {
		return $this->activation_controller;
	}

	public function init_settings_controller(){
		$this->license_controller = new SettingsController();
	}

	public function init_license_controller(){
		$this->license_controller = new SettingsController( '', '_license' );
	}

	public function get_license_instance() {
		return $this->license_instance;
	}

	public function get_stats_instance() {
		return $this->stat_collection;
	}

	public function get_version() {
		return $this->version;
	}

	public function activation() {

		// $cartimize = Cartimize::instance();

		$errors = $this->get_activation_controller()->activate();

		$this->set_anonymous_data_collection_cron();
		$this->set_license_check_cron();
		$this->setup_custom_logo();
		try{
			$this->get_license_instance()->check_now();
		}
		catch(\CartimizeException $e){
			$error = $e->getError();
			$error_msg = $e->getErrorMsg();
		}

		if ( $errors ) {
			set_transient( '_cartimize_setting_redirect_on_activation', true, 30 );
		}

	}

	public function deactivation() {

		$cartimize = Cartimize::instance();
		$cartimize->unset_anonymous_data_collection_cron();
		$cartimize->unset_license_check_cron();
		$this->stat_collection->send_deactivation_data();
	}

	public function set_anonymous_data_collection_cron() {
		if ( ! wp_next_scheduled( 'cartimize_daily_scheduled_events_tracking' ) ) {
			wp_schedule_event( time(), 'daily', 'cartimize_daily_scheduled_events_tracking' );
		}
	}

	public function unset_anonymous_data_collection_cron() {
		wp_clear_scheduled_hook( 'cartimize_daily_scheduled_events_tracking' );
	}

	public function set_license_check_cron() {
		if ( ! wp_next_scheduled( 'cartimize_daily_license_check' ) ) {
			wp_schedule_event( time(), 'daily', 'cartimize_daily_license_check' );
		}
	}

	public function unset_license_check_cron() {
		wp_clear_scheduled_hook( 'cartimize_daily_license_check' );
	}

	public function override_woocommerce_registration_generate_password( $result ) {
		$result = 'yes';

		return $result;
	}


	public function override_woocommerce_registration_generate_username( $result ) {
		$result = 'yes';

		return $result;
	}

	public function modify_woocommerce_coupons_enabled( $result ){
		if ( self::is_cartimize_checkout() && $this->get_settings_controller()->get_setting('hide_coupons') == '1' ) {
			$result = false;
		}

		return $result;
	}

	public function checkout_redirect( $url ){
		if ( isset( $_GET['cartimize_redirect'] ) && $_GET['cartimize_redirect'] == 1 ) {
			if ( $this->get_settings_controller()->get_setting('demo_product_view') == '0' ) {
				$checkout_url = wc_get_checkout_url();
				wp_safe_redirect($checkout_url);
				$this->get_settings_controller()->update_setting('demo_product_view', 1);
				exit();
			}
		}

		return $url;
	}

	public function cartimize_header_logo_div( $logo_content ){
		$logo_content = $this->get_settings_controller()->get_setting('logo_id');
		$logo_type = $this->get_settings_controller()->get_setting('logo_type');
		$show_url = get_home_url();
		if ( empty( $logo_type ) && empty( $logo_content ) || !empty( $logo_type ) && empty( $logo_content )  ) {
			$logo_content = '<div class="cartimize-blog-name" style="padding: 10px 0; font-weight: 600; font-size: 16px;"> <a href="'.$show_url.'">'.get_bloginfo( 'name' ).'</a></div>';
		}elseif( $logo_type == 'text' ){
			$logo_content = '<div style="padding: 10px 0; font-weight: 600; font-size: 16px;"> <a href="'.$show_url.'">'.$this->get_settings_controller()->get_setting('store_text').'</a></div>';
		}elseif( !empty( $logo_content ) ){
			$logo_url =  wp_get_attachment_url( $logo_content );

			if ( !empty( $logo_url ) ) {
				$logo_content = '<a title="Back to Home" href="'.$show_url.'" class="custom-logo-link" rel="home"><img src="'.$logo_url.'" class="custom-logo" alt="'.get_bloginfo( 'name' ).'"></a>';
			}else{
				$logo_content = '<div title="Back to Home" class="cartimize-blog-name" style="padding: 10px 0; font-weight: 600; font-size: 16px;"> <a href="'.$show_url.'">'.get_bloginfo( 'name' ).'</a></div>';
			}

		}

		return $logo_content;
	}

	public function setup_custom_logo(){
		$logo_content = $this->get_settings_controller()->get_setting('logo_id');
		if ( empty($logo_content) ) {
			$logo_content = get_theme_mod( 'custom_logo' );
			if ( !empty( $logo_content ) ) {
				$logo_content = $this->get_settings_controller()->update_setting('logo_id', $logo_content);
			}
		}
	}

	public function feedback_integration(){
		if ( current_user_can('administrator') ) { 
			
		?>
			<div class="beta-program-cont">
				<a class="get-support" target="_blank" href="mailto:support@cartimize.com">
					<h6>Get Expert Help</h6>
					<p>Are you facing issues setting up Cartimize with your essential plugins? Let our experts set it up for you.</p>
					<div id="" style="background-color: #fff;color:#003e99;border:1px solid #003e99;box-shadow: 0 1px 0 rgba(0,0,0,0.1);border-radius: 5px;padding: 10px;cursor: pointer;display: block;text-align: center;margin: 15px 0 10px;">Get Premium Support</div>
				</a>
			</div>
		<?php
		}
	}
}