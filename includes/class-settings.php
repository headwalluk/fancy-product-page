<?php
/**
 * Plugin settings controller.
 *
 * @package Fancy_Product_Page
 */

namespace Fancy_Product_Page;

defined( 'WPINC' ) || die();

/**
 * Concrete settings controller for the plugin.
 *
 * Extends {@see Settings_Core} with the plugin's settings-page rendering and
 * save behaviour. Currently minimal: no settings page is registered and no
 * options are defined yet.
 */
class Settings extends Settings_Core {

	/**
	 * Render the settings page.
	 *
	 * @return void
	 */
	public function render_settings_page() {
		if ( ! current_user_can( $this->settings_cap ) ) {
			printf( '<p>%s</p>', esc_html_x( 'Not authorized', 'permission error shown in place of the settings page', 'fancy-product-page' ) );
		} else {
			$this->open_wrap();

			$this->render_page_title();

			$this->open_form();

			submit_button( esc_html_x( 'Save Changes', 'verb; form submit button label', 'fancy-product-page' ) );

			$this->close_form();

			$this->close_wrap();
		}
	}

	/**
	 * Persist submitted settings.
	 *
	 * Called by {@see Settings_Core::maybe_save_settings()} after nonce and
	 * capability checks. No options are defined yet.
	 *
	 * @return void
	 */
	public function save_settings() {
		// No options are defined yet.
	}

	/**
	 * Get the default value for an option.
	 *
	 * @param string $option_name Option key.
	 *
	 * @return mixed The default value, or null when none is defined.
	 */
	public function get_default_value( string $option_name ) {
		$value = null;

		return $value;
	}
}
