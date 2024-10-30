<?php
/**
 * Linear Checkout for WooCommerce by Cartimize
 * Copyright (c) 2020 Revmakx LLC
 * revmakx.com
 */

namespace Cartimize\License;

use Cartimize\Admin\Auth;
use Cartimize\Controllers\AjaxController;
use Cartimize\License\Actions\CheckNowAction;
use Cartimize\License\Actions\JoinTrial;
use Cartimize\License\Actions\ProActivate;
use Cartimize\License\Actions\ProFetchURL;
use Cartimize\License\Actions\ProInstall;
use Cartimize\License\Actions\DismissNotice;

class License{
	
	private $plugin_instance;
	private $ajax_manager;

	public function __construct( $plugin_instance ){
		$this->plugin_instance = $plugin_instance;
		add_action( 'init', array( $this, 'schedule_check' ) );
		add_filter( 'cron_schedules', array( $this, 'add_schedules' ) );
		add_action( 'cartimize_check_now', array( $this, 'do_check_now' ) );
	}

	public function init(){
		$this->create_objects();
		$this->configure_objects();
		$this->add_action_hooks();
	}

	public function add_action_hooks(){
		add_action( 'cartimize_inside_setting_header', array( $this, 'license_section' ));
		add_action( 'admin_menu', array( $this, 'cartimize_pro_onboarding_register') );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_styles'), 10 );
	}

	public function create_objects(){
		$this->ajax_manager = new AjaxController( $this->get_ajax_actions() );
	}

	private function configure_objects() {
		$this->ajax_manager->load_all();
	}

	public function get_ajax_actions(){
		return apply_filters('cartimize_license_ajax_actions', array(
			new CheckNowAction( 'check_now', true, 'wp_ajax_' ),
			new JoinTrial( 'join_trial', true, 'wp_ajax_' ),
			new ProActivate( 'pro_activate', true, 'wp_ajax_' ),
			new ProFetchURL( 'pro_fetch_url', true, 'wp_ajax_' ),
			new ProInstall( 'pro_install', true, 'wp_ajax_' ),
			new DismissNotice( 'dismiss_notice', true, 'wp_ajax_' ),
		));
	}

	public function license_section(){
		if ($this->is_pro_plan()) {
			?>
				<div class="license-section" >
					<?php echo wp_kses_post($this->button_html()); ?>
					<?php $this->include_popup(); ?>
				</div>
			<?php
		}
	}

	public function admin_enqueue_styles(){

		if (  isset( $_GET['page'] ) && $_GET['page'] == 'cartimize-pro-onboarding' ) {
			wp_register_style('cartimize_checkopt_admin_style', CARTIMIZE_PLUGIN_URL . 'assets/css/pro-onboarding.css', array(), CARTIMIZE_VERSION);
		}
	}

	public function license_type(){
		$license_type = $this->plugin_instance->get_license_controller()->get_setting('type');

		if ( $license_type == false ) {
			$license_type = 'free';
		}

		return $license_type;
	}

	public function get_site_token(){
		return $this->plugin_instance->get_license_controller()->get_setting( 'site_token' );
	}

	public function is_free_plan(){
		if ( $this->license_type() == 'free' ) {
			return true;
		}

		return false;
	}

	public function is_trial_plan(){
		if ( $this->license_type() == 'trial' ) {
			return true;
		}

		return false;
	}

	public function is_pro_plan(){
		$free = $this->is_free_plan();
		$trial = $this->is_trial_plan();

		if ( $free == true || $trial == true ) {
			return false;
		}

		return true;
	}

	public function is_pro_site(){
		return $this->plugin_instance->get_license_controller()->get_setting( 'is_pro_site' );
	}

	public function is_expired(){
		$expiry = $this->plugin_instance->get_license_controller()->get_setting('expiry');
		if ( $expiry < time() ) {
			return true;
		}

		return false;
	}

	public function remaining_trial_days(){
		$expiry = $this->plugin_instance->get_license_controller()->get_setting('expiry');
		$current_time = time();
		if ( $expiry < $current_time ) {
			return 0;
		}
		$interval = $expiry-$current_time;
		$days = ($interval/3600/24);
		return floor($days);
	}

