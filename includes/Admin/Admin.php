<?php
/**
 * Linear Checkout for WooCommerce by Cartimize
 * Copyright (c) 2020 Revmakx LLC
 * revmakx.com
 */

namespace Cartimize\Admin;

use Cartimize\Admin\SettingsPage;
use Cartimize\Admin\ServiceAuth;
use Cartimize\Stats\DeactivationFeedback;
use Cartimize\Compatibility\Plugins\CartFlows;

class Admin {

	public $plugin_instance;

	public $service_auth_instance;

	public $deactivate_instance;

	public $license_instance;

	public function __construct( $plugin ) {
		$this->plugin_instance = $plugin;
		$this->init();
	}

	public function run() {
		add_action(
			'plugins_loaded', function() {
			    $this->start();
		    }, 1
		);
	}

	private function init(){
		if( isset($_GET['page']) && !isset($_GET['logout']) && (( isset($_GET['tab']) && $_GET['page'] === 'wc-settings' && $_GET['tab'] === 'cartimize_checkopt_settings') || $_GET['page'] == 'cartimize-pro-onboarding')){
			$this->plugin_instance->get_license_instance()->do_check_now();
		}
	}

	public function start(){
		new SettingsPage( $this->plugin_instance );
		$this->service_auth_instance = new ServiceAuth( $this->plugin_instance );
		$this->deactivate_instance = new DeactivationFeedback( $this->plugin_instance );
		$this->license_instance = $this->plugin_instance->get_license_instance();
		$this->license_instance->init();
		$this->add_action_hooks();
		$this->add_filter_hooks();
		$this->compatibility();
	}

