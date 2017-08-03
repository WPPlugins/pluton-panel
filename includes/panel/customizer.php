<?php
/**
 * Customizer Manager
 *
 * @package Pluton_Panel
 * @category Core
 * @author Nick
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Pluton Customizer Manager
 */
if ( ! class_exists( 'PLUTON_Customizer_Manager' ) ) {
	class PLUTON_Customizer_Manager {
		
		/**
		 * Start things up
		 */
		public function __construct() {

			add_action( 'customizer_manager_pluton_panel', array( $this, 'pp_customizer_manager' ) );

		}

		/**
		 * Add sub menu page
		 */
		public function pp_customizer_manager() {
			add_submenu_page(
				'pluton-panel',
				esc_html__( 'Customizer Manager', 'pluton-panel' ),
				esc_html__( 'Customizer Manager', 'pluton-panel' ),
				'administrator',
				'pluton-panel-customizer',
				array( $this, 'create_admin_page' )
			);
		}

		/**
		 * Settings page output
		 *
		 */
		public function create_admin_page() {

			global $pluton_customizer ?>

			<div class="wrap">

				<h2><?php esc_html_e( 'Customizer Manager', 'pluton' ); ?></h2>
				<p style="max-width:70%;"><?php esc_html_e( 'It\'s best to disable the Customizer panels you\'re not currently changing or won\'t need to change anymore to speed things up. Your settings will still be set, so don\'t worry about them being reverted to their defaults.', 'pluton' ); ?></p>

				<?php
				// Customizer url
				$customize_url = add_query_arg( array(
					'return'                => urlencode( wp_unslash( $_SERVER['REQUEST_URI'] ) ),
					'pluton_theme_customizer' => 'true',
				), 'customize.php' ); ?>

				<h2 class="nav-tab-wrapper">
					<a href="#" class="nav-tab nav-tab-active"><?php esc_html_e( 'Enable Panels', 'pluton' ); ?></a>
					<a href="<?php echo esc_url( $customize_url ); ?>"  class="nav-tab"><?php esc_html_e( 'Customizer', 'pluton' ); ?><span class="dashicons dashicons-external" style="padding-left:7px;"></span></a>
				</h2>

				<div style="margin-top:20px;">
					<a href="#" class="pluton-customizer-check-all"><?php esc_html_e( 'Check all', 'pluton' ); ?></a> | <a href="#" class="pluton-customizer-uncheck-all"><?php esc_html_e( 'Uncheck all', 'pluton' ); ?></a>
				</div>

				<form method="post" action="options.php">

					<?php settings_fields( 'pluton_customizer_editor' ); ?>

					<table class="form-table pluton-customizer-editor-table">
						<?php
						// Get panels
						$panels = $pluton_customizer->get_panels();

						// Get options and set defaults
						$options = get_option( 'pluton_customizer_panels', $panels );

						// Loop through panels and add checkbox
						foreach ( $panels as $id => $val ) {

							// Parse panel data
							$title     = isset( $val['title'] ) ? $val['title'] : $val;
							$condition = isset( $val['condition'] ) ? $val['condition'] : true;

							// Get option
							$option = isset( $options[$id] ) ? 'on' : false;

							// Display option if condition is met
							if ( $condition ) { ?>

								<tr valign="top">
									<th scope="row"><?php echo $title; ?></th>
									<td>
										<fieldset>
											<input class="pluton-customizer-editor-checkbox" type="checkbox" name="pluton_customizer_panels[<?php echo $id; ?>]"<?php checked( $option, 'on' ); ?>>
										</fieldset>
									</td>
								</tr>

							<?php }

							// Condition isn't met so add it as a hidden item
							else { ?>

								<input type="hidden" name="pluton_customizer_panels[<?php echo $id; ?>]"<?php checked( $option, 'on' ); ?>>	

							<?php } ?>

						<?php } ?>

					</table>

					<?php submit_button(); ?>

				</form>

			</div><!-- .wrap -->

			<script>
				(function($) {
					"use strict";
						$( '.pluton-customizer-check-all' ).click( function() {
							$('.pluton-customizer-editor-checkbox').each( function() {
								this.checked = true;
							} );
							return false;
						} );
						$( '.pluton-customizer-uncheck-all' ).click( function() {
							$('.pluton-customizer-editor-checkbox').each( function() {
								this.checked = false;
							} );
							return false;
						} );
				} ) ( jQuery );
			</script>

		<?php } // END create_admin_page()

	}
}

// Start up class and set to global var
$pluton_customizer_manager = new PLUTON_Customizer_Manager();