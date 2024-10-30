/**
 * WooCommerce Checkout Optimization by Cartimize
 * Copyright (c) 2019 Revmakx LLC
 * revmakx.com
 */

jQuery( function( $ ) {

	$('#cartimize_service_login_btn').on('click', function(){
		cartimize_service_login( "#cartimize_service_login_btn" );
	});

	$('#cartimize_service_create_btn').on('click', function(){
		cartimize_service_login( "#cartimize_service_create_btn" );
	});

	jQuery('.acc-login-form').keypress( function(e){
		if(e.which == 13){
			if ( $(this).hasClass('create-form') ) {
				$('#cartimize_service_create_btn').click();
			}else if( $(this).hasClass('login-form') ) {
				$('#cartimize_service_login_btn').click();
			}
			
		}
	});

	async function cartimize_service_login( this_element ){
		var result_element = '#cartimize_service_login_btn_result';
	
		var email = jQuery('#cartimize_service_email').val();
		var password = jQuery('#cartimize_service_password').val();

		if ( !cartimize_email_validation( email ) ) {
			cartimize_show_result(result_element, 'error', "Invalid Email.");
			return false;
		}

		if ( this_element != "#cartimize_service_create_btn" && !password ) {
			cartimize_show_result(result_element, 'error', "Invalid Password.");
			return false;
		}
		
		var request = {};
		var response = {};

		request.url = cartimize_checkopt_ajax.ajax_url;
		request.method = 'POST';

		if ( this_element == "#cartimize_service_create_btn" ) {
			request.data = {
				action: 'cartimize_process_ajax_request',
				cartimize_action: 'service_create_account',
				email: email,
			};
		}else{
			request.data = {
				action: 'cartimize_process_ajax_request',
				cartimize_action: 'service_login_account',
				email: email,
				password: password
			};
		}
	
		
		jQuery(result_element).html('');

		jQuery(this_element).addClass('loading');
		jQuery(this_element).data('default_value', jQuery(this_element).val() );
		if ( this_element == "#cartimize_service_create_btn" ) {
			jQuery(this_element).val('Creating your account...');
		}else{
			jQuery(this_element).val('Logging you in...');
		}
		await cartimize_do_http_call(request, response);
		jQuery(this_element).val( jQuery(this_element).data('default_value') );
		jQuery(this_element).removeClass('loading');
	
		if(response.http_is_success){
			response_data = cartimize_clean_and_parse_json_response(response.http_data);
			if(response_data === 'JSON_PARSE_ERROR'){
				cartimize_show_result(result_element, 'error', 'Invalid response received.');
				return false;
			}
			if(response_data.hasOwnProperty('status')){
				if(response_data.status === 'success'){
	
					// var result_html = '<div class="success-box">Sucess! Redirecting...</div>';
					// jQuery(result_element).html(result_html);
	
					//redirect to main page
					if(typeof cartimize_redirect_after_login != 'undefined'){
						jQuery(this_element).val('Success! Redirecting...');
						setTimeout(function() { location.assign(cartimize_redirect_after_login); }, 10);
					}
					return true;
				}
				if(response_data.status === 'error'){
					cartimize_show_result(result_element, 'error', response_data.error_msg, response_data.error_code);
					//alert('Error:' + response_data.error_msg);
					if ( response_data.error_code  == 'service__existing_user_login' || response_data.error_code  == 'service__existing_user_email') {
						$( '#login-account' ).click();
					}
					return true;
				}
			}
		}
		else{
			cartimize_show_result(result_element, 'error', 'HTTP call failed.');
			//alert('HTTP call failed.');
		}
	}
	////service auth - related code -- end here

	$( document.body ).on( 'change', '.cartimize-radio-li :radio', ( e ) => {
        let radio_button     = $( '#login-account' );

        if ( !$("#cartimize_service_login_btn_result .error-box").hasClass('service__existing_user_login') && !$("#cartimize_service_login_btn_result .error-box").hasClass('service__existing_user_email') ) {
        	$("#cartimize_service_login_btn_result .error-box").remove();
        }
        $("#cartimize_service_email").focus();
        if ( radio_button.is( ':checked')) {
        	$(".login-content").show();
        	$(".create-content").hide();
        	$(".acc-login-form").removeClass('create-form');
        	$(".acc-login-form").addClass('login-form');
        	$("#cartimize_skip_signup").hide();
        }else{
        	$(".login-content").hide();
        	$(".create-content").show();
        	$(".acc-login-form").removeClass('login-form');
        	$(".acc-login-form").addClass('create-form');
        	$("#cartimize_skip_signup").show();
        }
    } );

    jQuery( document ).ready( function( $ ) {

		// Uploading files
		var file_frame;
		var wp_media_post_id = wp.media.model.settings.post.id; // Store the old id
		var set_to_post_id = cartimize_checkopt_ajax.logo_id; // Set this

		jQuery('#cartimize_upload_logo').on('click', function( event ){

			event.preventDefault();

			// If the media frame already exists, reopen it.
			if ( file_frame ) {
				// Set the post ID to what we want
				file_frame.uploader.uploader.param( 'post_id', set_to_post_id );
				// Open frame
				file_frame.open();
				return;
			} else {
				// Set the wp.media post id so the uploader grabs the ID we want when initialised
				wp.media.model.settings.post.id = set_to_post_id;
			}

			// Create the media frame.
			file_frame = wp.media.frames.file_frame = wp.media({
				title: 'Select a image to upload',
				button: {
					text: 'Use this image',
				},
				multiple: false	// Set to true to allow multiple files to be selected
			});

			// When an image is selected, run a callback.
			file_frame.on( 'select', function() {
				// We set multiple to false so only get one image from the uploader
				attachment = file_frame.state().get('selection').first().toJSON();

				// Do something with attachment.id and/or attachment.url here
				$( '#cartimize_logo_preview' ).attr( 'src', attachment.url ).css( {'width': 'auto', 'display': 'block'} );
				$( '#cartimize_logo_id' ).val( attachment.id );

				// Restore the main post ID
				wp.media.model.settings.post.id = wp_media_post_id;
			});

				// Finally, open the modal
				file_frame.open();
		});

		// Restore the main ID when the add media button is pressed
		jQuery( 'a.add_media' ).on( 'click', function() {
			wp.media.model.settings.post.id = wp_media_post_id;
		});
	});

	const additional_css_el = jQuery( '#_cartimize__settingadditional_cssstring' );

	if ( additional_css_el.length && !$( '#cartimize_additional_css_editor_wrapper' ).hasClass('hide') ) {
	    const editorSettings = wp.codeEditor.defaultSettings ? _.clone( wp.codeEditor.defaultSettings ) : {};
	    editorSettings.codemirror = _.extend(
	        {},
	        editorSettings.codemirror,
	        {
	            indentUnit: 2,
	            tabSize: 2,
	        },
	    );
	    wp.codeEditor.initialize( additional_css_el, editorSettings );
	    $( '#cartimize_additional_css_editor_wrapper' ).addClass('initialized');
	}

	$( "#cartimize-keep-me-updated" ).on('click', function(event) {
		if (!$(this).hasClass('loading')) {
			cartimize_email_subscription();
		}
	});

	async function cartimize_email_subscription( ){
		var request = {};
		var response = {};
		var this_element = "#cartimize-keep-me-updated";
		var error_element = "#cartimize-keep-me-updated-error";
		var success_element = "#cartimize-keep-me-updated-success";
		var email = $( '#cartimize-email-subscription' ).val();
		var promotional = false;
		var updates = false;

		if ( $( '.cartimize-product-update' ).is( ':checked' ) ) {
			updates = true;
		}

		if ( $( '.cartimize-promotional-email' ).is( ':checked' ) ) {
			promotional = true;
		}
		request.url = cartimize_checkopt_ajax.ajax_url;
		request.method = 'POST';
		request.data = {
				action: 'cartimize_process_ajax_request',
				cartimize_action: 'keep_me_updated',
				subscription_email: email,
				is_promotional: promotional,
				is_product_update: updates
			};

		$(this_element).text('Adding you...');
		$(this_element).addClass('loading');
		$(error_element).hide();

		await cartimize_do_http_call(request, response);

		
		if(response.http_is_success){
			response_data = cartimize_clean_and_parse_json_response(response.http_data);
			if(response_data === 'JSON_PARSE_ERROR'){
				$(this_element).text('Join the mailing list');
				$(this_element).removeClass('loading');
				$(error_element).show();
				cartimize_show_result(error_element, 'error', 'Invalid response received.');
				return false;
			}
			if(response_data.hasOwnProperty('status')){
				if(response_data.status === 'success'){
					$( this_element ).hide();
					$( success_element ).show();
					return true;
				}
				if(response_data.status === 'error'){
					$(this_element).text('Join the mailing list');
					$(this_element).removeClass('loading');
					$(error_element).show();
					cartimize_show_result(error_element, 'error', response_data.error_msg, response_data.error_code);
					return true;
				}
			}
		}
		else{
			$(this_element).text('Join the mailing list');
			$(this_element).removeClass('loading');
			$(error_element).show();
			cartimize_show_result(error_element, 'error', 'HTTP call failed.');
		}

	}

	$( "input[name^='_cartimize__setting[additional_css_enable][string]']" ).on('click', function(event) {
		$( '#cartimize_additional_css_editor_wrapper' ).toggleClass('hide');
		var initialized = $( '#cartimize_additional_css_editor_wrapper' ).hasClass('initialized');
		if( initialized === false){

			const additional_css_el = jQuery( '#_cartimize__settingadditional_cssstring' );

			if ( additional_css_el.length ) {
			    const editorSettings = wp.codeEditor.defaultSettings ? _.clone( wp.codeEditor.defaultSettings ) : {};
			    editorSettings.codemirror = _.extend(
			        {},
			        editorSettings.codemirror,
			        {
			            indentUnit: 2,
			            tabSize: 2,
			        },
			    );
			    wp.codeEditor.initialize( additional_css_el, editorSettings );
			    $( '#cartimize_additional_css_editor_wrapper' ).addClass('initialized');
			}
		}
	});

	$( "input[name^='_cartimize__setting[logo_type][string]']" ).on('click', function(event) {
		if ( $(this).val() == 'logo' ) {
			$( '.cartimize-log-wrapper' ).removeClass('hide');
			$( '#cartimize-store-text' ).addClass( 'hide' );
		}else{
			$( '#cartimize-store-text' ).removeClass( 'hide' );
			$( '.cartimize-log-wrapper' ).addClass('hide');
		}
	});

	$( ".cartimize-see-data" ).on('click', function(event) {
		$(this).hide();
		$( '.cartimize-collected-data' ).show();
	});

	$( ".cartimize-report-bug" ).on('click', function(event) {
		$('#cartimize-report-bug-modal').dialog({
	           autoOpen: true, //FALSE if you open the dialog with, for example, a button click
	           title: 'Report a Bug',
	           dialogClass: 'wp-dialog cartimize-report-dialog',
	           modal: true,
	           draggable: false,
	           width: '600',
	      });
		var error_element = '#cartimize-report-error';
		var success_element = '#cartimize-report-success';
		var this_element = '#cartimize-send-report';
		$(this_element).text('Send the report');
		$(this_element).removeAttr('disabled');
		$(this_element).attr('disabled', 'disabled');
		$(this_element).show();
		$(error_element).hide();
		$(success_element).hide();
		$( ".cartimize-see-data" ).hide();
		$( ".cartimize-see-data-loader" ).show();
		$( '.cartimize-collected-data' ).hide();
		$('.ui-dialog-titlebar-close').attr('disabled', 'disabled');
		cartimize_collect_report_issue_data();
	});

	async function cartimize_collect_report_issue_data(){
		var request = {};
		var response = {};
		var error_element = '';
		var this_element = '.cartimize-collected-data';


		request.url = cartimize_checkopt_ajax.ajax_url;
		request.method = 'POST';
		request.data = {
				action: 'cartimize_process_ajax_request',
				cartimize_action: 'collect_report_issue',
			};

		await cartimize_do_http_call(request, response);

		if(response.http_is_success){
			response_data = cartimize_clean_and_parse_json_response(response.http_data);
			if(response_data === 'JSON_PARSE_ERROR'){
				cartimize_show_result(error_element, 'error', 'Invalid response received.');
				return false;
			}
			if(response_data.hasOwnProperty('status')){
				if(response_data.status === 'success'){
					$(this_element).html( response_data.result );
					$( ".cartimize-see-data" ).show();
					$( ".cartimize-see-data-loader" ).hide();
					$( '.cartimize-collected-data' ).hide();
					$( "#cartimize-send-report" ).removeAttr('disabled');
					$('.ui-dialog-titlebar-close').removeAttr('disabled');
					return true;
				}
				if(response_data.status === 'error'){
					cartimize_show_result(error_element, 'error', response_data.error_msg, response_data.error_code);
					return true;
				}
			}
		}
		else{
			cartimize_show_result(error_element, 'error', 'HTTP call failed.');
		}

	}

	$( '#cartimize-send-report' ).on('click', function(event) {
		cartimize_send_report();
	});

	async function cartimize_send_report(){
		var request = {};
		var response = {};
		var error_element = '#cartimize-report-error';
		var success_element = '#cartimize-report-success';
		var this_element = '#cartimize-send-report';
		var close_button = '.ui-dialog-titlebar-close';

		var bug_message = $( '#cartimize-bug-message' ).val();
		var bug_email = $( '#cartimize-bug-email' ).val();


		if ( !bug_message ) {
			$( error_element ).text( 'Please explain the bug.' );
			$( error_element ).show();
			$( '#cartimize-bug-message' ).focus();
			return false
		}

		if ( !bug_email ) {
			$( error_element ).text( 'Please fill the email.' );
			$( error_element ).show();
			$( '#cartimize-bug-email' ).focus();
			return false
		}
		request.url = cartimize_checkopt_ajax.ajax_url;
		request.method = 'POST';
		request.data = {
				action: 'cartimize_process_ajax_request',
				cartimize_action: 'send_report',
				email: bug_email,
				message: bug_message,
			};

		$(this_element).text('Sending the report...');
		$(this_element).attr('disabled', 'disabled');
		$(close_button).attr('disabled', 'disabled');
		$(error_element).hide();

		await cartimize_do_http_call(request, response);

		
		if(response.http_is_success){
			response_data = cartimize_clean_and_parse_json_response(response.http_data);
			if(response_data === 'JSON_PARSE_ERROR'){
				$(this_element).text('Send the report');
				$(this_element).removeAttr('disabled');
				$(close_button).removeAttr('disabled');
				$(error_element).show();
				cartimize_show_result(error_element, 'error', 'Invalid response received.');
				return false;
			}
			if(response_data.hasOwnProperty('status')){
				if(response_data.status === 'success'){
					$( this_element ).hide();
					$( success_element ).show();
					$(close_button).removeAttr('disabled');
					setTimeout(function(){ $('#cartimize-report-bug-modal').dialog('close') }, 1000);
					return true;
				}
				if(response_data.status === 'error'){
					$(this_element).text('Send the report');
					$(this_element).removeAttr('disabled');
					$(error_element).show();
					$(close_button).removeAttr('disabled');
					cartimize_show_result(error_element, 'error', response_data.error_msg, response_data.error_code);
					return true;
				}
			}
		}
		else{
			$(this_element).text('Send the report');
			$(this_element).removeAttr('disabled');
			$(error_element).show();
			$(close_button).removeAttr('disabled');
			cartimize_show_result(error_element, 'error', 'HTTP call failed.');
		}
	}

	$( window ).on( 'load', () => {
		var qParams = cartimize_geturl_vars();

		if ( typeof qParams.params != 'undefined' && qParams.params == "report_bug" ) {
			$( ".cartimize-report-bug" ).click();
		}
	})


});