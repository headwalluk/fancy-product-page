# Architecture

How the plugin is wired together. For the hooks it consumes and provides, see [hooks.md](hooks.md).

## Bootstrap

`fancy-product-page.php` is the only entry point. It:

1. Guards with `defined( 'WPINC' ) || die();`.
2. Defines `PP_FPP_NAME`, `PP_FPP_VERSION` (const) and path/URL defines: `PP_FPP_DIR`, `PP_FPP_URL`, `PP_FPP_ADMIN_TEMPLATES_DIR`, `PP_FPP_PUBLIC_TEMPLATES_DIR`, `PP_FPP_ASSETS_DIR`, `PP_FPP_ASSETS_URL`.
3. `require_once`s, in dependency order: `constants.php`, `functions-private.php`, `functions.php`, `includes/form-helpers.php`, the base classes (`class-component.php`, `class-meta-box.php`, `class-settings-core.php`), then the concrete classes, then `includes/shortcode-add-to-cart-button.php`.
4. Instantiates a single global `$pp_fpp_plugin = new Fancy_Product_Page\Plugin( PP_FPP_NAME, PP_FPP_VERSION )` and calls `->run()`.

There is no autoloader and no build step. Classes are loaded explicitly, dependencies first; assets are plain CSS.

## Top-level orchestrator: `Plugin`

`includes/class-plugin.php` is the only place that registers WordPress hooks. Its `run()` method wires every hook the plugin uses (see [hooks.md](hooks.md)); the behaviour lives in the same class's callbacks. `Plugin` lazily instantiates `Settings` (in `run()`) and `Admin_Hooks` (via `get_admin_hooks()`).

## Component map

```
Plugin (class-plugin.php) ───────────── extends Component
├── Settings (class-settings.php) ────── extends Settings_Core ── extends Component
├── Admin_Hooks (class-admin-hooks.php)  extends Component
└── Product_Meta_Box (class-product-meta-box.php) ── extends Meta_Box

Base classes (formerly from pp-core.php, now local):
- Component       (class-component.php)      — stores name/version
- Meta_Box        (class-meta-box.php)        — nonce + save-time verification
- Settings_Core   (class-settings-core.php)   — typed option get/set, render helpers

Helpers:
- functions.php          — global, theme-facing: has_fancy_product_page()
- functions-private.php  — namespaced internals: get_settings_controller(),
                           get_fancy_product_page_id(), get_fancy_page_post_types()
- includes/form-helpers.php — is_woocommerce_available(),
                              pp_fpp_get_select_list_html()
```

## Data model

A single post-meta key, `META_FANCY_PRODUCT_PAGE_ID` (`_fancy_product_page`), is stored on a `product` post. Its value is the ID of the Page/Post to use as that product's front-end. Resolve it only through `get_fancy_product_page_id()` — do not read the meta key directly.

## Request flows

**Admin — choosing a page:** `Product_Meta_Box` registers a meta box on the `product` editor. `admin-templates/product-meta-box.php` renders a `<select>` (via `pp_fpp_get_select_list_html()`) listing eligible pages/posts. On save, the chosen ID is written to the meta key, or the meta is deleted when "Standard WooCommerce product page" (0) is selected.

**Front-end — links:** the `post_type_link` filter rewrites every product permalink to its fancy page, so the whole site links to the page.

**Front-end — the fancy page itself:** on `wp`, if the current page is a fancy product page, the plugin finds the owning product and calls `WC()->structured_data->generate_product_data()`; the `woocommerce_structured_data_type_for_page` filter then adds `product` to the page's structured-data types so the JSON-LD is emitted.

**Front-end — cart:** on `wp_loaded` (priority 19), a non-numeric `add-to-cart` request value is resolved from SKU to product ID before WooCommerce processes it.

## Conventions

- Register hooks in `Plugin::run()`; implement callbacks in the relevant class.
- Keep magic strings in `constants.php` (`META_`, `OPT_`, `DEF_`).
- Guard WooCommerce work with `is_woocommerce_available()` / `function_exists( 'WC' )`.
- Theme-facing helpers go in the global namespace (`functions.php`); internals stay namespaced.
- See [CLAUDE.md](../CLAUDE.md) for the full convention list.
