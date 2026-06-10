# Fancy Product Page - Project Tracker

**Version:** 1.2.0
**Last Updated:** 10 June 2026 (Milestones 1, 2, 4 & 5 complete)

---

## Overview

Fancy Product Page is a WordPress/WooCommerce plugin that lets an administrator display a WooCommerce product using an ordinary WordPress Page (or Post) in place of the default single-product template. It hooks WooCommerce's permalink, structured-data (SEO), and add-to-cart machinery so the substituted page behaves like a real product page.

The plugin integrates with:

- **WooCommerce** — product permalinks, structured data (schema.org), add-to-cart by SKU, price/cart shortcode
- **WordPress** — Pages/Posts act as product front-ends; product meta box selector

This tracker covers the June 2026 modernisation effort: removing the bundled Power Plugins library, establishing version control, and bringing the plugin to a professional release standard — mirroring the structure used in the bullfix-erp plugin.

---

## Active TODO Items

- [x] Remove dependency on `pp-core.php` and `pp-assets/` (Milestone 1). ✅
- [x] Initialise git and publish to `git@github.com:headwalluk/fancy-product-page.git` (Milestone 2). ✅
- [x] Add release documentation: README.md, readme.txt, CHANGELOG.md, LICENSE, phpcs.xml, docs/ (Milestone 4). ✅
- [x] Add GitHub auto-updates + release workflow (Milestone 5). ✅
- [ ] Code cleanup: dead/disabled code and vestigial Settings (Milestone 3, pending decisions — **deferred until after 1.2.0 is tested & published**).
- [ ] Run `phpcs` against the new `phpcs.xml` and address WPCS formatting (Milestone 4 follow-up).
- [ ] **Future maintenance:** remove `maybe_redirect_to_fancy_page()` properly. It predates the current approach — the product's real URL is now handled by the `post_type_link` filter (`override_product_permalink()`), so the unhooked `template_redirect` 301 method is redundant. Left in place for now; remove during the post-1.2.0 cleanup pass. (Also `output_structured_data()` is dead.)
- [ ] `Settings` class is vestigial — no admin menu, no options defined. Either implement a real settings page or remove the scaffolding.
- [x] Plugin header lists `Domain Path: /languages` — `languages/` now populated (`.pot` + 8 locales) and text domain loaded on `init`. ✅
- [ ] Align code to WordPress Coding Standards (WPCS) — current code uses tight, non-WPCS formatting.

---

## Milestones

### Milestone 0: Onboarding & Conventions ✅

**Status:** Complete
**Priority:** High
**Started:** 10 June 2026
**Completed:** 10 June 2026
**Version:** 1.0.2

**Goal:** Understand the existing plugin, compare it against the bullfix-erp reference plugin, and capture conventions so future work is consistent.

**Completed Tasks:**
- [x] Reviewed bullfix-erp structure, `CLAUDE.md`, and `dev-notes/00-project-tracker.md` format.
- [x] Mapped Fancy Product Page functionality (permalink override, SEO structured data, SKU→ID add-to-cart, shortcode, meta box).
- [x] Identified exactly which parts of `pp-core.php` the plugin uses (see Milestone 1).
- [x] Authored `CLAUDE.md` documenting architecture, functional map, and conventions.
- [x] Created this project tracker.

---

### Milestone 1: Remove Power Plugins (`pp-core.php` / `pp-assets/`) Dependency ✅

**Status:** Complete
**Priority:** High
**Started:** 10 June 2026
**Completed:** 10 June 2026
**Version:** target 1.1.0

**Goal:** Make the plugin fully self-contained by replacing the small slice of the bundled Power Plugins library it actually uses, then delete `pp-core.php` (~2300 lines) and the `pp-assets/` directory.

**Rationale:**
- `pp-core.php` is a large shared library; the plugin uses only a fraction of it.
- Bundling the whole library bloats the plugin, complicates licensing, and couples updates to an external codebase.
- A self-contained plugin is easier to audit, document, and release.

**Dependency audit — what the plugin actually consumes from `pp-core.php`:**

Base classes:
- `Component` — base for `Plugin` and `Admin_Hooks`. Only stores `name`/`version`; the AJAX post/term-search wiring it registers is **unused** by this plugin.
- `Settings_Core` — base for `Settings`. Uses only `settings_cap`, `maybe_save_settings()`, `get_settings_cap()`, and the `open_wrap()/open_form()/close_form()/close_wrap()/render_page_title()` render helpers. The settings page is never actually shown (no menu), so most of this is dormant.
- `Meta_Box` — base for `Product_Meta_Box`. Uses `get_post_types()`, `render_nonce_field()`, `is_saving_meta_box()`. **Actively used.**

