<?php

/**
 * Admin template: the "Fancy Product Page" meta box body.
 *
 * Rendered by {@see Product_Meta_Box::render()}, which provides `$post` (the
 * product being edited) and `$settings` in scope. Outputs a select control
 * listing the eligible pages/posts for this product, plus the structured-data
 * opt-out checkbox.
 *
 * @package Fancy_Product_Page
 */

namespace Fancy_Product_Page;

defined('WPINC') || die();

echo '<p class="pp-form-row">';

$all_pages = [
    '0' => _x('Standard WooCommerce product page', 'dropdown option; use no fancy page', 'fancy-product-page')
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
        $raw_post_title = _x('No title', 'placeholder for a page/post with an empty title', 'fancy-product-page');
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
    _x('Fancy product page', 'meta box field label; the page that replaces this product page', 'fancy-product-page'),
    $all_pages,
    intval(get_post_meta($post->ID, META_FANCY_PRODUCT_PAGE_ID, true)),
    __('Choose the page or post to use for this product in the frontend.', 'fancy-product-page')
);

echo '</p>';

echo '<p class="pp-form-row">';

/*
 * Reverse logic: the meta records suppression, so an absent/empty value means
 * "write the schema" and the checkbox shows as ticked. Existing products (which
 * have no such meta) therefore keep their current behaviour without anyone
 * having to touch this control.
 */
echo pp_fpp_get_checkbox_html(
    FIELD_WRITE_FANCY_PAGE_PRODUCT_SCHEMA, // ...
    __('Write the structured data to the fancy product page?', 'fancy-product-page'),
    !is_fancy_page_product_schema_suppressed($post->ID),
    __('Leave this checked unless something else on the page (an SEO plugin, or hand-written markup) already outputs product schema.org data.', 'fancy-product-page')
);

echo '</p>';
