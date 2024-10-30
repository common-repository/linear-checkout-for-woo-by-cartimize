<?php 
/**
 * Linear Checkout for WooCommerce by Cartimize
 * Copyright (c) 2020 Revmakx LLC
 * revmakx.com
 */

namespace Cartimize\Admin;

class SettingsPage{
	public $plugin_instance;

	public function __construct( $plugin ) {
		$this->plugin_instance = $plugin;
		$this->init();
	}
	public function init() {
		add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_settings_tab'), 50 );
		add_action( 'woocommerce_settings_tabs_cartimize_checkopt_settings', array( $this, 'settings_tab') );
		$site_token = $this->plugin_instance->get_license_controller()->get_setting( 'site_token' );
		if ( $site_token == false ) {
			add_action('admin_menu', array( $this, 'cartimize_login_register'));
		}
	}

	public function cartimize_login_register(){
		add_submenu_page( null, 'Cartimize', 'Cartimize', 'manage_options', 'cartimize-login', array( $this, 'signin_signup'));
	}

	public function add_settings_tab( $settings_tabs ) {
		$settings_tabs['cartimize_checkopt_settings'] = __( 'Cartimize', 'linear-checkout-for-woo-by-cartimize' );
		return $settings_tabs;
	}

	public function settings_tab(){
		do_action( 'cartimize_before_setting_page' );
		if (isset($_GET['page']) && isset($_GET['tab']) && isset($_GET['skip_signup']) && $_GET['page'] === 'wc-settings' && $_GET['tab'] === 'cartimize_checkopt_settings' && $_GET['skip_signup'] === '1') {
			$this->plugin_instance->get_settings_controller()->add_setting( 'skip_signup', 1 );
			$this->plugin_instance->get_settings_controller()->add_setting( 'lcw_enable', 1 );
			$this->plugin_instance->init_settings_controller();
			$this->plugin_instance->verify_setting();
		}
		if( isset($_GET['page']) && isset($_GET['tab']) && isset($_POST['join_beta']) && $_GET['page'] === 'wc-settings' && $_GET['tab'] === 'cartimize_checkopt_settings' && $_POST['join_beta'] === '1'){
			$this->plugin_instance->get_settings_controller()->add_setting( 'is_beta_program', 1 );
			$this->plugin_instance->get_settings_controller()->add_setting( 'lcw_enable', 1 );

		}elseif (isset($_GET['page']) && isset($_GET['tab']) && isset($_POST['join_beta']) && $_GET['page'] === 'wc-settings' && $_GET['tab'] === 'cartimize_checkopt_settings' && $_POST['join_beta'] === '0') {
			$this->plugin_instance->get_settings_controller()->add_setting( 'is_beta_program', 0 );
			$this->plugin_instance->get_settings_controller()->add_setting( 'lcw_enable', 1 );
			$this->plugin_instance->get_settings_controller()->add_setting( 'demo_product_view', 0 );
		}elseif( isset($_GET['page']) && isset($_GET['tab']) && isset($_GET['logout']) && $_GET['page'] === 'wc-settings' && $_GET['tab'] === 'cartimize_checkopt_settings' && $_GET['logout'] === '1'){
			delete_option( '_cartimize__license' );
			wp_redirect(CARTIMIZE_LOGIN_PAGE_URL);
			$this->plugin_instance->get_settings_controller()->add_setting( 'demo_product_view', 0 );
		}

		$site_token = $this->plugin_instance->get_license_controller()->get_setting( 'site_token' );
		$skip_signup = $this->plugin_instance->get_settings_controller()->get_setting( 'skip_signup' );
		$is_beta_program = $this->plugin_instance->get_settings_controller()->get_setting( 'is_beta_program' );
		if ( $site_token == false &&  $skip_signup == false) {
			wp_redirect(CARTIMIZE_LOGIN_PAGE_URL);
		}elseif ( $is_beta_program === false &&  $skip_signup == false) {
			$GLOBALS['hide_save_button'] = true;
			echo '<div class="cartimize_checkopt_settings_page">';
			include(CARTIMIZE_PATH.'/templates/Admin/cartimize-beta-welcome-page.php');
			echo "</div>";
		}else{
			?>
			<div class="cartimize_checkopt_settings_page">
				<?php do_action( 'cartimize_inside_setting_element' ); ?>
				<?php $this->plugin_instance->get_settings_controller()->the_nonce(); ?>
				<?php $this->display_on_boarding(); ?>
				<?php $this->build_table(); ?>
				<?php $this->report_bug_modal()  ?>
				<div data-upvoty></div>
			</div>

			<?php
		}
	}

