<?php

/**
 * Plugin settings controller.
 *
 * @package Fancy_Product_Page
 */

namespace Fancy_Product_Page;

defined('WPINC') || die();

/**
 * Concrete settings controller for the plugin.
 *
 * Extends {@see Settings_Core} with the plugin's settings-page rendering and
 * save behaviour. Currently minimal: no settings page is registered and no
 * options are defined yet.
 */
class Settings extends Settings_Core
{
    /**
     * Constructor.
     *
     * @param string $name    Plugin name/slug.
     * @param string $version Plugin version.
     */
    public function __construct(string $name, string $version)
    {
        parent::__construct($name, $version);
    }

    /**
     * Render the settings page.
     *
     * @return void
     */
    public function render_settings_page()
    {
        if (!current_user_can($this->settings_cap)) {
            printf('<p>%s</p>', esc_html__('Not authorized', 'fancy-product-page'));
        } else {
            $this->open_wrap();

            $this->render_page_title();

            $this->open_form();

            $settings = $this;

            // if ((bool) apply_filters('ttt_is_importer_enabled', IS_IMPORTER_ENABLED)) {
            // 	include PP_TTT_ADMIN_TEMPLATES_DIR . 'settings-import.php';
            // }

            // if ((bool) apply_filters('ttt_is_business_service_enabled', IS_BUSINESS_SERVICE_ENABLED)) {
            // 	include PP_TTT_ADMIN_TEMPLATES_DIR . 'settings-business-service.php';
            // }

            // if (is_woocommerce_available()) {
            // 	include PP_TTT_ADMIN_TEMPLATES_DIR . 'settings-woocommerce.php';
            // }

            submit_button(esc_html__('Save Changes', 'fancy-product-page'));

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
    public function save_settings()
    {
        // ...
    }

    /**
     * Get the default value for an option.
     *
     * @param string $option_name Option key.
     *
     * @return mixed The default value, or null when none is defined.
     */
    public function get_default_value(string $option_name)
    {
        $value = null;

        return $value;
    }
}