Functions:
- `is_woocommerce_available()` — trivial `function_exists('WC')` wrapper.
- `pp_get_select_list_html()` — renders the meta box `<select>`. **Actively used.**
- `pp_enqueue_admin_assets()` — enqueues `pp-assets/pp-admin.css` + `pp-admin.js` (jQuery-UI autocomplete item-chooser). The meta box uses only a native `<select>`, so the heavy JS chooser is **not needed**; only light admin CSS for `.pp-wrap`/`.pp-form-row` styling matters.

**Architectural Decisions:**
1. Introduce a minimal `includes/class-component.php`, `includes/class-settings-core.php`, `includes/class-meta-box.php` containing only the methods this plugin uses (no post/term AJAX search, no item-chooser).
2. Move `is_woocommerce_available()` and a slimmed `pp_get_select_list_html()` (renamed to a `pp_fpp_`-prefixed helper) into `functions-private.php` or a new `includes/form-helpers.php`.
3. Replace `pp-assets/` with a project-owned `assets/` directory containing a single small `fpp-admin.css` (no JS required for the native `<select>`).
4. Drop the `pp_enqueue_admin_assets()` localisation/AJAX globals entirely.
5. Decide on `defined('WPINC')` vs `defined('ABSPATH')` guards (bullfix uses `ABSPATH`).

#### Implementation Checklist

- [x] Create `includes/class-component.php` (minimal — name/version only, AJAX wiring dropped).
- [x] Create `includes/class-meta-box.php` (nonce + save-guard + post-types).
- [x] Create `includes/class-settings-core.php` (trimmed: render helpers + type-safe option get/set).
- [x] Create `includes/form-helpers.php` with `is_woocommerce_available()` and `pp_fpp_get_select_list_html()`.
- [x] Create `assets/fpp-admin.css` (self-contained, no CSS-variable dependency).
- [x] Update `Admin_Hooks` to enqueue the new CSS instead of `pp_enqueue_admin_assets()`.
- [x] Update `fancy-product-page.php` requires; remove the `pp-core.php` include.
- [x] Update `product-meta-box.php` template to use the new select helper.
- [x] Delete `pp-core.php` and `pp-assets/`.
- [x] Load-harness verification: require chain, class inheritance, `run()` hook registration, meta box, and form helpers all pass under WP stubs. `php -l` clean on all files; no dangling references to removed symbols.

**Testing:** Verified via a PHP load-harness (WordPress functions stubbed) that reproduces plugin load + `Plugin::run()`. Still pending manual smoke-test against a live WooCommerce install: product meta box, permalink override, page structured data, `?add-to-cart=SKU`, `[add_to_cart_btn]`.

**Result:** Removed ~2300-line `pp-core.php` and the `pp-assets/` directory (net −3928/+454 lines). Plugin is now fully self-contained.

---

### Milestone 2: Version Control & GitHub ✅

**Status:** Complete
**Priority:** High
**Started:** 10 June 2026
**Completed:** 10 June 2026

**Goal:** Put the plugin under git and publish to `git@github.com:headwalluk/fancy-product-page.git`.

#### Implementation Checklist

- [x] Add `.gitignore` (mirror bullfix-erp: vendor, node_modules, IDE files, OS files, archives).
- [x] `git init` on branch `main`.
- [x] Initial commit of the working baseline (incl. pp-core) so the refactor shows as a clean diff.
- [x] Add remote `git@github.com:headwalluk/fancy-product-page.git` and push `main`.
- [x] Milestone 1 done on branch `refactor/remove-pp-core` (pushed); merge to `main` pending review.

---

### Milestone 3: Code Cleanup 📋

**Status:** Not Started
**Priority:** Medium
**Version:** —

**Goal:** Remove dead/disabled code and resolve the vestigial settings scaffolding.

#### Implementation Checklist

- [ ] Resolve `maybe_redirect_to_fancy_page()` — wire behind a setting or remove (note the permalink override already covers most link cases).
- [ ] Remove `output_structured_data()` dead code and unused `$rendered_product_ids`.
- [ ] Strip commented-out scaffolding copied from the source template plugin.
- [ ] Decide whether to keep the `Settings` page (implement) or remove it.
- [ ] Remove stray `error_log()` debug calls.

---

### Milestone 4: Release Documentation & Standards ✅ (docs) / 📋 (phpcs + i18n)

**Status:** Documentation complete; phpcs run and i18n catalogue pending
**Priority:** Medium
**Started:** 10 June 2026
**Completed (docs):** 10 June 2026

**Goal:** Bring the plugin to professional release standard, matching bullfix-erp's documentation set.

#### Implementation Checklist