	public function signin_signup(){
		echo '<div class="cartimize_checkopt_settings_page">';
		include(CARTIMIZE_PATH.'/templates/Admin/cartimize-service-signin-signup.php');
		echo "</div>";
	}

	public function display_on_boarding(){
		if ( $this->plugin_instance->get_settings_controller()->get_setting( 'demo_product_view' ) == '1' ) {
			return;
		}
		$top_sellers = array();
		if ( !class_exists( '\WC_Admin_Report' ) ) {

			if ( file_exists( dirname( WC_PLUGIN_FILE ) . '/includes/admin/reports/class-wc-admin-report.php' ) ) {
				include_once dirname( WC_PLUGIN_FILE ) . '/includes/admin/reports/class-wc-admin-report.php';
			}else{
				$top_sellers = false;
			}
		}
		if ( is_array( $top_sellers ) ) {
			# code...
			$wc_report = new \WC_Admin_Report();
			$start_date = strtotime( date('Y-m-d') . ' -10 year' );
			$end_date = strtotime( 'midnight', current_time( 'timestamp' ) );
			$wc_report->start_date = $start_date;
			$wc_report->end_date = $end_date;
			$top_sellers = $wc_report->get_order_report_data(
				array(
					'data'         => array(
						'_product_id' => array(
							'type'            => 'order_item_meta',
							'order_item_type' => 'line_item',
							'function'        => '',
							'name'            => 'product_id',
						),
						'_qty'        => array(
							'type'            => 'order_item_meta',
							'order_item_type' => 'line_item',
							'function'        => 'SUM',
							'name'            => 'order_item_qty',
						),
					),
					'order_by'     => 'order_item_qty DESC',
					'group_by'     => 'product_id',
					'limit'        => 1,
					'query_type'   => 'get_results',
					'filter_range' => true,
					'order_status' => array( 'completed', 'processing', 'on-hold', 'refunded' ),
				)
			);
		}

		if ( empty ( $top_sellers ) ) {
			$args = array(
				'orderby' => 'date',
				'order' => 'DESC',
			    'limit' => 1,
			    'return' => 'id',
			    'status' => 'publish',
			    'type' => 'simple',
			);
			$products = wc_get_products( $args );
			
			if ( empty( $products ) ) {
				return false;
			}

			$product_id = $products[0]->get_id();

		}else{
			$product_id = $top_sellers[0]->product_id;
		}

		$product = wc_get_product($product_id);

		if( is_object($product) && $product->is_type( 'variable' ) ){
			$variations = $product->get_available_variations();
			$variations_id = wp_list_pluck( $variations, 'variation_id' );
			if (empty($variations_id[0])) {
				return false;
			}
			$product_url = trim( site_url(), '/' ).'/'.'?add-to-cart='.$product_id.'&variation_id='.$variations_id[0].'&cartimize_redirect=1';
		} else{
			$product_url = trim( site_url(), '/' ).'/'.'?add-to-cart='.$product_id.'&cartimize_redirect=1';
		}


		?>
			<div style="background-color: #D3DFEF; padding: 10px 20px; border: 1px dashed #10305F; color: #10305F; border-radius: 10px; margin-bottom: 20px; margin-top: 20px;"><?php esc_html_e( 'The linear checkout is now active for your store.', 'linear-checkout-for-woo-by-cartimize' ) ?>
				<a style="background-color: #10305F; color: #fff; padding: 10px 20px; display: inline-block; border-radius: 5px; margin-left: 20px; text-transform: uppercase; font-weight: bold; font-size: 12px; cursor: pointer;" href="<?php echo esc_url($product_url); ?>" target="_blank"><?php esc_html_e( 'View the checkout page', 'linear-checkout-for-woo-by-cartimize' ) ?> &nearr;</a>
			</div>

		<?php
	}