	public function is_limit_reached(){
		$is_limit_reached = $this->plugin_instance->get_license_controller()->get_setting('is_limit_reached');
	
		return $is_limit_reached;
	}

	public function schedule_check() {
		add_action( 'cartimize_daily_license_check', array( $this, 'build_send' ) );
	}

	public function add_schedules( $schedules = array() ) {
		if ( ! isset( $schedules['daily'] ) ) {
			$schedules['daily'] = array(
				'interval' => 86400,
				'display'  => __( 'Once Daily' ),
			);
		}
		return $schedules;
	}

	public function build_send(){
		try{
			$response_data = $this->check_now();
		}
		catch(\CartimizeException $e){
			$error = $e->getError();
			$error_msg = $e->getErrorMsg();
		}
	}

	public function get_button_details( $condition, $page ){
		$default_details = array( 'name'=> '', 'hint'=> '',  'tag' => 'a', 'class' => '', 'id' => '', 'href' => '#', 'condition'=> $condition, 'target'=>'');
		$button_details = array();
		switch ( $condition ) {
			case 'free':
				$button_details['name'] = 'Try pro features, free for 14 days';
				if ( $page == 'onboard' ) {
					$button_details['hint'] = $this->get_hint( $condition, $page );
					$button_details['class'] = 'button cartimize-try-pro';
				}
				if ( $page == 'setting' ) {
					$button_details['href'] = CARTIMIZE_ONBOARDING_PAGE_URL;
					$button_details['hint'] = $this->get_hint( $condition, $page );
				}
				break;
			case 'trial_with_limit':
				if ( $page == 'onboard' ) {
					$button_details['name'] = 'Continue trial';
					$button_details['class'] = 'button';
					$button_details['hint'] = $this->get_hint( $condition, $page );
					$button_details['href'] = CARTIMIZE_SETTINGS_PAGE_URL;
				}elseif ( $page == 'setting' ) {
					$button_details['name'] = 'Upgrade to pro';
					$button_details['hint'] = $this->get_hint( $condition, $page );
					$button_details['href'] = CARTIMIZE_MY_ACCOUNT_URL;
					$button_details['target'] = '_blank';
				}
				break;
			case 'trial_with_limit_pro_not_installed':
			case 'pro_with_limit_pro_not_instaled':
				if ( $page == 'onboard' ) {
					$button_details['class'] = 'button cartimize-install-pro';
				}else{
					$button_details['class'] = 'cartimize-install-pro';
				}
				$button_details['name'] = 'Install Pro Plugin';
				$button_details['hint'] = $this->get_hint( $condition, $page );
				break;
			case 'trial_with_limit_pro_not_activated':
			case 'pro_with_limit_pro_not_activated':
				if ( $page == 'onboard' ) {
					$button_details['class'] = 'button cartimize-activate-pro';
				}else{
					$button_details['class'] = 'cartimize-activate-pro';
				}
				$button_details['name'] = 'Activate Pro Plugin';
				$button_details['hint'] = $this->get_hint( $condition, $page );
				break;
			case 'trial_limit_reached':
				$button_details['name'] = 'Upgrade to Pro';
				if ( $page == 'onboard' ) {
					$button_details['class'] = 'button';
				}
				$button_details['target'] = '_blank';
				$button_details['href'] = CARTIMIZE_MY_ACCOUNT_URL;
				$button_details['hint'] = $this->get_hint( $condition, $page );
				break;
			case 'trial_expired':
				$button_details['name'] = 'Upgrade to Pro';
				$button_details['href'] = CARTIMIZE_MY_ACCOUNT_URL;
				if ( $page == 'onboard' ) {
					$button_details['class'] = 'button';
				}
				$button_details['target'] = '_blank';
				$button_details['hint'] = $this->get_hint( $condition, $page );
				break;
			case 'pro_limit_reached':
				$button_details['name'] = 'Upgrade to Pro';
				if ( $page == 'onboard' ) {
					$button_details['class'] = 'button';
				}
				$button_details['target'] = '_blank';
				$button_details['href'] = CARTIMIZE_MY_ACCOUNT_URL;
				$button_details['hint'] = $this->get_hint( $condition, $page );
				break;
			case 'pro_with_limit':
				if ( $page == 'onboard' ) {
					$button_details['name'] = 'Continue Pro';
					$button_details['class'] = 'button';
					$button_details['href'] = CARTIMIZE_SETTINGS_PAGE_URL;
				}else{
					$button_details['name'] = 'Manage sites';
					$button_details['href'] = CARTIMIZE_MY_ACCOUNT_URL;
					$button_details['target'] = '_blank';
				}
				$button_details['hint'] = $this->get_hint( $condition, $page );
				break;
			case 'pro_expired':
				$button_details['name'] = 'Renew License';
				if ( $page == 'onboard' ) {
					$button_details['class'] = 'button';
				}
				$button_details['target'] = '_blank';
				$button_details['href'] = CARTIMIZE_MY_ACCOUNT_URL;
				$button_details['hint'] = $this->get_hint( $condition, $page );
				break;
			
			default:
				# code...
				break;
		}
		return wp_parse_args($button_details,$default_details);
	}

