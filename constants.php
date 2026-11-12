<?php
/**
 * Plugin Constants
 *
 * @package Fancy_Product_Page
 * @since 1.0.0
 */

namespace Fancy_Product_Page;

// Exit if accessed directly.
defined( 'ABSPATH' ) || die();

/**
 * WooCommerce product post type.
 *
 * @since 1.0.0
 */
const POST_TYPE_PRODUCT = 'product';

/**
 * Post types that can act as a fancy page unless the fancy_product_page_post_types filter changes them.
 *
 * @since 1.5.0
 */
const DEFAULT_FANCY_PAGE_POST_TYPES = array( 'page', 'post' );

/**
 * Product post-meta keys. Both predate the plugin-prefix rule and are stored
 * data, so they keep their names.
 *
 * META_SUPPRESS_FANCY_PAGE_PRODUCT_SCHEMA is set only when an administrator
 * opts out of writing product schema to the fancy page: absent means "write it",
 * so products saved before 1.3.0 keep their behaviour.
 *
 * @since 1.0.0
 */
const META_FANCY_PRODUCT_PAGE_ID              = '_fancy_product_page';
const META_SUPPRESS_FANCY_PAGE_PRODUCT_SCHEMA = '_suppress_fancy_page_product_schema';

/**
 * Meta box: the positively worded checkbox backing
 * META_SUPPRESS_FANCY_PAGE_PRODUCT_SCHEMA, and the nonce.
 *
 * META_BOX_ID is the 1.x class name, kept because WordPress stores each user's
 * meta box order and collapsed state under it.
 *
 * @since 1.3.0
 */
const FIELD_WRITE_FANCY_PAGE_PRODUCT_SCHEMA = 'fancy_product_page_write_product_schema';
const META_BOX_ID                           = 'Fancy_Product_Page\Product_Meta_Box';
const NONCE_META_BOX_ACTION                 = 'fancy_product_page_save_meta_box';
const NONCE_META_BOX_FIELD                  = 'fancy_product_page_meta_box_nonce';

/**
 * Admin stylesheet handle. A public contract: other code may list it as a dependency.
 *
 * @since 1.0.0
 */
const STYLE_HANDLE_ADMIN = 'fancy-product-page-admin';

/**
 * Whether to convert a non-numeric `add-to-cart` request value (a SKU) to a
 * product ID before WooCommerce processes it.
 *
 * @since 1.0.0
 */
const CONVERT_ADD_TO_CART_SKU_TO_ID = true;

/**
 * Status used by the redirect from the product's own URL to its fancy page.
 *
 * @since 1.0.0
 */
const REDIRECT_HTTP_CODE = 301;

/**
 * GitHub updater: the repository checked for releases, and the filter passed to
 * Github_Updater. Other hooks are named literally at each call, so phpcs can
 * check their prefix.
 *
 * @since 1.2.0
 */
const UPDATER_GITHUB_REPO    = 'headwalluk/fancy-product-page';
const FILTER_UPDATER_ENABLED = 'fancy_product_page_updater_enabled';
