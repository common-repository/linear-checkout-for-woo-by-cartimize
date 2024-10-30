<?php
/**
 * Linear Checkout for WooCommerce by Cartimize
 * Copyright (c) 2020 Revmakx LLC
 * revmakx.com
 */

namespace Cartimize\Stats;

use Cartimize\Cartimize;
use \WC_Report_Sales_By_Date;
use \DateInterval;
use \DateTime;

class AnonymousDataCollection{

	private $data = [];

	private $approved_woocommerce_settings = [];

	private $approved_woocomerce_store_stats = [];

	private $settings_controller = null;

	private $license_controller = null;

	private $woocommerce_settings = [];

	private $anonymous_collection_url = 'https://service.cartimize.com/stats/';

	private $last_send_key = 'anonymous_last_send';

	private $is_deactive = false;

	protected static $instance = array();

	public function __construct( $settings_controller = null, $license_controller = null ) {
		$this->settings_controller = $settings_controller;
		$this->license_controller = $license_controller;

		add_action( 'init', array( $this, 'schedule_send' ) );
		add_filter( 'cron_schedules', array( $this, 'add_schedules' ) );

		$this->approved_woocommerce_settings = [
			'woocommerce_default_country',
			'woocommerce_default_customer_address',
			'woocommerce_calc_taxes',
			'woocommerce_enable_coupons',
			'woocommerce_calc_discounts_sequentially',
			'woocommerce_currency',
			'woocommerce_prices_include_tax',
			'woocommerce_tax_based_on',
			'woocommerce_tax_round_at_subtotal',
			'woocommerce_tax_classes',
			'woocommerce_tax_display_shop',
			'woocommerce_tax_display_cart',
			'woocommerce_tax_total_display',
			'woocommerce_enable_shipping_calc',
			'woocommerce_shipping_cost_requires_address',
			'woocommerce_ship_to_destination',
			'woocommerce_enable_guest_checkout',
			'woocommerce_enable_checkout_login_reminder',
			'woocommerce_enable_signup_and_login_from_checkout',
			'woocommerce_registration_generate_username',
			'woocommerce_registration_generate_password',
		];

		$this->approved_woocomerce_store_stats = [
			'total_sales',
			'total_orders',
			'total_items',
		];

		if ( isset( $_REQUEST['force-send'] ) && $_REQUEST['force-send'] == 1) {
			add_action( 'init', function() {
				$this->build_send( true );
            } );
        }
	}

	public function schedule_send() {
		if ( $this->is_allowed() ) {
			add_action( 'cartimize_daily_scheduled_events_tracking', array( $this, 'build_send' ) );
		}
	}

	public function is_allowed(){
		$site_token = $this->license_controller->get_setting( 'site_token' );
		$is_beta_program = $this->settings_controller->get_setting( 'is_beta_program' );

		if ( $site_token == false || $is_beta_program == false ) {
			return false;
		}

		return true;
	}

	public function build_send( $override = false, $ignore_last_checkin = true ) {
		$home_url = trailingslashit( home_url() );

		// Allows us to stop our own site from checking in, and a filter for our additional sites.
		if ( apply_filters( 'cartimize_disable_tracking_checkin', false, $home_url ) ) {
			return false;
		}

		if ( ! $this->is_allowed() && ! $override ) {
			return false;
		}

		// Send a maximum of once per week.
		$last_send = $this->get_last_send();
		if ( is_numeric( $last_send ) && $last_send > strtotime( '-1 week' ) && ! $ignore_last_checkin ) {
			return false;
		}

		$this->build_data();
		$remote_url = $this->anonymous_collection_url;

		wp_remote_request(
			$remote_url,
			array(
				'method'      => 'POST',
				'headers'     => [
					'Content-Type' => 'application/json',
				],
				'timeout'     => 8,
				'redirection' => 5,
				'httpversion' => '1.1',
				'body'        => wp_json_encode( $this->data ),
				'sslverify'   => true,
			)
		);

		$this->settings_controller->update_setting( $this->last_send_key, time() );

		return true;

	}

