<?php

/**
 * Base class for the plugin settings controller.
 *
 * Provides type-safe option getters/setters, nonce-guarded save handling,
 * and admin-page render helpers. Previously provided by the bundled Power
 * Plugins library (pp-core.php); reimplemented here (trimmed to what this
 * plugin uses) so the plugin is self-contained.
 *
 * @package Fancy_Product_Page
 */

namespace Fancy_Product_Page;

defined('WPINC') || die();

abstract class Settings_Core extends Component
{
    private $settings_action;
    private $settings_nonce;
    protected $settings_cap;
    protected $settings_page_name;

    public function __construct(string $name, string $version)
    {
        parent::__construct($name, $version);

        $this->settings_action = 'svestngsact' . $name;
        $this->settings_nonce = 'svestngsnce' . $name;
        $this->settings_cap = 'manage_options';
        $this->settings_page_name = $name;
    }

    /**
     * Persist submitted settings. Implemented by the concrete subclass.
     */
    abstract public function save_settings();

    public function get_settings_cap()
    {
        return $this->settings_cap;
    }

    public function set_settings_cap(string $value)
    {
        $this->settings_cap = $value;
    }

    public function get_settings_page_name()
    {
        return $this->settings_page_name;
    }

    public function get_settings_page_url()
    {
        return admin_url('options-general.php?page=' . $this->settings_page_name);
    }

    /**
     * Render helpers for the settings page.
     */
    public function render_page_title(string $page_title = '')
    {
        if (empty($page_title)) {
            $page_title = get_admin_page_title();
        }

        printf('<h1>%s</h1>', esc_html($page_title));
    }

    public function open_wrap()
    {
        echo '<div class="wrap pp-wrap">';
    }

    public function open_form()
    {
        echo '<form method="post">';

        wp_nonce_field($this->settings_action, $this->settings_nonce);
    }

    public function close_form()
    {
        echo '</form>';
    }

    public function close_wrap()
    {
        echo '</div>';
    }

    /**
     * Verify nonce + capability, then hand off to save_settings().
     */
    public function maybe_save_settings()
    {
        if (!is_admin() || wp_doing_ajax()) {
            // ...
        } elseif (!array_key_exists($this->settings_nonce, $_POST)) {
            // ...
        } elseif (!wp_verify_nonce($_POST[$this->settings_nonce], $this->settings_action)) {
            // ...
        } elseif (!current_user_can($this->settings_cap)) {
            // ...
        } else {
            $this->save_settings();
        }
    }

    public function get_default_value(string $option_name)
    {
        return null;
    }

    public function sanitise_value(string $option_name, $value)
    {
        return $value;
    }

    /**
     * Type-safe option getters/setters.
     */
    public function get_string(string $option_name, string $default = ''): string
    {
        if (empty($default)) {
            $default = strval($this->get_default_value($option_name));
        }

        $value = strval(get_option($option_name, $default));

        return $this->sanitise_value($option_name, $value);
    }

    public function set_string(string $option_name, string $value = '', $autoload = null)
    {
        if (!empty($value)) {
            update_option($option_name, $value, $autoload);
        } else {
            delete_option($option_name);
        }
    }

    public function get_bool(string $option_name, bool $default = false): bool
    {
        return filter_var(get_option($option_name, $default), FILTER_VALIDATE_BOOLEAN);
    }

    public function set_bool(string $option_name, $value, $autoload = null)
    {
        if (filter_var($value, FILTER_VALIDATE_BOOLEAN)) {
            update_option($option_name, 'true', $autoload);
        } else {
            delete_option($option_name);
        }
    }

    public function get_int(string $option_name, int $default = 0): int
    {
        if (empty($default)) {
            $default = intval($this->get_default_value($option_name));
        }

        $value = intval(get_option($option_name, $default));

        return $this->sanitise_value($option_name, $value);
    }

    public function set_int(string $option_name, int $value, $autoload = null)
    {
        update_option($option_name, $value, $autoload);
    }

    public function get_array(string $option_name, array $default = []): array
    {
        if (empty($default)) {
            $default = (array) $this->get_default_value($option_name);
        }

        $value = (array) get_option($option_name, $default);

        return $this->sanitise_value($option_name, $value);
    }

    public function set_array(string $option_name, array $value = [], $autoload = null)
    {
        if (!empty($value)) {
            update_option($option_name, $value, $autoload);
        } else {
            delete_option($option_name);
        }
    }
}
