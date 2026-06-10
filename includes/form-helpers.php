<?php

/**
 * Small form / environment helpers.
 *
 * Previously provided by the bundled Power Plugins library (pp-core.php);
 * reimplemented here (only what this plugin uses) so the plugin is
 * self-contained.
 *
 * @package Fancy_Product_Page
 */

namespace Fancy_Product_Page;

defined('WPINC') || die();

/**
 * Is WooCommerce loaded and available?
 */
function is_woocommerce_available(): bool
{
    return function_exists('WC');
}

/**
 * Generate a unique, sequential control id for labelling form fields.
 */
function pp_fpp_get_next_control_id(): string
{
    global $pp_fpp_control_index;

    if (is_null($pp_fpp_control_index)) {
        $pp_fpp_control_index = 1;
    }

    $control_id = 'fppctx' . $pp_fpp_control_index;

    ++$pp_fpp_control_index;

    return $control_id;
}

/**
 * Render a labelled <select> control with optional help text.
 *
 * @param string $field_name         The select's name attribute.
 * @param string $label              Field label.
 * @param array  $values             Map of option value => label (or ['label' => ...]).
 * @param string $current_value      Currently selected value.
 * @param string $help_text          Optional help text shown under the label.
 * @param string $additional_classes Space-separated extra classes for the select.
 *
 * @return string Escaped HTML.
 */
function pp_fpp_get_select_list_html(string $field_name, string $label, array $values, string $current_value = '', string $help_text = '', string $additional_classes = ''): string
{
    $classes = explode(' ', $additional_classes);
    $control_id = pp_fpp_get_next_control_id();

    $html = sprintf('<label for="%s">%s</label><span class="pp-help">%s</span>', esc_attr($control_id), esc_html($label), esc_html($help_text));

    $html .= sprintf('<select id="%s" name="%s" class="%s">', esc_attr($control_id), esc_attr($field_name), esc_attr(implode(' ', $classes)));

    foreach ($values as $value_key => $value_meta) {
        $value_label = '';
        $is_valid = false;
        if (is_string($value_meta)) {
            $value_label = (string) $value_meta;
            $is_valid = true;
        } elseif (is_array($value_meta) && array_key_exists('label', $value_meta)) {
            $value_label = (string) $value_meta['label'];
            $is_valid = true;
        } else {
            error_log(__FUNCTION__ . ' Invalid options');
        }

        if ($is_valid) {
            $props = '';
            if (!empty($current_value) && $current_value == $value_key) {
                $props .= ' selected';
            }

            $html .= sprintf('<option value="%s" %s>%s</option>', esc_attr($value_key), $props, esc_html($value_label));
        }
    }

    $html .= '</select>';

    return $html;
}