	public function add_action_hooks(){
		add_action('admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts'), 10);
		add_action('admin_enqueue_scripts', array( $this, 'admin_enqueue_styles'), 10);
		add_action('wp_ajax_cartimize_process_ajax_request', array( $this, 'admin_process_ajax_request') );
		add_action( 'admin_init', array($this, 'setting_page_redirect_on_activation') );
		add_action( 'admin_init', array($this, 'onboarding_page_redirect') );
		add_action( 'admin_init', array($this, 'login_page_redirect') );
		add_action( 'admin_notices', array( $this, 'contribute' ) );
		add_action( 'admin_head', array( $this, 'hide_admin_notce' ), 1 );
		add_action( 'admin_footer', array( $this, 'print_deactivation_html' ), 1 );
	}

	public function add_filter_hooks(){
		add_filter( 'plugin_action_links', array($this, 'add_setting_button_plugin_row'), 10, 5 );
	}
	public function admin_enqueue_styles() {
		wp_register_style('cartimize_checkopt_admin_style', CARTIMIZE_PLUGIN_URL . 'assets/css/admin.css', array(), CARTIMIZE_VERSION);
		wp_enqueue_style('cartimize_checkopt_admin_style');
		wp_enqueue_style( 'wp-jquery-ui-dialog' );
	}

	public function admin_enqueue_scripts() {
		wp_enqueue_code_editor( array( 'type' => 'text/html' ) );
		wp_register_script('cartimize_checkopt_admin_common', CARTIMIZE_PLUGIN_URL . 'assets/js/common.js', array('jquery'), CARTIMIZE_VERSION, true);
		wp_enqueue_script('cartimize_checkopt_admin_common');

		wp_register_script('cartimize_checkopt_admin_script', CARTIMIZE_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), CARTIMIZE_VERSION, true);
		wp_enqueue_script('cartimize_checkopt_admin_script');
		$message = $this->get_all_messages();
		wp_localize_script('cartimize_checkopt_admin_script', 'cartimize_checkopt_ajax', array( 
			'ajax_url' => admin_url('admin-ajax.php'), 
			'admin_url' => admin_url(), 'logo_id'=> $this->plugin_instance->get_settings_controller()->get_setting( "logo_id" ), 
			'setting_page_url' =>CARTIMIZE_SETTINGS_PAGE_URL,
			'ui_messages' => $message,
			 ));
		wp_enqueue_script( 'jquery-ui-dialog' );
		wp_enqueue_media();

		$min = ( ! CARTIMIZE_DEV ) ? '.min' : '';

		$front = trailingslashit( CARTIMIZE_PLUGIN_URL ) . 'assets/dist';
		$version = CARTIMIZE_VERSION;
		
		wp_register_style( 'cartimize_admin_style', "{$front}/css/cartimize-admin-style-{$version}{$min}.css", array() );
		wp_enqueue_style('cartimize_admin_style');

		wp_enqueue_script( 'cartimize_admin_js', "{$front}/js/cartimize-admin-js-{$version}{$min}.js", array( 'jquery', 'jquery-blockui', 'jquery-migrate', 'js-cookie', 'jquery-ui-tabs' ) );

	}

	public function admin_process_ajax_request(){

		if(isset($_POST['cartimize_action']) && in_array($_POST['cartimize_action'] , array('service_login_account', 'service_create_account'))){
			$response = array();

			$creds = array();
			$creds['email'] = isset($_POST['email']) ? sanitize_email( $_POST['email'] ) : '';
			$creds['password'] = isset($_POST['password']) ? sanitize_text_field( $_POST['password'] ) : '';
			//santizing of email and password taken care at service side, beware of using it/displaying here.

			try{
				if ( $_POST['cartimize_action'] == 'service_create_account' ) {
					$result = $this->service_auth_instance->create_account($creds);
				}else{
					$result = $this->service_auth_instance->login_account($creds);
				}
				$response['status'] = $result ? 'success' : 'error';
				$response['error_msg'] = '';
			}
			catch(\CartimizeException $e){
				$error = $e->getError();
				$error_msg = $e->getErrorMsg();
		
				$response = array();
				$response['status'] = 'error';
				$response['error_msg'] = $error_msg;
				$response['error_code'] = $error;
			}		
			
		}elseif ( isset($_POST['cartimize_action']) && in_array($_POST['cartimize_action'] , array('keep_me_updated')) ) {
			$response = array();

			$params = array();
			$params['subscription_email'] = isset($_POST['subscription_email']) ? sanitize_email( $_POST['subscription_email'] ) : '';
			$params['is_promotional'] = isset($_POST['is_promotional']) ? sanitize_text_field( $_POST['is_promotional'] ) : '';
			$params['is_product_update'] = isset($_POST['is_product_update']) ? sanitize_text_field( $_POST['is_product_update'] ) : '';
			//santizing of email and password taken care at service side, beware of using it/displaying here.

			try{
				$result = $this->service_auth_instance->keep_me_updated($params);
				$response['status'] = $result ? 'success' : 'error';
				$response['error_msg'] = '';
			}
			catch(\CartimizeException $e){
				$error = $e->getError();
				$error_msg = $e->getErrorMsg();
			
				$response = array();
				$response['status'] = 'error';
				$response['error_msg'] = $error_msg;
				$response['error_code'] = $error;
			}	
		}elseif ( isset($_POST['cartimize_action']) && in_array($_POST['cartimize_action'] , array('collect_report_issue')) ) {
			$response = array();

			try{
				$result = $this->plugin_instance->stat_collection->build_data( true );
				$response['status'] = $result ? 'success' : 'error';
				$response['result'] = serialize($result);
				$response['error_msg'] = '';
			}
			catch(\CartimizeException $e){
				$error = $e->getError();
				$error_msg = $e->getErrorMsg();
			
				$response = array();
				$response['status'] = 'error';
				$response['error_msg'] = $error_msg;
				$response['error_code'] = $error;
			}	
		}elseif ( isset($_POST['cartimize_action']) && in_array($_POST['cartimize_action'] , array('send_report')) ) {
			$response = array();

			$params = array();
			$params['email'] = isset($_POST['email']) ? sanitize_email( $_POST['email'] ) : '';
			$params['message'] = isset($_POST['message']) ? sanitize_textarea_field( $_POST['message'] ) : '';
			//santizing of email and password taken care at service side, beware of using it/displaying here.

			try{
				$result = $this->service_auth_instance->send_report($params);
				$response['status'] = $result ? 'success' : 'error';
				$response['error_msg'] = '';
			}
			catch(\CartimizeException $e){
				$error = $e->getError();
				$error_msg = $e->getErrorMsg();
			
				$response = array();
				$response['status'] = 'error';
				$response['error_msg'] = $error_msg;
				$response['error_code'] = $error;
			}	
		}

		if(!empty($response)){
			echo self::cartimize_prepare_response($response);// PHPCS:Ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			exit();
		}
	}

	public static function cartimize_prepare_response($response){//to send response in form json with a wrapper
		$json = wp_json_encode($response);
		return '<cartimize_response>'.$json.'</cartimize_response>';
	}

	public static function cartimize_get_response_from_json($response){
		self::cartimize_check_response_error($response);
		self::cartimize_check_http_error($response);

		$response_str = wp_remote_retrieve_body($response);
		$clean_response_str = self::cartimize_remove_response_junk($response_str);
		$response_data = json_decode($clean_response_str, true);

		if($response_data === null){
			//if required use json_last_error()
			throw new \CartimizeException('invalid_response_json_failed');
		}
		
		return $response_data;
	}

	public static function cartimize_check_response_error($response){
		if ( is_wp_error( $response ) ) {
			throw new \CartimizeException($response->get_error_code(), $response->get_error_message());
		}
	}

	public static function cartimize_check_http_error($response){
		$http_code = wp_remote_retrieve_response_code( $response );
		if($http_code !== 200){
			$response_msg = wp_remote_retrieve_response_message( $response );
			throw new \CartimizeException('http_error', 'HTTP status code: ('.$http_code.') '.$response_msg);
		}
	}

	public static function cartimize_remove_response_junk($response){
		$start_tag_len = strlen('<cartimize_response>');
		$start_pos = stripos($response, '<cartimize_response');
		$end_pos = stripos($response, '</cartimize_response');
		if($start_pos === false || $end_pos === false){
			throw new \CartimizeException('invalid_response_format');
		}

		$response = substr($response, $start_pos);//clearing anything before start tag
		$end_pos = stripos($response, '</cartimize_response');//new end_pos
		$response = substr($response, $start_tag_len, $end_pos-$start_tag_len);

		return $response;
	}

	public static function cartimize_get_error_msg($error_slug){
		return self::cartimize_get_lang($error_slug);
	}

	public static function cartimize_get_lang($lang_slug){
		static $lang;
		if(!isset($lang)){
			include_once(CARTIMIZE_PATH . 'resources/php/error-codes.php');
			$lang = $cartimize_lang;
		}
		return isset($lang[$lang_slug]) ? $lang[$lang_slug] : $lang_slug;
	}

	public function setting_page_redirect_on_activation(){
		if ( ! get_transient( '_cartimize_setting_redirect_on_activation' ) ) {
			return;
		}

		delete_transient( '_cartimize_setting_redirect_on_activation' );

		if ( is_network_admin() || isset( $_GET['activate-multi'] ) ) {
			return;
		}

		wp_redirect(CARTIMIZE_SETTINGS_PAGE_URL);
	}

	public function onboarding_page_redirect(){
		if ( ! get_transient( '_cartimize_onboarding_page_redirect' ) ) {
			return;
		}

		delete_transient( '_cartimize_onboarding_page_redirect' );

		if ( is_network_admin() || isset( $_GET['activate-multi'] ) ) {
			return;
		}

		wp_redirect(CARTIMIZE_ONBOARDING_PAGE_URL);
	}

	public function login_page_redirect(){
		if ( ! get_transient( '_cartimize_login_page_redirect' ) ) {
			return;
		}

		delete_transient( '_cartimize_login_page_redirect' );

		wp_redirect(CARTIMIZE_LOGIN_PAGE_URL);
	}

	public function add_setting_button_plugin_row($links, $file){
	    if ( strpos( $file, CARTIMIZE_PLUGIN_SLUG ) !== false ) {
	            $new_links = array(
	                    'settings' => '<a href="'.admin_url('admin.php?page=wc-settings&tab=cartimize_checkopt_settings').'" style="font-weight:bold">Settings</a>',
	                    'docs' => '<a href="'.CARTIMIZE_SUPPORT_DOC_URL.'" target="_blank" style="font-weight:bold">Docs</a>',
	                    'support' => '<a href="mailto: support@cartimize.com" target="_blank" style="font-weight:bold">Support</a>',
	                    );
	            
	            $links = array_merge( $links, $new_links );
	    }
	        
	    return $links;
	}

	public function compatibility() {
	    $cart_flows = new CartFlows();
	    $cart_flows->admin_init();
    }

    public function contribute(){
    	global $wp;
    	$local = get_locale();
    	$un_supported_lang = array();
    	if ( defined( 'CARTIMIZE_SKIP_LANGUAGE_PROMOTION' ) ) {
    		$un_supported_lang = explode(',', str_replace(' ', '',CARTIMIZE_SKIP_LANGUAGE_PROMOTION));
    	}
    	$is_lang_promotion = $this->plugin_instance->get_settings_controller()->get_setting( "is_lang_promotion" );
    	if ( $is_lang_promotion == true || in_array( $local, $un_supported_lang ) ) {
    		return;
    	}

    	if ( isset( $_GET['cartimize-lang-promotion-dismiss'] ) && $_GET['cartimize-lang-promotion-dismiss'] == 1 ) {
    		$this->plugin_instance->get_settings_controller()->update_setting( "is_lang_promotion", 1 );
    		return;
    	}
    	if ( !function_exists( 'wp_get_available_translations' ) ) {
    		require_once( ABSPATH . 'wp-admin/includes/translation-install.php' );
    	}
    	$available = wp_get_available_translations();

    	if ( empty( $available[ $local ] ) ) {
    		return;
    	}
    	$current_query = array();
    	$current_query['cartimize-lang-promotion-dismiss'] = 1;
    	$current_query = add_query_arg( $current_query, $_SERVER['REQUEST_URI'] );
    	?>
    		<div class="notice woocommerce_checkout_field_editor_admin_notice notice-warning">
				<p>Welcome to <strong>Linear Checkout for WooCommerce</strong>. Contribute to translate the checkout into <?php echo esc_html($available[ $local ]['english_name']); ?> and win 30% discount on the Pro plugin. <a target="_blank" href="<?php echo esc_url(CARTIMIZE_SUPPORT_DOC_URL); ?>article/6-how-can-i-contribute-to-the-linear-checkout-plugin-translations">Know More &nearr;</a>. <a style="float: right; cursor: pointer;" href="<?php echo esc_url($current_query);?>">Dismiss</a></p>
			</div>
    	<?php
    }

    public function hide_admin_notce(){
    	if ( $this->is_cartimize_page() ) {
    		remove_all_actions( 'admin_notices' );
    		add_action(
    			'admin_notices', array( $this, 'cartimize_trigger_admin_notice' )
    		);
    	}
    }

    private function is_cartimize_page(){
    	
    	if (  isset( $_GET['page'] ) && in_array( $_GET['page'], $this->cartimize_pages() ) ) {
    		return true;
    	}

    	return false;
    }

    public function cartimize_pages(){
    	return array('cartimize-login', 'cartimize-pro-onboarding');
    }

    public function get_login_redirect_url(){
    	if (0 && $this->license_instance->is_free_plan()) {
    		return CARTIMIZE_ONBOARDING_PAGE_URL;
    	}else{
    		return CARTIMIZE_SETTINGS_PAGE_URL;
    	}
    }

    public function get_all_messages(){
    	return apply_filters( 'cartimize_ui_info_messages', array(
    			'connect_server' => 'Connecting to server...',
    			'trial_success' => 'Trial registered successfully.',
    			'download_trial' => 'Downloading Pro plugin...',
    			'installing_pro' => 'Installing Pro plugin...',
    			'activate_pro' => 'Activating Pro Plugin...',
    			'install_success' => 'The Pro plugin has been successfully installed.',
    			'trial_started' => 'Your 14-day trial has now started.',
    			'redirect_setting' => 'Redirecting to Cartimize settings page...',
    			'refresh_setting' => 'Refreshing the Cartimize settings page...',
    			'invalid_account' => 'Invalid account details. Redirecting to Cartimize llogin page...',

    	) );
    }

    public function print_deactivation_html(){
    	global $pagenow;
    	if ( $pagenow == 'plugins.php' ){
    		include(CARTIMIZE_PATH.'/templates/Admin/cartimize-deactivation.php');
    	}
    }

    public function cartimize_trigger_admin_notice(){
    	do_action('cartimize_show_admin_notice');
    }

}