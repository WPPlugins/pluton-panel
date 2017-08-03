<?php
/**
 * Polylang Functions
 *
 * @package    Pluton
 * @author     Nick
 * @copyright  Copyright (c) 2015, Nick
 * @license    http://www.gnu.org/licenses/gpl-2.0.html
 * @since      1.0.0
 */

// Start Class
if ( ! class_exists( 'PLUTON_Polylang_Config' ) ) {

	class PLUTON_Polylang_Config {

		/**
		 * Start things up
		 *
		 * @since 1.0.0
		 */
		public function __construct() {
			add_action( 'init', array( $this, 'register_strings' ) );
			add_shortcode( 'polylang_switcher', array( $this, 'switcher_shortcode' ) );
		}

		/**
		 * Registers theme_mod strings into Polylang
		 *
		 * @since 1.0.0
		 */
		public function register_strings() {
			if ( function_exists( 'pll_register_string' ) && $strings = pluton_register_theme_mod_strings() ) {
				foreach( $strings as $string => $default ) {
					pll_register_string( $string, get_theme_mod( $string, $default ), 'Theme Mod', true );
				}
			}
		}

		/**
		 * Registers the Polylang Language Switcher function as a shortcode
		 *
		 * @since 1.0.0
		 */
		public function switcher_shortcode( $atts, $content = null ) {

			// Make sure pll_the_languages() is defined
			if ( function_exists( 'pll_the_languages' ) ) {

				// Extract attributes
				extract( shortcode_atts( array(
					'dropdown'               => false,
					'show_flags'             => true,
					'show_names'             => false,
					'classes'                => '',
					'hide_if_empty'          => true,
					'force_home'             => false,
					'hide_if_no_translation' => false,
					'hide_current'           => false,
					'post_id'                => null,
					'raw'                    => false,

				), $atts ) );

				// Args
				$dropdown   = 'true' == $dropdown ? true : false;
				$show_flags = 'true' == $show_flags ? true : false;
				$show_names = 'true' == $show_names ? true : false;

				// Dropdown args
				if ( $dropdown ) {
					$show_flags = $show_names = false;
				}

				// Classes
				$wrap_classes = 'polylang-switcher-shortcode clr';
				if ( $show_names && !$dropdown ) {
					$wrap_classes .= ' flags-and-names';
				}
				if ( $classes ) {
					$wrap_classes .= ' '. $classes;
				}

				// Display Switcher
				if ( ! $dropdown ) {
					echo '<ul class="'. $wrap_classes .'">';
				}

					// Display the switcher
					pll_the_languages( array(
						'dropdown'               => $dropdown,
						'show_flags'             => $show_flags,
						'show_names'             => $show_names,
						'hide_if_empty'          => $hide_if_empty,
						'force_home'             => $force_home,
						'hide_if_no_translation' => $hide_if_no_translation,
						'hide_current'           => $hide_current,
						'post_id'                => $post_id,
						'raw'                    => $raw,
						) );

				if ( ! $dropdown ) {
					echo '</ul>';
				}

			}

		}

	}

}
new PLUTON_Polylang_Config();