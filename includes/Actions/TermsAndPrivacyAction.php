<?php 
/**
 * Linear Checkout for WooCommerce by Cartimize
 * Copyright (c) 2020 Revmakx LLC
 * revmakx.com
 */

namespace Cartimize\Actions;
use Cartimize\Core\ActionCore;

class TermsAndPrivacyAction extends ActionCore {

	
	public function __construct( $id, $no_privilege, $action_prefix ) {
		parent::__construct( $id, $no_privilege, $action_prefix );
	}

	public function action() {

		if ( isset( $_POST['page'] ) && wc_clean( wp_unslash( $_POST['page'] ) ) == 'privacy_link' ) {
			$page_id = wc_privacy_policy_page_id();
		}else{
			$page_id   = wc_terms_and_conditions_page_id();
		}

		$content = get_post_field('post_content', $page_id);
		$content = apply_filters('the_content', $content);
		$content = str_replace(']]>', ']]>', $content);
		
		$this->out( array( 'html' => $content, 'page'=> wc_clean( wp_unslash( $_POST['page'] ) ) ) );
	}
}
