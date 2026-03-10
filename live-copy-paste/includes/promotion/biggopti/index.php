<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'BdtAdminApiBiggopties' ) ) {

	/**
	 * Admin Api Biggopties class
	 */
	class BdtAdminApiBiggopties {

		private static $instance;

		public static function get_instance() {
			if (!isset(self::$instance)) {
				self::$instance = new self;
			}
			return self::$instance;
		}

		public function __construct() {
			add_action('wp_ajax_bdt_admin_api_biggopti_dismiss', [$this, 'bdt_admin_api_biggopti_dismiss']);
			add_action('admin_enqueue_scripts', [$this, 'bdt_admin_api_biggopti_scripts']);
		}

		public function bdt_admin_api_biggopti_scripts() {
			$bdt_admin_api_biggopti_dir_url = plugin_dir_url(__FILE__);
			wp_enqueue_script(
				'lcp-admin-api-biggopti',
				$bdt_admin_api_biggopti_dir_url . 'script.js',
				[],
				'1.0.0',
				true
			);
			wp_enqueue_style(
				'bdt-admin-api-biggopti',
				$bdt_admin_api_biggopti_dir_url . 'style.css',
				[],
				'1.0.0'
			);

			$dismissals = get_option('bdt_biggopti_dismissals', []);
			$dismissed_display_ids = [];
			$prefix = 'bdt-admin-biggopti-api-biggopti-';
			foreach (array_keys($dismissals) as $key) {
				if (strpos($key, $prefix) === 0) {
					$dismissed_display_ids[] = substr($key, strlen($prefix));
				} else {
					$dismissed_display_ids[] = $key;
				}
			}

			$current_sector = '';
			if ( isset( $_GET['page'] ) && $_GET['page'] === 'live_copy_paste_options' ) {
				$current_sector = 'plugin_dashboard';
			}
			wp_localize_script('lcp-admin-api-biggopti', 'BdtAdminApiBiggoptiConfig', [
				'ajax_url' => admin_url('admin-ajax.php'),
				'nonce'    => wp_create_nonce('bdt-admin-api-biggopti'),
				// 'isPro'             	=> function_exists('_is_lcp_pro_activated') && _is_lcp_pro_activated(),
				'isPro'             	=> false,
				'assetsUrl'         	=> $bdt_admin_api_biggopti_dir_url,
				'dismissedDisplayIds'	=> $dismissed_display_ids,
				'currentSector'      	=> $current_sector,
			]);
		}

		/**
		 * Dismiss Admin API Biggopti.
		 */
		public function bdt_admin_api_biggopti_dismiss() {
			$nonce = (isset($_POST['_wpnonce'])) ? sanitize_text_field($_POST['_wpnonce']) : '';
			$display_id = (isset($_POST['display_id'])) ? sanitize_text_field($_POST['display_id']) : '';
			$id   = (isset($_POST['id'])) ? esc_attr($_POST['id']) : '';
			$meta = (isset($_POST['meta'])) ? esc_attr($_POST['meta']) : '';

			if ( ! wp_verify_nonce($nonce, 'bdt-admin-api-biggopti') ) {
				wp_send_json_error();
			}

			if ( ! current_user_can('manage_options') ) {
				wp_send_json_error();
			}

			// Prefer display_id; fallback: extract from id (bdt-admin-api-biggopti-{display_id})
			if (empty($display_id) && !empty($id)) {
				$prefix = 'bdt-admin-api-biggopti-';
				if (strpos($id, $prefix) === 0) {
					$display_id = substr($id, strlen($prefix));
				} else {
					$display_id = $id;
				}
			}

			/**
			 * Valid inputs?
			 */
			if (!empty($display_id)) {
				if ('user' === $meta) {
					$user_key = 'bdt-admin-api-biggopti-' . $display_id;
					update_user_meta(get_current_user_id(), $user_key, true);
				} else {
					// Save to options table only - display_id based, no end-time expiration
					$dismissals_option = get_option('bdt_biggopti_dismissals', []);
					$dismissals_option[$display_id] = ['dismissed_at' => time()];
					update_option('bdt_biggopti_dismissals', $dismissals_option, false);
				}

				wp_send_json_success();
			}

			wp_send_json_error();
		}
	}
}

BdtAdminApiBiggopties::get_instance();