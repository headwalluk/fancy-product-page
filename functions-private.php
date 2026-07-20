<?php

/**
 * Private, namespaced helper functions for internal plugin use.
 *
 * @package Fancy_Product_Page
 */

namespace Fancy_Product_Page;

defined('WPINC') || die();

/**
 * Get the plugin's settings controller from the global plugin instance.
 *
 * @return Settings The settings controller.
 */
function get_settings_controller()
{
    global $pp_fpp_plugin;
    return $pp_fpp_plugin->get_settings_controller();
}

/**
 * Resolve the fancy page/post ID associated with a product.
 *
 * When no product ID is given and the current request is a singular product,
 * the current product is used. Returns 0 when WooCommerce is unavailable, the
 * product is invalid, no mapping exists, or the mapped post type is not an
 * allowed fancy-page type.
 *
 * @param int $product_id WooCommerce product ID. Defaults to the current product.
 *
 * @return int The associated page/post ID, or 0 if none.
 */
function get_fancy_product_page_id(int $product_id = 0): int
{
    $post_id = 0;

    if ($product_id == 0 && is_singular('product')) {
        $product_id = get_the_ID();
    }

    if (!function_exists('WC')) {
        // ...
    } elseif ($product_id <= 0) {
        // Invalid product id
    } elseif (get_post_type($product_id) != 'product') {
        // ...
    } elseif (($fancy_product_page_id = intval(get_post_meta($product_id, META_FANCY_PRODUCT_PAGE_ID, true))) < 0) {
        // ...
        // } elseif (!in_array(get_post_type($fancy_product_page_id), ['page', 'post'])) {
    } elseif (!in_array(get_post_type($fancy_product_page_id), get_fancy_page_post_types())) {
        // ...
    } else {
        $post_id = $fancy_product_page_id;
    }

    return $post_id;
}

/**
 * Should product structured data be kept off this product's fancy page?
 *
 * Reverse logic by design: the {@see META_SUPPRESS_FANCY_PAGE_PRODUCT_SCHEMA}
 * meta only exists when an administrator has opted out, so products saved
 * before this option existed continue to emit their schema.
 *
 * @param int $product_id WooCommerce product ID.
 *
 * @return bool True when the schema should be suppressed.
 */
function is_fancy_page_product_schema_suppressed(int $product_id): bool
{
    if ($product_id <= 0) {
        return false;
    }

    return !empty(get_post_meta($product_id, META_SUPPRESS_FANCY_PAGE_PRODUCT_SCHEMA, true));
}

/**
 * Get the post types eligible to act as a fancy product page.
 *
 * Defaults to `page` and `post`, and is filterable via the
 * `fancy_product_page_post_types` filter. The result is memoised in a global
 * for the duration of the request.
 *
 * @return array List of post type slugs.
 */
function get_fancy_page_post_types(): array
{
    global $pp_fpp_post_types;
    if (is_null($pp_fpp_post_types)) {
        $pp_fpp_post_types = ['page', 'post'];

        $pp_fpp_post_types = (array) apply_filters('fancy_product_page_post_types', $pp_fpp_post_types);
    }

    return $pp_fpp_post_types;
}
