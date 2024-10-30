<?php
/**
 * Linear Checkout for WooCommerce by Cartimize
 * Copyright (c) 2020 Revmakx LLC
 * revmakx.com
 */

namespace Cartimize\Compatibility\Gateways;

use Cartimize\Compatibility\Core;


class KlarnaPayment extends Core {

	protected $klarna_payments = null;

	public function __construct() {
		parent::__construct();
	}

	function is_available() {
		return class_exists( '\\WC_Klarna_Payments' );
	}

	function pre_init() {
		add_action( 'cartimize_payment_gateway_list_klarna_payments_alternate', array( $this, 'klarna_payments_content' ), 10, 1 );
		add_filter( 'cartimize_show_gateway_klarna_payments', '__return_false' );
	}

	function klarna_payments_content( $count ) {
		do_action( 'klarna_payments_template' );

		if ( is_array( WC()->session->get( 'klarna_payments_categories' ) ) ) {
			$available_gateways = WC()->payment_gateways()->get_available_payment_gateways();
			$current_gateway    = WC()->session->get( 'chosen_payment_method' );

			foreach ( apply_filters( 'wc_klarna_payments_available_payment_categories', WC()->session->get( 'klarna_payments_categories' ) ) as $payment_category ) {
				$payment_category      = is_array( $payment_category ) ? $this->toObject( $payment_category ) : $payment_category;
				$payment_category_id   = 'klarna_payments_' . $payment_category->identifier;
				$payment_category_name = $payment_category->name;
				$payment_category_icon = $payment_category->asset_urls->standard;
				$kp                    = $available_gateways['klarna_payments'];
				$kp->id                = $payment_category_id;
				$kp->title             = $payment_category_name;

				?>
				<li class=" li wc_payment_method payment_method_<?php echo esc_attr( $kp->id ); ?> cartimize-radio-reveal-li">
						<input id="payment_method_<?php echo esc_attr( $kp->id ); ?>" type="radio" class="input-radio" name="payment_method" value="<?php echo esc_attr( $kp->id ); ?>" <?php echo ( ( empty( $current_gateway ) && $count == 0 ) || stripos( $current_gateway, 'klarna_payments' ) !== false ) ? 'checked' : ''; ?> data-order_button_text="<?php echo esc_attr( $kp->order_button_text ); ?>" />
						<label class="payment_method_label cartimize-radio-reveal-label" for="payment_method_<?php echo esc_attr($kp->id); ?>">
							<span class="payment_method_title cartimize-radio-reveal-title"><?php echo wp_kses_post($kp->get_title()); ?></span>
						</label>
					<?php if ( apply_filters( "cartimize_payment_gateway_{$kp->id}_content", $kp->has_fields() || $kp->get_description() ) ) : ?>
						<div class=" payment-form-container payment_box payment_method_<?php echo esc_attr($kp->id); ?> cartimize-radio-reveal-content" 
																		  <?php
																			if ( ! $kp->chosen ) :
																				?>
							style="display:none;"<?php endif; ?>>
							<?php
							ob_start();
							$kp->payment_fields();

							$field_html = ob_get_clean();

							/**
							 * Gateway Compatibility Patches
							 */
							// Expiration field fix
							$field_html = str_ireplace( 'js-sv-wc-payment-gateway-credit-card-form-expiry', 'js-sv-wc-payment-gateway-credit-card-form-expiry  wc-credit-card-form-card-expiry', $field_html );
							$field_html = str_ireplace( 'js-sv-wc-payment-gateway-credit-card-form-account-number', 'js-sv-wc-payment-gateway-credit-card-form-account-number  wc-credit-card-form-card-number', $field_html );

							// Credit Card Field Placeholders
							$field_html = str_ireplace( '•••• •••• •••• ••••', 'Card Number', $field_html );
							$field_html = str_ireplace( '&bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull;', 'Card Number', $field_html );

							echo apply_filters( "cartimize_payment_gateway_field_html_{$kp->id}", $field_html );// PHPCS:Ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							?>
						</div>
					<?php endif; ?>
				</li>
				<?php
			}
		}
	}

	function typescript_class_and_params( $compatibility ) {
		$compatibility[] = [
			'class'  => 'KlarnaPayments',
			'params' => [],
		];

		return $compatibility;
	}

	function toObject( $array ) {
		$obj = new \stdClass();

		foreach ( $array as $key => $val ) {
			$obj->$key = is_array( $val ) ? $this->toObject( $val ) : $val;
		}

		return $obj;
	}
	
}
