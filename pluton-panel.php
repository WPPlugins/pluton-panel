<?php
/**
 * Plugin Name:			Pluton Panel
 * Plugin URI:			https://plutonwp.com/extension/pluton-panel/
 * Description:			Add meta boxes for your posts, pages, posts types and Theme Panel to extend the functionality of the theme.
 * Version:				1.0.8.1
 * Author:				PlutonWP
 * Author URI:			https://plutonwp.com/
 * Requires at least:	4.0.0
 * Tested up to:		4.6.1
 *
 * Text Domain: pluton-panel
 * Domain Path: /languages/
 *
 * @package Pluton_Panel
 * @category Core
 * @author PlutonWP
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Returns the main instance of Pluton_Panel to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return object Pluton_Panel
 */
function Pluton_Panel() {
	return Pluton_Panel::instance();
} // End Pluton_Panel()

Pluton_Panel();

/**
 * Main Pluton_Panel Class
 *
 * @class Pluton_Panel
 * @version	1.0.0
 * @since 1.0.0
 * @package	Pluton_Panel
 */
final class Pluton_Panel {
	/**
	 * Pluton_Panel The single instance of Pluton_Panel.
	 * @var 	object
	 * @access  private
	 * @since 	1.0.0
	 */
	private static $_instance = null;

	/**
	 * The token.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $token;

	/**
	 * The version number.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $version;

	// Admin - Start
	/**
	 * The admin object.
	 * @var     object
	 * @access  public
	 * @since   1.0.0
	 */
	public $admin;

	/**
	 * Constructor function.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function __construct( $widget_areas = array() ) {
		$this->token 			= 'pluton-panel';
		$this->plugin_url 		= plugin_dir_url( __FILE__ );
		$this->plugin_path 		= plugin_dir_path( __FILE__ );
		$this->version 			= '1.0.8.1';

		define( 'PP_ROOT', dirname( __FILE__ ) );
		define( 'PP_ADMIN_PANEL_HOOK_PREFIX', 'theme-panel_page_pluton-panel' );

		register_activation_hook( __FILE__, array( $this, 'install' ) );

		add_action( 'init', array( $this, 'pp_load_plugin_textdomain' ) );

		add_action( 'init', array( $this, 'pp_setup' ) );
	}

	/**
	 * Main Pluton_Panel Instance
	 *
	 * Ensures only one instance of Pluton_Panel is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see Pluton_Panel()
	 * @return Main Pluton_Panel instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) )
			self::$_instance = new self();
		return self::$_instance;
	} // End instance()

	/**
	 * Load the localisation file.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function pp_load_plugin_textdomain() {
		load_plugin_textdomain( 'pluton-panel', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), '1.0.0' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), '1.0.0' );
	}

	/**
	 * Installation.
	 * Runs on activation. Logs the version number and assigns a notice message to a WordPress option.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function install() {
		$this->_log_version_number();
	}

	/**
	 * Log the plugin version number.
	 * @access  private
	 * @since   1.0.0
	 * @return  void
	 */
	private function _log_version_number() {
		// Log the version number.
		update_option( $this->token . '-version', $this->version );
	}

	/**
	 * Setup all the things.
	 * Only executes if Pluton or a child theme using Pluton as a parent is active and the extension specific filter returns true.
	 * Child themes can disable this extension using the pluton_panel filter
	 * @return void
	 */
	public function pp_setup() {
		$theme = wp_get_theme();

		if ( 'Pluton' == $theme->name || 'pluton' == $theme->template && apply_filters( 'pluton_panel', true ) ) {
			require_once( PP_ROOT .'/includes/panel/theme-panel.php' );
			require_once( PP_ROOT .'/includes/metabox/gallery-metabox/gallery-metabox.php' );
			if ( is_admin() ) {
				require_once( PP_ROOT .'/includes/metabox/metabox.php' );
			}
			if ( class_exists( 'SitePress' ) ) {
				require_once( PP_ROOT .'/includes/config/wpml.php' );
			}
			if ( class_exists( 'Polylang' ) ) {
				require_once( PP_ROOT .'/includes/config/polylang.php' );
			}
		} else {
			add_action( 'admin_notices', array( $this, 'pp_install_pluton_notice' ) );
		}
	}

	/**
	 * Pluton install
	 * If the user activates the plugin while having a different parent theme active, prompt them to install Pluton.
	 * @since   1.0.0
	 * @return  void
	 */
	public function pp_install_pluton_notice() {
		echo '<div class="notice is-dismissible updated">
				<p>' . esc_html__( 'Pluton Panel requires that you use Pluton as your parent theme.', 'pluton-panel' ) . ' <a href="https://plutonwp.com/">' . esc_html__( 'Install Pluton Now', 'pluton-panel' ) . '</a></p>
			</div>';
	}

} // End Class
