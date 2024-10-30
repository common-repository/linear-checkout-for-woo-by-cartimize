<?php
/**
 * Linear Checkout for WooCommerce by Cartimize
 * Copyright (c) 2020 Revmakx LLC
 * revmakx.com
 */
namespace Cartimize\Stats\Actions;
use Cartimize\Core\ActionCore;

class SendDeactivationReport extends ActionCore{
	public function __construct( $id, $no_privilege, $action_prefix ) {
		parent::__construct( $id, $no_privilege, $action_prefix );
	}

	public function action() {
		$response = array();

		try{
			if ( isset($_POST['action']) && $_POST['action'] == 'send_report'  ) {

				$params = array();
				$params['feedBackType'] = sanitize_text_field($_POST['feedBackType']);
				$params['sendAnonymous'] = sanitize_text_field($_POST['sendAnonymous']);
				$params['message'] = sanitize_text_field($_POST['message']);
				$result = $this->send_feedback( $params );;
			}else{
				$result = array();
			}
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
		$this->out( $response );
	}

	private function send_feedback( $params ){
		global $cartimize;
		$feedbackParam = array();
		$data = array();
		$collect_data_feedback_type = array( 'issue', 'better_plugin' );
		if ( in_array( $params['feedBackType'], $collect_data_feedback_type ) && ($params['sendAnonymous'] == 'true'|| $params['sendAnonymous'] === true) ) {
			$data = $cartimize->get_stats_instance()->build_data( true, true );
		}elseif( in_array( $params['feedBackType'], $collect_data_feedback_type ) ){
			$email = $cartimize->get_license_controller()->get_setting( 'email' );
			if ( empty($email) && $is_feedback ) {
				$current_user = wp_get_current_user();
				$email = $current_user->user_email;
			}
			$email = $email ? $email: '';
			$data['email'] = $email;
		}

		$feedbackParam['feedBackType'] = $params['feedBackType'];
		$feedbackParam['message'] = $params['message'];

		if ( !empty( $data ) ) {

			$feedbackParam['data'] = $data;
		}

		$remote_url = CARTIMIZE_SERVICE_FEEDBACK_URL;

		wp_remote_request(
			$remote_url,
			array(
				'method'      => 'POST',
				'headers'     => [
					'Content-Type' => 'application/json',
				],
				'timeout'     => 8,
				'redirection' => 5,
				'httpversion' => '1.1',
				'body'        => wp_json_encode( $feedbackParam ),
				'sslverify'   => true,
			)
		);

		return true;
	}
}