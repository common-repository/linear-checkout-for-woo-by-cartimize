<?php
/**
 * Linear Checkout for WooCommerce by Cartimize
 * Copyright (c) 2020 Revmakx LLC
 * revmakx.com
 */

namespace Cartimize\Admin;

use Cartimize\Admin\Admin;

class ServiceAuth{

	public $plugin_instance;

	public function __construct( $plugin ) {
		$this->plugin_instance = $plugin;
	}

	private function do_auth_with_action($creds, $action, $data = false){

		if( (empty($creds['email']) || empty($action) || 
		( $action === 'check_validity' && (empty($creds['site_token']) && $action != 'keep_me_updated')) ) ){
			throw new \CartimizeException('invalid_request');
		}

		if(!in_array($action, array('check_validity', 'add_site', 'create_account', 'login_account', 'keep_me_updated'))){
			throw new \CartimizeException('invalid_request');
		}

		$url = CARTIMIZE_SERVICE_URL;

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

		$body = $request_data;

		$http_args = array(
			'method' => "POST",
			'timeout' => 300,
			'body' => $body
		);

		try{
			$response = wp_remote_request( $url, $http_args );

			$response_data = Admin::cartimize_get_response_from_json($response);
		}
		catch(\CartimizeException $e){
			throw $e;
		}

		if(empty($response_data) || !is_array($response_data)){
			throw new \CartimizeException('invalid_response'); 
		}

		return $response_data;
	}

	private function save_creds_info($creds){
		
		if( isset($creds['site_token']) ){
			$creds['site_token'] = base64_encode($creds['site_token']);
		}

		$whitelist = array('email', 'site_token', 'last_validated', 'last_checked', 'status', 'expiry', 'validity_error', 'issue_deducted', 'user_id', 'is_insider');
		$creds = array_intersect_key( $creds, array_flip( $whitelist ) );

		return update_option('_cartimize__license', $creds);;

	}

	public function login_account($creds){
		//santizing of email and password taken care at service side, beware of using it/displaying here.
		$creds['email'] = trim($creds['email']);
		$response_data = $this->do_auth_with_action($creds, 'login_account');

		if( !isset($response_data['status']) || !isset($response_data['message']) ){
			throw new \CartimizeException('service__invalid_response');
		}

		if( $response_data['status'] === 'success' && $response_data['message'] === 'valid_account' ){
			$creds_to_save = $creds;
			unset($creds_to_save['password']);
			$creds_to_save['status'] = 'valid';
			$creds_to_save['expiry'] = isset($response_data['expiry']) ? $response_data['expiry'] : '';
			$creds_to_save['last_checked'] = time();
			$creds_to_save['last_validated'] = time();
			$creds_to_save['user_id'] = $response_data['user_id'];
			$creds_to_save['is_insider'] = $response_data['is_insider'];

			if( empty($response_data['site_token']) || !is_string($response_data['site_token']) ){
				throw new \CartimizeException('service__invalid_token');
			}

			$creds_to_save['site_token'] = $response_data['site_token'];
			unset($creds_to_save['issue_deducted']);
			$this->save_creds_info($creds_to_save);
			return true;
		}
		elseif( $response_data['status'] === 'error' && $response_data['message'] ){
			throw new \CartimizeException('service__'.$response_data['message']); 
		}
		else{
			throw new \CartimizeException('service__invalid_response');
		}
	}

	public function create_account($creds){
		//santizing of email and password taken care at service side, beware of using it/displaying here.
		$creds['email'] = trim($creds['email']);
		$response_data = $this->do_auth_with_action($creds, 'create_account');

		if( !isset($response_data['status']) || !isset($response_data['message']) ){
			throw new \CartimizeException('service__invalid_response');
		}

		if( $response_data['status'] === 'success' && $response_data['message'] === 'created' ){
			$creds_to_save = $creds;
			unset($creds_to_save['password']);
			$creds_to_save['status'] = 'valid';
			$creds_to_save['expiry'] = isset($response_data['expiry']) ? $response_data['expiry'] : '';
			$creds_to_save['last_checked'] = time();
			$creds_to_save['last_validated'] = time();
			$creds_to_save['user_id'] = $response_data['user_id'];

			if( empty($response_data['site_token']) || !is_string($response_data['site_token']) ){
				throw new \CartimizeException('service__invalid_token');
			}

			$creds_to_save['site_token'] = $response_data['site_token'];
			unset($creds_to_save['issue_deducted']);
			$this->save_creds_info($creds_to_save);
			return true;
		}
		elseif( $response_data['status'] === 'error' && $response_data['message'] ){
			throw new \CartimizeException('service__'.$response_data['message']); 
		}
		else{
			throw new \CartimizeException('service__invalid_response');
		}
	}

	public function keep_me_updated( $params ){

		$params['site_token'] = $this->plugin_instance->get_license_controller()->get_setting( 'site_token' );
		$params['email'] = $params['subscription_email'];

		$response_data = $this->do_auth_with_action($params, 'keep_me_updated');

		if( !isset($response_data['status']) || !isset($response_data['message']) ){
			throw new \CartimizeException('service__invalid_response');
		}

		if( $response_data['status'] === 'success' && $response_data['message'] === 'subscribed' ){
			$this->plugin_instance->get_license_controller()->update_setting( 'is_insider', '1' );
			return true;
		}
		elseif( $response_data['status'] === 'error' && $response_data['message'] ){
			throw new \CartimizeException('service__'.$response_data['message']); 
		}
		else{
			throw new \CartimizeException('service__invalid_response');
		}
	}

	public function send_report( $params ){

		$params['site_token'] = $this->plugin_instance->get_license_controller()->get_setting( 'site_token' );
		$params['cartimize_email'] = $this->plugin_instance->get_license_controller()->get_setting( 'email' );

		$response_data = $this->collect_and_send_report($params, 'send_report');

		if( !isset($response_data['status']) || !isset($response_data['message']) ){
			throw new \CartimizeException('service__invalid_response');
		}

		if( $response_data['status'] === 'success' && $response_data['message'] === 'created' ){
			return true;
		}
		elseif( $response_data['status'] === 'error' && $response_data['message'] ){
			throw new \CartimizeException('service__'.$response_data['message']); 
		}
		else{
			throw new \CartimizeException('service__invalid_response');
		}
	}

	public function collect_and_send_report( $params, $action ){

		if( empty($params['email']) || empty($action) ){
			throw new \CartimizeException('invalid_request');
		}

		if(!in_array($action, array('send_report'))){
			throw new \CartimizeException('invalid_request');
		}

		$data = $this->plugin_instance->stat_collection->build_data( true );
		$remote_url = CARTIMIZE_SERVICE_REPORT_URL;

		$build_data = $data;
		$build_data[ 'report_email' ] = $params[ 'email' ];
		$build_data[ 'message' ] = $params[ 'message' ];

		try{
			$response = wp_remote_request(
				$remote_url,
				array(
					'method'      => 'POST',
					'headers'     => [
						'Content-Type' => 'application/json',
					],
					'timeout'     => 8,
					'redirection' => 5,
					'httpversion' => '1.1',
					'body'        => wp_json_encode( $build_data ),
					'sslverify'   => true,
				)
			);


			$response_data = Admin::cartimize_get_response_from_json($response);
		}
		catch(\CartimizeException $e){
			throw $e;
		}

		if(empty($response_data) || !is_array($response_data)){
			throw new \CartimizeException('invalid_response'); 
		}

		return $response_data;
	}

}