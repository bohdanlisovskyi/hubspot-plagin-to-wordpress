<?php
/*
Plugin Name: Contact Form 7 to Hubspot CRM (Create Contact and Deal)
Plugin URI: https://github.com/bohdanlisovskyi/hubspot-plagin-to-wordpress
Description: This plugin enables HubSpot Contacts and Deals integration with Contact Form 7 forms. In order for this plugin to work <a href="http://php.net/manual/en/book.curl.php" target="_blank">cURL for PHP</a> should be enabled.
Author: Startokay (by Bohdan Lisovskyi)
Version: 1.0
Author URI: https://github.com/bohdanlisovskyi

PREFIX: cf7hsfi (Contact Form 7 HubSpot Contact and Deal Integration)

*/

// check to make sure contact form 7 is installed and active
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

if ( is_plugin_active( 'contact-form-7/wp-contact-form-7.php' ) ) {

	function cf7hsfi_root_url( $append = false ) {

		$base_url = plugin_dir_url( __FILE__ );

		return ($append ? $base_url . $append : $base_url);

	}

	function cf7hsfi_root_dir( $append = false ) {

		$base_dir = plugin_dir_path( __FILE__ );

		return ($append ? $base_dir . $append : $base_dir);

	}

	include_once( cf7hsfi_root_dir('inc/constants.php') );

	function cf7hsfi_enqueue( $hook ) {

		if ( !strpos( $hook, 'wpcf7' ) )
			return;

		wp_enqueue_style( 'cf7hsfi-styles',
			cf7hsfi_root_url('assets/css/styles.css'),
			false,
			CF7HSFI_VERSION );

		wp_enqueue_script( 'cf7hsfi-scripts',
			cf7hsfi_root_url('assets/js/scripts.js'),
			array('jquery'),
			CF7HSFI_VERSION );

	}
	add_action( 'admin_enqueue_scripts', 'cf7hsfi_enqueue' );

	function cf7hsfi_admin_panel ( $panels ) {

		$new_page = array(
			'hubspot-forms-integration-addon' => array(
				'title' => __( 'HubSpot Contact/Deal Integration', 'contact-form-7' ),
				'callback' => 'cf7hsfi_admin_panel_content'
			)
		);

		$panels = array_merge($panels, $new_page);

		return $panels;

	}
	add_filter( 'wpcf7_editor_panels', 'cf7hsfi_admin_panel' );

	function cf7hsfi_admin_panel_content( $cf7 ) {

		$post_id = sanitize_text_field($_GET['post']);

		$api_key = get_option($post_id . "_cf7hsfi_api_key");
		$deal_name = get_option($post_id . "_cf7hsfi_deal_name");
		$deal_price = get_option($post_id . "_cf7hsfi_deal_price");
		$form_fields_str = get_option($post_id . "_cf7hsfi_form_fields");
		$form_fields = $form_fields_str ? unserialize($form_fields_str) : false;
		$debug_log = get_post_meta($post_id, "_cf7hsfi_debug_log", true);

		$template = cf7hsfi_get_view_template('form-fields.tpl.php');

		if($form_fields) {

			$form_fields_html = '';
			$count = 1;

			foreach ($form_fields as $key => $value) {

				$search_replace = array(
					'{first_field}' => ' first_field',
					'{field_name}' => $key,
					'{field_value}' => $value,
					'{add_button}' => '<a href="#" class="button add_field">Add Another Field</a>',
					'{remove_button}' => '<a href="#" class="button remove_field">Remove Field</a>',
				);

				$search = array_keys($search_replace);
				$replace = array_values($search_replace);

				if($count >  1) $replace[0] = $replace[3] = '';
				if($count == 1) $replace[4] = '';

				$form_fields_html .= str_replace($search, $replace, $template);

				$count++;

			}

		} else {

			$search_replace = array(
				'{first_field}' => ' first_field',
				'{field_name}' => '',
				'{field_value}' => '',
				'{add_button}' => '<a href="#" class="button add_field">Add Another Field</a>',
				'{remove_button}' => '',
			);

			$search = array_keys($search_replace);
			$replace = array_values($search_replace);

			$form_fields_html = str_replace($search, $replace, $template);

		}

		$debug_log = unserialize($debug_log);
		$debug_log_str = is_array($debug_log) ? print_r($debug_log, true) : $debug_log;

		$search_replace = array(
			'{api_key}' => $api_key,
			'{deal_name}' => $deal_name,
			'{deal_price}' => $deal_price,
			'{form_fields_html}' => $form_fields_html,
			'{debug_log}' => $debug_log_str,
		);

		$search = array_keys($search_replace);
		$replace = array_values($search_replace);

		$template = cf7hsfi_get_view_template('ui-tabs-panel.tpl.php');

		$admin_table_output = str_replace($search, $replace, $template);

		echo $admin_table_output;

	}

	function cf7hsfi_get_view_template( $template_name ) {

		$template_content = false;
		$template_path = CF7HSFI_VIEWS_DIR . $template_name;

		if( file_exists($template_path) ) {

			$search_replace = array(
				"<?php if(!defined( 'ABSPATH')) exit; ?>" => '',
				"{plugin_url}" => cf7hsfi_root_url(),
				"{site_url}" => get_site_url(),
			);

			$search = array_keys($search_replace);
			$replace = array_values($search_replace);

			$template_content = str_replace($search, $replace, file_get_contents( $template_path ));

		}

		return $template_content;

	}

	function cf7hsfi_admin_save_form( $cf7 ) {

		$post_id = sanitize_text_field($_GET['post']);

		$form_fields = array();

		foreach ($_POST['cf7hsfi_hs_field'] as $key => $value) {

			if($_POST['cf7hsfi_cf7_field'][$key] == '' && $value == '') continue;

			$form_fields[$value] = $_POST['cf7hsfi_cf7_field'][$key];

		}

		delete_option( $post_id . '_cf7hsfi_api_key');
		delete_option( $post_id . '_cf7hsfi_deal_name');
		delete_option( $post_id . '_cf7hsfi_deal_price');
		delete_option( $post_id . '_cf7hsfi_form_fields');
		add_option( $post_id . '_cf7hsfi_api_key',  $_POST['cf7hsfi_api_key'], '', 'yes' );
		add_option( $post_id . '_cf7hsfi_deal_name',  $_POST['cf7hsfi_deal_name'], '', 'yes' );
		add_option( $post_id . '_cf7hsfi_deal_price',  $_POST['cf7hsfi_deal_price'], '', 'yes' );
		add_option( $post_id . '_cf7hsfi_form_fields',  serialize($form_fields), '', 'yes' );

	}
	add_action('wpcf7_save_contact_form', 'cf7hsfi_admin_save_form');
	add_action("wpcf7_before_send_mail", "cf7hsfi_frontend_submit_form");

	function cf7hsfi_frontend_submit_form( $wpcf7_data ) {

		$post_id = $wpcf7_data->id;
		$api_key = get_option($post_id . "_cf7hsfi_api_key");
		$deal_name = get_option($post_id . "_cf7hsfi_deal_name");
		$deal_price = get_option($post_id . "_cf7hsfi_deal_price");
		$form_fields_str = get_option($post_id . "_cf7hsfi_form_fields");
		$form_fields = $form_fields_str ? unserialize($form_fields_str) : false;

		if( $form_fields ) {

			$properties = array();
			foreach ($form_fields as $key => $value) {

				array_push($properties, array('property' => $value,
					'value' => $_POST[$key]));

			}

			addContact($api_key, array('properties'=>$properties), $deal_name, $deal_price);
		}
	}

	function addContact($apiKey, array $properties, $deal_name, $deal_price) {

		$post_json = json_encode($properties);

		$endpoint = 'https://api.hubapi.com/contacts/v1/contact?hapikey=' . $apiKey;

		$response = postRequest($endpoint, $post_json);

		if (isset($response["vid"])) {

			addDeal($response["vid"],$deal_name, $apiKey, $deal_price);
		}
	}

	function addDeal($contactId, $dealName, $apiKey, $deal_price) {

		$endpoint = 'https://api.hubapi.com/deals/v1/deal?hapikey=' . $apiKey;

		postRequest($endpoint, generateDealProperties($contactId, $dealName, $apiKey, $deal_price));
	}

	function generateDealProperties($contactId, $dealName, $apiKey, $deal_price) {
		$properties = [
			[
				"value"=> $dealName,
				"name"=> "dealname"
			]
		];

		$stage = returnFirsPipeline($apiKey);

		if ($stage != "") {
			array_push($properties,
				[
					"value"=> $stage,
					"name"=> "dealstage"
				]);
		}

		if ($deal_price != "") {
			array_push($properties,
				[
					"value"=> $deal_price,
					"name"=> "amount"
				]);
		}

		$array = array("associations" => [
			"associatedVids" => [
				$contactId
			]
		],
			"properties" => $properties
		);

		return json_encode($array);
	}

	function postRequest($url, $data) {
		$ch = @curl_init();
		@curl_setopt($ch, CURLOPT_POST, true);
		@curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		@curl_setopt($ch, CURLOPT_URL, $url);
		@curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		@curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response = json_decode(@curl_exec($ch), 1);
		@curl_close($ch);
		return $response;
	}

	function returnFirsPipeline($apiKey) {

		$endpoint = 'https://api.hubapi.com/deals/v1/pipelines?hapikey=' . $apiKey;
		$ch = @curl_init();
		@curl_setopt($ch, CURLOPT_URL, $endpoint);
		@curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		@curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$response = json_decode(@curl_exec($ch), 1);

		@curl_close($ch);
		print_r($response);

		if (isset($response[0]['stages'][0]['stageId'])) {
			return $response[0]['stages'][0]['stageId'];
		}
		return "";
	}
}
