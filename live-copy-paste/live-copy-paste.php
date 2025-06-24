<?php

/**
 * Plugin Name: Live Copy Paste
 * Plugin URI: https://bdthemes.com/live-copy-paste/
 * Description: By using this plugin, you can easily import/paste all sections on your site from the Elementor Editor/Widget Demo/Ready-Made Pages and Blocks. One click to change the world.
 * Version: 1.4.8
 * Author: BdThemes
 * Author URI: https://bdthemes.com/
 * Text Domain: live-copy-paste
 * Domain Path: /languages
 * License: GPL3
 * Elementor requires at least: 3.22
 * Elementor tested up to: 3.29.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


define( 'BDT_LCP_VER', '1.4.8' );

require_once 'classes/class-live-copy-paste-loader.php';



/**
 * SDK Integration
 */

if ( ! function_exists( 'live_copy_paste_dci_plugin' ) ) {
	function live_copy_paste_dci_plugin() {

		// Include DCI SDK.
		require_once dirname( __FILE__ ) . '/dci/start.php';
		wp_register_style( 'dci-sdk-live-copy-paste', plugins_url( 'dci/assets/css/dci.css', __FILE__ ), array(), '1.2.1', 'all' );
		wp_enqueue_style( 'dci-sdk-live-copy-paste' );

		dci_dynamic_init( array(
			'sdk_version'          => '1.2.1',
			'product_id'           => 9,
			'plugin_name'          => 'Live Copy Paste', // make simple, must not empty
			'plugin_title'         => 'Love using Live Copy Paste? Congrats 🎉  ( Never miss an Important Update )', // You can describe your plugin title here
			'plugin_icon'          => plugins_url( 'assets/imgs/logo.svg', __FILE__ ),
			'api_endpoint'         => 'https://analytics.bdthemes.com/wp-json/dci/v1/data-insights',
			'slug'                 => 'live-copy-paste', // folder-name or write 'no-need' if you don't want to use
			'core_file'            => false,
			'plugin_deactivate_id' => false,
			'public_key'           => 'pk_JH50y0w3bTGLDxfzWei69eRgcBsrTxyM',
			'is_premium'           => false,
			//'custom_data' => array(
			//'test' => 'value',
			//),
			'popup_notice'         => false,
			'deactivate_feedback'  => true,
			'text_domain'          => 'live-copy-paste',
			'plugin_msg'           => '<p>Be Top-contributor by sharing non-sensitive plugin data and create an impact to the global WordPress community today! You can receive valuable emails periodically.</p>',
		) );

	}
	add_action( 'admin_init', 'live_copy_paste_dci_plugin' );
}
