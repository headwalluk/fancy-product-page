# Shortcode: `[add_to_cart_btn]`

Renders a themed "Add to cart" link for a WooCommerce product, including the product's display price and (optionally) a quantity. Intended for embedding a buy button inside a fancy product page.

The shortcode outputs nothing in the admin, during AJAX, or when WooCommerce is unavailable.

## Attributes

| Attribute | Type | Default | Description |
| --- | --- | --- | --- |
| `id` | int | `0` | WooCommerce product ID. |
| `sku` | string | `''` | Product SKU. If provided, it is resolved to a product ID and **takes precedence over `id`**. |
| `qty` | int | `1` | Quantity to add. When greater than `1`, the quantity is shown in the button and the price reflects the quantity. |

You must supply either `id` or `sku`. If the product cannot be resolved (an unknown ID or SKU), the button renders a "Failed to determine the product" placeholder link.

## Output

The button links to `?add-to-cart=<id>&quantity=<qty>` with `class="button" rel="nofollow"` and contains:

- the "Add to cart" label,
- the display price (`wc_price()` of `wc_get_price_to_display()` for the given quantity),
- a `<span class="product-qty">` when `qty > 1`.

## Examples

```text
[add_to_cart_btn id="123"]
```

```text
[add_to_cart_btn sku="WIDGET-BLUE"]
```

```text
[add_to_cart_btn sku="WIDGET-BLUE" qty="3"]
```

## Notes

- When you give a `sku`, the button's link still uses the numeric product ID.
- Hand-written links can use a SKU directly — `?add-to-cart=WIDGET-BLUE` — and the plugin converts it to the product ID before WooCommerce adds it to the cart.
- Prices use WooCommerce's display context, so tax display follows your store settings.
