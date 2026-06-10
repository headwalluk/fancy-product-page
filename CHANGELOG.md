# Changelog

All notable changes to Fancy Product Page will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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
