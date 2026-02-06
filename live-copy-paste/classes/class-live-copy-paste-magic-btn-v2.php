<?php

/**
 * GET DATA from /widget-json/{postid}/{widgetid}
 *
 */

if (! class_exists('LiveCopyPasteMagicBtnV2')) {
	class LiveCopyPasteMagicBtnV2 {
		public function __construct() {
			add_action('wp_enqueue_scripts', array($this, 'enqueue_magic_btn_assets'));
		}

		public function enqueue_magic_btn_assets() {
			$this->enqueue_styles();
			$this->enqueue_scripts();
		}
		public function enqueue_styles() {
			wp_register_style('live-copy-paste-css', BDT_LCP_DIR_URL . 'assets/css/live-copy-paste-public.css', array(), BDT_LCP_VER, 'all');
			wp_enqueue_style('live-copy-paste-css');
		}

		public function enqueue_scripts() {
			wp_register_script('live-copy-paste-scripts-js', BDT_LCP_DIR_URL . 'assets/js/live-copy-paste-public-v2.js', ['jquery'], BDT_LCP_VER, true);
			wp_enqueue_script('live-copy-paste-scripts-js');
			wp_localize_script('live-copy-paste-scripts-js', 'live_copy_settings_control', [
				'only_login_users'      => get_option('lcp_enable_magic_copy_btn_login_user'),
				'only_specific_section' => get_option('lcp_enable_magic_copy_btn_specific_section'),
			]);
		}
	}
}

if (class_exists('LiveCopyPasteJSON')) {
	new LiveCopyPasteMagicBtnV2();
}
