# Changelog

All notable changes to Fancy Product Page will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [Unreleased]

**Requires PHP 8.2 and WordPress 6.2** (from 8.0 and 6.0). Sites below these stop being offered updates.

### Fixed

- **A fancy page in draft, private or the bin still took over the product.** The product's links and its own URL pointed at a page visitors couldn't see. Only a published page counts now; unpublishing it sends the product back to its standard page, and publishing it again restores the fancy page. Introduced in 1.0.0.
- **WooCommerce reported the plugin as incompatible with High-Performance Order Storage**, because it never declared compatibility. It now declares both HPOS and Checkout block compatibility; it reads neither orders nor the checkout. Introduced in 1.0.0.
- Another plugin passing a non-array to `woocommerce_structured_data_type_for_page` caused a fatal error on fancy pages. The value is now passed through untouched. Introduced in 1.0.0.
- The `[add_to_cart_btn]` price lost WooCommerce's `<bdi>` wrapper, which keeps the currency symbol on the correct side in right-to-left languages. Introduced in 1.0.0.
- A `qty` of zero or less in `[add_to_cart_btn]` produced a `quantity=0` link; it now counts as 1. Introduced in 1.0.0.
- The admin stylesheet loaded only for administrators, so shop managers saw the meta box unstyled. It now loads for anyone who can edit products. Introduced in 1.0.0.
- A `fancy_product_page_post_types` filter returning no usable post types left no page eligible. It now falls back to pages and posts, and logs an error.

### Changed

- Global constants renamed to match the plugin's hook prefix: `FANCY_PRODUCT_PAGE_VERSION`, `_FILE`, `_PATH`, `_URL` and `_BASENAME`. The `PP_FPP_*` names still work; see Deprecated.
- `Plugin::instance()` returns the plugin object. The `$pp_fpp_plugin` global still holds it.
- The meta box saves only through `save_post_product`, and also skips revisions.
- The plugin and author links on the Plugins screen now go to the plugin's page and the author's page on headwall-hosting.com.
- The plugin description is now translated; the plugin name, author and links stay in English.

### Deprecated

- The `PP_FPP_*` constants. Removed in 2.0.0. `docs/developers/architecture.md` lists each one's replacement.

### Added

- `uninstall.php`, which deletes the updater cache. Each product's choice of page stays in its meta.
- `SECURITY.md`, and requirements and privacy sections in `docs/usage.md`.

### Removed

- The unused settings layer (`Settings`, `Settings_Core`) and the `Component` and `Meta_Box` base classes, along with `Plugin::get_settings_controller()`, `get_admin_hooks()` and the namespaced `get_settings_controller()`. No settings page ever used them.
- `readme.txt`. `README.md`, `CHANGELOG.md` and `docs/` cover it, and the release workflow no longer checks its stable tag.

---

## [1.4.1] - 2026-11-08

### Changed
- **GitHub updater replaced with the shared portable updater** from Quick 2FA. It takes the plugin's details as constructor arguments and is loaded from the main plugin file on admin, cron and WP-CLI requests only, no longer on front-end requests. The `fancy_product_page_updater_enabled` filter is unchanged.
- The installed version is read from the plugin header rather than `PP_FPP_VERSION`, and an error is logged if the two disagree.
- A failed GitHub lookup now backs off for an hour instead of retrying on every update check.
- The versioned-zip fallback requires a digit after the slug, so an asset like `fancy-product-page-docs.zip` is never mistaken for the plugin.
- Release data is cached in the `fancy_product_page_github_release` transient. The old `pp_fpp_github_release` transient is no longer used and expires by itself.
- The release workflow refuses to build unless the plugin header, `PP_FPP_VERSION` and the `readme.txt` stable tag all match the git tag.

- The whole plugin now passes the WordPress Coding Standards with no errors or warnings. Request values are unslashed and sanitised, comparisons are strict, and the meta box, permalink filter and structured-data code check their objects with `instanceof` before use.
- Developer docs moved to `docs/developers/` (`architecture.md`, `hooks-and-filters.md`), with a new `docs/README.md` index.

### Removed
- `UPDATER_CACHE_KEY` and `UPDATER_CACHE_TTL` constants; the updater derives its own cache names.
- `Meta_Box::is_trying_to_save_meta_box()`, which had no callers.
- The body of the deprecated `Plugin::output_structured_data()`, which never worked; it now only raises its deprecation notice.
- `dev-notes/` is no longer tracked in the public repository.

---

## [1.4.0] - 2026-07-24

### Added
- **Canonical product URL now redirects to the fancy page.** When a product has an associated fancy page, requests to its canonical `/product/...` URL are 301-redirected to that page (`Plugin::maybe_redirect_to_fancy_page()`, hooked on `template_redirect`). Previously the permalink filter only rewrote the links WordPress/WooCommerce *generated*; direct hits and search-engine crawls of the underlying product URL still reached WooCommerce's default single-product template. This closes that gap so all traffic resolves to the fancy page.

