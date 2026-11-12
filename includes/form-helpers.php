<?php
/**
 * Form and environment helpers.
 *
 * @package Fancy_Product_Page
 * @since 1.1.0
 */

namespace Fancy_Product_Page;

// Exit if accessed directly.
defined( 'ABSPATH' ) || die();

/**
 * Check whether WooCommerce is loaded.
 *
 * @since 1.1.0
 *
 * @return bool True when WooCommerce is active.
 */
function is_woocommerce_available(): bool {
	return function_exists( 'WC' );
}

/**
 * Get a unique control ID for labelling a form field.
 *
 * @since 1.1.0
 *
 * @return string Control ID.
 */
function get_next_control_id(): string {
	static $control_index = 0;

	++$control_index;

	return 'fppctx' . $control_index;
}

/**
 * Render a labelled checkbox with optional help text.
 *
 * @since 1.1.0
 *
 * @param string $field_name         The checkbox's name attribute.
 * @param string $label              Field label (rendered beside the checkbox).
 * @param bool   $is_checked         Whether the checkbox starts ticked.
 * @param string $help_text          Optional help text shown under the label.
 * @param string $additional_classes Space-separated extra classes for the input.
 * @return string Escaped HTML.
 */
function get_checkbox_html( string $field_name, string $label, bool $is_checked = false, string $help_text = '', string $additional_classes = '' ): string {
	$control_id = get_next_control_id();

	$html = sprintf(
		'<label class="pp-checkbox-label" for="%s"><input type="checkbox" id="%s" name="%s" value="1" class="%s" %s />%s</label>',
		esc_attr( $control_id ),
		esc_attr( $control_id ),
		esc_attr( $field_name ),
		esc_attr( $additional_classes ),
		$is_checked ? 'checked' : '',
		esc_html( $label )
	);

	if ( '' !== $help_text ) {
		$html .= sprintf( '<span class="pp-help">%s</span>', esc_html( $help_text ) );
	}

	return $html;
}

/**
 * Render a labelled select control with optional help text.
 *
 * @since 1.1.0
 *
 * @param string                $field_name         The select's name attribute.
 * @param string                $label              Field label.
 * @param array<string, string> $values             Option value => label.
 * @param string                $current_value      Currently selected value.
 * @param string                $help_text          Optional help text shown under the label.
 * @param string                $additional_classes Space-separated extra classes for the select.
 * @return string Escaped HTML.
 */
function get_select_list_html( string $field_name, string $label, array $values, string $current_value = '', string $help_text = '', string $additional_classes = '' ): string {
	$control_id = get_next_control_id();

	$html  = sprintf( '<label for="%s">%s</label><span class="pp-help">%s</span>', esc_attr( $control_id ), esc_html( $label ), esc_html( $help_text ) );
	$html .= sprintf( '<select id="%s" name="%s" class="%s">', esc_attr( $control_id ), esc_attr( $field_name ), esc_attr( $additional_classes ) );

	foreach ( $values as $value_key => $value_label ) {
		$html .= sprintf(
			'<option value="%s"%s>%s</option>',
			esc_attr( (string) $value_key ),
			(string) $value_key === $current_value ? ' selected' : '',
			esc_html( (string) $value_label )
		);
	}

	$html .= '</select>';

	return $html;
}
