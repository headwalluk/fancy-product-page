# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Fancy Product Page is a WordPress/WooCommerce plugin that lets a site administrator display a WooCommerce **product** using an ordinary WordPress **Page** (or Post) instead of the default single-product template. It hooks WooCommerce's permalink, structured-data (SEO/schema.org), and add-to-cart machinery so the substituted page behaves like a real product page for links, search engines, and the cart.

The typical use case: build a rich, bespoke landing page for a product in the page editor (or a page builder such as GenerateBlocks/Elementor), associate it with a WooCommerce product, and have the whole site — menus, related-product links, shop loop — point at that page while still selling the underlying product through WooCommerce.

- **Namespace:** `Fancy_Product_Page`
- **Text Domain:** `fancy-product-page`
- **Constant/define prefix:** `PP_FPP_` (e.g. `PP_FPP_DIR`); function prefix `pp_fpp_`; global instance `$pp_fpp_plugin`
- **PHP:** 8.0+ (do NOT use `declare(strict_types=1)` — breaks WordPress/WooCommerce interop)
- **WordPress:** 6.0+, WooCommerce active (the plugin degrades gracefully when `WC()` is absent)
- **No build system** — no npm, no Composer, no bundler. Assets are plain CSS.
- **Self-contained** — as of v1.1.0 the plugin has no external library dependency. It previously bundled the Power Plugins library (`pp-core.php` + `pp-assets/`); the slice it used was reimplemented locally and the library deleted.

This plugin is published publicly on GitHub. Tracked files must contain no client names, client URLs or client data of any kind — in code, comments, docs or commit messages. `dev-notes/` is **private and untracked** (`.gitignore`) for as long as the repository is public, and blocked from the web by `dev-notes/.htaccess`. It is the right home for client-identifying material; never copy from it into a tracked file without scrubbing, and never link a `dev-notes/` path from a tracked file.

## Commands

```bash
phpcs                  # Check WordPress coding standards compliance
phpcbf                 # Auto-fix coding standards violations
phpcs includes/        # Check a specific directory
```

The config is in `phpcs.xml` — WordPress standards with prefixes `fancy_product_page`, `pp_fpp`, `Fancy_Product_Page`. The whole plugin is phpcs-clean: **no errors and no warnings**. Keep it that way — run `phpcs` before every commit, and give every `phpcs:ignore` the exact sniff name and a `-- reason`. The form helpers that escape their own output are declared in `phpcs.xml` (`customAutoEscapedFunctions`) rather than ignored at each call site.

There is no test suite. Behaviour is exercised against the live dev site through WP-CLI (`wp eval`) and `curl`; reset any state you touch afterwards, and test the defensive path (a wrong-typed hook argument, a bad nonce) as well as the happy one.

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
- **`Github_Updater`** (`class-github-updater.php`) — Standalone (no base class). Checks the GitHub repo (`UPDATER_GITHUB_REPO`) for the latest release and serves it through the WP plugin-update system. A verbatim copy of quick-2fa's portable updater: **only the `namespace` and `@package` lines differ — never edit it here.** Fixes go into quick-2fa first and are copied across (see quick-2fa's CLAUDE.md, "Porting the GitHub updater", for the drift-check `diff`). Loaded and instantiated from `fancy-product-page.php` on admin, cron and WP-CLI requests only. Disabled by the `fancy_product_page_updater_enabled` filter (a public contract). Caches in the `fancy_product_page_github_release` transient (12h) with a `fancy_product_page_github_failed` back-off (1h); `Github_Updater::delete_cache( 'fancy-product-page' )` clears both. Reads the installed version from the plugin header and logs an error if `PP_FPP_VERSION` disagrees. Releases are built by `.github/workflows/release.yml` on `v*.*.*` tags, which refuses to build unless the header, `PP_FPP_VERSION` and the `readme.txt` stable tag all match the tag; `.distignore` controls the zip contents.

- **`Component`** (`class-component.php`) — minimal base storing name/version.
- **`Settings_Core`** (`class-settings-core.php`) — base settings controller: type-safe option get/set and render helpers.
- **`Meta_Box`** (`class-meta-box.php`) — base meta-box class: nonce rendering and save-time verification.

(`Component`, `Settings_Core`, `Meta_Box`, and the helpers in `form-helpers.php` were previously provided by `pp-core.php`; they are now local — see Milestone 1 in the tracker.)

### What the plugin actually does (functional map)

The behaviour is driven by hooks registered in `Plugin::run()`:

