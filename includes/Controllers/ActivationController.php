<?php 
/**
 * Linear Checkout for WooCommerce by Cartimize
 * Copyright (c) 2020 Revmakx LLC
 * revmakx.com
 */

namespace Cartimize\Controllers;

class ActivationController{
	private $plugin_instance;

	public function __construct( $plugin_instance ){
		$this->plugin_instance = $plugin_instance;
		add_action('admin_bar_init', array($this, 'self_deactivation'));
	}

	public function activate(){

		if ( !class_exists('WooCommerce') ) {
			set_transient( '_cartimize_woo_error_on_activation', true, 30 );
			return false;
		}elseif( ($this->check_min_version_requirements(false) ) !== true ){
			set_transient( '_cartimize_min_version_error_on_activation', true, 30 );
			return false;
		}elseif ( $this->plugin_instance->get_license_instance()->is_show_trial_remainder_notice()  ) {
			$this->show_trial_remainder_notice();
		}elseif ( $this->plugin_instance->get_license_instance()->is_show_trial_expiry_notice()  ) {
			$this->show_trial_expiry_notice();
		}

		return true;
	}

	public function check_all_requirements( $force = false ){
		if ( $force ) {
			$this->activate();
		}

		if ( get_transient( '_cartimize_woo_error_on_activation' ) ) {
			$this->woocom_not_active_deactivate_self_notice();
			delete_transient( '_cartimize_woo_error_on_activation' );
			$this->deactivate_self();
			return;
		}

		if ( get_transient( '_cartimize_min_version_error_on_activation' ) ) {
			$this->requirments_not_met_deactivate_self_notice();
			delete_transient( '_cartimize_min_version_error_on_activation' );
			$this->deactivate_self();
			return;
		}

		$this->woocom_not_active_error();
	}

	public function check_min_version_requirements($full=true){

		global $woocommerce;

		$required = array();
		$required['php']['version'] = '7.0';
		$required['php']['name'] = 'PHP';
		$required['mysql']['version'] = '5.5';
		$required['mysql']['name'] = 'MySQl';
		$required['wp']['version'] = '4.7';
		$required['wp']['name'] = 'WP';
		$required['wc']['version'] = '3.5';
		$required['wc']['name'] = 'WooCommerce';


		if($full){
			$mysql_full_version = $GLOBALS['wpdb']->get_var("SELECT VERSION()");
			$mysql_tmp = explode('-', $mysql_full_version);
			$mysql_version = array_shift($mysql_tmp);
		}

		$php_version = PHP_VERSION;
		$php_tmp = explode('-', $php_version);
		$php_version = array_shift($php_tmp);

		if ( is_object( $woocommerce ) ) {
			$wc_version = $woocommerce->version;//make sure WooCommerce is active before using this.
		}else{
			$wc_version = null;
		}


		$installed = array();
		$installed['php']['version'] = $php_version;
		if($full){
			$installed['mysql']['version'] = $mysql_version;
		}
		$installed['wp']['version'] = get_bloginfo( 'version' );
		$installed['wc']['version'] = $wc_version;

		$is_all_ok = true;
		if (version_compare($installed['php']['version'], $required['php']['version'], '<')) {
			//not ok
			$is_all_ok = false;
			$installed['php']['is_met'] = false;
		}
		else{
			$installed['php']['is_met'] = true;
		}
		if ($full){
			if( version_compare($installed['mysql']['version'], $required['mysql']['version'], '<')) {
				//not ok
				$is_all_ok = false;
				$installed['mysql']['is_met'] = false;
			}
			else{
				$installed['mysql']['is_met'] = true;
			}
		}

		if (version_compare($installed['wp']['version'], $required['wp']['version'], '<')) {
			//not ok
			$is_all_ok = false;
			$installed['wp']['is_met'] = false;
		}
		else{
			$installed['wp']['is_met'] = true;
		}

		if (version_compare($installed['wc']['version'], $required['wc']['version'], '<')) {
			//not ok
			$is_all_ok = false;
			$installed['wc']['is_met'] = false;
		}
		else{
			
			$installed['wc']['is_met'] = true;
		}

		if($is_all_ok){
			return true;
		}
		if(!$full){
			unset($required['mysql'], $installed['mysql']);
		}
		return array('required' => $required, 'installed' => $installed);
	}

