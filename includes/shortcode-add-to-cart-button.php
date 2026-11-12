<?php
/**
 * `[add_to_cart_btn]` shortcode.
 *
 * @package Fancy_Product_Page
 * @since 1.0.0
 */

namespace Fancy_Product_Page;

// Exit if accessed directly.
defined( 'ABSPATH' ) || die();

/**
 * Render a themed "Add to cart" button for a WooCommerce product.
 *
 * Resolves the product by `id` or `sku` (SKU takes precedence), and outputs a
 * link to `?add-to-cart=<id>&quantity=<qty>` including the display price and an
 * optional quantity. Returns an empty string in the admin, during AJAX, or when
 * WooCommerce is unavailable.
 *
 * @since 1.0.0
 *
 * @param array|string $atts Shortcode attributes: `id` (int), `sku` (string),
 *                           `qty` (int, default 1).
 * @return string The button HTML, or an empty string.
 */
function do_shortcode_add_to_cart_btn( mixed $atts ): string {
	$html = '';

	if ( is_admin() || wp_doing_ajax() ) {
		// Do nothing.
	} elseif ( ! is_woocommerce_available() ) {
		// WooCommerce is not active.
	} else {
		$args = shortcode_atts(
			array(
				'id'  => 0,
				'sku' => '',
				'qty' => 1,
			),
			is_array( $atts ) ? $atts : array()
		);

		$args['sku'] = is_scalar( $args['sku'] ) ? (string) $args['sku'] : '';
		$args['id']  = is_numeric( $args['id'] ) ? (int) $args['id'] : 0;
		$args['qty'] = is_numeric( $args['qty'] ) ? max( 1, (int) $args['qty'] ) : 1;

		if ( ! empty( $args['sku'] ) ) {
			$args['id'] = (int) wc_get_product_id_by_sku( $args['sku'] );
		}

		$product = null;
		if ( ! empty( $args['id'] ) ) {
			$product = wc_get_product( $args['id'] );
		}

		$display_price = '';
		if ( $product instanceof \WC_Product ) {
			$raw_price = wc_get_price_to_display(
				$product,
				array(
					'qty'             => $args['qty'],
					'display_context' => 'shop',
				)
			);

			$display_price = wc_price( $raw_price );
		}

		$display_qty = '';
		if ( $args['qty'] > 1 ) {
			$display_qty = sprintf( '<span class="product-qty">%d</span>', $args['qty'] );
		}

		if ( ! $product instanceof \WC_Product ) {
			$html .= sprintf( '<a href="#">%s</a>', esc_html__( 'Failed to determine the product', 'fancy-product-page' ) );
		} else {
			$html .= sprintf(
				'<a href="?add-to-cart=%s&quantity=%d" class="button" rel="nofollow"><span class="product-name">%s</span>%s%s</a>',
				esc_attr( $args['id'] ),
				$args['qty'],
				esc_html_x( 'Add to cart', 'verb; button label on a shop button', 'fancy-product-page' ),
				// wc_price() escapes its own output; wp_kses_post() would strip its <bdi>.
				$display_price,
				$display_qty
			);
		}
	}

	return $html;
}
add_shortcode( 'add_to_cart_btn', '\\Fancy_Product_Page\\do_shortcode_add_to_cart_btn' );
