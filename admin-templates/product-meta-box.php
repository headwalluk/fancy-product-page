<?php

/**
 * Admin template: the "Fancy Product Page" meta box body.
 *
 * Rendered by {@see Product_Meta_Box::render()}, which provides `$post` (the
 * product being edited) and `$settings` in scope. Outputs a select control
 * listing the eligible pages/posts for this product.
 *
 * @package Fancy_Product_Page
 */

namespace Fancy_Product_Page;

defined('WPINC') || die();

echo '<p class="pp-form-row">';

$all_pages = [
    '0' => __('Standard WooCommerce product page', 'fancy-product-page')
];

$query = [
    'post_type' => get_fancy_page_post_types(),
    'post_status' => 'publish',
    'orderby' => 'title',
    'order' => 'ASC',
    'numberposts' => -1
];

$pages = get_posts($query);
foreach ($pages as $page) {
    $page_id = $page->ID;
    if (empty(($raw_post_title = get_the_title($page)))) {
        $raw_post_title = __('No title', 'fancy-product-page');
    }

    $all_pages[strval($page_id)] = sprintf(
        '%s (%s, %d)', // ...
        $raw_post_title,
        get_post_type($page_id),
        $page_id
    );
}

echo pp_fpp_get_select_list_html(
    META_FANCY_PRODUCT_PAGE_ID, // ...
    __('Fancy product page', 'fancy-product-page'),
    $all_pages,
    intval(get_post_meta($post->ID, META_FANCY_PRODUCT_PAGE_ID, true)),
    __('Choose a page (not a post) to use for this product in the frontend.', 'fancy-product-page')
);

echo '</p>';
