# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Fancy Product Page is a WordPress/WooCommerce plugin that lets a site administrator display a WooCommerce **product** using an ordinary WordPress **Page** (or Post) instead of the default single-product template. It hooks WooCommerce's permalink, structured-data (SEO/schema.org), and add-to-cart machinery so the substituted page behaves like a real product page for links, search engines, and the cart.

The typical use case: build a rich, bespoke landing page for a product in the page editor (or a page builder such as GenerateBlocks/Elementor), associate it with a WooCommerce product, and have the whole site — menus, related-product links, shop loop — point at that page while still selling the underlying product through WooCommerce.

- **Namespace:** `Fancy_Product_Page`
- **Text Domain:** `fancy-product-page`
- **Constant/define prefix:** `PP_FPP_` (e.g. `PP_FPP_DIR`); function prefix `pp_fpp_`; global instance `$pp_fpp_plugin`
- **PHP:** 7.4+ (do NOT use `declare(strict_types=1)` — breaks WordPress/WooCommerce interop)
- **WordPress:** 6.0+, WooCommerce active (the plugin degrades gracefully when `WC()` is absent)
- **No build system** — no npm, no Composer, no bundler. Assets are plain CSS.
- **Self-contained** — as of v1.1.0 the plugin has no external library dependency. It previously bundled the Power Plugins library (`pp-core.php` + `pp-assets/`); the slice it used was reimplemented locally and the library deleted (see `dev-notes/00-project-tracker.md`, Milestone 1).

## Commands

```bash
phpcs                  # Check WordPress coding standards compliance (once phpcs.xml exists)
phpcbf                 # Auto-fix coding standards violations
phpcs includes/        # Check a specific directory
```

The config is in `phpcs.xml` — WordPress standards with prefixes `fancy_product_page`, `pp_fpp`, `Fancy_Product_Page`. The existing code is not yet WPCS-clean (formatting alignment is a tracked milestone). There is no test suite — verification is manual against a live WooCommerce install, or via a PHP load-harness that stubs WordPress functions.

## Architecture

### Entry Point & Bootstrap

`fancy-product-page.php` is the main plugin file. It:

1. Guards with `defined( 'WPINC' ) || die();`
2. Defines `PP_FPP_NAME`, `PP_FPP_VERSION` (const) and path/URL defines (`PP_FPP_DIR`, `PP_FPP_URL`, `PP_FPP_ADMIN_TEMPLATES_DIR`, `PP_FPP_ASSETS_DIR`, `PP_FPP_ASSETS_URL`, …).
3. `require_once`s files in dependency order: `constants.php`, `functions-private.php`, `functions.php`, `includes/form-helpers.php`, the base classes (`class-component.php`, `class-meta-box.php`, `class-settings-core.php`), the concrete classes, then the shortcode file.
4. Instantiates the global `$pp_fpp_plugin = new Fancy_Product_Page\Plugin( PP_FPP_NAME, PP_FPP_VERSION )` and calls `->run()`.

There is **no Composer autoloader** — classes are manually `require_once`d, dependencies first.

### Core Classes (`includes/`)

- **`Plugin`** (`class-plugin.php`) — Main orchestrator, extends `Component`. All hooks are registered in `run()`. Owns the lazily-constructed `Settings` and `Admin_Hooks` instances and the SEO/permalink/cart logic.
- **`Settings`** (`class-settings.php`) — Extends `Settings_Core`. Currently a thin/vestigial shell: it can render and save a settings page but **no admin menu registers it and no options are defined yet**. `save_settings()` and `get_default_value()` are empty placeholders.
- **`Admin_Hooks`** (`class-admin-hooks.php`) — Extends `Component`. Enqueues admin assets on the product edit/list screens.
- **`Product_Meta_Box`** (`class-product-meta-box.php`) — Extends `Meta_Box`. Registers the "Fancy Product Page" meta box on the `product` post type and saves the selected page ID to post meta.

- **`Component`** (`class-component.php`) — minimal base storing name/version.
- **`Settings_Core`** (`class-settings-core.php`) — base settings controller: type-safe option get/set and render helpers.
- **`Meta_Box`** (`class-meta-box.php`) — base meta-box class: nonce rendering and save-time verification.

(`Component`, `Settings_Core`, `Meta_Box`, and the helpers in `form-helpers.php` were previously provided by `pp-core.php`; they are now local — see Milestone 1 in the tracker.)

### What the plugin actually does (functional map)

The behaviour is driven by hooks registered in `Plugin::run()`:

