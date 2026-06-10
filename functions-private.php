<?php

namespace Fancy_Product_Page;

defined('WPINC') || die();

function get_settings_controller()
{
    global $pp_fpp_plugin;
    return $pp_fpp_plugin->get_settings_controller();
}

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

function get_fancy_page_post_types(): array
{
    global $pp_fpp_post_types;
    if (is_null($pp_fpp_post_types)) {
        $pp_fpp_post_types = ['page', 'post'];

        $pp_fpp_post_types = (array) apply_filters('fancy_product_page_post_types', $pp_fpp_post_types);
    }

    return $pp_fpp_post_types;
}
