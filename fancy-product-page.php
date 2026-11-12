<?php
/**
 * Plugin Name:       Fancy Product Page
 * Plugin URI:        https://headwall-hosting.com/plugins/fancy-product-page/
 * Description:       Display a WooCommerce product using an ordinary WordPress Page or Post in place of the default single-product template.
 * Version:           1.4.1
 * Requires at least: 6.2
 * Requires PHP:      8.2
 * Requires Plugins:  woocommerce
 * Author:            Paul Faulkner
 * Author URI:        https://headwall-hosting.com/a-web-guys-blog/
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       fancy-product-page
 * Domain Path:       /languages
 * WC requires at least: 6.0
 * WC tested up to:   11.3
 *
 * @package Fancy_Product_Page
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || die();

define( 'FANCY_PRODUCT_PAGE_VERSION', '1.4.1' );
define( 'FANCY_PRODUCT_PAGE_FILE', __FILE__ );
define( 'FANCY_PRODUCT_PAGE_PATH', plugin_dir_path( __FILE__ ) );
define( 'FANCY_PRODUCT_PAGE_URL', plugin_dir_url( __FILE__ ) );
define( 'FANCY_PRODUCT_PAGE_BASENAME', plugin_basename( __FILE__ ) );

/*
 * Deprecated since 1.5.0: the 1.x names, kept for code that reads them. Removed in 2.0.0.
 */
const PP_FPP_NAME    = 'fancy-product-page';
const PP_FPP_VERSION = FANCY_PRODUCT_PAGE_VERSION;
define( 'PP_FPP_FILE', FANCY_PRODUCT_PAGE_FILE );
define( 'PP_FPP_BASENAME', FANCY_PRODUCT_PAGE_BASENAME );
define( 'PP_FPP_DIR', FANCY_PRODUCT_PAGE_PATH );
define( 'PP_FPP_URL', FANCY_PRODUCT_PAGE_URL );
define( 'PP_FPP_ADMIN_TEMPLATES_DIR', trailingslashit( FANCY_PRODUCT_PAGE_PATH . 'views' ) );
define( 'PP_FPP_PUBLIC_TEMPLATES_DIR', trailingslashit( FANCY_PRODUCT_PAGE_PATH . 'public-templates' ) );
define( 'PP_FPP_ASSETS_DIR', trailingslashit( FANCY_PRODUCT_PAGE_PATH . 'assets' ) );
define( 'PP_FPP_ASSETS_URL', trailingslashit( FANCY_PRODUCT_PAGE_URL . 'assets' ) );

require_once FANCY_PRODUCT_PAGE_PATH . 'constants.php';
require_once FANCY_PRODUCT_PAGE_PATH . 'functions-private.php';
require_once FANCY_PRODUCT_PAGE_PATH . 'functions.php';
require_once FANCY_PRODUCT_PAGE_PATH . 'includes/form-helpers.php';

require_once FANCY_PRODUCT_PAGE_PATH . 'includes/class-product-meta-box.php';
require_once FANCY_PRODUCT_PAGE_PATH . 'includes/class-admin-hooks.php';
require_once FANCY_PRODUCT_PAGE_PATH . 'includes/class-plugin.php';

require_once FANCY_PRODUCT_PAGE_PATH . 'includes/shortcode-add-to-cart-button.php';

// GitHub auto-updates: admin, cron and WP-CLI only.
if ( is_admin() || ( defined( 'DOING_CRON' ) && DOING_CRON ) || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
	require_once FANCY_PRODUCT_PAGE_PATH . 'includes/class-github-updater.php';
	new Fancy_Product_Page\Github_Updater( FANCY_PRODUCT_PAGE_FILE, Fancy_Product_Page\UPDATER_GITHUB_REPO, FANCY_PRODUCT_PAGE_VERSION, Fancy_Product_Page\FILTER_UPDATER_ENABLED );
}

/**
 * Initialise the plugin.
 *
 * @since 1.5.0
 */
function fancy_product_page_run(): void {
	global $pp_fpp_plugin;

	// The global predates Plugin::instance() and is kept for code that reads it.
	$pp_fpp_plugin = Fancy_Product_Page\Plugin::instance();
	$pp_fpp_plugin->run();
}
fancy_product_page_run();
