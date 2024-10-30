<?php
/**
 * Linear Checkout for WooCommerce by Cartimize
 * Copyright (c) 2020 Revmakx LLC
 * revmakx.com
 */

namespace Cartimize\Admin;
class Auth{
	private $url = CARTIMIZE_SERVICE_URL;
	private $timeout = 300;
	private $registered_actions = array('check_validity', 'add_site', 'create_account', 'login_account', 'keep_me_updated', 'join_trial', 'check_now', 'pro_fetch_url', 'activate_pro');
	private $whitelist = array('email', 'site_token', 'last_validated', 'last_checked', 'license_status', 'expiry', 'validity_error', 'issue_deducted', 'user_id', 'is_insider', 'type', 'site_limit', 'sites_count', 'is_limit_reached', 'is_pro_site', 'trial_time_limit', 'total_sites_count');

	public function __construct( $plugin ){
		$this->plugin_instance = $plugin;
	}

	public function do_service_request($creds, $action, $data = false){

		$this->valid_request( $creds, $action );

		$this->check_registered_actions( $action );

		$request_data = $this->prepare_request( $creds, $data, $action );

		$response_data = $this->send_sevice_request( $request_data );

		$this->check_and_reset_account($response_data);

		if(empty($response_data) || !is_array($response_data)){
			throw new \CartimizeException('invalid_response'); 
		}

		return $response_data;

	}

	private function prepare_request( $creds, $data, $action ){
		$request_data = array();
		if(!empty($data) && is_array($data)){
			$request_data = $data;
		}

		$request_data['email'] =  base64_encode($creds['email']);
		$request_data[$action] =  '1';
		$request_data['site_url'] =  trailingslashit(network_site_url());
		$request_data['plugin_slug'] =  CARTIMIZE_PLUGIN_SLUG;
		$request_data['plugin_version'] =  CARTIMIZE_VERSION;

		if( isset($creds['password']) ){
			$request_data['password'] =  base64_encode($creds['password']);
		}
		
		if( isset($creds['site_token']) ){
			$request_data['site_token'] =  base64_encode($creds['site_token']);
		}

		if( isset($creds['subscription_email']) ){
			$request_data['subscription_email'] =  base64_encode($creds['subscription_email']);
		}

		if( isset($creds['is_promotional']) ){
			$request_data['is_promotional'] =  $creds['is_promotional'];
		}

		if( isset($creds['is_product_update']) ){
			$request_data['is_product_update'] =  $creds['is_product_update'];
		}

		return $request_data;
	}

	public function send_sevice_request( $request_data ){

		$body = $request_data;

		$http_args = array(
			'method' => "POST",
			'timeout' => $this->timeout,
			'body' => $body
		);

		try{
			$response = wp_remote_request( $this->url , $http_args );
			$response_data = self::cartimize_get_response_from_json($response);
		}
		catch(\CartimizeException $e){
			throw $e;
		}

		return $response_data;

	}

	private function valid_request( $creds, $action ){
		if( empty($creds['email']) || empty($action) || 
		( $action === 'check_validity' && empty($creds['site_token']) ) ){
			throw new \CartimizeException('invalid_request');
		}
	}

	private function check_registered_actions( $action ){
		if(!in_array($action, $this->registered_actions)){
			throw new \CartimizeException('invalid_request');
		}
	}

	public static function cartimize_prepare_response($response){//to send response in form json with a wrapper
		$json = json_encode($response);
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

	public function save_creds_info($creds){
		
		if( isset($creds['site_token']) ){
			$creds['site_token'] = base64_encode($creds['site_token']);
		}

		$creds = array_intersect_key( $creds, array_flip( $this->whitelist ) );
		$return = update_option('_cartimize__license', $creds);
		do_action('cartimize_refresh_license_controller');

		return $return;

	}

	private function check_and_reset_account($response_data){

		if ( isset( $response_data['reset_account'] ) && $response_data['reset_account'] == 1 ) {
			delete_option('_cartimize__license');
			set_transient( '_cartimize_login_page_redirect', true, 30 );
			do_action('cartimize_refresh_license_controller');
		}
	}
}