	public function build_table(){
		$settings = apply_filters( 'cartimize_setting_array',
						array(
							'lcw_enable' => array( $this, 'lcw_enable' ),
							'logo_manager' => array( $this, 'logo_manager' ),
							'shipping_method_order' => array( $this, 'shipping_method_order' ),
							'hide_coupons' => array( $this, 'hide_coupons' ),
							'additional_css_enable' => array( $this, 'additional_css_enable' ),
						)
					 );
		$settings['is_beta_program'] = array( $this, 'is_beta_program' );
		$settings = apply_filters( 'cartimize_setting_final_array', $settings);
		$this->setting_header();
		$this->table_header();
		foreach ($settings as $key => $setting) {
			call_user_func($setting);
		}
		$this->table_footer();
		$this->become_insider();
	}

	function logo_manager(){

		$logo_content = $this->plugin_instance->get_settings_controller()->get_setting('logo_id');
		$logo_url =  wp_get_attachment_url( $logo_content );

		if ( empty( $this->plugin_instance->get_settings_controller()->get_setting( "store_text" ) ) ) {
			$this->plugin_instance->get_settings_controller()->update_setting( "store_text", get_bloginfo( 'name' ) ) ;
		}
		if ( empty( $this->plugin_instance->get_settings_controller()->get_setting( "logo_type" ) ) ) {
			$this->plugin_instance->get_settings_controller()->update_setting( "logo_type", 'logo' ) ;
		}

		if ( empty( $logo_url ) ) {
			$this->plugin_instance->get_settings_controller()->update_setting( "logo_type", 'text' ) ;
		}


		?>
			<tr>
				<th><label class=''> </label><?php esc_html_e( 'Store logo', 'linear-checkout-for-woo-by-cartimize' ) ?><div style="font-size: 13px; font-weight: normal;"><?php esc_html_e( 'Will be displayed in the checkout header', 'linear-checkout-for-woo-by-cartimize' ) ?></div></th>
				<td><div>
				<div style="margin-bottom: 5px;">
					<label>
						<input name="<?php echo esc_attr($this->plugin_instance->get_settings_controller()->get_field_name('logo_type'));?>" type="radio" value="logo" <?php if ( $this->plugin_instance->get_settings_controller()->get_setting('logo_type') == "logo" ) echo "checked"; ?>  style="margin-right: 8px; display: inline-block;"><?php esc_html_e( 'Use image', 'linear-checkout-for-woo-by-cartimize' ) ?>
					</label>
				</div>
				<div class="disable-control cartimize-log-wrapper <?php if ( $this->plugin_instance->get_settings_controller()->get_setting('logo_type') != "logo" ) echo "hide"; ?> ">
					<div style="display:flex; align-items:center;margin-left: 25px;"><input style="float: left; margin-right: 10px;" id="cartimize_upload_logo" type="button" class="button" value="<?php esc_html_e( 'Upload logo', 'linear-checkout-for-woo-by-cartimize' ); ?>" />
					<input type='hidden' name='_cartimize__setting[logo_id][string]' id='cartimize_logo_id' value='<?php echo esc_attr($this->plugin_instance->get_settings_controller()->get_setting( "logo_id" )); ?>'>
					<div class='image-preview-wrapper'>
					<?php if( $this->plugin_instance->get_settings_controller()->get_setting( "logo_id" ) ): ?>
						<img id='cartimize_logo_preview' src='<?php echo esc_url(wp_get_attachment_url( $this->plugin_instance->get_settings_controller()->get_setting( "logo_id" ))); ?>' style='max-height: 50px; width: auto;'>
					<?php else: ?>
						<img id='cartimize_logo_preview' src='' style='max-height: 50px; width: auto;display: none'>
					<?php endif; ?>
					</div>
					</div>
					<p class="description i1" style="padding: 5px 0; font-size: 13px; font-style: italic;clear: both"><?php esc_html_e( 'The logo will be proportionately restricted to a maximum height of 50px.', 'linear-checkout-for-woo-by-cartimize' ) ?></p>
					</div>
					<div style="margin-top: 15px;">
						<label style="margin-bottom: 5px; display: inline-block;">
							<input name="<?php echo esc_attr($this->plugin_instance->get_settings_controller()->get_field_name('logo_type'));?>" type="radio" value="text" <?php if ( $this->plugin_instance->get_settings_controller()->get_setting('logo_type') == "text" ) echo "checked"; ?>  style="margin-right: 8px; display: inline-block;"><?php esc_html_e( 'Use text', 'linear-checkout-for-woo-by-cartimize' ) ?>
						</label><br>
						<input style="margin-left: 25px;" <?php if ( $this->plugin_instance->get_settings_controller()->get_setting('logo_type') != "text" ){ ?> class="hide" <?php } ?> type="text" id="cartimize-store-text" name="<?php echo esc_attr($this->plugin_instance->get_settings_controller()->get_field_name('store_text')) ?>" value='<?php echo esc_attr($this->plugin_instance->get_settings_controller()->get_setting( "store_text" )) ?>'></div>
					</div>
				</td>
			</tr>
		<?php
	}

	

