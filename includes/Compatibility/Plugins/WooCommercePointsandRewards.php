<?php
/**
 * Linear Checkout for WooCommerce by Cartimize
 * Copyright (c) 2020 Revmakx LLC
 * revmakx.com
 */

namespace Cartimize\Compatibility\Plugins;

use Cartimize\Compatibility\Core;

class WooCommercePointsandRewards extends Core {
	public function is_available() {
		return function_exists( 'woocommerce_points_and_rewards_missing_wc_notice' );
	}

	public function run() {
		add_action( 'cartimize_wp_head', array( $this, 'add_helper_script' ) );
	}

	function add_helper_script() {
		?>
		<script type="text/javascript">
			jQuery( document ).ready( function() {
				jQuery( document.body ).on( 'cartimize-apply-coupon-success', function() {
					jQuery( ".wc_points_rewards_earn_points" ).remove();
					jQuery( ".wc_points_redeem_earn_points" ).remove();
				} );
			} );
		</script>
		<?php
	}
}
