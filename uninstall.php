<?php
/**
 * Fancy Product Page: Uninstaller
 *
 * Runs when the plugin is deleted from the Plugins screen, not on deactivation.
 * The plugin keeps no settings: this removes the updater's cache. The
 * product-to-page choices stay in each product's meta, so reinstalling restores
 * them; without the plugin they do nothing.
 *
 * @package Fancy_Product_Page
 */

namespace Fancy_Product_Page;

// Bail out if not invoked by WordPress's uninstall handler.
defined( 'WP_UNINSTALL_PLUGIN' ) || die();

require_once __DIR__ . '/includes/class-github-updater.php';

Github_Updater::delete_cache( basename( __DIR__ ) );
