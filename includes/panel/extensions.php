<?php
/**
 * Extensions
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
class Pluton_Extensions {

	/**
	 * Start things up
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_page' ), 9999 );
		add_action( 'admin_enqueue_scripts',array( $this,'scripts' ) );
	}

	/**
	 * Add sub menu page for the custom CSS input
	 *
	 * @since 1.0.0
	 */
	public function add_page() {
		add_submenu_page(
			'pluton-panel',
			esc_html__( 'Extensions', 'pluton-panel' ),
			'<span style="color: #00B9EB">' . esc_html__( 'Extensions', 'pluton-panel' ) . '</span>',
			'manage_options',
			'pluton-panel-extensions',
			array( $this, 'create_admin_page' )
		);
	}

	/**
	 * Load scripts
	 *
	 * @since 1.0.0
	 */
	public static function scripts( $hook ) {

		// Only load scripts when needed
		if ( PP_ADMIN_PANEL_HOOK_PREFIX . '-extensions' != $hook ) {
			return;
		}

		// CSS
		wp_enqueue_style( 'pluton-admin', plugins_url( '/assets/admin-fields/admin.css', __FILE__ ) );

	}

	/**
	 * Settings page output
	 *
	 * @since 1.0.0
	 */
	public function create_admin_page() {

		$premium['addons-vc'] = array(
			'url' 		=> 'https://plutonwp.com/extension/pluton-addons-vc/',
			'image' 	=> plugins_url( '/assets/img/addons-vc-image.png', __FILE__ ),
			'name' 		=> 'Addons VC',
			'desc' 		=> 'Includes Visual Composer premium addon elements like Blog Carousel, Heading, Icon, Icon Box.',
			'ref_url' 	=> '',
		);

		$premium['footer-callout'] = array(
			'url' 		=> 'https://plutonwp.com/extension/pluton-footer-callout/',
			'image' 	=> plugins_url( '/assets/img/footer-callout-image.png', __FILE__ ),
			'name' 		=> 'Footer Callout',
			'desc' 		=> 'Add some relevant/important information about your company or product in your footer.',
			'ref_url' 	=> '',
		);

		$premium['sticky-header'] = array(
			'url' 		=> 'https://plutonwp.com/extension/pluton-sticky-header/',
			'image' 	=> plugins_url( '/assets/img/sticky-header-image.png', __FILE__ ),
			'name' 		=> 'Sticky Header',
			'desc' 		=> 'Attach the header with or without the top bar at the top of your screen with an animation.',
			'ref_url' 	=> '',
		);

		$premium['woo-styling'] = array(
			'url' 		=> 'https://plutonwp.com/extension/pluton-woo-styling/',
			'image' 	=> plugins_url( '/assets/img/woo-styling-image.png', __FILE__ ),
			'name' 		=> 'Woo Styling',
			'desc' 		=> 'Change the colors of all your WooCommerce pages directly through the customizer.',
			'ref_url' 	=> '',
		);

		$premium['custom-actions'] = array(
			'url' 		=> 'https://plutonwp.com/extension/pluton-custom-actions/',
			'image' 	=> plugins_url( '/assets/img/custom-actions-image.png', __FILE__ ),
			'name' 		=> 'Custom Actions',
			'desc'		=> 'Add wrapper, scripts or php code directly via the Theme Panel.',
			'ref_url' 	=> '',
		);

		$free['panel'] = array(
			'url' 	=> 'https://plutonwp.com/extension/pluton-panel/',
			'image' => plugins_url( '/assets/img/panel-image.png', __FILE__ ),
			'name' 	=> 'Theme Panel',
			'desc' 	=> 'Add meta boxes and Theme Panel to extend the functionality of the theme.',
		);

		$free['custom-sidebar'] = array(
			'url' 	=> 'https://plutonwp.com/extension/pluton-custom-sidebar/',
			'image' => plugins_url( '/assets/img/custom-sidebar-image.png', __FILE__ ),
			'name' 	=> 'Custom Sidebar',
			'desc' 	=> 'Create an unlimited number of sidebars and assign unlimited number of widgets.',
		);

		$free['social-sharing'] = array(
			'url' 	=> 'https://plutonwp.com/extension/pluton-social-sharing/',
			'image' => plugins_url( '/assets/img/social-share-image.png', __FILE__ ),
			'name' 	=> 'Social Sharing',
			'desc' 	=> 'Add social share buttons to your single posts with this free extension.',
		);

		$free['product-sharing'] = array(
			'url' 	=> 'https://plutonwp.com/extension/pluton-product-sharing/',
			'image' => plugins_url( '/assets/img/product-share-image.png', __FILE__ ),
			'name' 	=> 'Product Sharing',
			'desc' 	=> 'Add social share buttons to your single product page with this free extension.',
		); ?>

		<div id="pluton-extensions-wrap" class="wrap">
				
			<h2>Pluton - Extensions</h2>
			
			<div class="wp-filter pluton-admin-notice pluton-filter">
				<div class="alignleft"><strong>Core Extensions Bundle</strong> – Check out our extensions bundle which includes all extensions at a significant discount.</div>
				<div class="alignright"><a href="https://plutonwp.com/core-extensions-bundle/" class="button button-primary" target="_blank">View our Extensions Bundle</a></div>
			</div>
			
			<div class="wp-filter">
				<ul class="filter-links">
					<li><a href='?page=pluton-panel-extensions&filter=premium' class='<?php if ( !isset($_REQUEST['filter']) || isset( $_REQUEST['filter'] ) && $_REQUEST['filter'] == 'premium' ) { echo 'current'; } ?>'>Premium</a></li>
					<li><a href='?page=pluton-panel-extensions&filter=free' class='<?php if ( isset( $_REQUEST['filter'] ) && $_REQUEST['filter'] == 'free' ) { echo 'current'; } ?>'>Free</a></li>
				</ul>
			</div>

			<div class="wp-list-table widefat plugin-install">
				<div id="the-list">
				
					<?php if ( !isset($_REQUEST['filter']) || isset( $_REQUEST['filter'] ) && $_REQUEST['filter'] == 'premium' ) { ?>
					
					<?php foreach( $premium as $key => $info ) {

					$aff_ref = apply_filters( 'pluton_affiliate_ref', $info['ref_url'] ); ?>
					
					<div class="plugin-card">

						<a href="<?php echo $info['url']; ?><?php echo $aff_ref; ?>" class="plugin-image" target="_blank"><img src="<?php echo $info['image']; ?>" /></a>

						<div class="plugin-card-top">

							<div class="name column-name">
								<h4><a href="<?php echo $info['url']; ?><?php echo $aff_ref; ?>" target="_blank"><?php echo $info['name']; ?></a></h4>
							</div>

							<div class="action-links">
								<ul class="plugin-action-buttons"><li><a class="install-now button" href="<?php echo $info['url']; ?><?php echo $aff_ref; ?>" target="_blank">Get this Add on</a></li>
								<li><a href="<?php echo $info['url']; ?><?php echo $aff_ref; ?>" target="_blank">More Details</a></li></ul>
							</div>

							<div class="desc column-description">
								<p><?php echo $info['desc']; ?></p>
							</div>

						</div>

					</div>
					
					<?php } 
					
					} ?>
					
					<?php if ( isset( $_REQUEST['filter'] ) && $_REQUEST['filter'] == 'free' ) { ?>
					
					<?php foreach( $free as $key => $info ) { ?>
					
					<div class="plugin-card">

						<a href="<?php echo $info['url']; ?>" class="plugin-image"><img src="<?php echo $info['image']; ?>" /></a>

						<div class="plugin-card-top">

							<div class="name column-name">
								<h4><a href="<?php echo $info['url']; ?>"><?php echo $info['name']; ?></a></h4>
							</div>

							<div class="action-links">
								<ul class="plugin-action-buttons"><li><a class="install-now button" href="<?php echo $info['url']; ?>">Get this Add on</a></li>
								<li><a href="<?php echo $info['url']; ?>">More Details</a></li></ul>
							</div>

							<div class="desc column-description">
								<p><?php echo $info['desc']; ?></p>
							</div>

						</div>

					</div>
					
					<?php } 
					
					} ?>

				</div>
			</div>

		</div><div style="clear: both;"></div>

	<?php }
}
new Pluton_Extensions();