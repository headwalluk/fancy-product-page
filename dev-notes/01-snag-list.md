# Fancy Product Page - Snag List

Small, known defects and rough edges that are **not** worth blocking a release
over, but should not be forgotten. Bigger pieces of work belong in
`00-project-tracker.md`; this file is for the papercuts.

Format: one `##` section per snag, newest at the top.

---

## Plugin name and author are machine-translated in the `.po` files

**Filed:** 20 July 2026 (during the 1.3.0 i18n review)
**Severity:** cosmetic, but user-visible
**Status:** open — deliberately deferred

The `Plugin Name` and `Author` plugin-header fields are extracted into the
`.pot` like any other string, and wp-translate hands them to DeepL. Proper nouns
come back translated:

| msgid | fr_FR | Should be |
| --- | --- | --- |
| `Fancy Product Page` | `Page produit haut de gamme` | `Fancy Product Page` |
| `Paul Faulkner` / previously `Headwall WP Tutorials` | `Tutoriels Headwall WP` | *(verbatim)* |

URLs are unaffected — they already pass through verbatim.

WordPress uses these header translations on the **Plugins** screen, so a French
site shows a translated plugin name.

**Already done:** the meta box title in `includes/class-product-meta-box.php`
was un-wrapped from `__()` and is now the literal `'Fancy Product Page'`, so the
meta box header is correct in every locale. That fixed the in-UI half of the
problem. The header fields cannot be fixed plugin-side — there is no way to mark
a plugin-header value as non-translatable.

**Fix options, in preference order:**

1. **Tool-side (preferred).** Add a proper-noun / brand guard to wp-translate
   alongside the existing `isProtectedAcronym()` in
   `~/Projects/wp-translate-tool/src/acronyms.ts`, so listed brand strings pass
   through verbatim for every plugin the tool touches. Durable, and fixes this
   everywhere at once.
2. **Plugin-side stopgap.** Blank the `msgstr` for these entries in the seven
   non-English `.po` files and recompile. Works, but the next `wp-translate` run
   re-translates them, so it has to be redone every time.

---

## `wp-translate --dry-run` reports "Nothing new to translate" for existing locales

**Filed:** 20 July 2026
**Severity:** low (affects the preview only; the real run is correct)
**Status:** open — belongs to the wp-translate-tool repo, not this plugin

Running `wp-translate . --dry-run` against this plugin reported *"Nothing new to
translate"* for all 8 locales, even though two new strings had been added and six
had gained a `msgctxt`. The subsequent real run correctly found and translated
11 strings per locale.

**Cause:** in `~/Projects/wp-translate-tool/src/index.ts` the
`updatePo( potFile, poFile )` msgmerge step sits inside `if ( ! dryRun )`, so a
dry run parses the *un-merged* `.po`. Every entry in that file already has a
translation, so `getUntranslated()` returns 0. The `sourceFile` fallback further
down only rescues locales that have **no** `.po` yet — meaning dry-run is
accurate for brand-new locales and silently reports zero for established ones,
which is the common case.

**Also noted:** the dry run prints *"No files will be modified"* but does
regenerate `languages/fancy-product-page.pot` — the `.pot` rebuild happens before
the `dryRun` guard.

**Impact here:** don't trust `--dry-run` to tell you whether a translation pass
is needed on this plugin. Check the `.po` files directly, or just run the real
thing.
