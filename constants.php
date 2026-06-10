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