	public function setting_header(){
			
		?>
			<div style="text-align: right; margin-bottom: 20px;">
			<?php do_action( 'cartimize_inside_setting_header' ); ?>
			<span style="margin: 0 10px;">&bull;</span>
				<a href="https://docs.cartimize.com/" target="_blank">Help docs &nearr;</a>
				<span style="margin: 0 10px;">&bull;</span>
				<?php 
				if ($this->plugin_instance->get_license_controller()->get_setting('email')) {
					echo esc_html__($this->plugin_instance->get_license_controller()->get_setting('email')); ?>
					<a style="margin-left: 5px;" href="<?php echo esc_url(admin_url( 'admin.php?page=wc-settings&tab=cartimize_checkopt_settings&logout=1' )); ?>"><?php esc_html_e( 'Logout', 'linear-checkout-for-woo-by-cartimize' ) ?></a>
				<?php }else{ ?>
					<a style="margin-left: 5px;" href="<?php echo esc_url(CARTIMIZE_LOGIN_PAGE_URL_MUST_SIGNUP); ?>"><?php esc_html_e( 'Login / Register', 'linear-checkout-for-woo-by-cartimize' ) ?></a>
				<?php } ?>
			</div>
		<?php
	}

	public function table_header(){
		?>
			<div style="display:flex;">
				<div style="flex: 1 1 auto; padding-right: 100px;">
					<table class="form-table">
						<tbody>
		<?php
	}

	public function table_footer(){
		?>
					</tbody>
					
				</table>
			</div>
		<?php
	}

	public function become_insider(){
		?>
			<div style="margin-left:auto; flex: 0 0 250px;">
				<?php if ( !$this->plugin_instance->get_license_controller()->get_setting( 'is_insider' ) ): ?>
					<div class="email-optin-cont" style="width: 300px; padding: 20px; background-color: #fff; margin-bottom: 20px" >
						<h3 style="margin: 0;">Be in the know</h3>
						<p>Join our mailing list to receive occasional emails and be in the know.</p>
						<strong style="font-size: 11px; margin-bottom: 3px; display: block;">ENTER YOUR EMAIL ADDRESS</strong>
						<input id="cartimize-email-subscription" type="email" id="cartimize-" value="<?php echo esc_attr($this->plugin_instance->get_license_controller()->get_setting('email')) ?>" style="width: 100%; margin-bottom:5px; background-color: #f7f7f7;
    border: 1px solid #ddd;">
						<label style=" display:inline-block; padding:5px 5px 5px 22px;"><input type="checkbox" style="margin-top: 1px;" class="cartimize-product-update" checked name="list-selection"> Product updates</label><br>
						<label style="display:inline-block; padding:5px 5px 5px 22px; margin-bottom: 10px;"><input type="checkbox" style="margin-top: 1px;" class="cartimize-promotional-email" checked name="list-selection"> Promotional emails</label>
						<a id="cartimize-keep-me-updated" style="background-color: rgb(0 62 153 / 5%); color:#003e99; border:1px solid #003e99; box-shadow: 0 1px 0 rgba(0,0,0,0.1);border-radius: 5px; padding: 10px; text-align:center; cursor: pointer;display: block;">Join the mailing list</a>
						<div id="cartimize-keep-me-updated-success" style="color:#46b450;border: 1px dashed #46b450; padding: 10px; background: rgb(70 180 80 / 0.2); display: none;">You have subscribed successfully! Welcome!</div>
						<div id="cartimize-keep-me-updated-error" style="margin-top: 25px;background-color: #f2dede;border: 1px solid #ebcccc;color: #a94442;padding: 4px 7px;border-radius: 5px; display: none;"> </div>
					</div>
				<?php endif ?>
				<div>
					<?php do_action( 'cartimize_setting_page' ) ?>
				</div>
			</div>
			
		</div>

		<?php
	}
	public function lcw_enable(){
		?>
			<tr>
				<th><label class=''> </label><?php esc_html_e( 'Enable LCW', 'linear-checkout-for-woo-by-cartimize' ) ?> </th>
				<td>
					<input type="hidden" name="_cartimize__setting[lcw_enable][string]" value="0">
					<label> <input type='checkbox' value='1' name='_cartimize__setting[lcw_enable][string]' <?php if ( $this->plugin_instance->get_settings_controller()->get_setting('lcw_enable') == "1" ) echo "checked"; ?>  /> <?php esc_html_e( 'Enable the Linear Checkout', 'linear-checkout-for-woo-by-cartimize' ) ?></label>
				</td>
			</tr>

		<?php
	}

