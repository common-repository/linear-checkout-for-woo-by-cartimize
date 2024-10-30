<?php
/**
 * Linear Checkout for WooCommerce by Cartimize
 * Copyright (c) 2020 Revmakx LLC
 * revmakx.com
 */

namespace Cartimize\Compatibility\Themes;

use Cartimize\Compatibility\Core;

class TMOrganik extends Core {
	public function is_available() {
		return class_exists( '\\Insight_Functions' );
	}

	public function run() {
		add_action( 'wp_head', array( $this, 'shim_headroom' ) );
	}

	public function shim_headroom() {
		?>
		<script type="text/javascript">
			jQuery(document).ready( function() {
				jQuery.fn.headroom = function () {};
			} );
		</script>
		<?php
	}
}