	public function build_data( $return = false, $is_feedback = false ) {
		$data                = array();
		$home_url = trailingslashit( home_url() );
		$cartimize_version         = defined( 'CARTIMIZE_VERSION' ) ? CARTIMIZE_VERSION : Cartimize::instance()->get_version();

		// Retrieve memory limit info.
		$database_version = wc_get_server_database_version();
		$memory           = wc_let_to_num( WP_MEMORY_LIMIT );

		if ( function_exists( 'memory_get_usage' ) ) {
			$system_memory = wc_let_to_num( @ini_get( 'memory_limit' ) );
			$memory        = max( $memory, $system_memory );
		}
		
		$woo_version = defined( 'WC_VERSION' )? WC_VERSION : false; 

		$is_dev_site = $this->is_dev_and_staging_domains( $home_url );

		$user_id = $this->license_controller->get_setting( 'user_id' );
		$user_id = $user_id ? $user_id: '';

		$email = $this->license_controller->get_setting( 'email' );
		if ( empty($email) && $is_feedback ) {
			$current_user = wp_get_current_user();
			$email = $current_user->user_email;
		}
		$email = $email ? $email: '';

		$site_token  = $this->license_controller->get_setting( 'site_token' );
		$site_token  = $site_token ? base64_decode($site_token): false;

		$plugins                   = $this->get_plugins();
		$themes                    = $this->get_themes();
		// $checkout_page             = $this->get_option( $this->tracked_page_key );
		$wp_data['wp_debug_mode']  = ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ? 'yes' : 'no';
		$data['user_id']				   = $user_id;
		$data['email']				   	   = $email;
		$data['site_token']				   = $site_token;
		$data['php_version']               = phpversion();
		$data['cartimize_version']         = $cartimize_version;
		$data['wp_version']                = get_bloginfo( 'version' );
		$data['mysql_version']             = $database_version['number'];
		$data['woo_version']               = $woo_version;
		$data['server']                    = isset( $_SERVER['SERVER_SOFTWARE'] ) ? $_SERVER['SERVER_SOFTWARE'] : '';
		$data['php_max_upload_size']       = size_format( wp_max_upload_size() );
		$data['php_default_timezone']      = date_default_timezone_get();
		$data['php_fsockopen']             = function_exists( 'fsockopen' ) ? 'yes' : 'no';
		$data['php_curl']                  = function_exists( 'curl_init' ) ? 'yes' : 'no';
		$data['memory_limit']              = size_format( $memory );
		// $data['install_date']              = false !== $checkout_page ? get_post_field( 'post_date', $checkout_page ) : null;
		$data['multisite']                 = is_multisite();
		$data['locale']                    = get_locale();
		$data['theme']                     = self::get_theme_info();
		$data['gateways']                  = self::get_active_payment_gateways();
		$data['wc_order_stats']            = $this->get_woo_order_stats();
		$data['wc_order_stats']['site_id'] = md5( get_site_url() );
		$data['shipping_methods']          = self::get_active_shipping_methods();
		$data['wc_settings']               = $this->get_woo_site_settings();
		$data['inactive_plugins']          = isset( $plugins['inactive'] ) ? $plugins['inactive'] : array();
		$data['active_plugins']            = isset( $plugins['active'] ) ? $plugins['active'] : array();
		$data['inactive_themes']           = isset( $themes['inactive'] ) ? $themes['inactive'] : array();
		$data['active_themes']             = isset( $themes['active'] ) ? $themes['active'] : array();
		$data['debug_modes']               = $wp_data;
		$data['home_url']                  = $home_url;
		$data['is_dev_site']               = $is_dev_site;
		$data['is_deactive']               = $this->is_deactive;
		$data['from_feedback']             = $is_feedback;
		$data['skip_signup']               = $site_token ? false: $this->settings_controller->get_setting( 'skip_signup' );

		$this->data = $data;

		if ( $return ) {
			return $data;
		}
	}

	private function get_last_send() {
		return $this->settings_controller->get_setting( $this->last_send_key );
	}

	public function add_schedules( $schedules = array() ) {
		if ( ! isset( $schedules['daily'] ) ) {
			$schedules['daily'] = array(
				'interval' => 86400,
				'display'  => __( 'Once Daily' ),
			);
		}
		return $schedules;
	}

