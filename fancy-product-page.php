<?php

/**
 * Plugin Name:       Fancy Product Page
 * Plugin URI:        Cart to Quote
 * Description:       Create ad-hoc bespoke product pages
 * Version:           1.0.2
 * Author:            Headwall WP Tutorials
 * Author URI:        https://wp-tutorials.tech/
 * License:           GPLv3 or later
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       fancy-product-page
 * Domain Path:       /languages
 */

defined( 'WPINC' ) || die();

const PP_FPP_NAME    = 'fancy-product-page';
const PP_FPP_VERSION = '1.0.2';

define( 'PP_FPP_DIR', plugin_dir_path( __FILE__ ) );
define( 'PP_FPP_URL', plugin_dir_url( __FILE__ ) );
define( 'PP_FPP_ADMIN_TEMPLATES_DIR', trailingslashit( PP_FPP_DIR . 'admin-templates' ) );
define( 'PP_FPP_PUBLIC_TEMPLATES_DIR', trailingslashit( PP_FPP_DIR . 'public-templates' ) );
define( 'PP_FPP_ASSETS_DIR', trailingslashit( PP_FPP_DIR . 'assets' ) );
define( 'PP_FPP_ASSETS_URL', trailingslashit( PP_FPP_URL . 'assets' ) );

// Power Plugins
require_once PP_FPP_DIR . 'pp-core.php';

require_once PP_FPP_DIR . 'constants.php';
require_once PP_FPP_DIR . 'functions-private.php';
require_once PP_FPP_DIR . 'functions.php';

require_once PP_FPP_DIR . 'includes/class-settings.php';
require_once PP_FPP_DIR . 'includes/class-product-meta-box.php';

require_once PP_FPP_DIR . 'includes/class-admin-hooks.php';
require_once PP_FPP_DIR . 'includes/class-plugin.php';

require_once PP_FPP_DIR . 'includes/shortcode-add-to-cart-button.php';

function pp_fpp_plugin_run() {
	global $pp_fpp_plugin;
	$pp_fpp_plugin = new Fancy_Product_Page\Plugin( PP_FPP_NAME, PP_FPP_VERSION );
	$pp_fpp_plugin->run();
}
pp_fpp_plugin_run();
