# Hooks and filters

WordPress / WooCommerce hooks this plugin consumes, and the filters it provides for customisation. Callbacks are registered in `Plugin::run()` (`includes/class-plugin.php`) unless the table says otherwise; see [architecture](architecture.md#where-hooks-are-registered).

The filters, the `has_fancy_product_page()` helper, the `[add_to_cart_btn]` shortcode and the `?add-to-cart=<SKU>` URL form are public contracts: renaming or removing any of them breaks sites that use them.

## Hooks consumed

| Hook | Type | Callback | Purpose |
| --- | --- | --- | --- |
| `init` | action | `Plugin::load_textdomain()` | Load translations from the bundled `languages/` directory. |
| `post_type_link` | filter | `Plugin::override_product_permalink()` | Rewrite a product's permalink to its associated fancy page. |
| `wp` | action | `Plugin::late_init()` | On a fancy product page (any post type in `get_fancy_page_post_types()`), generate WooCommerce product structured data for the owning product, unless that product has opted out. |
| `woocommerce_structured_data_type_for_page` | filter | `Plugin::structured_data_types_for_page()` | Add `product` to the page's structured-data types so the JSON-LD is output. |
| `wp_loaded` (priority 19) | action | `Plugin::add_to_cart_action()` | Convert a non-numeric `add-to-cart` value (SKU) to a product ID. Registered only when `CONVERT_ADD_TO_CART_SKU_TO_ID` is `true`. |
| `admin_init` | action | `Plugin::admin_init()` | Register admin asset hooks, instantiate the product meta box, handle settings save. |
| `admin_enqueue_scripts` | action | `Admin_Hooks::admin_enqueue_scripts()` | Enqueue `assets/fpp-admin.css` on the product add/edit/list screens. |
| `add_meta_boxes` | action | `Product_Meta_Box::register_meta_box()` | Register the "Fancy Product Page" meta box on the `product` post type. Registered by the `Product_Meta_Box` constructor. |
| `save_post` | action | `Product_Meta_Box::save()` | Persist (or delete) the chosen page ID and the structured-data opt-out on the product. Registered by the `Product_Meta_Box` constructor. |
| `template_redirect` | action | `Plugin::maybe_redirect_to_fancy_page()` | 301-redirect the canonical `/product/...` URL to the product's fancy page. |
| `add_shortcode( 'add_to_cart_btn' )` | shortcode | `do_shortcode_add_to_cart_btn()` | Themed add-to-cart button (see [the shortcode reference](../shortcode.md)). Registered when `includes/shortcode-add-to-cart-button.php` loads. |
| `pre_set_site_transient_update_plugins` | filter | `Github_Updater::check_for_update()` | Offer a newer GitHub release as a plugin update. Registered by the `Github_Updater` constructor, on admin, cron and WP-CLI requests only. |
| `plugins_api` | filter | `Github_Updater::plugin_info()` | Fill the "View details" modal from the GitHub release. Registered by the `Github_Updater` constructor. |
| `upgrader_process_complete` | action | `Github_Updater::clear_cache()` | Clear the cached release data after this plugin updates. Registered by the `Github_Updater` constructor. |

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

### `fancy_product_page_updater_enabled`

Controls whether the plugin checks its GitHub releases for updates. Return `false` to switch update checks off for this plugin only — useful on staging, or to pin a site to its current version.

- **Default:** `true`
- **Parameter:** `bool $enabled`
- **Return:** `bool`

```php
add_filter( 'fancy_product_page_updater_enabled', '__return_false' );
```

## Theme-facing helper

`has_fancy_product_page( int $product_id ): bool` (global namespace, `functions.php`) — returns `true` if the given product has an associated fancy page. Useful in theme templates.

```php
if ( has_fancy_product_page( get_the_ID() ) ) {
    // ...
}
```
