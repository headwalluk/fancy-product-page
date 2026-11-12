# CLAUDE.md

Repository: public

Follows the **headwall-wp-plugin** skill. Load it before working on this plugin; this file holds
only what is specific to Fancy Product Page.

## Project Overview

Lets a WooCommerce product use an ordinary Page or Post as its front end. The plugin rewrites
the product's links to the page, 301-redirects the product's own URL there, writes the product's
schema.org JSON-LD onto the page, and makes `?add-to-cart=<SKU>` and the `[add_to_cart_btn]`
shortcode work for building the page.

- **Namespace:** `Fancy_Product_Page` · **Text domain:** `fancy-product-page` · **Prefixes:** `fancy_product_page_` (hooks, updater cache), `FANCY_PRODUCT_PAGE_` (constants). `PP_FPP_*` constants and the `$pp_fpp_plugin` global are 1.x names kept for compatibility
- **PHP / WordPress floor:** 8.2 / 6.2. **WooCommerce:** 6.0+, `Requires Plugins: woocommerce`
- **No build system, no settings, no stored options, no WP-CLI commands.** Configuration is per product, in post meta

## Request Flow

- `post_type_link` → `Plugin::override_product_permalink()`: a product with a fancy page links
  to the page
- `template_redirect` → `maybe_redirect_to_fancy_page()`: a request for the product's own URL
  301s to the page (`wp_redirect()`, not `wp_safe_redirect()`, so domain-mapped sites work)
- `wp` → `late_init()`: on a singular page of an eligible type, find the product whose
  `_fancy_product_page` meta is this page and call
  `WC()->structured_data->generate_product_data()`, unless the product opted out;
  `woocommerce_structured_data_type_for_page` then adds `product`
- `wp_loaded` (19, before WooCommerce at 20) → `add_to_cart_action()`: a non-numeric
  `add-to-cart` value is looked up as a SKU and replaced with the product ID
- `admin_init` → `Product_Meta_Box` (`add_meta_boxes_product`, `save_post_product`) and the
  `Admin_Hooks` stylesheet

Every lookup goes through `get_fancy_product_page_id()`, which returns 0 unless the product
exists, the mapped post is one of `get_fancy_page_post_types()`, and it is **published**.

## Key Files

| File | Purpose |
|------|---------|
| `fancy-product-page.php` | Header, constants and their deprecated `PP_FPP_*` aliases, requires, updater, run |
| `constants.php` | Meta keys, field and nonce names, meta box ID, style handle, redirect code, updater repo |
| `functions.php` | `has_fancy_product_page()`, global namespace |
| `functions-private.php` | `get_fancy_product_page_id()`, `get_fancy_page_post_types()` (static memo), `is_fancy_page_product_schema_suppressed()`, `log_error()` |
| `includes/class-plugin.php` | Hooks and the front-end behaviour |
| `includes/class-product-meta-box.php` | Meta box render, save and save checks |
| `includes/class-admin-hooks.php` | Meta box stylesheet, for `edit_products` |
| `includes/form-helpers.php` | `is_woocommerce_available()`, `get_select_list_html()`, `get_checkbox_html()` (declared auto-escaped in `phpcs.xml`) |
| `includes/shortcode-add-to-cart-button.php` | `[add_to_cart_btn id sku qty]` |
| `includes/class-github-updater.php` | Copied from Quick 2FA; change only `namespace` and `@package` |
| `views/product-meta-box.php` | The meta box markup |

## Data Storage

- Product meta `_fancy_product_page`: the chosen page ID. Absent means the standard page
- Product meta `_suppress_fancy_page_product_schema`: `'yes'` only when the admin unticked
  "Write the structured data". Inverted so products saved before 1.3.0 keep writing schema
- Neither key carries the plugin prefix; both predate the rule and are stored data
- Updater transients `fancy_product_page_github_release` / `_github_failed`
- `uninstall.php` deletes the updater cache only. Product meta stays, so reinstalling restores
  the mappings

## Public Contracts

The skill's `public-contracts.md` has the rules. This plugin's:

| Contract | Names |
|----------|-------|
| Filters | `fancy_product_page_post_types`, `fancy_product_page_updater_enabled` |
| Theme helper | `has_fancy_product_page( int $product_id ): bool`, global namespace |
| Shortcode | `[add_to_cart_btn id="" sku="" qty=""]` and its markup classes |
| URL | `?add-to-cart=<SKU>` |
| Stored keys | `_fancy_product_page`, `_suppress_fancy_page_product_schema` |
| Style handle | `fancy-product-page-admin` |
| Meta box ID | `Fancy_Product_Page\Product_Meta_Box`. Users' box order and collapsed state are stored under it |
| Global constants | `FANCY_PRODUCT_PAGE_*`; `PP_FPP_*` deprecated in 1.5.0, remove in 2.0.0 |
| Global variable | `$pp_fpp_plugin` |
| Deprecated method | `Plugin::output_structured_data()`, deprecated 1.3.0, remove in 2.0.0 |

All of them are documented in `docs/developers/hooks-and-filters.md` and
`docs/developers/architecture.md`.

## Testing

`dev-notes/testing/test-plugin.php` creates its own pages and products and deletes them:

```bash
wp eval-file wp-content/plugins/fancy-product-page/dev-notes/testing/test-plugin.php
```

`get_fancy_page_post_types()` memoises per request, so filtered post-type cases are checked
with one `wp eval` each. The redirect and the JSON-LD are checked over HTTP with `curl` against a
temporary page and product. On the dev site, anonymous `?add-to-cart=` requests get a bare 302
with no cart cookie even for a numeric ID, so the SKU conversion is tested in-process.

It must pass before a release.

## Release

Follow the skill's `release.md` (public). The updater's repository is `UPDATER_GITHUB_REPO` in
`constants.php`. The default branch is `main`.

## Reference Files

`docs/` (tracked, public):

- `docs/usage.md`, `docs/shortcode.md`: site administrators
- `docs/developers/architecture.md`, `docs/developers/hooks-and-filters.md`: developers

`dev-notes/` (private, untracked):

- `dev-notes/00-project-tracker.md`: milestones and roadmap
- `dev-notes/01-snag-list.md`: small deferred defects
- `dev-notes/testing/`: the harness

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