1. **Permalink override** — `post_type_link` filter → `override_product_permalink()`. If a product has an associated fancy page, every link WordPress/WooCommerce generates for that product resolves to the fancy page's permalink instead of `/product/...`.
2. **Structured data injection** — `wp` action → `late_init()`. On a singular page that *is* a fancy product page, it finds the product whose `META_FANCY_PRODUCT_PAGE_ID` meta equals the current page ID and calls `WC()->structured_data->generate_product_data( $product )` so the page emits product schema.org JSON-LD.
3. **Structured-data type** — `woocommerce_structured_data_type_for_page` filter → `structured_data_types_for_page()`. Adds `product` to the page's structured-data types so WooCommerce outputs the data generated in step 2.
4. **SKU → ID add-to-cart** — `wp_loaded` (priority 19) → `add_to_cart_action()`, gated by the `CONVERT_ADD_TO_CART_SKU_TO_ID` constant. Lets `?add-to-cart=<SKU>` work by converting a non-numeric `add-to-cart` value to a product ID before WooCommerce processes it.
5. **Add-to-cart shortcode** — `[add_to_cart_btn id="" sku="" qty=""]` (`includes/shortcode-add-to-cart-button.php`). Renders a themed add-to-cart button (resolves product by id or SKU, shows price and quantity) for embedding inside the fancy page.
6. **Admin meta box** — `Product_Meta_Box` adds a "Fancy Product Page" selector to the product editor. The dropdown lists eligible pages/posts; saving writes the chosen ID to `META_FANCY_PRODUCT_PAGE_ID`, or deletes the meta when "Standard WooCommerce product page" (0) is chosen.

> **Disabled code:** `maybe_redirect_to_fancy_page()` (a `template_redirect` 301 from the canonical product URL to the fancy page) exists but is **not** hooked. `output_structured_data()` is dead code. Both are candidates for cleanup during the refactor.

### Constants (`constants.php`)

All magic strings live in `constants.php` under the `Fancy_Product_Page` namespace:

| Constant | Value | Purpose |
| --- | --- | --- |
| `POST_TYPE_PRODUCT` | `product` | WooCommerce product post type |
| `META_FANCY_PRODUCT_PAGE_ID` | `_fancy_product_page` | Post-meta key storing the chosen page ID on a product |
| `CONVERT_ADD_TO_CART_SKU_TO_ID` | `true` | Enable `?add-to-cart=<SKU>` support |
| `REDIRECT_HTTP_CODE` | `301` | Status used by the (currently disabled) redirect |

Convention going forward: `META_` for post-meta keys, `OPT_` for option keys, `DEF_` for defaults (mirrors bullfix-erp).

### Public / Private functions

- `functions.php` — public, theme-facing helpers in the **global** namespace (e.g. `has_fancy_product_page( int $product_id ): bool`).
- `functions-private.php` — namespaced internal helpers: `get_settings_controller()`, `get_fancy_product_page_id()`, `get_fancy_page_post_types()`.

### Extensibility (filters provided)

- `fancy_product_page_post_types` — array of post types eligible to act as a fancy page. Default `['page', 'post']`.

## Key Conventions

- Register all hooks in `Plugin::run()`; implement the callbacks in the relevant class.
- Use constants from `constants.php` — never hardcode meta keys, post types, or magic values.
- Resolve a product's fancy page only through `get_fancy_product_page_id()`; don't read the meta key directly elsewhere.
- Degrade gracefully when WooCommerce is absent — guard with `function_exists( 'WC' )` / `is_woocommerce_available()`.
- Admin templates live in `admin-templates/` and are `include`d from within a class method that has set up `$post` / `$settings` in scope.
- Security: nonce verification (handled by the `Meta_Box` / `Settings_Core` base classes), `manage_options` capability checks, sanitize input, escape output.
- Keep theme-facing helpers in the global namespace (`functions.php`); keep internals namespaced (`functions-private.php`).

> **Style note:** the existing code uses tight formatting (`defined('WPINC')`, short array syntax, no inner-paren spacing) rather than full WordPress-Coding-Standards spacing. Aligning to WPCS is a tracked milestone; match the surrounding file's existing style until then.

## Commit Messages

```
type: brief description

- Detail 1
- Detail 2
```

Types: `feat:` `fix:` `refactor:` `chore:` `docs:` `style:` `test:`

## Reference Files

- `dev-notes/00-project-tracker.md` — Current milestones, roadmap, and refactor plan.
- `docs/` — Developer docs: `architecture.md`, `hooks.md`, `shortcode.md`, `usage.md`.
- `README.md` / `readme.txt` / `CHANGELOG.md` — Release documentation.
- `constants.php` — Single source of truth for meta keys and configuration constants.
