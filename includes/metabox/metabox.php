<?php
/**
 * Adds custom metaboxes
 *
 * @package Pluton_Panel
 * @category Core
 * @author Nick
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// The Metabox class
class Pluton_Post_Metaboxes {
	private $post_types;

	/**
	 * Register this class with the WordPress API
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// Post types to add the metabox to
		$this->post_types = apply_filters( 'pluton_main_metaboxes_post_types', array(
			'post'         => 'post',
			'page'         => 'page',
			'product'      => 'product',
		) );

		// Add metabox to corresponding post types
		foreach( $this->post_types as $key => $val ) {
			add_action( 'add_meta_boxes_'. $val, array( $this, 'post_meta' ), 11 );
		}

		// Save meta
		add_action( 'save_post', array( $this, 'save_meta_data' ) );

		// Load scripts for the metabox
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

		// Load custom css for metabox
		add_action( 'admin_print_styles-post.php', array( $this, 'metaboxes_css' ) );
		add_action( 'admin_print_styles-post-new.php', array( $this, 'metaboxes_css' ) );

		// Load custom js for metabox
		add_action( 'admin_footer-post.php', array( $this, 'metaboxes_js' ) );
		add_action( 'admin_footer-post-new.php', array( $this, 'metaboxes_js' ) );

	}

	/**
	 * The function responsible for creating the actual meta box.
	 *
	 * @since 1.0.0
	 */
	public function post_meta( $post ) {

		// Add metabox
		$obj = get_post_type_object( $post->post_type );
		add_meta_box(
			'pluton-metabox',
			$obj->labels->singular_name . ' '. esc_html__( 'Settings', 'pluton-panel' ),
			array( $this, 'display_meta_box' ),
			$post->post_type,
			'normal',
			'high'
		);

	}

	/**
	 * Enqueue scripts and styles needed for the metaboxes
	 *
	 * @since 1.0.0
	 */
	public static function admin_enqueue_scripts() {
		wp_enqueue_media();
		wp_enqueue_script( 'jquery' );
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );
	}

	/**
	 * Renders the content of the meta box.
	 *
	 * @since 1.0.0
	 */
	public function display_meta_box( $post ) {

		// Add an nonce field so we can check for it later.
		wp_nonce_field( 'pluton_metabox', 'pluton_metabox_nonce' );

		// Get current post data
		$post_id   = $post->ID;
		$post_type = get_post_type();

		// Get tabs
		$tabs = $this->tabs_array();

		// Make sure tabs aren't empty
		if ( empty( $tabs ) ) {
			echo '<p>Hey your settings are empty, something is going on please contact your webmaster</p>';
			return;
		}

		// Store tabs that should display on this specific page in an array for use later
		$active_tabs = array();
		foreach ( $tabs as $tab ) {
			$tab_post_type = isset( $tab['post_type'] ) ? $tab['post_type'] : '';
			if ( ! $tab_post_type ) {
				$display_tab = true;
			} elseif ( in_array( $post_type, $tab_post_type ) ) {
				$display_tab = true;
			} else {
				$display_tab = false;
			}
			if ( $display_tab ) {
				$active_tabs[] = $tab;
			}
		} ?>

		<ul class="wp-tab-bar">
			<?php
			// Output tab links
			$pluton_count = '';
			foreach ( $active_tabs as $tab ) {
				$pluton_count++;
				// Define tab title
				$tab_title = $tab['title'] ? $tab['title'] : esc_html__( 'Other', 'pluton-panel' ); ?>
				<li<?php if ( '1' == $pluton_count ) echo ' class="wp-tab-active"'; ?>>
					<a href="javascript:;" data-tab="#pluton-mb-tab-<?php echo $pluton_count; ?>"><?php echo $tab_title; ?></a>
				</li>
			<?php } ?>
		</ul><!-- .pluton-mb-tabnav -->

		<?php
		// Output tab sections
		$pluton_count = '';
		foreach ( $active_tabs as $tab ) {
			$pluton_count++; ?>
			<div id="pluton-mb-tab-<?php echo $pluton_count; ?>" class="wp-tab-panel clr">
				<table class="form-table">
					<?php
					// Loop through sections and store meta output
					foreach ( $tab['settings'] as $setting ) {

						// Vars
						$meta_id     = $setting['id'];
						$title       = $setting['title'];
						$hidden      = isset ( $setting['hidden'] ) ? $setting['hidden'] : false;
						$type        = isset ( $setting['type'] ) ? $setting['type'] : 'text';
						$default     = isset ( $setting['default'] ) ? $setting['default'] : '';
						$description = isset ( $setting['description'] ) ? $setting['description'] : '';
						$meta_value  = get_post_meta( $post_id, $meta_id, true );
						$meta_value  = $meta_value ? $meta_value : $default; ?>

						<tr<?php if ( $hidden ) echo ' style="display:none;"'; ?> id="<?php echo $meta_id; ?>_tr">
							<th>
								<label for="pluton_main_layout"><strong><?php echo $title; ?></strong></label>
								<?php
								// Display field description
								if ( $description ) { ?>
									<p class="pluton-mb-description"><?php echo $description; ?></p>
								<?php } ?>
							</th>

							<?php
							// Text Field
							if ( 'text' == $type ) { ?>

								<td><input name="<?php echo $meta_id; ?>" type="text" value="<?php echo $meta_value; ?>"></td>

							<?php }

							// Number Field
							if ( 'number' == $type ) { ?>

								<td><input name="<?php echo $meta_id; ?>" type="number" value="<?php echo $meta_value; ?>"></td>

							<?php }

							// HTML Text
							if ( 'text_html' == $type ) { ?>

								<td><input name="<?php echo $meta_id; ?>" type="text" value="<?php echo esc_html( $meta_value ); ?>"></td>

							<?php }

							// Link field
							elseif ( 'link' == $type ) { ?>

								<td><input name="<?php echo $meta_id; ?>" type="text" value="<?php echo esc_url( $meta_value ); ?>"></td>

							<?php }

							// Textarea Field
							elseif ( 'textarea' == $type ) {
								$rows = isset ( $setting['rows'] ) ? $setting['rows'] : '4';?>

								<td>
									<textarea rows="<?php echo $rows; ?>" cols="1" name="<?php echo $meta_id; ?>" type="text" class="pluton-mb-textarea"><?php echo $meta_value; ?></textarea>
								</td>

							<?php }

							// Code Field
							elseif ( 'code' == $type ) { ?>

								<td>
									<textarea rows="1" cols="1" name="<?php echo $meta_id; ?>" type="text" class="pluton-mb-textarea-code"><?php echo $meta_value; ?></textarea>
								</td>

							<?php }

							// Checkbox
							elseif ( 'checkbox' == $type ) {

								$meta_value = ( 'on' == $meta_value ) ? false : true; ?>
								<td><input name="<?php echo $meta_id; ?>" type="checkbox" <?php checked( $meta_value, true, true ); ?>></td>

							<?php }

							// Select
							elseif ( 'select' == $type ) {

								$options = isset ( $setting['options'] ) ? $setting['options'] : '';
								if ( ! empty( $options ) ) { ?>
									<td><select id="<?php echo $meta_id; ?>" name="<?php echo $meta_id; ?>">
									<?php foreach ( $options as $option_value => $option_name ) { ?>
										<option value="<?php echo $option_value; ?>" <?php selected( $meta_value, $option_value, true ); ?>><?php echo $option_name; ?></option>
									<?php } ?>
									</select></td>
								<?php }

							}

							// Select
							elseif ( 'color' == $type ) { ?>

								<td><input name="<?php echo $meta_id; ?>" type="text" value="<?php echo $meta_value; ?>" class="pluton-mb-color-field"></td>

							<?php }

							// Media
							elseif ( 'media' == $type ) {

								// Validate data if array
								if ( is_array( $meta_value ) ) {
									if ( ! empty( $meta_value['url'] ) ) {
										$meta_value = $meta_value['url'];
									} else {
										$meta_value = '';
									}
								} ?>
								<td>
									<div class="uploader">
										<input type="text" name="<?php echo $meta_id; ?>" value="<?php echo $meta_value; ?>">
										<input class="pluton-mb-uploader button-secondary" name="<?php echo $meta_id; ?>" type="button" value="<?php esc_html_e( 'Upload', 'pluton-panel' ); ?>" />
									</div>
								</td>

							<?php }

							// Editor
							elseif ( 'editor' == $type ) {
								$teeny= isset( $setting['teeny'] ) ? $setting['teeny'] : false;
								$rows = isset( $setting['rows'] ) ? $setting['rows'] : '10';
								$media_buttons= isset( $setting['media_buttons'] ) ? $setting['media_buttons'] : true; ?>
								<td><?php wp_editor( $meta_value, $meta_id, array(
									'textarea_name' => $meta_id,
									'teeny' => $teeny,
									'textarea_rows' => $rows,
									'media_buttons' => $media_buttons,
								) ); ?></td>
							<?php } ?>
						</tr>

					<?php } ?>
				</table>
			</div>
		<?php } ?>

		<div class="pluton-mb-reset">
			<a class="button button-secondary pluton-reset-btn"><?php esc_html_e( 'Reset Settings', 'pluton-panel' ); ?></a>
			<div class="pluton-reset-checkbox"><input type="checkbox" name="pluton_metabox_reset"> <?php esc_html_e( 'Are you sure? Check this box, then update your post to reset all settings.', 'pluton-panel' ); ?></div>
		</div>

		<div class="clear"></div>

	<?php }

	/**
	 * Save metabox data
	 *
	 * @since 1.0.0
	 */
	public function save_meta_data( $post_id ) {

		/*
		 * We need to verify this came from our screen and with proper authorization,
		 * because the save_post action can be triggered at other times.
		 */

		// Check if our nonce is set.
		if ( ! isset( $_POST['pluton_metabox_nonce'] ) ) {
			return;
		}

		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $_POST['pluton_metabox_nonce'], 'pluton_metabox' ) ) {
			return;
		}

		// If this is an autosave, our form has not been submitted, so we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Check the user's permissions.
		if ( isset( $_POST['post_type'] ) && 'page' == $_POST['post_type'] ) {

			if ( ! current_user_can( 'edit_page', $post_id ) ) {
				return;
			}

		} else {

			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				return;
			}
		}

		/* OK, it's safe for us to save the data now. Now we can loop through fields */

		// Check reset field
		$reset = isset( $_POST['pluton_metabox_reset'] ) ? $_POST['pluton_metabox_reset'] : '';

		// Set settings array
		$tabs = $this->tabs_array();
		$settings = array();
		foreach( $tabs as $tab ) {
			foreach ( $tab['settings'] as $setting ) {
				$settings[] = $setting;
			}
		}

		// Loop through settings and validate
		foreach ( $settings as $setting ) {

			// Vars
			$value = '';
			$id    = $setting['id'];
			$type  = isset ( $setting['type'] ) ? $setting['type'] : 'text';

			// Make sure field exists and if so validate the data
			if ( isset( $_POST[$id] ) ) {

				// Validate text
				if ( 'text' == $type ) {
					$value = sanitize_text_field( $_POST[$id] );
				}

				// Validate textarea
				if ( 'textarea' == $type ) {
					$value = esc_html( $_POST[$id] );
				}

				// Links
				elseif ( 'link' == $type ) {
					$value = esc_url( $_POST[$id] );
				}

				// Validate select
				elseif ( 'select' == $type ) {
					if ( 'default' == $_POST[$id] ) {
						$value = '';
					} else {
						$value = $_POST[$id];
					}
				}

				// Validate media
				if ( 'media' == $type ) {

					// Sanitize
					$value = $_POST[$id];

				}

				// All else
				else {
					$value = $_POST[$id];
				}

				// Update meta if value exists
				if ( $value && 'on' != $reset ) {
					update_post_meta( $post_id, $id, $value );
				}

				// Otherwise cleanup stuff
				else {
					delete_post_meta( $post_id, $id );
				}
			}

		}

	}

	/**
	 * Helpers
	 *
	 * @since 1.0.0
	 */
	public static function helpers( $return = NULl ) {


		// Return array of WP menus
		if ( 'menus' == $return ) {
			$menus = array( esc_html__( 'Default', 'pluton-panel' ) );
			$get_menus = get_terms( 'nav_menu', array( 'hide_empty' => true ) );
			foreach ( $get_menus as $menu) {
				$menus[$menu->term_id] = $menu->name;
			}
			return $menus;
		}

		// Title styles
		elseif ( 'title_styles' == $return ) {
			return apply_filters( 'pluton_title_styles', array(
				''                 => esc_html__( 'Default', 'pluton-panel' ),
				'centered'         => esc_html__( 'Centered', 'pluton-panel' ),
				'centered-minimal' => esc_html__( 'Centered Minimal', 'pluton-panel' ),
				'background-image' => esc_html__( 'Background Image', 'pluton-panel' ),
				'solid-color'      => esc_html__( 'Solid Color & White Text', 'pluton-panel' ),
			) );
		}

		// Widgets
		elseif ( 'widget_areas' == $return ) {
			global $wp_registered_sidebars;
			$widgets_areas = array( esc_html__( 'Default', 'pluton-panel' ) );
			$get_widget_areas = $wp_registered_sidebars;
			if ( ! empty( $get_widget_areas ) ) {
				foreach ( $get_widget_areas as $widget_area ) {
					$name = isset ( $widget_area['name'] ) ? $widget_area['name'] : '';
					$id = isset ( $widget_area['id'] ) ? $widget_area['id'] : '';
					if ( $name && $id ) {
						$widgets_areas[$id] = $name;
					}
				}
			}
			return $widgets_areas;
		}

	}

	/**
	 * Settings Array
	 *
	 * @since 1.0.0
	 */
	public function tabs_array() {

		// Prefix
		$prefix = 'pluton_';

		// Define variable
		$array = array();

		// Main Tab
		$array['main'] = array(
			'title' => esc_html__( 'Main', 'pluton-panel' ),
			'settings' => array(
				'post_layout' => array(
					'title' => esc_html__( 'Content Layout', 'pluton-panel' ),
					'type' => 'select',
					'id' => $prefix . 'post_layout',
					'description' => esc_html__( 'Select your custom layout for this page or post content.', 'pluton-panel' ),
					'options' => array(
						'' => esc_html__( 'Default', 'pluton-panel' ),
						'right-sidebar' => esc_html__( 'Right Sidebar', 'pluton-panel' ),
						'left-sidebar' => esc_html__( 'Left Sidebar', 'pluton-panel' ),
						'full-width' => esc_html__( 'No Sidebar', 'pluton-panel' ),
						'full-screen' => esc_html__( 'Full Screen', 'pluton-panel' ),
					),
				),
				'post_custom_width' => array(
					'title' => esc_html__( 'Content Custom Width', 'pluton-panel' ),
					'type' => 'text',
					'id' => $prefix . 'post_custom_width',
					'description' => esc_html__( 'Select your custom width in px for the content of this page or post.', 'pluton-panel' ),
					'hidden' => true,
				),
				'sidebar' => array(
					'title' => esc_html__( 'Sidebar', 'pluton-panel' ),
					'type' => 'select',
					'id' => 'sidebar',
					'description' => esc_html__( 'Select your a custom sidebar for this page or post.', 'pluton-panel' ),
					'options' => $this->helpers( 'widget_areas' ),
				),
				'disable_top_bar' => array(
					'title' => esc_html__( 'Top Bar', 'pluton-panel' ),
					'id' => $prefix . 'disable_top_bar',
					'type' => 'select',
					'description' => esc_html__( 'Enable or disable this element on this page or post.', 'pluton-panel' ),
					'options' => array(
						'' => esc_html__( 'Default', 'pluton-panel' ),
						'enable' => esc_html__( 'Enable', 'pluton-panel' ),
						'on' => esc_html__( 'Disable', 'pluton-panel' ),
					),
				),
				'header_style' => array(
					'title' => esc_html__( 'Header Style', 'pluton-panel' ),
					'id' => $prefix . 'header_style',
					'type' => 'select',
					'description' => esc_html__( 'Select the style of the header on this page or post.', 'pluton-panel' ),
					'options' => array(
						'' 				=> esc_html__( 'Default', 'pluton-panel' ),
						'minimal' 		=> esc_html__( 'Minimal', 'pluton-panel' ),
						'transparent' 	=> esc_html__( 'Transparent', 'pluton-panel' ),
						'reveal'	 	=> esc_html__( 'Reveal', 'pluton-panel' ),
						'full_screen'	=> esc_html__( 'Full Screen', 'pluton-panel' ),
						'top'			=> esc_html__( 'Top Menu', 'pluton-panel' ),
						'centered'		=> esc_html__( 'Centered', 'pluton-panel' ),
					),
					'default' => '',
				),
				'transparent_header_color' => array(
					'title' => esc_html__( 'Transparent Header Links Color', 'pluton-panel' ),
					'type' => 'select',
					'id' => $prefix . 'transparent_header_color',
					'description' => esc_html__( 'Select your transparent header links color', 'pluton-panel' ),
					'options' => array(
						'' => esc_html__( 'Default', 'pluton-panel' ),
						'white' => esc_html__( 'White', 'pluton-panel' ),
						'dark' => esc_html__( 'Dark', 'pluton-panel' ),
					),
					'default' => '',
				),
				'transparent_header_logo' => array(
					'title' => esc_html__( 'Transparent Header Logo', 'pluton-panel'),
					'id' => $prefix . 'transparent_header_logo',
					'type' => 'media',
					'description' => esc_html__( 'Select a custom logo (optional) for the transparent header.', 'pluton-panel' ),
				),
				'transparent_header_logo_retina' => array(
					'title' => esc_html__( 'Transparent Header Logo: Retina', 'pluton-panel'),
					'id' => $prefix . 'transparent_header_logo_retina',
					'type' => 'media',
					'description' => esc_html__( 'Retina version for the transparent header custom logo.', 'pluton-panel' ),
				),
				'transparent_header_retina_logo_height' => array(
					'title' => esc_html__( 'Transparent Header Retina Logo Height', 'pluton-panel'),
					'id' => $prefix . 'transparent_header_logo_retina_height',
					'description' => esc_html__( 'Enter a size.', 'pluton-panel' ),
					'type' => 'number',
				),
				'full_screen_header_logo' => array(
					'title' => esc_html__( 'Full Screen Header Logo', 'pluton-panel'),
					'id' => $prefix . 'full_screen_header_logo',
					'type' => 'media',
					'description' => esc_html__( 'Select a custom logo (optional) when the menu is opened.', 'pluton-panel' ),
				),
				'full_screen_header_logo_height' => array(
					'title' => esc_html__( 'Full Screen Header Logo Height', 'pluton-panel'),
					'id' => $prefix . 'full_screen_header_logo_height',
					'description' => esc_html__( 'Enter a size.', 'pluton-panel' ),
					'type' => 'number',
				),
				'disable_breadcrumbs' => array(
					'title' => esc_html__( 'Breadcrumbs', 'pluton-panel' ),
					'id' => $prefix . 'disable_breadcrumbs',
					'type' => 'select',
					'description' => esc_html__( 'Enable or disable this element on this page or post.', 'pluton-panel' ),
					'options' => array(
						'' => esc_html__( 'Default', 'pluton-panel' ),
						'enable' => esc_html__( 'Enable', 'pluton-panel' ),
						'on' => esc_html__( 'Disable', 'pluton-panel' ),
					),
				),
				'disable_margins' => array(
					'title' => esc_html__( 'Margins', 'pluton-panel' ),
					'id' => $prefix . 'disable_margins',
					'type' => 'select',
					'description' => esc_html__( 'Enable or disable this element on this page or post.', 'pluton-panel' ),
					'options' => array(
						'' => esc_html__( 'Default', 'pluton-panel' ),
						'enable' => esc_html__( 'Enable', 'pluton-panel' ),
						'on' => esc_html__( 'Disable', 'pluton-panel' ),
					),
				),
			),
		);

		// Title Tab
		$array['title'] = array(
			'title' => esc_html__( 'Title', 'pluton-panel' ),
			'settings' => array(
				'disable_title' => array(
					'title' => esc_html__( 'Title', 'pluton-panel' ),
					'id' => $prefix . 'disable_title',
					'type' => 'select',
					'description' => esc_html__( 'Enable or disable this element on this page or post.', 'pluton-panel' ),
					'options' => array(
						'' => esc_html__( 'Enable', 'pluton-panel' ),
						'on' => esc_html__( 'Disable', 'pluton-panel' ),
					),
				),
				'post_title' => array(
					'title' => esc_html__( 'Custom Title', 'pluton-panel' ),
					'id' => $prefix . 'post_title',
					'type' => 'text',
					'description' => esc_html__( 'Alter the main title display.', 'pluton-panel' ),
				),
				'post_subheading' => array(
					'title' => esc_html__( 'Subheading', 'pluton-panel' ),
					'type' => 'text_html',
					'id' => $prefix . 'post_subheading',
					'description' => esc_html__( 'Enter your page subheading. Shortcodes & HTML is allowed.', 'pluton-panel' ),
				),
				'post_title_style' => array(
					'title' => esc_html__( 'Title Style', 'pluton-panel' ),
					'type' => 'select',
					'id' => $prefix . 'post_title_style',
					'description' => esc_html__( 'Select a custom title style for this page or post.', 'pluton-panel' ),
					'options' => $this->helpers( 'title_styles' ),
				),
				'post_title_background_color' => array(
					'title' => esc_html__( 'Title: Background Color', 'pluton-panel' ),
					'description' => esc_html__( 'Select a color.', 'pluton-panel' ),
					'id' => $prefix .'post_title_background_color',
					'type' => 'color',
					'hidden' => true,
				),
				'post_title_background' => array(
					'title' => esc_html__( 'Title: Background Image', 'pluton-panel'),
					'id' => $prefix . 'post_title_background',
					'type' => 'media',
					'description' => esc_html__( 'Select a custom header image for your main title.', 'pluton-panel' ),
					'hidden' => true,
				),
				'post_title_height' => array(
					'title' => esc_html__( 'Title: Background Height', 'pluton-panel' ),
					'type' => 'text',
					'id' => $prefix . 'post_title_height',
					'description' => esc_html__( 'Select your custom height for your title background. Default is 400px.', 'pluton-panel' ),
					'hidden' => true,
				),
				'post_title_background_overlay' => array(
					'title' => esc_html__( 'Title: Background Overlay', 'pluton-panel' ),
					'type' => 'select',
					'id' => $prefix . 'post_title_background_overlay',
					'description' => esc_html__( 'Select an overlay for the title background.', 'pluton-panel' ),
					'options' => array(
						'' => esc_html__( 'None', 'pluton-panel' ),
						'yes' => esc_html__( 'Yes', 'pluton-panel' ),
					),
					'hidden' => true,
				),
				'post_title_background_overlay_opacity' => array(
					'id' => $prefix . 'post_title_background_overlay_opacity',
					'type' => 'text',
					'title' => esc_html__( 'Title: Background Overlay Opacity', 'pluton-panel' ),
					'description' => esc_html__( 'Enter a custom opacity for your title background overlay.', 'pluton-panel' ),
					'default' => '',
					'hidden' => true,
				),
			),
		);

		// Posts Slider Tab
		$array['posts-slider'] = array(
			'title' => esc_html__( 'Posts Slider', 'pluton-panel' ),
			'settings' => array(
				'posts_slider' 	=> array(
					'title' 		=> esc_html__( 'Posts Slider', 'pluton-panel' ),
					'id' 			=> $prefix . 'posts_slider',
					'type' 			=> 'select',
					'description' 	=> esc_html__( 'Enable posts slider for this page/post.', 'pluton-panel' ),
					'options' 		=> array(
                        'on'         => esc_html__( 'No', 'pluton-panel' ),
                        'enable'     => esc_html__( 'Yes', 'pluton-panel' ),
					),
				),
				'slider_style' 	=> array(
					'title' 		=> esc_html__( 'Slider Style', 'pluton-panel' ),
					'id' 			=> $prefix . 'slider_style',
					'type' 			=> 'select',
					'description' 	=> esc_html__( 'Select your style for the slider.', 'pluton-panel' ),
					'options' 		=> array(
                        'one'       => esc_html__( 'One Image', 'pluton-panel' ),
                        'images'    => esc_html__( 'Three Images', 'pluton-panel' ),
					),
                    'default'   	=> 'one',
				),
				'slider_cats_exclude' => array(
					'title' 		=> esc_html__( 'Exclude Categories', 'pluton-panel' ),
					'description' 	=> esc_html__( 'Use this option to exclude categories, separate by commas.', 'pluton-panel' ),
					'id' 			=> $prefix . 'slider_cats_exclude',
					'type' 			=> 'text',
				),
				'slide_margin' => array(
					'title' 		=> esc_html__( 'Margin', 'pluton-panel' ),
					'description' 	=> esc_html__( 'Enter a margin, top/left/bottom/right. Eg 20px 0.', 'pluton-panel' ),
					'id' 			=> $prefix . 'slide_margin',
					'type' 			=> 'text',
				),
				'slide_image_width' => array(
					'title' 		=> esc_html__( 'Images Width', 'pluton-panel' ),
					'description' 	=> esc_html__( 'Enter a width for the images.', 'pluton-panel' ),
					'id' 			=> $prefix . 'slide_image_width',
					'type' 			=> 'text',
				),
				'slide_image_height' => array(
					'title' 		=> esc_html__( 'Images Height', 'pluton-panel' ),
					'description' 	=> esc_html__( 'Enter a height for the images.', 'pluton-panel' ),
					'id' 			=> $prefix . 'slide_image_height',
					'type' 			=> 'text',
				),
				'slide_speed' 		=> array(
					'title' 		=> esc_html__( 'Speed', 'pluton-panel' ),
					'description' 	=>esc_html__( 'Enter a number for the speed, default is 7000ms.', 'pluton-panel' ),
					'id' 			=> $prefix . 'slide_speed',
					'type' 			=> 'text',
				),
				'slide_number' 		=> array(
					'title' 		=> esc_html__( 'Number of Slides', 'pluton-panel' ),
					'description' 	=> esc_html__( 'Enter a number of post to display in the slider.', 'pluton-panel' ),
					'id' 			=> $prefix . 'slide_number',
					'type' 			=> 'text',
				),
				'slide_order' 		=> array(
					'title' 		=> esc_html__( 'Order', 'pluton-panel' ),
					'id' 			=> $prefix . 'slide_order',
					'type' 			=> 'select',
					'description' 	=> esc_html__( 'Random or Recent?', 'pluton-panel' ),
					'options' 		=> array(
                        'ASC'           => esc_html__( 'Recent', 'pluton-panel' ),
                        'rand'          => esc_html__( 'Random', 'pluton-panel' ),
                        'comment_count' => esc_html__( 'Most Comments', 'pluton-panel' ),
                        'modified'      => esc_html__( 'Last Modified', 'pluton-panel' ),
					),
				),
				'slide_read_more' 	=> array(
					'title' 		=> esc_html__( 'Read More Text', 'pluton-panel' ),
					'description' 	=> esc_html__( 'Enter a custom text.', 'pluton-panel' ),
					'id' 			=> $prefix . 'slide_read_more',
					'type' 			=> 'text',
				),
			),
		);

		// Media tab
		$array['media'] = array(
			'title' => esc_html__( 'Media', 'pluton-panel' ),
			'post_type' => array( 'post' ),
			'settings' => array(
				'post_media_position' => array(
					'title' => esc_html__( 'Media Display/Position', 'pluton-panel' ),
					'id' => $prefix . 'post_media_position',
					'type' => 'select',
					'description' => esc_html__( 'Select your preferred position for your post\'s media (featured image or video).', 'pluton-panel' ),
					'options' => array(
						'' => esc_html__( 'Default', 'pluton-panel' ),
						'above' => esc_html__( 'Full-Width Above Content', 'pluton-panel' ),
						'hidden' => esc_html__( 'None (Do Not Display Featured Image/Video)', 'pluton-panel' ),
					),
				),
				'post_oembed' => array(
					'title' => esc_html__( 'oEmbed URL', 'pluton-panel' ),
					'description' => esc_html__( 'Enter a URL that is compatible with WP\'s built-in oEmbed feature. This setting is used for your video and audio post formats.', 'pluton-panel' ) .'<br /><a href="http://codex.wordpress.org/Embeds" target="_blank">'. esc_html__( 'Learn More', 'pluton-panel' ) .' &rarr;</a>',
					'id' => $prefix . 'post_oembed',
					'type' => 'text',
				),
				'post_self_hosted_shortcode' => array(
					'title' => esc_html__( 'Self Hosted', 'pluton-panel' ),
					'description' => esc_html__( 'Insert your self hosted video or audio url here.', 'pluton-panel' ) .'<br /><a href="http://make.wordpress.org/core/2013/04/08/audio-video-support-in-core/" target="_blank">'. esc_html__( 'Learn More', 'pluton-panel' ) .' &rarr;</a>',
					'id' => $prefix . 'post_self_hosted_media',
					'type' => 'media',
				),
				'post_video_embed' => array(
					'title' => esc_html__( 'Embed Code', 'pluton-panel' ),
					'description' => esc_html__( 'Insert your embed/iframe code.', 'pluton-panel' ),
					'id' => $prefix . 'post_video_embed',
					'type' => 'textarea',
					'rows' => '2',
				),
			),
		);

		// Link/Quote tab
		$array['link_quote'] = array(
			'title' 	=> esc_html__( 'Link/Quote', 'pluton-panel' ),
			'post_type' => array( 'post' ),
			'settings' 	=> array(
				'link_format' 		=> array(
					'title' 		=> esc_html__( 'Link', 'pluton-panel' ),
					'description' 	=>esc_html__( 'Enter your external url. This setting is used for your link post formats.', 'pluton-panel' ),
					'id' 			=> $prefix . 'link_format',
					'type' 			=> 'text',
				),
				'link_format_target' => array(
					'title' 		=> esc_html__( 'Link Target', 'pluton-panel' ),
					'type' 			=> 'select',
					'id' 			=> $prefix . 'link_format_target',
					'description' 	=> esc_html__( 'Choose your target for the url. This setting is used for your link post formats.', 'pluton-panel' ),
					'options' => array(
						'self' 		=> esc_html__( 'Self', 'pluton-panel' ),
						'blank' 	=> esc_html__( 'Blank', 'pluton-panel' ),
					),
				),
				'quote_format' 		=> array(
					'title' 		=> esc_html__( 'Quote', 'pluton-panel' ),
					'description' 	=> esc_html__( 'Enter your quote. This setting is used for your quote post formats.', 'pluton-panel' ),
					'id' 			=> $prefix . 'quote_format',
					'type' 			=> 'textarea',
					'rows' 			=> '2',
				),
				'quote_format_link' => array(
					'title' 		=> esc_html__( 'Quote Link', 'pluton-panel' ),
					'type' 			=> 'select',
					'id' 			=> $prefix . 'quote_format_link',
					'description' 	=> esc_html__( 'Choose your quote link. This setting is used for your quote post formats.', 'pluton-panel' ),
					'options' => array(
						'post' 		=> esc_html__( 'Post', 'pluton-panel' ),
						'none' 		=> esc_html__( 'None', 'pluton-panel' ),
					),
				),
			),
		);

		// Apply filter & return settings array
		return apply_filters( 'pluton_metabox_array', $array );
	}

	/**
	 * Adds custom CSS for the metaboxes inline instead of loading another stylesheet
	 *
	 * @see assets/metabox.css
	 * @since 1.0.0
	 */
	public static function metaboxes_css() { ?>

		<style type="text/css">
			#pluton-metabox .wp-tab-panel{display:none;max-height:none!important}#pluton-metabox .wp-tab-panel#pluton-mb-tab-1{display:block}#pluton-metabox ul.wp-tab-bar{-webkit-touch-callout:none;-webkit-user-select:none;-khtml-user-select:none;-moz-user-select:none;-ms-user-select:none;user-select:none;padding-top:5px}#pluton-metabox ul.wp-tab-bar:after{content:"";display:block;height:0;clear:both;visibility:hidden;zoom:1}#pluton-metabox ul.wp-tab-bar li{padding:5px 12px;font-size:14px}#pluton-metabox ul.wp-tab-bar li a:focus{box-shadow:none}#pluton-metabox .inside .form-table tr{border-top:1px solid #dfdfdf}#pluton-metabox .inside .form-table tr:first-child{border:none}#pluton-metabox .inside .form-table th{width:240px;padding:10px 30px 10px 0}#pluton-metabox .inside .form-table td{padding:10px 0}#pluton-metabox .inside .form-table label{display:block}#pluton-metabox .inside .form-table th label span{margin-right:7px}#pluton-metabox .pluton-mb-uploader{margin-left:5px}#pluton-metabox .inside .form-table th p.pluton-mb-description{font-size:12px;font-weight:400;margin:0;padding:4px 0 0}#pluton-metabox .inside .form-table .pluton-mb-textarea-code,#pluton-metabox .inside .form-table input[type=text],#pluton-metabox .inside .form-table input[type=number]{width:40%;padding:7px 15px}#pluton-metabox .inside .form-table textarea{width:100%}#pluton-metabox .inside .form-table select{display:block;min-width:40%;height:auto;border:1px solid #d9d9d9;color:#333;font-size:13px;outline:0;padding:7px 15px;cursor:pointer;z-index:5}#pluton-metabox .pluton-mb-reset{margin-top:7px}#pluton-metabox .pluton-mb-reset .pluton-reset-btn{display:block;float:left}#pluton-metabox .pluton-mb-reset .pluton-reset-checkbox{float:left;display:none;margin-left:10px;padding-top:5px}
		</style>

	<?php

	}

	/**
	 * Adds custom js for the metaboxes inline instead of loading another js file
	 *
	 * @see assets/metabox.js
	 * @since 1.0.0
	 */
	public static function metaboxes_js() { ?>

		<script type="text/javascript">
			!function(t){"use strict";t(document).on("ready",function(){t("div#pluton-metabox ul.wp-tab-bar a").click(function(){var e=t("#pluton-metabox ul.wp-tab-bar li"),o=t(this).data("tab"),l=t("#pluton-metabox div.wp-tab-panel");return t(e).removeClass("wp-tab-active"),t(l).hide(),t(o).show(),t(this).parent("li").addClass("wp-tab-active"),!1}),t("div#pluton-metabox .pluton-mb-color-field").wpColorPicker();var e=!0,o=wp.media.editor.send.attachment;t("div#pluton-metabox .pluton-mb-uploader").click(function(l){var n=(wp.media.editor.send.attachment,t(this)),a=n.prev();return wp.media.editor.send.attachment=function(l,n){return e?void t(a).val(n.id):o.apply(this,[l,n])},wp.media.editor.open(n),!1}),t("div#pluton-metabox .add_media").on("click",function(){e=!1}),t("div#pluton-metabox div.pluton-mb-reset a.pluton-reset-btn").click(function(){var e=t("div.pluton-mb-reset div.pluton-reset-checkbox"),o=confirm.is(":visible")?"<?php esc_html_e(  'Reset Settings', 'pluton' ); ?>":"<?php esc_html_e(  'Cancel Reset', 'pluton' ); ?>";t(this).text(o),t("div.pluton-mb-reset div.pluton-reset-checkbox input").attr("checked",!1),e.toggle()});var l=t("div#pluton-metabox select#pluton_post_layout"),n=t("#pluton_post_custom_width_tr");"full-width"===l.val()?n.show():n.hide(),l.change(function(){"full-width"===t(this).val()?n.show():n.hide()});var a=t("div#pluton-metabox select#pluton_header_style"),i=t("#pluton_transparent_header_color_tr, #pluton_transparent_header_logo_tr,#pluton_transparent_header_logo_retina_tr,#pluton_transparent_header_logo_retina_height_tr");"transparent"===a.val()?i.show():i.hide(),a.change(function(){"transparent"===t(this).val()?i.show():i.hide()});var r=t("div#pluton-metabox select#pluton_header_style"),_=t("#pluton_full_screen_header_logo_tr, #pluton_full_screen_header_logo_height_tr");"full_screen"===r.val()?_.show():_.hide(),r.change(function(){"full_screen"===t(this).val()?_.show():_.hide()});var s=t("div#pluton-metabox select#pluton_disable_title"),p=t("#pluton_disable_header_margin_tr, #pluton_post_subheading_tr,#pluton_post_title_style_tr"),u=t("div#pluton-metabox select#pluton_post_title_style"),d=u.val(),h=t("#pluton_post_title_background_color_tr, #pluton_post_title_background_tr,#pluton_post_title_height_tr,#pluton_post_title_background_overlay_tr,#pluton_post_title_background_overlay_opacity_tr"),c=t("#pluton_post_title_background_color_tr");"on"===s.val()?(p.hide(),h.hide()):p.show(),"background-image"===d?h.show():"solid-color"===d&&c.show(),s.change(function(){if("on"===t(this).val())p.hide(),h.hide();else{p.show();var e=u.val();"background-image"===e?h.show():"solid-color"===e&&c.show()}}),u.change(function(){h.hide(),"background-image"==t(this).val()?h.show():"solid-color"===t(this).val()&&c.show()})})}(jQuery);
		</script>

	<?php }

}
$pluton_post_metaboxes = new Pluton_Post_Metaboxes();