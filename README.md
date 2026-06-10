# Fancy Product Page

![WordPress](https://img.shields.io/badge/WordPress-6.0%2B-21759B?logo=wordpress&logoColor=white)
![WooCommerce](https://img.shields.io/badge/WooCommerce-required-7F54B3?logo=woocommerce&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.0%2B-777BB4?logo=php&logoColor=white)
![License](https://img.shields.io/badge/license-GPLv2%20or%20later-blue)

A WordPress / WooCommerce plugin that lets you display a WooCommerce **product** using an ordinary WordPress **Page** (or Post) in place of the default single-product template.

Build a rich, bespoke landing page for a product in the page editor (or a page builder such as GenerateBlocks / Elementor), associate it with a WooCommerce product, and the whole site — menus, related-product links, the shop loop — points at that page while the underlying product is still sold through WooCommerce. The plugin keeps WooCommerce's SEO structured data, permalinks, and add-to-cart behaviour working on the substituted page.

## What it does

- **Page-as-product** — associate any published Page/Post with a product via a "Fancy Product Page" selector on the product editor.
- **Permalink override** — every link WordPress/WooCommerce generates for that product resolves to the chosen page instead of `/product/...`.
- **SEO structured data** — injects WooCommerce product schema.org JSON-LD into the substituted page so search engines still see a product.
- **Add to cart by SKU** — `?add-to-cart=<SKU>` works alongside the usual numeric product ID.
- **`[add_to_cart_btn]` shortcode** — drop a themed add-to-cart button (by id or SKU, with quantity and price) anywhere in the page.

## Requirements

- WordPress 6.0+
- WooCommerce (active)
- PHP 8.0+

## Installation

1. Copy the `fancy-product-page` directory into `wp-content/plugins/`.
2. Activate **Fancy Product Page** in **Plugins**.
3. Edit a WooCommerce product, find the **Fancy Product Page** meta box, and choose the Page/Post to use as its front-end.

## Updates

The plugin self-updates from its GitHub releases. Once installed, new tagged releases appear in **Dashboard → Updates** like any other plugin update. Releases are built automatically by `.github/workflows/release.yml` when a `v*.*.*` tag is pushed. Update checks can be disabled with the `fancy_product_page_updater_enabled` filter.

## Usage

See [docs/usage.md](docs/usage.md) for a step-by-step walkthrough, and [docs/shortcode.md](docs/shortcode.md) for the `[add_to_cart_btn]` reference.

## Developer documentation

The `docs/` directory is the entry point for any developer picking this project up later:

- [docs/architecture.md](docs/architecture.md) — plugin structure, classes, bootstrap, conventions
- [docs/hooks.md](docs/hooks.md) — WordPress/WooCommerce hooks consumed, and filters this plugin provides
- [docs/shortcode.md](docs/shortcode.md) — `[add_to_cart_btn]` attributes and examples
- [docs/usage.md](docs/usage.md) — administrator walkthrough

See also [CLAUDE.md](CLAUDE.md) for an architectural overview oriented at AI-assisted development.

Project history and active milestones: [dev-notes/00-project-tracker.md](dev-notes/00-project-tracker.md).
Per-version release notes: [CHANGELOG.md](CHANGELOG.md).

## License

Licensed under [GPLv2 or later](LICENSE). Maintained by [Headwall Hosting](https://headwall-hosting.com).