	public function hide_coupons(){
		?>
			<tr>
				<th><label class=''> </label><?php esc_html_e( 'Hide coupon field?', 'linear-checkout-for-woo-by-cartimize' ) ?></th>
				<td>
					<input type="hidden" name="_cartimize__setting[hide_coupons][string]" value="0">
					<label> <input type='checkbox' value='1' name='_cartimize__setting[hide_coupons][string]' <?php if ( $this->plugin_instance->get_settings_controller()->get_setting('hide_coupons') == "1" ) echo "checked"; ?>  /> <?php esc_html_e( 'Hide the coupon field in the checkout page', 'linear-checkout-for-woo-by-cartimize' ) ?> </label>
				</td>
			</tr>

		<?php
	}

	public function additional_css_enable(){
		?>
		<tr>
			<th><label class=''> </label><?php esc_html_e( 'Custom CSS', 'linear-checkout-for-woo-by-cartimize' ) ?></th>
			<td>
				<div>
				<input type="hidden" name="_cartimize__setting[additional_css_enable][string]" value="0">
				<label> <input type='checkbox' value='1' name='_cartimize__setting[additional_css_enable][string]' <?php if ( $this->plugin_instance->get_settings_controller()->get_setting('additional_css_enable') == "1" ) echo "checked"; ?>  /> <?php esc_html_e( 'Enable custom CSS', 'linear-checkout-for-woo-by-cartimize' ) ?></label>
				<p class="description i1"><?php esc_html_e( 'Add custom CSS to over-write the checkout styles.', 'linear-checkout-for-woo-by-cartimize' ) ?></p>
				<div id="cartimize_additional_css_editor_wrapper" <?php if ( $this->plugin_instance->get_settings_controller()->get_setting('additional_css_enable') != '1' ){ ?>  class="hide"  <?php } ?> >
					<?php wp_editor( stripslashes_deep( $this->plugin_instance->get_settings_controller()->get_setting('additional_css') ), sanitize_title_with_dashes( $this->plugin_instance->get_settings_controller()->get_field_name('additional_css') ), array('textarea_rows' => 6, 'quicktags' => false, 'media_buttons' => false, 'textarea_name' => $this->plugin_instance->get_settings_controller()->get_field_name('additional_css'), 'tinymce' => false ) ); ?>
				</div></div>
			</td>
		</tr>

		<?php
	}

	public function is_beta_program(){
		?>
			<tr>
				<th><label class=''> </label><?php esc_html_e( 'Help us improve the plugin?', 'linear-checkout-for-woo-by-cartimize' ) ?> </th>
				<td>
					<input type="hidden" name="_cartimize__setting[is_beta_program][string]" value="0">
					<label style="padding-left:25px; display: inline-block;"> <input type='checkbox' value='1' name='_cartimize__setting[is_beta_program][string]' <?php if ( $this->plugin_instance->get_settings_controller()->get_setting('is_beta_program') == "1" ) echo "checked"; ?>  /> <?php esc_html_e( 'Share non-sensitive, non-personally identifiable data, solely for the purpose of improving the plugin.', 'linear-checkout-for-woo-by-cartimize' ) ?></label>
				</td>
			</tr>

		<?php
	}