---

## [1.3.0] - 2026-07-20

### Added
- **Per-product control over structured data** — the "Fancy Product Page" meta box gains a **"Write the structured data to the fancy product page?"** checkbox. Untick it to stop the plugin emitting product schema.org JSON-LD on that product's fancy page — useful when an SEO plugin or hand-written markup already provides it. Stored as the `_suppress_fancy_page_product_schema` post meta, using inverted logic so that an absent value means "write the schema": existing products are unaffected and the checkbox shows as already ticked.
- `is_fancy_page_product_schema_suppressed()` (`functions-private.php`) and `pp_fpp_get_checkbox_html()` (`includes/form-helpers.php`).
- `dev-notes/01-snag-list.md` — running list of known papercuts.

### Fixed
- **Structured data was only generated on `page` fancy pages.** `Plugin::late_init()` tested `is_page()`, so a product mapped to a **post** (or to any post type added via the `fancy_product_page_post_types` filter) silently emitted no product schema. It now tests `is_singular( get_fancy_page_post_types() )`.
- The meta box help text said "Choose a page (not a post)", contradicting both the dropdown — which has always listed posts — and the `fancy_product_page_post_types` filter.

### Changed
- **Translatable strings reviewed.** Short, ambiguous labels (`No title`, `Add to cart`, `Save Changes`, `Not authorized`, and the two meta box labels) now use `_x()` with translator context, so machine translation resolves the intended sense instead of guessing. Translations regenerated for all 8 locales.
- The meta box title is no longer wrapped in `__()` — it is the plugin's name, a proper noun, and was being translated (French rendered it "Page produit haut de gamme").

### Deprecated
- `Plugin::output_structured_data()` — never hooked, and it reads a property that does not exist. It now emits a `_deprecated_function()` notice and will be removed in a future release. Structured data is generated by `Plugin::late_init()`.

---

## [1.2.0] - 2026-06-10

### Added
- **GitHub-based automatic updates** — `Github_Updater` (`includes/class-github-updater.php`) checks the plugin's GitHub repository for new releases and serves them through the standard WordPress plugin-update system (update notice in **Dashboard → Updates** and the "View details" modal). Results are cached in a transient. Update checks can be disabled with the `fancy_product_page_updater_enabled` filter.
- **Release workflow** — `.github/workflows/release.yml` builds and publishes the release zips (`fancy-product-page.zip` + a versioned copy) when a `v*.*.*` tag is pushed, and `.distignore` controls which files are stripped from the distributed zip.
- **Internationalisation** — `Plugin::load_textdomain()` loads the `fancy-product-page` text domain on `init` from the bundled `languages/` directory (`.pot` + `.po`/`.mo` for 8 locales).

### Changed
- **Minimum PHP raised to 8.0** (the updater uses `str_starts_with()` / `str_ends_with()`).
- Author updated to Paul Faulkner.

---

## [1.1.0] - 2026-06-10

### Removed
- **Bundled Power Plugins library (`pp-core.php` and `pp-assets/`)** — the plugin only used a small slice of this ~2300-line shared library. That slice has been reimplemented as self-contained code, and the library and its assets directory have been deleted (net −3928 / +454 lines). The plugin now has no external library dependency.

### Added
- `includes/class-component.php` — minimal base class (name/version) for plugin components.
- `includes/class-meta-box.php` — base class for admin meta boxes (nonce rendering + save-time verification).
- `includes/class-settings-core.php` — base settings controller with type-safe option get/set and render helpers.
- `includes/form-helpers.php` — `is_woocommerce_available()` and `pp_fpp_get_select_list_html()`.
- `assets/fpp-admin.css` — minimal, self-contained admin styling (replaces `pp-assets/pp-admin.css`).
- Developer documentation: `README.md`, `readme.txt`, `CHANGELOG.md`, `docs/`, `phpcs.xml`, and `dev-notes/00-project-tracker.md`.
- `CLAUDE.md` — architectural overview for AI-assisted development.

### Changed
- **License corrected to GPLv2 or later** (was GPLv3) to align with WordPress core.
- Plugin header completed with `Requires at least`, `Requires PHP`, `Requires Plugins`, and `WC requires at least`.
- `class-admin-hooks.php` now enqueues `assets/fpp-admin.css` instead of the removed `pp_enqueue_admin_assets()`, and dead commented code was removed.

### Notes
- Verified with a PHP load-harness (WordPress functions stubbed) reproducing plugin load and `Plugin::run()`: require chain, class inheritance, hook registration, the product meta box, and the form helpers all pass. `php -l` is clean across every file.

---

## [1.0.2] - 2025-07-23

### Added
- Baseline release as deployed. WooCommerce product pages backed by ordinary WordPress Pages/Posts: permalink override, SEO structured-data injection, SKU→ID add-to-cart, the `[add_to_cart_btn]` shortcode, and the product meta box. Bundled the Power Plugins library (`pp-core.php` / `pp-assets/`).
