<?php 
/**
 * Linear Checkout for WooCommerce by Cartimize
 * Copyright (c) 2020 Revmakx LLC
 * revmakx.com
 */

namespace Cartimize\Actions;
use Cartimize\Core\ActionCore;

class TermsConditionAction extends ActionCore {

	
	public function __construct( $id, $no_privilege, $action_prefix ) {
		parent::__construct( $id, $no_privilege, $action_prefix );
	}

	public function action() {

		$page_id = get_option( 'wp_page_for_privacy_policy', 0 );

		$post = get_post($page_id); 
		$content = apply_filters('the_content', $post->post_content); 
		
		$this->out( array( 'html' => $content) );
	}
}