	function report_bug_modal(){
		?>
			<div id="cartimize-report-bug-modal" style="display: none;">
				<div>
				<div  id="cartimize-report-error" style="color:#dc3232;border: 1px dashed #dc3232; padding: 10px; background: rgb(220 50 50 / 0.2); margin-bottom: 10px; display: none;"></div>
					 <label for="cartimize-bug-message" style="margin-bottom: 5px; display: inline-block;">Please explain the bug in detail below <br><div style="font-size:12px;">Screenshot links would be immensely helpful.</div></label>
					 <textarea id="cartimize-bug-message" rows="5" style="width: 100%"></textarea>
					 <label for="cartimize-bug-email" style="margin:10px 0 5px; display: inline-block;">We will send responses to this email:</label>
					<input id="cartimize-bug-email" type="email" id="cartimize-" value="<?php echo esc_attr($this->plugin_instance->get_license_controller()->get_setting('email')) ?>" style="width: 100%; margin-bottom:10px;">
					 <a class="cartimize-see-data" style="margin: 10px 0 5px; display: inline-block; cursor:pointer;color: #003e99; font-size: 12px;">+ See report content</a>
					 <a class="cartimize-see-data-loader" style="margin: 10px 0 5px; display: inline-block;  font-size: 12px;">Collecting data for report..</a>
					 <div class="cartimize-collected-data" style="border: 1px solid #7e8993; border-radius:5px; padding:5px; font-family: Monospace; font-size:12px; background-color:#f7f7f7;max-height: 200px; overflow: auto;    margin-top: 10px; display: none;"></div>
					 <button style="width: 100%;background-color: #003e99; color:#fff; border:0; border-radius: 5px; margin-top:20px; padding:15px 10px;" id="cartimize-send-report">Send bug report</button>
					 <div id="cartimize-report-success" style="margin-top: 20px; color:#46b450;border: 1px dashed #46b450; padding: 10px; background: rgb(70 180 80 / 0.2); display: none;">Your bug has been reported successfully. We'll get back to you asap.</div>
				</div>
			</div>
		<?php
	}

	function shipping_method_order(){
		$shipping_method_order = $this->plugin_instance->get_settings_controller()->get_setting( "shipping_methods_order" );
		if ( empty( $shipping_method_order ) ) {
			$this->plugin_instance->get_settings_controller()->update_setting( "shipping_methods_order", 'recommend' ) ;
		}
		?>
			<tr>
			   <th><label class=""> <?php esc_html_e( 'Shipping methods ordering', 'linear-checkout-for-woo-by-cartimize' )   ?> </label></th>
			   <td>
			      <div>
			        <div style="margin-bottom: 5px;">
			            <label>
			            	<input name="<?php echo esc_attr($this->plugin_instance->get_settings_controller()->get_field_name('shipping_methods_order'));?>" type="radio" value="recommend" <?php if ( $shipping_method_order == "recommend" ) echo "checked"; ?>  style="margin-right: 8px; display: inline-block;"><span style="background-color: #e5e5e5;border: 1px solid #ccc;padding: 1px 3px;border-radius: 3px;font-size: 11px;
			               "><?php esc_html_e( 'RECOMMENDED', 'linear-checkout-for-woo-by-cartimize' ) ?></span> <?php esc_html_e( 'Order by cost: Free or cheapest to most expensive', 'linear-checkout-for-woo-by-cartimize' ) ?> 
			           	</label>
			        </div>
	            	<p class="description i1"><?php esc_html_e( "This will keep the free or cheapest option on top and selected by default and the most expensive option as the last option. This will reduce the perceived cost and any increase in total cost will be the result of your customer's explicit action of choosing a more expensive shipping method.", 'linear-checkout-for-woo-by-cartimize' ) ?></p>
			        <div style="margin-top: 15px;">
			            <label>
			            	<input name="<?php echo esc_attr($this->plugin_instance->get_settings_controller()->get_field_name('shipping_methods_order'));?>" type="radio" value="default" <?php if ( $shipping_method_order == "default" ) echo "checked"; ?> style="margin-right: 8px; display: inline-block;"><?php esc_html_e( 'Use default order', 'linear-checkout-for-woo-by-cartimize' ) ?>
			            </label><br>
			            <p class="description i1">
			            <?php esc_html_e( "This will follow the order you set under WooCommerce › Settings › Shipping › Shipping Zones. Use this only if you have a strong reason not to use the 'Order by cost' option above.", 'linear-checkout-for-woo-by-cartimize' ) ?>
	</p>
			         </div>
			      </div>
			   </td>
			</tr>
		<?php
		}
}