	public function is_dev_and_staging_domains( $home_url ) {
		$parts = parse_url( $home_url );

		/**
		 * Local dev domains
		 */
	    if ( stripos( $home_url, '.test' ) !== false ) {
	        return true;
        }

		if ( $parts['host'] == 'localhost' ) {
			return true;
		}

		if ( stripos( $home_url, '.local' ) !== false ) {
			return true;
		}

		/**
		 * Staging domains
		 */
		if ( stripos( $home_url, 'staging.' ) !== false ) {
			return true;
		}

		if ( stripos( $home_url, '/staging/' ) !== false ) {
			return true;
		}

		if ( stripos( $home_url, 'stage.' ) !== false ) {
			return true;
		}

		if ( stripos( $home_url, 'test.' ) !== false ) {
			return true;
		}

		if ( stripos( $home_url, '.development' ) !== false ) {
			return true;
		}

		if ( stripos( $home_url, 'wpengine.com' ) !== false ) {
			return true;
		}

		if ( stripos( $home_url, 'cloudwaysapps.com' ) !== false ) {
			return true;
		}

		if ( stripos( $home_url, 'pantheonsite.io' ) !== false ) {
			return true;
		}

		if ( stripos( $home_url, 'flywheelsites.com' ) !== false ) {
			return true;
		}

		if ( stripos( $home_url, 'liquidwebsites.com' ) !== false ) {
			return true;
		}

		if ( stripos( $home_url, 'c9users.io' ) !== false ) {
			return true;
		}

		if ( stripos( $home_url, 'kinsta.com' ) !== false ) {
			return true;
		}

		if ( stripos( $home_url, 'kinsta.cloud' ) !== false ) {
			return true;
		}

		if ( stripos( $home_url, 'dev.' ) !== false ) {
			return true;
		}

		/**
		 * IP Domains
		 */
		if ( ip2long( $parts['host'] ) ) {
			return true;
		}

        return false;
    }

    public static function get_theme_info() {
    	$theme_data        = wp_get_theme();
    	$theme_child_theme = wc_bool_to_string( is_child_theme() );
    	$theme_wc_support  = wc_bool_to_string( current_theme_supports( 'woocommerce' ) );

    	return array(
    		'name'        => $theme_data->Name, // @phpcs:ignore
    		'version'     => $theme_data->Version, // @phpcs:ignore
    		'child_theme' => $theme_child_theme,
    		'wc_support'  => $theme_wc_support,
    	);
    }

    private static function get_active_payment_gateways() {
    	$active_gateways = array();
    	$gateways        = WC()->payment_gateways->payment_gateways();
    	foreach ( $gateways as $id => $gateway ) {
    		if ( isset( $gateway->enabled ) && 'yes' === $gateway->enabled ) {
    			$active_gateways[] = $id;
    		}
    	}

    	return $active_gateways;
    }

    public function get_woo_order_stats( $interval = 'P7D' ) {
    	$wc_path        = WC()->plugin_path();
    	$collected_data = array();
    	$sales_by_date  = null;

    	include_once "$wc_path/includes/admin/reports/class-wc-admin-report.php";
    	include_once "$wc_path/includes/admin/reports/class-wc-report-sales-by-date.php";

    	if ( class_exists( 'WC_Report_Sales_By_Date' ) ) {
    		$sales_by_date                 = new WC_Report_Sales_By_Date();
    		$sales_by_date->start_date     = strtotime( '-6 days', strtotime( 'midnight', current_time( 'timestamp' ) ) );
    		$sales_by_date->end_date       = strtotime( 'midnight', current_time( 'timestamp' ) );
    		$sales_by_date->chart_groupby  = 'day';
    		$sales_by_date->group_by_query = 'YEAR(posts.post_date), MONTH(posts.post_date), DAY(posts.post_date)';
    	}

    	$report_data     = $sales_by_date->get_report_data();
    	$report_data_arr = get_object_vars( $report_data );

    	foreach ( $report_data_arr as $key => $value ) {
    		if ( in_array( $key, $this->approved_woocomerce_store_stats, true ) ) {
    			$collected_data[ $key ] = $value;
    		}
    	}

    	return $collected_data;
    }

    private static function get_active_shipping_methods() {

    	$shipping_methods = WC()->shipping->load_shipping_methods();
    	$active_methods   = array();
    	foreach ( $shipping_methods as $id => $shipping_method ) {
    		if ( isset( $shipping_method->enabled ) && 'yes' === $shipping_method->enabled ) {
    			$method_title = ! empty( $shipping_method->title ) ? $shipping_method->title : $shipping_method->method_title;
    			if ( 'international_delivery' === $id ) {
    				$method_title .= ' (International)';
    			}
    			array_push( $active_methods, array( 'id' => $method_title ) );
    		}
    	}

    	return $active_methods;
    }

