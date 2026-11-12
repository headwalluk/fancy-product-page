<?php
/**
 * Private helper functions. Internal to the plugin: sites should use the hooks
 * and helpers in docs/developers/hooks-and-filters.md instead.
 *
 * @package Fancy_Product_Page
 * @since 1.0.0
 */

namespace Fancy_Product_Page;

// Exit if accessed directly.
defined( 'ABSPATH' ) || die();

/**
 * Resolve the fancy page/post ID associated with a product.
 *
 * With no product ID, uses the current product on a singular product request.
 * Returns 0 when WooCommerce is unavailable, the product is invalid, no mapping
 * exists, the mapped post isn't an eligible type, or it isn't published.
 *
 * @since 1.0.0
 *
 * @param int $product_id WooCommerce product ID. Defaults to the current product.
 * @return int The associated page/post ID, or 0 if none.
 */
function get_fancy_product_page_id( int $product_id = 0 ): int {
	$post_id = 0;

	if ( 0 === $product_id && is_singular( POST_TYPE_PRODUCT ) ) {
		$product_id = (int) get_the_ID();
	}

	$fancy_product_page_id = $product_id > 0 ? (int) get_post_meta( $product_id, META_FANCY_PRODUCT_PAGE_ID, true ) : 0;

	if ( ! is_woocommerce_available() ) {
		// WooCommerce is not active.
	} elseif ( $product_id <= 0 ) {
		// No product to look up.
	} elseif ( POST_TYPE_PRODUCT !== get_post_type( $product_id ) ) {
		// Not a product.
	} elseif ( $fancy_product_page_id <= 0 ) {
		// No fancy page mapped.
	} elseif ( ! in_array( get_post_type( $fancy_product_page_id ), get_fancy_page_post_types(), true ) ) {
		// Mapped post is not an eligible fancy-page type.
	} elseif ( 'publish' !== get_post_status( $fancy_product_page_id ) ) {
		// A draft, private or trashed page would send visitors to a page they can't see.
	} else {
		$post_id = $fancy_product_page_id;
	}

	return $post_id;
}

/**
 * Check whether product structured data should be kept off this product's fancy page.
 *
 * @since 1.3.0
 *
 * @param int $product_id WooCommerce product ID.
 * @return bool True when the schema should be suppressed.
 */
function is_fancy_page_product_schema_suppressed( int $product_id ): bool {
	$is_suppressed = false;

	if ( $product_id > 0 ) {
		$is_suppressed = ! empty( get_post_meta( $product_id, META_SUPPRESS_FANCY_PAGE_PRODUCT_SCHEMA, true ) );
	}

	return $is_suppressed;
}

/**
 * Get the post types eligible to act as a fancy product page.
 *
 * Passed through the fancy_product_page_post_types filter once per request.
 * Non-string entries are dropped; an empty or non-array result falls back to the defaults.
 *
 * @since 1.0.0
 *
 * @return string[] Post type slugs.
 */
function get_fancy_page_post_types(): array {
	static $post_types = null;

	if ( null === $post_types ) {
		/**
		 * Filter the post types that can be chosen as a product's fancy page.
		 *
		 * @since 1.0.0
		 *
		 * @param string[] $post_types Post type slugs. Default array( 'page', 'post' ).
		 */
		$filtered = apply_filters( 'fancy_product_page_post_types', DEFAULT_FANCY_PAGE_POST_TYPES );

		// 1.x cast the result to an array, so a single post type name as a string still works.
		$post_types = is_string( $filtered ) ? array( $filtered ) : $filtered;
		$post_types = is_array( $post_types ) ? array_values( array_filter( $post_types, 'is_string' ) ) : array();

		if ( empty( $post_types ) ) {
			log_error( 'fancy_product_page_post_types returned no post type names; using page and post.' );
			$post_types = DEFAULT_FANCY_PAGE_POST_TYPES;
		}
	}

	return $post_types;
}

/**
 * Log a genuine failure. Always logs, whatever WP_DEBUG says.
 *
 * @since 1.5.0
 *
 * @param string $message Message to log.
 */
function log_error( string $message ): void {
	error_log( 'Fancy Product Page: ' . $message ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Errors must reach the server log without WP_DEBUG.
}
