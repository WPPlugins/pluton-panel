<?php
/**
 * WPML Configuration Class
 *
 * @package    Pluton
 * @author     Nick
 * @copyright  Copyright (c) 2015, Nick
 * @license    http://www.gnu.org/licenses/gpl-2.0.html
 * @since      1.0.0
 */

global $wpml_config;

if ( ! class_exists( 'PLUTON_WPML_Config' ) ) {

	class PLUTON_WPML_Config {

		/**
		 * Start things up
		 *
		 * @since 1.0.0
		 */
		public function __construct() {

			// Add Actions
			add_action( 'admin_init', array( $this, 'register_strings' ) );
			add_filter( 'body_class', array( $this, 'body_class' ) );

			// Add Filters
			add_filter( 'upload_dir', array( $this, 'upload_dir' ) );
			add_filter( 'pluton_toggle_bar_content_id', array( $this, 'toggle_bar_content_id' ) );

			// Register shortcodes
			add_shortcode( 'wpml_translate', array( $this, 'translate_shortcode' ) );
			add_shortcode( 'wpml_lang_selector', array( $this, 'switcher_shortcode' ) );

		}

		/**
		 * Registers theme_mod strings into WPML
		 *
		 * @since 1.0.0
		 */
		public function register_strings() {
			if ( function_exists( 'icl_register_string' ) && $strings = pluton_register_theme_mod_strings() ) {
				foreach( $strings as $string => $default ) {
					icl_register_string( 'Theme Mod', $string, get_theme_mod( $string, $default ) );
				}
			}
		}

		/**
		 * Registers theme_mod strings into WPML
		 *
		 * @since 1.0.0
		 */
		public function body_class( $classes ) {
			if ( defined( 'ICL_LANGUAGE_CODE' ) ) {
				$classes[] = 'wpml-language-'. ICL_LANGUAGE_CODE;
			}
			return $classes;
		}

		/**
		 * Fix for when users have the Language URL Option on "different domains"
		 * which causes cropped images to fail.
		 *
		 * @since 1.0.0
		 */
		public function upload_dir( $upload ) {

			// Check if WPML language_negociation type
			$language_negociation = apply_filters( 'wpml_setting', false, 'language_negotiation_type' );
			if ( $language_negociation !== false && $language_negociation == 2 ) {
				$upload['baseurl'] = apply_filters( 'wpml_permalink', $upload['baseurl'] );
			}

			// Return $upload var
			return $upload;

		}

		/**
		 * Converts toggle page ID to WPML compatible ID
		 *
		 * @since 1.0.0
		 */
		public function toggle_bar_content_id( $id ) {
			if ( function_exists( 'icl_object_id' ) && defined( 'ICL_LANGUAGE_CODE' ) ) {
				$id = icl_object_id( $id, 'page', false, ICL_LANGUAGE_CODE );
			}
			return $id;
		}

		/**
		 * WPML Translation Shortcode
		 *
		 * [wpml_translate lang=es]Hola[/wpml_translate]
		 * [wpml_translate lang=en]Hello[/wpml_translate]
		 *
		 * @since 1.0.0
		 */
		public function translate_shortcode( $atts, $content = null ) {
			extract( shortcode_atts( array(
				'lang'	=> '',
			), $atts ) );
			$lang_active = ICL_LANGUAGE_CODE;
			if ( $lang == $lang_active ) {
				return do_shortcode( $content );
			}
		}

		/**
		 * Language switcher plugin
		 *
		 * @since 1.0.0
		 */
		public function switcher_shortcode( $atts, $content = null ) {
			do_action( 'icl_language_selector' );
		}

	}
	
}
$wpml_config = new PLUTON_WPML_Config();