	public function get_user_state(){
		$pro_details = $this->is_pro_install_or_active();
		if ($this->is_free_plan()) {
			return 'free';
		}elseif ($this->is_trial_plan() && $this->is_expired()) {
			return 'trial_expired';
		}elseif ($this->is_trial_plan() && !$this->is_expired() && $this->is_limit_reached()) {
			return 'trial_limit_reached';
		}elseif ($this->is_trial_plan() && !$this->is_expired() && !$this->is_limit_reached() && (isset($pro_details['install_pro']) && $pro_details['install_pro'] == 1) ) {
			return 'trial_with_limit_pro_not_installed';
		}elseif ($this->is_trial_plan() && !$this->is_expired() && !$this->is_limit_reached() && (isset($pro_details['activate_pro']) && $pro_details['activate_pro'] == 1) ) {
			return 'trial_with_limit_pro_not_activated';
		}elseif ($this->is_trial_plan() && !$this->is_expired() && !$this->is_limit_reached() &&  (isset($pro_details['continue'])&& $pro_details['continue'])) {
			return 'trial_with_limit';
		}elseif ($this->is_pro_plan() && $this->is_expired() ) {
			return 'pro_expired';//
		}elseif ($this->is_pro_plan() && !$this->is_expired() && $this->is_limit_reached() ) {
			return 'pro_limit_reached';//
		}elseif ($this->is_pro_plan() && !$this->is_expired() && !$this->is_limit_reached() && (isset($pro_details['install_pro']) && $pro_details['install_pro'] == 1) ) {
			return 'pro_with_limit_pro_not_instaled';//
		}elseif ($this->is_pro_plan() && !$this->is_expired() && !$this->is_limit_reached() && (isset($pro_details['activate_pro']) && $pro_details['activate_pro'] == 1) ) {
			return 'pro_with_limit_pro_not_activated';
		}elseif ($this->is_pro_plan() && !$this->is_expired() && !$this->is_limit_reached() &&  (isset($pro_details['continue'])&& $pro_details['continue'])) {
			return 'pro_with_limit';
		}

		return '';
	}

	private function get_hint( $condition = '', $page = 'setting' ){
		$hint_html = '';
		if ( $condition == '' ) {
			return $hint_html;
		}

		switch ( $condition) {
			case 'free':
				 $hint_html = 'No credit card needed';
				break;
			case 'trial_expired':
				$hint_html = 'Trial period has ended';
				break;
			case 'trial_limit_reached':
				$hint_html = 'Your trial is active on another site';
				break;
			case 'trial_with_limit':
			case 'trial_with_limit_pro_not_activated':
			case 'trial_with_limit_pro_not_installed':
				$remaining = $this->remaining_trial_days();
				$day = ($remaining == 1)?'day':'days';
				$hint_html = sprintf("%d %s remaining in trial ", $remaining, $day);
				break;
			case 'pro_with_limit':
			case 'pro_with_limit_pro_not_activated':
			case 'pro_with_limit_pro_not_instaled':
				$site_limit = $this->plugin_instance->get_license_controller()->get_setting('site_limit');
				$site_count = $this->plugin_instance->get_license_controller()->get_setting('total_sites_count');
				$limit = (int)$site_limit - ((int)$site_count);
				$site = ($site_limit == 1)?'site':'sites';
				$hint_html = sprintf("%d/%d %s remaining ", $limit, $site_limit, $site);
				break;
			case 'pro_limit_reached':
				$site_limit = $this->plugin_instance->get_license_controller()->get_setting('site_limit');
				$site_count = $this->plugin_instance->get_license_controller()->get_setting('sites_count');
				$limit = (int)$site_limit - (int)$site_count;
				$site = ($site_limit == 1)?'site':'sites';
				$hint_html = sprintf("%d/%d %s remaining ", $limit, $site_limit, $site);
				break;
			case 'pro_expired':
				$hint_html = 'Pro License expired';
				break;
			
			default:
				# code...
				break;
		}

		return $hint_html;
	}

