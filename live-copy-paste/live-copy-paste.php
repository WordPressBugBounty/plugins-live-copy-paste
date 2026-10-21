<?php

/**
 * Plugin Name: Live Copy Paste
 * Plugin URI: https://bdthemes.com/live-copy-paste/
 * Description: By using this plugin, you can easily import/paste all sections on your site from the Elementor Editor/Widget Demo/Ready-Made Pages and Blocks. One click to change the world.
 * Version: 1.5.12
 * Author: BdThemes
 * Author URI: https://bdthemes.com/
 * Text Domain: live-copy-paste
 * Domain Path: /languages
 * License: GPL3
 * Elementor requires at least: 4.0.0
 * Elementor tested up to: 4.2.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// BdThemes deals biggopti, shared by all BdThemes plugins (shown once).
if ( is_admin() ) {
	require_once __DIR__ . '/includes/bdthemes-deals-biggopti.php';
}


define( 'BDT_LCP_VER', '1.5.12' );

require_once 'classes/class-live-copy-paste-loader.php';

