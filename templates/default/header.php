<?php
/**
 * Linear Checkout for WooCommerce by Cartimize
 * Copyright (c) 2020 Revmakx LLC
 * revmakx.com
 */
 
if ( ! defined( 'ABSPATH' ) ) {
	exit; 
}
?>
<header class="top">
	<div class="main">
  		<div class="logo">
  			<?php 
                echo wp_kses_post(apply_filters( 'cartimize_header_logo', get_theme_mod( 'custom_logo' ) )); 
            ?>
  		</div>
  		<?php do_action( 'cartimize_header_content' ) ?>
		<?php do_action( 'cartimize_header_after_logo_content' ) ?>
  	</div>
</header>