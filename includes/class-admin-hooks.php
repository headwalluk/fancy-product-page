<?php

namespace Fancy_Product_Page;

defined('WPINC') || die();

class Admin_Hooks extends Component
{
    public function __construct(string $name, string $version)
    {
        parent::__construct($name, $version);
    }

    public function admin_enqueue_scripts($current_page)
    {
        $are_assets_required = false;

        $settings = get_settings_controller();

        if (current_user_can($settings->get_settings_cap())) {
            $post_types = [POST_TYPE_PRODUCT];

            $are_assets_required |= $current_page == 'post.php' && in_array(get_post_type(), $post_types);
            $are_assets_required |= $current_page == 'edit.php' && in_array(get_post_type(), $post_types);
            $are_assets_required |= $current_page == 'post-new.php' && array_key_exists('post_type', $_GET) && in_array($_GET['post_type'], $post_types);
        }

        if ($are_assets_required) {
            wp_enqueue_style(
                $this->name . '-admin',
                PP_FPP_ASSETS_URL . 'fpp-admin.css',
                null,
                $this->version
            );
        }
    }
}