1. **Permalink override** — `post_type_link` filter → `override_product_permalink()`. If a product has an associated fancy page, every link WordPress/WooCommerce generates for that product resolves to the fancy page's permalink instead of `/product/...`.
2. **Structured data injection** — `wp` action → `late_init()`. On a singular page that *is* a fancy product page, it finds the product whose `META_FANCY_PRODUCT_PAGE_ID` meta equals the current page ID and calls `WC()->structured_data->generate_product_data( $product )` so the page emits product schema.org JSON-LD. Skipped when the product has `META_SUPPRESS_FANCY_PAGE_PRODUCT_SCHEMA` set (see `is_fancy_page_product_schema_suppressed()`).
3. **Structured-data type** — `woocommerce_structured_data_type_for_page` filter → `structured_data_types_for_page()`. Adds `product` to the page's structured-data types so WooCommerce outputs the data generated in step 2.
4. **SKU → ID add-to-cart** — `wp_loaded` (priority 19) → `add_to_cart_action()`, gated by the `CONVERT_ADD_TO_CART_SKU_TO_ID` constant. Lets `?add-to-cart=<SKU>` work by converting a non-numeric `add-to-cart` value to a product ID before WooCommerce processes it.
5. **Add-to-cart shortcode** — `[add_to_cart_btn id="" sku="" qty=""]` (`includes/shortcode-add-to-cart-button.php`). Renders a themed add-to-cart button (resolves product by id or SKU, shows price and quantity) for embedding inside the fancy page.
6. **Admin meta box** — `Product_Meta_Box` adds a "Fancy Product Page" selector to the product editor. The dropdown lists eligible pages/posts; saving writes the chosen ID to `META_FANCY_PRODUCT_PAGE_ID`, or deletes the meta when "Standard WooCommerce product page" (0) is chosen.
7. **Canonical → fancy-page redirect** — `template_redirect` action → `maybe_redirect_to_fancy_page()`. A `REDIRECT_HTTP_CODE` (301) redirect from the canonical `/product/...` URL to the associated fancy page, so direct hits and search-engine crawls of the WooCommerce URL land on the fancy page. Required functionality (re-enabled in v1.4.0); the permalink override (step 1) rewrites generated links, and this catches direct requests to the underlying product URL.

> **Dead code:** `output_structured_data()` is dead code, now marked `@deprecated` and calling `_deprecated_function()`. A candidate for cleanup during the refactor.

### Constants (`constants.php`)

All magic strings live in `constants.php` under the `Fancy_Product_Page` namespace:

| Constant | Value | Purpose |
| --- | --- | --- |
| `POST_TYPE_PRODUCT` | `product` | WooCommerce product post type |
| `META_FANCY_PRODUCT_PAGE_ID` | `_fancy_product_page` | Post-meta key storing the chosen page ID on a product |
| `META_SUPPRESS_FANCY_PAGE_PRODUCT_SCHEMA` | `_suppress_fancy_page_product_schema` | Post-meta key set only when an admin opts *out* of writing product schema to the fancy page (reverse logic — absent means "write it") |
| `FIELD_WRITE_FANCY_PAGE_PRODUCT_SCHEMA` | `pp_fpp_write_product_schema` | Name of the positively-worded meta-box checkbox backing the above |
| `CONVERT_ADD_TO_CART_SKU_TO_ID` | `true` | Enable `?add-to-cart=<SKU>` support |
| `REDIRECT_HTTP_CODE` | `301` | Status used by `maybe_redirect_to_fancy_page()` (canonical → fancy-page redirect) |

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

> **Style:** WordPress Coding Standards throughout (tabs, `array()`, inner-paren spacing), as in quick-2fa. No assignment inside a condition; an `if` with `elseif` branches ends in an explicit `else`, with no-op branches holding a one-line comment (`phpcs.xml` permits these). Cast at the boundary, compare strictly, and guard object lookups (`wc_get_product()`, hook arguments) with `instanceof`.

## Commit Messages

```
type: brief description

- Detail 1
- Detail 2
```

Types: `feat:` `fix:` `refactor:` `chore:` `docs:` `style:` `test:`

## Reference Files

- `docs/README.md` — Documentation index. **One audience per document:** `docs/usage.md` and `docs/shortcode.md` are for site administrators; `docs/developers/architecture.md` and `docs/developers/hooks-and-filters.md` for developers.
- `dev-notes/00-project-tracker.md` — Milestones, roadmap and refactor plan (private, untracked).
- `dev-notes/01-snag-list.md` — Known papercuts and small deferred defects (private, untracked).
- `README.md` / `readme.txt` / `CHANGELOG.md` — Release documentation.
- `constants.php` — Single source of truth for meta keys and configuration constants.

<!-- wp-translate:begin v=1.2.0 hash=562171e8e3562ab27909d7894626c32c46519921f9c7784bb699f12b25f7b701 -->
## Translating this plugin (wp-translate conventions)

