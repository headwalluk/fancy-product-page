<?php

/**
 * Public, theme-facing helper functions (global namespace).
 *
 * @package Fancy_Product_Page
 */

defined('WPINC') || die();

/**
 * Does the given product have an associated fancy product page?
 *
 * @param int $product_id WooCommerce product ID.
 *
 * @return bool True if the product is mapped to a fancy page/post.
 */
function has_fancy_product_page(int $product_id): bool
{
    return !empty(\Fancy_Product_Page\get_fancy_product_page_id($product_id));
}