	public function button_html( $page = 'setting' ){
		$html= $hint = '';
		$user_state = $this->get_user_state();

		$button_details = $this->get_button_details( $user_state, $page );

		$common_class = $page.'_'.$button_details['condition'];
		if ( $button_details['hint'] != '' ) {
			if ( $page == 'setting' ) {
				$tag_name = 'span';
			}else{
				$tag_name = 'div';
			}
			$hint = '<'.$tag_name.' class="helpTxt helpTxt-'.$common_class.'">'.$button_details['hint'].'</'.$tag_name.'>';
		}

		$setting_class = '';
		if ( $page = "setting" ) {
			$setting_class = "cartimize_license_action_button";
		}
		$html = $hint.'<'.$button_details['tag'].' id="'.esc_attr($button_details['id']).'" class=" '.esc_attr($setting_class).' '.esc_attr($button_details['class']).'" href="'.esc_url($button_details['href']).'"  target='.esc_attr($button_details['target']).'>'.wp_kses_post($button_details['name']).'</'.$button_details['tag'].'>';
		return $html;
	}

	public function check_now_html(){
		?>
			<a class="cartimize-check-now"><span class="dashicons dashicons-update"></span></a>
		<?php
	}

	public function cartimize_pro_onboarding_register(){
		add_submenu_page( null, 'Cartimize', 'Cartimize', 'manage_options', 'cartimize-pro-onboarding', array( $this, 'pro_onboarding_page'));
	}

	public function pro_onboarding_page(){
		echo '<div class="cartimize_checkopt_settings_page">';
		include(CARTIMIZE_PATH.'/templates/Admin/cartimize-pro-onboarding-page.php');
		$this->include_popup();
		echo "</div>";
	}

	public function include_popup(){
		include(CARTIMIZE_PATH.'/templates/Admin/cartimize-popup-window.php');
	}

	public function is_pro_installed(){
		$plugin_slug = CARTIMIZE_PRO_SLUG;
		$installed_plugins = get_plugins();

		return array_key_exists( $plugin_slug, $installed_plugins ) || in_array( $plugin_slug, $installed_plugins, true );
	}

	public function is_valid_license(){
		$status = $this->plugin_instance->get_license_controller()->get_setting('license_status');
		if ( $status == 'valid' ) {
			return true;
		}

		return false;
	}

	public function is_pro_activated(){
		return class_exists('CartimizePro\\CartimizePro');
	}

	public function is_enable(){
		$type = $this->license_type();
		$is_expired = $this->is_expired();
		$is_valid_license = $this->is_valid_license();

		if ( $type == 'free' || $is_expired && !$is_valid_license  ) {
			return false;
		}

		return true;

	}

	public function is_pro_install_or_active(){
		$response = array();
		if ( $this->is_enable() == false ) {
			return $response;
		}
		$is_pro_installed = $this->is_pro_installed();
		$is_pro_activated = $this->is_pro_activated();
		if ( !$is_pro_installed ) {
			$response['install_pro'] = 1;
			
		}elseif ( $is_pro_installed && !$is_pro_activated ) {
			$response['activate_pro'] = 1;
		}elseif ( $is_pro_installed && $is_pro_activated ) {
			$response['continue'] = 1;
		}
		return $response;
	}