	public function deactivate_self(){
		$plugin_slug = CARTIMIZE_MAIN_FILE;
		deactivate_plugins( $plugin_slug );
	}

	public function requirments_not_met_deactivate_self_notice() {
		$requirements = $this->check_min_version_requirements(false);
		echo '<div class="notice notice-warning"><p>Linear Checkout for WooCommerce by Cartimize - Minimum requirements not met.'; 
		if( is_array( $requirements ) ){
			$this->show_requirements($requirements, true, false);
		}
		echo '</p></div>';
	}

	public function woocom_not_active_deactivate_self_notice() {
		echo '<div class="error"><p><strong>The \'Linear Checkout for WooCommerce by Cartimize\' plugin has been deactivated. It requires WooCommerce to be active.</strong></p></div>';
	}

	public function woocom_not_active_error() {
		if ( !class_exists('WooCommerce') ) {
			echo '<div class="error"><p><strong>The \'Linear Checkout for WooCommerce by Cartimize\' requires the WooCommerce plugin to be activated.</strong></p></div>';
		}
	}

	public function show_requirements($rr, $show_only_not_met=false, $show_heading=true){
		$show_all = !$show_only_not_met;
		?>
		<table width="300" border="0" cellspacing="5">
			<tbody>
				<?php if( $show_heading ){ ?>
					<tr>
						<td class="" colspan="3"><span class="error-box">Minimum requirements not met!</span><br><br></td>
					</tr>		
				<?php } ?>
				<tr>
					<td></td><td align="left"><strong>Required</strong></td><td align="left"><strong>Current</strong></td>
				</tr>
				<?php
				foreach($rr['required'] as $item => $value){
					if( $show_all || ($show_only_not_met && !$rr['installed'][$item]['is_met']) ){ ?>
						<tr>
							<td><?php echo wp_kses_post($rr['required'][$item]['name'])?></td>
							<td><?php echo wp_kses_post($rr['required'][$item]['version']) ?></td> 
							<td><?php echo wp_kses_post($rr['installed'][$item]['version'])?></td>
						</tr>
						<?php 
						}
				}
				?>
			</tbody>
		</table>
		<?php
	}

	public function show_trial_remainder_notice(){
		$remaining = $this->plugin_instance->get_license_instance()->remaining_trial_days();

		echo '<div class="cartimize-notice notice notice-warning is-dismissible"  data-notice-type="trial_remainder_notice"><p>Linear Checkout for WooCommerce Pro by Cartimize: <span style="color:red;">Your free trial is ending in '.esc_html($remaining).' days.</span><a style="background-color: #003e99;color: #fff;font-size: 12px;padding: 5px 10px;border-radius: 5px;margin-left: 10px;" href="'.esc_url(CARTIMIZE_MY_ACCOUNT_URL).'" target="_blank">Select a Pro Plan</a></p></div>';

	}

	public function show_trial_expiry_notice(){
		echo '<div class="cartimize-notice notice notice-warning is-dismissible"  data-notice-type="trial_expiry_notice"  style="padding-left: 20px;"><h3 style="margin: 15px 0 10px;">Linear Checkout for WooCommerce Pro by Cartimize: <span style="color:red;">Your 14-day free trial has ended...</span></h3><div>You lost access to Pro features you were using -<ul style="
    margin: 5px 0 10px 10px;
    list-style: disc;
    list-style-position: inside;
"><li>Inline cart editing</li><li>A better \'Thank you\' page</li><li>Google address autocomplete</li>
<li>Saved addresses</li>
    <li>Remove \'Powered by\' badge</li></ul> </div><a style="background-color: #003e99;color: #fff;font-size: 12px;padding: 10px 20px;border-radius: 5px;margin-left: 10px 10px 20px 10px;display: inline-block;" href="'.esc_url(CARTIMIZE_MY_ACCOUNT_URL).'" target="_blank">Select a Pro Plan</a> to continue using the Pro features.</div>';
	}

	public function self_deactivation(){
		if ( isset( $_GET['cartimize-deactivate'] ) && $_GET['cartimize-deactivate']=1 ) {
			set_transient( '_cartimize_pro_free_error_on_activation', true, 30 );
			$this->deactivate_self();
		}
	}

	public function check_plugin_installed( $plugin_slug ) {
	    $installed_plugins = get_plugins();
	    return array_key_exists( $plugin_slug, $installed_plugins ) || in_array( $plugin_slug, $installed_plugins, true );
	}

}