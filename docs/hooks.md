# Hooks

WordPress / WooCommerce hooks this plugin consumes, and the filters it provides for customisation. All callbacks are registered in `Plugin::run()` (`includes/class-plugin.php`) unless noted.

## Hooks consumed

| Hook | Type | Callback | Purpose |
| --- | --- | --- | --- |
| `post_type_link` | filter | `Plugin::override_product_permalink()` | Rewrite a product's permalink to its associated fancy page. |
| `wp` | action | `Plugin::late_init()` | On a fancy product page, generate WooCommerce product structured data for the owning product. |
| `woocommerce_structured_data_type_for_page` | filter | `Plugin::structured_data_types_for_page()` | Add `product` to the page's structured-data types so the JSON-LD is output. |
| `wp_loaded` (priority 19) | action | `Plugin::add_to_cart_action()` | Convert a non-numeric `add-to-cart` value (SKU) to a product ID. Registered only when `CONVERT_ADD_TO_CART_SKU_TO_ID` is `true`. |
| `admin_init` | action | `Plugin::admin_init()` | Register admin asset hooks, instantiate the product meta box, handle settings save. |
| `admin_enqueue_scripts` | action | `Admin_Hooks::admin_enqueue_scripts()` | Enqueue `assets/fpp-admin.css` on the product add/edit/list screens. |
| `add_meta_boxes` | action | `Product_Meta_Box::register_meta_box()` | Register the "Fancy Product Page" meta box on the `product` post type. |
| `save_post` | action | `Product_Meta_Box::save()` | Persist (or delete) the chosen page ID on the product. |
| `add_shortcode( 'add_to_cart_btn' )` | shortcode | `do_shortcode_add_to_cart_btn()` | Themed add-to-cart button (see [shortcode.md](shortcode.md)). |

> **Not enabled by default:** `Plugin::maybe_redirect_to_fancy_page()` (a `template_redirect` 301 from the canonical product URL to the fancy page) exists in the code but is not hooked.

## Filters provided

### `fancy_product_page_post_types`

Controls which post types may be used as a fancy product page (i.e. what appears in the meta box dropdown and what counts as a valid fancy page).

- **Default:** `[ 'page', 'post' ]`
- **Parameter:** `array $post_types`
- **Return:** `array`

```php
// Allow a custom 'landing' post type to be used as a fancy product page.
add_filter( 'fancy_product_page_post_types', function ( $post_types ) {
    $post_types[] = 'landing';
    return $post_types;
} );
```

## Theme-facing helper

`has_fancy_product_page( int $product_id ): bool` (global namespace, `functions.php`) — returns `true` if the given product has an associated fancy page. Useful in theme templates.

```php
if ( has_fancy_product_page( get_the_ID() ) ) {
    // ...
}
```
