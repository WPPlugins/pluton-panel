<?php
/**
 * Creates the admin panel and custom CSS output
 *
 * @package Pluton_Panel
 * @category Core
 * @author Nick
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Start Class
if ( ! class_exists( 'Pluton_Custom_CSS' ) ) {
	class Pluton_Custom_CSS {

		/**
		 * Start things up
		 *
		 * @since 1.0.0
		 */
		public function __construct() {
			add_action( 'admin_menu', array( $this, 'add_page' ), 20 );
			add_action( 'admin_bar_menu', array( $this, 'adminbar_menu' ), 999 );
			add_action( 'admin_init', array( $this,'register_settings' ) );
			add_action( 'admin_enqueue_scripts',array( $this,'scripts' ) );
			add_action( 'admin_notices', array( $this, 'notices' ) );
			add_action( 'pluton_head_css' , array( $this, 'output_css' ), 9999 );
		}

		/**
		 * Add sub menu page for the custom CSS input
		 *
		 * @since 1.0.0
		 */
		public function add_page() {
			add_submenu_page(
				'pluton-panel',
				esc_html__( 'Custom CSS', 'pluton-panel' ),
				esc_html__( 'Custom CSS', 'pluton-panel' ),
				'administrator',
				'pluton-panel-custom-css',
				array( $this, 'create_admin_page' )
			);
		}

		/**
		 * Add custom CSS to the adminbar since it will be used frequently
		 *
		 * @since 1.0.0
		 */
		public static function adminbar_menu( $wp_admin_bar ) {
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}
			$url  = admin_url( 'admin.php?page=pluton-panel-custom-css' );
			$args = array(
				'id'    => 'pluton_custom_css',
				'title' => esc_html__( 'Custom CSS', 'pluton-panel' ),
				'href'  => $url,
				'meta'  => array(
				'class' => 'pluton-custom-css',
				)
			);
			$wp_admin_bar->add_node( $args );
		}

		/**
		 * Load scripts
		 *
		 * @since 1.0.0
		 */
		public function scripts( $hook ) {
			if ( PP_ADMIN_PANEL_HOOK_PREFIX . '-custom-css' == $hook ) {
				wp_deregister_script( 'ace-editor' );
				wp_enqueue_script( 'pluton-ace-editor', plugins_url( '/assets/ace.js', __FILE__ ), array(), true );
			}
		}

		/**
		 * Register a setting and its sanitization callback.
		 *
		 * @since 1.0.0
		 */
		public function register_settings() {
			register_setting( 'pluton_custom_css', 'pluton_custom_css', array( $this, 'sanitize' ) );
		}

		/**
		 * Displays all messages registered to 'pluton-custom_css-notices'
		 *
		 * @since 1.0.0
		 */
		public static function notices() {
			settings_errors( 'pluton_custom_css_notices' );
		}

		/**
		 * Sanitization callback
		 *
		 * @since 1.0.0
		 */
		public static function sanitize( $option ) {

			// Sanitize and save theme mod
			if ( ! empty( $option ) ) {
				set_theme_mod( 'custom_css', wp_strip_all_tags( $option ) );
			} else {
				remove_theme_mod( 'custom_css' );
			}

			// Return notice
			add_settings_error(
				'pluton_custom_css_notices',
				esc_attr( 'settings_updated' ),
				esc_html__( 'Settings saved.', 'pluton-panel' ),
				'updated'
			);

			// Lets save the custom CSS into a standard option as well for backup
			return $option;
		}

		/**
		 * Settings page output
		 *
		 * @since 1.0.0
		 */
		public static function create_admin_page() { ?>

			<div class="wrap">

				<h2><?php esc_html_e( 'Custom CSS', 'pluton-panel' ); ?></h2>

				<?php
				// Get custom CSS
				$custom_css = get_theme_mod( 'custom_css', null ); ?>

				<div>
					<form method="post" action="options.php">
						<?php settings_fields( 'pluton_custom_css' ); ?>
						<table class="form-table">
							<tr valign="top">
								<td style="padding:0;">
									<textarea rows="40" cols="50" id="pluton_custom_css" style="display:none;" name="pluton_custom_css"><?php echo wp_strip_all_tags( $custom_css ); ?></textarea>
									<pre id="pluton_custom_css_editor" style="width:100%;height:800px;font-size:14px; border: 1px solid #bababa;"><?php echo wp_strip_all_tags( $custom_css ); ?></pre>
								</td>
							</tr>
						</table>
						<?php submit_button(); ?>
					</form>
				</div>

			</div><!-- .wrap -->

			<script>
				( function( $ ) {
					"use strict";
					jQuery( document ).ready( function( $ ) {

						// Start ace editor
						var $css_editor = $( '#pluton_custom_css_editor' ),
							$css_editor_input = $( '#pluton_custom_css' ),
							$editor = ace.edit( 'pluton_custom_css_editor' );
						$editor.getSession().setUseWorker(false);
						$editor.getSession().setMode( "ace/mode/css" );
						$editor.setTheme( "ace/theme/chrome" );
						$editor.find('needle',{
							backwards: false,
							wrap: false,
							caseSensitive: false,
							wholeWord: false,
							regExp: false
						});
						$editor.findNext();
						$editor.findPrevious();

						// Add val to hidden field
						$editor.on('input', function() {
							$css_editor_input.val( $editor.getValue() );
						} );

					} );
				} ) ( jQuery );
			</script>

		<?php }

		/**
		 * Outputs the custom CSS to the wp_head
		 *
		 * @since 1.0.0
		 */
		public static function output_css( $output ) {
			if ( $css = get_theme_mod( 'custom_css', false ) ) {
				$output .= '/*CUSTOM CSS*/'. $css;
			}
			return $output;
		}

	}
}
$pluton_custom_css = new Pluton_Custom_CSS();