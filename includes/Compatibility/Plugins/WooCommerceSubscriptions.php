<?php
/**
 * Linear Checkout for WooCommerce by Cartimize
 * Copyright (c) 2020 Revmakx LLC
 * revmakx.com
 */

namespace Cartimize\Compatibility\Plugins;

use Cartimize\Compatibility\Core;

class WooCommerceSubscriptions extends Core {
	public function __construct() {
		parent::__construct();
	}

	public function is_available() {
		return class_exists('\\WC_Subscriptions_Cart');
	}

	public function pre_init() {
		// Remove display recurring total because we build this for our own function 
		if ( $this->is_available() ) {
			add_filter('cartimize_chosen_shipping_methods_labels', array($this, 'cart_chosen_shipping_methods_labels'), 10, 1 );
			remove_action( 'woocommerce_review_order_after_order_total',  'WC_Subscriptions_Cart::display_recurring_totals' );
			add_action( 'woocommerce_review_order_after_order_total', array( $this, 'display_recurring_totals' ), 10 );
		}
	}

	public function run() {
		if ( $this->is_available() ) {
			add_filter('woocommerce_checkout_registration_required', array($this, 'override_registration_required'), 10, 1 );
			add_filter('cartimize_contain_products', array($this, 'cart_contains_without_subscription'), 10, 1 );
			add_action('cartimize_after_shipping_method_list_html', array($this, 'subscription_shipping_methods'), 10 );
			// add_action('cartimize_before_subscription_shipping_method_list_html', array($this, 'subscription_product_list'), 10 );
		}

	}

	function override_registration_required( $result ) {
		if ( \WC_Subscriptions_Cart::cart_contains_subscription() && ! is_user_logged_in() ) {
			$result = true;
		}

		return $result;
	}

	public function subscription_shipping_methods(){
		\WC_Subscriptions_Cart::set_calculation_type( 'recurring_total' );
		if ( \WC_Subscriptions_Cart::cart_contains_subscriptions_needing_shipping() ) : ?>
			<div class="form-row-header"><?php echo esc_html__(apply_filters( 'cartimize_choose_your_shipping_method_for_your_subscriptions_text', __( "Choose your Shipping Method for your Subscriptions", 'linear-checkout-for-woo-by-cartimize' ) )); ?></div>
			<?php do_action( 'cartimize_before_subscription_shipping_method_list_html' ) ?>
			<?php $this->shipping_method_list_html() ?>
		<?php	
		endif;
		\WC_Subscriptions_Cart::set_calculation_type( 'none' );
	}

	public function cart_contains_without_subscription( $contains_without_subscription ) {
		$packages = WC()->shipping->get_packages();
		if ( count( $packages ) > 0 ) {
			return $contains_without_subscription;
		}
		$contains_without_subscription = false;
		if ( ! empty( WC()->cart->cart_contents ) && ! wcs_cart_contains_renewal() ) {
			foreach ( WC()->cart->cart_contents as $cart_item ) {
				if ( !\WC_Subscriptions_Product::is_subscription( $cart_item['data'] ) ) {
					$contains_without_subscription = true;
					break; 
				}
			}
		}

		return $contains_without_subscription;
	}

