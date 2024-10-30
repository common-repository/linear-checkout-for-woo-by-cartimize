<?php
/**
 * Linear Checkout for WooCommerce by Cartimize
 * Copyright (c) 2020 Revmakx LLC
 * revmakx.com
 */
namespace Cartimize\License\Actions;
use Cartimize\Core\ActionCore;

class DismissNotice extends ActionCore{
	public function __construct( $id, $no_privilege, $action_prefix ) {
		parent::__construct( $id, $no_privilege, $action_prefix );
	}

	public function action() {
		$response = array();
		try{
			
			if ( isset( $_POST['action'] ) && $_POST['action'] == 'dismiss_notice' && isset( $_POST['notice_type'] ) ) {
				$notice_type = wc_clean( wp_unslash( $_POST['notice_type'] ) );
				update_option('_cartimize_'.$notice_type, 1);
			}
			$response['status'] = $result ? 'success' : 'error';
		}
		catch(\CartimizeException $e){
			$error = $e->getError();
			$error_msg = $e->getErrorMsg();
		
			$response['status'] = 'error';
			$response['error_msg'] = $error_msg;
			$response['error_code'] = $error;
		}

		$this->out( $response );
	}
}