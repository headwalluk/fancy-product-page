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
    /**
     * Nonce action for the settings form.
     *
     * @var string
     */
    private $settings_action;

    /**
     * Nonce field name for the settings form.
     *
     * @var string
     */
    private $settings_nonce;

    /**
     * Capability required to manage settings.
     *
     * @var string
     */
    protected $settings_cap;

    /**
     * Settings page slug.
     *
     * @var string
     */
    protected $settings_page_name;

    /**
     * Constructor.
     *
     * @param string $name    Plugin name/slug.
     * @param string $version Plugin version.
     */
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
     *
     * @return void
     */
    abstract public function save_settings();

    /**
     * Get the capability required to manage settings.
     *
     * @return string
     */
    public function get_settings_cap()
    {
        return $this->settings_cap;
    }

    /**
     * Set the capability required to manage settings.
     *
     * @param string $value Capability name.
     *
     * @return void
     */
    public function set_settings_cap(string $value)
    {
        $this->settings_cap = $value;
    }

    /**
     * Get the settings page slug.
     *
     * @return string
     */
    public function get_settings_page_name()
    {
        return $this->settings_page_name;
    }

    /**
     * Get the admin URL of the settings page.
     *
     * @return string
     */
    public function get_settings_page_url()
    {
        return admin_url('options-general.php?page=' . $this->settings_page_name);
    }

    /**
     * Render the settings page `<h1>` title.
     *
     * @param string $page_title Optional title; defaults to the admin page title.
     *
     * @return void
     */
    public function render_page_title(string $page_title = '')
    {
        if (empty($page_title)) {
            $page_title = get_admin_page_title();
        }

        printf('<h1>%s</h1>', esc_html($page_title));
    }

    /**
     * Open the settings page wrapper `<div>`.
     *
     * @return void
     */
    public function open_wrap()
    {
        echo '<div class="wrap pp-wrap">';
    }

    /**
     * Open the settings `<form>` and output its nonce field.
     *
     * @return void
     */
    public function open_form()
    {
        echo '<form method="post">';

        wp_nonce_field($this->settings_action, $this->settings_nonce);
    }

    /**
     * Close the settings `<form>`.
     *
     * @return void
     */
    public function close_form()
    {
        echo '</form>';
    }

    /**
     * Close the settings page wrapper `<div>`.
     *
     * @return void
     */
    public function close_wrap()
    {
        echo '</div>';
    }

    /**
     * Verify the nonce and capability, then hand off to {@see save_settings()}.
     *
     * @return void
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

    /**
     * Get the default value for an option. Override in the subclass.
     *
     * @param string $option_name Option key.
     *
     * @return mixed
     */
    public function get_default_value(string $option_name)
    {
        return null;
    }

    /**
     * Sanitise a value before it is returned by a getter. Override as needed.
     *
     * @param string $option_name Option key.
     * @param mixed  $value       Value to sanitise.
     *
     * @return mixed
     */
    public function sanitise_value(string $option_name, $value)
    {
        return $value;
    }

    /**
     * Get an option as a string.
     *
     * @param string $option_name Option key.
     * @param string $default     Default value; falls back to {@see get_default_value()}.
     *
     * @return string
     */
    public function get_string(string $option_name, string $default = ''): string
    {
        if (empty($default)) {
            $default = strval($this->get_default_value($option_name));
        }

        $value = strval(get_option($option_name, $default));

        return $this->sanitise_value($option_name, $value);
    }

    /**
     * Set (or delete, when empty) a string option.
     *
     * @param string    $option_name Option key.
     * @param string    $value       Value to store.
     * @param bool|null $autoload    WordPress autoload flag.
     *
     * @return void
     */
    public function set_string(string $option_name, string $value = '', $autoload = null)
    {
        if (!empty($value)) {
            update_option($option_name, $value, $autoload);
        } else {
            delete_option($option_name);
        }
    }

    /**
     * Get an option as a boolean.
     *
     * @param string $option_name Option key.
     * @param bool   $default     Default value.
     *
     * @return bool
     */
    public function get_bool(string $option_name, bool $default = false): bool
    {
        return filter_var(get_option($option_name, $default), FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Set (or delete, when falsey) a boolean option.
     *
     * @param string    $option_name Option key.
     * @param mixed     $value       Truthy value to store as 'true'.
     * @param bool|null $autoload    WordPress autoload flag.
     *
     * @return void
     */
    public function set_bool(string $option_name, $value, $autoload = null)
    {
        if (filter_var($value, FILTER_VALIDATE_BOOLEAN)) {
            update_option($option_name, 'true', $autoload);
        } else {
            delete_option($option_name);
        }
    }

    /**
     * Get an option as an integer.
     *
     * @param string $option_name Option key.
     * @param int    $default     Default value; falls back to {@see get_default_value()}.
     *
     * @return int
     */
    public function get_int(string $option_name, int $default = 0): int
    {
        if (empty($default)) {
            $default = intval($this->get_default_value($option_name));
        }

        $value = intval(get_option($option_name, $default));

        return $this->sanitise_value($option_name, $value);
    }

    /**
     * Set an integer option.
     *
     * @param string    $option_name Option key.
     * @param int       $value       Value to store.
     * @param bool|null $autoload    WordPress autoload flag.
     *
     * @return void
     */
    public function set_int(string $option_name, int $value, $autoload = null)
    {
        update_option($option_name, $value, $autoload);
    }

    /**
     * Get an option as an array.
     *
     * @param string $option_name Option key.
     * @param array  $default     Default value; falls back to {@see get_default_value()}.
     *
     * @return array
     */
    public function get_array(string $option_name, array $default = []): array
    {
        if (empty($default)) {
            $default = (array) $this->get_default_value($option_name);
        }

        $value = (array) get_option($option_name, $default);

        return $this->sanitise_value($option_name, $value);
    }

    /**
     * Set (or delete, when empty) an array option.
     *
     * @param string    $option_name Option key.
     * @param array     $value       Value to store.
     * @param bool|null $autoload    WordPress autoload flag.
     *
     * @return void
     */
    public function set_array(string $option_name, array $value = [], $autoload = null)
    {
        if (!empty($value)) {
            update_option($option_name, $value, $autoload);
        } else {
            delete_option($option_name);
        }
    }
}