This plugin's `.po`/`.mo` files are generated from source by
[wp-translate](https://github.com/headwalluk/wp-translate-tool), which
machine-translates strings with DeepL. Machine translation is only as good as
the strings you give it — follow these conventions when adding or editing
user-facing text.

### 1. Disambiguate short or ambiguous strings with `_x()`

DeepL handles full sentences well but guesses badly on short, context-free
labels. Give it context with `_x()` (or `esc_html_x()`, `_ex()`):

```php
// Ambiguous out of context — DeepL may read "Sent" as "late", "Folder" as "leaflet"
__( 'Sent', 'fancy-product-page' );

// Disambiguated — the context is passed to the translator and to DeepL
_x( 'Sent', 'email delivery status', 'fancy-product-page' );
_x( 'Folder', 'IMAP mailbox', 'fancy-product-page' );
_x( 'Open', 'verb; button label', 'fancy-product-page' );
```

The context (2nd argument) is never shown to users. Use it whenever a string is a
single word, a short label, or has more than one plausible meaning.

### 2. Use placeholders, never concatenation

Build dynamic text with `printf`/`sprintf` so the whole sentence translates as a
unit, and add a `translators:` comment to explain each placeholder:

```php
/* translators: %s is the user's display name */
printf( esc_html__( 'Welcome back, %s', 'fancy-product-page' ), $name );
```

Never split a sentence across multiple translation calls — word order differs
between languages.

### 3. Use `_n()` for anything that can be counted

Never build a count-dependent sentence by hand, and never settle for a single
form that reads correctly only for one number. Languages differ in how many
plural forms they have — English and German have two, French treats 0 as
singular, Polish and Russian have three, Japanese has one, Arabic has six — and
`_n()` is the only way to express that.

```php
// Wrong — "1 reviews", and untranslatable into languages with other forms
printf( esc_html__( '%d reviews', 'fancy-product-page' ), $count );

// Right — wp-translate fills every form the target locale needs
printf(
    esc_html( _n( '%d review', '%d reviews', $count, 'fancy-product-page' ) ),
    $count
);
```

Keep the placeholder in **both** forms, even when the singular reads fine
without it (`'%d review'`, not `'One review'`) — some locales use the singular
slot for other numbers too.

For a short or ambiguous countable noun, use `_nx()` — the plural equivalent of
`_x()` — so the context reaches DeepL:

```php
// "Review" alone is ambiguous: critique? opinion? inspection?
_nx( '%d review', '%d reviews', $count, 'customer feedback on a company', 'fancy-product-page' );
```

**Locales needing more than two forms will have their extra slots left empty for
a human translator.** DeepL supplies a singular and a plural; nobody can invent
Polish's third form from those, and wp-translate deliberately leaves it blank
rather than filling it with a plausible guess. Expect to see empty
`msgstr[2]` entries in `pl_PL` — that is correct behaviour, not a failure.

### 4. Acronyms and technical tokens

wp-translate keeps common acronyms (`TLS`, `API`, `SMTP`, `URL`, `ID`, `UTC`, …)
verbatim automatically. If you introduce an unusual acronym or product name that
must not be translated, keep it as its own standalone string so it is recognised,
or ask the maintainer to add it to the tool's acronym list.

### 5. Don't translate dates — let WordPress localise them

Never add month or day-of-week names (full or abbreviated) as translatable
strings. DeepL frequently mistranslates short forms like `Mon`, `Tue`, `Jan`,
`Feb` even with context hints. WordPress already ships locale-aware names — use
`$wp_locale`:

```php
global $wp_locale;
$wp_locale->get_month( $month_number );        // "January" (1-based)
$wp_locale->get_month_abbrev( $month_name );   // "Jan"
$wp_locale->get_weekday( $weekday_number );     // "Monday" (0 = Sunday)
$wp_locale->get_weekday_abbrev( $weekday_name ); // "Mon"
```

For formatted dates, prefer `wp_date()` / `date_i18n()`, which localise month and
day names automatically.

### 6. English source dialect

Write source strings in standard English. wp-translate handles English targets
locally (no DeepL): `en`/`en_US` use the source as-is, and `en_GB`/`en_AU`/… get
American spellings converted to British automatically (`color` → `colour`).

### Running wp-translate

After changing strings, regenerate translations:

```bash
wp-translate /path/to/this-plugin              # auto-detect locales from languages/
wp-translate /path/to/this-plugin en_GB,fr_FR  # explicit locales
wp-translate /path/to/this-plugin --dry-run    # preview; no API calls, no writes
```

Requires WP-CLI (`wp`) and a DeepL API key at `~/.config/deepl.env`. The tool
regenerates the `.pot` from source, translates new/changed strings for each
locale, and compiles the `.mo` files.
<!-- wp-translate:end -->
