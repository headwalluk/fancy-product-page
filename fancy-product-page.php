<?php
/**
 * Plugin Name:       Fancy Product Page
 * Plugin URI:        https://github.com/headwalluk/fancy-product-page
 * Description:       Display a WooCommerce product using an ordinary WordPress Page or Post in place of the default single-product template.
 * Version:           1.4.1
 * Requires at least: 6.0
 * Requires PHP:      8.0
 * Requires Plugins:  woocommerce
 * Author:            Paul Faulkner
 * Author URI:        https://headwall-hosting.com
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       fancy-product-page
 * Domain Path:       /languages
 * WC requires at least: 6.0
 *
 * @package Fancy_Product_Page
 */

defined( 'WPINC' ) || die();

const PP_FPP_NAME    = 'fancy-product-page';
const PP_FPP_VERSION = '1.4.1';

define( 'PP_FPP_FILE', __FILE__ );
define( 'PP_FPP_BASENAME', plugin_basename( __FILE__ ) );
define( 'PP_FPP_DIR', plugin_dir_path( __FILE__ ) );
define( 'PP_FPP_URL', plugin_dir_url( __FILE__ ) );
define( 'PP_FPP_ADMIN_TEMPLATES_DIR', trailingslashit( PP_FPP_DIR . 'admin-templates' ) );
define( 'PP_FPP_PUBLIC_TEMPLATES_DIR', trailingslashit( PP_FPP_DIR . 'public-templates' ) );
define( 'PP_FPP_ASSETS_DIR', trailingslashit( PP_FPP_DIR . 'assets' ) );
define( 'PP_FPP_ASSETS_URL', trailingslashit( PP_FPP_URL . 'assets' ) );

require_once PP_FPP_DIR . 'constants.php';
require_once PP_FPP_DIR . 'functions-private.php';
require_once PP_FPP_DIR . 'functions.php';
require_once PP_FPP_DIR . 'includes/form-helpers.php';

// Base classes (load before the concrete classes that extend them).
require_once PP_FPP_DIR . 'includes/class-component.php';
require_once PP_FPP_DIR . 'includes/class-meta-box.php';
require_once PP_FPP_DIR . 'includes/class-settings-core.php';

require_once PP_FPP_DIR . 'includes/class-settings.php';
require_once PP_FPP_DIR . 'includes/class-product-meta-box.php';

require_once PP_FPP_DIR . 'includes/class-admin-hooks.php';
require_once PP_FPP_DIR . 'includes/class-plugin.php';

require_once PP_FPP_DIR . 'includes/shortcode-add-to-cart-button.php';

// GitHub auto-updates (admin, cron and WP-CLI only — no need to load on front-end requests).
if ( is_admin() || ( defined( 'DOING_CRON' ) && DOING_CRON ) || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
	require_once PP_FPP_DIR . 'includes/class-github-updater.php';
	new Fancy_Product_Page\Github_Updater( PP_FPP_FILE, Fancy_Product_Page\UPDATER_GITHUB_REPO, PP_FPP_VERSION, 'fancy_product_page_updater_enabled' );
}

/**
 * Bootstrap the plugin: create the global plugin instance and run it.
 *
 * @return void
 */
function pp_fpp_plugin_run() {
	global $pp_fpp_plugin;
	$pp_fpp_plugin = new Fancy_Product_Page\Plugin( PP_FPP_NAME, PP_FPP_VERSION );
	$pp_fpp_plugin->run();
}
pp_fpp_plugin_run();
