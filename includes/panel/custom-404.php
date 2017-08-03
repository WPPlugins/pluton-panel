<?php
/**
 * Custom 404 Page Design
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
if ( ! class_exists( 'Pluton_Custom_Error_page' ) ) {

	class Pluton_Custom_Error_page {

		/**
		 * Start things up
		 */
		public function __construct() {
			add_action( 'admin_menu', array( $this, 'add_page' ) );
			add_action( 'admin_init', array( $this,'register_page_options' ) );
			add_filter( 'template_redirect', array( $this, 'redirect' ) );
		}

		/**
		 * Add sub menu page for the custom CSS input
		 *
		 * @link http://codex.wordpress.org/Function_Reference/add_theme_page
		 */
		public function add_page() {
			add_submenu_page(
				'pluton-panel',
				esc_html__( 'Custom 404', 'pluton-panel' ),
				esc_html__( 'Custom 404', 'pluton-panel' ),
				'administrator',
				'pluton-panel-404',
				array( $this, 'create_admin_page' )
			);
		}

		/**
		 * Function that will register admin page options.
		 *
		 * @link http://codex.wordpress.org/Function_Reference/register_setting
		 * @link http://codex.wordpress.org/Function_Reference/add_settings_section
		 * @link http://codex.wordpress.org/Function_Reference/add_settings_field
		 */
		public function register_page_options() {

			// Register settings
			register_setting( 'pluton_error_page', 'error_page', array( $this, 'sanitize' ) );

			// Add main section to our options page
			add_settings_section( 'pluton_error_page_main', false, array( $this, 'section_main_callback' ), 'pluton-custom-error-page-admin' );

			// Redirect field
			add_settings_field(
				'redirect',
				esc_html__( 'Redirect 404\'s', 'pluton-panel' ),
				array( $this, 'redirect_field_callback' ),
				'pluton-custom-error-page-admin',
				'pluton_error_page_main'
			);

			// Custom Page ID
			add_settings_field(
				'error_page_id',
				esc_html__( 'Custom 404 Redirect', 'pluton-panel' ),
				array( $this, 'content_id_field_callback' ),
				'pluton-custom-error-page-admin',
				'pluton_error_page_main'
			);

			// Title field
			add_settings_field(
				'error_page_title',
				esc_html__( '404 Page Title', 'pluton-panel' ),
				array( $this, 'title_field_callback' ),
				'pluton-custom-error-page-admin',
				'pluton_error_page_main'
			);

			// Content field
			add_settings_field(
				'error_page_text',
				esc_html__( '404 Page Content', 'pluton-panel' ),
				array( $this, 'content_field_callback' ),
				'pluton-custom-error-page-admin',
				'pluton_error_page_main'
			);

		}

		/**
		 * Sanitization callback
		 */
		public function sanitize( $options ) {

			// Set theme mods
			if ( isset( $options['redirect'] ) ) {
				set_theme_mod( 'error_page_redirect', 1 );
			} else {
				remove_theme_mod( 'error_page_redirect' );
			}

			if ( ! empty( $options['title'] ) ) {
				set_theme_mod( 'error_page_title', $options['title'] );
			} else {
				remove_theme_mod( 'error_page_title' );
			}

			if ( ! empty( $options['text'] ) ) {
				set_theme_mod( 'error_page_text', $options['text'] );
			} else {
				remove_theme_mod( 'error_page_text' );
			}

			if ( ! empty( $options['content_id'] ) ) {
				set_theme_mod( 'error_page_content_id', $options['content_id'] );
			} else {
				remove_theme_mod( 'error_page_content_id' );
			}

			// Set options to nothing since we are storing in the theme mods
			$options = '';
			return $options;
		}

		/**
		 * Main Settings section callback
		 */
		public function section_main_callback( $options ) {
			// Leave blank
		}

		/**
		 * Fields callback functions
		 */

		// Redirect field
		public function redirect_field_callback() {
			$val = get_theme_mod( 'error_page_redirect' );
			echo '<input type="checkbox" name="error_page[redirect]" id="error-page-redirect" value="'. esc_attr( $val ) .'" '. checked( $val, true, false ) .'> ';
			echo '<span class="description">'. esc_html__( 'Automatically 301 redirect all 404 errors to your homepage.', 'pluton-panel' ) .'</span>';
		}

		// Custom Error Page ID
		public function content_id_field_callback() {
			wp_dropdown_pages( array(
				'echo'             => true,
				'selected'         => get_theme_mod( 'error_page_content_id' ),
				'name'             => 'error_page[content_id]',
				'id'               => 'error-page-content-id',
				'show_option_none' => esc_html__( 'None', 'pluton-panel' ),
			) ); ?>
			<br />
			<p class="description"><?php esc_html_e( 'Select a custom page if you want to use the Visual Composer to create your custom 404 page.', 'pluton-panel' ) ?></p>
		<?php }

		// Title field
		public function title_field_callback() { ?>
			<input type="text" name="error_page[title]" id="error-page-title" value="<?php echo get_theme_mod( 'error_page_title' ); ?>">
			<p class="description"><?php esc_html_e( 'Enter a custom title for your 404 page.', 'pluton-panel' ) ?></p>
		<?php }

		// Content field
		public function content_field_callback() {
			wp_editor( get_theme_mod( 'error_page_text' ), 'error_page_text', array(
				'textarea_name' => 'error_page[text]'
			) );
		}

		/**
		 * Settings page output
		 */
		public function create_admin_page() { ?>
			<div class="wrap">
				<h2 style="padding-right:0;">
					<?php esc_html_e( 'Custom 404', 'pluton-panel' ); ?>
				</h2>
				<form method="post" action="options.php">
					<?php settings_fields( 'pluton_error_page' ); ?>
					<?php do_settings_sections( 'pluton-custom-error-page-admin' ); ?>
					<?php submit_button(); ?>
				</form>
				<script>
					( function( $ ) {
						"use strict";

						// Hide/Show fields
						var	$redirectErrorPage = $( '#error-page-redirect' ),
							$pageIdSelect      = $( '#error-page-content-id' ),
							$pageIdVal         = $pageIdSelect.val(),
							$fieldsTohide      = $( '#error-page-title, #wp-error_page_text-wrap' );

						// Get tr of field to hide
						var $elementsTohide = $fieldsTohide.closest( 'tr' );

						// Check initial val
						if ( '1' == $redirectErrorPage.val() ) {
							$pageIdSelect.closest( 'tr' ).hide();
						}
						if ( $pageIdVal || '1' == $redirectErrorPage.val() ) {
							$elementsTohide.hide();
						}

						// Check on change
						$( $redirectErrorPage ).change(function () {
							if ( $(this ).is( ":checked" ) ) {
								$pageIdSelect.closest( 'tr' ).hide();
								$elementsTohide.hide();
							} else {
								$pageIdSelect.closest( 'tr' ).show();
								if ( ! $pageIdSelect.val() ) {
									$elementsTohide.show();
								}
							}
						} );
						$( $pageIdSelect ).change(function () {
							var $selected = $( this ).val();
							if ( $selected !== '' ) {
								$elementsTohide.hide();
							} else {
								$elementsTohide.show();
							}
						});

					} ) ( jQuery );
				</script>
			</div><!-- .wrap -->
		<?php }

		/**
		 * Redirect all pages to the under cronstruction page if user is not logged in
		 *
		 * @link  http://codex.wordpress.org/Plugin_API/Action_Reference/template_redirect
		 * @since 1.0.0
		 */
		public function redirect() {

			// Only redirect if it's a 404 page
			if ( is_404() ) {

				// Redirect home
				if ( get_theme_mod( 'error_page_redirect', false ) ) {
					wp_redirect( home_url( '/' ), 301 );
					exit();
				}

				// Custom redirect
				if ( $error_page_id = get_theme_mod( 'error_page_content_id', null ) ) {
					if ( function_exists( 'icl_object_id' ) ) {
						$error_page_id = icl_object_id( $error_page_id, 'page' );
					}
					$permalink = get_permalink( $error_page_id );
					if ( $permalink ) {
						wp_redirect( $permalink, 301 );
						exit();
					}
				}

			}

		}
	}

}
new Pluton_Custom_Error_page();