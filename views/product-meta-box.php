<?php
/**
 * Admin template: the "Fancy Product Page" meta box body.
 *
 * Rendered by Product_Meta_Box::render(). Outputs a select control listing the
 * eligible pages/posts for this product, plus the structured-data opt-out checkbox.
 *
 * @package Fancy_Product_Page
 * @since 1.0.0
 *
 * @var \WP_Post $post The product being edited.
 */

namespace Fancy_Product_Page;

// Exit if accessed directly.
defined( 'ABSPATH' ) || die();

echo '<p class="pp-form-row">';

$fancy_product_page_all_pages = array(
	'0' => _x( 'Standard WooCommerce product page', 'dropdown option; use no fancy page', 'fancy-product-page' ),
);

$fancy_product_page_query = array(
	'post_type'   => get_fancy_page_post_types(),
	'post_status' => 'publish',
	'orderby'     => 'title',
	'order'       => 'ASC',
	'numberposts' => -1,
);

$fancy_product_page_candidate_posts = get_posts( $fancy_product_page_query );
foreach ( $fancy_product_page_candidate_posts as $fancy_product_page_candidate_post ) {
	$fancy_product_page_candidate_id    = $fancy_product_page_candidate_post->ID;
	$fancy_product_page_candidate_title = get_the_title( $fancy_product_page_candidate_post );
	if ( '' === $fancy_product_page_candidate_title ) {
		$fancy_product_page_candidate_title = _x( 'No title', 'placeholder for a page/post with an empty title', 'fancy-product-page' );
	}

	$fancy_product_page_all_pages[ strval( $fancy_product_page_candidate_id ) ] = sprintf(
		'%s (%s, %d)',
		$fancy_product_page_candidate_title,
		get_post_type( $fancy_product_page_candidate_id ),
		$fancy_product_page_candidate_id
	);
}

echo get_select_list_html(
	META_FANCY_PRODUCT_PAGE_ID,
	_x( 'Fancy product page', 'meta box field label; the page that replaces this product page', 'fancy-product-page' ),
	$fancy_product_page_all_pages,
	(string) intval( get_post_meta( $post->ID, META_FANCY_PRODUCT_PAGE_ID, true ) ),
	__( 'Choose the page or post to use for this product in the frontend.', 'fancy-product-page' )
);

echo '</p>';

echo '<p class="pp-form-row">';

// Ticked unless the product has opted out: the meta records suppression.
echo get_checkbox_html(
	FIELD_WRITE_FANCY_PAGE_PRODUCT_SCHEMA,
	__( 'Write the structured data to the fancy product page?', 'fancy-product-page' ),
	! is_fancy_page_product_schema_suppressed( $post->ID ),
	__( 'Leave this checked unless something else on the page (an SEO plugin, or hand-written markup) already outputs product schema.org data.', 'fancy-product-page' )
);

echo '</p>';
