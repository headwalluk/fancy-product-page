=== Fancy Product Page ===
Contributors: Headwall
Tags: woocommerce, product page, landing page, seo, page builder
Requires at least: 6.0
Tested up to: 6.9
Requires PHP: 8.0
Stable tag: 1.3.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Display a WooCommerce product using an ordinary WordPress Page or Post in place of the default single-product template, with SEO and add-to-cart kept intact.

== Description ==

Fancy Product Page lets you replace a WooCommerce product's front-end with any WordPress Page or Post. Build a bespoke landing page in the block editor or a page builder, associate it with a product, and the whole site links to that page instead of the standard `/product/...` template — while the product continues to sell through WooCommerce.

The plugin keeps the important WooCommerce behaviour working on the substituted page: product permalinks resolve to it, SEO structured data is injected so search engines still recognise a product, and add-to-cart works by SKU as well as by product ID.

= Features =

* **Page-as-product**
  * Associate any published Page or Post with a WooCommerce product
  * Selected via a "Fancy Product Page" meta box on the product editor
  * Filterable list of eligible post types (`fancy_product_page_post_types`)

* **Permalink override**
  * Product links across the site (menus, related products, shop loop) resolve to the chosen page

* **SEO / structured data**
  * Injects WooCommerce product schema.org JSON-LD into the substituted page
  * Registers `product` as a structured-data type for the page

* **Cart integration**
  * `?add-to-cart=<SKU>` support (converts SKU to product ID before WooCommerce processes it)
  * `[add_to_cart_btn]` shortcode for a themed add-to-cart button with price and quantity

= Technical Features =

* Self-contained — no external libraries, no build step (plain CSS)
* Degrades gracefully when WooCommerce is not active
* Namespaced PHP, manual dependency-ordered class loading
* Extensible via WordPress filters

== Installation ==

1. Upload the `fancy-product-page` folder to `/wp-content/plugins/`.
2. Activate the plugin through the **Plugins** menu in WordPress.
3. Ensure WooCommerce is active.
4. Edit a product, open the **Fancy Product Page** meta box, and choose the Page/Post to use as its front-end.

== Frequently Asked Questions ==

= Does the original product URL still work? =

The product itself still exists in WooCommerce. Links generated for the product resolve to your chosen page. An optional 301 redirect of the canonical product URL to the fancy page exists in the code but is not enabled by default.

= Can I use a page builder? =

Yes. The "fancy page" is just a normal WordPress Page or Post, so any block editor or page-builder layout works. Use the `[add_to_cart_btn]` shortcode to add a buy button.

= What happens if WooCommerce is deactivated? =

The plugin checks for WooCommerce before doing any product work and degrades gracefully, so it will not fatal-error.

== Changelog ==

= 1.3.0 =
* Added a "Write the structured data to the fancy product page?" checkbox to the product meta box, so schema.org output can be turned off per product when an SEO plugin already provides it. Ticked by default; existing products are unaffected.
* Fixed: structured data was only generated when the fancy page was a Page. Products mapped to a Post (or a post type added via the `fancy_product_page_post_types` filter) now emit product schema correctly.
* Fixed: the meta box help text wrongly said a post could not be used.
* Improved translations: short, ambiguous labels now carry translator context, and all 8 locales have been regenerated.
* Deprecated the unused `Plugin::output_structured_data()` method.

= 1.2.0 =
* Added GitHub-based automatic updates: new tagged releases are offered through the standard WordPress plugin-update flow. Toggle with the `fancy_product_page_updater_enabled` filter.
* Added a GitHub Actions release workflow that builds and publishes the release zips on `v*.*.*` tags.
* Added internationalisation: the `fancy-product-page` text domain is loaded from the bundled `languages/` directory (.pot + 8 locales).
* Raised the minimum PHP requirement to 8.0.

= 1.1.0 =
* Removed the bundled Power Plugins library (`pp-core.php` / `pp-assets/`); the plugin is now fully self-contained.
* Corrected the license to GPLv2 or later, and added standard plugin headers (Requires PHP / WooCommerce).
* Added developer documentation, README, and project tracker.

= 1.0.2 =
* Baseline release (bundled the Power Plugins library).

See CHANGELOG.md for the full history.
