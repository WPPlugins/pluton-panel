<?php
/**
 * Creates the admin panel for the customizer
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
if ( ! class_exists( 'Pluton_Import_Export' ) ) {
	class Pluton_Import_Export {

		/**
		 * Start things up
		 *
		 * @since 1.0.0
		 */
		public function __construct() {
			add_action( 'admin_menu', array( $this, 'add_page' ), 50 );
			add_action( 'admin_init', array( $this,'register_settings' ) );
			add_action( 'admin_notices', array( $this, 'notices' ) );
		}

		/**
		 * Add sub menu page
		 *
		 * @since 1.0.0
		 */
		public function add_page() {
			add_submenu_page(
				'pluton-panel',
				esc_attr__( 'Import/Export', 'pluton-panel' ), 
				esc_attr__( 'Import/Export', 'pluton-panel' ),
				'manage_options',
				'pluton-panel-import-export',
				array( $this, 'create_admin_page' )
			);
		}

		/**
		 * Register a setting and its sanitization callback.
		 *
		 * @since 1.0.0
		 */
		public function register_settings() {
			register_setting(
				'pluton_customizer_options',
				'pluton_customizer_options',
				array( $this, 'sanitize' )
			);
		}

		/**
		 * Displays all messages registered to 'pluton-customizer-notices'
		 *
		 * @since 1.0.0
		 */
		public static function notices() {
			settings_errors( 'pluton-customizer-notices' );
		}

		/**
		 * Sanitization callback
		 *
		 * @since 1.0.0
		 */
		public static function sanitize( $options ) {

			// Import the imported options
			if ( $options ) {

				// Delete options if import set to -1
				if ( '-1' == $options['reset'] ) {

					// Get menu locations
					$locations 	= get_theme_mod( 'nav_menu_locations' );
					$save_menus	= array();

					if ( $locations ) {

						foreach( $locations as $key => $val ) {

							$save_menus[$key] = $val;
						}

					}

					// Get sidebars
					$widget_areas = get_theme_mod( 'widget_areas' );

					// Remove all mods
					remove_theme_mods();

					// Remove CSS cache
					delete_option( 'pluton_customizer_inline_css_cache' );

					// Re-add the menus
					set_theme_mod( 'nav_menu_locations', array_map( 'absint', $save_menus ) );
					set_theme_mod( 'widget_areas', $widget_areas );

					// Error messages
					$error_msg	= esc_attr__( 'All settings have been reset.', 'pluton-panel' );
					$error_type	= 'updated';

				}
				// Set theme mods based on json data
				elseif( ! empty( $options['import'] ) ) {

					// Decode input data
					$theme_mods = json_decode( $options['import'], true );

					// Validate json file then set new theme options
					if ( function_exists( 'json_last_error' ) ) {

						if ( '0' == json_last_error() ) {

							// Delete CSS cache
							delete_option( 'pluton_customizer_inline_css_cache' );

							// Loop through mods and add them
							foreach ( $theme_mods as $theme_mod => $value ) {
								set_theme_mod( $theme_mod, $value );
							}

							// Success message
							$error_msg  = esc_attr__( 'Settings imported successfully.', 'pluton-panel' );
							$error_type = 'updated';

						}

						// Display invalid json data error
						else {

							$error_msg  = esc_attr__( 'Invalid Import Data.', 'pluton-panel' );
							$error_type = 'error';

						}

					}
				}

				// No json data entered
				else {
					$error_msg = esc_attr__( 'No import data found.', 'pluton-panel' );
					$error_type = 'error';
				}

				// Make sure the settings data is reset! 
				$options = array(
					'import'	=> '',
					'reset'		=> '',
				);

			}

			// Display message
			add_settings_error(
				'pluton-customizer-notices',
				esc_attr( 'settings_updated' ),
				$error_msg,
				$error_type
			);

			// Return options
			return $options;

		}

		/**
		 * Settings page output
		 *
		 * @since 1.0.0
		 */
		public static function create_admin_page() { ?>

			<div class="wrap">

			<h2><?php esc_html_e( 'Import, Export or Reset Theme Settings', 'pluton-panel' ); ?></h2>

			<p><?php esc_html_e( 'This will export/import/delete ALL theme_mods that means if other plugins are adding settings in the Customizer it will export/import/delete those as well.', 'pluton-panel' ); ?></p>

			<?php
			// Default options
			$options = array(
				'import' => '',
				'reset'  => '',
			); ?>

			<form method="post" action="options.php">

				<?php
				// Output nonce, action, and option_page fields for a settings page
				$options = get_option( 'pluton_customizer_options', $options );
				settings_fields( 'pluton_customizer_options' ); ?>

				<table class="form-table">

					<tr valign="top">

						<th scope="row"><?php esc_html_e( 'Export Settings', 'pluton-panel' ); ?></th>

						<td>
							<?php
							// Get an array of all the theme mods
							if ( $theme_mods = get_theme_mods() ) {
								$mods = array();
								foreach ( $theme_mods as $theme_mod => $value ) {
									$mods[$theme_mod] = maybe_unserialize( $value );
								}
								$json = json_encode( $mods );
								$disabled = '';
							} else {
								$json     = esc_attr__( 'No Settings Found', 'pluton-panel' );
								$disabled = 'disabled';
							}
							echo '<textarea rows="10" cols="50" readonly id="pluton-customizer-export" style="width:100%;">' . $json . '</textarea>'; ?>
							<p class="submit">
								<a href="#" class="button-primary pluton-highlight-options <?php echo esc_attr( $disabled ); ?>"><?php esc_html_e( 'Highlight Options', 'pluton-panel' ); ?></a>
							</p>
						</td>
					</tr>

					<tr valign="top">
						<th scope="row"><?php esc_html_e( 'Import Settings', 'pluton-panel' ); ?></th>
						<td>
							<textarea name="pluton_customizer_options[import]" rows="10" cols="50" style="width:100%;"><?php echo stripslashes( $options['import'] ); ?></textarea>
							<input id="pluton-reset-hidden" name="pluton_customizer_options[reset]" type="hidden" value=""></input>
							<p class="submit">
								<input type="submit" class="button-primary pluton-submit-form" value="<?php esc_attr_e( 'Import Options', 'pluton-panel' ) ?>" />
								<a href="#" class="button-secondary pluton-delete-options"><?php esc_html_e( 'Reset Options', 'pluton-panel' ); ?></a>
								<a href="#" class="button-secondary pluton-cancel-delete-options" style="display:none;"><?php esc_html_e( 'Cancel Reset', 'pluton-panel' ); ?></a>
							</p>
							<div class="pluton-delete-options-warning error inline" style="display:none;">
								<p style="margin:.5em 0;"><?php esc_attr_e( 'Always make sure you have a backup of your settings before resetting, just incase! Your menu locations and widget areas will not reset and will remain intact. All customizer and addon settings will reset.', 'pluton-panel' ); ?></p>
							</div>
						</td>
					</tr>
				</table>
			</form>

			<script>
				(function($) {
					"use strict";
						$( '.pluton-highlight-options' ).click( function() {
							$( '#pluton-customizer-export' ).focus().select();
							return false;
						} );
						$( '.pluton-delete-options' ).click( function() {
							$(this).hide();
							$( '.pluton-delete-options-warning, .pluton-cancel-delete-options' ).show();
							$( '.pluton-submit-form' ).val( "<?php echo esc_js( __( 'Confirm Reset', 'pluton-panel' ) ); ?>" );
							$( '#pluton-reset-hidden' ).val( '-1' );
							return false;
						} );
						$( '.pluton-cancel-delete-options' ).click( function() {
							$(this).hide();
							$( '.pluton-delete-options-warning' ).hide();
							$( '.pluton-delete-options' ).show();
							$( '.pluton-submit-form' ).val( "<?php echo esc_js( __( 'Import Options', 'pluton-panel' ) ); ?>" );
							$( '#pluton-reset-hidden' ).val( '' );
							return false;
						} );
				} ) ( jQuery );
			</script>
			</div>
		<?php }

	}
}
new Pluton_Import_Export();