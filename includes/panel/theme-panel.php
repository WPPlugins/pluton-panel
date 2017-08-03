<?php
/**
 * Main Theme Panel
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
if ( ! class_exists( 'Pluton_Theme_Panel' ) ) {
	class Pluton_Theme_Panel {

		/**
		 * Start things up
		 *
		 * @since 1.0.0
		 */
		public function __construct() {

			// Add panel menu
			add_action( 'admin_menu', array( 'Pluton_Theme_Panel', 'add_menu_page' ), 0 );

			// Add panel submenu
			add_action( 'admin_menu', array( 'Pluton_Theme_Panel', 'add_menu_subpage' ) );

			// Add custom CSS for the theme panel
			add_action( 'admin_print_styles-toplevel_page_pluton-panel', array( 'Pluton_Theme_Panel','css' ) );

			// Register panel settings
			add_action( 'admin_init', array( 'Pluton_Theme_Panel','register_settings' ) );

			// Load addon files
			self::load_addons();

		}

		/**
		 * Return theme addons
		 * Can't be added in construct because translations won't work
		 *
		 * @since 1.0.8
		 */
		private static function get_addons() {
			$addons = array(
				'schema_markup' => array(
					'label'     => esc_html__( 'Schema Markup', 'pluton-panel' ),
					'icon'      => 'dashicons dashicons-feedback',
					'category'  => esc_html__( 'SEO', 'pluton-panel' ),
				),
				'minify_js' => array(
					'label'    => esc_html__( 'Minify Javascript', 'pluton-panel' ),
					'icon'     => 'dashicons dashicons-performance',
					'category' => esc_html__( 'Optimizations', 'pluton-panel' ),
				),
				'custom_css' => array(
					'label'    => esc_html__( 'Custom CSS', 'pluton-panel' ),
					'icon'     => 'dashicons dashicons-admin-appearance',
					'category' => esc_html__( 'Developers', 'pluton-panel' ),
				),
				'custom_js' => array(
					'label'    => esc_html__( 'Custom JS', 'pluton-panel' ),
					'icon'     => 'dashicons dashicons-media-code',
					'category' => esc_html__( 'Developers', 'pluton-panel' ),
					'disabled' => true,
				),
				'custom_404' => array(
					'label'    => esc_html__( 'Custom 404 Page', 'pluton-panel' ),
					'icon'     => 'dashicons dashicons-dismiss',
					'category' => esc_html__( 'Core', 'pluton-panel' ),
				),
				'customizer_panel' => array(
					'label'    => esc_html__( 'Customizer Manager', 'pluton-panel' ),
					'icon'     => 'dashicons dashicons-admin-settings',
					'category' => esc_html__( 'Optimizations', 'pluton-panel' ),
				),
				'custom_wp_gallery' => array(
					'label'    => esc_html__( 'Custom WordPress Gallery', 'pluton-panel' ),
					'icon'     => 'dashicons dashicons-images-alt2',
					'category' => esc_html__( 'Core', 'pluton-panel' ),
				),
				'image_sizes' => array(
					'label'    => esc_html__( 'Image Sizes', 'pluton-panel' ),
					'icon'     => 'dashicons dashicons-image-crop',
					'category' => esc_html__( 'Core', 'pluton-panel' ),
				),
				'import_export' => array(
					'label'    => esc_html__( 'Import/Export Panel', 'pluton-panel' ),
					'icon'     => 'dashicons dashicons-admin-settings',
					'category' => esc_html__( 'Core', 'pluton-panel' ),
				),
				'disable_gs' => array(
					'disabled'  => true,
					'label'     => esc_html__( 'Remove Google Fonts', 'pluton-panel' ),
					'custom_id' => true,
					'icon'      => 'dashicons dashicons-thumbs-down',
					'category'  => esc_html__( 'Optimizations', 'pluton-panel' ),
				),
			);

			// Apply filters and return
			return apply_filters( 'pluton_theme_addons', $addons );

		}

		/**
		 * Registers a new menu page
		 *
		 * @since 1.0.0
		 */
		public static function add_menu_page() {
		  add_menu_page(
				esc_html__( 'Theme Panel', 'pluton-panel' ),
				'Theme Panel', // menu title - can't be translated because it' used for the $hook prefix
				'manage_options',
				'pluton-panel',
				'',
				'dashicons-admin-generic',
				null
			);
		}

		/**
		 * Registers a new submenu page
		 *
		 * @since 1.0.0
		 */
		public static function add_menu_subpage(){
			add_submenu_page(
				'pluton-general',
				esc_html__( 'General', 'pluton-panel' ),
				esc_html__( 'General', 'pluton-panel' ),
				'manage_options',
				'pluton-panel',
				array( 'Pluton_Theme_Panel', 'create_admin_page' )
			);
		}

		/**
		 * Register a setting and its sanitization callback.
		 *
		 * @since 1.0.0
		 */
		public static function register_settings() {
			register_setting( 'pluton_tweaks', 'pluton_tweaks', array( 'Pluton_Theme_Panel', 'admin_sanitize' ) ); 
			register_setting( 'pluton_options', 'pluton_options', array( 'Pluton_Theme_Panel', 'admin_sanitize_license_options' ) ); 
		}

		/**
		 * Validate Settings Options
		 * 
		 * @since 1.0.0
		 */
		public static function admin_sanitize_license_options( $input ) {
	
			//filter to save all settings to database
			return $input;
		}

		/**
		 * Main Sanitization callback
		 *
		 * @since 1.0.0
		 */
		public static function admin_sanitize( $options ) {

			// Check options first
			if ( ! is_array( $options ) || empty( $options ) || ( false === $options ) ) {
				return array();
			}

			// Get addons array
			$theme_addons = self::get_addons();

			// Save checkboxes
			$checkboxes = array();

			// Add theme parts to checkboxes
			foreach ( $theme_addons as $key => $val ) {

				// Get correct ID
				$id = isset( $val['custom_id'] ) ? $key : $key .'_enable';

				// No need to save items that are enabled by default unless they have been disabled
				$default = isset ( $val['disabled'] ) ? false : true;

				// If default is true
				if ( $default ) {
					if ( ! isset( $options[$id] ) ) {
						set_theme_mod( $id, 0 ); // Disable option that is enabled by default
					} else {
						remove_theme_mod( $id ); // Make sure its not in the theme_mods since it's already enabled
					}
				}

				// If default is false
				elseif ( ! $default ) {
					if ( isset( $options[$id] ) ) {
						set_theme_mod( $id, 1 ); // Enable option that is disabled by default
					} else {
						remove_theme_mod( $id ); // Remove theme mod because it's disabled by default
					}
				}


			}

			// Remove thememods for checkboxes not in array
			foreach ( $checkboxes as $checkbox ) {
				if ( isset( $options[$checkbox] ) ) {
					set_theme_mod( $checkbox, 1 );
				} else {
					set_theme_mod( $checkbox, 0 );
				}
			}

			// No need to save in options table
			$options = '';
			return $options;

		}

		/**
		 * Settings page output
		 *
		 * @since 1.0.0
		 */
		public static function create_admin_page() {

			// Get addons array
			$theme_addons = self::get_addons(); ?>

			<div class="wrap pluton-theme-panel pluton-clr">

				<h1><?php esc_attr_e( 'Theme Panel', 'pluton-panel' ); ?></h1>

				<h2 class="nav-tab-wrapper">
					<?php
					//Get current tab
					$curr_tab	= !empty( $_GET['tab'] ) ? $_GET['tab'] : 'features';

					// Feature url
					$feature_url = add_query_arg(
						array(
							'page' 	=> 'pluton-panel',
							'tab' 	=> 'features',
						),
						'admin.php'
					);

					// License url
					$license_url = add_query_arg(
						array(
							'page' 	=> 'pluton-panel',
							'tab' 	=> 'license',
						),
						'admin.php'
					);

					// Customizer url
					$customize_url = add_query_arg(
						array(
							'return' => urlencode( wp_unslash( $_SERVER['REQUEST_URI'] ) ),
						),
						'customize.php'
					); ?>

					<a href="<?php echo esc_url( $feature_url ); ?>" class="nav-tab <?php echo $curr_tab == 'features' ? 'nav-tab-active' : '';?>"><?php esc_attr_e( 'Features', 'pluton-panel' ); ?></a>

					<a href="<?php echo esc_url( $customize_url ); ?>" class="nav-tab"><?php esc_attr_e( 'Customize', 'pluton-panel' ); ?></a>

					<?php if ( apply_filters( 'pluton_licence_tab_enable', false ) ) { ?>
						<a href="<?php echo esc_url( $license_url ); ?>" class="nav-tab <?php echo $curr_tab == 'license' ? 'nav-tab-active' : '';?>"><?php esc_attr_e( 'Licenses', 'pluton-panel' ); ?></a>
					<?php } ?>
				</h2>

				<div class="pluton-theme-panel-updated updated" style="border-color: #f0821e;display:none;">
					<p>
						<?php echo pluton_sanitize_data( __( 'Don\'t forget to <a href="#">save your changes</a>', 'pluton-panel' ), 'html' ); ?>
					</p>
				</div>

				<form id="pluton-license-form" method="post" action="options.php" <?php echo $curr_tab == 'license' ? '' : 'style="display:none;"';?>>

					<?php settings_fields( 'pluton_options' ); ?>

						<div id="pluton-licenses" class="post-box-container">
							<div class="metabox-holder">	
								<div class="meta-box-sortables ui-sortable">
									<div id="License" class="postbox">	
										<div class="handlediv" title="<?php esc_attr_e( 'Click to toggle', 'pluton-panel' ); ?>"><br /></div>

										<!-- licenses settings box title -->
										<h3 class="hndle">
											<span style="vertical-align: top;"><?php esc_attr_e( 'Licenses Settings', 'pluton-panel' ); ?></span>
										</h3>

										<div class="inside">
											<?php do_action( 'pluton_licenses_tab_top' ); ?>
											<table class="form-table">
												<tbody>
													<?php do_action( 'pluton_licenses_tab_fields' ); ?>
													<tr>
														<td colspan="2" valign="top" scope="row">
															<p class="submit"><input type="submit" value="<?php _e( 'Save Changes', 'pluton-panel' )?>" class="button button-primary" id="submit" name="pluton_licensekey_activateall"></p>
														</td>
													</tr>
												</tbody>
											 </table>
										</div><!-- .inside -->
									</div><!-- #License -->
								</div><!-- .meta-box-sortables ui-sortable -->
							</div><!-- .metabox-holder -->
						</div><!-- #pluton-licenses -->
				</form>

				<form id="pluton-theme-panel-form" method="post" action="options.php" <?php echo $curr_tab == 'features' ? '' : 'style="display:none;"';?>>

					<?php settings_fields( 'pluton_tweaks' ); ?>

					<div class="manage-right">

						<!-- View -->
						<h4><?php esc_attr_e( 'View', 'pluton-panel' ); ?></h4>
						<div class="button-group pluton-filter-active">
							<button type="button" class="button active"><?php esc_attr_e( 'All', 'pluton-panel' ); ?></button>
							<button type="button" class="button" data-filter-by="active"><?php esc_attr_e( 'Active', 'pluton-panel' ); ?></button>
							<button type="button" class="button" data-filter-by="inactive"><?php esc_attr_e( 'Inactive', 'pluton-panel' ); ?></button>
						</div>

						<!-- Sort -->
						<h4><?php esc_attr_e( 'Sort', 'pluton-panel' ); ?></h4>
						<?php
						// Categories
						$categories = wp_list_pluck( $theme_addons, 'category' );
						$categories = array_unique( $categories );
						asort( $categories ); ?>
						<ul class="pluton-theme-panel-sort">
							<li><a href="#" data-category="all" class="pluton-active-category"><?php esc_attr_e( 'All', 'pluton-panel' ); ?></a></li>
							<?php
							// Loop through cats
							foreach ( $categories as $key => $category ) :

								// Check condition
								$display = true;
								if ( isset( $theme_addons[$key]['condition'] ) ) {
									$display = $theme_addons[$key]['condition'];
								}

								// Show cat
								if ( $display ) {
									$sanitize_category = strtolower( str_replace( ' ', '_', $category ) ); ?>
									<li><a href="#" data-category="<?php echo esc_attr( $sanitize_category ); ?>" title="<?php echo esc_attr( $category ); ?>"><?php echo strip_tags( $category ); ?></a></li>
								<?php } ?>

							<?php endforeach; ?>
						</ul>

					</div><!-- manage-right -->

					<div class="manage-left">

						<table class="table table-bordered wp-list-table widefat fixed pluton-modules">

							<tbody id="the-list">

								<?php
								$count = 0;
								// Loop through theme pars and add checkboxes
								foreach ( $theme_addons as $key => $val ) :
									$count++;

									// Display setting?
									$display = true;
									if ( isset( $val['condition'] ) ) {
										$display = $val['condition'];
									}

									// Sanitize vars
									$default = isset ( $val['disabled'] ) ? false : true;
									$label   = isset ( $val['label'] ) ? $val['label'] : '';
									$icon    = isset ( $val['icon'] ) ? $val['icon'] : '';

									// Label
									if ( $icon ) {
										$label = '<i class="'. $icon .'"></i>'. $label;
									}

									// Set id
									if ( isset( $val['custom_id'] ) ) {
										$key = $key;
									} else {
										$key = $key .'_enable';
									}

									// Get theme option
									$theme_mod = get_theme_mod( $key, $default );

									// Get category and sanitize
									$category = isset( $val['category'] ) ? $val['category'] : ' other';
									$category = strtolower( str_replace( ' ', '_', $category ) );

									// Sanitize category
									$category = strtolower( str_replace( ' ', '_', $category ) );

									// Classes
									$classes = 'pluton-module';
									$classes .= $theme_mod ? ' pluton-active' : ' pluton-disabled';
									$classes .= ! $display ? ' pluton-hidden' : '';
									$classes .= ' pluton-category-'. $category;
									if ( $count = 2 ) {
										$classes .= ' alternative';
										$count = 0;
									} ?>

									<tr id="<?php echo esc_attr( $key ); ?>" class="<?php echo esc_attr( $classes ); ?>">

										<th scope="row" class="check-column">
											<input type="checkbox" name="pluton_tweaks[<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( $theme_mod ); ?>" <?php checked( $theme_mod, true ); ?> class="pluton-checkbox">
										</th>

										<td class="name column-name">
											<span class="info"><a href="#<?php echo esc_attr( $key ); ?>" class="pluton-theme-panel-module-link"><?php echo pluton_sanitize_data( $label, 'html' ); ?></a></span>
											<?php if ( isset( $val['desc'] ) ) { ?>
												<div class="pluton-module-description">
													<small><?php echo pluton_sanitize_data( $val['desc'], 'html' ); ?></small>
												</div>
											<?php } ?>
										</td>

									</tr>

								<?php endforeach; ?>

							</tbody>

						</table>

						<?php submit_button(); ?>

					</div><!-- .manage-left -->

				</form>

			</div>

			<script>
				( function( $ ) {
					"use strict";
					$( document ).ready( function() {
						// Show notice
						$( '.pluton-checkbox' ).click( function() {
							$( '.pluton-theme-panel-updated' ).show();
						} );
						$( '.pluton-theme-panel .manage-right input[type="text"]' ).change( function() {
							$( '.pluton-theme-panel-updated' ).show();
						} );
						// Save on link click
						$( '.pluton-theme-panel-updated a' ).click( function( e ) {
							e.preventDefault();
							$( "#pluton-theme-panel-form #submit" ).click();
						} );
						// Module on click
						$( '.pluton-theme-panel-module-link' ).click( function() {
							$( '.pluton-theme-panel-updated' ).show();
							var $ref = $(this).attr( 'href' ),
								$checkbox = $($ref).find( '.pluton-checkbox' );
							if ( $checkbox.is( ":checked" ) ) {
								$checkbox.attr( 'checked', false );
							} else {
								$checkbox.attr( 'checked', true );
							}
							return false;
						} );
						// Filter
						var $filter_buttons = $( '.pluton-filter-active button' );
						$filter_buttons.click( function() {
							var $filterBy = $( this ).data( 'filter-by' );
							$filter_buttons.removeClass( 'active' );
							$( this ).addClass( 'active' );
							$( '.pluton-module' ).removeClass( 'pluton-filterby-hide' );
							if ( 'active' == $filterBy ) {
								$( '.pluton-module' ).each( function() {
									if ( $( this ).hasClass( 'pluton-disabled' ) ) {
										$( this ).addClass( 'pluton-filterby-hide' );
									}
								} );
							} else if ( 'inactive' == $filterBy ) {
								$( '.pluton-module' ).each( function() {
									if ( ! $( this ).hasClass( 'pluton-disabled' ) ) {
										$( this ).addClass( 'pluton-filterby-hide' );
									}
								} );
							}
							return false;
						} );
						// Sort
						$( '.pluton-theme-panel-sort a' ).click( function() {
							var $data = $( this ).data( 'category' );
							$( '.pluton-theme-panel-sort a' ).removeClass( 'pluton-active-category' );
							$( this ).addClass( 'pluton-active-category' );
							if ( 'all' == $data ) {
								$( '.pluton-module' ).removeClass( 'pluton-sort-hide' );
							} else {
								$( '.pluton-module' ).addClass( 'pluton-sort-hide' );
								$( '.pluton-category-'+ $data ).each( function() {
									$( this ).removeClass( 'pluton-sort-hide' );
								} );
							}
							return false;
						} );
					} );
				} ) ( jQuery );
			</script>

		<?php
		}

		/**
		 * Include addons
		 *
		 * @since 1.0.0
		 */
		private function load_addons() {

			// Addons directory location
			$dir = PP_ROOT .'/includes/panel/';

			// Image Sizes
			if ( get_theme_mod( 'image_sizes_enable', true ) ) {
				require_once( $dir .'image-sizes.php' );
			}

			// Custom 404
			if ( get_theme_mod( 'custom_404_enable', true ) ) {
				require_once( $dir .'custom-404.php' );
			}

			// Customizer Manager
			if ( get_theme_mod( 'customizer_panel_enable', true ) ) {
				require_once( $dir .'customizer.php' );
			}

			// Custom WordPress gallery output
			if ( get_theme_mod( 'custom_wp_gallery_enable', true ) ) {
				require_once( $dir .'wp-gallery.php' );
			}

			// Custom CSS
			if ( get_theme_mod( 'custom_css_enable', true ) ) {
				require_once( $dir .'custom-css.php' );
			}

			// Custom JS
			if ( get_theme_mod( 'custom_js_enable', false ) ) {
				require_once( $dir .'custom-js.php' );
			}

			// Import Export Functions
			if ( is_admin() && get_theme_mod( 'import_export_enable', true ) ) {
				require_once( $dir .'import-export.php' );
			}

			// Links
			if ( current_user_can( 'manage_options' ) ) {
				require_once( $dir .'links.php' );
			}

			// Extensions
			require_once( $dir .'extensions.php' );

		}

		/**
		 * Theme panel CSS
		 *
		 * @since 1.0.0
		 */
		public static function css() { ?>
		
			<style type="text/css">

				.wrap.pluton-theme-panel h1 { margin-bottom: 10px; }
				.pluton-theme-panel .nav-tab-wrapper { margin-bottom: 20px; }
				.pluton-clr:after { content: ""; display: block; height: 0; clear: both; visibility: hidden; zoom: 1; }
				.pluton-filterby-hide { display: none; }
				.pluton-sort-hide { display: none; }

				/* Right */
				.pluton-theme-panel .manage-right { clear: both; margin: 0; padding: 0; float: right; width: 35%; }
				.pluton-theme-panel .manage-right { font-size: 12px; font-weight: bold; color: #bbb; text-transform: uppercase; letter-spacing: 1px; }
				.pluton-theme-panel .manage-right input[type="text"] { width: 100%; padding: 7px; font-weight: 600; }

				.pluton-filter-active button { font-weight: 400; text-transform: none; letter-spacing: normal; }

				.pluton-theme-panel-sort a { font-weight: 600; text-decoration: none; text-transform: none; letter-spacing: 0; }
				.pluton-theme-panel-sort a.pluton-active-category { padding: 1px 5px; border-radius: 2px; margin-left: -5px; background: #0D72B2; color: #fff; }

				/* Left */
				.pluton-theme-panel .manage-left { float: left; margin: 0; padding: 0; width: 63%; }
				.pluton-theme-panel .manage-left td,
				.pluton-theme-panel .manage-left th { box-shadow: inset 0 -1px 0 rgba(0,0,0,0.1); }
				.pluton-theme-panel .manage-left td { padding: 10px 10px 8px 10px; line-height: 25px; }
				.pluton-theme-panel .manage-left td .dashicons { margin-right: 10px; }
				.pluton-module-description { line-height: 1.5; padding-left: 30px; }
				.pluton-module.pluton-hidden { display: none !important; }

				tr.pluton-active { }
				tr.pluton-disabled { opacity: 0.5; }
				tr.pluton-disabled:hover { opacity: 1; }

				.admin-color-fresh .pluton-module a { color: #1a8dba; }
				.admin-color-midnight .pluton-module a { color: #dd382d; }
				.admin-color-light .pluton-module a { color: #777; }
				.admin-color-blue .pluton-module a { color: #096484; }
				.admin-color-coffee .pluton-module a { color: #59524c; }
				.admin-color-ectoplasm .pluton-module a { color: #523f6d; }
				.admin-color-ocean .pluton-module a { color: #6c976f; }
				.admin-color-sunrise .pluton-module a { color: #dd823b; }

				.admin-color-fresh .pluton-module:hover,
				.admin-color-fresh .pluton-module:focus { background: #f8fcfe; }

				.admin-color-coffee .pluton-module:hover,
				.admin-color-coffee .pluton-module:focus { background: #fcfcfb; }

				.admin-color-ectoplasm .pluton-module:hover,
				.admin-color-ectoplasm .pluton-module:focus { background: #fff; }

				.admin-color-ocean .pluton-module:hover,
				.admin-color-ocean .pluton-module:focus { background: #fbfcfc; }

				.admin-color-sunrise .pluton-module:hover,
				.admin-color-sunrise .pluton-module:focus { background: #fefdfc; }

				@media only screen and (max-width: 800px) { 
					.pluton-theme-panel .manage-right,
					.pluton-theme-panel .manage-left { width: 100%; float: none; }
				}

			</style>

		<?php }

	}
}
new Pluton_Theme_Panel();