    public function get_woo_site_settings() {
    	$settings_pages = \WC_Admin_Settings::get_settings_pages();

    	array_walk(
    		$settings_pages, function( $item ) {
    			if ( !is_callable( array( $item, "get_settings" ) ) ) {
    				return;
    			}
    			$settings = $item->get_settings();

    			array_walk(
    				$settings, function( $setting ) {
    					if ( empty( $setting['id'] ) ) {
    						return;
    					}

    					$settings = $this->get_woocommerce_settings();
    					$id       = $setting['id'];

    					if ( strpos( $id, 'woocommerce_' ) !== 0 ) {
    						$id = "woocommerce_{$id}";
    					}

    					if ( ! in_array( $id, $this->approved_woocommerce_settings ) ) {
    						return;
    					}

    					$setting_name = $id;
    					$settings[]   = $setting_name;
    					$this->set_woocommerce_settings( $settings );
    				}
    			);
    		}
    	);

    	$options      = (object) [ 'ops' => [] ];
    	$woo_settings = $this->get_woocommerce_settings();

    	array_walk(
    		$woo_settings, \Closure::bind(
    			function( $setting ) {
    				$op_value = get_option( $setting );

    				if ( $op_value === false ) {
    					return;
    				}

    				$this->ops[ $setting ] = get_option( $setting );
    			}, $options
    		)
    	);

    	return $options->ops;
    }

    public static function instance() {
		$class = (string) get_called_class();

		if (!array_key_exists($class, self::$instance)) {
			self::$instance[$class] = new static(...func_get_args());
		}

		return self::$instance[ $class ];
	}

	public function get_plugins() {
		// Retrieve current plugin information
		if ( ! function_exists( 'get_plugins' ) ) {
			include ABSPATH . '/wp-admin/includes/plugin.php';
		}

		$plugins        =  get_plugins() ;
		$active_plugins = get_option( 'active_plugins', array() );

		$plugins_list = [];

		foreach ( $plugins as $key => $plugin ) {
				// Remove active plugins from list so we can show active and inactive separately
			if ( in_array( $key, $active_plugins ) ) {
				$plugins_list['active'][] = array( 'slug' => $key, 'version' => $plugins[ $key ]['Version'] );
			} else {
				$plugins_list['inactive'][] = array( 'slug' => $key, 'version' => $plugins[ $key ]['Version'] );
			}
		}
		return $plugins_list;
	}

	public function get_themes(){
	    
	    $all_themes = wp_get_themes();	
	    $themes     = array(
				            'active' => array(),
				            'inactive' => array()
				        );
	    if (is_array($all_themes) && !empty($all_themes)) {
	        $current_theme = wp_get_theme();
	        
	        $br_a = 0;
	        $br_i = 0;
	        foreach ($all_themes as $theme_name => $theme) {

	            if ($current_theme == strip_tags($theme->Name)) {
	                $themes['active'][$br_a]       = array( 'slug' => $theme->Template, 'version' => $theme->Version );;
	                $br_a++;
	            }
	            
	            if ($current_theme != strip_tags($theme->Name)) {
	                $themes['inactive'][$br_i]       = array( 'slug' => $theme->Template, 'version' => $theme->Version );;
	                $br_i++;
	            }
	            
	        }
	        
	        if (!empty($search)) {
	            foreach ($themes['active'] as $k => $theme) {
	                if (!stristr($theme['name'], $search)) {
	                    unset($themes['active'][$k]);
	                }
	            }
	            
	            foreach ($themes['inactive'] as $k => $theme) {
	                if (!stristr($theme['name'], $search)) {
	                    unset($themes['inactive'][$k]);
	                }
	            }
	        }
	    }

	    return $themes;
	}

	public function get_woocommerce_settings() {
		return $this->woocommerce_settings;
	}

	public function set_woocommerce_settings( $woocommerce_settings ) {
		$this->woocommerce_settings = $woocommerce_settings;
	}
	
	public function get_data() {
		return $this->data;
	}

	public function send_deactivation_data(){
		$this->is_deactive = true;
		$this->build_send( false, true );
	}

}