	public function subscription_product_list(){
		if (count( WC()->cart->recurring_carts ) <= 1) {
			return false;
		}
		if ( ! empty( WC()->cart->cart_contents ) && ! wcs_cart_contains_renewal() ) :
			?> <div class="shipping-product-info subcription-product"> <?php
				foreach ( WC()->cart->cart_contents as $cart_item_key =>  $cart_item ):
					if ( \WC_Subscriptions_Product::is_subscription( $cart_item['data'] ) ) :
						$_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
						if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_checkout_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
							$item_thumb    = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image( 'cartimize_cart_thumb' ), $cart_item, $cart_item_key );
							$item_title    = apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key );
							if ( apply_filters( 'cartimize_show_cart_item_discount', false ) ) {
			                    $item_title = $item_title . ' (' . $_product->get_price_html() . ') ';
			                }
			                ?>
			                <div class="cart-item-row cart-item-<?php echo esc_attr( $cart_item_key) ; ?>">
			                	<?php if ( $item_thumb ) : ?>
			                    <div class="prod-img">
			                        <?php echo wp_kses_post($item_thumb); ?>
			                    </div>
			                    <?php endif; ?>
			                    <div class="cartimize-cart-item-description meta-name-qty">
			                    	<div class="prod-name">
		    		                    <?php echo wp_kses_post($item_title); ?>
		                            </div>
			                    </div>
			                </div>

			                <?php
						}
					endif;
				endforeach;
			?> </div>
			<?php
		endif;	
	}

	public function shipping_method_list_html(){
	
		$initial_packages = WC()->shipping->get_packages();

		$show_package_details = count( WC()->cart->recurring_carts ) > 1 ? true : false;
		$show_package_name    = true;

		// Create new subscriptions for each subscription product in the cart (that is not a renewal)
		foreach ( WC()->cart->recurring_carts as $recurring_cart_key => $recurring_cart ) {


			// Create shipping packages for each subscription item
			if ( \WC_Subscriptions_Cart::cart_contains_subscriptions_needing_shipping() && 0 !== $recurring_cart->next_payment_date ) {

				// This will get a package with the 'recurring_cart_key' set to 'none' (because WC_Subscriptions_Cart::display_recurring_totals() set WC_Subscriptions_Cart::$calculation_type to 'recurring_total', but WC_Subscriptions_Cart::$recurring_cart_key has not been set), which ensures that it's a unique package, which we need in order to get all the available packages, not just the package for the recurring cart calculation we completed previously where WC_Subscriptions_Cart::filter_package_rates() removed all unchosen rates and which WC then cached
				$packages = $recurring_cart->get_shipping_packages();
				foreach ( $packages as $i => $base_package ) {
					$product_names = array();
					$base_package['recurring_cart_key'] = $recurring_cart_key;

					$package = \WC_Subscriptions_Cart::get_calculated_shipping_for_package( $base_package );
					$index   = sprintf( '%1$s_%2$d', $recurring_cart_key, $i );

					if ( $show_package_details ) {
						foreach ( $package['contents'] as $item_id => $values ) {
							$product_names[] = $values['data']->get_title() . ' &times;' . $values['quantity'];
						}
						$package_details = implode( ', ', $product_names );
					} else {
						$package_details = '';
					}

					$chosen_initial_method   = isset( WC()->session->chosen_shipping_methods[ $i ] ) ? WC()->session->chosen_shipping_methods[ $i ] : '';
					$chosen_recurring_method = isset( WC()->session->chosen_shipping_methods[ $recurring_cart_key . '_' . $i ] ) ? WC()->session->chosen_shipping_methods[ $recurring_cart_key . '_' . $i ] : $chosen_initial_method;

					$shipping_selection_displayed = false;

					if ( ( 1 === count( $package['rates'] ) ) || ( isset( $package['rates'][ $chosen_initial_method ] ) && isset( $initial_packages[ $i ] ) && $package['rates'] == $initial_packages[ $i ]['rates'] && apply_filters( 'wcs_cart_totals_shipping_html_price_only', true, $package, $recurring_cart ) ) ) {
						$shipping_method = ( 1 === count( $package['rates'] ) ) ? current( $package['rates'] ) : $package['rates'][ $chosen_initial_method ];
						// packages match, display shipping amounts only
						?>
						<div class="shipping recurring-total <?php echo esc_attr( $recurring_cart_key ); ?>">
							<div data-title="<?php echo esc_attr( sprintf( __( 'Shipping via %s', 'woocommerce-subscriptions' ), $shipping_method->label ) ); ?>">
								<?php echo wp_kses_post( wcs_cart_totals_shipping_method_price_label( $shipping_method, $recurring_cart ) ); ?>
								<?php if ( 1 === count( $package['rates'] ) ) : ?>
									<?php wcs_cart_print_shipping_input( $index, $shipping_method ); ?>
									<?php do_action( 'woocommerce_after_shipping_rate', $shipping_method, $index ); ?>
								<?php endif; ?>
								<?php if ( ! empty( $show_package_details ) ) : ?>
									<?php echo '<p class="woocommerce-shipping-contents"><small>' . esc_html( $package_details ) . '</small></p>'; ?>
								<?php endif; ?>
							</div>
						</div>
						<?php
					} else {
						// Display the options
						$product_names = array();

						$shipping_selection_displayed = true;

						if ( $show_package_name ) {
							$package_name = apply_filters( 'woocommerce_shipping_package_name', sprintf( _n( 'Shipping', 'Shipping %d', ( $i + 1 ), 'woocommerce-subscriptions' ), ( $i + 1 ) ), $i, $package );
						} else {
							$package_name = '';
						}

						$package              = $package;
						$available_methods    = $package['rates'];
						$show_package_details = $show_package_details;
						$package_details      = $package_details;
						$package_name         = $package_name;
						$index                = $index;
						$chosen_method        = $chosen_recurring_method;
						$recurring_cart_key   = $recurring_cart_key;
						$recurring_cart       = $recurring_cart;
						?>	


						<?php if ( \WC_Subscriptions::is_woocommerce_pre( '2.6' ) && is_cart() ) : // WC < 2.6 did not allow string indexes for shipping methods on the cart page and there was no way to hook in ?>
						<div>
							<?php echo wp_kses_post( wpautop( __( 'Recurring shipping options can be selected on checkout.', 'woocommerce-subscriptions' ) ) ); ?>
						</div>
						<?php elseif ( 1 < count( $available_methods ) ) : ?>
							<div class="custom-radio ul shipping-methods" id="shipping_method_<?php echo esc_attr( $recurring_cart_key ); ?>">
								<?php foreach ( $available_methods as $method ) : ?>
									<div class="li">
										<?php
											wcs_cart_print_shipping_input( $index, $method, $chosen_method, 'radio' );
											printf( '<label for="shipping_method_%1$s_%2$s">%3$s</label>', esc_attr( $index ), esc_attr( sanitize_title( $method->id ) ), wp_kses_post( wcs_cart_totals_shipping_method( $method, $recurring_cart ) ) );
											do_action( 'woocommerce_after_shipping_rate', $method, $index );
										?>
									</div>
								<?php endforeach; ?>
							</div>
						<?php elseif ( ! WC()->customer->has_calculated_shipping() ) : ?>
							<div>
								<?php echo wp_kses_post( wpautop( __( 'Shipping costs will be calculated once you have provided your address.', 'woocommerce-subscriptions' ) ) ); ?>
							</div>
						<?php else : ?>
							<div>
								<?php echo wp_kses_post( apply_filters( 'woocommerce_no_shipping_available_html', wpautop( __( 'There are no shipping methods available. Please double check your address, or contact us if you need any help.', 'woocommerce-subscriptions' ) ) ) ); ?>
							</div>
						<?php endif; ?>

						<?php if ( $show_package_details ) : ?>
							<div>
								<?php echo '<p class="woocommerce-shipping-contents"><small>' . esc_html( $package_details ) . '</small></p>'; ?>
							</div>
						<?php endif; ?>


						<?php
						$show_package_name = false;
					}
					do_action( 'woocommerce_subscriptions_after_recurring_shipping_rates', $index, $base_package, $recurring_cart, $chosen_recurring_method, $shipping_selection_displayed );
				}
			}
		}
	}

	public function cart_chosen_shipping_methods_labels( $chosen_shipping_methods_labels ){
		\WC_Subscriptions_Cart::set_calculation_type( 'recurring_total' );
		foreach ( WC()->cart->recurring_carts as $recurring_cart_key => $recurring_cart ) {
			if ( \WC_Subscriptions_Cart::cart_contains_subscriptions_needing_shipping() && 0 !== $recurring_cart->next_payment_date ) {
				$packages = $recurring_cart->get_shipping_packages();
				foreach ( $packages as $i => $base_package ) {
					$package = \WC_Subscriptions_Cart::get_calculated_shipping_for_package( $base_package );
					$chosen_initial_method   = isset( WC()->session->chosen_shipping_methods[ $i ] ) ? WC()->session->chosen_shipping_methods[ $i ] : '';
					$chosen_recurring_method = isset( WC()->session->chosen_shipping_methods[ $recurring_cart_key . '_' . $i ] ) ? WC()->session->chosen_shipping_methods[ $recurring_cart_key . '_' . $i ] : $chosen_initial_method;
					if ( $chosen_recurring_method ) {
						$available_methods    = $package['rates'];
						$chosen_shipping_methods_labels[] = '<span class="txt-light">'.wp_kses_post( wcs_cart_totals_shipping_method( $available_methods[ $chosen_recurring_method ], $recurring_cart ) ).'</span><span> '.get_woocommerce_currency_symbol().$available_methods[ $chosen_recurring_method ]->get_cost().'</span>';
					}
				}
			}
		}
		\WC_Subscriptions_Cart::set_calculation_type( 'none' );
		return $chosen_shipping_methods_labels;
	}

	public function display_recurring_totals(){
		if ( \WC_Subscriptions_Cart::cart_contains_subscription() ) {

			// We only want shipping for recurring amounts, and they need to be calculated again here
			\WC_Subscriptions_Cart::set_calculation_type( 'recurring_total' );

			$shipping_methods = array();

			$carts_with_multiple_payments = 0;

			$recurring_carts = WC()->cart->recurring_carts;;


			// Create new subscriptions for each subscription product in the cart (that is not a renewal)
			foreach ( $recurring_carts as $recurring_cart_key => $recurring_cart ) {

				// Cart contains more than one payment
				if ( 0 != $recurring_cart->next_payment_date ) {
					$carts_with_multiple_payments++;
				}
			}

			if ( $carts_with_multiple_payments >= 1 ) {
				// wc_get_template( 'checkout/recurring-totals.php', array( 'shipping_methods' => $shipping_methods, 'recurring_carts' => $recurring_carts, 'carts_with_multiple_payments' => $carts_with_multiple_payments ), '', plugin_dir_path( WC_Subscriptions::$plugin_file ) . 'templates/' );
				$display_th = true;
				?>
				<tr class="spacer">
					<td colspan="2"><hr></hr></td>
				</tr>
				<tr class="recurring-totals">
					<th colspan="2"><?php esc_html_e( 'Recurring Totals', 'woocommerce-subscriptions' ); ?></th>
				</tr>
				<?php foreach ( $recurring_carts as $recurring_cart_key => $recurring_cart ) : ?>
					<?php if ( 0 == $recurring_cart->next_payment_date ) : ?>
						<?php continue; ?>
					<?php endif; ?>
					<tr class="cart-subtotal recurring-total">
						<?php if ( $display_th ) : $display_th = false; ?>
							<th rowspan="<?php echo esc_attr( $carts_with_multiple_payments ); ?>"><?php esc_html_e( 'Subtotal', 'woocommerce-subscriptions' ); ?></th>
							<td data-title="<?php esc_attr_e( 'Subtotal', 'woocommerce-subscriptions' ); ?>"><span class="processing-hide"><?php wcs_cart_totals_subtotal_html( $recurring_cart ); ?></span></td>
						<?php else : ?>
							<td><?php wcs_cart_totals_subtotal_html( $recurring_cart ); ?></td>
						<?php endif; ?>
					</tr>
				<?php endforeach; ?>
				<?php $display_th = true; ?>

				<?php foreach ( WC()->cart->get_coupons() as $code => $coupon ) : ?>
					<?php foreach ( $recurring_carts as $recurring_cart_key => $recurring_cart ) : ?>
						<?php if ( 0 == $recurring_cart->next_payment_date ) : ?>
							<?php continue; ?>
						<?php endif; ?>
						<?php foreach ( $recurring_cart->get_coupons() as $recurring_code => $recurring_coupon ) : ?>
							<?php if ( $recurring_code !== $code ) { continue; } ?>
								<tr class="cart-discount coupon-<?php echo esc_attr( $code ); ?> recurring-total">
									<?php if ( $display_th ) : $display_th = false; ?>
										<th rowspan="<?php echo esc_attr( $carts_with_multiple_payments ); ?>"><?php echo esc_html__('Discount', 'linear-checkout-for-woo-by-cartimize'); ?>:</th>
										<td data-title="<?php wc_cart_totals_coupon_label( $coupon ); ?>"><?php wcs_cart_totals_coupon_html( $recurring_coupon, $recurring_cart ); ?></td>
									<?php else : ?>
										<td><?php wcs_cart_totals_coupon_html( $recurring_coupon, $recurring_cart ); ?></td>
									<?php endif; ?>
								</tr>
						<?php endforeach; ?>
					<?php endforeach; ?>
					<?php $display_th = true; ?>
				<?php endforeach; ?>

				<?php foreach ( WC()->cart->get_fees() as $fee ) : ?>
					<?php foreach ( $recurring_carts as $recurring_cart_key => $recurring_cart ) : ?>
						<?php if ( 0 == $recurring_cart->next_payment_date ) : ?>
							<?php continue; ?>
						<?php endif; ?>
						<?php foreach ( $recurring_cart->get_fees() as $recurring_fee ) : ?>
							<?php if ( $recurring_fee->id !== $fee->id ) { continue; } ?>
							<tr class="fee recurring-total">
								<th><?php echo esc_html( $fee->name ); ?></th>
								<td><?php wc_cart_totals_fee_html( $fee ); ?></td>
							</tr>
						<?php endforeach; ?>
					<?php endforeach; ?>
				<?php endforeach; ?>

				<?php if ( WC()->cart->tax_display_cart === 'excl' ) : ?>
					<?php if ( get_option( 'woocommerce_tax_total_display' ) === 'itemized' ) : ?>

						<?php foreach ( WC()->cart->get_taxes() as $tax_id => $tax_total ) : ?>
							<?php foreach ( $recurring_carts as $recurring_cart_key => $recurring_cart ) : ?>
								<?php if ( 0 == $recurring_cart->next_payment_date ) : ?>
									<?php continue; ?>
								<?php endif; ?>
								<?php foreach ( $recurring_cart->get_tax_totals() as $recurring_code => $recurring_tax ) : ?>
									<?php if ( ! isset( $recurring_tax->tax_rate_id ) || $recurring_tax->tax_rate_id !== $tax_id ) { continue; } ?>
									<tr class="tax-rate tax-rate-<?php echo esc_attr( sanitize_title( $recurring_code ) ); ?> recurring-total">
										<?php if ( $display_th ) : $display_th = false; ?>
											<th><?php echo esc_html( $recurring_tax->label ); ?></th>
											<td data-title="<?php echo esc_attr( $recurring_tax->label ); ?>"><?php echo wp_kses_post( wcs_cart_price_string( $recurring_tax->formatted_amount, $recurring_cart ) ); ?></td>
										<?php else : ?>
											<th></th>
											<td><?php echo wp_kses_post( wcs_cart_price_string( $recurring_tax->formatted_amount, $recurring_cart ) ); ?></td>
										<?php endif; ?>
									</tr>
								<?php endforeach; ?>
							<?php endforeach; ?>
							<?php $display_th = true; ?>
						<?php endforeach; ?>

					<?php else : ?>

						<?php foreach ( $recurring_carts as $recurring_cart_key => $recurring_cart ) : ?>
							<?php if ( 0 == $recurring_cart->next_payment_date ) : ?>
								<?php continue; ?>
							<?php endif; ?>
							<tr class="tax-total recurring-total">
								<?php if ( $display_th ) : $display_th = false; ?>
									<th><?php echo esc_html( WC()->countries->tax_or_vat() ); ?></th>
									<td data-title="<?php echo esc_attr( WC()->countries->tax_or_vat() ); ?>"><?php echo wp_kses_post( wcs_cart_price_string( $recurring_cart->get_taxes_total(), $recurring_cart ) ); ?></td>
								<?php else : ?>
									<th></th>
									<td><?php echo wp_kses_post( wcs_cart_price_string( $recurring_cart->get_taxes_total(), $recurring_cart ) ); ?></td>
								<?php endif; ?>
							</tr>
						<?php endforeach; ?>
						<?php $display_th = true; ?>
					<?php endif; ?>
				<?php endif; ?>

				<?php $display_tr = true; ?>

				<?php foreach ( $recurring_carts as $recurring_cart_key => $recurring_cart ) : ?>
					<?php if ( 0 == $recurring_cart->next_payment_date ) : ?>
						<?php continue; ?>
					<?php endif; ?>
					<?php if ( $display_tr ) : $display_tr = false; ?>
						<tr class="spacer"><td colspan="2"></td></tr>
					<?php endif; ?>
					<tr class="order-total recurring-total">
						<?php if ( $display_th ) : $display_th = false; ?>
							<th rowspan="<?php echo esc_attr( $carts_with_multiple_payments ); ?>">
							<?php 
								if ( cartimize_estimate_shipping_and_order_total() ) {
									$title_total = esc_html__('Estimated Recurring Total', 'linear-checkout-for-woo-by-cartimize');
									esc_html_e( 'Estimated Recurring Total', 'linear-checkout-for-woo-by-cartimize' ); 
								}else{
									$title_total = esc_html__('Recurring Total', 'woocommerce-subscriptions');
							 		esc_html_e( 'Recurring Total', 'woocommerce-subscriptions' ); 
								}
							
							?>
							</th>
							<td data-title="<?php esc_attr_e( $title_total, 'woocommerce-subscriptions' ); ?>"><span class="processing-hide"><?php wcs_cart_totals_order_total_html( $recurring_cart ); ?></span></td>
						<?php else : ?>
							<td><span class="processing-hide"><?php wcs_cart_totals_order_total_html( $recurring_cart ); ?></span></td>
						<?php endif; ?>
					</tr>
				<?php endforeach; ?>
				<?php

			}

			\WC_Subscriptions_Cart::set_calculation_type( 'none' );
		}
	}

}
