<?php

/**
 * Plugin constants.
 *
 * Single source of truth for the meta keys, post types, and configuration
 * values used throughout the plugin.
 *
 * @package Fancy_Product_Page
 */

namespace Fancy_Product_Page;

defined('WPINC') || die();

/**
 * WooCommerce product post type.
 *
 * @var string
 */
const POST_TYPE_PRODUCT = 'product';

/**
 * Post-meta key storing the chosen fancy-page ID on a product.
 *
 * @var string
 */
const META_FANCY_PRODUCT_PAGE_ID = '_fancy_product_page';

/**
 * Post-meta key recording that product schema must NOT be written to the
 * product's fancy page.
 *
 * Deliberately stored with inverted logic: a missing/empty value means "write
 * the schema", so existing installs keep their current behaviour and the
 * admin checkbox reads as ticked by default.
 *
 * @var string
 */
const META_SUPPRESS_FANCY_PAGE_PRODUCT_SCHEMA = '_suppress_fancy_page_product_schema';

/**
 * Name of the (positively-worded) form field backing
 * {@see META_SUPPRESS_FANCY_PAGE_PRODUCT_SCHEMA} in the product meta box.
 *
 * @var string
 */
const FIELD_WRITE_FANCY_PAGE_PRODUCT_SCHEMA = 'pp_fpp_write_product_schema';

/**
 * Whether to convert a non-numeric `add-to-cart` request value (a SKU) to a
 * product ID before WooCommerce processes it.
 *
 * @var bool
 */
const CONVERT_ADD_TO_CART_SKU_TO_ID = true;

/**
 * HTTP status code used by the (currently disabled) product-to-page redirect.
 *
 * @var int
 */
const REDIRECT_HTTP_CODE = 301;

/**
 * GitHub repository (owner/name) checked for plugin updates.
 *
 * @var string
 */
const UPDATER_GITHUB_REPO = 'headwalluk/fancy-product-page';

/**
 * Lifetime of the cached GitHub release lookup, in seconds.
 *
 * @var int
 */
const UPDATER_CACHE_TTL = 12 * HOUR_IN_SECONDS;

/**
 * Transient key for the cached GitHub release data.
 *
 * @var string
 */
const UPDATER_CACHE_KEY = 'pp_fpp_github_release';
