<?php

namespace Fancy_Product_Page;

defined('WPINC') || die();

function do_shortcode_add_to_cart_btn($atts)
{
    $html = '';

    if (is_admin() || wp_doing_ajax()) {
        // Do nothing.
    } elseif (!is_woocommerce_available()) {
        // ...
    } else {
        $args = shortcode_atts(
            [
                'id' => 0,
                'sku' => '',
                'qty' => 1
            ],
            $atts
        );

        $args['sku'] = strval($args['sku']);
        $args['id'] = intval($args['id']);
        $args['qty'] = intval($args['qty']);

        $product_id = 0;

        if (!empty($args['sku'])) {
            $args['id'] = wc_get_product_id_by_sku($args['sku']);
        }

        $product = null;
        if (!empty($args['id'])) {
            $product = wc_get_product($args['id']);
        }

        $display_price = '';
        if (!empty($product)) {
            $raw_price = wc_get_price_to_display($product, [
                'qty' => $args['qty'],
                'display_context' => 'shop'
            ]);

            $display_price = wc_price($raw_price);
        }

        $display_qty = '';
        if ($args['qty'] > 1) {
            $display_qty = sprintf('<span class="product-qty">%d</span>', $args['qty']);
        }

        if (empty($product)) {
            $html .= sprintf('<a href="#">%s</a>', esc_html__('Failed to determine the product', 'fancy-product-page'));
        } else {
            $html .= sprintf(
                '<a href="?add-to-cart=%s&quantity=%d" class="button" rel="nofollow"><span class="product-name">%s</span>%s%s</a>', // ...
                esc_attr($args['id']),
                $args['qty'],
                esc_html__('Add to cart', 'fancy-product-page'),
                wp_kses_post($display_price),
                wp_kses_post($display_qty)
            );
        }
    }

    return $html;
}
add_shortcode('add_to_cart_btn', '\\Fancy_Product_Page\\do_shortcode_add_to_cart_btn');
