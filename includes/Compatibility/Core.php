<?php 
/**
 * Linear Checkout for WooCommerce by Cartimize
 * Copyright (c) 2020 Revmakx LLC
 * revmakx.com
 */

namespace Cartimize\Compatibility;

use Cartimize\Cartimize;

abstract class Core {

	public function __construct() {
	    $this->pre_init();

	    add_action( 'init', array( $this, 'compat_init') );
	}

	public function compat_init() {
		if ( $this->is_available() && ( ! is_admin() || wp_doing_ajax() ) ) {
			// Allow scripts and styles for certain plugins
			add_filter( 'cartimize_remove_style_handles', array( $this, 'remove_theme_styles' ), 10, 1 );
			add_filter( 'wp_get_custom_css', array( $this, 'remove_custom_css' ), 10, 2 );
			add_filter( 'cartimize_remove_style_handles', array( $this, 'remove_styles' ), 10, 1 );
			add_filter( 'cartimize_remove_script_handles', array( $this, 'remove_theme_scripts' ), 10, 1 );
			add_filter( 'cartimize_remove_script_handles', array( $this, 'remove_scripts' ), 10, 1 );
			add_filter( 'cartimize_typescript_compatibility_classes_and_params', array( $this, 'typescript_class_and_params' ), 10, 1 );

			// Run if on checkout
			$this->run_immediately();
            add_action( 'wp', array($this, 'run_on_checkout'), 0 );
			add_action( 'wp_loaded', array( $this, 'run_on_wp_loaded' ), 0 );
		}
    }

    public function pre_init() {
        // Silence is golden
    }

    public function run_on_checkout() {
	    if ( Cartimize::is_cartimize_checkout() || Cartimize::is_cartimize_checkout_pay_page() ) {
	        $this->run();
        }
    }

    public function run_on_order_received() {
	    if ( Cartimize::is_order_received_page() ) {
		    $this->run_on_thankyou();
	    }
    }

    public function run_on_thankyou() {
	    // Silence is golden
    }

	public function run() {
		// Silence be golden
	}

	public function run_immediately() {
		// Silence be golden
	}

	public function run_on_wp_loaded() {
        // Silence is golden
    }

	public function is_available() {
		return false;
	}

	function typescript_class_and_params( $compatibility ) {
		return $compatibility;
	}

	public function remove_styles( $styles ) {
		return $styles;
	}

	public function remove_theme_styles( $styles ) {
		global $wp_styles;

		$theme_directory_uri = get_theme_root_uri();
		$theme_directory_uri = str_replace( array('http:', 'https:'), '', $theme_directory_uri ); // handle both http/https/and relative protocol URLs

		foreach ( $wp_styles->registered as $wp_style ) {
			if ( ! empty($wp_style->src) && stripos( $wp_style->src, $theme_directory_uri ) !== false && stripos( $wp_style->src, '/'.CARTIMIZE_SLUG.'/' ) === false ) {
				$styles[] = $wp_style->handle;
			}
		}

		return $styles;
	}

    public function remove_scripts( $scripts ) {
	    return $scripts;
    }

	function remove_theme_scripts( $scripts ) {
		global $wp_scripts;

		$theme_directory_uri = get_theme_root_uri();
		$theme_directory_uri = str_replace( array('http:', 'https:'), '', $theme_directory_uri ); // handle both http/https/and relative protocol URLs

		foreach ( $wp_scripts->registered as $wp_script ) {
			if ( ! empty($wp_script->src) && stripos( $wp_script->src, $theme_directory_uri) !== false && stripos( $wp_script->src, '/'.CARTIMIZE_SLUG.'/' ) === false ) {
				$scripts[] = $wp_script->handle;
            }
		}

		return $scripts;
	}

	function remove_custom_css( $css, $stylesheet ){
		if ( !Cartimize::is_cartimize_checkout()) {
			return $css;
		}
	}

	function add_separator( $class = '', $id = '', $style = '' ) {
		if ( ! defined( 'CARTIMIZE_PAYMENT_BUTTON_SEPARATOR' ) ) {
			define( 'CARTIMIZE_PAYMENT_BUTTON_SEPARATOR', true );
		} else {
			return;
		}
		?>
		<div id="payment-info-separator-wrap" class="<?php echo esc_attr($class); ?>">
			<span <?php echo ( $id ) ? "id='".esc_attr($id)."'" : ''; ?> <?php echo ( $style ) ? "style='".esc_attr($style)."'" : ''; ?> class="pay-button-separator">
				<?php echo esc_html( apply_filters( 'cartimize_express_pay_separator_text', __( 'Or', 'linear-checkout-for-woo-by-cartimize' ) ) ); ?>
			</span>
		</div>
		<?php
	}

}