	public function is_server_writable(){
		if((!defined('FTP_HOST') || !defined('FTP_USER') || !defined('FTP_PASS')) && (get_filesystem_method(array(), false) != 'direct'))
			return false;
		else
			return true;
	}

	public function install_plugin(){
		global $wp_filesystem;
		if (!class_exists('\WP_Upgrader'))
		    include_once(ABSPATH . 'wp-admin/includes/class-wp-upgrader.php');
		include(CARTIMIZE_PATH.'/templates/Admin/updaterSkin.php');
		$package_url = get_option('_cartimize_pro_download_url_');

		$upgrader_skin = new \Cartimize_Updater_TraceableUpdaterSkin;
		$upgrader          = new \WP_Upgrader($upgrader_skin);
		$destination       = WP_PLUGIN_DIR;
		$clear_destination = false;
		add_filter( 'http_request_args',array( $this, 'bypass_url_validation' ), 10, 2 );
		$key                = basename($package_url);
		$install_info = @$upgrader->run(array(
		    'package' => $package_url,
		    'destination' => $destination,
		    'clear_destination' => $clear_destination, //Do not overwrite files.
		    'clear_working' => true,
		    'hook_extra' => array()
		));

		wp_cache_delete( 'plugins', 'plugins' );
		return $install_info;

	}

	public function bypass_url_validation($r,$url){
	    // $username = parse_url($url, PHP_URL_USER);
	    // $password = parse_url($url, PHP_URL_PASS);
	    // $r['headers'] = array('Authorization'=>'Basic'. base64_encode( $username . ':' . $password ) );
	    $r['reject_unsafe_urls'] = false;
	    return $r;
	}

	public function is_show_trial_remainder_notice(){
		$is_option = get_option('_cartimize_trial_remainder_notice');
		$remaining = $this->remaining_trial_days();
		if ( $remaining != 0 && $remaining <= 5 && empty($is_option) ) {
			return true;
		}
		return false;
	}

	public function is_show_trial_expiry_notice(){
		$is_option = get_option('_cartimize_trial_expiry_notice');
		if ( $this->is_trial_plan() && $this->is_expired() && empty($is_option) ) {
			return true;
		}
		return false;
	}

	private function get_trial_time_limit(){
		return $this->plugin_instance->get_license_controller()->get_setting('trial_time_limit');
	}

	public function check_now(){

		$auth_instance = new Auth($this->plugin_instance);
		$data = $config = array();
		$config['email'] = $this->plugin_instance->get_license_controller()->get_setting('email');
		$config['site_token'] = $this->plugin_instance->get_license_controller()->get_setting('site_token');
		$data['user_id'] = $this->plugin_instance->get_license_controller()->get_setting('user_id');
		if ($config['email'] == false || $config['site_token'] == false || $data['user_id'] == false) {
			throw new \CartimizeException('service__invalid_request');
		}
		$response_data = $auth_instance->do_service_request($config, 'check_now', $data);

		if( $response_data['status'] === 'success'){
			$result = $auth_instance->save_creds_info($response_data);
			do_action('cartimize_refresh_license_controller');
		}

		return $response_data;
	}

	public function activate_pro(){

		$auth_instance = new Auth($this->plugin_instance);
		$data = $config = array();
		$config['email'] = $this->plugin_instance->get_license_controller()->get_setting('email');
		$config['site_token'] = $this->plugin_instance->get_license_controller()->get_setting('site_token');
		$data['user_id'] = $this->plugin_instance->get_license_controller()->get_setting('user_id');
		if ($config['email'] == false || $config['site_token'] == false || $data['user_id'] == false) {
			throw new \CartimizeException('service__invalid_request');
		}
		$response_data = $auth_instance->do_service_request($config, 'activate_pro', $data);

		if( $response_data['status'] === 'success'){
			$result = $auth_instance->save_creds_info($response_data);
			do_action('cartimize_refresh_license_controller');
			return true;
		}

		return false;
	}

	public function do_check_now(){
		try{
			if ( !isset($_GET['cartimize_stop_check_now']) ) {
		   		$this->check_now();
			}
		}catch(\CartimizeException $e){

		}
	}

}