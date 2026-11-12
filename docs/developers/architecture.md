# Architecture

How the plugin is wired together. For the hooks it consumes and provides, see [hooks and filters](hooks-and-filters.md).

## Bootstrap

`fancy-product-page.php` is the only entry point. It:

1. Defines `FANCY_PRODUCT_PAGE_VERSION`, `FANCY_PRODUCT_PAGE_FILE`, `FANCY_PRODUCT_PAGE_PATH`, `FANCY_PRODUCT_PAGE_URL` and `FANCY_PRODUCT_PAGE_BASENAME`.
2. Defines the 1.x names as deprecated aliases (see below).
3. `require_once`s, in order: `constants.php`, `functions-private.php`, `functions.php`, `includes/form-helpers.php`, the classes, then `includes/shortcode-add-to-cart-button.php`.
4. On admin, cron and WP-CLI requests only, loads `includes/class-github-updater.php` and creates the `Github_Updater`.
5. Calls `Fancy_Product_Page\Plugin::instance()->run()`. The instance is also kept in the global `$pp_fpp_plugin`, for code that reads it.

There is no autoloader and no build step. Assets are plain CSS.

### Deprecated constants

1.5.0 renamed the global constants to match the plugin's hook prefix. The old names still work and are removed in 2.0.0:

- `PP_FPP_VERSION`, `PP_FPP_FILE`, `PP_FPP_BASENAME`, `PP_FPP_URL`: use the `FANCY_PRODUCT_PAGE_` versions
- `PP_FPP_DIR`: use `FANCY_PRODUCT_PAGE_PATH`
- `PP_FPP_NAME`, `PP_FPP_ADMIN_TEMPLATES_DIR`, `PP_FPP_ASSETS_DIR`, `PP_FPP_ASSETS_URL`, `PP_FPP_PUBLIC_TEMPLATES_DIR`: no replacement. Build paths from `FANCY_PRODUCT_PAGE_PATH` and `FANCY_PRODUCT_PAGE_URL`

## Where hooks are registered

`Plugin::run()` (`includes/class-plugin.php`) registers the plugin's own behaviour; the callbacks live in the same class. Three components register their own hooks instead:

- `Product_Meta_Box`: `add_meta_boxes_product` and `save_post_product`, from its constructor. `Plugin::admin_init()` creates it, and the `Admin_Hooks` stylesheet loader
- `Github_Updater`: the plugin-update hooks, from its constructor
- The shortcode file: `add_shortcode( 'add_to_cart_btn', … )` when it is loaded

## Files

```
fancy-product-page.php            header, constants, requires, updater, run
constants.php                     meta keys, field and nonce names, style handle, updater repo
functions.php                     global, theme-facing: has_fancy_product_page()
functions-private.php             get_fancy_product_page_id(), get_fancy_page_post_types(),
                                  is_fancy_page_product_schema_suppressed(), log_error()
includes/class-plugin.php         Plugin: hooks, permalinks, redirect, structured data, SKU add-to-cart
includes/class-product-meta-box.php  Product_Meta_Box: render, save, save checks
includes/class-admin-hooks.php    Admin_Hooks: the meta box stylesheet
includes/form-helpers.php         is_woocommerce_available(), get_select_list_html(), get_checkbox_html()
includes/shortcode-add-to-cart-button.php  [add_to_cart_btn]
includes/class-github-updater.php Github_Updater, copied from Quick 2FA
views/product-meta-box.php        the meta box markup
uninstall.php                     deletes the updater cache
```

## GitHub updater

`Github_Updater` is a verbatim copy of the portable updater maintained in the Quick 2FA plugin; only its `namespace` and `@package` lines differ. Fix it there first, then copy it across; never edit it here alone.

It reads the latest GitHub release, offers it through the WordPress update transient when it is newer than the version in the plugin header, and fills the "View details" modal. A successful lookup is cached for 12 hours (`fancy_product_page_github_release`); a failed one backs off for an hour (`fancy_product_page_github_failed`). To force a fresh check:

```bash
wp eval 'Fancy_Product_Page\Github_Updater::delete_cache( "fancy-product-page" );'
```

Each release must attach `fancy-product-page.zip`. `.github/workflows/release.yml` builds it when a `v*.*.*` tag is pushed, and refuses to build unless the plugin header `Version:` and `FANCY_PRODUCT_PAGE_VERSION` both match the tag.

## Data model

Two post-meta keys are stored on a `product` post.

`META_FANCY_PRODUCT_PAGE_ID` (`_fancy_product_page`) — the ID of the Page/Post to use as that product's front-end. Resolve it only through `get_fancy_product_page_id()`; do not read the meta key directly.

`META_SUPPRESS_FANCY_PAGE_PRODUCT_SCHEMA` (`_suppress_fancy_page_product_schema`) — set only when an administrator has opted *out* of writing product structured data to the fancy page. The logic is deliberately inverted: an absent or empty value means "write the schema", so products saved before this option existed keep emitting it, and the admin checkbox ("Write the structured data to the fancy product page?") reads as ticked by default. Read it through `is_fancy_page_product_schema_suppressed()`.

## Request flows

**Admin — choosing a page:** `Product_Meta_Box` registers a meta box on the `product` editor. `views/product-meta-box.php` renders a `<select>` (via `get_select_list_html()`) listing the published pages/posts of the eligible types, and the structured-data checkbox. On save, `Product_Meta_Box::is_saving_meta_box()` checks the nonce, post type, the user's `edit_post` capability, and that it isn't an autosave or revision; the chosen ID is then written to the meta key, or the meta is deleted when "Standard WooCommerce product page" (0) is selected.

**Front end — links:** the `post_type_link` filter rewrites every product permalink to its fancy page, so the whole site links to the page. Only a published fancy page counts: if the page is moved to draft, made private or trashed, the product's links and its own URL go back to the standard product page.

**Front end — the product's own URL:** on `template_redirect`, a request for a product that has a fancy page is 301-redirected (`REDIRECT_HTTP_CODE`) to that page. This catches direct hits and crawls that the permalink filter cannot.

**Front end — the fancy page itself:** on `wp`, if the current request is singular and its post type is one of `get_fancy_page_post_types()`, the plugin finds the owning product and calls `WC()->structured_data->generate_product_data()`; the `woocommerce_structured_data_type_for_page` filter then adds `product` to the page's structured-data types so the JSON-LD is emitted. Both halves are skipped when the product has opted out via `META_SUPPRESS_FANCY_PAGE_PRODUCT_SCHEMA`.

**Front end — cart:** on `wp_loaded` (priority 19, before WooCommerce's handler at 20), a non-numeric `add-to-cart` request value is resolved from SKU to product ID.

## Conventions

The plugin follows Headwall's shared WordPress plugin conventions. What is specific to this plugin is in [CLAUDE.md](../../CLAUDE.md).

- Register hooks in `Plugin::run()`; implement callbacks in the relevant class
- Keep magic strings in `constants.php`
- Theme-facing helpers go in the global namespace (`functions.php`); internals stay namespaced