- [x] `README.md` — developer-facing overview, badges, links to docs.
- [x] `readme.txt` — WordPress.org plugin header format (Description, Features, Installation, FAQ, Changelog).
- [x] `CHANGELOG.md` — Keep a Changelog + SemVer; 1.0.2 baseline + 1.1.0 refactor.
- [x] `LICENSE` — **GPLv2** (corrected from GPLv3 to match WordPress core; header updated to match).
- [x] `phpcs.xml` — WordPress standards with prefixes `fancy_product_page`, `pp_fpp`, `Fancy_Product_Page`.
- [x] `docs/` — `architecture.md`, `hooks.md`, `shortcode.md`, `usage.md`.
- [x] Completed plugin header (`Requires at least` / `Requires PHP` / `Requires Plugins` / `WC requires at least`) and bumped version to 1.1.0.
- [x] `languages/` — `.pot` + per-locale `.po`/`.mo` added (8 locales), and `Plugin::load_textdomain()` wired on `init` to load them. ✅
- [ ] Run `phpcs` and resolve findings. **Baseline (10 Jun 2026):** 1527 errors / 72 warnings, of which **phpcbf can auto-fix 1398** (mostly WPCS whitespace + Yoda conditions). Recommend a dedicated `style:` commit running `phpcbf` then hand-fixing the ~129 remainder — kept separate from functional changes for reviewability.

---

### Milestone 5: GitHub Auto-Updates & Release Workflow ✅

**Status:** Complete
**Priority:** Medium
**Started:** 10 June 2026
**Completed:** 10 June 2026
**Version:** 1.2.0

**Goal:** Let installed copies update themselves from GitHub releases, with an automated release-build pipeline — ported from the `quick-2fa` plugin.

**Rationale:**
- The plugin is distributed via GitHub, not WordPress.org, so it needs its own update channel.
- `quick-2fa` already has a proven updater + release workflow; reuse it rather than reinvent.

**Architectural Decisions:**
1. `Github_Updater` (`includes/class-github-updater.php`) hooks `pre_set_site_transient_update_plugins`, `plugins_api`, and `upgrader_process_complete`.
2. Instantiated in `Plugin::run()` (not `admin_init`) so it also runs under wp-cron auto-updates.
3. Config via namespaced constants in `constants.php`: `UPDATER_GITHUB_REPO`, `UPDATER_CACHE_KEY`, `UPDATER_CACHE_TTL`.
4. Plugin identity via global constants `\PP_FPP_BASENAME`, `\PP_FPP_FILE`, `\PP_FPP_VERSION`.
5. Kept the imported file in its WPCS formatting (tabs, `array()`) rather than down-converting to the legacy 4-space style — it already matches the eventual WPCS target.
6. `release.yml` builds `fancy-product-page.zip` (+ versioned copy) on `v*.*.*` tags; `.distignore` strips dev files from the zip.

#### Implementation Checklist

- [x] Refactor `includes/class-github-updater.php` (namespace, constants, guard, filter `fancy_product_page_updater_enabled`, log prefix, `@since 1.2.0`).
- [x] Add `UPDATER_*` constants to `constants.php`.
- [x] Add `PP_FPP_BASENAME` define; `require` the updater and `new Github_Updater()` in `Plugin::run()`.
- [x] Refactor `.github/workflows/release.yml` (slug `fancy-product-page`, `main` branch, release body).
- [x] Add `.distignore`.
- [x] Bump version to 1.2.0; update CHANGELOG, readme.txt, README.

**Testing:** `php -l` clean; load-harness confirms the updater instantiates and registers its three hooks within `Plugin::run()` without error. Live update flow to be verified after the first `v1.2.0` GitHub release is tagged.

**Release step (manual, when ready):** commit, then `git tag v1.2.0 && git push origin v1.2.0` to trigger the workflow and publish the first release.

---

## Current Architecture Notes

- **Bootstrap:** `fancy-product-page.php` → manual `require_once` chain → `new Plugin()->run()`.
- **Hook surface:** registered in `Plugin::run()` — `post_type_link`, `wp`, `wp_loaded`, `admin_init`, `init` (textdomain), `woocommerce_structured_data_type_for_page`. The updater registers its own (`pre_set_site_transient_update_plugins`, `plugins_api`, `upgrader_process_complete`) in `Github_Updater::__construct()`.
- **Data model:** single post-meta key `_fancy_product_page` on `product` posts, pointing at a page/post ID.
- **Front-end:** the fancy page IS a normal page; the plugin only injects product structured data and rewrites product links to it.

## Technical Debt

- Whole-library bundling of `pp-core.php` (addressed in Milestone 1).
- Vestigial `Settings` class with no menu or options.
- Disabled/dead code (`maybe_redirect_to_fancy_page`, `output_structured_data`).
- No coding-standards config, no tests, no i18n catalogue.
- Non-WPCS code formatting throughout.
