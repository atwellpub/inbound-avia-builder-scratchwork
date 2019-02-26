<?php
/*
Plugin Name: Inbound Extension - Enfold/Avia Template Builder
Plugin URI: http://www.inboundnow.com/
Description: Adds landing pages support to the Enfold Template Builder
Version: 1.0.8
Author: Inbound Now
Contributors: Hudson Atwell
Author URI: https://www.inboundnow.com/
*/


if (!class_exists('Inbound_AviaTemplate_Builder')) {


    class Inbound_AviaTemplate_Builder {

        /**
         *  Initialize class
         */
        public function __construct() {
            self::define_constants();
            self::load_hooks();
        }


        /**
         *  Define constants
         */
        public static function define_constants() {
            define('INBOUND_AVIA_TEMPLATE_BUILDER_CURRENT_VERSION', '1.0.8');
            define('INBOUND_AVIA_TEMPLATE_BUILDER_LABEL', __('Avia Template Builder Integration', 'inbound-pro'));
            define('INBOUND_AVIA_TEMPLATE_BUILDER_SLUG', 'inbound-cornerstone-builder');
            define('INBOUND_AVIA_TEMPLATE_BUILDER_FILE', __FILE__);
            define('INBOUND_AVIA_TEMPLATE_BUILDER_REMOTE_ITEM_NAME', 'cornerstone-page-builder-integration');
            define('INBOUND_AVIA_TEMPLATE_BUILDER_PATH', realpath(dirname(__FILE__)) . '/');
            $upload_dir = wp_upload_dir();
            $url = (!strstr(INBOUND_AVIA_TEMPLATE_BUILDER_PATH, 'plugins')) ? $upload_dir['baseurl'] . '/inbound-pro/extensions/' . plugin_basename(basename(__DIR__)) . '/' : WP_PLUGIN_URL . '/' . plugin_basename(dirname(__FILE__)) . '/';
            define('INBOUND_AVIA_TEMPLATE_BUILDER_URLPATH', $url);
        }

        /**
         * Load Hooks & Filters
         */
        public static function load_hooks() {
            add_filter('avf_builder_boxes', array( __CLASS__ , 'add_builder_to_posttype' ) );

            add_filter( 'avf_before_save_alb_post_data' , array( __CLASS__ , 'save_landing_page' ) , 100 , 2 );

            add_filter( 'avia_builder_precompile', array( __CLASS__ , 'prepare_content' ) , 100 , 1 );
            add_filter( 'avf_posts_alb_content', array( __CLASS__ , 'prepare_shortcode' ) , 100 , 1 );
            if (is_admin()) {
                /* Setup Automatic Updating & Licensing */
                add_action('admin_init', array(__CLASS__, 'license_setup'));
            }


        }

        function add_builder_to_posttype($metabox) {
            foreach($metabox as &$meta){
                if($meta['id'] == 'avia_builder' || $meta['id'] == 'layout') {
                    $meta['page'][] = 'landing-page';
                }
            }

            return $metabox;
        }

        public function save_landing_page( array $data, array $postarr )   {
            global $post;


            if ( $post->post_type !='landing-page' || wp_is_post_revision( $post->ID ) ) {
                return;
            }

            if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE  ) {
                return;
            }

            $variation_id = Landing_Pages_Variations::get_current_variation_id();
            //error_log('variation id');
            //error_log($variation_id);

            $post_content = $data['post_content'];
            //error_log('post content');
            //error_log($post_content);

            $shortcode = $_POST['_aviaLayoutBuilderCleanData'];
            //error_log('avia shortcode');
            //error_log($shortcode);


            if ( $variation_id > 0 ) {
                $content_key = 'content' . '-' . $variation_id;
                $shortcode_key = 'lp_aviaLayoutBuilderCleanData' . '-' . $variation_id;
            } else {
                $content_key = 'content';
                $shortcode_key = 'lp_aviaLayoutBuilderCleanData';
            }


            update_post_meta( $post->ID  , $content_key , $post_content );
            update_post_meta( $post->ID  , $shortcode_key , $shortcode );

            /**
            //error_log('here77');
            //error_log(print_r($data, true));
            //error_log(print_r($postarr, true));
            **/

            return $data;

        }

        public static function prepare_content($content) {
            global $post;

            $variation_id = Landing_Pages_Variations::get_current_variation_id();
            if ( $variation_id > 0 ) {
                $content_key = 'content' . '-' . $variation_id;
            } else {
                $content_key = 'content';
            }

            return get_post_meta($post->ID , $shortcode_key , true);

        }

        public static function prepare_shortcode($content) {
            global $post;
            //error_log('loading prepare shortcode');
            $variation_id = Landing_Pages_Variations::get_current_variation_id();
            if ( $variation_id > 0 ) {
                $shortcode_key = 'lp_aviaLayoutBuilderCleanData' . '-' . $variation_id;
            } else {
                $shortcode_key = 'lp_aviaLayoutBuilderCleanData';
            }

            return get_post_meta($post->ID , $shortcode_key , true);
        }

        /**
         * loads the correct variation into the preview page
         */
        public static function load_correct_variation() {
            global $current_variation_id;
            $current_variation_id = $_COOKIE['cornerstone_loaded_variation'];
        }

        /**
         * Setups Software Update API
         */
        public static function license_setup() {

            /* ignore these hooks if inbound pro is active */
            if (defined('INBOUND_PRO_CURRENT_VERSION')) {
                return;
            }

            /*PREPARE THIS EXTENSION FOR LICESNING*/
            if (class_exists('Inbound_License')) {
                $license = new Inbound_License(INBOUND_AVIA_TEMPLATE_BUILDER_FILE, INBOUND_AVIA_TEMPLATE_BUILDER_LABEL, INBOUND_AVIA_TEMPLATE_BUILDER_SLUG, INBOUND_AVIA_TEMPLATE_BUILDER_CURRENT_VERSION, INBOUND_AVIA_TEMPLATE_BUILDER_REMOTE_ITEM_NAME);
            }
        }
        
    }


    new Inbound_AviaTemplate_Builder();

}
