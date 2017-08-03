<?php
/**
 * Image Sizes
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
 * Pluton Image Sizes
 */
if ( ! class_exists( 'PLUTON_Panel_Image_Sizes' ) ) {
	class PLUTON_Panel_Image_Sizes {
		
		/**
		 * Start things up
		 */
		public function __construct() {

			add_action( 'image_sizes_pluton_panel', array( $this, 'pp_image_sizes' ) );

		}

		/**
		 * Add sub menu page
		 */
		public function pp_image_sizes() {
			add_submenu_page(
				'pluton-panel',
				esc_html__( 'Image Sizes', 'pluton-panel' ),
				esc_html__( 'Image Sizes', 'pluton-panel' ),
				'administrator',
				'pluton-panel-image-sizes',
				array( $this, 'create_admin_page' )
			);
		}

		/**
		 * Settings page output
		 *
		 */
		public function create_admin_page() {

			global $pluton_image_sizes ?>

			<div class="wrap">
				<h2><?php esc_html_e( 'Image Sizes', 'pluton-panel' ); ?></h2>
				<p><?php esc_html_e( 'Define the exact cropping for all the featured images on your site. Leave the width and height empty to display the full image. Set any height to "9999" or empty to disable cropping and simply resize the image to the corresponding width. All image sizes defined below will be added to the list of WordPress image sizes.', 'pluton-panel' ); ?></p>
				<form method="post" action="options.php">
					<?php settings_fields( 'pluton_image_sizes' ); ?>
					<table class="form-table">
						<tr valign="top">
							<th scope="row"><?php esc_html_e( 'Image Resizing', 'pluton-panel' ); ?></th>
							<td>
								<fieldset>
									<label>
										<input id="pluton_image_resizing" type="checkbox" name="pluton_image_sizes[image_resizing]" <?php checked( get_theme_mod( 'image_resizing', true ) ); ?>>
											<?php esc_html_e( 'Enable on the fly image cropping.', 'pluton-panel' ); ?>
											<p class="description"><?php esc_html_e( 'This theme includes an advanced "on the fly" cropping function that uses the safe and native WordPress function "wp_get_image_editor". If enabled whenever you upload a new image it will NOT be cropped into all the different sizes defined below, but rather cropped when loaded on the front-end (cropped once then saved to your uploads directory), this saving precious server space. However it may conflict with with certain CDN\'s, so you can disable if needed. If disabled you will need to "regenerate your thumbnails".', 'pluton-panel' ); ?></p>
									</label>
								</fieldset>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row"><?php esc_html_e( 'Retina', 'pluton-panel' ); ?></th>
							<td>
								<fieldset>
									<label>
										<input id="pluton_retina" type="checkbox" name="pluton_image_sizes[retina]" <?php checked( get_theme_mod( 'retina' ), true ); ?>> <?php esc_html_e( 'Enable retina support for your site (via retina.js).', 'pluton-panel' ); ?>
									</label>
								</fieldset>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row"><?php esc_html_e( 'Retina Mode', 'pluton-panel' ); ?></th>
							<?php $retina_mode = get_theme_mod( 'retina_mode', 1 ); ?>
							<td>
								<fieldset>
									<label>
										<select id="pluton_retina_mode" name="pluton_image_sizes[retina_mode]">
											<option value="1" <?php selected( $retina_mode, 1, true ); ?>><?php esc_html_e( 'Auto search for retina images', 'pluton-panel' ); ?></option>
											<option value="2" <?php selected( $retina_mode, 2, true ); ?>><?php esc_html_e( 'Require data-at2x for retina replacement', 'pluton-panel' ); ?></option>
										</select>
									</label>
								</fieldset>
							</td>
						</tr>

						<?php
						// Get sizes & crop locations
						$sizes          = $pluton_image_sizes->sizes;
						$crop_locations = array(
							''              => esc_html__( 'Default', 'pluton-panel' ),
							'left-top'      => esc_html__( 'Top Left', 'pluton-panel' ),
							'right-top'     => esc_html__( 'Top Right', 'pluton-panel' ),
							'center-top'    => esc_html__( 'Top Center', 'pluton-panel' ),
							'left-center'   => esc_html__( 'Center Left', 'pluton-panel' ),
							'right-center'  => esc_html__( 'Center Right', 'pluton-panel' ),
							'center-center' => esc_html__( 'Center Center', 'pluton-panel' ),
							'left-bottom'   => esc_html__( 'Bottom Left', 'pluton-panel' ),
							'right-bottom'  => esc_html__( 'Bottom Right', 'pluton-panel' ),
							'center-bottom' => esc_html__( 'Bottom Center', 'pluton-panel' ),
						); ?>

						<?php
						// Loop through all sizes
						foreach ( $sizes as $size => $args ) : ?>

							<?php
							// Extract args
							extract( $args );

							// Label is required
							if ( ! $label ) {
								continue;
							}

							// Define values
							$width_value  = get_theme_mod( $width );
							$height_value = get_theme_mod( $height );
							$crop_value   = get_theme_mod( $crop ); ?>

							<tr valign="top">
								<th scope="row"><?php echo strip_tags( $label ); ?></th>
								<td>
									<label for="<?php echo esc_attr( $width ); ?>"><?php esc_html_e( 'Width', 'pluton-panel' ); ?></label>
									<input name="pluton_image_sizes[<?php echo esc_attr( $width ); ?>]" type="number" step="1" min="0" value="<?php echo esc_attr( $width_value ); ?>" class="small-text" />

									<label for="<?php echo esc_attr( $height ); ?>"><?php esc_html_e( 'Height', 'pluton-panel' ); ?></label>
									<input name="pluton_image_sizes[<?php echo esc_attr( $height ); ?>]" type="number" step="1" min="0" value="<?php echo esc_attr( $height_value ); ?>" class="small-text" />
									<label for="<?php echo esc_attr( $crop ); ?>"><?php esc_html_e( 'Crop Location', 'pluton-panel' ); ?></label>

									<select name="pluton_image_sizes[<?php echo esc_attr( $crop ); ?>]">
										<?php foreach ( $crop_locations as $key => $label ) { ?>
											<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $key, $crop_value, true ); ?>><?php echo strip_tags( $label ); ?></option>
										<?php } ?>
									</select>

								</td>
							</tr>

						<?php endforeach; ?>

					</table>

					<?php submit_button(); ?>

				</form>

				<div id="pluton_regenerating_tools" style="display:none;">
					<hr />
					<p><?php esc_html_e( 'Useful Plugins:', 'pluton-panel' ); ?> <a href="https://wordpress.org/plugins/force-regenerate-thumbnails/" target="_blank"><?php esc_html_e( 'Regenerate Thumbnails', 'pluton-panel' ); ?></a> | <a href="https://wordpress.org/plugins/image-cleanup/screenshots/" target="_blank"><?php esc_html_e( 'Image Cleanup', 'pluton-panel' ); ?></a></p>
				</div><!-- #pluton_regenerating_tools -->

			</div><!-- .wrap -->

			<script>
				( function( $ ) {
					"use strict";

					// Disable and hide retina if image resizing is deleted
					var $imageResizing    = $( '#pluton_image_resizing' ),
						$imageResizingVal = $imageResizing.prop( 'checked' ),
						$retinaCheck      = $( '#pluton_retina' ),
						$retinaCheckVal   = $( '#pluton_retina' ).prop( 'checked' );

					// Check initial val
					if ( ! $imageResizingVal ) {
						$retinaCheck.attr('checked', false );
						$( '#pluton_retina' ).closest( 'tr' ).hide();
						$( '#pluton_retina_mode' ).closest( 'tr' ).hide();
						$( '#pluton_regenerating_tools' ).show();
					}

					if ( ! $retinaCheckVal ) {
						$( '#pluton_retina_mode' ).closest( 'tr' ).hide();
					}

					// Check on change
					$( $imageResizing ).change(function () {
						var $checked = $( this ).prop('checked');
						if ( $checked ) {
							$( '#pluton_retina' ).closest( 'tr' ).show();
							$( '#pluton_retina_mode' ).closest( 'tr' ).show();
							$( '#pluton_regenerating_tools' ).hide();
							$( '#pluton_retina' ).attr('checked', true );
						} else {
							$( '#pluton_retina' ).attr('checked', false );
							$( '#pluton_retina' ).closest( 'tr' ).hide();
							$( '#pluton_retina_mode' ).closest( 'tr' ).hide();
							$( '#pluton_regenerating_tools' ).show();
						}
					} );

					// Check on change
					$( $retinaCheck ).change(function () {
						var $checked = $( this ).prop( 'checked' );
						if ( $checked ) {
							$( '#pluton_retina_mode' ).closest( 'tr' ).show();
						} else {
							$( '#pluton_retina_mode' ).closest( 'tr' ).hide();
						}
					} );

				} ) ( jQuery );

			</script>
		<?php
		}

	}
}
new PLUTON_Panel_Image_Sizes();