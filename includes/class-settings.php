<?php

namespace Fancy_Product_Page;

defined('WPINC') || die();

class Settings extends Settings_Core
{
    public function __construct(string $name, string $version)
    {
        parent::__construct($name, $version);
    }

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

    public function save_settings()
    {
        // ...
    }

    public function get_default_value(string $option_name)
    {
        $value = null;

        return $value;
    }
}
