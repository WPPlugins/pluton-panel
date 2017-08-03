<?php
/**
 * Links
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
class Pluton_Links {

	/**
	 * Start things up
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_page' ), 9999 );
		add_action( 'admin_footer', array( $this, 'links_blank' ) );
	}

	/**
	 * Add sub menu page
	 *
	 * @since 1.0.0
	 */
	public function add_page() {
		global $submenu;
	    $submenu[ 'pluton-panel' ][] = array( '<div class="pluton-link">' . esc_html__( 'Documentation', 'pluton-panel' ) . '</div>', 'manage_options', 'http://docs.plutonwp.com/' );
	    $submenu[ 'pluton-panel' ][] = array( '<div class="pluton-link">' . esc_html__( 'Support', 'pluton-panel' ) . '</div>', 'manage_options', 'https://plutonwp.com/support/' );
	}

	/**
	 * Open links in new window
	 *
	 * @since 1.0.0
	 */
	public function links_blank() { ?>
	    <script type="text/javascript">
		    jQuery( document ).ready( function($) {
		        $( '.pluton-link' ).parent().attr( 'target', '_blank' );
		    });
	    </script>
    <?php
	}

}
new Pluton_Links();