<?php
/**
 * Linear Checkout for WooCommerce by Cartimize
 * Copyright (c) 2020 Revmakx LLC
 * revmakx.com
 */
namespace Cartimize\Stats\Actions;
use Cartimize\Core\ActionCore;

class ReportCollection extends ActionCore{
	public function __construct( $id, $no_privilege, $action_prefix ) {
		parent::__construct( $id, $no_privilege, $action_prefix );
	}

	public function action() {
		global $cartimize;
		$response = array();

		try{
			$result = $cartimize->get_stats_instance()->build_data( true );
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
}