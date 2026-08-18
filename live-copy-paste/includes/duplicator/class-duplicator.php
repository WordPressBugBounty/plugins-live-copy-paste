<?php


use Elementor\Core\Files\CSS\Post as Post_CSS;

if (!defined('ABSPATH')) {
    exit;
}
// Exit if accessed directly
if (!class_exists('BdThemes_Duplicator')) :
    class BdThemes_Duplicator {

        public function __construct() {
            add_action('admin_action_bdt_duplicate_as_draft', [$this, 'bdt_duplicate_as_draft']);
            add_filter('post_row_actions', [$this, 'bdt_duplicate_post_link'], 10, 2);
            add_filter('page_row_actions', [$this, 'bdt_duplicate_post_link'], 10, 2);
        }

        public function bdt_duplicate_as_draft() {
            global $wpdb;

            /*
         * get the original post id first, the nonce and the capability checks
         * below are both bound to it
         */
            $bdt_post_id = isset($_GET['post']) ? absint($_GET['post']) : (isset($_POST['post']) ? absint($_POST['post']) : 0);

            if (!$bdt_post_id) {
                wp_die(esc_html__('No post to duplicate has been supplied!', 'live-copy-paste'));
            }

            /*
        * Nonce verification. The nonce action carries the post id so a link
        * issued for one post cannot be replayed against a different one.
        */
            $bdt_nonce = isset($_GET['duplicate_nonce']) ? sanitize_text_field(wp_unslash($_GET['duplicate_nonce'])) : '';

            if (!wp_verify_nonce($bdt_nonce, 'bdt_duplicate_as_draft_' . $bdt_post_id)) {
                wp_die(esc_html__('Security check failed, please go back and try again!', 'live-copy-paste'), '', ['response' => 403]);
            }

            /*
         * and all the original post data then
         */
            $bdt_post = get_post($bdt_post_id);

            /*
         * The user must be allowed to edit this very post, not merely to publish
         * posts in general, otherwise any author level user could duplicate - and
         * so read - private or draft content owned by somebody else.
         */
            if (!$this->user_can_duplicate($bdt_post)) {
                wp_die(esc_html__('You have not any permission to duplicate it, please go back!', 'live-copy-paste'), '', ['response' => 403]);
            }
            /*
         * if you don't want current user to be the new post author,
         * then change next couple of lines to this: $new_post_author = $post->post_author;
         */
            $bdt_current_user    = wp_get_current_user();
            $bdt_new_post_author = $bdt_current_user->ID;

            /*
        * if post data exists, create the post duplicate
        */
            if (isset($bdt_post) && $bdt_post != null) {
                /*
             * new post data array
             */
                $bdt_args = [
                    'post_status'    => 'draft',
                    /* translators: %1$s: original post title */
                    'post_title'     => sprintf(__('%1$s - [Duplicated]', 'live-copy-paste'), $bdt_post->post_title),
                    'post_type'      => $bdt_post->post_type,
                    'post_name'      => $bdt_post->post_name,
                    'post_content'   => $bdt_post->post_content,
                    'post_excerpt'   => $bdt_post->post_excerpt,
                    'post_author'    => $bdt_new_post_author,
                    'post_parent'    => $bdt_post->post_parent,
                    'post_password'  => $bdt_post->post_password,
                    'comment_status' => $bdt_post->comment_status,
                    'ping_status'    => $bdt_post->ping_status,
                    'menu_order'     => $bdt_post->menu_order,
                    'to_ping'        => $bdt_post->to_ping,
                ];

                /*
             * insert the post by wp_insert_post() function
             */
                $bdt_new_post_id = wp_insert_post($bdt_args);

                /*
             * get all current post terms ad set them to the new post draft
             */
                $bdt_taxonomies = get_object_taxonomies($bdt_post->post_type);

                /*
             * returns array of taxonomy names for post type, ex array("category", "post_tag");
             */

                foreach ($bdt_taxonomies as $bdt_taxonomy) {
                    $bdt_post_terms = wp_get_object_terms($bdt_post_id, $bdt_taxonomy, ['fields' => 'slugs']);
                    wp_set_object_terms($bdt_new_post_id, $bdt_post_terms, $bdt_taxonomy, false);
                }

                /*
             * duplicate all post meta just in two SQL queries
             */
                $bdt_post_meta_infos = $wpdb->get_results("SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id=$bdt_post_id");

                if (is_array($bdt_post_meta_infos)) {
                    $bdt_sql_query     = "INSERT INTO {$wpdb->postmeta} ( post_id, meta_key, meta_value ) VALUES ";
                    $bdt_sql_query_sel = [];

                    foreach ($bdt_post_meta_infos as $bdt_meta_info) {
                        $bdt_meta_value      = wp_slash($bdt_meta_info->meta_value);
                        $bdt_sql_query_sel[] = "( $bdt_new_post_id, '{$bdt_meta_info->meta_key}', '{$bdt_meta_value}' )";
                    }

                    $bdt_sql_query .= implode(', ', $bdt_sql_query_sel) . ';';
                    $wpdb->query($bdt_sql_query);

                    /*
                * fix template type issues
                */
                    $source_type = get_post_meta($bdt_post_id, '_elementor_template_type', true);
                    delete_post_meta($bdt_new_post_id, '_elementor_template_type');
                    update_post_meta($bdt_new_post_id, '_elementor_template_type', $source_type);
                }

                $css = Post_CSS::create($bdt_new_post_id);
                $css->update();

                /*
             * finally, redirect to the edit post screen for the new draft
             */

                $bdt_all_post_types = get_post_types([], 'names');

                foreach ($bdt_all_post_types as $bdt_key => $bdt_value) {
                    $bdt_names[] = $bdt_key;
                }

                $bdt_current_post_type = get_post_type($bdt_post_id);

                if (is_array($bdt_names) && in_array($bdt_current_post_type, $bdt_names)) {
                    wp_redirect(admin_url('edit.php?post_type=' . $bdt_current_post_type));
                }

                exit;
            } else {
                wp_die('Failed. Not Found Post: ' . esc_html($bdt_post_id));
            }
        }

        /*
         * Post types this plugin is allowed to duplicate. The handler is reachable
         * on its own, so it has to agree with the row action link.
         */
        private function get_duplicable_post_types() {
            return ['post', 'page', 'elementor_library'];
        }

        private function user_can_duplicate($post) {
            if (!$post instanceof WP_Post) {
                return false;
            }

            if (!in_array($post->post_type, $this->get_duplicable_post_types(), true)) {
                return false;
            }

            // Read side, the duplicate exposes the whole content and post meta.
            if (!current_user_can('edit_post', $post->ID)) {
                return false;
            }

            // Write side, the duplicate is a new post of the same type.
            $bdt_post_type_object = get_post_type_object($post->post_type);

            if (!$bdt_post_type_object) {
                return false;
            }

            return current_user_can($bdt_post_type_object->cap->create_posts);
        }

        public function bdt_duplicate_post_link($actions, $post) {

            if (!$this->user_can_duplicate($post)) {
                return $actions;
            }

            $bdt_labels = [
                'post'              => _x('Duplicate Post', 'Admin String', 'live-copy-paste'),
                'page'              => _x('Duplicate Page', 'Admin String', 'live-copy-paste'),
                'elementor_library' => _x('Duplicate Template', 'Admin String', 'live-copy-paste'),
            ];

            $bdt_label = $bdt_labels[$post->post_type];

            $bdt_url = wp_nonce_url(
                'admin.php?action=bdt_duplicate_as_draft&post=' . $post->ID,
                'bdt_duplicate_as_draft_' . $post->ID,
                'duplicate_nonce'
            );

            $actions['duplicate'] = '<a href="' . esc_url($bdt_url) . '" title="' . esc_attr($bdt_label) . '" rel="permalink">' . esc_html($bdt_label) . '</a>';

            return $actions;
        }
    }

    new BdThemes_Duplicator();
endif;
