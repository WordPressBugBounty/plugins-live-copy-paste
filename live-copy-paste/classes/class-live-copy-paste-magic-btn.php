<?php


use Elementor\Plugin;

if (! class_exists('LiveCopyPasteMagicBtn')) {
	class LiveCopyPasteMagicBtn {
		public function __construct() {
			add_action('wp_enqueue_scripts', array($this, 'enqueue_magic_btn_assets'));
			add_action('wp_ajax_live_copy_paste_magic_data_server_request', array($this, 'get_bdt_lcp_data'));

			if (1 != get_option('lcp_enable_magic_copy_btn_login_user')) {
				add_action('wp_ajax_nopriv_live_copy_paste_magic_data_server_request', array($this, 'get_bdt_lcp_data'));
			}
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
			wp_register_script('live-copy-paste-storage-js', BDT_LCP_DIR_URL . 'assets/js/xdLocalStorage.js', [], BDT_LCP_VER, true);
			wp_register_script('live-copy-paste-scripts-js', BDT_LCP_DIR_URL . 'assets/js/live-copy-paste-public.js', ['jquery', 'live-copy-paste-storage-js'], BDT_LCP_VER, true);

			wp_enqueue_script('live-copy-paste-storage-js');
			wp_enqueue_script('live-copy-paste-scripts-js');

			wp_localize_script('live-copy-paste-scripts-js', 'live_copy_settings_control', [
				'only_login_users'      => get_option('lcp_enable_magic_copy_btn_login_user'),
				'only_specific_section' => get_option('lcp_enable_magic_copy_btn_specific_section'),
			]);
		}

		private function find_element_recursive($elements, $form_id) {

			foreach ($elements as $element) {
				if ($form_id === $element['id']) {
					return $element;
				}

				if (! empty($element['elements'])) {
					$element = $this->find_element_recursive($element['elements'], $form_id);

					if ($element) {
						return $element;
					}
				}
			}

			return false;
		}

		private function find_element_recursive_2($elements, $form_id) {

			foreach ($elements as $element) {
				if ($form_id === $element['id']) {
					$section_data             = array();
					$section_data['elements'] = [$element];
					$meta_data                = array();
					$meta_data['type']        = 'elementor';
					$meta_data['siteurl']     = get_rest_url();
					$section_data             = array_merge($meta_data, $section_data);

					return $section_data;
				}
			}

			return false;
		}

		public function get_bdt_lcp_data() {
			$post_id   = isset($_REQUEST['post_id']) ? absint($_REQUEST['post_id']) : 0;
			$widget_id = isset($_REQUEST['widget_id']) ? sanitize_text_field(wp_unslash($_REQUEST['widget_id'])) : '';
			$nonce     = isset($_REQUEST['security']) ? sanitize_text_field(wp_unslash($_REQUEST['security'])) : '';

			if (!$post_id || !$widget_id) {
				wp_send_json_error(['message' => esc_html__('Sorry, invalid request!', 'live-copy-paste')]);
			}

			/*
			 * The nonce is printed into front end HTML, which a full page cache
			 * hands to every logged out visitor for longer than the 24 hour nonce
			 * lifetime, so the token is dead by the time the button is clicked.
			 * For a logged out visitor it is no boundary anyway - it is derived
			 * from user id 0 and an empty session token, so anybody can mint one -
			 * user_can_access_post() below is what actually guards the content.
			 * Enforce it where it still means something, on the logged in request.
			 */
			if (is_user_logged_in() && !wp_verify_nonce($nonce, 'live-copy-paste-magic-nonce')) {
				wp_send_json_error(['message' => esc_html__('Sorry, invalid request!', 'live-copy-paste')]);
			}

			if (1 == get_option('lcp_enable_magic_copy_btn_login_user') && !is_user_logged_in()) {
				wp_send_json_error(['message' => esc_html__('Authentication required.', 'live-copy-paste')]);
			}

			if (!$this->user_can_access_post($post_id)) {
				wp_send_json_error(['message' => esc_html__('Sorry, you are not allowed to access this content.', 'live-copy-paste')]);
			}

			$result = $this->get_bdt_lcp_data_settings($post_id, $widget_id);

			if (is_wp_error($result)) {
				wp_send_json_error(['message' => $result->get_error_message()]);
			}

			$data = array(
				'widget_data' => [
					'widget' => $result['widget_data'],
				],
				'copy_data'   => $result['copy_data'],
			);
			wp_send_json_success($data);
		}

		private function user_can_access_post($post_id) {
			if (current_user_can('edit_post', $post_id)) {
				return true;
			}

			return is_post_publicly_viewable($post_id);
		}

		protected function get_bdt_lcp_data_settings($post_id, $widget_id) {
			$errors = new \WP_Error();

			$elementor = Plugin::$instance;
			$pageMeta  = $elementor->documents->get($post_id);

			if (! $pageMeta) {
				$errors->add('msg', esc_html__('Invalid Post or Page ID.', 'live-copy-paste'));
				return $errors;
			}

			$metaData = $pageMeta->get_elements_data();

			if (! $metaData) {
				$errors->add('msg', esc_html__('Page page is not under elementor.', 'live-copy-paste'));
				return $errors;
			}

			$widget_data = array();

			$widget_data['widget_data'] = $this->find_element_recursive($metaData, $widget_id);
			$widget_data['copy_data']   = $this->find_element_recursive_2($metaData, $widget_id);

			return $widget_data;
		}
	}
}


if (!class_exists('LiveCopyPasteJSON')) {
	new LiveCopyPasteMagicBtn();
}
