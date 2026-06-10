<?php

/**
 * Base class for admin meta boxes.
 *
 * Handles nonce rendering and save-time verification (capability,
 * post type, autosave and nonce checks) for a meta box attached to one
 * or more post types. Previously provided by the bundled Power Plugins
 * library (pp-core.php); reimplemented here so the plugin is
 * self-contained.
 *
 * @package Fancy_Product_Page
 */

namespace Fancy_Product_Page;

defined('WPINC') || die();

abstract class Meta_Box
{
    private $save_action;
    private $nonce_field_name;
    private $post_types;

    public function __construct($post_types, string $save_action = '', string $nonce_field_name = '')
    {
        $this->save_action = $save_action;
        $this->nonce_field_name = $nonce_field_name;

        if (empty($post_types)) {
            $this->post_types = ['post'];
        } elseif (is_array($post_types)) {
            $this->post_types = $post_types;
        } else {
            $this->post_types = explode(' ', $post_types);
        }

        if (empty($this->save_action)) {
            $this->save_action = sanitize_title(get_class($this)) . '_' . \PP_FPP_NAME . '_svembx';
        }

        if (empty($this->nonce_field_name)) {
            $this->nonce_field_name = sanitize_title(get_class($this)) . '_' . \PP_FPP_NAME . '_nncembx';
        }
    }

    public function get_post_types()
    {
        return $this->post_types;
    }

    public function render_nonce_field()
    {
        wp_nonce_field($this->save_action, $this->nonce_field_name);
    }

    public function is_trying_to_save_meta_box()
    {
        return array_key_exists($this->nonce_field_name, $_POST) && wp_verify_nonce($_POST[$this->nonce_field_name], $this->save_action);
    }

    public function is_saving_meta_box($post_id, $post)
    {
        $is_saving = false;

        if (!array_key_exists($this->nonce_field_name, $_POST)) {
            // ...
        } elseif (!wp_verify_nonce($_POST[$this->nonce_field_name], $this->save_action)) {
            // ...
        } elseif (!in_array($post->post_type, $this->post_types)) {
            // ...
        } elseif (empty(($wp_post_type = get_post_type_object($post->post_type)))) {
            // ...
        } elseif (!current_user_can($wp_post_type->cap->edit_post, $post_id)) {
            // ...
        } elseif (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            // ...
        } else {
            $is_saving = true;
        }

        return $is_saving;
    }
}
