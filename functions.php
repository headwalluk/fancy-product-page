<?php
/**
 * Public, theme-facing helper functions (global namespace).
 *
 * @package Fancy_Product_Page
 * @since 1.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || die();

/**
 * Check whether a product has an associated fancy product page.
 *
 * @since 1.0.0
 *
 * @param int $product_id WooCommerce product ID.
 * @return bool True if the product is mapped to a fancy page/post.
 */
function has_fancy_product_page( int $product_id ): bool { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- Public theme-facing API; renaming would break theme templates.
	return ! empty( \Fancy_Product_Page\get_fancy_product_page_id( $product_id ) );
}
